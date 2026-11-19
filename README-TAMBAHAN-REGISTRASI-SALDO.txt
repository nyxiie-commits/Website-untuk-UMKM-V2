KARYAKITA - TAMBAHAN PILIHAN AKUN & PENARIKAN SALDO PENJUAL

BASIS
Paket ini adalah tambahan untuk KaryaKita_MultiVendor_Patch.zip sebelumnya.
File register.php pada paket Multi-Vendor sebelumnya SUDAH memiliki pilihan
"Pembeli" atau "Penjual" serta input nama toko yang muncul saat memilih Penjual.
Pastikan file register.php dan login.php dari paket Multi-Vendor dipasang.

FITUR SALDO
- Dashboard penjual ditambah menu Saldo & Penarikan.
- Saldo dihitung dari nilai produk milik penjual pada pesanan yang:
  payment_status = 'berhasil' DAN order_status = 'selesai'.
- Permintaan pending dan penarikan paid mengurangi saldo tersedia.
- Penjual mengajukan penarikan dengan bank/e-wallet, nomor akun, dan nama pemilik.
- Super Admin memeriksa permintaan di admin/penarikan.php.
- Super Admin melakukan transfer secara manual, lalu klik "Tandai sudah ditransfer".
- Jika ditolak, dana kembali tersedia karena status rejected tidak mengurangi saldo.

CARA PASANG
1. Backup file dan database.
2. Pastikan paket KaryaKita_MultiVendor_Patch.zip sebelumnya sudah dipasang.
3. Upload folder seller/ dan admin/ dari ZIP ini ke htdocs, gabungkan foldernya.
4. Timpa register.php dengan file dari ZIP ini agar pilihan Pembeli/Penjual tersedia saat daftar.
5. Jalankan MIGRASI_SALDO_PENJUAL.sql satu kali di phpMyAdmin.
6. File seller/header.php dan admin/header.php dalam ZIP ini sudah berisi menu tambahan; upload dan timpa file lama.
7. Tambahkan menu pada seller/header.php:
   'saldo.php'=>['Saldo & Penarikan','bi-wallet2']
   di dalam array $nav.
8. Tambahkan menu pada admin/header.php:
   'penarikan.php' => ['Penarikan Penjual', 'bi-wallet2'],
   di dalam array $adminNav.
9. Uji dengan pesanan baru: pembayaran diverifikasi, pesanan ditandai selesai,
   saldo penjual baru akan bertambah.

PENTING
- Ini adalah pencairan manual, bukan integrasi payment gateway/bank API.
- Jangan menandai Paid sebelum transfer benar-benar dilakukan.
- Ongkir tidak masuk saldo penjual. Saldo hanya berasal dari subtotal produk.
- Belum ada komisi marketplace; seluruh nilai produk menjadi hak penjual.
- Untuk data pesanan lama, seller_id pada order_items harus sudah terisi benar.
- Pastikan admin/penarikan.php hanya dapat diakses super admin melalui auth.php.
