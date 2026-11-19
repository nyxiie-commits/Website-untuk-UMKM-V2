KARYAKITA - VALIDASI FOTO PRODUK (MAKSIMAL 2 MB)

Perubahan:
- Produk baru tidak dapat disimpan tanpa memilih file foto.
- Batas ukuran file adalah 2 MB (2.097.152 byte).
- Format yang diterima: JPG/JPEG, PNG, dan WebP.
- Validasi dilakukan di browser (required) dan server (ukuran + MIME).
- Saat mengedit produk, foto lama boleh dipertahankan tanpa upload ulang.
- Jika memilih foto baru saat edit, foto baru juga wajib memenuhi batas 2 MB.
- Berlaku untuk produk penjual dan produk KaryaKita Official di Super Admin.

Pemasangan patch:
1. Backup file seller/produk.php dan admin/produk.php yang sekarang.
2. Upload kedua file dari ZIP patch ke folder yang sama di public_html.
3. Timpa file lama.
4. Tidak perlu menjalankan SQL atau mengubah database.
5. Uji tambah produk tanpa foto (harus ditolak), dengan foto <=2 MB (diterima),
   dan foto >2 MB (harus ditolak).

Catatan:
Jika batas upload PHP hosting lebih rendah dari 2 MB, file yang lebih besar dari
batas hosting akan tetap ditolak oleh server. Batas aplikasi ini tidak menaikkan
batas upload PHP hosting.
