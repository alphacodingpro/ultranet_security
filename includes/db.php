<?php
// =============================================
//  includes/db.php — PDO Database Connection
// =============================================

require_once dirname(__DIR__) . '/config/config.php';

function getDB(): PDO
{
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    try {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    } catch (PDOException $e) {
        $code = $e->getCode();
        $msg  = $e->getMessage();

        // Helpful error messages for common problems
        if ($code == 1045) {
            $tip = '<strong>Wrong username or password.</strong><br>
                    Open your <code>.env</code> file and fix:<br>
                    <code>DB_USER=root</code><br>
                    <code>DB_PASS=</code> &nbsp;&nbsp;&larr; Leave BLANK for XAMPP default';
        } elseif ($code == 1049) {
            $tip = '<strong>Database not found.</strong><br>
                    Open phpMyAdmin &rarr; Create database named <code>ultranet_security</code><br>
                    Then import <code>database/schema.sql</code>';
        } elseif ($code == 2002) {
            $tip = '<strong>MySQL is not running.</strong><br>
                    Open XAMPP Control Panel &rarr; Click <strong>Start</strong> next to MySQL.';
        } else {
            $tip = htmlspecialchars($msg);
        }

        die('
        <style>
          body{font-family:sans-serif;background:#f4f6fb;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0}
          .err{background:#fff;border-left:5px solid #e63946;border-radius:12px;padding:32px 36px;max-width:620px;width:90%;box-shadow:0 8px 32px rgba(0,0,0,.1)}
          .err h2{color:#e63946;font-size:20px;margin-bottom:12px}
          .err p{color:#444;line-height:1.7;margin-bottom:10px;font-size:15px}
          code{background:#f0f2f7;padding:3px 8px;border-radius:4px;font-size:14px}
          .err a{color:#e63946;font-weight:600}
          .fix{background:#fff8f0;border:1px solid #fde8bc;border-radius:8px;padding:16px 18px;margin-top:16px}
          .fix h3{font-size:14px;font-weight:700;margin-bottom:8px;color:#c07000}
        </style>
        <div class="err">
          <h2>⚠ Database Connection Error</h2>
          <p>' . $tip . '</p>
          <div class="fix">
            <h3>Quick Fix Steps:</h3>
            <p>1. Open file: <code>.env</code><br>
               2. Set: <code>DB_PASS=</code> (blank for XAMPP)<br>
               3. Save the file and refresh this page</p>
            <p>Run the diagnostic tool: <a href="' . SITE_URL . '/db-check.php">db-check.php</a></p>
          </div>
          <p style="margin-top:16px;font-size:13px;color:#999">Error code: ' . $code . ' | ' . htmlspecialchars(substr($msg,0,120)) . '</p>
        </div>');
    }
    return $pdo;
}
