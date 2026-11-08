<?php
session_start();
include "koneksi.php";

$root    = '';
$userdir = 'user/';

// Kategori untuk ditampilkan di beranda
$kategoriQuery = mysqli_query($conn, "SELECT * FROM tb_kategori ORDER BY nama_kategori ASC LIMIT 6");

// 8 buku terbaru
$bukuQuery = mysqli_query($conn, "SELECT * FROM tb_buku ORDER BY id_buku DESC LIMIT 25");

$bukuList = [];
while ($row = mysqli_fetch_assoc($bukuQuery)) {
    $bukuList[] = $row;
}

// 5 cover buku (yang punya gambar) untuk hiasan hero
$coverHero = [];
foreach ($bukuList as $b) {
    if (!empty($b['gambar'])) {
        $coverHero[] = $b;
    }
    if (count($coverHero) === 5) {
        break;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>ASBookStore - Toko Buku Online</title>

<?php include "includes/navbar.php"; ?>

</head>
<body>

<div class="container-page">

    <!-- HERO -->
    <section class="hero">

        <div>
            <span class="hero-eyebrow">Toko Buku Online</span>
            <h1>Selamat Datang di ASBookStore</h1>
            <p>Temukan ribuan koleksi buku terbaik dengan harga terjangkau.</p>
            <a href="daftarbuku.php" class="btn btn-warning">Mulai Belanja</a>
        </div>

        <?php if (count($coverHero) > 0): ?>
            <div class="hero-covers" aria-hidden="true">
                <?php foreach ($coverHero as $c): ?>
                    <img src="<?= htmlspecialchars(FOLDER_GAMBAR_BUKU . $c['gambar']) ?>" alt="">
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </section>

    <!-- KEUNGGULAN -->
    <section class="features">

        <div class="feature">
            <span class="ico">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
            </span>
            <div>
                <h4>Cari dengan Mudah</h4>
                <p>Temukan buku lewat judul, penulis, atau kategori.</p>
            </div>
        </div>

        <div class="feature">
            <span class="ico">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg>
            </span>
            <div>
                <h4>Checkout Praktis</h4>
                <p>Transfer, lalu unggah bukti pembayaran langsung di website.</p>
            </div>
        </div>

        <div class="feature">
            <span class="ico">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 8 12 3 3 8v8l9 5 9-5z"/><path d="m3 8 9 5 9-5M12 13v8"/></svg>
            </span>
            <div>
                <h4>Pantau Pesanan</h4>
                <p>Cek status pesananmu kapan saja di halaman Pesanan Saya.</p>
            </div>
        </div>

    </section>

    <?php if (mysqli_num_rows($kategoriQuery) > 0): ?>

        <h2 class="section-title">Kategori Populer</h2>

        <div class="kategori-pills">
            <?php while ($kat = mysqli_fetch_assoc($kategoriQuery)): ?>
                <a href="daftarbuku.php?kategori=<?= (int) $kat['id_kategori'] ?>">
                    <?= htmlspecialchars($kat['nama_kategori']) ?>
                </a>
            <?php endwhile; ?>
        </div>

    <?php endif; ?>

    <div class="section-head">
        <h2 class="section-title">Buku Terbaru</h2>
        <a href="daftarbuku.php" class="section-link">Lihat semua &rarr;</a>
    </div>

    <?php if (count($bukuList) > 0): ?>

        <div class="book-grid">

            <?php foreach ($bukuList as $buku):

                $gambar = !empty($buku['gambar'])
                    ? FOLDER_GAMBAR_BUKU . $buku['gambar']
                    : "https://via.placeholder.com/300x400?text=No+Image";

                $habis = (int) $buku['stok'] <= 0;
            ?>

                <a href="detailbuku.php?id=<?= (int) $buku['id_buku'] ?>" class="book-card<?= $habis ? ' is-soldout' : '' ?>">

                    <div class="thumb">
                        <img src="<?= htmlspecialchars($gambar) ?>" alt="<?= htmlspecialchars($buku['judul']) ?>" loading="lazy">

                        <?php if ($habis): ?>
                            <span class="stock-flag">Out of Stock</span>
                        <?php endif; ?>
                    </div>

                    <div class="body">
                        <h3><?= htmlspecialchars($buku['judul']) ?></h3>
                        <div class="author"><?= htmlspecialchars($buku['penulis']) ?></div>
                        <div class="price">Rp <?= number_format($buku['harga'], 0, ',', '.') ?></div>
                    </div>

                </a>

            <?php endforeach; ?>

        </div>

        <div style="text-align:center;margin:36px 0 0;">
            <a href="daftarbuku.php" class="btn btn-outline">Lihat Semua Buku</a>
        </div>

    <?php else: ?>

        <div class="empty-box">Belum ada data buku.</div>

    <?php endif; ?>

</div>

<?php include "includes/footer.php"; ?>

</body>
</html>
