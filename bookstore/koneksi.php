<?php
// ==========================================
// KONEKSI DATABASE BOOKSTORE
// ==========================================

$host     = "localhost";
$username = "root";
$password = "";
$database = "bookstore";

// Membuat koneksi
$conn = mysqli_connect($host, $username, $password, $database);

// Mengecek koneksi
if (!$conn) {
    die("Koneksi database gagal : " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");

// Mengatur timezone Indonesia
date_default_timezone_set("Asia/Jakarta");

// ==========================================
// KONSTANTA FOLDER UPLOAD (relatif dari root project)
// ==========================================
define("FOLDER_GAMBAR_BUKU", "gambar/");
define("FOLDER_BUKTI_BAYAR", "uploads/bukti/");
