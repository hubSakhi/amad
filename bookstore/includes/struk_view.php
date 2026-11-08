<?php
/**
 * TAMPILAN STRUK (dipakai user/struk.php dan admin/struk.php)
 * Variabel yang harus tersedia: $order (array), $detail_query (mysqli result),
 * $urlKembali (string), $rootPath ('../' dari folder user/ atau admin/)
 */

$metode = $order['metode_pembayaran'] ?? 'Transfer';
$status = $order['status'];

// Status pembayaran yang ditampilkan di struk
if ($status === 'Cancelled') {
    $statusBayar = 'DIBATALKAN';
} elseif ($metode === 'COD') {
    $statusBayar = ($status === 'Completed') ? 'LUNAS' : 'BAYAR SAAT BARANG TIBA';
} else {
    $statusBayar = ($status === 'Pending') ? 'MENUNGGU VERIFIKASI' : 'LUNAS';
}

$noStruk = 'INV-' . date('Ymd', strtotime($order['tanggal_order'])) . '-' . str_pad($order['id_order'], 5, '0', STR_PAD_LEFT);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Struk <?= htmlspecialchars($noStruk) ?> - ASBookStore</title>
<style>
    *{ box-sizing:border-box; }
    body{ margin:0; background:#f3f4f6; font-family:'Courier New',monospace; color:#111; }
    .aksi{ max-width:380px; margin:20px auto 0; display:flex; gap:8px; padding:0 8px; }
    .aksi a, .aksi button{
        flex:1; text-align:center; padding:10px; border-radius:8px; border:1px solid #4f46e5;
        font:600 14px system-ui,Arial,sans-serif; cursor:pointer; text-decoration:none;
    }
    .aksi button{ background:#4f46e5; color:#fff; }
    .aksi a{ background:#fff; color:#4f46e5; }
    .struk{
        max-width:380px; margin:14px auto 30px; background:#fff; padding:22px 20px;
        box-shadow:0 4px 14px rgba(0,0,0,.1); font-size:13px; line-height:1.55;
    }
    .center{ text-align:center; }
    .toko{ font-size:18px; font-weight:bold; letter-spacing:1px; }
    .garis{ border:none; border-top:1px dashed #555; margin:10px 0; }
    .baris{ display:flex; justify-content:space-between; gap:10px; }
    .item{ margin-bottom:6px; }
    .item .nama{ font-weight:bold; word-break:break-word; }
    .total{ font-size:15px; font-weight:bold; }
    .stempel{
        margin:12px 0 4px; padding:6px; text-align:center; font-weight:bold;
        border:2px solid #111; letter-spacing:1px;
    }
    @media print{
        body{ background:#fff; }
        .aksi{ display:none; }
        .struk{ box-shadow:none; margin:0 auto; }
    }
</style>
</head>
<body>

<div class="aksi">
    <button type="button" onclick="window.print()">🖨 Cetak / Simpan PDF</button>
    <a href="<?= htmlspecialchars($urlKembali) ?>">← Kembali</a>
</div>

<div class="struk">

    <div class="center">
        <div class="toko">ASBOOKSTORE</div>
        <div>Struk Pembelian</div>
    </div>

    <hr class="garis">

    <div class="baris"><span>No. Struk</span><span><?= htmlspecialchars($noStruk) ?></span></div>
    <div class="baris"><span>Pesanan</span><span>#<?= (int) $order['id_order'] ?></span></div>
    <div class="baris"><span>Tanggal</span><span><?= date('d-m-Y H:i', strtotime($order['tanggal_order'])) ?></span></div>
    <div class="baris"><span>Penerima</span><span><?= htmlspecialchars($order['nama_penerima'] ?: '-') ?></span></div>
    <div class="baris"><span>No HP</span><span><?= htmlspecialchars($order['no_hp'] ?: '-') ?></span></div>
    <div style="margin-top:4px;">Alamat: <?= nl2br(htmlspecialchars($order['alamat_pengiriman'] ?: '-')) ?></div>

    <hr class="garis">

    <?php while ($item = mysqli_fetch_assoc($detail_query)): ?>
        <div class="item">
            <div class="nama"><?= htmlspecialchars($item['judul']) ?></div>
            <div class="baris">
                <span><?= (int) $item['qty'] ?> x <?= number_format($item['harga'], 0, ',', '.') ?></span>
                <span><?= number_format($item['subtotal'], 0, ',', '.') ?></span>
            </div>
        </div>
    <?php endwhile; ?>

    <hr class="garis">

    <div class="baris total"><span>TOTAL</span><span>Rp <?= number_format($order['total_harga'], 0, ',', '.') ?></span></div>
    <div class="baris"><span>Pembayaran</span><span><?= htmlspecialchars(labelMetode($metode)) ?></span></div>
    <div class="baris"><span>Status Pesanan</span><span><?= htmlspecialchars($status) ?></span></div>

    <div class="stempel"><?= $statusBayar ?></div>

    <hr class="garis">

    <div class="center">
        Terima kasih sudah berbelanja!<br>
        Simpan struk ini sebagai bukti pembelian.
    </div>

</div>

</body>
</html>
