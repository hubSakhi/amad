<?php
session_start();
include "koneksi.php";

$root    = '';
$userdir = 'user/';

$idBuku = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($idBuku <= 0) {
    header("Location: daftarbuku.php");
    exit;
}

$query = mysqli_query($conn, "
    SELECT
        tb_buku.*,
        tb_kategori.nama_kategori
    FROM tb_buku
    LEFT JOIN tb_kategori
        ON tb_buku.id_kategori = tb_kategori.id_kategori
    WHERE tb_buku.id_buku = $idBuku
");

if (!$query || mysqli_num_rows($query) === 0) {
    echo "<script>alert('Buku tidak ditemukan.'); window.location='daftarbuku.php';</script>";
    exit;
}

$buku  = mysqli_fetch_assoc($query);
$habis = (int) $buku['stok'] <= 0;

$gambar = !empty($buku['gambar'])
    ? FOLDER_GAMBAR_BUKU . $buku['gambar']
    : "https://via.placeholder.com/400x560?text=No+Image";

$isLoggedInUser = isset($_SESSION['id_user']) && ($_SESSION['role'] ?? '') === 'user';
?>
<!DOCTYPE html>
<html lang="id">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($buku['judul']) ?> - ASBookStore</title>

<?php include "includes/navbar.php"; ?>

</head>
<body>

<div class="container-page">

    <a href="daftarbuku.php" class="back-link">&larr; Kembali ke Daftar Buku</a>

    <div class="detail-wrap">

        <div class="detail-cover">
            <img src="<?= htmlspecialchars($gambar) ?>" alt="<?= htmlspecialchars($buku['judul']) ?>">
        </div>

        <div class="detail-info">

            <?php if (!empty($buku['nama_kategori'])): ?>
                <span class="kategori-tag"><?= htmlspecialchars($buku['nama_kategori']) ?></span>
            <?php endif; ?>

            <h1><?= htmlspecialchars($buku['judul']) ?></h1>
            <div class="meta">oleh <?= htmlspecialchars($buku['penulis']) ?></div>

            <table>
                <tr>
                    <td>Penerbit</td>
                    <td>: <?= htmlspecialchars($buku['penerbit'] ?: '-') ?></td>
                </tr>
                <tr>
                    <td>Tahun Terbit</td>
                    <td>: <?= htmlspecialchars($buku['tahun_terbit'] ?: '-') ?></td>
                </tr>
                <tr>
                    <td>Stok</td>
                    <td>
                        :
                        <?php if ($habis): ?>
                            <span class="status-pill status-Cancelled">Out of Stock</span>
                        <?php else: ?>
                            <?= (int) $buku['stok'] ?> buku tersedia
                        <?php endif; ?>
                    </td>
                </tr>
            </table>

            <?php if (!empty($buku['deskripsi'])): ?>
                <p style="line-height:1.7;color:#444;">
                    <?= nl2br(htmlspecialchars($buku['deskripsi'])) ?>
                </p>
            <?php endif; ?>

            <div class="detail-price">Rp <?= number_format($buku['harga'], 0, ',', '.') ?></div>

            <?php if ($habis): ?>

                <button class="btn btn-block" disabled>Stok Habis</button>

            <?php elseif (!$isLoggedInUser): ?>

                <a href="login.php" class="btn btn-block">Login untuk Membeli</a>

            <?php else: ?>

                <div class="qty-box">
                    <button type="button" onclick="ubahQty(-1)">−</button>
                    <input type="number" id="qtyInput" value="1" min="1" max="<?= (int) $buku['stok'] ?>" readonly>
                    <button type="button" onclick="ubahQty(1)">+</button>
                </div>

                <button class="btn btn-block" id="btnAddCart" onclick="tambahKeKeranjang(<?= (int) $buku['id_buku'] ?>)">
                    🛒 Add to Cart
                </button>

            <?php endif; ?>

        </div>

    </div>

</div>

<?php include "includes/footer.php"; ?>

<script>
var stokMax = <?= (int) $buku['stok'] ?>;

function ubahQty(delta) {
    var input = document.getElementById('qtyInput');
    var val = parseInt(input.value || '1', 10) + delta;
    if (val < 1) val = 1;
    if (val > stokMax) val = stokMax;
    input.value = val;
}

function tambahKeKeranjang(idBuku) {
    var qty = parseInt(document.getElementById('qtyInput').value || '1', 10);
    var btn = document.getElementById('btnAddCart');

    btn.disabled = true;
    btn.textContent = 'Menambahkan...';

    var formData = new FormData();
    formData.append('id_buku', idBuku);
    formData.append('qty', qty);

    fetch('<?= $userdir ?>cart_add.php', {
        method: 'POST',
        body: formData
    })
    .then(function(res){ return res.json(); })
    .then(function(res){

        btn.disabled = false;
        btn.textContent = '🛒 Add to Cart';

        if (res.success) {
            bsToast(res.message || 'Berhasil ditambahkan ke keranjang');

            var badge = document.getElementById('navCartCount');
            if (badge) {
                badge.textContent = res.cart_count;
                badge.hidden = !(res.cart_count > 0);
            }
        } else {
            if (res.redirect) {
                window.location = res.redirect;
            } else {
                bsToast(res.message || 'Gagal menambahkan ke keranjang');
            }
        }
    })
    .catch(function(){
        btn.disabled = false;
        btn.textContent = '🛒 Add to Cart';
        bsToast('Terjadi kesalahan, coba lagi.');
    });
}
</script>

</body>
</html>
