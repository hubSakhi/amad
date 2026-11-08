<?php
include("../koneksi.php");

/** @var mysqli $conn */

// Mengambil data buku beserta nama kategorinya
$query = "
SELECT
    tb_buku.*,
    tb_kategori.nama_kategori
FROM tb_buku
LEFT JOIN tb_kategori
ON tb_buku.id_kategori = tb_kategori.id_kategori
ORDER BY tb_buku.id_buku DESC
";

$data = mysqli_query($conn, $query);

if(!$data){
    die("Query Error : " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Daftar Buku</title>

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:Arial,sans-serif;
}

body{
    background:#f5f5f5;
}

header{
    background:#4f46e5;
    color:white;
    padding:20px;
    text-align:center;
}

.container{
    width:90%;
    margin:30px auto;

    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(250px,1fr));
    gap:20px;
}

.card{

    background:white;
    border-radius:10px;
    overflow:hidden;

    box-shadow:0 5px 15px rgba(0,0,0,.15);

}

.card img{

    width:100%;
    height:300px;
    object-fit:cover;

}

.card-body{

    padding:15px;

}

.card-body h3{

    margin-bottom:10px;
    color:#333;

}

.card-body p{

    margin:6px 0;
    color:#555;

}

.btn{

    display:block;
    text-decoration:none;
    text-align:center;

    background:#4f46e5;

    color:white;

    padding:10px;

    border-radius:6px;

    margin-top:15px;

}

.btn:hover{

    background:#372fc9;

}

.empty{

    width:100%;
    text-align:center;
    padding:40px;
    font-size:20px;

}

</style>

</head>

<body>

<header>

<h1>📚 Daftar Buku</h1>

</header>

<div class="container">

<?php

if(mysqli_num_rows($data) > 0){

while($buku = mysqli_fetch_assoc($data)){

if(empty($buku['gambar'])){

$gambar = "https://via.placeholder.com/300x400?text=Book";

}else{

$gambar = "../gambar/".$buku['gambar'];

}

?>

<div class="card">

<img src="<?php echo $gambar; ?>">

<div class="card-body">

<h3><?php echo $buku['judul']; ?></h3>

<p><b>Penulis</b> : <?php echo $buku['penulis']; ?></p>

<?php if (!empty($buku['nama_kategori'])) { ?><p><b>Kategori</b> : <?php echo htmlspecialchars($buku['nama_kategori']); ?></p><?php } ?>

<p><b>Penerbit</b> : <?php echo $buku['penerbit']; ?></p>

<p><b>Tahun</b> : <?php echo $buku['tahun_terbit']; ?></p>

<p><b>Stok</b> : <?php echo $buku['stok']; ?></p>

<p><b>Harga</b> : Rp <?php echo number_format($buku['harga'],0,",","."); ?></p>

<a href="cart.php?id_buku=<?php echo $buku['id_buku']; ?>" class="btn">
    Add To Cart
</a>

</div>

</div>

<?php

}

}else{

?>

<div class="empty">

Belum ada data buku.

</div>

<?php

}

?>

</div>

</body>
</html>