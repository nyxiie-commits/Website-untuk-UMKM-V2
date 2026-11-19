# KaryaKita

**KaryaKita** adalah platform marketplace produk handmade yang menghubungkan pembeli dengan pelaku UMKM. Platform ini mendukung model **multi-vendor**, sehingga setiap penjual dapat mengelola toko, produk, pesanan, laporan penjualan, dan saldo secara mandiri.

> Belanja Lokal, Dukung Karya Indonesia.

## Fitur Utama

### 1. Multi-role account

KaryaKita memiliki tiga jenis akun dengan ruang kerja dan hak akses berbeda:

- **Super Admin** — mengelola pengguna, menyetujui penjual, kategori, produk KaryaKita Official, pesanan Official, dan permintaan penarikan saldo.
- **Penjual (Seller)** — mengelola profil toko, produk, stok, pesanan toko, laporan penjualan, saldo, dan permintaan penarikan.
- **Pembeli (Buyer)** — melihat katalog, mencari produk, memasukkan produk ke keranjang, checkout, mengunggah bukti pembayaran, dan melihat riwayat pesanan.

Pendaftaran penjual menggunakan status menunggu persetujuan. Toko yang belum disetujui tidak ditampilkan sebagai toko publik di marketplace.

### 2. Fitur penjual

- Dashboard Seller Center.
- Tambah, edit, lihat, dan hapus produk milik toko sendiri.
- Pengelolaan kategori dan stok produk.
- Profil publik toko yang dapat dilihat pembeli.
- Pengelolaan pesanan yang berasal dari toko sendiri.
- Verifikasi atau penolakan bukti pembayaran.
- Pembaruan status pesanan hingga proses pengiriman dan selesai.
- Unduh laporan penjualan dalam format Excel `.xlsx`.
- Informasi saldo dan pengajuan penarikan saldo.

### 3. Fitur pembeli

- Katalog produk handmade.
- Pencarian dan filter kategori.
- Detail produk dan rekomendasi produk terkait.
- Identitas toko pada halaman produk.
- Profil publik penjual.
- Keranjang dan checkout.
- Pilihan ekspedisi dan perhitungan ongkos kirim sesuai tarif simulasi aplikasi.
- Unggah bukti pembayaran.
- Riwayat pesanan dan akses struk.

### 4. KaryaKita Official

Produk yang dimiliki akun pengelola marketplace ditampilkan dengan identitas **KaryaKita Official**. Produk penjual menampilkan nama toko masing-masing.

Transaksi produk KaryaKita Official diproses oleh Super Admin, sedangkan transaksi produk toko mitra diproses oleh penjual terkait.

### 5. Pemisahan pesanan multi-vendor

Jika keranjang berisi produk dari beberapa toko, sistem checkout membuat pesanan terpisah berdasarkan pemilik produk. Dengan demikian:

- Penjual hanya mengelola pesanan milik tokonya.
- Super Admin hanya memproses pesanan produk KaryaKita Official.
- Pembeli dapat melihat setiap pesanan secara terpisah.

### 6. Validasi foto produk

- Foto wajib diunggah ketika menambahkan produk baru.
- Ukuran maksimal file adalah **2 MB**.
- Format yang diterima: JPG/JPEG, PNG, dan WebP.
- Validasi ukuran dan tipe file dilakukan di sisi server.
- Saat mengedit produk, foto lama dapat dipertahankan tanpa mengunggah ulang.

## Teknologi

| Bagian | Teknologi |
|---|---|
| Backend | PHP Native |
| Database | MySQL / MariaDB |
| Database access | PDO |
| Frontend | HTML5, CSS3, JavaScript |
| UI framework | Bootstrap 5 |
| Icons | Bootstrap Icons |
| Dashboard charts | Chart.js |
| Export laporan | XLSX |
| Local development | XAMPP |
| Deployment | InfinityFree / hosting PHP dan MySQL |

Source juga menyediakan fondasi MVC sederhana di direktori `app/` untuk pengembangan dan pemindahan halaman secara bertahap. Halaman utama yang berjalan masih menggunakan route PHP native di root, `admin/`, dan `seller/`.

## Struktur Direktori

```text
KaryaKita/
├── admin/                  # Panel Super Admin
├── app/                    # Fondasi MVC (Core, Controller, Model, View)
├── assets/                 # CSS dan aset frontend
├── config/                 # File pendukung konfigurasi
├── seller/                 # Seller Center
├── storage/                # Area penyimpanan runtime
├── uploads/
│   ├── products/           # Foto produk
│   ├── store/              # Logo dan banner toko
│   └── payments/           # Bukti pembayaran (tidak dilacak Git)
├── account.php             # Halaman akun
├── all-products.php        # Katalog produk
├── cart.php                # Keranjang
├── category.php            # Produk berdasarkan kategori
├── checkout.php            # Checkout multi-vendor
├── config.example.php      # Template konfigurasi database
├── database.sql            # Skema dasar legacy untuk database kosong
├── functions.php           # Helper aplikasi
├── index.php               # Beranda marketplace
├── login.php               # Login
├── register.php            # Registrasi buyer/seller
├── orders.php              # Riwayat pesanan pembeli
├── product.php             # Detail produk
├── receipt.php             # Struk pesanan
└── store.php               # Profil publik toko
```

