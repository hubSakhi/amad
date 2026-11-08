<?php
session_start();
include "../koneksi.php";
include "../includes/pembayaran.php";

if (!isset($_SESSION['login']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../login.php");
    exit;
}

/* ---------- helper kecil ---------- */
function ambilBaris($conn, $sql)
{
    $rows = [];
    $q = mysqli_query($conn, $sql);
    if ($q) {
        while ($r = mysqli_fetch_assoc($q)) {
            $rows[] = $r;
        }
    }
    return $rows;
}

function ambilAngka($conn, $sql)
{
    $rows = ambilBaris($conn, $sql);
    return $rows ? (float) array_values($rows[0])[0] : 0;
}

function rupiah($n)
{
    return 'Rp ' . number_format($n, 0, ',', '.');
}

function rupiahSingkat($n)
{
    if ($n >= 1000000) return rtrim(rtrim(number_format($n / 1000000, 1, ',', ''), '0'), ',') . ' jt';
    if ($n >= 1000)    return round($n / 1000) . ' rb';
    return (string) round($n);
}

/* ---------- statistik utama ---------- */
$buku      = (int) ambilAngka($conn, "SELECT COUNT(*) FROM tb_buku");
$kategori  = (int) ambilAngka($conn, "SELECT COUNT(*) FROM tb_kategori");
$user      = (int) ambilAngka($conn, "SELECT COUNT(*) FROM tb_user");
$pesanan   = (int) ambilAngka($conn, "SELECT COUNT(*) FROM tb_order");
$habisStok = (int) ambilAngka($conn, "SELECT COUNT(*) FROM tb_buku WHERE stok <= 0");
$pending   = (int) ambilAngka($conn, "SELECT COUNT(*) FROM tb_order WHERE status = 'Pending'");

$pendapatan      = ambilAngka($conn, "SELECT COALESCE(SUM(total_harga),0) FROM tb_order WHERE status <> 'Cancelled'");
$pendapatanBulan = ambilAngka($conn, "SELECT COALESCE(SUM(total_harga),0) FROM tb_order WHERE status <> 'Cancelled' AND YEAR(tanggal_order)=YEAR(CURDATE()) AND MONTH(tanggal_order)=MONTH(CURDATE())");
$bukuTerjual     = (int) ambilAngka($conn, "SELECT COALESCE(SUM(d.qty),0) FROM tb_order_detail d JOIN tb_order o ON d.id_order=o.id_order WHERE o.status <> 'Cancelled'");

/* ---------- penjualan 7 hari terakhir ---------- */
$hariNama = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
$perHari  = [];
for ($i = 6; $i >= 0; $i--) {
    $tgl = date('Y-m-d', strtotime("-$i day"));
    $perHari[$tgl] = ['label' => $hariNama[(int) date('w', strtotime($tgl))], 'total' => 0, 'jumlah' => 0];
}
foreach (ambilBaris($conn, "
    SELECT DATE(tanggal_order) AS tgl, SUM(total_harga) AS total, COUNT(*) AS jumlah
    FROM tb_order
    WHERE status <> 'Cancelled' AND tanggal_order >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
    GROUP BY DATE(tanggal_order)
") as $r) {
    if (isset($perHari[$r['tgl']])) {
        $perHari[$r['tgl']]['total']  = (float) $r['total'];
        $perHari[$r['tgl']]['jumlah'] = (int) $r['jumlah'];
    }
}
$maxHari   = max(1, max(array_column($perHari, 'total')));
$total7    = array_sum(array_column($perHari, 'total'));
$pesanan7  = array_sum(array_column($perHari, 'jumlah'));

/* ---------- pesanan per status ---------- */
$statusList = ['Pending', 'Processing', 'Shipping', 'Completed', 'Cancelled'];
$perStatus  = array_fill_keys($statusList, 0);
foreach (ambilBaris($conn, "SELECT status, COUNT(*) AS n FROM tb_order GROUP BY status") as $r) {
    if (isset($perStatus[$r['status']])) {
        $perStatus[$r['status']] = (int) $r['n'];
    }
}

/* ---------- pesanan per metode pembayaran ---------- */
$perMetode = array_fill_keys(array_keys($METODE_BAYAR), 0);
foreach (ambilBaris($conn, "SELECT metode_pembayaran AS m, COUNT(*) AS n FROM tb_order GROUP BY metode_pembayaran") as $r) {
    if (isset($perMetode[$r['m']])) {
        $perMetode[$r['m']] = (int) $r['n'];
    }
}

/* ---------- daftar-daftar ---------- */
$pesananTerbaru = ambilBaris($conn, "
    SELECT o.*, u.nama
    FROM tb_order o
    JOIN tb_user u ON o.id_user = u.id_user
    ORDER BY o.id_order DESC
    LIMIT 6
");

$terlaris = ambilBaris($conn, "
    SELECT b.judul, b.gambar, SUM(d.qty) AS terjual
    FROM tb_order_detail d
    JOIN tb_order o ON d.id_order = o.id_order
    JOIN tb_buku b  ON d.id_buku = b.id_buku
    WHERE o.status <> 'Cancelled'
    GROUP BY b.id_buku, b.judul, b.gambar
    ORDER BY terjual DESC
    LIMIT 5
");

$stokMenipis = ambilBaris($conn, "
    SELECT judul, stok FROM tb_buku
    WHERE stok <= 5
    ORDER BY stok ASC, judul ASC
    LIMIT 6
");

/* ---------- sapaan ---------- */
$jam = (int) date('G');
$sapaan = $jam < 11 ? 'Selamat pagi' : ($jam < 15 ? 'Selamat siang' : ($jam < 18 ? 'Selamat sore' : 'Selamat malam'));
$bulanNama = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
$hariPanjang = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
$tanggalHariIni = $hariPanjang[(int) date('w')] . ', ' . date('j') . ' ' . $bulanNama[(int) date('n')] . ' ' . date('Y');
?>
<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard Admin - ASBookStore</title>

<?php include "includes/navbar.php"; ?>

</head>

<body>

<div class="admin-content">

    <!-- ===== HEADER ===== -->
    <div class="dash-hero">
        <div>
            <div class="dash-date">📅 <?= $tanggalHariIni ?></div>
            <h1><?= $sapaan ?>, <?= htmlspecialchars($_SESSION['nama']); ?> 👋</h1>
            <p>
                <?php if ($pending > 0): ?>
                    Ada <b><?= $pending ?> pesanan</b> yang menunggu diproses hari ini.
                <?php else: ?>
                    Semua pesanan sudah ditangani. Kerja bagus!
                <?php endif; ?>
            </p>
        </div>
        <div class="dash-hero-actions">
            <a href="pesanan.php" class="btn btn-light">🧾 Lihat Pesanan</a>
            <a href="buku.php" class="btn btn-ghost">📚 Kelola Buku</a>
        </div>
    </div>

    <!-- ===== KARTU STATISTIK ===== -->
    <div class="dash-stats">

        <div class="dash-stat s-green">
            <div class="ico">💰</div>
            <div>
                <div class="lbl">Total Pendapatan</div>
                <div class="val"><?= rupiah($pendapatan) ?></div>
                <div class="sub">Bulan ini: <?= rupiah($pendapatanBulan) ?></div>
            </div>
        </div>

        <div class="dash-stat s-indigo">
            <div class="ico">🧾</div>
            <div>
                <div class="lbl">Total Pesanan</div>
                <div class="val"><?= $pesanan ?></div>
                <div class="sub"><?= $bukuTerjual ?> buku terjual</div>
            </div>
        </div>

        <div class="dash-stat s-amber">
            <div class="ico">⏳</div>
            <div>
                <div class="lbl">Menunggu Diproses</div>
                <div class="val"><?= $pending ?></div>
                <div class="sub"><a href="pesanan.php">Kelola pesanan →</a></div>
            </div>
        </div>

        <div class="dash-stat s-red">
            <div class="ico">📕</div>
            <div>
                <div class="lbl">Stok Habis</div>
                <div class="val"><?= $habisStok ?></div>
                <div class="sub"><a href="buku.php">Kelola buku →</a></div>
            </div>
        </div>

    </div>

    <div class="dash-mini">
        <a href="buku.php" class="dash-chip">📚 <b><?= $buku ?></b> Buku</a>
        <a href="kategori.php" class="dash-chip">🏷️ <b><?= $kategori ?></b> Kategori</a>
        <a href="user.php" class="dash-chip">👤 <b><?= $user ?></b> User</a>
    </div>

    <!-- ===== GRAFIK + STATUS ===== -->
    <div class="dash-row dash-row-main">

        <div class="dash-card">
            <div class="dash-card-head">
                <div>
                    <h3>Penjualan 7 Hari Terakhir</h3>
                    <small><?= rupiah($total7) ?> dari <?= $pesanan7 ?> pesanan (tanpa yang dibatalkan)</small>
                </div>
            </div>

            <div class="dash-chart">
                <?php foreach ($perHari as $tgl => $d):
                    $tinggi = $d['total'] > 0 ? max(6, round($d['total'] / $maxHari * 100)) : 0;
                    $hariIni = ($tgl === date('Y-m-d'));
                ?>
                    <div class="bar-col <?= $hariIni ? 'today' : '' ?>" title="<?= date('d-m-Y', strtotime($tgl)) ?>: <?= rupiah($d['total']) ?> (<?= $d['jumlah'] ?> pesanan)">
                        <div class="bar-val"><?= $d['total'] > 0 ? rupiahSingkat($d['total']) : '' ?></div>
                        <div class="bar-track">
                            <div class="bar-fill" style="height:<?= $tinggi ?>%"></div>
                        </div>
                        <div class="bar-lbl"><?= $hariIni ? 'Hari ini' : $d['label'] ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="dash-card">
            <div class="dash-card-head"><h3>Status Pesanan</h3></div>

            <?php foreach ($perStatus as $st => $n):
                $persen = $pesanan > 0 ? round($n / $pesanan * 100) : 0;
            ?>
                <div class="prog">
                    <div class="prog-top">
                        <span><span class="dot status-<?= $st ?>"></span><?= $st ?></span>
                        <b><?= $n ?></b>
                    </div>
                    <div class="prog-bar"><div class="status-<?= $st ?>" style="width:<?= $persen ?>%"></div></div>
                </div>
            <?php endforeach; ?>

            <div class="dash-sep"></div>

            <div class="dash-card-head" style="margin-bottom:10px;"><h3 style="font-size:15px;">Metode Pembayaran</h3></div>
            <div class="metode-row">
                <?php foreach ($METODE_BAYAR as $kode => $m): ?>
                    <div class="metode-item">
                        <div class="mi-ico"><?= $m['icon'] ?></div>
                        <div class="mi-n"><?= $perMetode[$kode] ?></div>
                        <div class="mi-l"><?= $kode ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

    </div>

    <!-- ===== PESANAN TERBARU ===== -->
    <div class="dash-card" style="margin-top:20px;">
        <div class="dash-card-head">
            <h3>Pesanan Terbaru</h3>
            <a href="pesanan.php" class="dash-link">Lihat semua →</a>
        </div>

        <div class="dash-table-wrap">
            <table class="table-admin dash-table">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Pelanggan</th>
                        <th>Tanggal</th>
                        <th>Total</th>
                        <th>Metode</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($pesananTerbaru as $o): ?>
                    <tr>
                        <td><b>#<?= (int) $o['id_order'] ?></b></td>
                        <td><?= htmlspecialchars($o['nama']) ?></td>
                        <td><?= date('d M Y, H:i', strtotime($o['tanggal_order'])) ?></td>
                        <td><?= rupiah($o['total_harga']) ?></td>
                        <td><span class="badge-metode <?= $o['metode_pembayaran'] === 'COD' ? 'badge-cod' : '' ?>"><?= htmlspecialchars($o['metode_pembayaran']) ?></span></td>
                        <td><span class="status-pill status-<?= htmlspecialchars($o['status']) ?>"><?= htmlspecialchars($o['status']) ?></span></td>
                        <td><a href="detailpesanan.php?id_order=<?= (int) $o['id_order'] ?>" class="btn btn-sm">Detail</a></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$pesananTerbaru): ?>
                    <tr><td colspan="7" class="dash-empty">Belum ada pesanan masuk.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ===== TERLARIS + STOK ===== -->
    <div class="dash-row dash-row-half">

        <div class="dash-card">
            <div class="dash-card-head"><h3>🏆 Buku Terlaris</h3></div>

            <?php foreach ($terlaris as $i => $b): ?>
                <div class="dash-item">
                    <span class="rank rank-<?= $i + 1 ?>"><?= $i + 1 ?></span>
                    <?php if (!empty($b['gambar'])): ?>
                        <img src="../<?= FOLDER_GAMBAR_BUKU . htmlspecialchars($b['gambar']) ?>" class="thumb-sm" alt="">
                    <?php endif; ?>
                    <div class="di-info">
                        <div class="di-title"><?= htmlspecialchars($b['judul']) ?></div>
                        <div class="di-sub"><?= (int) $b['terjual'] ?> terjual</div>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php if (!$terlaris): ?>
                <div class="dash-empty">Belum ada data penjualan.</div>
            <?php endif; ?>
        </div>

        <div class="dash-card">
            <div class="dash-card-head">
                <h3>⚠️ Stok Menipis</h3>
                <a href="buku.php" class="dash-link">Kelola →</a>
            </div>

            <?php foreach ($stokMenipis as $b): ?>
                <div class="dash-item">
                    <div class="di-info">
                        <div class="di-title"><?= htmlspecialchars($b['judul']) ?></div>
                    </div>
                    <?php if ((int) $b['stok'] <= 0): ?>
                        <span class="stok-pill stok-habis">Habis</span>
                    <?php else: ?>
                        <span class="stok-pill stok-tipis">Sisa <?= (int) $b['stok'] ?></span>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            <?php if (!$stokMenipis): ?>
                <div class="dash-empty">Semua stok aman 👍</div>
            <?php endif; ?>
        </div>

    </div>

</div>

</body>

</html>
