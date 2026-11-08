<?php
session_start();
include "../koneksi.php";
include "../includes/pembayaran.php";

if (!isset($_SESSION['id_user']) || ($_SESSION['role'] ?? '') !== 'user') {
    header("Location: ../login.php");
    exit;
}

$id_user = (int) $_SESSION['id_user'];

if (!isset($_GET['id_order'])) {
    header("Location: pesanan.php");
    exit;
}

$id_order = (int) $_GET['id_order'];

// User hanya boleh melihat struk pesanannya sendiri
$order_query = mysqli_query($conn, "
    SELECT * FROM tb_order
    WHERE id_order = $id_order AND id_user = $id_user
");

if (!$order_query || mysqli_num_rows($order_query) == 0) {
    echo "<script>
        alert('Pesanan tidak ditemukan!');
        window.location='pesanan.php';
    </script>";
    exit;
}

$order = mysqli_fetch_assoc($order_query);

$detail_query = mysqli_query($conn, "
    SELECT tb_order_detail.*, tb_buku.judul
    FROM tb_order_detail
    JOIN tb_buku ON tb_order_detail.id_buku = tb_buku.id_buku
    WHERE tb_order_detail.id_order = $id_order
");

if (!$detail_query) {
    die("Query Error: " . mysqli_error($conn));
}

$urlKembali = "detailpesanan.php?id_order=$id_order";

include "../includes/struk_view.php";
