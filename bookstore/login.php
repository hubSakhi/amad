<?php
session_start();
include "koneksi.php";

$pesan = "";

if (isset($_POST['login'])) {

    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $password = $_POST['password'];

    // ==========================
    // LOGIN ADMIN
    // ==========================
    $admin = mysqli_query($conn, "SELECT * FROM tb_admin WHERE username='$username'");

    if (mysqli_num_rows($admin) > 0) {

        $data = mysqli_fetch_assoc($admin);

        if (password_verify($password, $data['password'])) {

            $_SESSION['login']    = true;
            $_SESSION['role']     = "admin";
            $_SESSION['id_admin'] = $data['id_admin'];
            $_SESSION['nama']     = $data['nama'];

            header("Location: admin/dashboard.php");
            exit;

        }

    }

    // ==========================
    // LOGIN USER
    // ==========================
    $user = mysqli_query($conn, "SELECT * FROM tb_user WHERE username='$username'");

    if (mysqli_num_rows($user) > 0) {

        $data = mysqli_fetch_assoc($user);

        if (password_verify($password, $data['password'])) {

            $_SESSION['login']   = true;
            $_SESSION['role']    = "user";
            $_SESSION['id_user'] = $data['id_user'];
            $_SESSION['nama']    = $data['nama'];

            header("Location: index.php");
            exit;

        }

    }

    $pesan = "<div class='alert alert-danger'>Username atau Password salah.</div>";

}
?>
<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login - ASBookStore</title>

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
            <h2>Your Books, Your Stories.</h2>
            <p>Masuk untuk mulai belanja, simpan buku ke keranjang, dan pantau status pesananmu.</p>

            <ul class="auth-points">
                <li>Koleksi buku dari berbagai kategori</li>
                <li>Checkout praktis, cukup unggah bukti transfer</li>
                <li>Pantau pesanan kapan saja</li>
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

            <h1>Selamat datang kembali</h1>
            <p class="sub">Masuk ke akun ASBookStore kamu.</p>

            <?= $pesan ?>

            <form method="POST">

                <div class="form-group">
                    <label for="username">Username</label>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        required
                        autofocus
                        autocomplete="username"
                        value="<?= (isset($_POST['username']) && is_string($_POST['username'])) ? htmlspecialchars($_POST['username']) : '' ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="pw-field">
                        <input type="password" id="password" name="password" required autocomplete="current-password">
                        <button type="button" class="pw-toggle" aria-label="Tampilkan password" onclick="togglePw('password', this)">Lihat</button>
                    </div>
                </div>

                <button type="submit" name="login" class="btn btn-block">Login</button>

            </form>

            <div class="auth-foot">
                Belum punya akun? <a href="register.php">Daftar Sekarang</a>
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
