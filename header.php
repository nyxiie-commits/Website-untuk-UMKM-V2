
<?php
// =============================================
// HEADER KARYAKITA
// =============================================

require_once __DIR__ . '/functions.php';

// Jumlah produk dalam keranjang
$cartCount = array_sum($_SESSION['cart'] ?? []);

// Informasi pengguna
$currentUser = user();
$currentRoleMeta = role_meta($currentUser['role'] ?? null);

// Judul halaman
$pageTitle = $pageTitle ?? 'KaryaKita';

// Notifikasi
$notice = flash();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= e($pageTitle) ?> | KaryaKita</title>

    <!-- Bootstrap Icons -->
    <link
        rel="stylesheet"
        href="<?= url('assets/css/bootstrap-icons.min.css') ?>"
    >

    <!-- CSS Utama -->
    <link
        rel="stylesheet"
        href="<?= url('assets/css/style.css') ?>"
    >

    <!-- CSS khusus halaman profil pengguna -->
    <link
        rel="stylesheet"
        href="<?= url('assets/css/account.css') ?>?v=20261117-1"
    >
<style>
.nav-account-link{display:inline-flex;align-items:center;gap:7px}
.role-chip{display:inline-flex;align-items:center;gap:5px;padding:4px 8px;border-radius:999px;font-size:10px;font-weight:800;letter-spacing:.045em;line-height:1.2;white-space:nowrap}
.role-chip-admin{background:#fff0cf;color:#8a5a08;border:1px solid #efd18c}
.role-chip-seller{background:#e8f1ff;color:#245b9c;border:1px solid #c7dcfb}
.role-chip-buyer{background:#e7f6eb;color:#237343;border:1px solid #c6e8d0}
.role-chip-guest{background:#f1f1f1;color:#666;border:1px solid #ddd}
@media(max-width:760px){.role-chip{font-size:9px;padding:3px 6px}}
</style>
</head>

<body>

    <!-- HEADER -->
    <header class="topbar">
        <div class="container">

            <!-- LOGO -->
            <a href="<?= url('index.php') ?>" class="logo">
                <span class="logo-mark">K</span>

                <span class="logo-content">
                    <span class="logo-name">
                        Karya<span>Kita</span>
                    </span>

                    <small>
                        Belanja Lokal, Dukung Karya Indonesia
                    </small>
                </span>
            </a>

            <!-- PENCARIAN -->
            <form
                class="search"
                action="<?= url('index.php#produk') ?>"
                method="GET"
            >
                <input
                    type="search"
                    name="q"
                    placeholder="Cari produk handmade..."
                    value="<?= e($_GET['q'] ?? '') ?>"
                    aria-label="Cari produk handmade"
                >

                <button type="submit" aria-label="Cari">
    <svg
        xmlns="http://www.w3.org/2000/svg"
        width="20"
        height="20"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="2"
        stroke-linecap="round"
        stroke-linejoin="round"
        aria-hidden="true"
    >
        <circle cx="11" cy="11" r="7"></circle>
        <line x1="16.65" y1="16.65" x2="21" y2="21"></line>
    </svg>
</button>
            </form>

            <!-- NAVIGASI -->
            <nav class="nav" aria-label="Navigasi utama">

                <a href="<?= url('index.php') ?>">
                    Beranda
                </a>

                <a href="<?= url('index.php#kategori') ?>">
                    Kategori
                </a>

                <a href="<?= url('cart.php') ?>">
                    Keranjang (<?= (int) $cartCount ?>)
                </a>

                <?php if ($currentUser): ?>

                    <a class="nav-account-link" href="<?= url('account.php') ?>">
                        Akun
                        <span class="role-chip role-chip-<?= e($currentRoleMeta['tone']) ?>">
                            <i class="bi <?= e($currentRoleMeta['icon']) ?>"></i>
                            <?= e($currentRoleMeta['label']) ?>
                        </span>
                    </a>

                    <?php if (in_array($currentUser['role'] ?? '', ['super_admin','admin','seller'], true)): ?>
                        <a href="<?= url(role_home_path($currentUser['role'])) ?>">
                            <?= $currentUser['role'] === 'seller' ? 'Seller Center' : 'Admin Panel' ?>
                        </a>
                    <?php endif; ?>

                    <a href="<?= url('logout.php') ?>">
                        Keluar
                    </a>

                <?php else: ?>

                    <a
                        href="<?= url('login.php') ?>"
                        class="nav-login"
                    >
                        Masuk
                    </a>

                <?php endif; ?>

            </nav>

        </div>
    </header>

    <!-- KONTEN HALAMAN -->
    <main class="container">

        <?php if ($notice): ?>
            <div class="notice">
                <?= e($notice) ?>
            </div>
        <?php endif; ?>
