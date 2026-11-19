<?php
require 'functions.php';
need_login();

$id = (int)($_GET['id'] ?? 0);
$s = $pdo->prepare('SELECT * FROM orders WHERE id=? AND user_id=?');
$s->execute([$id, user()['id']]);
$o = $s->fetch();
if (!$o) {
    http_response_code(404);
    exit('Pesanan tidak ditemukan.');
}

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_FILES['proof'])) {
        $err = 'File bukti pembayaran belum dipilih.';
    } else {
        $f = $_FILES['proof'];
        $uploadErrors = [
            UPLOAD_ERR_INI_SIZE => 'Ukuran file melebihi batas upload server.',
            UPLOAD_ERR_FORM_SIZE => 'Ukuran file melebihi batas formulir.',
            UPLOAD_ERR_PARTIAL => 'File hanya terunggah sebagian. Coba unggah ulang.',
            UPLOAD_ERR_NO_FILE => 'Pilih file bukti pembayaran terlebih dahulu.',
            UPLOAD_ERR_NO_TMP_DIR => 'Folder sementara upload di server tidak tersedia.',
            UPLOAD_ERR_CANT_WRITE => 'Server gagal menulis file upload ke penyimpanan.',
            UPLOAD_ERR_EXTENSION => 'Upload dihentikan oleh konfigurasi server.'
        ];

        if ($f['error'] !== UPLOAD_ERR_OK) {
            $err = $uploadErrors[$f['error']] ?? 'Upload gagal (kode ' . (int)$f['error'] . ').';
        } else {
            $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
            $allowed = ['image/jpeg'=>'jpg','image/png'=>'png','application/pdf'=>'pdf'];

            if (!isset($allowed[$mime])) {
                $err = 'Format harus JPG, PNG, atau PDF.';
            } elseif ($f['size'] > 3 * 1024 * 1024) {
                $err = 'Ukuran maksimal 3 MB.';
            } else {
                $uploadDir = __DIR__ . '/uploads/payments';
                if (!is_dir($uploadDir)) @mkdir($uploadDir, 0755, true);
                if (is_dir($uploadDir) && !is_writable($uploadDir)) @chmod($uploadDir, 0755);

                if (!is_dir($uploadDir)) {
                    $err = 'Folder uploads/payments belum tersedia. Buat folder tersebut melalui File Manager.';
                } elseif (!is_writable($uploadDir)) {
                    $err = 'Folder uploads/payments tidak memiliki izin tulis. Atur permission folder menjadi 755.';
                } else {
                    $name = 'pay_' . bin2hex(random_bytes(12)) . '.' . $allowed[$mime];
                    $target = $uploadDir . '/' . $name;

                    if (!move_uploaded_file($f['tmp_name'], $target)) {
                        $err = 'Server gagal menyimpan file. Periksa permission folder uploads/payments (755).';
                    } else {
                        try {
                            $s = $pdo->prepare("UPDATE orders
                                SET payment_proof=?, payment_status='menunggu_verifikasi',
                                    order_status='menunggu_verifikasi'
                                WHERE id=? AND user_id=?");
                            $s->execute([$name, $id, user()['id']]);
                            flash('Bukti pembayaran berhasil diunggah. Struk pesanan sudah dapat dicetak. Status pembayaran tetap menunggu verifikasi admin.');
                            go('receipt.php?id=' . $id);
                        } catch (Throwable $e) {
                            @unlink($target);
                            error_log('KaryaKita payment update failed: ' . $e->getMessage());
                            $err = 'File berhasil diunggah, tetapi data pembayaran gagal diperbarui.';
                        }
                    }
                }
            }
        }
    }
}

$pageTitle = 'Pembayaran';
require 'header.php';
?>
<section class="section narrow">
    <h2>Pembayaran Pesanan #<?=e($id)?></h2>
    <div class="panel">
        <h3>Ringkasan Pembayaran</h3><p>Total produk <b style="float:right"><?=rupiah($o['subtotal'] ?? $o['total'])?></b></p><p>Ekspedisi <b style="float:right"><?=e($o['shipping_courier'] ?? '-')?></b></p><p>Biaya ongkir <b style="float:right"><?=rupiah($o['shipping_cost'] ?? 0)?></b></p><h3>Total pembayaran <b style="float:right"><?=rupiah($o['total'])?></b></h3>
        <p>Silakan transfer ke rekening demo: <b>Bank BCA 1234567890 a.n. KaryaKita</b></p>
        <small>Ganti informasi rekening ini dengan rekening resmi pemilik toko sebelum website digunakan.</small>
    </div>
    <?php if ($err): ?><div class="notice error"><?=e($err)?></div><?php endif; ?>
    <form method="post" enctype="multipart/form-data" class="form-card"><?=csrf_field()?>
        <label>Unggah bukti transfer (JPG/PNG/PDF, maks. 3 MB)
            <input type="file" name="proof" accept="image/jpeg,image/png,application/pdf" required>
        </label>
        <button class="btn">Kirim Bukti Pembayaran & Buat Struk</button>
    </form>
</section>
<?php require 'footer.php'; ?>
