<?php
session_start();
include "../koneksi.php";

if (!isset($_SESSION['login']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$pesan = "";

// ==========================================
// SIMPAN (TAMBAH / EDIT)
// ==========================================
if (isset($_POST['simpan'])) {

    $id_buku      = isset($_POST['id_buku']) ? (int) $_POST['id_buku'] : 0;
    $judul        = mysqli_real_escape_string($conn, trim($_POST['judul']));
    $penulis      = mysqli_real_escape_string($conn, trim($_POST['penulis']));
    $penerbit     = mysqli_real_escape_string($conn, trim($_POST['penerbit']));
    $tahun_terbit = (int) $_POST['tahun_terbit'];
    $id_kategori  = (int) ($_POST['id_kategori'] ?? 0);   // 0 = tanpa kategori
    $harga        = (float) $_POST['harga'];
    $stok         = (int) $_POST['stok'];
    $deskripsi    = mysqli_real_escape_string($conn, trim($_POST['deskripsi']));

    // Kategori boleh kosong. Kalau dipilih, pastikan kategorinya masih ada.
    $kategoriSql = 'NULL';

    if ($id_kategori > 0) {
        $cekKat = mysqli_query($conn, "SELECT id_kategori FROM tb_kategori WHERE id_kategori = $id_kategori");
        if ($cekKat && mysqli_num_rows($cekKat) > 0) {
            $kategoriSql = (string) $id_kategori;
        }
    }

    if ($judul === '' || $harga <= 0) {

        $pesan = "<div class='alert alert-danger'>Judul dan harga wajib diisi dengan benar.</div>";

    } else {

        $gambarSql = "";

        // Upload gambar baru (opsional saat edit, wajib saat tambah baru jika ingin ada gambar)
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {

            $ekstensi = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
            $allowed  = ['jpg', 'jpeg', 'png', 'webp'];

            if (!in_array($ekstensi, $allowed)) {

                $pesan = "<div class='alert alert-danger'>Format gambar harus JPG, PNG, atau WEBP.</div>";

            } else {

                $namaFile = "buku_" . time() . "_" . rand(100, 999) . "." . $ekstensi;
                $tujuan   = "../" . FOLDER_GAMBAR_BUKU . $namaFile;

                if (move_uploaded_file($_FILES['gambar']['tmp_name'], $tujuan)) {
                    $gambarSql = ", gambar = '$namaFile'";

                    // Hapus gambar lama kalau sedang edit
                    if ($id_buku > 0) {
                        $lama = mysqli_query($conn, "SELECT gambar FROM tb_buku WHERE id_buku = $id_buku");
                        $rowLama = mysqli_fetch_assoc($lama);
                        if ($rowLama && !empty($rowLama['gambar'])) {
                            $pathLama = "../" . FOLDER_GAMBAR_BUKU . $rowLama['gambar'];
                            if (file_exists($pathLama)) {
                                @unlink($pathLama);
                            }
                        }
                    }
                } else {
                    $pesan = "<div class='alert alert-danger'>Gagal mengunggah gambar.</div>";
                }
            }
        }

        if ($pesan === "") {

            if ($id_buku > 0) {

                // UPDATE
                mysqli_query($conn, "
                    UPDATE tb_buku SET
                        judul = '$judul',
                        penulis = '$penulis',
                        penerbit = '$penerbit',
                        tahun_terbit = $tahun_terbit,
                        id_kategori = $kategoriSql,
                        harga = $harga,
                        stok = $stok,
                        deskripsi = '$deskripsi'
                        $gambarSql
                    WHERE id_buku = $id_buku
                ");

                $pesan = "<div class='alert alert-success'>Buku berhasil diperbarui.</div>";

            } else {

                // INSERT
                $gambarInsert = "";
                if (isset($namaFile)) {
                    $gambarInsert = mysqli_real_escape_string($conn, $namaFile);
                }

                mysqli_query($conn, "
                    INSERT INTO tb_buku
                    (judul, penulis, penerbit, tahun_terbit, id_kategori, harga, stok, deskripsi, gambar)
                    VALUES
                    ('$judul', '$penulis', '$penerbit', $tahun_terbit, $kategoriSql, $harga, $stok, '$deskripsi', '$gambarInsert')
                ");

                $pesan = "<div class='alert alert-success'>Buku baru berhasil ditambahkan.</div>";

            }

        }

    }

}

// ==========================================
// HAPUS
// ==========================================
if (isset($_GET['hapus'])) {

    $id = (int) $_GET['hapus'];

    $cek = mysqli_query($conn, "SELECT gambar FROM tb_buku WHERE id_buku = $id");
    $row = mysqli_fetch_assoc($cek);

    mysqli_query($conn, "DELETE FROM tb_buku WHERE id_buku = $id");

    if ($row && !empty($row['gambar'])) {
        $path = "../" . FOLDER_GAMBAR_BUKU . $row['gambar'];
        if (file_exists($path)) {
            @unlink($path);
        }
    }

    $pesan = "<div class='alert alert-success'>Buku berhasil dihapus.</div>";

}

// ==========================================
// DATA UNTUK MODE EDIT
// ==========================================
$editData = null;

if (isset($_GET['edit'])) {

    $id = (int) $_GET['edit'];
    $q  = mysqli_query($conn, "SELECT * FROM tb_buku WHERE id_buku = $id");

    if ($q && mysqli_num_rows($q) > 0) {
        $editData = mysqli_fetch_assoc($q);
    }
}

$kategoriList = mysqli_query($conn, "SELECT * FROM tb_kategori ORDER BY nama_kategori ASC");

$dataBuku = mysqli_query($conn, "
    SELECT tb_buku.*, tb_kategori.nama_kategori
    FROM tb_buku
    LEFT JOIN tb_kategori ON tb_buku.id_kategori = tb_kategori.id_kategori
    ORDER BY tb_buku.id_buku DESC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Buku - Admin ASBookStore</title>

<?php include "includes/navbar.php"; ?>

</head>
<body>

<div class="admin-content">

    <h2>Kelola Buku</h2>
    <hr>

    <?= $pesan ?>

    <div class="admin-grid-form">

        <div class="stat-card">

                <h5 style="margin-bottom:14px;"><?= $editData ? '✏ Edit Buku' : '+ Tambah Buku' ?></h5>

                <form method="POST" enctype="multipart/form-data">

                    <?php if ($editData): ?>
                        <input type="hidden" name="id_buku" value="<?= $editData['id_buku'] ?>">
                    <?php endif; ?>

                    <div class="form-group">
                        <label>Judul</label>
                        <input type="text" name="judul" required value="<?= $editData ? htmlspecialchars($editData['judul']) : '' ?>">
                    </div>

                    <div class="form-group">
                        <label>Penulis</label>
                        <input type="text" name="penulis" value="<?= $editData ? htmlspecialchars($editData['penulis']) : '' ?>">
                    </div>

                    <div class="form-group">
                        <label>Penerbit</label>
                        <input type="text" name="penerbit" value="<?= $editData ? htmlspecialchars($editData['penerbit']) : '' ?>">
                    </div>

                    <div class="form-group">
                        <label>Tahun Terbit</label>
                        <input type="number" name="tahun_terbit" value="<?= $editData ? (int) $editData['tahun_terbit'] : date('Y') ?>">
                    </div>

                    <div class="form-group">
                        <label>Kategori</label>
                        <select name="id_kategori">
                            <option value="">-- Tanpa Kategori --</option>
                            <?php
                            mysqli_data_seek($kategoriList, 0);
                            while ($kat = mysqli_fetch_assoc($kategoriList)):
                            ?>
                                <option value="<?= $kat['id_kategori'] ?>"
                                    <?= $editData && (int) $editData['id_kategori'] === (int) $kat['id_kategori'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($kat['nama_kategori']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Harga (Rp)</label>
                        <input type="number" name="harga" min="0" required value="<?= $editData ? (int) $editData['harga'] : '' ?>">
                    </div>

                    <div class="form-group">
                        <label>Stok</label>
                        <input type="number" name="stok" min="0" required value="<?= $editData ? (int) $editData['stok'] : 0 ?>">
                        <div class="form-hint">Jika stok diisi 0, buku otomatis tampil "Out of Stock" dan tidak bisa dibeli.</div>
                    </div>

                    <div class="form-group">
                        <label>Deskripsi</label>
                        <textarea name="deskripsi"><?= $editData ? htmlspecialchars($editData['deskripsi']) : '' ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Gambar Sampul <?= $editData ? '(kosongkan jika tidak ganti)' : '' ?></label>
                        <input type="file" name="gambar" accept=".jpg,.jpeg,.png,.webp">
                    </div>

                    <button type="submit" name="simpan" class="btn btn-block"><?= $editData ? 'Update Buku' : 'Simpan Buku' ?></button>

                    <?php if ($editData): ?>
                        <a href="buku.php" class="btn btn-outline btn-block" style="margin-top:8px;">Batal Edit</a>
                    <?php endif; ?>

                </form>

            </div>

        <div>

            <table class="table-admin">
                <thead>
                    <tr>
                        <th>Cover</th>
                        <th>Judul</th>
                        <th>Kategori</th>
                        <th>Harga</th>
                        <th>Stok</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>

                <?php $adaBuku = false; while ($b = mysqli_fetch_assoc($dataBuku)): $adaBuku = true; ?>

                    <tr>
                        <td>
                            <img
                                src="<?= !empty($b['gambar']) ? '../' . FOLDER_GAMBAR_BUKU . htmlspecialchars($b['gambar']) : 'https://via.placeholder.com/44x60?text=-' ?>"
                                class="thumb-sm">
                        </td>
                        <td><?= htmlspecialchars($b['judul']) ?></td>
                        <td><?= htmlspecialchars($b['nama_kategori'] ?? '') ?></td>
                        <td>Rp <?= number_format($b['harga'], 0, ',', '.') ?></td>
                        <td>
                            <?php if ((int) $b['stok'] <= 0): ?>
                                <span class="badge-soft badge-no">Out of Stock</span>
                            <?php else: ?>
                                <?= (int) $b['stok'] ?>
                            <?php endif; ?>
                        </td>
                        <td style="white-space:nowrap;">
                            <a href="buku.php?edit=<?= $b['id_buku'] ?>" class="btn btn-sm">Edit</a>
                            <a href="buku.php?hapus=<?= $b['id_buku'] ?>" class="btn btn-sm btn-danger"
                               onclick="return confirm('Hapus buku ini?');">Hapus</a>
                        </td>
                    </tr>

                <?php endwhile; ?>

                <?php if (!$adaBuku): ?>
                    <tr><td colspan="6" style="text-align:center;color:#888;">Belum ada data buku.</td></tr>
                <?php endif; ?>

                </tbody>
            </table>

        </div>

    </div>

</div>

</body>
</html>
