
<?php
include "koneksi.php";

// Password baru administrator
$passwordBaru = "admin123";
$hashPassword = password_hash($passwordBaru, PASSWORD_DEFAULT);

// Update akun admin yang sudah ada
$stmt = mysqli_prepare(
    $conn,
    "UPDATE tb_admin SET password = ? WHERE username = ?"
);

$username = "admin";

mysqli_stmt_bind_param($stmt, "ss", $hashPassword, $username);

if (mysqli_stmt_execute($stmt)) {
    if (mysqli_stmt_affected_rows($stmt) > 0) {
        echo "Password admin berhasil diperbarui!";
    } else {
        echo "Username admin tidak ditemukan atau password tidak berubah.";
    }
} else {
    echo "Gagal memperbarui password.";
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>
