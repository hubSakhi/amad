<?php

include "koneksi.php";

$query = mysqli_query($conn,"SELECT * FROM tb_admin");

$data = mysqli_fetch_assoc($query);

echo "Username : ".$data['username'];

echo "<br>";

echo "Password Hash : ".$data['password'];

echo "<br><br>";

if(password_verify("admin123",$data['password'])){

    echo "PASSWORD BENAR";

}else{

    echo "PASSWORD SALAH";

}