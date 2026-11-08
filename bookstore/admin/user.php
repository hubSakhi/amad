<?php
session_start();
include "../koneksi.php";

if (!isset($_SESSION['login']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$pesan = "";

// ==========================================
// HAPUS USER
// ==========================================
if (isset($_GET['hapus'])) {

    $id = (int) $_GET['hapus'];

    $cekOrder = mysqli_query($conn, "SELECT COUNT(*) AS total FROM tb_order WHERE id_user = $id");
    $totalOrder = mysqli_fetch_assoc($cekOrder)['total'];

    if ($totalOrder > 0) {

        $pesan = "<div class='alert alert-danger'>Tidak dapat menghapus user ini karena sudah memiliki $totalOrder pesanan.</div>";

    } else {

        mysqli_query($conn, "DELETE FROM tb_cart WHERE id_user = $id");
        mysqli_query($conn, "DELETE FROM tb_user WHERE id_user = $id");

        $pesan = "<div class='alert alert-success'>User berhasil dihapus.</div>";

    }

}

$dataUser = mysqli_query($conn, "
    SELECT tb_user.*, COUNT(tb_order.id_order) AS jumlah_pesanan
    FROM tb_user
    LEFT JOIN tb_order ON tb_order.id_user = tb_user.id_user
    GROUP BY tb_user.id_user
    ORDER BY tb_user.id_user DESC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>User - Admin ASBookStore</title>

<?php include "includes/navbar.php"; ?>

</head>
<body>

<div class="admin-content">

    <h2>Kelola User</h2>
    <hr>

    <?= $pesan ?>

    <table class="table-admin">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>Username</th>
                <th>Email</th>
                <th>Total Pesanan</th>
                <th>Terdaftar</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>

        <?php $no = 1; while ($u = mysqli_fetch_assoc($dataUser)): ?>

            <tr>
                <td><?= $no++ ?></td>
                <td><?= htmlspecialchars($u['nama']) ?></td>
                <td><?= htmlspecialchars($u['username']) ?></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><?= (int) $u['jumlah_pesanan'] ?></td>
                <td><?= !empty($u['created_at']) ? date('d-m-Y', strtotime($u['created_at'])) : '-' ?></td>
                <td>
                    <a href="user.php?hapus=<?= $u['id_user'] ?>" class="btn btn-sm btn-danger"
                       onclick="return confirm('Hapus user ini?');">Hapus</a>
                </td>
            </tr>

        <?php endwhile; ?>

        <?php if ($no === 1): ?>
            <tr><td colspan="7" style="text-align:center;color:#888;">Belum ada user terdaftar.</td></tr>
        <?php endif; ?>

        </tbody>
    </table>

</div>

</body>
</html>
