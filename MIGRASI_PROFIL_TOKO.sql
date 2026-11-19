-- MIGRASI PROFIL TOKO KARYAKITA
-- Jalankan SATU KALI pada database KaryaKita setelah backup.
-- Menambahkan detail profil toko ke tabel users tanpa menghapus data lama.

ALTER TABLE users
  ADD COLUMN store_description TEXT NULL AFTER store_name,
  ADD COLUMN store_address VARCHAR(255) NULL AFTER store_description,
  ADD COLUMN store_phone VARCHAR(30) NULL AFTER store_address,
  ADD COLUMN store_logo VARCHAR(255) NULL AFTER store_phone,
  ADD COLUMN store_banner VARCHAR(255) NULL AFTER store_logo;
