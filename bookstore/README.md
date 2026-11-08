# ASBookStore — Catatan Update

Semua permintaan sudah dikerjakan. Berikut ringkasannya, plus panduan instalasi
di paling bawah.

## 1. Registrasi lebih sederhana
`register.php` sekarang hanya meminta **Nama, Username, Email, Password,
Konfirmasi Password**. Field No HP & Alamat dihapus dari form registrasi —
dipindahkan ke halaman **Checkout**, karena data pengiriman memang lebih masuk
akal diisi per-pesanan (bisa beda alamat tiap kali beli), bukan disimpan
permanen di profil.

## 2. Navbar konsisten di semua halaman
Sebelumnya tiap halaman punya menu navigasi yang beda-beda (ada yang lupa
"Cart", ada yang lupa "About", cart.php bahkan tidak punya menu sama sekali).

Sekarang dibuat **satu file navbar terpusat**: `includes/navbar.php`, dipakai
oleh SEMUA halaman (root maupun `/user/`). Isinya persis seperti yang diminta:
Home, About, Contact, Pesanan Saya, Cart, Logout (kalau sudah login) — atau
Login/Register (kalau belum login). Karena hanya satu file, menunya dijamin
selalu sama di setiap halaman.

## 3. Tombol "Add to Cart" tidak lagi redirect ke cart
Sebelumnya klik "Add to Cart" langsung `header("Location: cart.php")`.
Sekarang pakai **AJAX** (`user/cart_add.php` + fetch JS di `detailbuku.php`):
data langsung tersimpan ke database, muncul notifikasi kecil ("toast") di
pojok kanan atas, dan badge jumlah cart di navbar ikut update — tanpa
pindah halaman sama sekali.

## 4. Bukti pembayaran wajib saat checkout
`user/checkout.php` sekarang menampilkan form data pengiriman (nama
penerima, no HP, alamat) + **upload bukti pembayaran (wajib, JPG/PNG/WEBP/PDF,
maks 3MB)**. File ini disimpan di `uploads/bukti/` dan filenya tersimpan di
kolom `bukti_pembayaran` pada tabel `tb_order`.

Di sisi admin (`admin/detailpesanan.php` & `admin/pesanan.php`), bukti
pembayaran ditampilkan (preview gambar / link file). **Jika bukti belum ada,
form ubah status disembunyikan** dan diganti peringatan — bahkan kalau
diakali lewat POST langsung, server tetap menolak update status tanpa bukti
pembayaran (validasi dobel di server, bukan cuma di tampilan).

## 5. Pencarian & kategori
- `daftarbuku.php` (sekarang di root, bisa diakses tanpa login untuk lihat-
  lihat) punya form pencarian keyword (judul/penulis) + filter dropdown
  kategori.
- Admin bisa CRUD kategori penuh di **`admin/kategori.php`** (tambah, edit
  nama, hapus — hapus diblokir kalau kategori masih dipakai buku).

## 6. Card buku → halaman detail
Setiap card buku (di beranda maupun daftar buku) adalah link ke
**`detailbuku.php?id=...`**, halaman baru yang menampilkan info lengkap:
penulis, penerbit, tahun terbit, stok, deskripsi, harga, dan tombol Add to
Cart (dengan pengatur jumlah).

## 7. Stok habis → Out of Stock
- Kalau `stok = 0`, badge merah **"Out of Stock"** otomatis muncul di card &
  halaman detail.
- Tombol Add to Cart otomatis berubah jadi **disabled** ("Stok Habis") —
  tidak bisa ditambah ke keranjang sama sekali.
- Endpoint `cart_add.php` juga memvalidasi ulang di server (bukan cuma di
  tampilan), jadi tidak bisa diakali.
- Di keranjang, kalau ada barang yang stoknya habis/berkurang setelah
  dimasukkan cart, tombol Checkout otomatis disembunyikan sampai user
  menghapus/menyesuaikan barang tersebut.
- Admin bisa atur stok di `admin/buku.php`; begitu 0, badge "Out of Stock"
  otomatis muncul di tabel admin juga.

## 8. Bagian yang sebelumnya belum selesai
Sidebar admin sudah punya link ke `kategori.php`, `buku.php`, `user.php`
sejak awal, tapi ketiga file itu **belum pernah dibuat** (klik menunya akan
error). Sekarang sudah dibuat lengkap:

- **`admin/kategori.php`** — tambah / edit / hapus kategori.
- **`admin/buku.php`** — tambah / edit / hapus buku, termasuk upload gambar
  sampul, atur harga & stok, deskripsi.
- **`admin/user.php`** — daftar semua user terdaftar (hapus diblokir kalau
  user sudah punya pesanan, supaya data pesanan tidak rusak).
- **`admin/dashboard.php`** ditambah 2 kartu statistik baru: jumlah buku
  Out of Stock & jumlah pesanan Pending yang menunggu diverifikasi.

Selain itu ada beberapa bug lama yang ikut diperbaiki:
- Path gambar buku sebelumnya tidak konsisten (`upload/buku/…` di satu
  halaman, `../gambar/…` di halaman lain, padahal file aslinya ada di
  `/gambar/`). Sekarang seragam pakai konstanta `FOLDER_GAMBAR_BUKU`.
