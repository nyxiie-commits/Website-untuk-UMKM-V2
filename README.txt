KARYAKITA - PROFIL TOKO PUBLIK UNTUK PEMBELI

Fitur:
- Detail produk menampilkan kartu penjual dan tombol Lihat Toko.
- Halaman profil publik menampilkan logo, banner, nama, deskripsi, alamat,
  kontak dan semua produk milik penjual tersebut.
- Halaman publik hanya menampilkan akun seller yang sudah approved.
- Produk difilter memakai products.seller_id.

Syarat:
- MIGRASI_MULTI_VENDOR.sql sudah berhasil dijalankan.
- MIGRASI_PROFIL_TOKO.sql sudah berhasil dijalankan.
- Seller sudah disetujui dan profil toko diisi.

Pemasangan:
1. Backup product.php.
2. Upload product.php dan store.php ke root website (htdocs).
3. Timpa product.php lama dan tambahkan store.php.
4. Tidak perlu SQL baru jika dua migrasi di atas sudah dijalankan.

URL profil toko: store.php?id=ID_PENJUAL
