<?php
session_start();
include "../koneksi.php";

if (!isset($_SESSION['id_user']) || ($_SESSION['role'] ?? '') !== 'user') {
    header("Location: ../login.php");
    exit;
}

$id_user = (int) $_SESSION['id_user'];

$root    = '../';
$userdir = '';


// =============================
// TAMBAH JUMLAH (dibatasi stok)
// =============================
if (isset($_GET['tambah'])) {

    $id_cart = (int) $_GET['tambah'];

    mysqli_query($conn, "
        UPDATE tb_cart
        SET
            qty = LEAST(qty + 1, (SELECT stok FROM tb_buku WHERE tb_buku.id_buku = tb_cart.id_buku)),
            subtotal = LEAST(qty + 1, (SELECT stok FROM tb_buku WHERE tb_buku.id_buku = tb_cart.id_buku))
                       * (SELECT harga FROM tb_buku WHERE tb_buku.id_buku = tb_cart.id_buku)
        WHERE id_cart = $id_cart
        AND id_user = $id_user
    ");

    header("Location: cart.php");
    exit;
}


// =============================
// KURANGI JUMLAH
// =============================
if (isset($_GET['kurang'])) {

    $id_cart = (int) $_GET['kurang'];

    mysqli_query($conn, "
        UPDATE tb_cart
        SET
            qty = qty - 1,
            subtotal = (qty - 1) * (
                SELECT harga
                FROM tb_buku
                WHERE tb_buku.id_buku = tb_cart.id_buku
            )
        WHERE id_cart = $id_cart
        AND id_user = $id_user
        AND qty > 1
    ");

    header("Location: cart.php");
    exit;
}


// =============================
// HAPUS DARI CART
// =============================
if (isset($_GET['hapus'])) {

    $id_cart = (int) $_GET['hapus'];

    mysqli_query($conn, "
        DELETE FROM tb_cart
        WHERE id_cart = $id_cart
        AND id_user = $id_user
    ");

    header("Location: cart.php");
    exit;
}


// =============================
// AMBIL DATA CART
// =============================
$query = mysqli_query($conn, "
    SELECT
        tb_cart.*,
        tb_buku.judul,
        tb_buku.harga,
        tb_buku.gambar,
        tb_buku.stok
    FROM tb_cart
    JOIN tb_buku
        ON tb_cart.id_buku = tb_buku.id_buku
    WHERE tb_cart.id_user = $id_user
");
?>
<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang - ASBookStore</title>

    <?php include "../includes/navbar.php"; ?>

</head>

<body>

<div class="container-page">

    <h1 class="section-title" style="margin-top:0;">🛒 Keranjang Belanja</h1>

    <?php

    $total       = 0;
    $adaHabis    = false;

    if (mysqli_num_rows($query) == 0) {

    ?>

        <div class="empty-box">
            <h3>Keranjang masih kosong</h3>
            <p>Yuk cari buku yang kamu inginkan!</p>
            <a href="../daftarbuku.php" class="btn" style="margin-top:14px;">Lihat Daftar Buku</a>
        </div>

    <?php

    } else {

        while ($cart = mysqli_fetch_assoc($query)) {

            $total += $cart['subtotal'];
            $stokHabis = (int) $cart['stok'] <= 0;
            $melebihiStok = $cart['qty'] > $cart['stok'];

            if ($stokHabis || $melebihiStok) {
                $adaHabis = true;
            }

    ?>

        <div class="cart-item">

            <img
                src="<?= !empty($cart['gambar']) ? '../' . FOLDER_GAMBAR_BUKU . htmlspecialchars($cart['gambar']) : 'https://via.placeholder.com/90x126?text=Book' ?>"
                alt="<?= htmlspecialchars($cart['judul']) ?>"
            >

            <div class="info">

                <h3><?= htmlspecialchars($cart['judul']) ?></h3>

                <p>Harga: <b>Rp <?= number_format($cart['harga'], 0, ',', '.') ?></b></p>

                <p>Subtotal: <b>Rp <?= number_format($cart['subtotal'], 0, ',', '.') ?></b></p>

                <?php if ($stokHabis): ?>
                    <p style="color:#dc2626;font-weight:bold;">⚠ Stok buku ini sudah habis (Out of Stock). Hapus dari keranjang.</p>
                <?php elseif ($melebihiStok): ?>
                    <p style="color:#dc2626;font-weight:bold;">⚠ Jumlah melebihi stok tersisa (<?= (int) $cart['stok'] ?>).</p>
                <?php endif; ?>

                <div class="jumlah">

                    Jumlah:

                    <a href="cart.php?kurang=<?= $cart['id_cart']; ?>">−</a>

                    <span><b><?= $cart['qty']; ?></b></span>

                    <a href="cart.php?tambah=<?= $cart['id_cart']; ?>" <?= $stokHabis || $cart['qty'] >= $cart['stok'] ? 'style="pointer-events:none;opacity:.4;"' : '' ?>>+</a>

                </div>

                <a
                    href="cart.php?hapus=<?= $cart['id_cart']; ?>"
                    style="color:#dc2626;text-decoration:none;display:inline-block;margin-top:10px;"
                    onclick="return confirm('Hapus buku ini dari keranjang?');"
                >
                    🗑 Hapus
                </a>

            </div>

        </div>

    <?php

        }

    ?>

        <div class="total-box">

            <h2 style="margin-bottom:14px;">Total Belanja: Rp <?= number_format($total, 0, ',', '.') ?></h2>

            <?php if ($adaHabis): ?>
                <p class="alert alert-warning" style="text-align:left;">
                    Beberapa buku di keranjang stoknya habis / melebihi stok tersedia. Hapus atau sesuaikan jumlahnya sebelum checkout.
                </p>
            <?php else: ?>
                <a href="checkout.php" class="btn">Checkout →</a>
            <?php endif; ?>

        </div>

    <?php } ?>

    <a href="../daftarbuku.php" class="btn btn-outline" style="margin-top:10px;display:inline-block;">← Kembali ke Buku</a>

</div>

<?php include "../includes/footer.php"; ?>

</body>

</html>
