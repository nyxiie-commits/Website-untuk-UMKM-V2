-- Jalankan SATU KALI setelah MIGRASI_MULTI_VENDOR.sql.
-- Tidak menghapus data yang sudah ada.
CREATE TABLE IF NOT EXISTS seller_withdrawals (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  seller_id INT UNSIGNED NOT NULL,
  amount DECIMAL(14,2) NOT NULL,
  bank_name VARCHAR(80) NOT NULL,
  account_number VARCHAR(60) NOT NULL,
  account_holder VARCHAR(120) NOT NULL,
  status ENUM('pending','paid','rejected') NOT NULL DEFAULT 'pending',
  seller_note VARCHAR(500) NULL,
  admin_note VARCHAR(500) NULL,
  requested_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  processed_at DATETIME NULL,
  processed_by INT UNSIGNED NULL,
  PRIMARY KEY (id),
  KEY idx_withdraw_seller_status (seller_id,status),
  KEY idx_withdraw_status_date (status,requested_at),
  CONSTRAINT fk_withdraw_seller FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_withdraw_admin FOREIGN KEY (processed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