## Instalasi Lokal

### Persyaratan

- PHP 8.0 atau lebih baru.
- MySQL atau MariaDB.
- Apache/Nginx.
- Ekstensi PHP PDO MySQL, Fileinfo, dan Zlib.
- XAMPP dapat digunakan untuk menjalankan aplikasi secara lokal.

### Langkah instalasi

1. Clone repository:

   ```bash
   git clone https://github.com/USERNAME/KaryaKita.git
   ```

2. Pindahkan folder project ke direktori web server, misalnya `htdocs` pada XAMPP.

3. Buat database kosong melalui phpMyAdmin.

4. **Untuk database pengembangan yang benar-benar kosong saja**, impor `database.sql` sebagai skema dasar. File ini merupakan skema legacy dan mengandung perintah `DROP TABLE`; jangan pernah menjalankannya pada database yang sudah berisi data atau database hosting aktif.

5. Jalankan migrasi satu kali secara berurutan pada database pengembangan:

   - `KaryaKita_Migrasi_Ekspedisi_Ongkir.sql`
   - `MIGRASI_MULTI_VENDOR.sql`
   - `MIGRASI_PROFIL_TOKO.sql`
   - `MIGRASI_SALDO_PENJUAL.sql`

6. Jika ingin memasangkan foto default kategori pada data produk, periksa lalu jalankan `update_foto_produk.sql` sesuai kebutuhan.

7. Salin `config.example.php` menjadi `config.php`, kemudian sesuaikan konfigurasi database:

   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'nama_database');
   define('DB_USER', 'username_database');
   define('DB_PASS', 'password_database');
   define('DB_CHARSET', 'utf8mb4');
   define('BASE_URL', '/KaryaKita');
   ```

   Jika aplikasi dijalankan pada root domain, `BASE_URL` dapat dikosongkan sesuai konfigurasi aplikasi.

8. Pastikan direktori `uploads/products`, `uploads/store`, dan `uploads/payments` tersedia serta dapat ditulisi oleh PHP.

9. Jalankan Apache dan MySQL melalui XAMPP, lalu buka aplikasi melalui browser.

> **Penting:** File migrasi ditujukan untuk skema dasar yang sesuai dengan versi project ini. Jangan menjalankan migrasi berulang kali. Untuk database yang sudah berjalan, backup database dan periksa struktur tabel terlebih dahulu.

## Akun dan Alur Penggunaan

1. Pembeli mendaftar sebagai **Pembeli** dan dapat langsung menggunakan fitur belanja.
2. Pengguna yang mendaftar sebagai **Penjual** mengisi nama toko dan menunggu persetujuan Super Admin.
3. Super Admin menyetujui pendaftaran penjual melalui menu pengelolaan penjual.
4. Setelah disetujui, penjual dapat mengelola produk dan profil tokonya.
5. Pembeli melakukan checkout. Jika produk berasal dari beberapa toko, pesanan dipisahkan berdasarkan pemilik produk.
6. Penjual memverifikasi pembayaran dan memproses pesanan tokonya.
7. Setelah pesanan memenuhi ketentuan penyelesaian, pendapatan toko masuk ke perhitungan saldo penjual.
8. Penjual dapat mengajukan penarikan saldo, kemudian permintaan diproses Super Admin.

## Catatan Implementasi

- Tarif ekspedisi dan ongkos kirim pada source ini merupakan tarif simulasi aplikasi, bukan integrasi tarif kurir real-time.
- Rekomendasi produk pada source menggunakan pencocokan sederhana berdasarkan kategori dan rentang harga.
- Pesanan lama yang dibuat sebelum pemisahan checkout multi-vendor perlu diaudit apabila berisi produk dari beberapa pemilik. Gunakan `AUDIT_PESANAN_LAMA_MULTI_VENDOR.sql` untuk pemeriksaan.
- `database.sql` hanya untuk skema dasar database pengembangan yang kosong. Jangan impor ke database produksi.
- File bukti pembayaran pengguna tidak disertakan dalam repository untuk menjaga privasi.

## Deployment

Untuk deployment pada hosting PHP/MySQL seperti InfinityFree:

1. Siapkan database MySQL melalui panel hosting.
2. Sesuaikan `config.php` pada server menggunakan kredensial database hosting.
3. Upload source ke document root hosting dengan struktur direktori tetap dipertahankan.
4. Pastikan folder upload dapat ditulisi oleh PHP.
5. Jalankan migrasi hanya jika struktur database hosting belum memiliki kolom/tabel yang dibutuhkan dan setelah backup.
6. Uji login setiap role, registrasi penjual, persetujuan toko, upload produk, checkout, upload bukti, verifikasi pesanan, ekspor XLSX, dan penarikan saldo.

## Kontribusi

Pengembangan dan perbaikan fitur dapat dilakukan melalui branch atau pull request. Sertakan penjelasan perubahan dan langkah pengujian agar perubahan mudah ditinjau.

## Lisensi

Lisensi belum ditentukan. Hubungi pemilik repository sebelum menggunakan ulang atau mendistribusikan source untuk keperluan di luar pembelajaran/pengembangan project.
