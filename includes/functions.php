<?php
// =============================================
//  includes/functions.php  — Shared helper functions
// =============================================

require_once __DIR__ . '/db.php';

/* ── STRING / SEO ── */

function slugify(string $text): string
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    return trim($text, '-');
}

function h(string $str): string
{
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

function formatPrice(float $amount): string
{
    return 'PKR ' . number_format($amount, 0);
}

/* ── CATEGORIES ── */

function getAllCategories(): array
{
    $db = getDB();
    return $db->query('SELECT * FROM categories ORDER BY name ASC')->fetchAll();
}

function getCategoryBySlug(string $slug): ?array
{
    $db  = getDB();
    $st  = $db->prepare('SELECT * FROM categories WHERE slug = ?');
    $st->execute([$slug]);
    return $st->fetch() ?: null;
}

function getCategoryById(int $id): ?array
{
    $db = getDB();
    $st = $db->prepare('SELECT * FROM categories WHERE id = ?');
    $st->execute([$id]);
    return $st->fetch() ?: null;
}

/* ── PRODUCTS ── */

function getProducts(array $opts = []): array
{
    $db     = getDB();
    $where  = ['p.status = "active"'];
    $params = [];

    if (!empty($opts['category_id'])) {
        $where[]  = 'p.category_id = ?';
        $params[] = (int)$opts['category_id'];
    }
    if (!empty($opts['brand'])) {
        $where[]  = 'p.brand = ?';
        $params[] = $opts['brand'];
    }
    if (!empty($opts['featured'])) {
        $where[] = 'p.featured = 1';
    }
    if (!empty($opts['search'])) {
        $where[]  = '(p.name LIKE ? OR p.brand LIKE ? OR p.short_desc LIKE ?)';
        $kw       = '%' . $opts['search'] . '%';
        $params[] = $kw;
        $params[] = $kw;
        $params[] = $kw;
    }

    $sql = 'SELECT p.*, c.name AS category_name, c.slug AS category_slug
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE ' . implode(' AND ', $where) . '
            ORDER BY p.featured DESC, p.id DESC';

    $st = $db->prepare($sql);
    $st->execute($params);
    return $st->fetchAll();
}

function getProductBySlug(string $slug): ?array
{
    $db = getDB();
    $st = $db->prepare(
        'SELECT p.*, c.name AS category_name, c.slug AS category_slug
         FROM products p
         LEFT JOIN categories c ON p.category_id = c.id
         WHERE p.slug = ? AND p.status = "active"'
    );
    $st->execute([$slug]);
    return $st->fetch() ?: null;
}

function getProductById(int $id): ?array
{
    $db = getDB();
    $st = $db->prepare('SELECT * FROM products WHERE id = ?');
    $st->execute([$id]);
    return $st->fetch() ?: null;
}

function getRelatedProducts(int $categoryId, int $excludeId, int $limit = 4): array
{
    $db = getDB();
    $st = $db->prepare(
        'SELECT p.*, c.name AS category_name, c.slug AS category_slug
         FROM products p
         LEFT JOIN categories c ON p.category_id = c.id
         WHERE p.category_id = ? AND p.id != ? AND p.status = "active"
         ORDER BY RAND() LIMIT ?'
    );
    $st->execute([$categoryId, $excludeId, $limit]);
    return $st->fetchAll();
}

function getAllBrands(): array
{
    $db = getDB();
    return $db->query(
        'SELECT DISTINCT brand FROM products WHERE status="active" AND brand IS NOT NULL ORDER BY brand'
    )->fetchAll(PDO::FETCH_COLUMN);
}

/* ── IMAGE ── */

function productImageUrl(?string $img): string
{
    if ($img && file_exists(UPLOAD_DIR . $img)) {
        // Cache-bust with file's last-modified time so browsers always show
        // the latest version after an image is replaced in Admin.
        return UPLOAD_URL . $img . '?v=' . filemtime(UPLOAD_DIR . $img);
    }
    // Fallback to assets/img folder
    if ($img && file_exists(dirname(__DIR__) . '/assets/img/' . $img)) {
        return ASSETS_URL . '/img/' . $img;
    }
    return ASSETS_URL . '/img/no-image.jpg';
}

function uploadProductImage(array $file): string
{
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Upload failed. Please try again.');
    }
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_EXT, true)) {
        throw new RuntimeException('Invalid file type. Allowed: jpg, jpeg, png, webp');
    }
    if ($file['size'] > MAX_FILE_SIZE) {
        throw new RuntimeException('File too large. Max size: 5 MB');
    }
    // Verify this is genuinely an image (not a script/executable renamed
    // with an image extension — a common file-upload attack technique).
    $imgInfo = @getimagesize($file['tmp_name']);
    if ($imgInfo === false) {
        throw new RuntimeException('This file is not a valid image.');
    }
    $allowedMime = ['image/jpeg', 'image/png', 'image/webp'];
    if (!in_array($imgInfo['mime'], $allowedMime, true)) {
        throw new RuntimeException('Invalid image format detected.');
    }
    if (!is_uploaded_file($file['tmp_name'])) {
        throw new RuntimeException('Invalid upload — please try again.');
    }
    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }
    // Filename is always regenerated (never uses the original name),
    // which also neutralises any path-traversal or double-extension tricks.
    $filename = uniqid('prod_', true) . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], UPLOAD_DIR . $filename)) {
        throw new RuntimeException('Failed to move uploaded file');
    }
    @chmod(UPLOAD_DIR . $filename, 0644);
    return $filename;
}

