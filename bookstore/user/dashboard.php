<?php
session_start();

if (!isset($_SESSION['id_user'])) {
    header("Location: ../login.php");
    exit;
}

include("../koneksi.php");

$data = mysqli_query($conn, "SELECT * FROM tb_buku ORDER BY id_buku DESC LIMIT 4");
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>BookStore</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
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
            width: 75%;
            margin: 40px auto;
        }

        .hero {
            background: #4f46e5;
            color: white;
            text-align: center;
            padding: 80px 20px;
            border-radius: 15px;
        }

        .hero h1 {
            font-size: 38px;
            margin-bottom: 20px;
        }

        .hero p {
            margin-bottom: 30px;
        }

        .btn {
            display: inline-block;
            background: #ffc107;
            color: black;
            padding: 12px 25px;
            border-radius: 7px;
            text-decoration: none;
        }

        h2 {
            margin-top: 45px;
            margin-bottom: 20px;
        }

        .kategori {
            display: flex;
            gap: 20px;
        }

        .kategori div {
            background: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px;
            flex: 1;
        }

        .buku {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }

        .card {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            padding-bottom: 15px;
        }

        .card img {
            width: 100%;
            height: 250px;
            object-fit: cover;
        }

        .card-content {
            padding: 15px;
        }

        .card h3 {
            margin-bottom: 10px;
        }

        .harga {
            margin-top: 10px;
            font-weight: bold;
        }
    </style>
</head>

<body>

    <nav>
        <h2>📚 ASBookStore</h2>

        <div>
            <a href="dashboard.php">Home</a>
            <a href="about.php">About</a>
            <a href="contact.php">Contact</a>
            <a href="logout.php">Logout</a>
        </div>
    </nav>


    <div class="container">

        <div class="hero">

            <h1>Selamat Datang di BookStore</h1>

            <p>
                Temukan ribuan koleksi buku terbaik dengan harga terjangkau.
            </p>

            <a href="daftarbuku.php" class="btn">
                Mulai Belanja
            </a>

        </div>


        <h2>Kategori Populer</h2>

        <div class="kategori">

            <div>Novel</div>
            <div>Teknologi</div>
            <div>Pendidikan</div>
            <div>Komik</div>

        </div>


        <h2>Buku Terbaru</h2>

        <div class="buku">

            <?php while ($buku = mysqli_fetch_assoc($data)) { ?>

                <div class="card">

                    <img src="../gambar/<?php echo $buku['gambar']; ?>">

                    <div class="card-content">

                        <h3>
                            <?php echo $buku['judul']; ?>
                        </h3>

                        <p>
                            <?php echo $buku['penulis']; ?>
                        </p>

                        <p class="harga">
                            Rp <?php echo number_format($buku['harga']); ?>
                        </p>

                    </div>

                </div>

            <?php } ?>

        </div>

    </div>

</body>

</html>