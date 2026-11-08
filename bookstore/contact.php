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
    <title>Contact Admin - ASBookStore</title>

    <?php include "includes/navbar.php"; ?>
</head>
<body>

<div class="container-page">

    <div class="content-card contact-box">

        <h1>Contact Admin</h1>
        <p>Ada pertanyaan atau kendala? Hubungi admin melalui WhatsApp.</p>

        <form onsubmit="kirimWhatsApp(); return false;">

            <div class="form-group">
                <label for="nama">Nama</label>
                <input type="text" id="nama" placeholder="Masukkan nama kamu" required>
            </div>

            <div class="form-group">
                <label for="pesan">Pesan</label>
                <textarea id="pesan" placeholder="Tulis pesan kamu..." required></textarea>
            </div>

            <button type="submit" class="wa-btn">💬 Kirim via WhatsApp</button>

        </form>

    </div>

</div>

<?php include "includes/footer.php"; ?>

<script>
function kirimWhatsApp() {
    var nama  = document.getElementById("nama").value;
    var pesan = document.getElementById("pesan").value;
    var nomorAdmin = "6289517447753";

    var text = "Halo Admin BookStore!%0A%0ANama: " + encodeURIComponent(nama) +
               "%0APesan: " + encodeURIComponent(pesan);

    var url = "https://wa.me/" + nomorAdmin + "?text=" + text;
    window.open(url, "_blank");
}
</script>

</body>
</html>
