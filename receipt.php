
<?php
require_once 'functions.php';

need_login();

// ========================================
// 1. AMBIL DATA PESANAN
// ========================================

$id = (int) ($_GET['id'] ?? 0);

$sql = "
    SELECT
        o.*,
        u.name AS customer,
        u.email
    FROM orders o
    JOIN users u ON u.id = o.user_id
    WHERE o.id = ?
";

$params = [$id];

// Pembeli hanya melihat struk miliknya. Penjual hanya melihat pesanan tokonya.
// Super Admin hanya melihat transaksi produk KaryaKita Official.
$receiptRole = user()['role'] ?? '';
if (in_array($receiptRole, ['super_admin','admin'], true)) {
    $officialId = official_seller_id($pdo);
    $sql .= " AND EXISTS (SELECT 1 FROM order_items oi WHERE oi.order_id=o.id)
              AND NOT EXISTS (SELECT 1 FROM order_items oi WHERE oi.order_id=o.id AND (oi.seller_id IS NULL OR oi.seller_id<>?))";
    $params[] = $officialId;
} elseif ($receiptRole === 'seller') {
    $sql .= " AND EXISTS (SELECT 1 FROM order_items oi WHERE oi.order_id=o.id)
              AND NOT EXISTS (SELECT 1 FROM order_items oi WHERE oi.order_id=o.id AND (oi.seller_id IS NULL OR oi.seller_id<>?))";
    $params[] = (int)user()['id'];
} else {
    $sql .= " AND o.user_id = ?";
    $params[] = user()['id'];
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    http_response_code(404);
    exit('Pesanan tidak ditemukan.');
}

if (empty($order['payment_proof'])) {
    http_response_code(403);
    exit('Struk tersedia setelah bukti pembayaran berhasil diunggah.');
}

// ========================================
// 2. AMBIL DETAIL PRODUK PESANAN
// ========================================

