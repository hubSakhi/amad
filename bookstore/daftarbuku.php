<?php
session_start();
include "koneksi.php";

$root    = '';
$userdir = 'user/';

// ==============================
// AMBIL PARAMETER PENCARIAN, FILTER & URUTAN
// (kotak search ada di navbar, kirim ?q=...)
// ==============================
$keyword    = (isset($_GET['q']) && is_string($_GET['q'])) ? trim($_GET['q']) : '';
$idKategori = isset($_GET['kategori']) ? (int) $_GET['kategori'] : 0;

// Pilihan urutan (whitelist, jadi aman dipakai langsung di ORDER BY)
$sortOptions = [
    'terbaru'  => ['Terbaru',          'tb_buku.id_buku DESC'],
    'termurah' => ['Harga Terendah',   'tb_buku.harga ASC'],
    'termahal' => ['Harga Tertinggi',  'tb_buku.harga DESC'],
    'judul'    => ['Judul A-Z',        'tb_buku.judul ASC'],
];

$sort = (isset($_GET['sort']) && is_string($_GET['sort']) && isset($sortOptions[$_GET['sort']]))
    ? $_GET['sort']
    : 'terbaru';

// Bikin URL daftarbuku.php dengan parameter yang sedang aktif
function urlDaftarBuku(array $params)
{
    $bersih = [];

    foreach ($params as $k => $v) {
        if ($v === '' || $v === 0 || $v === null) {
            continue;
        }
        if ($k === 'sort' && $v === 'terbaru') {
            continue;
        }
        $bersih[$k] = $v;
    }

    return 'daftarbuku.php' . (count($bersih) > 0 ? '?' . http_build_query($bersih) : '');
}

$kategoriList = [];
$kategoriQuery = mysqli_query($conn, "SELECT * FROM tb_kategori ORDER BY nama_kategori ASC");

while ($k = mysqli_fetch_assoc($kategoriQuery)) {
    $kategoriList[] = $k;
}

$namaKategoriAktif = '';
foreach ($kategoriList as $k) {
    if ((int) $k['id_kategori'] === $idKategori) {
        $namaKategoriAktif = $k['nama_kategori'];
    }
}

// ==============================
// BANGUN QUERY BUKU (dengan keyword & kategori)
// ==============================
$where = [];

if ($keyword !== '') {
    $kw = mysqli_real_escape_string($conn, $keyword);
    $where[] = "(tb_buku.judul LIKE '%$kw%' OR tb_buku.penulis LIKE '%$kw%')";
}

if ($idKategori > 0) {
    $where[] = "tb_buku.id_kategori = $idKategori";
}

$whereSql = count($where) > 0 ? "WHERE " . implode(" AND ", $where) : "";
$orderSql = $sortOptions[$sort][1];

$query = "
    SELECT
        tb_buku.*,
        tb_kategori.nama_kategori
    FROM tb_buku
    LEFT JOIN tb_kategori
        ON tb_buku.id_kategori = tb_kategori.id_kategori
    $whereSql
    ORDER BY $orderSql
";

$data = mysqli_query($conn, $query);

if (!$data) {
    die("Query Error : " . mysqli_error($conn));
}

$jumlahBuku = mysqli_num_rows($data);
$adaFilter  = ($keyword !== '' || $idKategori > 0);
?>
<!DOCTYPE html>
<html lang="id">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Daftar Buku - ASBookStore</title>

<?php include "includes/navbar.php"; ?>

</head>
<body>

<div class="container-page">

    <div class="page-head">
        <h1 class="section-title">Daftar Buku</h1>
        <p>Temukan bacaan favoritmu, urutkan, dan saring sesuai kategori.</p>
    </div>

    <!-- CHIP KATEGORI -->
    <div class="kategori-pills">

        <a href="<?= htmlspecialchars(urlDaftarBuku(['q' => $keyword, 'sort' => $sort])) ?>" class="<?= $idKategori === 0 ? 'active' : '' ?>">Semua</a>

        <?php foreach ($kategoriList as $kat): ?>
            <a
                href="<?= htmlspecialchars(urlDaftarBuku(['q' => $keyword, 'kategori' => (int) $kat['id_kategori'], 'sort' => $sort])) ?>"
                class="<?= $idKategori === (int) $kat['id_kategori'] ? 'active' : '' ?>"
            >
                <?= htmlspecialchars($kat['nama_kategori']) ?>
            </a>
        <?php endforeach; ?>

    </div>

    <!-- INFO HASIL + URUTAN -->
    <div class="list-toolbar">

        <div class="result-info">
            <strong><?= $jumlahBuku ?></strong> buku
            <?php if ($keyword !== ''): ?>
                untuk &ldquo;<strong><?= htmlspecialchars($keyword) ?></strong>&rdquo;
            <?php endif; ?>
            <?php if ($namaKategoriAktif !== ''): ?>
                di kategori <strong><?= htmlspecialchars($namaKategoriAktif) ?></strong>
            <?php endif; ?>
            <?php if ($adaFilter): ?>
                &middot; <a href="daftarbuku.php" style="color:var(--primary);font-weight:600;text-decoration:none;">Hapus filter</a>
            <?php endif; ?>
        </div>

        <form method="GET" action="daftarbuku.php" class="sort-form">
            <?php if ($keyword !== ''): ?>
                <input type="hidden" name="q" value="<?= htmlspecialchars($keyword) ?>">
            <?php endif; ?>
            <?php if ($idKategori > 0): ?>
                <input type="hidden" name="kategori" value="<?= $idKategori ?>">
            <?php endif; ?>

            <label for="sortSelect">Urutkan</label>
            <select name="sort" id="sortSelect" onchange="this.form.submit()">
                <?php foreach ($sortOptions as $key => $opt): ?>
                    <option value="<?= $key ?>" <?= $sort === $key ? 'selected' : '' ?>><?= htmlspecialchars($opt[0]) ?></option>
                <?php endforeach; ?>
            </select>
        </form>

    </div>

    <?php if ($jumlahBuku > 0): ?>

        <div class="book-grid">

            <?php while ($buku = mysqli_fetch_assoc($data)):

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
                        <?php if (!empty($buku['nama_kategori'])): ?>
                            <span class="kategori-tag"><?= htmlspecialchars($buku['nama_kategori']) ?></span>
                        <?php endif; ?>

                        <h3><?= htmlspecialchars($buku['judul']) ?></h3>
                        <div class="author"><?= htmlspecialchars($buku['penulis']) ?></div>
                        <div class="price">Rp <?= number_format($buku['harga'], 0, ',', '.') ?></div>
                    </div>

                </a>

            <?php endwhile; ?>

        </div>

    <?php else: ?>

        <div class="empty-box">
            <h3>Buku tidak ditemukan</h3>
            <p>Coba kata kunci atau kategori lain.</p>
            <a href="daftarbuku.php" class="btn btn-outline" style="margin-top:10px;">Lihat Semua Buku</a>
        </div>

    <?php endif; ?>

</div>

<?php include "includes/footer.php"; ?>

</body>
</html>
