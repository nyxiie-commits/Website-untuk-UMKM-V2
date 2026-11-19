<?php
/**
 * KaryaKita - konfigurasi aplikasi.
 * Isi DB_PASS melalui File Manager pada hosting. Jangan bagikan password.
 */
define('DB_HOST', 'sql213.infinityfree.com');
define('DB_NAME', 'if0_43449860_KaryaKita');
define('DB_USER', 'if0_43449860');
define('DB_PASS', 'Tgbyhn1504');
define('DB_CHARSET', 'utf8mb4');
define('BASE_URL', 'https://karyakita.gt.tc');

// Pengaturan sesi dasar
if (session_status() === PHP_SESSION_NONE) {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_STRINGIFY_FETCHES => false
        ]
    );
} catch (PDOException $e) {
    error_log('[KaryaKita] Database connection failed: ' . $e->getMessage());
    http_response_code(500);
    exit('Koneksi database gagal. Periksa konfigurasi database.');
}
