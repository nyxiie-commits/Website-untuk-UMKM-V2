KARYAKITA - ROLE IDENTITY UPDATE
================================

Pembaruan ini mengintegrasikan identitas tiga role pada source utama:

1. SUPER ADMIN
   - Identitas emas pada Admin Panel.
   - Label SUPER ADMIN di topbar.
   - Menu Semua Pengguna untuk melihat direktori akun dan role.
   - Ringkasan jumlah Super Admin, Penjual, dan Pembeli.

2. PENJUAL
   - Identitas biru pada Seller Center.
   - Label IDENTITAS PENJUAL di header dan ROLE PENJUAL di dashboard.
   - Navigasi dilengkapi Saldo & Penarikan dan Profil Akun.
   - Shortcut ke Seller Center dan profil toko.

3. PEMBELI
   - Identitas hijau pada header marketplace dan halaman Akun.
   - Halaman Akun menunjukkan label PEMBELI dan menu belanja/pesanan.

PERUBAHAN UTAMA
- functions.php: helper role_meta() dan role_home_path().
- header.php: badge role dan shortcut panel sesuai role.
- account.php: profil dan menu akun dinamis untuk tiga role.
- seller/header.php: identitas penjual, Profil Akun, menu saldo.
- seller/index.php: identitas role di dashboard.
- admin/header.php: identitas Super Admin dan menu Semua Pengguna.
- admin/index.php: heading khusus Super Admin.
- admin/pengguna.php: direktori akun dengan filter role dan pencarian.

DATABASE
Tidak ada migrasi SQL baru. Pembaruan menggunakan kolom role, seller_status,
store_name, seller_id, dan struktur yang sudah ada pada paket ini.

PEMASANGAN
- Source ini adalah paket lengkap hasil pembaruan dari ZIP yang diberikan.
- Upload file/folder sesuai struktur ke document root hosting.
- Bila menggabungkan manual, timpa file yang berubah dan tambahkan admin/pengguna.php.
- Tidak perlu mengimpor database.sql dan tidak perlu menjalankan SQL baru.

CATATAN IMPLEMENTASI
- Badge hanya identitas visual. Otorisasi tetap memakai role database dan
  pemeriksaan need_admin(), need_seller(), need_buyer().
- Role lama 'admin' tetap ditampilkan sebagai SUPER ADMIN untuk kompatibilitas.
- Menu Semua Pengguna bersifat direktori baca-saja; persetujuan penjual tetap
  dilakukan melalui menu Penjual yang sudah tersedia.
