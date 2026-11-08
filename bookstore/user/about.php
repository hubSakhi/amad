<?php
session_start();

if(!isset($_SESSION['id_user'])){
    header("Location: ../login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>About Us - ASBookStore</title>

    <style>
        *{
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body{
            background: #f5f5f5;
        }

        nav{
            background: #4f46e5;
            padding: 20px 12%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: white;
        }

        nav h2{
            font-size: 24px;
        }

        nav a{
            color: white;
            text-decoration: none;
            margin-left: 25px;
        }

        .hero{
            background: #4f46e5;
            color: white;
            width: 70%;
            margin: 40px auto 0;
            padding: 50px;
            text-align: center;
            border-radius: 15px;
        }

        .hero h1{
            font-size: 40px;
            margin-bottom: 10px;
        }

        .hero p{
            font-size: 18px;
            font-style: italic;
        }

        .container{
            width: 70%;
            margin: 25px auto 50px;
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }

        .container h2{
            margin-bottom: 15px;
        }

        .container p{
            line-height: 1.8;
            color: #555;
            margin-bottom: 15px;
        }

        .info{
            margin-top: 25px;
            padding: 20px;
            background: #f3f4ff;
            border-radius: 10px;
        }

        .info h3{
            margin-bottom: 10px;
        }

        .back{
            display: inline-block;
            margin-top: 20px;
            text-decoration: none;
            background: #4f46e5;
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
        }
    </style>
</head>

<body>

<nav>
    <h2>📚 ASBookStore</h2>

    <div>
        <a href="dashboard.php">Home</a>
        <a href="daftarbuku.php">Buku</a>
        <a href="about.php">About</a>
        <a href="contact.php">Contact</a>
        <a href="logout.php">Logout</a>
    </div>
</nav>

<section class="hero">
    <h1>ASBookStore</h1>
    <p>Your Books, Your Stories.</p>
</section>

<div class="container">

    <h2>Tentang ASBookStore</h2>

    <p>
        <b>ASBookStore</b> adalah sebuah platform toko buku online
        yang dibuat untuk memudahkan pengguna dalam mencari dan membeli
        berbagai macam buku dengan lebih mudah dan praktis.
    </p>

    <p>
        ASBookStore menyediakan berbagai kategori buku, mulai dari
        novel, teknologi, pendidikan, komik, dan berbagai jenis buku
        lainnya.
    </p>

    <p>
        Melalui ASBookStore, pengguna dapat melihat informasi buku,
        mencari buku yang diinginkan, menambahkan buku ke dalam keranjang,
        serta melakukan pemesanan dengan mudah.
    </p>

    <div class="info">
        <h3>📖 Your Books, Your Stories.</h3>

        <p>
            Kami percaya bahwa setiap buku memiliki cerita,
            dan setiap pembaca memiliki kisahnya sendiri.
            ASBookStore hadir untuk membantu kamu menemukan
            buku yang sesuai dengan cerita dan perjalananmu.
        </p>
    </div>

    <a href="dashboard.php" class="back">
        ← Kembali ke Dashboard
    </a>

</div>

</body>
</html>