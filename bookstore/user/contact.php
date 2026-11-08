<?php
session_start();

if (!isset($_SESSION['id_user'])) {
    header("Location: ../login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Contact Admin - BookStore</title>

    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background: #f5f5f5;
        }

        nav {
            background: #4f46e5;
            padding: 18px 12%;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        nav h2 {
            color: white;
        }

        nav a {
            color: white;
            text-decoration: none;
            margin-left: 20px;
        }

        .container {
            width: 500px;
            max-width: 90%;
            margin: 60px auto;
        }

        .contact-box {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
        }

        .contact-box h1 {
            text-align: center;
            margin-bottom: 10px;
        }

        .contact-box p {
            text-align: center;
            color: #666;
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin-bottom: 7px;
            font-weight: bold;
        }

        input,
        textarea {
            width: 100%;
            padding: 12px;
            margin-bottom: 18px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        textarea {
            height: 120px;
            resize: none;
        }

        button {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 6px;
            background: #25D366;
            color: white;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background: #1da851;
        }

        .back {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #4f46e5;
            text-decoration: none;
        }

    </style>
</head>

<body>

    <nav>

        <h2>📚 ASBookStore</h2>

        <div>
            <a href="dashboard.php">Home</a>
            <a href="daftarbuku.php">Buku</a>
            <a href="contact.php">Contact</a>
            <a href="logout.php">Logout</a>
        </div>

    </nav>


    <div class="container">

        <div class="contact-box">

            <h1>Contact Admin</h1>

            <p>
                Ada pertanyaan atau kendala? Hubungi admin melalui WhatsApp.
            </p>


            <form onsubmit="kirimWhatsApp(); return false;">

                <label>Nama</label>

                <input
                    type="text"
                    id="nama"
                    placeholder="Masukkan nama kamu"
                    required
                >


                <label>Pesan</label>

                <textarea
                    id="pesan"
                    placeholder="Tulis pesan kamu..."
                    required
                ></textarea>


                <button type="submit">
                    💬 Kirim via WhatsApp
                </button>

            </form>


            <a href="dashboard.php" class="back">
                ← Kembali ke Dashboard
            </a>

        </div>

    </div>


    <script>

        function kirimWhatsApp() {

            let nama = document.getElementById("nama").value;
            let pesan = document.getElementById("pesan").value;

            let nomorAdmin = "6289517447753";

            let text =
                "Halo Admin BookStore!%0A%0A" +
                "Nama: " + nama + "%0A" +
                "Pesan: " + pesan;

            let url = "https://wa.me/" + nomorAdmin + "?text=" + text;

            window.open(url, "_blank");

        }

    </script>

</body>

</html>