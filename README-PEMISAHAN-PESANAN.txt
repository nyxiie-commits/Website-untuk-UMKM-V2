KARYAKITA - PEMISAHAN VERIFIKASI PESANAN MULTI-VENDOR
=====================================================

PERILAKU BARU
1. Checkout memisahkan keranjang menjadi satu pesanan untuk setiap pemilik produk.
   Contoh: keranjang berisi produk Tukang Mabel dan KaryaKita Official akan
   menghasilkan dua nomor pesanan yang terpisah.
2. Pembeli mengunggah bukti pembayaran per nomor pesanan melalui Pesanan Saya.
3. Pesanan produk penjual hanya muncul di Seller Center milik penjual tersebut.
   Penjual dapat melihat bukti, menyetujui/menolak pembayaran, menandai dikirim,
   dan menyelesaikan pesanan.
4. Pesanan produk KaryaKita Official hanya muncul di Admin Panel > Pesanan Official.
   Super Admin memproses transaksi produk resmi KaryaKita.
5. Produk yang dikelola Super Admin diberi identitas "KaryaKita Official".
   Produk penjual menampilkan "Dijual oleh [Nama Toko]" pada katalog dan detail.
6. Admin > Produk sekarang hanya mengelola inventaris resmi KaryaKita Official,
   bukan produk yang dimiliki penjual.
7. Struk dibatasi sesuai pemilik pesanan: pembeli untuk pesanan miliknya,
   penjual untuk pesanan tokonya, Super Admin untuk transaksi Official.

PEMILIK PRODUK
- products.seller_id adalah pemilik produk.
- order_items.seller_id menyimpan snapshot pemilik saat transaksi dibuat.
- Produk dengan pemilik akun role super_admin/admin ditampilkan sebagai KaryaKita Official.
- Produk penjual ditampilkan dengan nama toko (fallback ke nama pemilik akun).

DATABASE
Tidak ada perubahan struktur database baru pada pembaruan ini. Implementasi memakai
products.seller_id dan order_items.seller_id yang sudah disediakan oleh migrasi
multi-vendor sebelumnya. Jangan impor database.sql ke database produksi.

PEMASANGAN
1. Backup source dan database.
2. Upload/replace file dari ZIP sesuai struktur direktori.
3. Tidak perlu menjalankan migrasi SQL baru untuk checkout dan pemisahan role.
4. Jalankan AUDIT_PESANAN_LAMA_MULTI_VENDOR.sql sebagai SELECT untuk memeriksa
   pesanan lama yang sudah terlanjur mencampur produk dari beberapa pemilik.
5. Uji dengan dua produk: satu milik akun penjual dan satu milik KaryaKita Official.
   Checkout harus menghasilkan dua pesanan berbeda.

CATATAN PESANAN LAMA
Pesanan yang dibuat sebelum pembaruan mungkin masih berisi beberapa pemilik produk
dalam satu nomor pesanan. Karena payment_status dan order_status lama tersimpan pada
level orders, pesanan campuran lama tidak ditampilkan pada antrean verifikasi seller
atau Official agar satu pihak tidak bisa memverifikasi barang milik pihak lain.
Periksa hasil audit SQL untuk menentukan penanganan data historis secara manual.

PENGUJIAN
File PHP yang diubah harus lolos php -l. Uji fungsional di hosting tetap diperlukan
untuk memastikan koneksi database, upload bukti, dan alur pembayaran sesuai data
produksi dan konfigurasi server.
