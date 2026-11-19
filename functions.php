<?php
require_once __DIR__ . '/config.php';

function e($value): string { return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function url(string $path = ''): string { return rtrim(BASE_URL, '/') . '/' . ltrim($path, '/'); }
function go(string $path): void { header('Location: ' . url($path)); exit; }
function user(): ?array { return $_SESSION['user'] ?? null; }

function role_meta(?string $role = null): array {
    $role = $role ?? (user()['role'] ?? 'guest');
    return match ($role) {
        'super_admin', 'admin' => [
            'key' => 'super_admin', 'label' => 'SUPER ADMIN',
            'title' => 'Administrator Utama', 'description' => 'Mengelola seluruh marketplace KaryaKita.',
            'icon' => 'bi-shield-lock-fill', 'tone' => 'admin',
            'home' => 'admin/index.php'
        ],
        'seller' => [
            'key' => 'seller', 'label' => 'PENJUAL',
            'title' => 'Seller Center', 'description' => 'Mengelola toko, produk, pesanan, dan saldo penjualan.',
            'icon' => 'bi-shop', 'tone' => 'seller',
            'home' => 'seller/index.php'
        ],
        'buyer' => [
            'key' => 'buyer', 'label' => 'PEMBELI',
            'title' => 'Customer KaryaKita', 'description' => 'Berbelanja produk handmade dari berbagai toko.',
            'icon' => 'bi-bag-heart', 'tone' => 'buyer',
            'home' => 'index.php'
        ],
        default => [
            'key' => 'guest', 'label' => 'PENGUNJUNG',
            'title' => 'Pengunjung KaryaKita', 'description' => 'Jelajahi produk handmade lokal.',
            'icon' => 'bi-person', 'tone' => 'guest',
            'home' => 'index.php'
        ],
    };
}
function role_home_path(?string $role = null): string {
    return role_meta($role)['home'];
}

function product_seller_label(PDO $pdo, ?int $sellerId): string {
    static $cache = [];
    $sellerId = (int)($sellerId ?? 0);
    if ($sellerId <= 0) return 'KaryaKita Official';
    if (!array_key_exists($sellerId, $cache)) {
        $q = $pdo->prepare("SELECT role, name, store_name FROM users WHERE id=? LIMIT 1");
        $q->execute([$sellerId]);
        $u = $q->fetch(PDO::FETCH_ASSOC);
        $cache[$sellerId] = (!$u || in_array($u['role'], ['super_admin','admin'], true))
            ? 'KaryaKita Official'
            : (trim((string)($u['store_name'] ?? '')) ?: (string)$u['name']);
    }
    return $cache[$sellerId];
}
function official_seller_id(PDO $pdo): int {
    static $id = null;
    if ($id !== null) return $id;
    $q = $pdo->query("SELECT id FROM users WHERE role IN ('super_admin','admin') ORDER BY CASE WHEN role='super_admin' THEN 0 ELSE 1 END, id LIMIT 1");
    $id = (int)($q->fetchColumn() ?: 0);
    return $id;
}
function order_belongs_to_seller(PDO $pdo, int $orderId, int $sellerId): bool {
    $q = $pdo->prepare("SELECT COUNT(*) total_items,
        SUM(CASE WHEN seller_id = ? THEN 1 ELSE 0 END) owned_items
        FROM order_items WHERE order_id = ?");
    $q->execute([$sellerId, $orderId]);
    $r = $q->fetch(PDO::FETCH_ASSOC);
    return $r && (int)$r['total_items'] > 0 && (int)$r['total_items'] === (int)$r['owned_items'];
}
function need_login(): void {
    if (!user()) { $_SESSION['after_login'] = $_SERVER['REQUEST_URI'] ?? url(''); go('login.php?required=1'); }
}
function need_admin(): void {
    need_login();
    if (!in_array((user()['role'] ?? ''), ['super_admin', 'admin'], true)) { http_response_code(403); exit('Akses khusus super admin.'); }
}
function need_super_admin(): void { need_admin(); }
function need_seller(): void {
    need_login();
    if ((user()['role'] ?? '') !== 'seller') { http_response_code(403); exit('Akses khusus penjual.'); }
    $q = $GLOBALS['pdo']->prepare("SELECT seller_status FROM users WHERE id = ? AND role = 'seller'");
    $q->execute([(int) user()['id']]);
    if ($q->fetchColumn() !== 'approved') { http_response_code(403); exit('Akun toko belum disetujui super admin.'); }
}
function need_buyer(): void {
    need_login();
    if ((user()['role'] ?? '') !== 'buyer') { http_response_code(403); exit('Fitur ini hanya untuk pembeli.'); }
}
function rupiah($number): string { return 'Rp' . number_format((float)$number, 0, ',', '.'); }
function flash($message = null) {
    if ($message !== null) { $_SESSION['flash'] = $message; return null; }
    $value = $_SESSION['flash'] ?? ''; unset($_SESSION['flash']); return $value;
}
function image_url($name): string {
    $file = basename((string)$name);
    return $file && is_file(__DIR__ . '/uploads/products/' . $file)
        ? url('uploads/products/' . rawurlencode($file))
        : 'https://placehold.co/600x450/fff3c4/7b4b16?text=KaryaKita';
}
function csrf_token(): string {
    if (empty($_SESSION['_csrf'])) $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['_csrf'];
}
function csrf_field(): string { return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">'; }
function csrf_verify(): void {
    $sent = $_POST['_csrf'] ?? '';
    $known = $_SESSION['_csrf'] ?? '';
    if (!is_string($sent) || !$known || !hash_equals($known, $sent)) {
        http_response_code(419);
        exit('Sesi formulir kedaluwarsa atau token keamanan tidak valid. Muat ulang halaman lalu coba kembali.');
    }
}
function login_is_limited(string $key): bool {
    $entry = $_SESSION['_login_attempts'][$key] ?? ['count' => 0, 'until' => 0];
    if (($entry['until'] ?? 0) <= time()) { unset($_SESSION['_login_attempts'][$key]); return false; }
    return (int)($entry['count'] ?? 0) >= 5;
}
function login_failed(string $key): void {
    $entry = $_SESSION['_login_attempts'][$key] ?? ['count' => 0, 'until' => time() + 900];
    $entry['count'] = (int)$entry['count'] + 1;
    $entry['until'] = time() + 900;
    $_SESSION['_login_attempts'][$key] = $entry;
}
function login_succeeded(string $key): void { unset($_SESSION['_login_attempts'][$key]); }

function recommend_products($pdo, $productId = null, $limit = 4): array {
    $limit = max(1, min(20, (int)$limit));
    if ($productId) {
        $s = $pdo->prepare('SELECT * FROM products WHERE id = ?'); $s->execute([(int)$productId]); $base = $s->fetch();
        if (!$base) return [];
        $s = $pdo->prepare("SELECT p.*, c.name category_name,
            (CASE WHEN p.category_id = ? THEN 5 ELSE 0 END + CASE WHEN p.price BETWEEN ? AND ? THEN 2 ELSE 0 END) score
            FROM products p LEFT JOIN categories c ON c.id = p.category_id
            WHERE p.id <> ? AND p.stock > 0 ORDER BY score DESC, p.created_at DESC LIMIT ?");
        $s->bindValue(1, (int)$base['category_id'], PDO::PARAM_INT);
        $s->bindValue(2, max(0, (float)$base['price'] * .7));
        $s->bindValue(3, (float)$base['price'] * 1.3);
        $s->bindValue(4, (int)$productId, PDO::PARAM_INT);
        $s->bindValue(5, $limit, PDO::PARAM_INT); $s->execute(); return $s->fetchAll();
    }
    return $pdo->query("SELECT p.*, c.name category_name FROM products p LEFT JOIN categories c ON c.id=p.category_id WHERE p.stock>0 ORDER BY p.created_at DESC LIMIT {$limit}")->fetchAll();
}

// Semua permintaan POST wajib menyertakan token CSRF.
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') csrf_verify();
