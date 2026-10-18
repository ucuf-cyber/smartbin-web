-- =====================================================
-- SmartBin IoT — Database Schema
-- Sesuai BAB XIV PRD SmartBin IoT v1.0
-- =====================================================

CREATE DATABASE IF NOT EXISTS smartbin_db CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE smartbin_db;

-- ---------------------------------------------------
-- Tabel admin
-- ---------------------------------------------------
CREATE TABLE admin (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Akun default: username = admin | password = admin123
-- (password sudah di-hash dengan bcrypt / password_hash())
INSERT INTO admin (username, password) VALUES
('admin', '$2b$10$S1ZJweqShF7Fz4dcJFuVsOnNZ7LNu3BN.HPwVahVFW4E4rqRt2p.q');

-- ---------------------------------------------------
-- Tabel sensor_logs
-- Catatan: kapasitas dipecah jadi 2 kolom karena alat pakai 2 kompartemen
-- terpisah (bin logam & bin non-logam), masing-masing dengan ultrasonik sendiri.
-- ---------------------------------------------------
-- trigger_type membedakan baris "event" (ada barang masuk & dipilah) dari
-- "heartbeat" (update kapasitas rutin tiap interval_send walau tak ada aktivitas).
-- Riwayat di web cuma menampilkan 'event' supaya tidak banjir data heartbeat.
CREATE TABLE sensor_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  capacity_logam INT NOT NULL DEFAULT 0,
  capacity_non_logam INT NOT NULL DEFAULT 0,
  waste_type ENUM('Logam','Non-Logam','Unknown') NOT NULL DEFAULT 'Unknown',
  lid ENUM('Open','Closed') NOT NULL DEFAULT 'Closed',
  status ENUM('Online','Offline') NOT NULL DEFAULT 'Offline',
  trigger_type ENUM('event','heartbeat') NOT NULL DEFAULT 'heartbeat',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_created_at (created_at),
  INDEX idx_waste_type (waste_type),
  INDEX idx_trigger_type (trigger_type)
);

-- ---------------------------------------------------
-- Tabel settings
-- ---------------------------------------------------
CREATE TABLE settings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  warning_limit INT NOT NULL DEFAULT 80,
  critical_limit INT NOT NULL DEFAULT 100,
  delay_close INT NOT NULL DEFAULT 5,
  interval_send INT NOT NULL DEFAULT 5
);

INSERT INTO settings (warning_limit, critical_limit, delay_close, interval_send)
VALUES (80, 100, 5, 5);

-- ---------------------------------------------------
-- (Opsional) Tabel activity_log — untuk audit sesuai BAB XX
-- ---------------------------------------------------
CREATE TABLE activity_log (
  id INT AUTO_INCREMENT PRIMARY KEY,
  admin_id INT NULL,
  activity VARCHAR(255) NOT NULL,
  ip_address VARCHAR(45) NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (admin_id) REFERENCES admin(id) ON DELETE SET NULL
);

-- Catatan: tabel sensor_logs sengaja dibiarkan kosong — data akan terisi
-- otomatis dari alat NodeMCU asli melalui POST /api/send_data.