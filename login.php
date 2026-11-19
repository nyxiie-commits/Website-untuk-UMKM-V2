<?php
require 'functions.php';
if (user()) {
    $role = user()['role'] ?? '';
    go(in_array($role, ['admin','super_admin'], true) ? 'admin/index.php' : ($role === 'seller' ? 'seller/index.php' : 'index.php'));
}
$err = '';
$selectedType = ($_POST['account_type'] ?? $_GET['type'] ?? 'buyer');
if (!in_array($selectedType, ['buyer','seller'], true)) $selectedType = 'buyer';
$attemptKey = hash('sha256', strtolower((string)($_SERVER['REMOTE_ADDR'] ?? 'unknown')));
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (login_is_limited($attemptKey)) {
        $err = 'Terlalu banyak percobaan masuk. Coba lagi dalam 15 menit.';
    } else {
        $email = strtolower(trim($_POST['email'] ?? ''));
        $pass = $_POST['password'] ?? '';
        $s = $pdo->prepare('SELECT * FROM users WHERE email=?');
        $s->execute([$email]);
        $u = $s->fetch();
        if ($u && password_verify($pass, $u['password'])) {
            $actualRole = $u['role'];
            // Role choice is a user-facing entry point, never an authorization mechanism.
            // The role stored in the database remains authoritative.
            if ($actualRole === 'seller' && ($u['seller_status'] ?? '') !== 'approved') {
                $err = 'Akun penjual Anda belum disetujui Super Admin. Silakan tunggu persetujuan.';
            } elseif ($actualRole === 'buyer' && $selectedType === 'seller') {
                $err = 'Akun ini terdaftar sebagai pembeli. Pilih opsi "Saya ingin membeli".';
            } elseif ($actualRole === 'seller' && $selectedType === 'buyer') {
                $err = 'Akun ini terdaftar sebagai penjual. Pilih opsi "Saya ingin berjualan".';
            } else {
                login_succeeded($attemptKey);
                session_regenerate_id(true);
                $_SESSION['user'] = [
                    'id'=>$u['id'], 'name'=>$u['name'], 'email'=>$u['email'],
                    'role'=>$actualRole, 'store_name'=>$u['store_name'] ?? null
                ];
                $dest = $_SESSION['after_login'] ?? match($actualRole) {
                    'admin','super_admin' => 'admin/index.php',
                    'seller' => 'seller/index.php',
                    default => 'index.php'
                };
                unset($_SESSION['after_login']);
                header('Location: '.$dest);
                exit;
            }
        } else {
            login_failed($attemptKey);
            $err = 'Email atau password salah.';
        }
    }
}
$pageTitle = 'Masuk';
require 'header.php';
?>
<style>
.auth-choice-wrap{max-width:720px;margin:0 auto 22px}
.auth-choice-title{text-align:center;font-size:14px;color:#765b3e;margin:0 0 12px}
.auth-choice{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.auth-choice button{display:flex;align-items:center;gap:12px;text-align:left;padding:14px 16px;border:1px solid #ead9c5;border-radius:14px;background:#fff;color:#563b27;cursor:pointer;transition:.18s}
.auth-choice button:hover{border-color:#e99435;background:#fffaf1}
.auth-choice button.active{border:2px solid #e99435;background:#fff5e7;box-shadow:0 0 0 3px rgba(233,148,53,.10)}
.auth-choice .choice-icon{width:42px;height:42px;border-radius:12px;display:grid;place-items:center;background:#fff0c5;font-size:21px;flex:none}
.auth-choice strong{display:block;font-size:14px}
.auth-choice small{display:block;color:#806b55;font-size:12px;margin-top:3px}
.auth-choice .choice-check{margin-left:auto;color:#d98222;opacity:0}
.auth-choice button.active .choice-check{opacity:1}
.auth .form-card{margin-top:0}

.password-field{
    position:relative;
    display:flex;
    align-items:center;
    width:100%;
}
.password-field input{
    width:100%;
    padding-right:48px !important;
}
.password-toggle{
    position:absolute;
    right:5px;
    top:50%;
    transform:translateY(-50%);
    width:38px;
    height:38px;
    padding:0;
    margin:0;
    border:0;
    border-radius:8px;
    background:transparent;
    color:#765b3e;
    display:grid;
    place-items:center;
    cursor:pointer;
    box-shadow:none;
}
.password-toggle:hover{
    background:#fff3d2;
}
.password-toggle:focus-visible{
    outline:2px solid #e99435;
    outline-offset:1px;
}
.password-toggle svg{
    display:block;
    width:20px;
    height:20px;
}

.auth-links{display:flex;justify-content:center;gap:8px;flex-wrap:wrap;margin-top:18px}
.auth-links a{font-weight:600}
@media(max-width:520px){.auth-choice{grid-template-columns:1fr}.auth-choice button{padding:12px}}
</style>
<section class="auth">
    <h1>Selamat datang kembali</h1>
    <p>Masuk ke KaryaKita sebagai pembeli atau penjual.</p>

    <div class="auth-choice-wrap">
        <p class="auth-choice-title">Pilih tujuan akun Anda</p>
        <div class="auth-choice" role="group" aria-label="Pilih jenis akun">
            <button type="button" data-type="buyer" class="<?= $selectedType==='buyer'?'active':'' ?>" aria-pressed="<?= $selectedType==='buyer'?'true':'false' ?>">
                <span class="choice-icon">🛍️</span>
                <span><strong>Saya ingin membeli</strong><small>Cari dan beli produk handmade</small></span>
                <span class="choice-check">✓</span>
            </button>
            <button type="button" data-type="seller" class="<?= $selectedType==='seller'?'active':'' ?>" aria-pressed="<?= $selectedType==='seller'?'true':'false' ?>">
                <span class="choice-icon">🏪</span>
                <span><strong>Saya ingin berjualan</strong><small>Buka toko dan kelola produk</small></span>
                <span class="choice-check">✓</span>
            </button>
        </div>
    </div>

    <?php if ($err): ?><div class="notice error"><?=e($err)?></div><?php endif; ?>
    <form method="post" class="form-card">
        <?=csrf_field()?>
        <input type="hidden" name="account_type" id="accountType" value="<?=e($selectedType)?>">
        <label>Email<input type="email" name="email" required autocomplete="username"></label>
        <label>Password
            <div class="password-field">
                <input id="loginPassword" type="password" name="password" required autocomplete="current-password">
                <button class="password-toggle" type="button" id="togglePassword" aria-label="Tampilkan password" aria-pressed="false">
                    <svg id="eyeIcon" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
            </div>
        </label>
        <button class="btn" type="submit">Masuk</button>
    </form>
    <div class="auth-links">
        <span>Belum punya akun?</span>
        <a id="registerLink" href="<?=url('register.php?type='.urlencode($selectedType))?>">Daftar sebagai <?= $selectedType==='seller'?'penjual':'pembeli' ?></a>
    </div>
</section>
<script>
(function(){
 const choices=document.querySelectorAll('.auth-choice button');
 const hidden=document.getElementById('accountType');
 const link=document.getElementById('registerLink');
 const base=<?=json_encode(url('register.php?type='))?>;
 choices.forEach(btn=>btn.addEventListener('click',()=>{
   const type=btn.dataset.type; hidden.value=type;
   choices.forEach(item=>{
     const active=item===btn;
     item.classList.toggle('active',active);
     item.setAttribute('aria-pressed',active?'true':'false');
   });
   link.href=base+type;
   link.textContent='Daftar sebagai '+(type==='seller'?'penjual':'pembeli');
 }));
 const field=document.getElementById('loginPassword');
 const toggle=document.getElementById('togglePassword');
 const icon=document.getElementById('eyeIcon');
 if(toggle&&field&&icon) toggle.addEventListener('click',function(){
   const show=field.type==='password'; field.type=show?'text':'password';
   toggle.setAttribute('aria-pressed',show?'true':'false');
   toggle.setAttribute('aria-label',show?'Sembunyikan password':'Tampilkan password');
   icon.innerHTML=show?'<path d="M3 3l18 18"/><path d="M10.6 10.6a2 2 0 0 0 2.8 2.8"/><path d="M9.9 5.2A10.8 10.8 0 0 1 12 5c6.4 0 10 7 10 7a17 17 0 0 1-3.1 3.9"/><path d="M6.2 6.2C3.5 8 2 12 2 12s3.6 7 10 7c1.3 0 2.5-.3 3.5-.8"/>':'<path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>';
 });
})();
</script>
<?php require 'footer.php'; ?>
