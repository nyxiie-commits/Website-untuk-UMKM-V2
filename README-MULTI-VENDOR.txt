KARYAKITA - PENAMBAHAN FITUR MULTI-VENDOR
=========================================

FITUR
- Tiga role akun: super_admin, seller, buyer.
- Registrasi pembeli dan penjual pada halaman register.php.
- Akun penjual berstatus pending sampai disetujui super admin.
- Super admin mengelola persetujuan penjual melalui admin/penjual.php.
- Penjual mengelola produk sendiri melalui seller/produk.php.
- Penjual melihat pesanan yang memuat produk tokonya melalui seller/pesanan.php.
- Penjual mengunduh laporan detail penjualan produknya dalam XLSX melalui seller/export-penjualan.php.
- Buyer tetap memakai katalog, keranjang, checkout, pembayaran, dan riwayat pesanan yang tersedia.
- Setiap order_items menyimpan seller_id agar penjualan tiap penjual dapat dipisahkan walaupun satu checkout berisi produk lintas toko.

URUTAN PEMASANGAN
1. Backup semua file website dan export database aktif.
2. Upload file dari paket patch ini ke folder htdocs dengan mempertahankan struktur folder.
3. Jalankan MIGRASI_MULTI_VENDOR.sql SATU KALI pada database aktif melalui phpMyAdmin.
4. Logout dari website lalu login kembali agar role sesi diperbarui.
5. Login sebagai akun admin lama (yang dimigrasikan menjadi super_admin).
6. Buka Admin > Penjual untuk menyetujui akun seller yang mendaftar.
7. Uji dengan akun penjual dan pembeli terpisah.

PENTING
- Jangan jalankan database.sql yang lama pada database produksi karena file tersebut berisi DROP TABLE.
- Jangan jalankan MIGRASI_MULTI_VENDOR.sql lebih dari satu kali.
- Produk lama dipertahankan dan akan dimiliki akun super admin pertama.
- Password database tidak perlu dimasukkan ke file patch. Jangan pernah membagikan config.php berisi kredensial.
- Pastikan folder uploads/products dapat ditulis oleh PHP.
- Uji pada salinan/staging sebelum menerapkan ke website live.

BATASAN IMPLEMENTASI VERSI INI
- Super admin tetap menjadi pihak yang memverifikasi pembayaran dan mengubah status pengiriman pada modul pesanan existing.
- Seller dapat melihat data pesanan terkait tokonya, tetapi tidak dapat mengubah status pembayaran/pengiriman seluruh order yang mungkin berisi produk seller lain.
- Checkout masih menggunakan satu pesanan induk untuk semua produk dalam keranjang; rincian item dan nilai penjualan dipisahkan berdasarkan seller_id.
- Pengiriman/ongkir masih mengikuti mekanisme checkout existing.
