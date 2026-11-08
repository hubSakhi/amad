<?php
/**
 * SIDEBAR ADMIN BERSAMA
 * Dipakai oleh semua halaman di /admin/ supaya menu selalu konsisten.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$currentPage = basename($_SERVER['SCRIPT_NAME']);

// Badge notifikasi: jumlah pesanan Pending yang sudah punya bukti pembayaran
// dan menunggu diproses admin.
$badgePending = 0;

if (isset($conn)) {
    $q = mysqli_query($conn, "
        SELECT COUNT(*) AS total
        FROM tb_order
        WHERE status = 'Pending'
        AND bukti_pembayaran IS NOT NULL
        AND bukti_pembayaran != ''
    ");

    if ($q) {
        $badgePending = (int) mysqli_fetch_assoc($q)['total'];
    }
}
?>
<!-- Halaman admin ini mandiri: TIDAK memuat apa pun dari CDN internet
     (dulu pakai Bootstrap + FontAwesome, sekarang murni css/style.css sendiri) -->
<link href="../css/style.css?v=<?= @filemtime(__DIR__ . '/../../css/style.css') ?: 1 ?>" rel="stylesheet">

<button class="admin-toggle" type="button" aria-label="Buka menu admin" aria-controls="adminSidebar" aria-expanded="false" onclick="document.getElementById('adminSidebar').classList.toggle('open')">☰ Menu</button>

<div class="admin-sidebar" id="adminSidebar">

    <h3><svg class="brand-logo" width="30" height="30" viewBox="0 0 32 32" fill="none" aria-hidden="true"><path d="M3.5 6.5c4.6-1.5 8.7-.4 12.5 3.1v18c-3.8-3.5-7.9-4.6-12.5-3.1v-18Z" stroke="currentColor" stroke-width="2.2" stroke-linejoin="round"/><path d="M28.5 6.5C23.9 5 19.8 6.1 16 9.6v18c3.8-3.5 7.9-4.6 12.5-3.1v-18Z" stroke="currentColor" stroke-width="2.2" stroke-linejoin="round"/><path d="M16 9.6v18" stroke="currentColor" stroke-width="1.8"/></svg> ASBookStore<br><small style="font-weight:normal;font-size:13px;">Admin Panel</small></h3>

    <div class="menu-label">Menu Utama</div>

    <a href="dashboard.php" class="<?= $currentPage === 'dashboard.php' ? 'active' : '' ?>">
        🏠 Dashboard
    </a>

    <div class="menu-label">Data Toko</div>

    <a href="kategori.php" class="<?= $currentPage === 'kategori.php' ? 'active' : '' ?>">
        🏷️ Kategori
    </a>

    <a href="buku.php" class="<?= $currentPage === 'buku.php' ? 'active' : '' ?>">
        📚 Buku
    </a>

    <a href="user.php" class="<?= $currentPage === 'user.php' ? 'active' : '' ?>">
        👤 User
    </a>

    <div class="menu-label">Transaksi</div>

    <a href="pesanan.php" class="<?= in_array($currentPage, ['pesanan.php', 'detailpesanan.php']) ? 'active' : '' ?>">
        🧾 Pesanan
        <?php if ($badgePending > 0): ?>
            <span class="cart-badge"><?= $badgePending ?></span>
        <?php endif; ?>
    </a>

    <div class="admin-user">
        <div class="avatar"><?= strtoupper(substr($_SESSION['nama'] ?? 'A', 0, 1)) ?></div>
        <div class="au-info">
            <b><?= htmlspecialchars($_SESSION['nama'] ?? 'Admin') ?></b>
            <small>Administrator</small>
        </div>
        <a href="../logout.php" class="au-logout" title="Logout" aria-label="Logout">🚪</a>
    </div>

</div>
