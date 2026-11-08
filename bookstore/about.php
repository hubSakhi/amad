<?php
session_start();
include "koneksi.php";

$root    = '';
$userdir = 'user/';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - ASBookStore</title>

    <?php include "includes/navbar.php"; ?>
</head>
<body>

<div class="container-page">

    <div class="page-hero">
        <h1>ASBookStore</h1>
        <p>Your Books, Your Stories.</p>
    </div>

    <div class="content-card">

        <h2>Tentang ASBookStore</h2>

        <p>
            <b>ASBookStore</b> adalah sebuah platform toko buku online yang dibuat
            untuk memudahkan pengguna dalam mencari dan membeli berbagai macam buku
            dengan lebih mudah dan praktis.
        </p>

        <p>
            ASBookStore menyediakan berbagai kategori buku, mulai dari novel,
            teknologi, pengembangan diri, dan berbagai jenis buku lainnya.
        </p>

        <p>
            Melalui ASBookStore, pengguna dapat mencari buku, melihat detail
            lengkapnya, menambahkan buku ke keranjang, lalu melakukan pemesanan
            dengan mengunggah bukti pembayaran secara langsung.
        </p>

        <div class="info-box">
            <h3>📖 Your Books, Your Stories.</h3>
            <p style="margin-bottom:0;">
                Kami percaya setiap buku memiliki cerita, dan setiap pembaca
                memiliki kisahnya sendiri. ASBookStore hadir untuk membantu kamu
                menemukan buku yang sesuai dengan perjalananmu.
            </p>
        </div>

    </div>

</div>

<?php include "includes/footer.php"; ?>

</body>
</html>
