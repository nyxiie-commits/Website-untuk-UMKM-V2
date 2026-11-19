-- KaryaKita: pasangkan foto kategori ke seluruh produk dalam kategori terkait.
-- Jalankan pada database if0_43449860_KaryaKita melalui phpMyAdmin.
-- Aman untuk data produk: hanya memperbarui kolom image.

UPDATE products p
JOIN categories c ON c.id = p.category_id
SET p.image = CASE c.name
    WHEN 'Aksesori Handmade' THEN 'aksesori-handmade.jpg'
    WHEN 'Kerajinan Kayu' THEN 'kerajinan-kayu.jpg'
    WHEN 'Kerajinan Tekstil' THEN 'kerajinan-tekstil.jpg'
    WHEN 'Keramik & Gerabah' THEN 'keramik-gerabah.jpg'
    WHEN 'Lilin & Sabun' THEN 'lilin-sabun.jpg'
    ELSE p.image
END
WHERE c.name IN (
    'Aksesori Handmade',
    'Kerajinan Kayu',
    'Kerajinan Tekstil',
    'Keramik & Gerabah',
    'Lilin & Sabun'
);
