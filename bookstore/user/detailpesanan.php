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

if (!isset($_GET['id_order'])) {
    header("Location: pesanan.php");
    exit;
}

$id_order = (int) $_GET['id_order'];

$order_query = mysqli_query($conn, "
    SELECT *
    FROM tb_order
    WHERE id_order = $id_order
    AND id_user = $id_user
");

if (!$order_query || mysqli_num_rows($order_query) == 0) {
    echo "<script>
        alert('Pesanan tidak ditemukan!');
        window.location='pesanan.php';
    </script>";
    exit;
}

$order = mysqli_fetch_assoc($order_query);

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

$buktiExt = $order['bukti_pembayaran'] ? strtolower(pathinfo($order['bukti_pembayaran'], PATHINFO_EXTENSION)) : '';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pesanan - ASBookStore</title>

    <?php include "../includes/navbar.php"; ?>
</head>

<body>

<div class="container-page">

    <h1 class="section-title" style="margin-top:0;">📦 Detail Pesanan</h1>

    <div class="order-card">

        <p><b>Nomor Pesanan:</b> #<?= $order['id_order']; ?></p>
        <p><b>Tanggal:</b> <?= date("d-m-Y H:i", strtotime($order['tanggal_order'])); ?></p>
        <p><b>Status:</b> <span class="status-pill status-<?= htmlspecialchars($order['status']) ?>"><?= htmlspecialchars($order['status']) ?></span></p>

        <hr style="border:none;border-top:1px solid #eee;margin:14px 0;">

        <p><b>Penerima:</b> <?= htmlspecialchars($order['nama_penerima'] ?: '-') ?></p>
        <p><b>No HP:</b> <?= htmlspecialchars($order['no_hp'] ?: '-') ?></p>
        <p><b>Alamat Pengiriman:</b> <?= nl2br(htmlspecialchars($order['alamat_pengiriman'] ?: '-')) ?></p>

        <hr style="border:none;border-top:1px solid #eee;margin:14px 0;">

        <p><b>Metode Pembayaran:</b> <span class="badge-metode <?= $order['metode_pembayaran'] === 'COD' ? 'badge-cod' : '' ?>"><?= htmlspecialchars(labelMetode($order['metode_pembayaran'])) ?></span></p>

        <?php if (!metodeButuhBukti($order['metode_pembayaran'])): ?>

            <p style="margin-top:8px;">Bayar tunai ke kurir saat pesanan tiba sebesar <b>Rp <?= number_format($order['total_harga'], 0, ",", ".") ?></b>.</p>

        <?php else: ?>

        <p style="margin-top:8px;"><b>Bukti Pembayaran:</b></p>

        <?php endif; ?>

        <?php if (!metodeButuhBukti($order['metode_pembayaran'])): ?>
            <?php /* COD: tidak ada bukti pembayaran */ ?>
        <?php elseif (!empty($order['bukti_pembayaran'])): ?>

            <?php if (in_array($buktiExt, ['jpg', 'jpeg', 'png', 'webp'])): ?>
                <a href="../<?= FOLDER_BUKTI_BAYAR . htmlspecialchars($order['bukti_pembayaran']) ?>" target="_blank">
                    <img src="../<?= FOLDER_BUKTI_BAYAR . htmlspecialchars($order['bukti_pembayaran']) ?>" style="max-width:220px;border-radius:8px;margin-top:8px;border:1px solid #eee;">
                </a>
            <?php else: ?>
                <a href="../<?= FOLDER_BUKTI_BAYAR . htmlspecialchars($order['bukti_pembayaran']) ?>" target="_blank" class="btn btn-sm" style="margin-top:8px;display:inline-block;">
                    📄 Lihat File Bukti Pembayaran
                </a>
            <?php endif; ?>

        <?php else: ?>
            <span class="badge-soft badge-no">✘ Belum ada bukti pembayaran</span>
        <?php endif; ?>

    </div>

    <h3 class="section-title">Daftar Buku</h3>

    <?php while ($item = mysqli_fetch_assoc($detail_query)): ?>

        <div class="cart-item">

            <img
                src="<?= !empty($item['gambar']) ? '../' . FOLDER_GAMBAR_BUKU . htmlspecialchars($item['gambar']) : 'https://via.placeholder.com/90x126?text=Book' ?>"
                alt="<?= htmlspecialchars($item['judul']) ?>"
            >

            <div class="info">
                <h3><?= htmlspecialchars($item['judul']) ?></h3>
                <p>Harga: Rp <?= number_format($item['harga'], 0, ",", ".") ?></p>
                <p>Jumlah: <?= $item['qty']; ?></p>
                <p>Subtotal: <b>Rp <?= number_format($item['subtotal'], 0, ",", ".") ?></b></p>
            </div>

        </div>

    <?php endwhile; ?>

    <div class="total-box">
        <h2>Total Pesanan: Rp <?= number_format($order['total_harga'], 0, ",", ".") ?></h2>
    </div>

    <a href="struk.php?id_order=<?= $order['id_order']; ?>" class="btn" target="_blank">🧾 Lihat / Cetak Struk</a>
    <a href="pesanan.php" class="btn btn-outline">← Kembali ke Pesanan</a>

</div>

<?php include "../includes/footer.php"; ?>

</body>
</html>
