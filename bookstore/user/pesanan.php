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

$query = mysqli_query($conn, "
    SELECT *
    FROM tb_order
    WHERE id_user = $id_user
    ORDER BY id_order DESC
");

if (!$query) {
    die("Query Error: " . mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Saya - ASBookStore</title>

    <?php include "../includes/navbar.php"; ?>
</head>

<body>

<div class="container-page">

    <h1 class="section-title" style="margin-top:0;">📦 Pesanan Saya</h1>

    <?php if (isset($_GET['sukses'])): ?>
        <div class="alert alert-success">
            <?php if (($_GET['metode'] ?? '') === 'COD'): ?>
                Pesanan berhasil dibuat! Siapkan pembayaran tunai saat pesanan tiba.
            <?php else: ?>
                Pesanan berhasil dibuat! Admin akan memverifikasi bukti pembayaran kamu.
            <?php endif; ?>
            <?php if (!empty($_GET['id_order'])): ?>
                <a href="struk.php?id_order=<?= (int) $_GET['id_order'] ?>" target="_blank" style="font-weight:600;">🧾 Lihat struk</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if (mysqli_num_rows($query) == 0): ?>

        <div class="empty-box">
            <h3>Belum ada pesanan</h3>
            <p>Kamu belum melakukan pembelian buku.</p>
            <a href="../daftarbuku.php" class="btn" style="margin-top:14px;">Mulai Belanja</a>
        </div>

    <?php endif; ?>

    <?php while ($order = mysqli_fetch_assoc($query)): ?>

        <div class="order-card">

            <div class="head">
                <h3>Pesanan #<?= $order['id_order']; ?></h3>
                <span class="status-pill status-<?= htmlspecialchars($order['status']) ?>"><?= htmlspecialchars($order['status']) ?></span>
            </div>

            <p>Tanggal Pesanan: <b><?= date("d-m-Y H:i", strtotime($order['tanggal_order'])); ?></b></p>
            <p>Total Harga: <b>Rp <?= number_format($order['total_harga'], 0, ",", "."); ?></b></p>
            <p>Metode Pembayaran: <span class="badge-metode <?= $order['metode_pembayaran'] === 'COD' ? 'badge-cod' : '' ?>"><?= htmlspecialchars(labelMetode($order['metode_pembayaran'])) ?></span></p>
            <?php if (metodeButuhBukti($order['metode_pembayaran'])): ?>
            <p>
                Bukti Pembayaran:
                <?php if (!empty($order['bukti_pembayaran'])): ?>
                    <span class="badge-soft badge-yes">✔ Sudah diunggah</span>
                <?php else: ?>
                    <span class="badge-soft badge-no">✘ Belum ada</span>
                <?php endif; ?>
            </p>
            <?php else: ?>
            <p>Pembayaran: <b>Tunai saat barang tiba</b></p>
            <?php endif; ?>

            <a href="detailpesanan.php?id_order=<?= $order['id_order']; ?>" class="btn btn-sm" style="margin-top:10px;display:inline-block;">
                Lihat Detail
            </a>
            <a href="struk.php?id_order=<?= $order['id_order']; ?>" class="btn btn-sm btn-outline" style="margin-top:10px;display:inline-block;" target="_blank">
                🧾 Struk
            </a>

        </div>

    <?php endwhile; ?>

</div>

<?php include "../includes/footer.php"; ?>

</body>
</html>
