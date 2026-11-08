-- ==========================================================
-- MIGRASI: tambah metode pembayaran (Transfer / QRIS / COD)
-- Jalankan SEKALI di phpMyAdmin (tab SQL) pada database `bookstore`
-- jika database kamu sudah ada. Pesanan lama otomatis tercatat 'Transfer'.
-- ==========================================================
USE bookstore;

ALTER TABLE tb_order
    ADD COLUMN metode_pembayaran VARCHAR(20) NOT NULL DEFAULT 'Transfer' AFTER total_harga;
