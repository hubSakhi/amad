<?php
// ==========================================
// DATA METODE PEMBAYARAN (dipakai checkout, pesanan user & admin)
// Ubah nomor rekening di sini kalau berubah.
// ==========================================
define("REK_BANK",  "Bank BCA");
define("REK_NOMOR", "1234567890");
define("REK_NAMA",  "ASBookStore");
define("QRIS_GAMBAR", "gambar/pembayaran/qris.png");

$METODE_BAYAR = [
    'Transfer' => ['label' => 'Transfer Bank', 'icon' => '🏦', 'butuh_bukti' => true],
    'QRIS'     => ['label' => 'QRIS',          'icon' => '📱', 'butuh_bukti' => true],
    'COD'      => ['label' => 'COD (Bayar di Tempat)', 'icon' => '🚚', 'butuh_bukti' => false],
];

function labelMetode($kode)
{
    global $METODE_BAYAR;
    return isset($METODE_BAYAR[$kode]) ? $METODE_BAYAR[$kode]['label'] : 'Transfer Bank';
}

function metodeButuhBukti($kode)
{
    global $METODE_BAYAR;
    return !isset($METODE_BAYAR[$kode]) || $METODE_BAYAR[$kode]['butuh_bukti'];
}
