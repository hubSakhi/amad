<?php
session_start();
include "../koneksi.php";
include "../includes/pembayaran.php";

if (!isset($_SESSION['login']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../login.php");
    exit;
}

if (!isset($_GET['id_order'])) {
    header("Location: pesanan.php");
    exit;
}

$id_order = (int) $_GET['id_order'];

$pesan = "";

/* Ambil data order dulu (dipakai untuk validasi bukti pembayaran) */
$order_query = mysqli_query($conn, "
    SELECT
        tb_order.*,
        tb_user.nama
    FROM tb_order
    JOIN tb_user
        ON tb_order.id_user = tb_user.id_user
    WHERE tb_order.id_order = $id_order
");

if (!$order_query || mysqli_num_rows($order_query) == 0) {
    echo "<script>
        alert('Pesanan tidak ditemukan!');
        window.location='pesanan.php';
    </script>";
    exit;
}

$order = mysqli_fetch_assoc($order_query);


/* ==========================================
   UPDATE STATUS PESANAN
   Transfer & QRIS: hanya boleh jika bukti pembayaran SUDAH ada.
   COD: tidak butuh bukti (dibayar tunai saat barang tiba),
   walaupun form-nya di-bypass langsung via POST.
   ========================================== */
if (isset($_POST['update_status'])) {

    if (metodeButuhBukti($order['metode_pembayaran']) && empty($order['bukti_pembayaran'])) {

        $pesan = "<div class='alert alert-danger'>Status tidak bisa diubah karena pelanggan belum mengunggah bukti pembayaran.</div>";

    } else {

        $status = mysqli_real_escape_string($conn, $_POST['status']);

        mysqli_query($conn, "
            UPDATE tb_order
            SET status = '$status'
            WHERE id_order = $id_order
        ");

        header("Location: detailpesanan.php?id_order=$id_order");
        exit;

    }

}

$detail_query = mysqli_query($conn, "
    SELECT
        tb_order_detail.*,
        tb_buku.judul,
        tb_buku.gambar
    FROM tb_order_detail
    JOIN tb_buku
        ON tb_order_detail.id_buku = tb_buku.id_buku
    WHERE tb_order_detail.id_order = $id_order
");

if (!$detail_query) {
    die("Query Error: " . mysqli_error($conn));
}

$butuhBukti = metodeButuhBukti($order['metode_pembayaran']);
$adaBukti = !empty($order['bukti_pembayaran']);
$bolehUbahStatus = !$butuhBukti || $adaBukti;
$buktiExt = $adaBukti ? strtolower(pathinfo($order['bukti_pembayaran'], PATHINFO_EXTENSION)) : '';
?>
<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">
<title>Detail Pesanan - Admin ASBookStore</title>

<?php include "includes/navbar.php"; ?>

</head>

<body>

<div class="admin-content">

    <h2>Detail Pesanan #<?= $order['id_order']; ?></h2>
    <br>

    <?= $pesan ?>

    <div class="stat-card" style="margin-bottom:20px;">

        <p><b>Nama User:</b> <?= htmlspecialchars($order['nama']); ?></p>
        <p><b>Tanggal Pesanan:</b> <?= date('d-m-Y H:i', strtotime($order['tanggal_order'])); ?></p>
        <p><b>Penerima:</b> <?= htmlspecialchars($order['nama_penerima'] ?: '-') ?></p>
        <p><b>No HP:</b> <?= htmlspecialchars($order['no_hp'] ?: '-') ?></p>
        <p><b>Alamat Pengiriman:</b> <?= nl2br(htmlspecialchars($order['alamat_pengiriman'] ?: '-')) ?></p>
        <p><b>Total:</b> Rp <?= number_format($order['total_harga'], 0, ",", "."); ?></p>
        <p><b>Metode Pembayaran:</b> <span class="badge-metode <?= $order['metode_pembayaran'] === 'COD' ? 'badge-cod' : '' ?>"><?= htmlspecialchars(labelMetode($order['metode_pembayaran'])) ?></span></p>

        <hr style="border:none;border-top:1px solid #eee;margin:14px 0;">

        <p><b>Bukti Pembayaran:</b></p>

        <?php if (!$butuhBukti): ?>

            <span class="badge-soft" style="background:#fef3c7;color:#92400e;">Tidak diperlukan — COD, tagih tunai Rp <?= number_format($order['total_harga'], 0, ",", "."); ?> saat barang diterima</span>

        <?php elseif ($adaBukti): ?>

            <?php if (in_array($buktiExt, ['jpg', 'jpeg', 'png', 'webp'])): ?>
                <a href="../<?= FOLDER_BUKTI_BAYAR . htmlspecialchars($order['bukti_pembayaran']) ?>" target="_blank">
                    <img src="../<?= FOLDER_BUKTI_BAYAR . htmlspecialchars($order['bukti_pembayaran']) ?>"
                         style="max-width:240px;border-radius:8px;margin:8px 0;border:1px solid #eee;">
                </a>
            <?php else: ?>
                <a href="../<?= FOLDER_BUKTI_BAYAR . htmlspecialchars($order['bukti_pembayaran']) ?>" target="_blank" class="btn btn-sm">
                    📄 Lihat File Bukti Pembayaran
                </a>
            <?php endif; ?>

        <?php else: ?>
            <span class="badge-soft badge-no">✘ Belum ada bukti pembayaran</span>
        <?php endif; ?>

        <hr style="border:none;border-top:1px solid #eee;margin:14px 0;">

        <p><b>Status:</b></p>

        <?php if (!$bolehUbahStatus): ?>

            <div class="alert alert-warning" style="margin-top:8px;">
                ⚠ Status pesanan tidak dapat diubah sampai pelanggan mengunggah bukti pembayaran.
            </div>

        <?php else: ?>

            <form method="POST" style="margin-top:8px;">

                <select name="status" style="max-width:300px;padding:10px;border:1px solid #d1d5db;border-radius:8px;">
                    <?php foreach (['Pending', 'Processing', 'Shipping', 'Completed', 'Cancelled'] as $opt): ?>
                        <option value="<?= $opt ?>" <?= $order['status'] === $opt ? 'selected' : '' ?>><?= $opt ?></option>
                    <?php endforeach; ?>
                </select>

                <button type="submit" name="update_status" class="btn" style="margin-top:10px;">
                    Update Status
                </button>

            </form>

        <?php endif; ?>

    </div>

    <div class="stat-card">

        <h4 style="margin-bottom:14px;">Daftar Buku</h4>

        <table class="table-admin">

            <thead>
            <tr>
                <th>No</th>
                <th>Buku</th>
                <th>Jumlah</th>
                <th>Harga</th>
                <th>Subtotal</th>
            </tr>
            </thead>

            <tbody>

            <?php $no = 1; while ($detail = mysqli_fetch_assoc($detail_query)): ?>

                <tr>
                    <td><?= $no++; ?></td>
                    <td>
                        <?php if (!empty($detail['gambar'])): ?>
                            <img src="../<?= FOLDER_GAMBAR_BUKU . htmlspecialchars($detail['gambar']) ?>" class="thumb-sm">
                        <?php endif; ?>
                        <?= htmlspecialchars($detail['judul']); ?>
                    </td>
                    <td><?= $detail['qty']; ?></td>
                    <td>Rp <?= number_format($detail['harga'], 0, ",", "."); ?></td>
                    <td>Rp <?= number_format($detail['subtotal'], 0, ",", "."); ?></td>
                </tr>

            <?php endwhile; ?>

            </tbody>

        </table>

    </div>

    <br>

    <a href="struk.php?id_order=<?= $order['id_order']; ?>" class="btn" target="_blank">🧾 Cetak Struk</a>
    <a href="pesanan.php" class="btn btn-outline">← Kembali</a>

</div>

</body>

</html>
