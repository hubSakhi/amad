-- ==========================================================
-- MIGRASI: kategori boleh dihapus tanpa menghapus bukunya
-- Jalankan SEKALI di phpMyAdmin (tab SQL) pada database `bookstore`.
-- Setelah ini, menghapus kategori membuat bukunya tampil tanpa kategori.
-- ==========================================================
USE bookstore;

-- 1) Lepas aturan lama (RESTRICT)
ALTER TABLE tb_buku DROP FOREIGN KEY fk_buku_kategori;

-- 2) Kolom kategori boleh kosong (NULL)
ALTER TABLE tb_buku MODIFY id_kategori INT NULL;

-- 3) Pasang lagi: kategori dihapus => id_kategori buku jadi NULL
ALTER TABLE tb_buku
    ADD CONSTRAINT fk_buku_kategori FOREIGN KEY (id_kategori)
    REFERENCES tb_kategori (id_kategori)
    ON DELETE SET NULL;

-- Kalau langkah 1 error "Can't DROP ... check that it exists", nama aturannya beda.
-- Lihat namanya lewat:  SHOW CREATE TABLE tb_buku;
-- lalu ganti fk_buku_kategori di langkah 1 dengan nama yang tampil di sana.
