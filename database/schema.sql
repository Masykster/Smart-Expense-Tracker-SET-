-- Smart Expense Tracker (SET) - Database Schema
-- Database: expense_tracker

CREATE DATABASE IF NOT EXISTS expense_tracker;
USE expense_tracker;

-- Tabel Utama Transaksi
CREATE TABLE IF NOT EXISTS tb_transaksi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tanggal DATE NOT NULL,
    jenis VARCHAR(10) NOT NULL CHECK (jenis IN ('Masuk', 'Keluar')),
    kategori VARCHAR(50) NOT NULL,
    nominal DOUBLE NOT NULL,
    keterangan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Log Hapus (untuk Trigger)
CREATE TABLE IF NOT EXISTS tb_log_hapus (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_transaksi_lama INT NOT NULL,
    nominal DOUBLE NOT NULL,
    waktu_hapus TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- View: Ringkasan Bulanan
CREATE OR REPLACE VIEW v_ringkasan_bulanan AS
SELECT
    MONTH(tanggal) as bulan,
    YEAR(tanggal) as tahun,
    SUM(CASE WHEN jenis = 'Masuk' THEN nominal ELSE 0 END) as total_masuk,
    SUM(CASE WHEN jenis = 'Keluar' THEN nominal ELSE 0 END) as total_keluar,
    (SUM(CASE WHEN jenis = 'Masuk' THEN nominal ELSE 0 END) - 
     SUM(CASE WHEN jenis = 'Keluar' THEN nominal ELSE 0 END)) as saldo
FROM tb_transaksi 
WHERE MONTH(tanggal) = MONTH(CURRENT_DATE()) 
  AND YEAR(tanggal) = YEAR(CURRENT_DATE())
GROUP BY MONTH(tanggal), YEAR(tanggal);

-- Stored Procedure: Tambah Transaksi
DELIMITER //
CREATE PROCEDURE IF NOT EXISTS sp_tambah_transaksi(
    IN p_tanggal DATE,
    IN p_jenis VARCHAR(10),
    IN p_kategori VARCHAR(50),
    IN p_nominal DOUBLE,
    IN p_keterangan TEXT
)
BEGIN
    DECLARE v_keterangan TEXT;
    
    IF p_keterangan IS NULL OR p_keterangan = '' THEN
        SET v_keterangan = 'Tanpa Keterangan';
    ELSE
        SET v_keterangan = p_keterangan;
    END IF;
    
    INSERT INTO tb_transaksi (tanggal, jenis, kategori, nominal, keterangan)
    VALUES (p_tanggal, p_jenis, p_kategori, p_nominal, v_keterangan);
END //
DELIMITER ;

-- Function: Cek Kesehatan Finansial
DELIMITER //
CREATE FUNCTION IF NOT EXISTS fn_cek_kesehatan_finansial(p_masuk DOUBLE, p_keluar DOUBLE)
RETURNS VARCHAR(20) DETERMINISTIC
BEGIN
    IF p_keluar > p_masuk THEN 
        RETURN 'BAHAYA/BOROS';
    ELSE 
        RETURN 'AMAN/HEMAT';
    END IF;
END //
DELIMITER ;

-- Trigger: Audit Hapus
DELIMITER //
CREATE TRIGGER IF NOT EXISTS tr_audit_hapus
BEFORE DELETE ON tb_transaksi
FOR EACH ROW
BEGIN
    INSERT INTO tb_log_hapus (id_transaksi_lama, nominal, waktu_hapus)
    VALUES (OLD.id, OLD.nominal, NOW());
END //
DELIMITER ;

-- Insert Sample Data (Optional)
INSERT INTO tb_transaksi (tanggal, jenis, kategori, nominal, keterangan) VALUES
('2024-01-15', 'Masuk', 'Gaji', 5000000, 'Gaji bulanan'),
('2024-01-16', 'Keluar', 'Makan', 50000, 'Makan siang'),
('2024-01-17', 'Keluar', 'Transport', 30000, 'Ojek online'),
('2024-01-18', 'Keluar', 'Makan', 75000, 'Dinner'),
('2024-01-19', 'Masuk', 'Bonus', 500000, 'Bonus project');

