<?php
session_start();
include "../koneksi.php";

header('Content-Type: application/json');

// ==============================
// HARUS LOGIN SEBAGAI USER
// ==============================
if (!isset($_SESSION['id_user']) || ($_SESSION['role'] ?? '') !== 'user') {
    echo json_encode([
        'success'  => false,
        'message'  => 'Silakan login terlebih dahulu.',
        'redirect' => '../login.php'
    ]);
    exit;
}

$idUser = (int) $_SESSION['id_user'];
$idBuku = isset($_POST['id_buku']) ? (int) $_POST['id_buku'] : 0;
$qty    = isset($_POST['qty']) ? (int) $_POST['qty'] : 1;

if ($qty < 1) {
    $qty = 1;
}

if ($idBuku <= 0) {
    echo json_encode(['success' => false, 'message' => 'Buku tidak valid.']);
    exit;
}

// ==============================
// CEK BUKU & STOK
// ==============================
$bukuRes = mysqli_query($conn, "SELECT id_buku, harga, stok FROM tb_buku WHERE id_buku = $idBuku");

if (!$bukuRes || mysqli_num_rows($bukuRes) === 0) {
    echo json_encode(['success' => false, 'message' => 'Buku tidak ditemukan.']);
    exit;
}

$buku = mysqli_fetch_assoc($bukuRes);

if ((int) $buku['stok'] <= 0) {
    echo json_encode(['success' => false, 'message' => 'Maaf, stok buku ini sedang habis (Out of Stock).']);
    exit;
}

// ==============================
// CEK APAKAH SUDAH ADA DI CART
// ==============================
$cek = mysqli_query($conn, "SELECT * FROM tb_cart WHERE id_user = $idUser AND id_buku = $idBuku");
$cartLama = mysqli_fetch_assoc($cek);

$qtyBaru = $qty + ($cartLama ? (int) $cartLama['qty'] : 0);

if ($qtyBaru > (int) $buku['stok']) {
    echo json_encode([
        'success' => false,
        'message' => 'Jumlah melebihi stok yang tersedia (sisa ' . $buku['stok'] . ').'
    ]);
    exit;
}

$subtotal = $qtyBaru * (float) $buku['harga'];

if ($cartLama) {

    mysqli_query($conn, "
        UPDATE tb_cart
        SET qty = $qtyBaru, subtotal = $subtotal
        WHERE id_cart = " . (int) $cartLama['id_cart'] . "
    ");

} else {

    mysqli_query($conn, "
        INSERT INTO tb_cart (id_user, id_buku, qty, subtotal)
        VALUES ($idUser, $idBuku, $qtyBaru, $subtotal)
    ");

}

// Hitung ulang total item di cart untuk badge navbar
$totalCartRes = mysqli_query($conn, "SELECT COALESCE(SUM(qty),0) AS total FROM tb_cart WHERE id_user = $idUser");
$totalCart    = (int) mysqli_fetch_assoc($totalCartRes)['total'];

echo json_encode([
    'success'    => true,
    'message'    => 'Buku berhasil ditambahkan ke keranjang!',
    'cart_count' => $totalCart
]);
