-- db_setup.sql
-- Run this script in your MySQL server to set up tables and seed data.

USE db_sales_app;

SET FOREIGN_KEY_CHECKS = 0;

-- 1. Table: Customer
CREATE TABLE IF NOT EXISTS tabel_customer (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sales_account_id INT NOT NULL,
    nama VARCHAR(100) NOT NULL,
    alamat VARCHAR(255) NOT NULL,
    status ENUM('Follow Up', 'Terjadwal', 'SPK', 'Test Drive') NOT NULL DEFAULT 'Follow Up',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sales_account_id) REFERENCES sales_accounts(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Customer Data
TRUNCATE TABLE tabel_customer;
INSERT INTO tabel_customer (id, sales_account_id, nama, alamat, status) VALUES
(1, 1, 'Bapak Andi', 'Bandung - Coblong', 'Follow Up'),
(2, 1, 'Ibu Sinta', 'Bandung - Antapani', 'Terjadwal'),
(3, 1, 'Bapak Rudi', 'Bandung - Bojongloa Kaler', 'SPK'),
(4, 1, 'Bapak Ahmad', 'Bandung - Antapani', 'Test Drive');

-- 2. Table: Jadwal / Timeline
CREATE TABLE IF NOT EXISTS tabel_jadwal (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sales_account_id INT NOT NULL,
    waktu TIME NOT NULL,
    judul VARCHAR(255) NOT NULL,
    deskripsi TEXT NOT NULL,
    status ENUM('Selesai', 'Terjadwal') DEFAULT 'Terjadwal',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sales_account_id) REFERENCES sales_accounts(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Jadwal Data
TRUNCATE TABLE tabel_jadwal;
INSERT INTO tabel_jadwal (sales_account_id, waktu, judul, deskripsi, status) VALUES
(1, '09:00:00', 'Follow Up Bapak Andi', 'Bapak Andi - Coblong (Avanza)', 'Selesai'),
(1, '11:00:00', 'Test Drive Ibu Sinta', 'Ibu Sinta - Antapani (Raize)', 'Terjadwal'),
(1, '14:00:00', 'Tanda Tangan SPK Bapak Rudi', 'Bapak Rudi - Bojongloa Kaler (Rush)', 'Terjadwal');

-- 3. Table: SPK & Approvals
CREATE TABLE IF NOT EXISTS tabel_spk (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sales_account_id INT NOT NULL,
    nama_customer VARCHAR(100) NOT NULL,
    no_hp VARCHAR(20) NOT NULL,
    model VARCHAR(100) NOT NULL,
    nominal BIGINT NOT NULL,
    tipe_pembelian ENUM('Cash', 'Kredit') NOT NULL,
    status ENUM('Menunggu', 'Disetujui', 'Ditolak') NOT NULL DEFAULT 'Menunggu',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sales_account_id) REFERENCES sales_accounts(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed SPK Data
TRUNCATE TABLE tabel_spk;
INSERT INTO tabel_spk (id, sales_account_id, nama_customer, no_hp, model, nominal, tipe_pembelian, status) VALUES
(1, 1, 'Bapak Rudi', '081234567890', 'Toyota Rush', 312000000, 'Kredit', 'Menunggu'),
(2, 1, 'Ibu Sinta', '082198765432', 'Toyota Raize', 248000000, 'Cash', 'Disetujui'),
(3, 1, 'Bapak Hendra', '083811223344', 'Toyota Avanza', 285000000, 'Kredit', 'Menunggu');

-- 4. Table: Test Drive Units
CREATE TABLE IF NOT EXISTS tabel_test_drive_unit (
    id VARCHAR(50) PRIMARY KEY,
    model VARCHAR(100) NOT NULL,
    type ENUM('AT', 'MT') NOT NULL,
    warna VARCHAR(50) NOT NULL,
    tahun VARCHAR(4) NOT NULL,
    ketersediaan ENUM('tersedia', 'terbatas', 'tidak tersedia') NOT NULL DEFAULT 'tersedia'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Test Drive Units
TRUNCATE TABLE tabel_test_drive_unit;
INSERT INTO tabel_test_drive_unit (id, model, type, warna, tahun, ketersediaan) VALUES
('TD-AVZ-001', 'Avanza 1.5 G', 'AT', 'Silver', '2024', 'tersedia'),
('TD-RAZ-002', 'Raize 1.2 R', 'MT', 'Putih', '2024', 'terbatas'),
('TD-RSH-003', 'Rush 1.5 G', 'AT', 'Merah', '2023', 'tersedia'),
('TD-CLY-004', 'Calya 1.2 E', 'MT', 'Hitam', '2024', 'terbatas');

-- 5. Table: Brosur
CREATE TABLE IF NOT EXISTS tabel_brosur (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    deskripsi VARCHAR(255) NOT NULL,
    pdf_url VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Brosur Data
TRUNCATE TABLE tabel_brosur;
INSERT INTO tabel_brosur (nama, deskripsi, pdf_url) VALUES
('Avanza', 'PDF - Spesifikasi & Promo', '../uploads/brosur_avanza.pdf'),
('Calya', 'PDF - Spesifikasi & Promo', '../uploads/brosur_calya.pdf'),
('Raize', 'PDF - Spesifikasi & Promo', '../uploads/brosur_raize.pdf');

-- 6. Table: Dokumen Customer
CREATE TABLE IF NOT EXISTS tabel_dokumen_customer (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    nama_dokumen VARCHAR(100) NOT NULL,
    status ENUM('Tersimpan', 'Belum Ada', 'Opsional') NOT NULL DEFAULT 'Belum Ada',
    file_path VARCHAR(255) DEFAULT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES tabel_customer(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Dokumen Customer
TRUNCATE TABLE tabel_dokumen_customer;
INSERT INTO tabel_dokumen_customer (customer_id, nama_dokumen, status, file_path) VALUES
(1, 'KTP Customer', 'Tersimpan', 'uploads/ktp_andi.jpg'),
(1, 'NPWP', 'Belum Ada', NULL),
(1, 'Slip Gaji', 'Tersimpan', 'uploads/slip_andi.pdf'),
(1, 'Rekening Koran', 'Opsional', NULL),
(1, 'Surat Keterangan Kerja', 'Opsional', NULL);

-- 7. Table: OLX Trade-In Listings
CREATE TABLE IF NOT EXISTS tabel_trade_in (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sales_account_id INT NOT NULL,
    nama_kendaraan VARCHAR(255) NOT NULL,
    jenis_type VARCHAR(50) NOT NULL,
    tahun INT NOT NULL,
    warna VARCHAR(50) NOT NULL,
    harga_estimasi BIGINT NOT NULL,
    lokasi_kecamatan VARCHAR(100) NOT NULL,
    deskripsi_kondisi TEXT NOT NULL,
    foto_paths TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sales_account_id) REFERENCES sales_accounts(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. Table: Notifikasi
CREATE TABLE IF NOT EXISTS tabel_notifikasi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sales_account_id INT NOT NULL,
    title VARCHAR(100) NOT NULL,
    body TEXT NOT NULL,
    time_label VARCHAR(50) NOT NULL,
    unread BOOLEAN DEFAULT TRUE,
    status_icon VARCHAR(50) DEFAULT 'bell',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sales_account_id) REFERENCES sales_accounts(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Notifikasi Data
TRUNCATE TABLE tabel_notifikasi;
INSERT INTO tabel_notifikasi (sales_account_id, title, body, time_label, unread, status_icon) VALUES
(1, 'Approval Aktivitas', 'Aktivitas Follow Up customer Anda sudah masuk ke Supervisor.', 'Baru saja', 1, 'check-to-slot'),
(1, 'Jadwal Test Drive', 'Reminder: Jadwal Test Drive dengan Ibu Sinta pukul 13.00.', 'Hari ini', 1, 'calendar-days'),
(1, 'Target Hampir Tercapai', 'Sisa 5 unit lagi menuju target DO bulan ini.', 'Kemarin', 1, 'bullseye');

SET FOREIGN_KEY_CHECKS = 1;
