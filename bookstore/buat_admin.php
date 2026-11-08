<?php
include "koneksi.php";

$nama = "Administrator";
$username = "admin";
$password = password_hash("admin123", PASSWORD_DEFAULT);

$query = mysqli_query($conn, "INSERT INTO tb_admin (nama, username, password)
VALUES ('$nama', '$username', '$password')");

if($query){
    echo "Admin berhasil dibuat!";
}else{
    echo "Error: " . mysqli_error($conn);
}
?>