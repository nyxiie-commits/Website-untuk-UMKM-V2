-- KaryaKita: buat / perbarui akun administrator
-- Jalankan pada database aktif if0_43449860_KaryaKita melalui phpMyAdmin.
-- Tidak menghapus atau mengubah tabel/data lain.
INSERT INTO `users` (`name`, `email`, `password`, `role`)
VALUES (
  'Administrator KaryaKita',
  'admin@karyakita.com',
  '$2y$12$kgaHBDrwZe7DaaD06MWz9uPmgqsdLn6X02XvH2mCI5GBtqcDdBjOm',
  'admin'
)
ON DUPLICATE KEY UPDATE
  `name` = VALUES(`name`),
  `password` = VALUES(`password`),
  `role` = 'admin';
