<?php
declare(strict_types=1);

require_once __DIR__ . '/functions.php';
need_login();

$currentUser = user();
$userId = (int) $currentUser['id'];
$errors = [];
$success = '';

$stmt = $pdo->prepare('SELECT id, name, email, password, role, store_name, seller_status, created_at FROM users WHERE id = ? LIMIT 1');
$stmt->execute([$userId]);
$profile = $stmt->fetch();

if (!$profile) {
    $_SESSION = [];
    session_regenerate_id(true);
    go('login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim((string) ($_POST['name'] ?? ''));
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $currentPassword = (string) ($_POST['current_password'] ?? '');
    $newPassword = (string) ($_POST['new_password'] ?? '');
    $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

    if ($name === '' || strlen($name) > 120) {
        $errors[] = 'Nama wajib diisi dan maksimal 120 karakter.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190) {
        $errors[] = 'Format email tidak valid.';
    } else {
        $check = $pdo->prepare('SELECT id FROM users WHERE email = ? AND id <> ? LIMIT 1');
        $check->execute([$email, $userId]);
        if ($check->fetch()) $errors[] = 'Email tersebut sudah digunakan akun lain.';
    }

    $changingPassword = ($newPassword !== '' || $confirmPassword !== '');
    if ($changingPassword) {
        if (!password_verify($currentPassword, $profile['password'])) {
            $errors[] = 'Password saat ini tidak sesuai.';
        }
        if (strlen($newPassword) < 8) {
            $errors[] = 'Password baru minimal 8 karakter.';
        }
        if ($newPassword !== $confirmPassword) {
            $errors[] = 'Konfirmasi password baru tidak sama.';
        }
    }

    if (!$errors) {
        if ($changingPassword) {
            $update = $pdo->prepare('UPDATE users SET name = ?, email = ?, password = ? WHERE id = ?');
            $update->execute([$name, $email, password_hash($newPassword, PASSWORD_DEFAULT), $userId]);
        } else {
            $update = $pdo->prepare('UPDATE users SET name = ?, email = ? WHERE id = ?');
            $update->execute([$name, $email, $userId]);
        }

        $_SESSION['user']['name'] = $name;
        $_SESSION['user']['email'] = $email;
        session_regenerate_id(true);

        $stmt->execute([$userId]);
        $profile = $stmt->fetch();
        $success = 'Profil berhasil diperbarui.';
    }
}

$roleMeta = role_meta($profile['role']);
$pageTitle = 'Profil Saya';
require __DIR__ . '/header.php';
?>

<style>
.role-identity{display:inline-flex!important;align-items:center;gap:7px;padding:7px 12px;border-radius:999px;font-size:11px;font-weight:800;letter-spacing:.05em}
.role-identity-admin{background:#fff0cf!important;color:#8a5a08!important;border:1px solid #efd18c}
.role-identity-seller{background:#e8f1ff!important;color:#245b9c!important;border:1px solid #c7dcfb}
.role-identity-buyer{background:#e7f6eb!important;color:#237343!important;border:1px solid #c6e8d0}
.account-role-description{font-size:13px;color:#806b55;margin:9px 0 0}
</style>
<section class="account-page">
    <div class="account-heading">
        <div>
            <span class="account-eyebrow">PENGATURAN AKUN</span>
            <h1>Profil Saya</h1>
            <p>Kelola informasi pribadi dan keamanan akun KaryaKita.</p>
        </div>
        <span class="account-role role-identity role-identity-<?= e($roleMeta['tone']) ?>">
            <i class="bi <?= e($roleMeta['icon']) ?>"></i> <?= e($roleMeta['label']) ?>
        </span>
    </div>

    <?php if ($success): ?>
        <div class="account-alert account-alert-success" role="status"><?= e($success) ?></div>
    <?php endif; ?>

    <?php if ($errors): ?>
        <div class="account-alert account-alert-error" role="alert">
            <strong>Periksa kembali data berikut:</strong>
            <ul>
                <?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="account-grid">
        <aside class="account-side-card">
            <div class="account-avatar"><?= e(strtoupper(substr($profile['name'], 0, 1))) ?></div>
            <h2><?= e($profile['name']) ?></h2>
            <p><?= e($profile['email']) ?></p>
            <span class="account-role role-identity role-identity-<?= e($roleMeta['tone']) ?>">
                <i class="bi <?= e($roleMeta['icon']) ?>"></i> <?= e($roleMeta['title']) ?>
            </span>
            <p class="account-role-description"><?= e($roleMeta['description']) ?></p>

            <div class="account-side-links">
                <?php if (in_array($profile['role'], ['super_admin','admin'], true)): ?>
                    <a href="<?= url('admin/index.php') ?>"><span>Dashboard Super Admin</span><span>→</span></a>
                    <a href="<?= url('admin/pengguna.php') ?>"><span>Manajemen Pengguna</span><span>→</span></a>
                    <a href="<?= url('admin/produk.php') ?>"><span>Kelola Produk</span><span>→</span></a>
                    <a href="<?= url('admin/pesanan.php') ?>"><span>Kelola Pesanan</span><span>→</span></a>
                <?php elseif ($profile['role'] === 'seller'): ?>
                    <a href="<?= url('seller/index.php') ?>"><span>Dashboard Penjual</span><span>→</span></a>
                    <a href="<?= url('seller/profil-toko.php') ?>"><span>Profil Toko</span><span>→</span></a>
                    <a href="<?= url('seller/produk.php') ?>"><span>Produk Saya</span><span>→</span></a>
                    <a href="<?= url('seller/saldo.php') ?>"><span>Saldo & Penarikan</span><span>→</span></a>
                <?php else: ?>
                    <a href="<?= url('orders.php') ?>"><span>Pesanan Saya</span><span>→</span></a>
                    <a href="<?= url('cart.php') ?>"><span>Keranjang Belanja</span><span>→</span></a>
                <?php endif; ?>
                <a href="<?= url('index.php') ?>"><span>Kembali ke Beranda</span><span>→</span></a>
                <a href="<?= url('logout.php') ?>" class="account-logout"><span>Keluar dari Akun</span><span>→</span></a>
            </div>
        </aside>

        <section class="account-form-card">
            <div class="account-card-heading">
                <h2>Informasi Pribadi</h2>
                <p>Perbarui nama dan email yang terhubung dengan akun.</p>
            </div>

            <form method="post" action="<?= e(url('account.php')) ?>" class="account-form">
                <?= csrf_field() ?>

                <label for="profile-name">Nama lengkap</label>
                <input id="profile-name" type="text" name="name" maxlength="120" required value="<?= e($_POST['name'] ?? $profile['name']) ?>">

                <label for="profile-email">Alamat email</label>
                <input id="profile-email" type="email" name="email" maxlength="190" required value="<?= e($_POST['email'] ?? $profile['email']) ?>">

                <div class="account-divider"></div>
                <div class="account-card-heading">
                    <h2>Ubah Password</h2>
                    <p>Kosongkan bagian password jika tidak ingin mengubahnya.</p>
                </div>

                <label for="current-password">Password saat ini</label>
                <input id="current-password" type="password" name="current_password" autocomplete="current-password">

                <label for="new-password">Password baru</label>
                <input id="new-password" type="password" name="new_password" minlength="8" autocomplete="new-password">

                <label for="confirm-password">Konfirmasi password baru</label>
                <input id="confirm-password" type="password" name="confirm_password" minlength="8" autocomplete="new-password">

                <button class="btn account-save" type="submit">Simpan Perubahan</button>
            </form>
        </section>
    </div>
</section>

<?php require __DIR__ . '/footer.php'; ?>
