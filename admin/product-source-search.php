<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
requireAdminLogin();

function ensureProductDraftTable(PDO $db): void {
    $db->exec("CREATE TABLE IF NOT EXISTS product_source_drafts (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, category_id INT UNSIGNED NOT NULL,
        source_brand VARCHAR(40) NOT NULL, source_url VARCHAR(500) NOT NULL UNIQUE,
        name VARCHAR(180) NOT NULL, slug VARCHAR(200) NOT NULL, short_desc VARCHAR(300) DEFAULT NULL,
        raw_json MEDIUMTEXT DEFAULT NULL, status ENUM('pending','imported','rejected','duplicate') NOT NULL DEFAULT 'pending',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX(category_id), INDEX(status), INDEX(slug)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}
function apifySearch(string $query, string $domain): array {
    if (APIFY_API_TOKEN === '') throw new RuntimeException('APIFY_API_TOKEN is missing. Add it to cPanel .env first.');
    // Apify API actor paths use a tilde between owner and actor name.
    $actor = rawurlencode(str_replace('/', '~', APIFY_SEARCH_ACTOR));
    $url = 'https://api.apify.com/v2/acts/' . $actor . '/run-sync-get-dataset-items?token=' . rawurlencode(APIFY_API_TOKEN);
    $payload = json_encode(['queries' => trim($query) . ' site:' . $domain, 'maxPagesPerQuery' => 1, 'resultsPerPage' => 10]);
    if (!function_exists('curl_init')) throw new RuntimeException('PHP cURL is disabled on this hosting account. Enable it in cPanel first.');
    $ch = curl_init($url);
    curl_setopt_array($ch, [CURLOPT_POST => true, CURLOPT_POSTFIELDS => $payload, CURLOPT_HTTPHEADER => ['Content-Type: application/json'], CURLOPT_RETURNTRANSFER => true, CURLOPT_CONNECTTIMEOUT => 15, CURLOPT_TIMEOUT => 90]);
    $response = curl_exec($ch); $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE); $error = curl_error($ch); curl_close($ch);
    if ($response === false || $code < 200 || $code >= 300) throw new RuntimeException('Apify search failed' . ($error ? ': ' . $error : '.'));
    $data = json_decode($response, true);
    if (!is_array($data)) throw new RuntimeException('Apify returned an unreadable response.');
    return $data;
}

$db = getDB(); ensureProductDraftTable($db);
$categories = getAllCategories(); $message = ''; $errors = [];
$sources = ['hikvision' => ['label' => 'Hikvision official', 'domain' => 'hikvision.com'], 'dahua' => ['label' => 'Dahua official', 'domain' => 'dahuasecurity.com']];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf(); $query = trim($_POST['query'] ?? ''); $categoryId = (int)($_POST['category_id'] ?? 0); $source = $_POST['source'] ?? '';
    if ($query === '' || !$categoryId || !isset($sources[$source])) $errors[] = 'Query, category and official source are required.';
    if (!$errors) {
        try {
            $items = apifySearch($query, $sources[$source]['domain']); $rows = [];
            foreach ($items as $item) foreach (($item['organicResults'] ?? [$item]) as $result) {
                $url = trim((string)($result['url'] ?? $result['link'] ?? '')); $title = trim((string)($result['title'] ?? $result['name'] ?? ''));
                $host = strtolower((string)parse_url($url, PHP_URL_HOST));
                $approvedDomain = $sources[$source]['domain'];
                if ($title === '' || $url === '' || substr($host, -strlen($approvedDomain)) !== $approvedDomain) continue;
                $rows[] = ['url' => $url, 'name' => mb_substr($title, 0, 180), 'desc' => mb_substr(trim((string)($result['description'] ?? $result['snippet'] ?? '')), 0, 300), 'raw' => $result];
            }
            $insert = $db->prepare('INSERT IGNORE INTO product_source_drafts (category_id,source_brand,source_url,name,slug,short_desc,raw_json) VALUES (?,?,?,?,?,?,?)');
            $added = 0; foreach ($rows as $row) {
                $slug = slugify($row['name']); if ($slug === '') continue;
                $exists = $db->prepare('SELECT id FROM products WHERE slug=? OR name=? LIMIT 1'); $exists->execute([$slug, $row['name']]);
                if ($exists->fetch()) continue;
                $insert->execute([$categoryId, $source, $row['url'], $row['name'], $slug, $row['desc'] ?: null, json_encode($row['raw'])]);
                $added += $insert->rowCount();
            }
            $message = $added . ' new candidate(s) saved for review. Existing products and repeated sources were skipped.';
        } catch (Throwable $e) { $errors[] = $e->getMessage(); }
    }
}
$adminPageTitle = 'Product Research'; include __DIR__ . '/includes/header.php';
?>
<div class="admin-page-header"><h1 class="admin-page-title"><i class="fa-solid fa-magnifying-glass me-2"></i>Product Research</h1><a href="<?= ADMIN_URL ?>/product-source-drafts.php" class="btn-admin-outline"><i class="fa-solid fa-list me-1"></i>Review Drafts</a></div>
<?php if ($message): ?><div class="alert alert-success"><?= h($message) ?></div><?php endif; ?>
<?php if ($errors): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $error): ?><li><?= h($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<div class="admin-card"><div class="admin-card-header"><h5><i class="fa-solid fa-shield-halved me-2"></i>Official Brand Search</h5></div><div class="admin-card-body"><form method="POST" class="row g-3"><?php csrfField(); ?><div class="col-md-5"><label class="form-label fw-600">Product query</label><input class="form-control" name="query" placeholder="e.g. 4MP ColorVu bullet camera" required></div><div class="col-md-3"><label class="form-label fw-600">Website category</label><select name="category_id" class="form-select" required><option value="">— Select —</option><?php foreach ($categories as $cat): ?><option value="<?= $cat['id'] ?>"><?= h($cat['name']) ?></option><?php endforeach; ?></select></div><div class="col-md-2"><label class="form-label fw-600">Official source</label><select name="source" class="form-select"><?php foreach ($sources as $key => $source): ?><option value="<?= $key ?>"><?= h($source['label']) ?></option><?php endforeach; ?></select></div><div class="col-md-2 d-flex align-items-end"><button class="btn-admin-primary w-100" type="submit"><i class="fa-solid fa-magnifying-glass me-1"></i>Search</button></div></form><p class="small text-muted mb-0 mt-3">Search is restricted to official domains. Results remain drafts; no prices, images or live products are created automatically.</p></div></div>
<?php include __DIR__ . '/includes/footer.php'; ?>
