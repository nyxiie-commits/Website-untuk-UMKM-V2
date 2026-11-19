-- AUDIT PESANAN LAMA MULTI-VENDOR
-- Jalankan sebagai SELECT di phpMyAdmin untuk menemukan pesanan lama
-- yang berisi produk dari lebih dari satu pemilik toko.
-- Pesanan campuran lama tidak dapat diverifikasi parsial karena status pembayaran
-- masih berada di level orders. Checkout baru sudah otomatis memisahkan pesanan per toko.

SELECT oi.order_id,
       COUNT(*) AS jumlah_baris_produk,
       COUNT(DISTINCT oi.seller_id) AS jumlah_pemilik,
       GROUP_CONCAT(DISTINCT COALESCE(u.store_name, u.name, 'Pemilik tidak diketahui') SEPARATOR ' | ') AS pemilik_produk
FROM order_items oi
LEFT JOIN users u ON u.id = oi.seller_id
GROUP BY oi.order_id
HAVING COUNT(DISTINCT oi.seller_id) > 1
    OR SUM(CASE WHEN oi.seller_id IS NULL THEN 1 ELSE 0 END) > 0
ORDER BY oi.order_id DESC;