/* ── ADMIN AUTH ── */

function isAdminLoggedIn(): bool
{
    return !empty($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function requireAdminLogin(): void
{
    if (!isAdminLoggedIn()) {
        header('Location: ' . ADMIN_URL . '/login.php');
        exit;
    }
}

function adminLogin(string $username, string $password): bool
{
    // hash_equals() gives a timing-safe comparison (prevents timing attacks
    // that could otherwise be used to guess the password character-by-character)
    $userMatch = hash_equals(ADMIN_USERNAME, $username);
    $passMatch = hash_equals(ADMIN_PASSWORD, $password);
    if ($userMatch && $passMatch) {
        // Regenerate session ID on login to prevent session fixation attacks
        session_regenerate_id(true);
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username']  = $username;
        $_SESSION['admin_name']      = env('ADMIN_NAME', 'Admin');
        return true;
    }
    return false;
}

function adminLogout(): void
{
    $_SESSION = [];
    session_destroy();
}

/* ── FLASH MESSAGES ── */

function setFlash(string $type, string $msg): void
{
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}

function getFlash(): ?array
{
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $f;
    }
    return null;
}

function showFlash(): void
{
    $f = getFlash();
    if (!$f) return;
    $cls = $f['type'] === 'success' ? 'alert-success' : 'alert-danger';
    echo '<div class="alert ' . $cls . ' alert-dismissible fade show" role="alert">'
        . h($f['msg'])
        . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
}

/* ── PAGINATION ── */

function paginate(int $total, int $perPage, int $current): array
{
    $totalPages = max(1, (int)ceil($total / $perPage));
    return [
        'total'      => $total,
        'per_page'   => $perPage,
        'current'    => max(1, min($current, $totalPages)),
        'total_pages'=> $totalPages,
        'offset'     => ($current - 1) * $perPage,
    ];
}

/* ── PRODUCT IMAGES (multiple) ── */

function getProductImages(int $productId): array
{
    $db = getDB();
    $st = $db->prepare(
        'SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order ASC, id ASC'
    );
    $st->execute([$productId]);
    return $st->fetchAll();
}

function addProductImage(int $productId, string $filename, int $sortOrder = 0): void
{
    $db = getDB();
    $db->prepare('INSERT INTO product_images (product_id, image, sort_order) VALUES (?,?,?)')
       ->execute([$productId, $filename, $sortOrder]);
}

function deleteProductImage(int $imageId, int $productId): bool
{
    $db = getDB();
    $st = $db->prepare('SELECT image FROM product_images WHERE id = ? AND product_id = ?');
    $st->execute([$imageId, $productId]);
    $row = $st->fetch();
    if (!$row) return false;
    // Remove physical file
    if (file_exists(UPLOAD_DIR . $row['image'])) {
        @unlink(UPLOAD_DIR . $row['image']);
    }
    $db->prepare('DELETE FROM product_images WHERE id = ?')->execute([$imageId]);
    return true;
}

function deleteAllProductImages(int $productId): void
{
    $imgs = getProductImages($productId);
    foreach ($imgs as $img) {
        if (file_exists(UPLOAD_DIR . $img['image'])) {
            @unlink(UPLOAD_DIR . $img['image']);
        }
    }
    getDB()->prepare('DELETE FROM product_images WHERE product_id = ?')->execute([$productId]);
}

/* ── VIDEO URL HELPERS ── */

function getYouTubeEmbedUrl(string $url): ?string
{
    $url = trim($url);
    if ($url === '') return null;

    // youtu.be/VIDEOID
    if (preg_match('/youtu\.be\/([a-zA-Z0-9_-]{11})/', $url, $m)) {
        return 'https://www.youtube.com/embed/' . $m[1] . '?rel=0&modestbranding=1';
    }
    // youtube.com/embed/VIDEOID
    if (preg_match('/youtube\.com\/embed\/([a-zA-Z0-9_-]{11})/', $url, $m)) {
        return 'https://www.youtube.com/embed/' . $m[1] . '?rel=0&modestbranding=1';
    }
    // youtube.com/shorts/VIDEOID
    if (preg_match('/youtube\.com\/shorts\/([a-zA-Z0-9_-]{11})/', $url, $m)) {
        return 'https://www.youtube.com/embed/' . $m[1] . '?rel=0&modestbranding=1';
    }
    // Any youtube.com URL with a v= query param, in any position (?list=x&v=ID etc.)
    $parts = parse_url($url);
    if (!empty($parts['host']) && stripos($parts['host'], 'youtube.com') !== false && !empty($parts['query'])) {
        parse_str($parts['query'], $qs);
        if (!empty($qs['v']) && preg_match('/^[a-zA-Z0-9_-]{11}$/', $qs['v'])) {
            return 'https://www.youtube.com/embed/' . $qs['v'] . '?rel=0&modestbranding=1';
        }
    }
    // Fallback: any other valid http(s) URL (Vimeo, direct video link, etc.)
    // Restricted to http/https only — blocks javascript:, data:, file: etc.
    // schemes from ever being embedded as an iframe src.
    $scheme = parse_url($url, PHP_URL_SCHEME);
    if (in_array(strtolower((string)$scheme), ['http', 'https'], true) && filter_var($url, FILTER_VALIDATE_URL)) {
        return $url;
    }
    return null;
}

function isYouTubeUrl(string $url): bool
{
    return str_contains($url, 'youtube.com') || str_contains($url, 'youtu.be');
}

/* ── SETTINGS ── */

function getSetting(string $key, $default = null)
{
    static $cache = null;
    if ($cache === null) {
        $db = getDB();
        $cache = [];
        try {
            foreach ($db->query('SELECT setting_key, setting_value FROM settings') as $row) {
                $cache[$row['setting_key']] = $row['setting_value'];
            }
        } catch (PDOException $e) {
            // settings table not migrated yet
        }
    }
    return $cache[$key] ?? $default;
}

function setSetting(string $key, string $value): void
{
    $db = getDB();
    $st = $db->prepare(
        'INSERT INTO settings (setting_key, setting_value) VALUES (?,?)
         ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
    );
    $st->execute([$key, $value]);
}

/* ── CALCULATOR: product lookups by type ── */

function getProductsByType(string $type): array
{
    $db = getDB();
    $st = $db->prepare(
        "SELECT * FROM products WHERE product_type = ? AND status = 'active' ORDER BY price ASC"
    );
    $st->execute([$type]);
    return $st->fetchAll();
}

function findBestRecorder(string $systemType, int $cameraCount): ?array
{
    $type = $systemType === 'ip' ? 'nvr' : 'dvr';
    $db   = getDB();
    // Smallest channel count that still covers camera_count
    $st = $db->prepare(
        "SELECT * FROM products WHERE product_type = ? AND status='active'
         AND channels >= ? ORDER BY channels ASC, price ASC LIMIT 1"
    );
    $st->execute([$type, $cameraCount]);
    $row = $st->fetch();
    if ($row) return $row;
    // Fallback: biggest available (with shortfall)
    $st = $db->prepare(
        "SELECT * FROM products WHERE product_type = ? AND status='active'
         ORDER BY channels DESC, price DESC LIMIT 1"
    );
    $st->execute([$type]);
    return $st->fetch() ?: null;
}

function findBestHdd(int $requiredGb): array
{
    // returns ['product'=>..,'qty'=>N] or ['product'=>null,'qty'=>0]
    $db = getDB();
    $st = $db->prepare(
        "SELECT * FROM products WHERE product_type='hdd' AND status='active'
         AND storage_gb >= ? ORDER BY storage_gb ASC, price ASC LIMIT 1"
    );
    $st->execute([$requiredGb]);
    $row = $st->fetch();
    if ($row) return ['product' => $row, 'qty' => 1];

    // No single drive big enough — use the largest, multiply
    $st = $db->prepare(
        "SELECT * FROM products WHERE product_type='hdd' AND status='active'
         ORDER BY storage_gb DESC, price ASC LIMIT 1"
    );
    $st->execute();
    $row = $st->fetch();
    if (!$row || empty($row['storage_gb'])) return ['product' => null, 'qty' => 0];
    $qty = (int)ceil($requiredGb / $row['storage_gb']);
    return ['product' => $row, 'qty' => max(1, $qty)];
}

function findBestPoeSwitch(int $cameraCount, int $requiredWatts): ?array
{
    $db = getDB();
    $st = $db->prepare(
        "SELECT * FROM products WHERE product_type='poe_switch' AND status='active'
         AND poe_ports >= ? AND poe_budget_watts >= ?
         ORDER BY poe_ports ASC, price ASC LIMIT 1"
    );
    $st->execute([$cameraCount, $requiredWatts]);
    $row = $st->fetch();
    if ($row) return $row;
    // Fallback: biggest available
    $st = $db->query(
        "SELECT * FROM products WHERE product_type='poe_switch' AND status='active'
         ORDER BY poe_ports DESC, poe_budget_watts DESC LIMIT 1"
    );
    return $st->fetch() ?: null;
}

function getCableProduct(): ?array
{
    $db = getDB();
    $st = $db->query(
        "SELECT * FROM products WHERE product_type='cable' AND status='active'
         ORDER BY price_per_meter ASC LIMIT 1"
    );
    return $st->fetch() ?: null;
}

/* ── CALCULATOR: core math ── */

function calcRequiredHddGb(int $cameraCount, string $resolutionTier, int $days, string $mode): int
{
    // Approx H.265 bitrate (Mbps) per camera by resolution tier
    $bitrates = ['2mp' => 2, '4mp' => 4, '5mp' => 5, '8mp' => 8];
    $bitrate  = $bitrates[$resolutionTier] ?? 4;
    $hoursPerDay = $mode === 'motion' ? 24 * 0.4 : 24;
    // GB = (Mbps / 8) * 3600 * hours * days * cameras / 1024
    $gb = ($bitrate / 8) * 3600 * $hoursPerDay * $days * $cameraCount / 1024;
    return (int)ceil($gb);
}

function calcRequiredPoeWatts(int $cameraCount, float $wattsPerCamera): int
{
    $headroom = (float)getSetting('poe_headroom_percent', 20) / 100;
    return (int)ceil($cameraCount * $wattsPerCamera * (1 + $headroom));
}

function generateRefCode(): string
{
    // Cryptographically random — old uniqid()-based codes were guessable
    // and could expose another client's name/phone/email/pricing.
    return 'UNS-' . strtoupper(bin2hex(random_bytes(5)));
}

/* ── CALCULATOR REQUESTS (admin) ── */

function saveCalculatorRequest(array $data): string
{
    $db  = getDB();
    $ref = generateRefCode();
    $st  = $db->prepare(
        'INSERT INTO calculator_requests
         (ref_code, client_name, client_phone, client_email, system_type, camera_count,
          resolution_label, lens_note, recording_days, recording_mode, cable_length_m,
          required_hdd_gb, required_poe_watts, items_json, subtotal, discount_percent,
          discount_amount, grand_total, status)
         VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,\'new\')'
    );
    $st->execute([
        $ref, $data['client_name'], $data['client_phone'], $data['client_email'] ?: null,
        $data['system_type'], $data['camera_count'], $data['resolution_label'] ?: null,
        $data['lens_note'] ?: null, $data['recording_days'] ?: null, $data['recording_mode'],
        $data['cable_length_m'] ?: null, $data['required_hdd_gb'] ?: null,
        $data['required_poe_watts'] ?: null, json_encode($data['items']),
        $data['subtotal'], $data['discount_percent'], $data['discount_amount'], $data['grand_total'],
    ]);
    return $ref;
}

function getCalculatorRequestByRef(string $ref): ?array
{
    $db = getDB();
    $st = $db->prepare('SELECT * FROM calculator_requests WHERE ref_code = ?');
    $st->execute([$ref]);
    $row = $st->fetch();
    if ($row) $row['items'] = json_decode($row['items_json'], true) ?: [];
    return $row ?: null;
}

function getCalculatorRequestById(int $id): ?array
{
    $db = getDB();
    $st = $db->prepare('SELECT * FROM calculator_requests WHERE id = ?');
    $st->execute([$id]);
    $row = $st->fetch();
    if ($row) $row['items'] = json_decode($row['items_json'], true) ?: [];
    return $row ?: null;
}

function getAllCalculatorRequests(string $statusFilter = ''): array
{
    $db  = getDB();
    $sql = 'SELECT * FROM calculator_requests';
    $params = [];
    if ($statusFilter) { $sql .= ' WHERE status = ?'; $params[] = $statusFilter; }
    $sql .= ' ORDER BY created_at DESC';
    $st = $db->prepare($sql);
    $st->execute($params);
    return $st->fetchAll();
}

/* ── EMAIL (SMTP) ── */

/**
 * Send a notification email using the SmtpMailer class.
 * Never throws — logs failure and returns false so a broken
 * SMTP config never breaks form submission for the visitor.
 */
function sendNotificationEmail(string $to, string $subject, string $htmlBody, ?string $replyToEmail = null, ?string $replyToName = null): bool
{
    static $loaded = false;
    if (!$loaded) {
        require_once __DIR__ . '/SmtpMailer.php';
        $loaded = true;
    }
    if (!SMTP_USERNAME || !SMTP_PASSWORD || !$to) {
        error_log('[UltraNet] Email not sent — SMTP not configured or no recipient.');
        return false;
    }
    try {
        $mailer = new SmtpMailer(SMTP_HOST, SMTP_PORT, SMTP_ENCRYPTION, SMTP_USERNAME, SMTP_PASSWORD, SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mailer->send($to, $subject, $htmlBody, $replyToEmail, $replyToName);
        return true;
    } catch (Throwable $e) {
        error_log('[UltraNet] Email send failed: ' . $e->getMessage());
        return false;
    }
}

/** Simple branded HTML wrapper for outgoing notification emails */
function emailTemplate(string $title, string $bodyHtml): string
{
    return '
    <div style="font-family:Arial,Helvetica,sans-serif;background:#f4f6fb;padding:30px 16px">
      <div style="max-width:560px;margin:0 auto;background:#fff;border-radius:12px;overflow:hidden;border:1px solid #e0e4ef">
        <div style="background:#0a1628;padding:22px 28px">
          <span style="color:#fff;font-size:20px;font-weight:bold;letter-spacing:.5px">UltraNet <span style="color:#e63946">Security</span></span>
        </div>
        <div style="padding:26px 28px">
          <h2 style="margin:0 0 16px;color:#0a1628;font-size:18px">' . htmlspecialchars($title) . '</h2>
          ' . $bodyHtml . '
        </div>
        <div style="background:#f8f9fc;padding:16px 28px;font-size:12px;color:#6c757d">
          UltraNet Security &middot; 0309-1243189 &middot; Manzoor Colony, Karachi
        </div>
      </div>
    </div>';
}

/* ── CONTACT MESSAGES ── */

function saveContactMessage(array $data): int
{
    $db = getDB();
    $st = $db->prepare(
        'INSERT INTO contact_messages (name, phone, email, service, area, cameras, message, source)
         VALUES (?,?,?,?,?,?,?,?)'
    );
    $st->execute([
        $data['name'], $data['phone'], $data['email'] ?: null,
        $data['service'] ?: null, $data['area'] ?: null, $data['cameras'] ?: null,
        $data['message'] ?: null, $data['source'] ?? 'homepage_contact_form',
    ]);
    return (int)$db->lastInsertId();
}

function markContactEmailSent(int $id): void
{
    getDB()->prepare('UPDATE contact_messages SET email_sent = 1 WHERE id = ?')->execute([$id]);
}

function getAllContactMessages(string $statusFilter = ''): array
{
    $db  = getDB();
    $sql = 'SELECT * FROM contact_messages';
    $params = [];
    if ($statusFilter) { $sql .= ' WHERE status = ?'; $params[] = $statusFilter; }
    $sql .= ' ORDER BY created_at DESC';
    $st = $db->prepare($sql);
    $st->execute($params);
    return $st->fetchAll();
}

function getContactMessageById(int $id): ?array
{
    $db = getDB();
    $st = $db->prepare('SELECT * FROM contact_messages WHERE id = ?');
    $st->execute([$id]);
    return $st->fetch() ?: null;
}

function countNewContactMessages(): int
{
    try {
        return (int)getDB()->query("SELECT COUNT(*) FROM contact_messages WHERE status='new'")->fetchColumn();
    } catch (Throwable $e) {
        return 0;
    }
}

/* ── CSRF PROTECTION ── */

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/** Echo a hidden CSRF field for use inside <form> tags */
function csrfField(): void
{
    echo '<input type="hidden" name="csrf_token" value="' . h(csrfToken()) . '">';
}

/** Call at the top of every POST handler. Dies with 403 on mismatch. */
function verifyCsrf(): void
{
    $sent = $_POST['csrf_token'] ?? '';
    if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $sent)) {
        http_response_code(403);
        die('<div style="font-family:sans-serif;padding:40px;text-align:center">
             <h2>Security check failed (403)</h2>
             <p>Your session may have expired. Please go back and try again.</p>
             <a href="javascript:history.back()">&larr; Go Back</a></div>');
    }
}

/* ── LOGIN BRUTE-FORCE PROTECTION ── */

function loginAttemptsFile(): string
{
    $dir = sys_get_temp_dir();
    return $dir . '/uns_login_attempts.json';
}

function getLoginAttempts(string $ip): array
{
    $file = loginAttemptsFile();
    if (!file_exists($file)) return ['count' => 0, 'first' => time()];
    $data = json_decode(file_get_contents($file), true) ?: [];
    return $data[$ip] ?? ['count' => 0, 'first' => time()];
}

function registerFailedLogin(string $ip): void
{
    $file = loginAttemptsFile();
    $data = file_exists($file) ? (json_decode(file_get_contents($file), true) ?: []) : [];
    $entry = $data[$ip] ?? ['count' => 0, 'first' => time()];
    // Reset window after 15 minutes
    if (time() - $entry['first'] > 900) {
        $entry = ['count' => 0, 'first' => time()];
    }
    $entry['count']++;
    $data[$ip] = $entry;
    @file_put_contents($file, json_encode($data));
}

function clearLoginAttempts(string $ip): void
{
    $file = loginAttemptsFile();
    if (!file_exists($file)) return;
    $data = json_decode(file_get_contents($file), true) ?: [];
    unset($data[$ip]);
    @file_put_contents($file, json_encode($data));
}

function isLoginLocked(string $ip): int
{
    // Returns seconds remaining locked (0 if not locked)
    $entry = getLoginAttempts($ip);
    $maxAttempts = 6;
    $lockSeconds = 600; // 10 minutes
    if ($entry['count'] >= $maxAttempts) {
        $elapsed = time() - $entry['first'];
        if ($elapsed < $lockSeconds) return $lockSeconds - $elapsed;
    }
    return 0;
}

/* ── MISC SECURITY HELPERS ── */

/** Strip CR/LF from any value that will be used inside an email header (prevents header injection) */
function sanitizeHeaderValue(string $val): string
{
    return trim(str_replace(["\r", "\n", "%0a", "%0d"], '', $val));
}

/* ── GENERIC RATE LIMITER (public forms: contact, calculator) ── */

/**
 * Simple file-based rate limiter to slow down spam/bot flooding on
 * public endpoints (contact-submit.php, calculator-submit.php).
 * Not meant to stop a determined attacker — just casual/bot abuse.
 *
 * @param string $bucket   A name for this limiter, e.g. 'contact_form'
 * @param int    $maxHits  Max allowed submissions in the window
 * @param int    $windowSeconds  Time window in seconds
 * @return bool  true if allowed, false if rate-limited
 */
function checkRateLimit(string $bucket, int $maxHits = 5, int $windowSeconds = 300): bool
{
    $ip   = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $file = sys_get_temp_dir() . '/uns_ratelimit_' . preg_replace('/[^a-z0-9_]/i', '', $bucket) . '.json';

    $data = file_exists($file) ? (json_decode(file_get_contents($file), true) ?: []) : [];
    $entry = $data[$ip] ?? ['count' => 0, 'first' => time()];

    if (time() - $entry['first'] > $windowSeconds) {
        $entry = ['count' => 0, 'first' => time()];
    }

    if ($entry['count'] >= $maxHits) {
        return false;
    }

    $entry['count']++;
    $data[$ip] = $entry;
    @file_put_contents($file, json_encode($data));
    return true;
}

/* ── SEO HELPERS ── */

/** Truncate text to a max length at a word boundary, adding … only if actually cut */
function seoTruncate(string $text, int $maxLen): string
{
    $text = trim($text);
    if (mb_strlen($text) <= $maxLen) return $text;
    $cut = mb_substr($text, 0, $maxLen);
    $cut = mb_substr($cut, 0, mb_strrpos($cut, ' ') ?: $maxLen);
    return rtrim($cut, " ,.-") . '…';
}

/** Build a page <title> that never exceeds ~60 chars, brand-suffixed when it fits */
function seoTitle(string $primary, string $suffix = 'UltraNet Security', int $maxLen = 60): string
{
    $full = $primary . ' | ' . $suffix;
    if (mb_strlen($full) <= $maxLen) return $full;
    // Suffix doesn't fit — trim the primary part instead, keep the brand
    $budget = $maxLen - mb_strlen(' | ' . $suffix);
    if ($budget > 15) {
        return seoTruncate($primary, $budget) . ' | ' . $suffix;
    }
    // Extreme edge case: just trim the whole thing
    return seoTruncate($full, $maxLen);
}

/** Build a meta description within Google's ~155-160 char display limit */
function seoDescription(string $text, int $maxLen = 155): string
{
    return seoTruncate($text, $maxLen);
}

/* ── STRUCTURED DATA (Schema.org) BUILDERS ── */
/* Each returns a PHP array; includes/header.php JSON-encodes it safely. */

function schemaProduct(array $product): array
{
    $availability = $product['stock_status'] === 'in_stock'
        ? 'https://schema.org/InStock'
        : ($product['stock_status'] === 'on_order' ? 'https://schema.org/PreOrder' : 'https://schema.org/OutOfStock');

    $data = [
        "@context" => "https://schema.org",
        "@type"    => "Product",
        "name"     => $product['name'],
        "image"    => [productImageUrl($product['image'])],
        "description" => $product['short_desc'] ?: $product['name'],
        "sku"      => $product['sku'] ?: (string)$product['id'],
        "offers"   => [
            "@type"         => "Offer",
            "url"           => SITE_URL . '/product/' . $product['slug'],
            "priceCurrency" => "PKR",
            "price"         => number_format((float)$product['price'], 2, '.', ''),
            "availability"  => $availability,
            "itemCondition" => "https://schema.org/NewCondition",
        ],
    ];
    if (!empty($product['brand'])) {
        $data['brand'] = ["@type" => "Brand", "name" => $product['brand']];
    }
    return $data;
}

function schemaBreadcrumb(array $items): array
{
    // $items = [['name'=>'Home','url'=>SITE_URL.'/'], ['name'=>'Products','url'=>...], ...]
    $list = [];
    foreach ($items as $i => $item) {
        $list[] = [
            "@type"    => "ListItem",
            "position" => $i + 1,
            "name"     => $item['name'],
            "item"     => $item['url'],
        ];
    }
    return [
        "@context" => "https://schema.org",
        "@type" => "BreadcrumbList",
        "itemListElement" => $list,
    ];
}

function schemaFaqPage(array $faqs): array
{
    // $faqs = [[question, answer], ...]
    $entities = [];
    foreach ($faqs as [$q, $a]) {
        $entities[] = [
            "@type" => "Question",
            "name"  => $q,
            "acceptedAnswer" => ["@type" => "Answer", "text" => $a],
        ];
    }
    return [
        "@context" => "https://schema.org",
        "@type" => "FAQPage",
        "mainEntity" => $entities,
    ];
}

function schemaItemList(array $products, string $listName): array
{
    $items = [];
    foreach ($products as $i => $p) {
        $items[] = [
            "@type"    => "ListItem",
            "position" => $i + 1,
            "url"      => SITE_URL . '/product/' . $p['slug'],
        ];
    }
    return [
        "@context" => "https://schema.org",
        "@type" => "ItemList",
        "name" => $listName,
        "itemListElement" => $items,
    ];
}
