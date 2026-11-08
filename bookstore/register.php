<?php
include "koneksi.php";

$pesan = "";

if (isset($_POST['register'])) {

    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $email    = mysqli_real_escape_string($conn, trim($_POST['email']));
    $password = $_POST['password'];

    // Kolom nama di tabel tb_user wajib diisi (NOT NULL), tapi form register
    // sengaja tidak menanyakannya lagi -- otomatis dipakaikan dari username.
    $nama = $username;

    if ($username === '' || $email === '' || $password === '') {

        $pesan = "<div class='alert alert-danger'>Semua kolom wajib diisi.</div>";

    } else {

        // Cek Username
        $cekUsername = mysqli_query($conn, "SELECT * FROM tb_user WHERE username='$username'");

        if (mysqli_num_rows($cekUsername) > 0) {

            $pesan = "<div class='alert alert-danger'>Username sudah digunakan.</div>";

        } else {

            // Cek Email
            $cekEmail = mysqli_query($conn, "SELECT * FROM tb_user WHERE email='$email'");

            if (mysqli_num_rows($cekEmail) > 0) {

                $pesan = "<div class='alert alert-danger'>Email sudah digunakan.</div>";

            } elseif (strlen($password) < 6) {

                $pesan = "<div class='alert alert-warning'>Password minimal 6 karakter.</div>";

            } else {

                $passwordHash = password_hash($password, PASSWORD_DEFAULT);

                $simpan = mysqli_query($conn, "
                    INSERT INTO tb_user (nama, username, email, password)
                    VALUES ('$nama', '$username', '$email', '$passwordHash')
                ");

                if ($simpan) {

                    echo "<script>
                        alert('Registrasi berhasil! Silakan login.');
                        window.location='login.php';
                    </script>";
                    exit;

                } else {

                    $pesan = "<div class='alert alert-danger'>Registrasi gagal. " . mysqli_error($conn) . "</div>";

                }

            }

        }

    }

}
?>
<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Register - ASBookStore</title>

<link href="css/style.css?v=<?= @filemtime(__DIR__ . '/css/style.css') ?: 1 ?>" rel="stylesheet">

</head>

<body class="auth-page">

<div class="auth-shell">

    <aside class="auth-side">

        <a href="index.php" class="brand">
            <span class="brand-mark"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg></span>
            <span>ASBookStore</span>
        </a>

        <div>
            <h2>Mulai perjalanan bacamu.</h2>
            <p>Buat akun dalam hitungan detik. Alamat pengiriman baru diisi saat checkout.</p>

            <ul class="auth-points">
                <li>Cukup isi data singkat</li>
                <li>Simpan buku favorit di keranjang</li>
                <li>Pantau status pesanan dengan mudah</li>
            </ul>
        </div>

        <small>&copy; <?= date("Y") ?> ASBookStore</small>

    </aside>

    <main class="auth-main">

        <div class="auth-card">

            <a href="index.php" class="brand auth-mobile-brand">
                <span class="brand-mark"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg></span>
                <span>ASBookStore</span>
            </a>

            <h1>Daftar Akun</h1>
            <p class="sub">Cukup isi username, email, dan password.</p>

            <?= $pesan ?>

            <form method="POST">

                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required autocomplete="username" value="<?= (isset($_POST['username']) && is_string($_POST['username'])) ? htmlspecialchars($_POST['username']) : '' ?>">
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required autocomplete="email" value="<?= (isset($_POST['email']) && is_string($_POST['email'])) ? htmlspecialchars($_POST['email']) : '' ?>">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="pw-field">
                        <input type="password" id="password" name="password" required minlength="6" autocomplete="new-password">
                        <button type="button" class="pw-toggle" aria-label="Tampilkan password" onclick="togglePw('password', this)">Lihat</button>
                    </div>
                    <div class="form-hint">Minimal 6 karakter.</div>
                </div>

                <button type="submit" name="register" class="btn btn-block">Daftar Sekarang</button>

            </form>

            <div class="auth-foot">
                Sudah punya akun? <a href="login.php">Login Disini</a>
                <br>
                <a href="index.php" class="auth-back">&larr; Kembali ke Beranda</a>
            </div>

        </div>

    </main>

</div>

<script>
function togglePw(id, btn) {
    var input = document.getElementById(id);
    var show  = input.type === 'password';
    input.type = show ? 'text' : 'password';
    btn.textContent = show ? 'Sembunyi' : 'Lihat';
    btn.setAttribute('aria-label', show ? 'Sembunyikan password' : 'Tampilkan password');
}
</script>

</body>

</html>
