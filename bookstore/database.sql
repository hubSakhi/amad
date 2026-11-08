-- ==========================================================
-- DATABASE: bookstore
-- Skema lengkap untuk ASBookStore (sudah termasuk fitur baru:
-- kategori, deskripsi buku, bukti pembayaran, data pengiriman)
-- ==========================================================

CREATE DATABASE IF NOT EXISTS bookstore CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE bookstore;

-- ==========================================================
-- 1. TABEL ADMIN
-- ==========================================================
CREATE TABLE IF NOT EXISTS tb_admin (
    id_admin  INT AUTO_INCREMENT PRIMARY KEY,
    nama      VARCHAR(100) NOT NULL,
    username  VARCHAR(50)  NOT NULL UNIQUE,
    password  VARCHAR(255) NOT NULL
) ENGINE=InnoDB;

-- ==========================================================
-- 2. TABEL USER
-- (Registrasi disederhanakan: hanya nama, username, email, password.
--  No HP & alamat diisi saat checkout, bisa beda tiap pesanan.)
-- ==========================================================
CREATE TABLE IF NOT EXISTS tb_user (
    id_user     INT AUTO_INCREMENT PRIMARY KEY,
    nama        VARCHAR(100) NOT NULL,
    username    VARCHAR(50)  NOT NULL UNIQUE,
    email       VARCHAR(100) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ==========================================================
-- 3. TABEL KATEGORI (CRUD oleh admin)
-- ==========================================================
CREATE TABLE IF NOT EXISTS tb_kategori (
    id_kategori   INT AUTO_INCREMENT PRIMARY KEY,
    nama_kategori VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- ==========================================================
-- 4. TABEL BUKU
-- ==========================================================
CREATE TABLE IF NOT EXISTS tb_buku (
    id_buku       INT AUTO_INCREMENT PRIMARY KEY,
    id_kategori   INT NULL,   -- boleh kosong: buku tanpa genre
    judul         VARCHAR(200) NOT NULL,
    penulis       VARCHAR(150),
    penerbit      VARCHAR(150),
    tahun_terbit  INT,
    harga         DECIMAL(12,2) NOT NULL DEFAULT 0,
    stok          INT NOT NULL DEFAULT 0,
    gambar        VARCHAR(255),
    deskripsi     TEXT,
    CONSTRAINT fk_buku_kategori FOREIGN KEY (id_kategori)
        REFERENCES tb_kategori (id_kategori)
        ON DELETE SET NULL   -- hapus kategori => buku tetap ada, kategorinya kosong
) ENGINE=InnoDB;

-- ==========================================================
-- 5. TABEL CART
-- ==========================================================
CREATE TABLE IF NOT EXISTS tb_cart (
    id_cart   INT AUTO_INCREMENT PRIMARY KEY,
    id_user   INT NOT NULL,
    id_buku   INT NOT NULL,
    qty       INT NOT NULL DEFAULT 1,
    subtotal  DECIMAL(12,2) NOT NULL DEFAULT 0,
    CONSTRAINT fk_cart_user FOREIGN KEY (id_user)
        REFERENCES tb_user (id_user) ON DELETE CASCADE,
    CONSTRAINT fk_cart_buku FOREIGN KEY (id_buku)
        REFERENCES tb_buku (id_buku) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ==========================================================
-- 6. TABEL ORDER
-- (metode_pembayaran: Transfer / QRIS / COD.
--  bukti_pembayaran WAJIB untuk Transfer & QRIS, tidak perlu untuk COD)
-- ==========================================================
CREATE TABLE IF NOT EXISTS tb_order (
    id_order            INT AUTO_INCREMENT PRIMARY KEY,
    id_user             INT NOT NULL,
    nama_penerima       VARCHAR(150),
    no_hp               VARCHAR(30),
    alamat_pengiriman   TEXT,
    total_harga         DECIMAL(12,2) NOT NULL DEFAULT 0,
    metode_pembayaran   VARCHAR(20) NOT NULL DEFAULT 'Transfer',
    bukti_pembayaran    VARCHAR(255) DEFAULT NULL,
    status              VARCHAR(20) NOT NULL DEFAULT 'Pending',
    tanggal_order       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_order_user FOREIGN KEY (id_user)
        REFERENCES tb_user (id_user) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ==========================================================
-- 7. TABEL ORDER DETAIL
-- ==========================================================
CREATE TABLE IF NOT EXISTS tb_order_detail (
    id_order_detail  INT AUTO_INCREMENT PRIMARY KEY,
    id_order         INT NOT NULL,
    id_buku          INT NOT NULL,
    qty              INT NOT NULL,
    harga            DECIMAL(12,2) NOT NULL,
    subtotal         DECIMAL(12,2) NOT NULL,
    CONSTRAINT fk_detail_order FOREIGN KEY (id_order)
        REFERENCES tb_order (id_order) ON DELETE CASCADE,
    CONSTRAINT fk_detail_buku FOREIGN KEY (id_buku)
        REFERENCES tb_buku (id_buku) ON DELETE RESTRICT
) ENGINE=InnoDB;


-- ==========================================================
-- MIGRASI (JALANKAN BAGIAN INI SAJA JIKA DATABASE LAMA SUDAH ADA
-- dan kamu TIDAK ingin membuat ulang dari awal).
-- Jika baru install dari kosong, bagian di atas sudah cukup,
-- lewati bagian ALTER TABLE di bawah ini.
-- ==========================================================

-- ALTER TABLE tb_buku      ADD COLUMN deskripsi TEXT;
-- ALTER TABLE tb_user      ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
-- ALTER TABLE tb_order     ADD COLUMN nama_penerima VARCHAR(150);
-- ALTER TABLE tb_order     ADD COLUMN no_hp VARCHAR(30);
-- ALTER TABLE tb_order     ADD COLUMN alamat_pengiriman TEXT;
-- ALTER TABLE tb_order     ADD COLUMN bukti_pembayaran VARCHAR(255) DEFAULT NULL;
-- ALTER TABLE tb_order     ADD COLUMN metode_pembayaran VARCHAR(20) NOT NULL DEFAULT 'Transfer' AFTER total_harga;


-- ==========================================================
-- DATA CONTOH (KATEGORI)
-- ==========================================================
INSERT INTO tb_kategori (nama_kategori) VALUES
('Novel'),
('Teknologi'),
('Pengembangan Diri'),
('Filsafat');

-- ==========================================================
-- DATA CONTOH (BUKU) - memakai gambar yang sudah ada di folder /gambar
-- ==========================================================
INSERT INTO tb_buku (id_kategori, judul, penulis, penerbit, tahun_terbit, harga, stok, gambar, deskripsi) VALUES
(1, 'Laskar Pelangi', 'Andrea Hirata', 'Bentang Pustaka', 2005, 85000, 12, 'laskarpelangi.jpg', 'Kisah perjuangan sekelompok anak di Belitung untuk mengenyam pendidikan di tengah keterbatasan.'),
(1, 'Bumi Manusia', 'Pramoedya Ananta Toer', 'Hasta Mitra', 1980, 95000, 8, 'bumimanusia.jpg', 'Novel epik tentang kehidupan Minke di masa kolonial Hindia Belanda.'),
(1, 'Bumi', 'Tere Liye', 'Gramedia Pustaka Utama', 2014, 89000, 0, 'bumi.jpg', 'Petualangan Raib, Seli, dan Ali menjelajahi dunia paralel penuh keajaiban.'),
(1, 'Pulang', 'Tere Liye', 'Republika Penerbit', 2015, 79000, 15, 'pulang.jpg', 'Kisah Bujang, pemuda dari keluarga Tigo Tungku yang menempuh jalan penuh liku.'),
(1, 'Amba', 'Laksmi Pamuntjak', 'Gramedia Pustaka Utama', 2012, 99000, 6, 'amba.jpg', 'Kisah cinta Amba dan Bhisma yang terjalin di tengah gejolak sejarah 1965.'),
(3, 'Atomic Habits', 'James Clear', 'Gramedia Pustaka Utama', 2018, 110000, 20, 'atomichabits.jpg', 'Panduan praktis membangun kebiasaan baik dan menghilangkan kebiasaan buruk.'),
(3, 'Rich Dad Poor Dad', 'Robert T. Kiyosaki', 'Gramedia Pustaka Utama', 1997, 98000, 10, 'richdadpoordad.jpg', 'Pelajaran tentang literasi finansial dari dua sosok ayah dengan pandangan berbeda.'),
(2, 'Clean Code', 'Robert C. Martin', 'Prentice Hall', 2008, 150000, 5, 'cleancode.jpg', 'Panduan menulis kode yang bersih, mudah dibaca, dan mudah dirawat.'),
(4, 'Filosofi Teras', 'Henry Manampiring', 'Kompas', 2019, 88000, 0, 'filosofiteras.jpg', 'Pengantar filsafat Stoa untuk membantu mengelola emosi di kehidupan modern.');

-- ==========================================================
-- AKUN ADMIN
-- Setelah import, buka file buat_admin.php SEKALI lewat browser
-- untuk membuat akun admin (username: admin / password: admin123),
-- lalu hapus/lindungi file tersebut agar tidak disalahgunakan.
-- ==========================================================
