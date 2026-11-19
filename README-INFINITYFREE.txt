KARYAKITA - PAKET PERAPIHAN SOURCE (MVC FOUNDATION)
==================================================

BASIS SOURCE
Paket ini dibuat dari KaryaKita_InfinityFree_v11_ekspedisi_ongkir.zip, karena versi tersebut memuat fitur ekspedisi dan ongkir yang paling baru di antara source yang tersedia.

STACK ACUAN
- Backend: PHP 8+ Native, fondasi MVC sederhana, PDO.
- Database: MySQL/MariaDB, utf8mb4, prepared statements.
- Frontend: HTML5, CSS3, Bootstrap 5, Bootstrap Icons, Chart.js (tersedia untuk dashboard admin).
- Keamanan: password_hash/password_verify, PDO prepared statements, CSRF untuk form POST, XSS escaping helper, pemeriksaan role admin, session cookie flags, session regeneration pada login, pembatasan percobaan login berbasis sesi.

PENTING SEBELUM UPLOAD
1. Backup file hosting dan database terlebih dahulu.
2. Buka config.php dan ganti DB_PASS dengan password MySQL BARU milik akun hosting. Placeholder tidak akan bisa dipakai untuk koneksi.
3. Pastikan BASE_URL sesuai domain aktif. Default paket: https://karyakita.gt.tc
4. Jangan mengimpor database.sql ke database produksi. File itu hanya referensi struktur awal; database produksi sudah memiliki data. Jangan jalankan DROP TABLE.
5. Jangan upload ulang isi uploads/payments dari backup lama. File bukti pembayaran pelanggan tidak disertakan dalam paket ini demi privasi.

CARA PASANG AMAN DI INFINITYFREE
1. Jangan langsung menimpa website aktif. Buat backup zip file htdocs dan export database.
2. Ekstrak paket di komputer. Periksa config.php dan isi password baru secara lokal. Jangan kirim file config.php berisi password ke siapa pun.
3. Untuk update langsung, upload file ke htdocs hanya setelah backup. Pertahankan folder uploads/products yang berisi gambar katalog.
4. Jangan menghapus atau mengganti database. Paket ini tidak memerlukan migrasi SQL untuk struktur data yang sudah digunakan v11.
5. Uji login, katalog, keranjang, checkout, pilihan ekspedisi, unggah bukti, struk, dan dashboard admin.

CATATAN STATUS ARSITEKTUR
- File route PHP lama (index.php, login.php, checkout.php, dan seterusnya) dipertahankan agar URL dan fitur existing tidak putus.
- app/Core, app/Models, app/Controllers, dan app/Views sudah disediakan sebagai fondasi MVC yang dapat dipakai untuk migrasi halaman secara bertahap.
- Ini adalah paket refaktor fondasi/kompatibilitas, bukan klaim bahwa seluruh halaman lama sudah dipindahkan ke MVC.
- Rekomendasi produk saat ini masih berupa skor sederhana berdasarkan kesamaan kategori dan rentang harga. Metode penelitian final perlu disesuaikan dengan keputusan metodologi skripsi.
- Tarif ekspedisi checkout tetap tarif simulasi Jabodetabek, bukan tarif API real-time.

STRUKTUR UTAMA
admin/       Halaman pengelolaan admin
app/         Fondasi MVC (Core, Controllers, Models, Views)
assets/      CSS dan aset frontend
config.php   Konfigurasi koneksi database (isi DB_PASS sendiri)
public/      Area aset publik untuk migrasi berikutnya
storage/     Area log/cache (tidak boleh diakses publik)
uploads/     Gambar katalog dan unggahan pembayaran

KEAMANAN
- Jangan pernah memasukkan password database ke file yang dibagikan atau repo publik.
- Jika password pernah ikut terunggah dalam ZIP, rotasi password database (sudah dilakukan pada sesi ini).
- Hapus file backup yang mengandung kredensial lama dari folder publik hosting.
