-- KARYAKITA MULTI-VENDOR MIGRATION
-- Jalankan SATU KALI melalui phpMyAdmin pada database aktif setelah backup.
-- Tidak menghapus tabel maupun data transaksi yang sudah ada.

ALTER TABLE users
  MODIFY role ENUM('admin','pelanggan','super_admin','seller','buyer') NOT NULL DEFAULT 'buyer',
  ADD COLUMN store_name VARCHAR(160) NULL AFTER name,
  ADD COLUMN seller_status ENUM('pending','approved','rejected','suspended') NULL DEFAULT NULL AFTER store_name;

UPDATE users SET role='super_admin' WHERE role='admin';
UPDATE users SET role='buyer' WHERE role='pelanggan';

ALTER TABLE users
  MODIFY role ENUM('super_admin','seller','buyer') NOT NULL DEFAULT 'buyer';

ALTER TABLE products
  ADD COLUMN seller_id INT UNSIGNED NULL AFTER category_id,
  ADD INDEX idx_products_seller (seller_id),
  ADD CONSTRAINT fk_products_seller FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE SET NULL;

-- Produk katalog lama tetap dimiliki akun pengelola utama agar tidak hilang.
UPDATE products
SET seller_id = (SELECT id FROM users WHERE role='super_admin' ORDER BY id LIMIT 1)
WHERE seller_id IS NULL;

ALTER TABLE order_items
  ADD COLUMN seller_id INT UNSIGNED NULL AFTER product_id,
  ADD INDEX idx_order_items_seller (seller_id),
  ADD CONSTRAINT fk_order_items_seller FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE SET NULL;

-- Simpan pemilik produk pada detail transaksi lama.
UPDATE order_items oi
LEFT JOIN products p ON p.id = oi.product_id
SET oi.seller_id = p.seller_id
WHERE oi.seller_id IS NULL;

-- Pastikan akun pengelola utama yang sudah ada dapat terus mengakses panel.
