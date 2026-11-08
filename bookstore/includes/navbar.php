<?php
/**
 * NAVBAR BERSAMA (guest & user)
 * ------------------------------------------------------------
 * Dipakai oleh SEMUA halaman publik & area user supaya menu
 * navigasi selalu sama persis di setiap halaman.
 *
 * Isi navbar: Logo | Search bar | Home, Buku, About, Contact |
 *             (guest) Login & Register
 *             (user)  Pesanan Saya, Cart, Logout
 *
 * Sebelum include file ini, halaman WAJIB (opsional tapi
 * disarankan) mengatur 2 variabel path berikut:
 *
 *   $root    = path menuju folder root project.
 *              '' untuk halaman di root, '../' untuk halaman di /user/.
 *   $userdir = path menuju folder /user/.
 *              'user/' untuk halaman di root, '' untuk halaman di /user/.
 *
 * session_start() dan koneksi.php harus sudah dipanggil sebelumnya.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$root    = $root    ?? '';
$userdir = $userdir ?? 'user/';

$isLoggedInUser = isset($_SESSION['id_user']) && ($_SESSION['role'] ?? '') === 'user';

// Hitung jumlah item di keranjang (kalau login & koneksi tersedia)
$jumlahCart = 0;

if ($isLoggedInUser && isset($conn)) {
    $idUserNav = (int) $_SESSION['id_user'];
    $cekCart   = mysqli_query($conn, "SELECT COALESCE(SUM(qty),0) AS total FROM tb_cart WHERE id_user = $idUserNav");

    if ($cekCart) {
        $jumlahCart = (int) mysqli_fetch_assoc($cekCart)['total'];
    }
}

$currentPage = basename($_SERVER['SCRIPT_NAME']);

// Versi CSS = waktu terakhir style.css diubah (biar browser tidak memakai cache lama)
$cssVer = @filemtime(__DIR__ . '/../css/style.css') ?: 1;

// Kata kunci yang sedang dicari (supaya tetap tampil di kotak search)
$navKeyword = (isset($_GET['q']) && is_string($_GET['q'])) ? trim($_GET['q']) : '';

// Nama user untuk tombol Logout
$navNama = $isLoggedInUser ? (string) ($_SESSION['nama'] ?? 'User') : '';
?>
<link href="<?= $root ?>css/style.css?v=<?= $cssVer ?>" rel="stylesheet">

<nav class="site-nav">
    <div class="nav-inner">

        <a href="<?= $root ?>index.php" class="brand"><svg class="brand-logo" width="30" height="30" viewBox="0 0 32 32" fill="none" aria-hidden="true"><path d="M3.5 6.5c4.6-1.5 8.7-.4 12.5 3.1v18c-3.8-3.5-7.9-4.6-12.5-3.1v-18Z" stroke="currentColor" stroke-width="2.2" stroke-linejoin="round"/><path d="M28.5 6.5C23.9 5 19.8 6.1 16 9.6v18c3.8-3.5 7.9-4.6 12.5-3.1v-18Z" stroke="currentColor" stroke-width="2.2" stroke-linejoin="round"/><path d="M16 9.6v18" stroke="currentColor" stroke-width="1.8"/></svg><span>ASBookStore</span></a>

        <!-- SEARCH BAR: satu baris dengan menu, ada di semua halaman -->
        <form class="nav-search" action="<?= $root ?>daftarbuku.php" method="get" role="search">
            <input
                type="search"
                name="q"
                value="<?= htmlspecialchars($navKeyword) ?>"
                placeholder="Cari buku atau penulis..."
                aria-label="Cari buku"
                autocomplete="off"
            >
            <button type="submit" aria-label="Cari">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
            </button>
        </form>

        <button class="nav-toggle" type="button" aria-label="Buka menu" aria-controls="siteMenu" aria-expanded="false" onclick="bsToggleMenu(this)">☰</button>

        <div class="nav-menu" id="siteMenu">

            <a href="<?= $root ?>index.php" class="nav-link <?= $currentPage === 'index.php' ? 'active' : '' ?>">Home</a>
            <a href="<?= $root ?>daftarbuku.php" class="nav-link <?= in_array($currentPage, ['daftarbuku.php', 'detailbuku.php']) ? 'active' : '' ?>">Buku</a>
            <a href="<?= $root ?>about.php" class="nav-link <?= $currentPage === 'about.php' ? 'active' : '' ?>">About</a>
            <a href="<?= $root ?>contact.php" class="nav-link <?= $currentPage === 'contact.php' ? 'active' : '' ?>">Contact</a>

            <?php if ($isLoggedInUser): ?>

                <a href="<?= $userdir ?>pesanan.php" class="nav-link <?= in_array($currentPage, ['pesanan.php', 'detailpesanan.php']) ? 'active' : '' ?>">Pesanan Saya</a>

                <a href="<?= $userdir ?>cart.php" class="nav-link <?= $currentPage === 'cart.php' ? 'active' : '' ?>">
                    Cart
                    <span class="cart-badge" id="navCartCount" <?= $jumlahCart > 0 ? '' : 'hidden' ?>><?= $jumlahCart ?></span>
                </a>

                <a href="<?= $root ?>logout.php" class="nav-link">Logout (<span class="u-name"><?= htmlspecialchars($navNama) ?></span>)</a>

            <?php else: ?>

                <a href="<?= $root ?>login.php" class="nav-btn">Login</a>
                <a href="<?= $root ?>register.php" class="nav-btn">Register</a>

            <?php endif; ?>

        </div>

    </div>
</nav>

<div id="bs-toast"></div>

<script>
function bsToast(msg) {
    var el = document.getElementById('bs-toast');
    el.textContent = msg;
    el.classList.remove('show');
    void el.offsetWidth; // restart animasi
    el.classList.add('show');
}

function bsToggleMenu(btn) {
    var open = document.getElementById('siteMenu').classList.toggle('open');
    btn.setAttribute('aria-expanded', open ? 'true' : 'false');
}
</script>
