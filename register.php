<?php
require 'functions.php';

if (user()) go('index.php');

$err = '';
$selectedRole = $_POST['role'] ?? ($_GET['type'] ?? 'buyer');
if (!in_array($selectedRole, ['buyer', 'seller'], true)) $selectedRole = 'buyer';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $role = $selectedRole;
    $store = trim($_POST['store_name'] ?? '');

    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        strlen($password) < 8 || ($role === 'seller' && $store === '')) {
        $err = 'Periksa kembali nama, email, password minimal 8 karakter, dan nama toko untuk penjual.';
    } else {
        try {
            // Periksa email secara spesifik agar tidak semua error database
            // keliru ditampilkan sebagai "email sudah digunakan".
            $check = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
            $check->execute([$email]);

            if ($check->fetchColumn()) {
                $err = 'Email ini sudah terdaftar. Gunakan email lain atau masuk ke akun yang sudah ada.';
            } else {
                $insert = $pdo->prepare(
                    'INSERT INTO users (name, email, password, role, store_name, seller_status)
                     VALUES (?, ?, ?, ?, ?, ?)'
                );
                $insert->execute([
                    $name,
                    $email,
                    password_hash($password, PASSWORD_DEFAULT),
                    $role,
                    $role === 'seller' ? $store : null,
                    $role === 'seller' ? 'pending' : null
                ]);

                flash(
                    $role === 'seller'
                        ? 'Pendaftaran penjual berhasil. Tunggu persetujuan Super Admin sebelum mulai berjualan.'
                        : 'Registrasi pembeli berhasil. Silakan masuk.'
                );
                unset($_SESSION['register_old']);
                go('login.php');
            }
        } catch (PDOException $e) {
            error_log('[KaryaKita register] ' . $e->getMessage());
            $driverCode = (int)($e->errorInfo[1] ?? 0);

            if ($driverCode === 1062) {
                $err = 'Email ini sudah terdaftar. Gunakan email lain atau masuk ke akun yang sudah ada.';
            } elseif (in_array($driverCode, [1054, 1364, 1265, 1146], true)) {
                $err = 'Struktur database belum sesuai dengan fitur multi-penjual. Jalankan migrasi multi-vendor terlebih dahulu melalui phpMyAdmin.';
            } else {
                $sqlState = (string)$e->getCode();
                $err = 'Registrasi gagal karena kendala database (kode ' .
                    e($sqlState) . ($driverCode ? '/' . $driverCode : '') .
                    '). Periksa file error log hosting atau hubungi pengelola website.';
            }
        }
    }

    // PRG: refresh tidak mengirim ulang POST. Jangan simpan password.
    $_SESSION['register_old'] = [
        'name' => $name,
        'email' => $email,
        'store_name' => $store,
        'role' => $role
    ];
    flash($err);
    go('register.php?type=' . urlencode($role));
}

$err = flash() ?: '';
$old = $_SESSION['register_old'] ?? [];
unset($_SESSION['register_old']);

$selectedRole = $old['role'] ?? ($_GET['type'] ?? 'buyer');
if (!in_array($selectedRole, ['buyer', 'seller'], true)) $selectedRole = 'buyer';

$pageTitle = 'Daftar';
require 'header.php';
?>
<style>
.register-password-field{position:relative;display:flex;align-items:center;width:100%}
.register-password-field input{width:100%;padding-right:48px!important}
.register-password-toggle{position:absolute;right:5px;top:50%;transform:translateY(-50%);width:38px;height:38px;border:0;background:transparent;color:#765b3e;display:grid;place-items:center;cursor:pointer;border-radius:8px}
.register-password-toggle:hover{background:#fff3d2}
.register-password-toggle:focus-visible{outline:2px solid #e99435;outline-offset:1px}
</style>
<section class="auth">
    <h1>Buat akun KaryaKita</h1>
    <?php if ($err): ?><div class="notice error"><?= e($err) ?></div><?php endif; ?>

    <form method="post" class="form-card">
        <?= csrf_field() ?>
        <label>Nama lengkap
            <input name="name" value="<?= e($old['name'] ?? '') ?>" required>
        </label>
        <label>Daftar sebagai
            <select name="role" id="accountRole">
                <option value="buyer" <?= $selectedRole === 'buyer' ? 'selected' : '' ?>>Pembeli</option>
                <option value="seller" <?= $selectedRole === 'seller' ? 'selected' : '' ?>>Penjual</option>
            </select>
        </label>
        <label id="storeField" <?= $selectedRole === 'seller' ? '' : 'hidden' ?>>Nama toko
            <input name="store_name" id="storeName" value="<?= e($old['store_name'] ?? '') ?>" <?= $selectedRole === 'seller' ? 'required' : '' ?>>
        </label>
        <label>Email
            <input type="email" name="email" value="<?= e($old['email'] ?? '') ?>" required autocomplete="email">
        </label>
        <label>Password (minimal 8 karakter)
            <div class="register-password-field">
                <input id="registerPassword" type="password" name="password" required minlength="8" autocomplete="new-password">
                <button class="register-password-toggle" type="button" id="toggleRegisterPassword" aria-label="Tampilkan password" aria-pressed="false" title="Tampilkan password">
                    <svg id="registerEyeIcon" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>
                    </svg>
                </button>
            </div>
        </label>
        <button class="btn" type="submit">Daftar</button>
    </form>
    <p>Sudah punya akun? <a href="<?= url('login.php') ?>">Masuk</a></p>
</section>
<script>
(function(){
 const role=document.getElementById('accountRole');
 const storeField=document.getElementById('storeField');
 const storeInput=document.getElementById('storeName');
 function syncStore(){const seller=role.value==='seller';storeField.hidden=!seller;storeInput.required=seller;}
 role.addEventListener('change',syncStore);syncStore();

 const password=document.getElementById('registerPassword');
 const toggle=document.getElementById('toggleRegisterPassword');
 const eye=document.getElementById('registerEyeIcon');
 toggle.addEventListener('click',function(){
  const show=password.type==='password';
  password.type=show?'text':'password';
  toggle.setAttribute('aria-pressed',show?'true':'false');
  toggle.setAttribute('aria-label',show?'Sembunyikan password':'Tampilkan password');
  toggle.title=show?'Sembunyikan password':'Tampilkan password';
  eye.innerHTML=show
   ? '<path d="M3 3l18 18"/><path d="M10.6 10.6a2 2 0 0 0 2.8 2.8"/><path d="M9.9 5.2A10.8 10.8 0 0 1 12 5c6.4 0 10 7 10 7a17 17 0 0 1-3.1 3.9"/><path d="M6.2 6.2C3.5 8 2 12 2 12s3.6 7 10 7c1.3 0 2.5-.3 3.5-.8"/>'
   : '<path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>';
 });
})();
</script>
<?php require 'footer.php'; ?>
