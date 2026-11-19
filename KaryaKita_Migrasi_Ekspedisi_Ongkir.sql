-- KaryaKita: migrasi pilihan ekspedisi dan rincian ongkos kirim
-- Jalankan SATU KALI melalui phpMyAdmin pada database KaryaKita.
-- Tidak menghapus data pesanan atau produk yang sudah ada.
ALTER TABLE orders
    ADD COLUMN subtotal DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER total,
    ADD COLUMN shipping_courier VARCHAR(80) NOT NULL DEFAULT 'Belum dipilih' AFTER subtotal,
    ADD COLUMN shipping_cost DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER shipping_courier;

-- Isi subtotal pesanan lama dari detail item agar rincian lama tetap masuk akal.
UPDATE orders o
LEFT JOIN (
    SELECT order_id, SUM(price * quantity) AS item_subtotal
    FROM order_items
    GROUP BY order_id
) x ON x.order_id = o.id
SET o.subtotal = COALESCE(x.item_subtotal, o.total),
    o.shipping_cost = 0
WHERE o.subtotal = 0;
