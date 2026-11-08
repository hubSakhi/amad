<?php
session_start();
include "../koneksi.php";
include "../includes/pembayaran.php";

if (!isset($_SESSION['login']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$query = mysqli_query($conn, "
    SELECT tb_order.*, tb_user.nama
    FROM tb_order
    JOIN tb_user ON tb_order.id_user = tb_user.id_user
    ORDER BY tb_order.id_order DESC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Pesanan - Admin ASBookStore</title>

<?php include "includes/navbar.php"; ?>

</head>

<body>

<div class="admin-content">

    <h2>Daftar Pesanan</h2>
    <hr>

    <table class="table-admin">

        <thead>
        <tr>
            <th>No</th>
            <th>ID Order</th>
            <th>User</th>
            <th>Tanggal</th>
            <th>Total</th>
            <th>Metode</th>
            <th>Bukti Bayar</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>
        </thead>

        <tbody>

        <?php $no = 1; while ($order = mysqli_fetch_assoc($query)): ?>

            <tr>
                <td><?= $no++; ?></td>
                <td>#<?= $order['id_order']; ?></td>
                <td><?= htmlspecialchars($order['nama']); ?></td>
                <td><?= date('d-m-Y H:i', strtotime($order['tanggal_order'])); ?></td>
                <td>Rp <?= number_format($order['total_harga'], 0, ',', '.'); ?></td>
                <td><span class="badge-metode <?= $order['metode_pembayaran'] === 'COD' ? 'badge-cod' : '' ?>"><?= htmlspecialchars(labelMetode($order['metode_pembayaran'])) ?></span></td>
                <td>
                    <?php if (!metodeButuhBukti($order['metode_pembayaran'])): ?>
                        <span class="badge-soft" style="background:#f3f4f6;color:#6b7280;">— (COD)</span>
                    <?php elseif (!empty($order['bukti_pembayaran'])): ?>
                        <span class="badge-soft badge-yes">✔ Ada</span>
                    <?php else: ?>
                        <span class="badge-soft badge-no">✘ Belum</span>
                    <?php endif; ?>
                </td>
                <td><span class="status-pill status-<?= htmlspecialchars($order['status']) ?>"><?= htmlspecialchars($order['status']) ?></span></td>
                <td>
                    <a href="detailpesanan.php?id_order=<?= $order['id_order']; ?>" class="btn btn-sm">
                        Detail
                    </a>
                </td>
            </tr>

        <?php endwhile; ?>

        <?php if ($no === 1): ?>
            <tr><td colspan="9" style="text-align:center;color:#888;">Belum ada pesanan.</td></tr>
        <?php endif; ?>

        </tbody>

    </table>

</div>

</body>
</html>