- File `cek.php` (debug tool yang menampilkan hash password admin) dihapus
  karena berisiko keamanan.
- Halaman About/Contact di root sebelumnya link ke file yang tidak ada
  sekarang benar-benar ada dan bisa diakses tanpa login.

## Struktur folder baru
```
/                     halaman publik (index, daftarbuku, detailbuku, about,
                      contact, login, register, logout)
/includes/            navbar.php & footer.php bersama
/css/style.css        1 file CSS bersama untuk seluruh tampilan
/user/                halaman yang butuh login (cart, checkout, pesanan,
                      detailpesanan, cart_add)
/admin/                panel admin (dashboard, kategori, buku, user,
                      pesanan, detailpesanan)
/admin/includes/       sidebar admin bersama
/gambar/               cover buku
/uploads/bukti/        bukti pembayaran hasil upload user
database.sql          skema database lengkap + data contoh
```

## Cara Install
1. Buat database (bisa import langsung): jalankan `database.sql` di
   phpMyAdmin / MySQL client. Ini akan membuat database `bookstore` beserta
   semua tabel + data contoh kategori & buku.
2. Sesuaikan `koneksi.php` kalau host/username/password MySQL kamu beda dari
   default XAMPP (`root` tanpa password).
3. Pastikan folder `uploads/bukti/` dan `gambar/` bisa ditulis (writable) oleh
   web server — di XAMPP biasanya sudah otomatis bisa.
4. Buka `buat_admin.php` sekali lewat browser untuk membuat akun admin
   (username `admin`, password `admin123`). Setelah itu **hapus atau
   pindahkan file `buat_admin.php`** supaya tidak disalahgunakan orang lain.
5. Akses `index.php` sebagai halaman utama. Login admin akan diarahkan ke
   `admin/dashboard.php`, login user diarahkan ke `index.php`.

Selamat mencoba! Kalau ada bagian yang mau disesuaikan lagi (misalnya
tambah metode pembayaran, ongkir, atau multi-gambar per buku), tinggal
bilang saja.

---

## Update: Metode Pembayaran (Transfer, QRIS, COD)

Halaman checkout (`user/checkout.php`) sekarang punya 3 pilihan pembayaran:

- **Transfer Bank** — tampil info rekening, wajib upload bukti transfer.
- **QRIS** — tampil gambar QRIS (`gambar/pembayaran/qris.png`) + total yang harus dibayar, wajib upload bukti pembayaran.
- **COD** — bayar tunai saat barang tiba, tidak perlu upload bukti.

Yang berubah:
- Tabel `tb_order` punya kolom baru `metode_pembayaran`. Kalau database sudah ada,
  jalankan **`migrasi_metode_pembayaran.sql`** sekali di phpMyAdmin (tab SQL).
- `includes/pembayaran.php` — satu tempat untuk daftar metode, nomor rekening, dan path gambar QRIS.
- Validasi dilakukan di server: bukti wajib untuk Transfer & QRIS, tidak wajib untuk COD.
- Halaman Pesanan Saya / Detail Pesanan (user) dan Daftar Pesanan / Detail Pesanan (admin)
  menampilkan metode pembayaran. Untuk COD, admin bisa langsung mengubah status tanpa bukti.
- Mengganti QRIS: timpa file `gambar/pembayaran/qris.png`.

## Update: Struk Pembelian

- `user/struk.php` — struk untuk pelanggan (hanya bisa membuka struk pesanannya sendiri).
- `admin/struk.php` — struk yang sama untuk admin.
- Tampilan struk ada di `includes/struk_view.php` (nomor struk, data penerima, daftar buku,
  total, metode pembayaran, dan stempel status: LUNAS / MENUNGGU VERIFIKASI /
  BAYAR SAAT BARANG TIBA / DIBATALKAN).
- Tombol "Struk" ada di Pesanan Saya, Detail Pesanan, notifikasi setelah checkout, dan detail pesanan admin.
- Tombol "Cetak / Simpan PDF" memakai fitur print browser; tombol dan latar otomatis disembunyikan saat dicetak.

## Update: Dashboard Admin

`admin/dashboard.php` didesain ulang (tetap tanpa CDN/library luar): sapaan sesuai jam,
kartu pendapatan / pesanan / pending / stok habis, grafik penjualan 7 hari terakhir,
rincian status pesanan dan metode pembayaran, 6 pesanan terbaru, 5 buku terlaris, dan
daftar stok menipis (sisa ≤ 5). Pendapatan tidak menghitung pesanan berstatus Cancelled.
Sidebar admin juga dirapikan (label grup menu + kartu admin di bawah). Gaya ada di
bagian "ADMIN DASHBOARD" pada `css/style.css`.

## Update: Hapus Kategori Tanpa Menghapus Buku

- Kategori kini bisa dihapus walau masih dipakai buku. Bukunya TIDAK terhapus; kolom
  kategorinya dikosongkan dan buku tampil tanpa genre (tag kategori tidak muncul).
- Jalankan **`migrasi_kategori_nullable.sql`** sekali di phpMyAdmin untuk database yang sudah ada.
- Di admin > Buku, pilihan kategori sekarang opsional ("-- Tanpa Kategori --").
