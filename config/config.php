<?php
// =============================================
//  config/config.php
// =============================================

// Load .env from project root
$envFile = dirname(__DIR__) . '/.env';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') continue;
        if (strpos($line, '=') !== false) {
            [$key, $val] = array_map('trim', explode('=', $line, 2));
            if (!array_key_exists($key, $_ENV)) {
                putenv("$key=$val");
                $_ENV[$key] = $val;
            }
        }
    }
}

function env(string $key, $default = ''): string
{
    $val = $_ENV[$key] ?? getenv($key);
    return ($val !== false && $val !== null) ? (string)$val : (string)$default;
}

// --- Site constants ---
define('SITE_URL',    rtrim(env('SITE_URL', 'http://localhost/ultranet-security-php'), '/'));
define('SITE_NAME',   env('SITE_NAME', 'UltraNet Security'));
define('UPLOAD_DIR',  dirname(__DIR__) . '/uploads/products/');
define('UPLOAD_URL',  SITE_URL . '/uploads/products/');
define('ASSETS_URL',  SITE_URL . '/assets');
define('ADMIN_URL',   SITE_URL . '/admin');
define('MAX_FILE_SIZE', 5 * 1024 * 1024);
define('ALLOWED_EXT', ['jpg','jpeg','png','webp']);

// --- DB constants ---
define('DB_HOST', env('DB_HOST', 'localhost'));
define('DB_NAME', env('DB_NAME', 'ultranet_security'));
define('DB_USER', env('DB_USER', 'root'));
define('DB_PASS', env('DB_PASS', ''));   // XAMPP default = empty

// --- Admin credentials ---
define('ADMIN_USERNAME', env('ADMIN_USERNAME', 'admin'));
define('ADMIN_PASSWORD', env('ADMIN_PASSWORD', 'Admin@12345'));

// --- SMTP / Email constants ---
define('SMTP_HOST',         env('SMTP_HOST', 'smtp.gmail.com'));
define('SMTP_PORT',         (int)env('SMTP_PORT', 587));
define('SMTP_ENCRYPTION',   env('SMTP_ENCRYPTION', 'tls')); // tls | ssl | none
define('SMTP_USERNAME',     env('SMTP_USERNAME', ''));
define('SMTP_PASSWORD',     env('SMTP_PASSWORD', ''));
define('SMTP_FROM_EMAIL',   env('SMTP_FROM_EMAIL', env('SMTP_USERNAME', '')));
define('SMTP_FROM_NAME',    env('SMTP_FROM_NAME', SITE_NAME));
define('ADMIN_NOTIFY_EMAIL',env('ADMIN_NOTIFY_EMAIL', env('SMTP_USERNAME', '')));

// --- Error reporting ---
// Controlled by APP_DEBUG in .env — defaults to OFF (safe for production).
// Leaving display_errors on in production can leak file paths / DB details.
$appDebug = strtolower(env('APP_DEBUG', 'false')) === 'true';
if ($appDebug) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
}

// --- Timezone ---
date_default_timezone_set('Asia/Karachi');

// --- Domain canonicalization (SEO) ---
// Enforces a single canonical domain (whatever host is set in SITE_URL —
// with or without "www"), 301-redirecting the other variant to it. Without
// this, both www.yoursite.pk and yoursite.pk could serve identical content,
// splitting search ranking signals between two URLs instead of one.
// Skipped automatically on localhost/local dev so it never breaks testing.
if (php_sapi_name() !== 'cli' && !empty($_SERVER['HTTP_HOST'])) {
    $preferredHost = parse_url(SITE_URL, PHP_URL_HOST);
    $currentHost   = $_SERVER['HTTP_HOST'];
    $isLocalDev    = stripos($currentHost, 'localhost') !== false || stripos($currentHost, '127.0.0.1') !== false;
    if (!$isLocalDev && $preferredHost && strcasecmp($currentHost, $preferredHost) !== 0) {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        header('Location: ' . $scheme . '://' . $preferredHost . ($_SERVER['REQUEST_URI'] ?? '/'), true, 301);
        exit;
    }
}

// --- Session (hardened) ---
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    ]);
    session_start();
}
