<?php
session_start();
include "../koneksi.php";
include "../includes/pembayaran.php";

if (!isset($_SESSION['id_user']) || ($_SESSION['role'] ?? '') !== 'user') {
    header("Location: ../login.php");
    exit;
}

$id_user = (int) $_SESSION['id_user'];
$root    = '../';
$userdir = '';

$pesan = "";

/* Ambil semua isi cart user (dipakai untuk ringkasan & validasi) */
function ambilCart($conn, $id_user)
{
    return mysqli_query($conn, "
        SELECT
            tb_cart.id_cart,
            tb_cart.id_buku,
            tb_cart.qty,
            tb_buku.judul,
            tb_buku.harga,
            tb_buku.stok,
            tb_buku.gambar,
            (tb_cart.qty * tb_buku.harga) AS subtotal
        FROM tb_cart
        JOIN tb_buku
            ON tb_cart.id_buku = tb_buku.id_buku
        WHERE tb_cart.id_user = $id_user
    ");
}

$queryCart = ambilCart($conn, $id_user);

if (!$queryCart) {
    die("Query Error: " . mysqli_error($conn));
}

if (mysqli_num_rows($queryCart) == 0) {
    echo "<script>
        alert('Keranjang masih kosong!');
        window.location='cart.php';
    </script>";
    exit;
}


// =========================================================
// PROSES CHECKOUT (SUBMIT FORM)
// =========================================================
if (isset($_POST['checkout'])) {

    $nama_penerima      = mysqli_real_escape_string($conn, trim($_POST['nama_penerima']));
    $no_hp              = mysqli_real_escape_string($conn, trim($_POST['no_hp']));
    $alamat_pengiriman  = mysqli_real_escape_string($conn, trim($_POST['alamat_pengiriman']));

    $metode = $_POST['metode_pembayaran'] ?? '';
    $butuhBukti = metodeButuhBukti($metode);
    $adaFile = isset($_FILES['bukti_pembayaran']) && $_FILES['bukti_pembayaran']['error'] === UPLOAD_ERR_OK;

    if ($nama_penerima === '' || $no_hp === '' || $alamat_pengiriman === '') {

        $pesan = "<div class='alert alert-danger'>Nama penerima, No HP, dan Alamat pengiriman wajib diisi.</div>";

    } elseif (!isset($METODE_BAYAR[$metode])) {

        $pesan = "<div class='alert alert-danger'>Pilih metode pembayaran terlebih dahulu.</div>";

    } elseif ($butuhBukti && !$adaFile) {

        // Transfer & QRIS wajib bukti pembayaran. COD tidak.
        $pesan = "<div class='alert alert-danger'>Bukti pembayaran wajib diunggah untuk metode " . htmlspecialchars(labelMetode($metode)) . ".</div>";

    } else {

        $file     = $adaFile ? $_FILES['bukti_pembayaran'] : null;
        $ekstensi = $adaFile ? strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)) : '';
        $allowed  = ['jpg', 'jpeg', 'png', 'webp', 'pdf'];

        if ($butuhBukti && !in_array($ekstensi, $allowed)) {

            $pesan = "<div class='alert alert-danger'>Format bukti pembayaran harus JPG, PNG, WEBP, atau PDF.</div>";

        } elseif ($butuhBukti && $file['size'] > 3 * 1024 * 1024) {

            $pesan = "<div class='alert alert-danger'>Ukuran file bukti pembayaran maksimal 3MB.</div>";

        } else {

            // Re-validasi cart & stok terkini
            $queryCart = ambilCart($conn, $id_user);

            $items       = [];
            $total_harga = 0;
            $stokTidakCukup = "";

            while ($cart = mysqli_fetch_assoc($queryCart)) {

                if ($cart['stok'] <= 0 || $cart['qty'] > $cart['stok']) {
                    $stokTidakCukup = $cart['judul'];
                    break;
                }

                $items[] = $cart;
                $total_harga += $cart['subtotal'];
            }

            if ($stokTidakCukup !== "") {

                echo "<script>
                    alert('Stok buku \"" . addslashes($stokTidakCukup) . "\" tidak mencukupi / habis!');
                    window.location='cart.php';
                </script>";
                exit;

            }

            // Simpan file bukti pembayaran (hanya Transfer & QRIS)
            $namaFileBaru = null;
            $tujuanUpload = null;
            $uploadOk     = true;

            if ($butuhBukti) {
                $namaFileBaru = "bukti_" . $id_user . "_" . time() . "." . $ekstensi;
                $tujuanUpload = "../" . FOLDER_BUKTI_BAYAR . $namaFileBaru;
                $uploadOk     = move_uploaded_file($file['tmp_name'], $tujuanUpload);
            }

            if (!$uploadOk) {

                $pesan = "<div class='alert alert-danger'>Gagal mengunggah bukti pembayaran. Coba lagi.</div>";

            } else {

                $buktiSql  = $namaFileBaru ? "'" . mysqli_real_escape_string($conn, $namaFileBaru) . "'" : "NULL";
                $metodeSql = mysqli_real_escape_string($conn, $metode);

                // =========================
                // TRANSAKSI DATABASE
                // =========================
                mysqli_begin_transaction($conn);

                try {

                    $insert_order = mysqli_query($conn, "
                        INSERT INTO tb_order
                        (id_user, nama_penerima, no_hp, alamat_pengiriman, total_harga, metode_pembayaran, bukti_pembayaran, status)
                        VALUES
                        ($id_user, '$nama_penerima', '$no_hp', '$alamat_pengiriman', '$total_harga', '$metodeSql', $buktiSql, 'Pending')
                    ");

                    if (!$insert_order) {
                        throw new Exception(mysqli_error($conn));
                    }

                    $id_order = mysqli_insert_id($conn);

                    foreach ($items as $item) {

                        $id_buku  = (int) $item['id_buku'];
                        $qty      = (int) $item['qty'];
                        $harga    = (float) $item['harga'];
                        $subtotal = (float) $item['subtotal'];

                        $insert_detail = mysqli_query($conn, "
                            INSERT INTO tb_order_detail
                            (id_order, id_buku, qty, harga, subtotal)
                            VALUES
                            ($id_order, $id_buku, $qty, '$harga', '$subtotal')
                        ");

                        if (!$insert_detail) {
                            throw new Exception(mysqli_error($conn));
                        }

                        $update_stok = mysqli_query($conn, "
                            UPDATE tb_buku
                            SET stok = stok - $qty
                            WHERE id_buku = $id_buku
                            AND stok >= $qty
                        ");

                        if (!$update_stok || mysqli_affected_rows($conn) === 0) {
                            throw new Exception("Stok tidak mencukupi untuk salah satu buku.");
                        }
                    }

                    $hapus_cart = mysqli_query($conn, "DELETE FROM tb_cart WHERE id_user = $id_user");

                    if (!$hapus_cart) {
                        throw new Exception(mysqli_error($conn));
                    }

                    mysqli_commit($conn);

                    header("Location: pesanan.php?sukses=1&id_order=$id_order&metode=" . urlencode($metode));
                    exit;

                } catch (Exception $e) {

                    mysqli_rollback($conn);

                    // Hapus file yang sudah terlanjur ter-upload
                    if ($tujuanUpload && file_exists($tujuanUpload)) {
                        unlink($tujuanUpload);
                    }

                    $pesan = "<div class='alert alert-danger'>Checkout gagal: " . htmlspecialchars($e->getMessage()) . "</div>";

                }

            }

        }

    }

    // Refresh data cart untuk ditampilkan ulang di form (kalau gagal)
    $queryCart = ambilCart($conn, $id_user);
}

// Total untuk ditampilkan di panel QRIS / COD
$totalBayar = 0;
$tmpCart = ambilCart($conn, $id_user);
while ($tmp = mysqli_fetch_assoc($tmpCart)) {
    $totalBayar += $tmp['subtotal'];
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - ASBookStore</title>

    <?php include "../includes/navbar.php"; ?>
</head>

<body>

<div class="container-page">

    <h1 class="section-title" style="margin-top:0;">💳 Checkout</h1>

    <?= $pesan ?>

    <div class="checkout-grid">

        <!-- FORM PENGIRIMAN + BUKTI PEMBAYARAN -->
        <div class="form-box">

            <h3 style="margin-bottom:16px;">Data Pengiriman</h3>

            <form method="POST" enctype="multipart/form-data">

                <div class="form-group">
                    <label>Nama Penerima</label>
                    <input type="text" name="nama_penerima" required
                        value="<?= isset($_POST['nama_penerima']) ? htmlspecialchars($_POST['nama_penerima']) : htmlspecialchars($_SESSION['nama']) ?>">
                </div>

                <div class="form-group">
                    <label>No HP</label>
                    <input type="text" name="no_hp" required
                        value="<?= isset($_POST['no_hp']) ? htmlspecialchars($_POST['no_hp']) : '' ?>">
                </div>

                <div class="form-group">
                    <label>Alamat Pengiriman</label>
                    <textarea name="alamat_pengiriman" required><?= isset($_POST['alamat_pengiriman']) ? htmlspecialchars($_POST['alamat_pengiriman']) : '' ?></textarea>
                </div>

                <hr style="border:none;border-top:1px solid #eee;margin:20px 0;">

                <h3 style="margin-bottom:12px;">Metode Pembayaran</h3>

                <?php $metodeDipilih = $_POST['metode_pembayaran'] ?? 'Transfer'; ?>

                <div class="pay-options">
                    <?php foreach ($METODE_BAYAR as $kode => $m): ?>
                        <label class="pay-option">
                            <input type="radio" name="metode_pembayaran" value="<?= $kode ?>"
                                <?= $metodeDipilih === $kode ? 'checked' : '' ?>>
                            <span class="pay-card">
                                <span class="pay-icon"><?= $m['icon'] ?></span>
                                <span class="pay-name"><?= htmlspecialchars($m['label']) ?></span>
                            </span>
                        </label>
                    <?php endforeach; ?>
                </div>

                <!-- PANEL: TRANSFER -->
                <div class="pay-panel" data-panel="Transfer">
                    <p class="pay-info">
                        Silakan transfer sesuai <b>Total Belanja</b> ke:<br>
                        <?= REK_BANK ?> <b><?= REK_NOMOR ?></b> a.n. <b><?= REK_NAMA ?></b><br>
                        Lalu unggah bukti transfer di bawah ini.
                    </p>
                </div>

                <!-- PANEL: QRIS -->
                <div class="pay-panel" data-panel="QRIS">
                    <p class="pay-info">
                        Scan kode QRIS di bawah ini dengan aplikasi e-wallet / m-banking kamu,
                        bayar sesuai <b>Total Belanja</b>, lalu unggah bukti pembayarannya.
                    </p>
                    <div class="qris-box">
                        <img src="../<?= QRIS_GAMBAR ?>" alt="QRIS ASBookStore">
                        <div class="qris-total">Total: <b>Rp <?= number_format($totalBayar, 0, ',', '.') ?></b></div>
                    </div>
                </div>

                <!-- PANEL: COD -->
                <div class="pay-panel" data-panel="COD">
                    <p class="pay-info">
                        Bayar tunai ke kurir saat pesanan sampai. Siapkan uang pas sebesar
                        <b>Rp <?= number_format($totalBayar, 0, ',', '.') ?></b>.
                        Tidak perlu mengunggah bukti pembayaran.
                    </p>
                </div>

                <!-- UPLOAD BUKTI (hanya Transfer & QRIS) -->
                <div class="form-group" id="boxBukti">
                    <label>Upload Bukti Pembayaran (wajib)</label>
                    <input type="file" name="bukti_pembayaran" id="inputBukti" accept=".jpg,.jpeg,.png,.webp,.pdf">
                    <div class="form-hint">Format JPG/PNG/WEBP/PDF, maksimal 3MB. Pesanan tidak akan diproses admin tanpa bukti pembayaran.</div>
                </div>

                <button type="submit" name="checkout" class="btn btn-block">Buat Pesanan</button>

            </form>

        </div>

        <!-- RINGKASAN PESANAN -->
        <div>

            <div class="form-box" style="margin-bottom:16px;">

                <h3 style="margin-bottom:14px;">Ringkasan Pesanan</h3>

                <?php
                $total = 0;
                while ($item = mysqli_fetch_assoc($queryCart)):
                    $total += $item['subtotal'];
                ?>
                    <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #f1f1f1;font-size:14px;">
                        <span><?= htmlspecialchars($item['judul']) ?> x<?= (int) $item['qty'] ?></span>
                        <span>Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></span>
                    </div>
                <?php endwhile; ?>

                <div style="display:flex;justify-content:space-between;padding-top:14px;font-weight:bold;font-size:17px;">
                    <span>Total</span>
                    <span>Rp <?= number_format($total, 0, ',', '.') ?></span>
                </div>

            </div>

            <a href="cart.php" class="btn btn-outline btn-block">← Kembali ke Keranjang</a>

        </div>

    </div>

</div>

<script>
(function () {
    var radios = document.querySelectorAll('input[name="metode_pembayaran"]');
    var panels = document.querySelectorAll('.pay-panel');
    var boxBukti = document.getElementById('boxBukti');
    var inputBukti = document.getElementById('inputBukti');

    function update() {
        var pilih = document.querySelector('input[name="metode_pembayaran"]:checked').value;
        panels.forEach(function (p) {
            p.style.display = (p.dataset.panel === pilih) ? 'block' : 'none';
        });
        var butuh = (pilih !== 'COD');
        boxBukti.style.display = butuh ? 'block' : 'none';
        inputBukti.required = butuh;
        if (!butuh) { inputBukti.value = ''; }
    }

    radios.forEach(function (r) { r.addEventListener('change', update); });
    update();
})();
</script>

<?php include "../includes/footer.php"; ?>

</body>
</html>
