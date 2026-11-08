<?php
session_start();
include "../koneksi.php";

if (!isset($_SESSION['login']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$pesan = "";

// ==========================================
// TAMBAH KATEGORI
// ==========================================
if (isset($_POST['tambah'])) {

    $nama = mysqli_real_escape_string($conn, trim($_POST['nama_kategori']));

    if ($nama === '') {

        $pesan = "<div class='alert alert-danger'>Nama kategori tidak boleh kosong.</div>";

    } else {

        $cek = mysqli_query($conn, "SELECT * FROM tb_kategori WHERE nama_kategori = '$nama'");

        if (mysqli_num_rows($cek) > 0) {
            $pesan = "<div class='alert alert-danger'>Kategori '$nama' sudah ada.</div>";
        } else {
            mysqli_query($conn, "INSERT INTO tb_kategori (nama_kategori) VALUES ('$nama')");
            $pesan = "<div class='alert alert-success'>Kategori berhasil ditambahkan.</div>";
        }

    }

}

// ==========================================
// EDIT KATEGORI
// ==========================================
if (isset($_POST['edit'])) {

    $id   = (int) $_POST['id_kategori'];
    $nama = mysqli_real_escape_string($conn, trim($_POST['nama_kategori_edit']));

    if ($nama === '') {
        $pesan = "<div class='alert alert-danger'>Nama kategori tidak boleh kosong.</div>";
    } else {
        mysqli_query($conn, "UPDATE tb_kategori SET nama_kategori = '$nama' WHERE id_kategori = $id");
        $pesan = "<div class='alert alert-success'>Kategori berhasil diperbarui.</div>";
    }

}

// ==========================================
// HAPUS KATEGORI
// ==========================================
if (isset($_GET['hapus'])) {

    $id = (int) $_GET['hapus'];

    // Buku TIDAK ikut terhapus. Buku yang memakai kategori ini hanya dikosongkan
    // kategorinya (id_kategori = NULL), jadi tampil tanpa genre.
    $cekBuku = mysqli_query($conn, "SELECT COUNT(*) AS total FROM tb_buku WHERE id_kategori = $id");
    $totalBuku = (int) mysqli_fetch_assoc($cekBuku)['total'];

    mysqli_begin_transaction($conn);

    try {

        if (!mysqli_query($conn, "UPDATE tb_buku SET id_kategori = NULL WHERE id_kategori = $id")) {
            throw new Exception(mysqli_error($conn));
        }

        if (!mysqli_query($conn, "DELETE FROM tb_kategori WHERE id_kategori = $id")) {
            throw new Exception(mysqli_error($conn));
        }

        mysqli_commit($conn);

        $pesan = "<div class='alert alert-success'>Kategori berhasil dihapus."
               . ($totalBuku > 0 ? " $totalBuku" : "")
               . "</div>";

    } catch (Throwable $e) {

        mysqli_rollback($conn);

        $pesan = "<div class='alert alert-danger'>Gagal menghapus kategori: " . htmlspecialchars($e->getMessage())
               . ". Pastikan <b>migrasi_kategori_nullable.sql</b> sudah dijalankan di database.</div>";

    }

}

$dataKategori = mysqli_query($conn, "
    SELECT tb_kategori.*, COUNT(tb_buku.id_buku) AS jumlah_buku
    FROM tb_kategori
    LEFT JOIN tb_buku ON tb_buku.id_kategori = tb_kategori.id_kategori
    GROUP BY tb_kategori.id_kategori
    ORDER BY tb_kategori.nama_kategori ASC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Kategori - Admin ASBookStore</title>

<?php include "includes/navbar.php"; ?>

</head>
<body>

<div class="admin-content">

    <h2>Kelola Kategori</h2>
    <hr>

    <?= $pesan ?>

    <div class="admin-grid-form">

        <div class="stat-card">
            <h5 style="margin-bottom:14px;">+ Tambah Kategori</h5>

                <form method="POST">
                    <div class="form-group">
                        <label>Nama Kategori</label>
                        <input type="text" name="nama_kategori" required placeholder="Contoh: Novel">
                    </div>
                    <button type="submit" name="tambah" class="btn btn-block">Simpan</button>
                </form>
        </div>

        <div>

            <table class="table-admin">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Kategori</th>
                        <th>Jumlah Buku</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>

                <?php $no = 1; while ($kat = mysqli_fetch_assoc($dataKategori)): ?>

                    <tr>
                        <td><?= $no++ ?></td>

                        <td>

                            <form method="POST" style="display:flex;gap:8px;align-items:center;">
                                <input type="hidden" name="id_kategori" value="<?= $kat['id_kategori'] ?>">
                                <input type="text" name="nama_kategori_edit" value="<?= htmlspecialchars($kat['nama_kategori']) ?>"
                                    style="padding:6px 8px;border:1px solid #d1d5db;border-radius:6px;">
                                <button type="submit" name="edit" class="btn btn-sm">Update</button>
                            </form>

                        </td>

                        <td><?= $kat['jumlah_buku'] ?> buku</td>

                        <td>
                            <a href="kategori.php?hapus=<?= $kat['id_kategori'] ?>"
                               class="btn btn-sm btn-danger"
                               onclick="return confirm('Hapus kategori <?= htmlspecialchars(addslashes($kat['nama_kategori']), ENT_QUOTES) ?>?<?= (int) $kat['jumlah_buku'] > 0 ? '\n\n' . (int) $kat['jumlah_buku'] . ' buku di kategori ini TIDAK akan dihapus, hanya menjadi tanpa kategori.' : '' ?>');">
                                Hapus
                            </a>
                        </td>
                    </tr>

                <?php endwhile; ?>

                <?php if ($no === 1): ?>
                    <tr><td colspan="4" style="text-align:center;color:#888;">Belum ada kategori.</td></tr>
                <?php endif; ?>

                </tbody>
            </table>

        </div>

    </div>

</div>

</body>
</html>
