<?php
/**
 * db-check.php — Database Connection Diagnostic Tool (Admin-only)
 * Run this in browser: http://localhost/ultranet-security-php/db-check.php
 * DELETE this file after setup is complete!
 */
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';

// Admin login does NOT require the database (credentials come from .env),
// so this still works even if your DB connection is broken — but it stops
// this page from leaking your DB host/user/config to random visitors.
if (!isAdminLoggedIn()) {
    header('Location: ' . ADMIN_URL . '/login.php?redirect=db-check');
    exit;
}

function check(string $label, bool $ok, string $detail = ''): void {
    $icon = $ok ? '✅' : '❌';
    $color = $ok ? '#1a7a3e' : '#c0392b';
    echo "<div style='padding:12px 16px;margin-bottom:8px;border-radius:8px;background:" . ($ok?'#eafaf1':'#fdf3f3') . ";border:1px solid " . ($ok?'#a9dfbf':'#f5b7b1') . "'>";
    echo "<strong style='color:$color'>$icon $label</strong>";
    if ($detail) echo "<br><small style='color:#555'>$detail</small>";
    echo "</div>";
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>DB Diagnostic – UltraNet Security</title>
<style>
  body{font-family:sans-serif;background:#f4f6fb;padding:40px 20px;margin:0}
  .wrap{max-width:680px;margin:0 auto;background:#fff;border-radius:16px;padding:36px;box-shadow:0 4px 24px rgba(0,0,0,.08)}
  h1{font-size:22px;color:#0a1628;margin-bottom:24px}
  h2{font-size:15px;color:#6c757d;text-transform:uppercase;letter-spacing:1px;margin:24px 0 10px}
  code{background:#f0f2f7;padding:3px 8px;border-radius:4px;font-size:13px;display:inline-block}
  .env-row{display:flex;gap:12px;align-items:center;padding:8px 0;border-bottom:1px solid #f0f2f7;font-size:14px}
  .key{font-weight:700;min-width:160px;color:#0a1628}
  .val{color:#444;font-family:monospace}
  .warn{background:#fffbea;border:1px solid #f0d060;border-radius:8px;padding:14px 16px;font-size:14px;margin-top:20px}
  .btn{display:inline-block;background:#e63946;color:#fff;padding:10px 22px;border-radius:8px;text-decoration:none;font-weight:600;margin-top:20px}
</style>
</head>
<body>
<div class="wrap">
<h1>🔧 UltraNet Security – Database Diagnostic</h1>

<h2>Current .env Settings</h2>
<div class="env-row"><span class="key">DB_HOST</span><code><?= DB_HOST ?></code></div>
<div class="env-row"><span class="key">DB_NAME</span><code><?= DB_NAME ?></code></div>
<div class="env-row"><span class="key">DB_USER</span><code><?= DB_USER ?></code></div>
<div class="env-row"><span class="key">DB_PASS</span><code><?= DB_PASS === '' ? '(empty — XAMPP default)' : '(set — ' . strlen(DB_PASS) . ' chars)' ?></code></div>
<div class="env-row"><span class="key">SITE_URL</span><code><?= SITE_URL ?></code></div>

<h2>Connection Tests</h2>
<?php

// TEST 1: MySQL running at all
$mysqlUp = false;
try {
    $pdo1 = new PDO('mysql:host=' . DB_HOST, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $mysqlUp = true;
    check('MySQL is running', true, 'Connected to MySQL server at ' . DB_HOST);
} catch (PDOException $e) {
    $ec = $e->getCode();
    if ($ec == 1045) {
        check('MySQL credentials', false, 'Wrong username or password. Open .env → set DB_PASS= (empty for XAMPP)');
    } elseif ($ec == 2002) {
        check('MySQL is running', false, 'Cannot reach MySQL. Open XAMPP Control Panel → Start MySQL.');
    } else {
        check('MySQL connection', false, $e->getMessage());
    }
}

// TEST 2: Database exists
$dbExists = false;
if ($mysqlUp) {
    try {
        $pdo2 = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $dbExists = true;
        check('Database "' . DB_NAME . '" exists', true, 'Database found successfully');
    } catch (PDOException $e) {
        check('Database "' . DB_NAME . '" exists', false,
              'Database not found. Open phpMyAdmin → Create database "' . DB_NAME . '" → Import database/schema.sql');
    }
}

// TEST 3: Tables exist
if ($dbExists) {
    try {
        $pdo3 = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $tables = $pdo3->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        $needed = ['categories', 'products', 'admins'];
        $missing = array_diff($needed, $tables);
        if (empty($missing)) {
            check('All tables exist', true, 'Tables found: ' . implode(', ', $tables));
            // Test product count
            $prodCount = $pdo3->query("SELECT COUNT(*) FROM products")->fetchColumn();
            $catCount  = $pdo3->query("SELECT COUNT(*) FROM categories")->fetchColumn();
            check('Sample data loaded', $prodCount > 0, "$prodCount products, $catCount categories in database");
        } else {
            check('Tables exist', false, 'Missing tables: ' . implode(', ', $missing) . '. Import database/schema.sql in phpMyAdmin');
        }
    } catch (PDOException $e) {
        check('Tables check', false, $e->getMessage());
    }
}

// TEST 4: uploads folder writable
$uploadWritable = is_writable(UPLOAD_DIR) || (!is_dir(UPLOAD_DIR) && is_writable(dirname(UPLOAD_DIR)));
check('uploads/products/ writable', $uploadWritable,
    $uploadWritable ? 'Image uploads will work correctly' : 'Right-click the uploads/products folder → Properties → Allow write');

// TEST 5: .htaccess present
check('.htaccess present', file_exists(__DIR__ . '/.htaccess'),
      'Required for clean product URLs like /product/hikvision-camera');
?>

<h2>How to Fix DB_PASS</h2>
<div style="background:#f8f9fc;border:1px solid #e0e4ef;border-radius:8px;padding:16px;font-size:14px;line-height:1.8">
  <strong>XAMPP (most common):</strong><br>
  Open <code>.env</code> and set: <code>DB_PASS=</code> &nbsp;← leave completely blank<br><br>
  <strong>If you set a MySQL password yourself:</strong><br>
  Open <code>.env</code> and set: <code>DB_PASS=your_actual_password</code><br><br>
  <strong>To find/reset in XAMPP:</strong><br>
  XAMPP Control Panel → MySQL → Admin → phpMyAdmin → User Accounts → root → Change password
</div>

<div class="warn">
  ⚠ <strong>Delete this file after setup!</strong> It exposes your DB settings.<br>
  Delete: <code>db-check.php</code> from your project folder when done.
</div>

<a href="<?= SITE_URL ?>/" class="btn">→ Go to Homepage</a>
</div>
</body>
</html>