$stmt = $pdo->prepare("
    SELECT product_name, price, quantity
    FROM order_items
    WHERE order_id = ?
");

$stmt->execute([$id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ========================================
// 3. HITUNG TOTAL PEMBAYARAN
// ========================================

$calculatedSubtotal = array_reduce(
    $items,
    function ($sum, $item) {
        return $sum + (
            (float) $item['price'] *
            (int) $item['quantity']
        );
    },
    0
);

$subtotal = isset($order['subtotal'])
    ? (float) $order['subtotal']
    : $calculatedSubtotal;

$shipping = (float) ($order['shipping_cost'] ?? 0);
$total = (float) $order['total'];

$courier = $order['shipping_courier'] ?? 'Belum dipilih';

$isVerified = ($order['payment_status'] === 'berhasil');

// ========================================
// 4. FORMAT TANGGAL
// ========================================

function receipt_date($date)
{
    if (empty($date)) {
        return '-';
    }

    return date('d-m-Y H:i', strtotime($date));
}

// ========================================
// 5. FORMAT STATUS
// ========================================

$status = str_replace(
    '_',
    ' ',
    $order['payment_status'] ?? 'menunggu verifikasi'
);

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        Struk Pesanan #<?= e($order['id']) ?> | KaryaKita
    </title>

    <style>
        /* =================================
           RESET DAN DASAR TAMPILAN
        ================================= */

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 30px 16px;
            background: #f7f4eb;
            color: #382719;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 14px;
            line-height: 1.5;
        }

        /* =================================
           KERTAS STRUK
        ================================= */

        .receipt {
            width: 100%;
            max-width: 720px;
            margin: 0 auto;
            padding: 34px;
            background: #ffffff;
            border: 1px solid #eadfc9;
            border-radius: 14px;
        }

        /* =================================
           HEADER / IDENTITAS TOKO
        ================================= */

        .brand {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 15px;
            padding-bottom: 18px;
            border-bottom: 2px solid #f3a21b;
        }

        .brand h1 {
            margin: 0;
            color: #9b5b12;
            font-size: 28px;
        }

        .brand p {
            margin: 5px 0;
            color: #806b55;
        }

        .brand-title {
            font-weight: bold;
            font-size: 13px;
        }

        .payment-badge {
            padding: 9px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            white-space: nowrap;
            background: <?= $isVerified ? '#e7f7e9' : '#fff2cc' ?>;
            color: <?= $isVerified ? '#176b2c' : '#8a5a16' ?>;
        }

        /* =================================
           INFORMASI PESANAN
        ================================= */

        .meta {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            margin: 22px 0;
        }

        .meta-item {
            min-width: 0;
            padding: 12px;
            background: #fff9e9;
            border-radius: 8px;
            overflow-wrap: anywhere;
        }

        .meta-item.full {
            grid-column: 1 / -1;
        }

        .meta-item small {
            display: block;
            margin-bottom: 5px;
            color: #806b55;
        }

        .meta-item strong {
            display: block;
            color: #382719;
        }

        /* =================================
           TABEL PRODUK
        ================================= */

        .product-table {
            width: 100%;
            margin: 20px 0;
            border-collapse: collapse;
        }

        .product-table th,
        .product-table td {
            padding: 12px 8px;
            text-align: left;
            border-bottom: 1px solid #eeeeee;
        }

        .product-table th {
            font-weight: bold;
        }

        .product-table th:last-child,
        .product-table td:last-child {
            text-align: right;
        }

        .product-table th:nth-child(3),
        .product-table td:nth-child(3) {
            text-align: center;
        }

        /* =================================
           RINGKASAN PEMBAYARAN
        ================================= */

        .summary {
            width: 100%;
            max-width: 360px;
            margin: 18px 0 10px auto;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            margin: 9px 0;
        }

        .summary-row strong {
            text-align: right;
        }

        .total {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            padding: 12px 0;
            border-top: 1px solid #eadfc9;
            color: #9b5b12;
            font-size: 21px;
            font-weight: bold;
        }

        /* =================================
           CATATAN STRUK
        ================================= */

        .foot {
            padding-top: 16px;
            border-top: 1px dashed #d8c7a8;
            color: #806b55;
            text-align: center;
            font-size: 13px;
        }

        /* =================================
           TOMBOL AKSI
        ================================= */

        .actions {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            margin: 22px auto;
        }

        .btn {
            display: inline-block;
            padding: 12px 22px;
            border: 0;
            border-radius: 30px;
            font-size: 14px;
            font-weight: bold;
            text-decoration: none;
            text-align: center;
            cursor: pointer;
            transition: 0.2s;
        }

        .btn-print {
            background: #f3a21b;
            color: #382719;
        }

        .btn-print:hover {
            background: #e18d0c;
        }

        .btn-home {
            background: #fff1c2;
            color: #633e20;
            border: 1px solid #f0d9a5;
        }

        .btn-home:hover {
            background: #ffe28a;
        }

        /* =================================
           RESPONSIVE MOBILE
        ================================= */

        @media (max-width: 600px) {
            body {
                padding: 12px 8px;
            }

            .receipt {
                padding: 18px 14px;
            }

            .brand {
                flex-direction: column;
            }

            .brand h1 {
                font-size: 24px;
            }

            .meta {
                grid-template-columns: 1fr;
            }

            .meta-item.full {
                grid-column: auto;
            }

            .product-table th,
            .product-table td {
                padding: 9px 4px;
                font-size: 12px;
            }

            .total {
                font-size: 17px;
            }

            .actions {
                flex-direction: column;
                padding: 0 8px;
            }

            .btn {
                width: 100%;
            }
        }

        /* =================================
           MODE CETAK / SIMPAN PDF
        ================================= */

        @media print {
            @page {
                size: A4;
                margin: 12mm;
            }

            body {
                padding: 0;
                background: #ffffff;
            }

            .receipt {
                max-width: 100%;
                padding: 12px;
                border: 0;
                border-radius: 0;
            }

            .actions {
                display: none !important;
            }

            .meta-item,
            .payment-badge {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .product-table tr {
                page-break-inside: avoid;
            }
        }
    </style>
</head>

<body>

    <main class="receipt">

        <!-- HEADER -->
        <header class="brand">

            <div>
                <h1>KaryaKita</h1>

                <p>Belanja Lokal, Dukung Karya Indonesia</p>

                <div class="brand-title">
                    STRUK PESANAN / PEMBAYARAN
                </div>
            </div>

            <span class="payment-badge">
                <?= $isVerified ? 'LUNAS / TERVERIFIKASI' : 'BUKTI DIUNGGAH' ?>
            </span>

        </header>

        <!-- INFORMASI PESANAN -->
        <section class="meta">

            <div class="meta-item">
                <small>Nomor Pesanan</small>
                <strong>#<?= e($order['id']) ?></strong>
            </div>

            <div class="meta-item">
                <small>Tanggal Pesanan</small>
                <strong><?= e(receipt_date($order['created_at'])) ?></strong>
            </div>

            <div class="meta-item">
                <small>Nama Pelanggan</small>
                <strong><?= e($order['customer']) ?></strong>
            </div>

            <div class="meta-item">
                <small>Metode Pembayaran</small>
                <strong><?= e($order['payment_method']) ?></strong>
            </div>

            <div class="meta-item">
                <small>Ekspedisi</small>
                <strong><?= e($courier) ?></strong>
            </div>

            <div class="meta-item">
                <small>Nomor Telepon</small>
                <strong><?= e($order['phone']) ?></strong>
            </div>

            <div class="meta-item full">
                <small>Alamat Pengiriman</small>
                <strong><?= e($order['address']) ?></strong>
            </div>

            <div class="meta-item">
                <small>Email</small>
                <strong><?= e($order['email']) ?></strong>
            </div>

            <div class="meta-item">
                <small>Status Pembayaran</small>
                <strong><?= e($status) ?></strong>
            </div>

        </section>

        <!-- DAFTAR PRODUK -->
        <section>

            <table class="product-table">

                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Harga</th>
                        <th>Qty</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>

                <tbody>

                    <?php foreach ($items as $item): ?>

                        <tr>
                            <td><?= e($item['product_name']) ?></td>

                            <td><?= rupiah($item['price']) ?></td>

                            <td><?= e($item['quantity']) ?></td>

                            <td>
                                <?= rupiah(
                                    $item['price'] * $item['quantity']
                                ) ?>
                            </td>
                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        </section>

        <!-- RINCIAN PEMBAYARAN -->
        <section class="summary">

            <div class="summary-row">
                <span>Total produk</span>
                <strong><?= rupiah($subtotal) ?></strong>
            </div>

            <div class="summary-row">
                <span>Ongkos kirim (<?= e($courier) ?>)</span>
                <strong><?= rupiah($shipping) ?></strong>
            </div>

        </section>

        <div class="total">
            <span>Total pembayaran</span>
            <span><?= rupiah($total) ?></span>
        </div>

        <!-- CATATAN -->
        <footer class="foot">

            <?php if ($isVerified): ?>

                Pembayaran telah diverifikasi oleh admin KaryaKita.

            <?php else: ?>

                Bukti pembayaran telah diunggah.
                Status pembayaran masih menunggu verifikasi admin.
                Dokumen ini merupakan struk pesanan dan bukan
                konfirmasi bahwa dana telah diterima.

            <?php endif; ?>

            <br>

            Terima kasih telah mendukung produk handmade UMKM Indonesia.

        </footer>

    </main>

    <!-- TOMBOL AKSI -->
    <div class="actions">

        <button
            type="button"
            class="btn btn-print"
            onclick="window.print()"
        >
            Cetak / Simpan sebagai PDF
        </button>

        <a href="index.php" class="btn btn-home">
            &larr; Kembali ke Beranda
        </a>

    </div>

</body>
</html>
