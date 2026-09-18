<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';

requireAdminLogin();

const IMPORT_MAX_ROWS = 250;
const IMPORT_MAX_BYTES = 2_000_000;

function importSlug(string $name): string
{
    return slugify($name);
}

function importEnum(string $value, array $allowed, string $default): string
{
    return in_array($value, $allowed, true) ? $value : $default;
}

function importNumber(string $value, bool $integer = false): ?float
{
    if ($value === '') return null;
    if (!is_numeric($value) || (float)$value < 0) return null;
    return $integer ? (float)(int)$value : (float)$value;
}

$categories = getAllCategories();
$results = ['added' => 0, 'skipped' => 0, 'errors' => []];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $file = $_FILES['csv_file'] ?? null;

    if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
        $results['errors'][] = 'Please choose a CSV file.';
    } elseif ($file['size'] > IMPORT_MAX_BYTES) {
        $results['errors'][] = 'CSV file is too large. Maximum size is 2 MB.';
    } elseif (strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)) !== 'csv') {
        $results['errors'][] = 'Only .csv files are allowed.';
    } elseif (!is_uploaded_file($file['tmp_name'])) {
        $results['errors'][] = 'Invalid upload. Please try again.';
    } elseif (($handle = fopen($file['tmp_name'], 'r')) === false) {
        $results['errors'][] = 'Unable to read the CSV file.';
    } else {
        $header = fgetcsv($handle);
        if ($header) $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
        $required = ['name', 'category', 'price'];
        $indexes = $header ? array_flip(array_map(static fn($v) => strtolower(trim((string)$v)), $header)) : [];
        $missing = array_diff($required, array_keys($indexes));

        if ($missing) {
            $results['errors'][] = 'Missing required CSV columns: ' . implode(', ', $missing) . '.';
        } else {
            $categoryMap = [];
            foreach ($categories as $category) {
                $categoryMap[strtolower($category['name'])] = (int)$category['id'];
                $categoryMap[strtolower($category['slug'])] = (int)$category['id'];
            }

            $db = getDB();
            $insert = $db->prepare(
                'INSERT INTO products
                 (category_id, name, slug, brand, sku, short_desc, description, price, old_price, image, video_url, stock_status, featured, status, meta_title, meta_description,
                  product_type, channels, storage_gb, poe_ports, poe_budget_watts, camera_watts, price_per_meter)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)'
            );
            $existingSlug = $db->prepare('SELECT id FROM products WHERE slug = ? LIMIT 1');
            $existingSku = $db->prepare('SELECT id FROM products WHERE sku = ? LIMIT 1');
            $rowNumber = 1;

            while (($row = fgetcsv($handle)) !== false) {
                $rowNumber++;
                if ($row === [null] || $row === []) continue;
                if ($rowNumber > IMPORT_MAX_ROWS + 1) {
                    $results['errors'][] = 'Only the first ' . IMPORT_MAX_ROWS . ' product rows are allowed.';
                    break;
                }
                $value = static function (string $field) use ($row, $indexes): string {
                    return isset($indexes[$field], $row[$indexes[$field]]) ? trim((string)$row[$indexes[$field]]) : '';
                };

                $name = $value('name');
                $categoryKey = strtolower($value('category'));
                $price = importNumber($value('price'));
                $sku = $value('sku');
                $slug = importSlug($name);
                if ($name === '' || !$slug || !isset($categoryMap[$categoryKey]) || $price === null) {
                    $results['errors'][] = "Row {$rowNumber}: name, valid category and non-negative price are required.";
                    continue;
                }
                $existingSlug->execute([$slug]);
                $duplicate = (bool)$existingSlug->fetch();
                if (!$duplicate && $sku !== '') {
                    $existingSku->execute([$sku]);
                    $duplicate = (bool)$existingSku->fetch();
                }
                if ($duplicate) {
                    $results['skipped']++;
                    $results['errors'][] = "Row {$rowNumber}: skipped because this product slug or SKU already exists.";
                    continue;
                }

                $image = basename($value('image_filename'));
                if ($image !== '' && !is_file(UPLOAD_DIR . $image)) {
                    $results['errors'][] = "Row {$rowNumber}: image '{$image}' was not found in uploads/products; product added without it.";
                    $image = '';
                }
                $metaTitle = $value('meta_title');
                $metaDescription = $value('meta_description');
                if ($metaTitle === '') $metaTitle = $name . ' in Karachi | ' . SITE_NAME;
                if ($metaDescription === '') {
                    $base = $value('short_desc') ?: $value('description');
                    $metaDescription = trim($base . ' Available from ' . SITE_NAME . ' in Karachi.');
                }
                $productType = importEnum($value('product_type'), ['camera','nvr','dvr','hdd','poe_switch','cable','other'], 'other');
                $oldPrice = importNumber($value('old_price'));
                $channels = importNumber($value('channels'), true);
                $storageGb = importNumber($value('storage_gb'), true);
                $poePorts = importNumber($value('poe_ports'), true);
                $poeBudget = importNumber($value('poe_budget_watts'), true);
                $cameraWatts = importNumber($value('camera_watts'));
                $pricePerMeter = importNumber($value('price_per_meter'));
                try {
                    $insert->execute([
                        $categoryMap[$categoryKey], $name, $slug, $value('brand') ?: null, $sku ?: null,
                        $value('short_desc') ?: null, $value('description') ?: null, $price, $oldPrice, $image ?: null,
                        $value('video_url') ?: null, importEnum($value('stock_status'), ['in_stock','out_of_stock','on_order'], 'in_stock'),
                        $value('featured') === '1' ? 1 : 0, importEnum($value('status'), ['active','inactive'], 'active'),
                        mb_substr($metaTitle, 0, 180), mb_substr($metaDescription, 0, 300), $productType,
                        $channels !== null ? (int)$channels : null, $storageGb !== null ? (int)$storageGb : null,
                        $poePorts !== null ? (int)$poePorts : null, $poeBudget !== null ? (int)$poeBudget : null,
                        $cameraWatts, $pricePerMeter
                    ]);
                    $results['added']++;
                } catch (Throwable $e) {
                    $results['errors'][] = "Row {$rowNumber}: could not be saved.";
                }
            }
        }
        fclose($handle);
    }
}

$adminPageTitle = 'Bulk CSV Upload';
include __DIR__ . '/includes/header.php';
?>

<div class="admin-page-header">
  <h1 class="admin-page-title"><i class="fa-solid fa-file-csv me-2"></i>Bulk CSV Upload</h1>
  <a href="<?= ADMIN_URL ?>/products.php" class="btn-admin-outline"><i class="fa-solid fa-arrow-left me-1"></i>Back</a>
</div>

<?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
  <?php if ($results['added']): ?><div class="alert alert-success">Added <?= $results['added'] ?> product(s). Skipped <?= $results['skipped'] ?> duplicate(s).</div><?php endif; ?>
  <?php if ($results['errors']): ?><div class="alert alert-warning"><strong>Import notes:</strong><ul class="mb-0 mt-2"><?php foreach ($results['errors'] as $error): ?><li><?= h($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<?php endif; ?>

<div class="row g-4">
  <div class="col-lg-7">
    <div class="admin-card">
      <div class="admin-card-header"><h5><i class="fa-solid fa-upload me-2"></i>Upload Products</h5></div>
      <div class="admin-card-body">
        <form method="POST" enctype="multipart/form-data">
          <?php csrfField(); ?>
          <div class="mb-3">
            <label class="form-label fw-600">CSV File</label>
            <input type="file" name="csv_file" class="form-control" accept=".csv,text/csv" required>
            <small class="text-muted">UTF-8 CSV, maximum <?= IMPORT_MAX_ROWS ?> products / 2 MB. Required: name, category, price.</small>
          </div>
          <button class="btn-admin-primary" type="submit"><i class="fa-solid fa-upload me-2"></i>Import Products</button>
          <a class="btn-admin-outline ms-2" href="<?= ADMIN_URL ?>/product-import-template.csv"><i class="fa-solid fa-download me-1"></i>Download Template</a>
        </form>
      </div>
    </div>
  </div>
  <div class="col-lg-5">
    <div class="admin-card">
      <div class="admin-card-header"><h5><i class="fa-solid fa-shield-halved me-2"></i>Safe Import Rules</h5></div>
      <div class="admin-card-body small">
        <ul class="mb-0 ps-3">
          <li>Existing manual add, edit and delete functions are unchanged.</li>
          <li>A duplicate SKU or SEO slug is skipped; nothing is overwritten.</li>
          <li>SEO title and description are created automatically when blank.</li>
          <li>Category can be its current name or slug, for example <code>CCTV Cameras</code> or <code>cctv-cameras</code>.</li>
          <li>For images, first upload the files through the current product image process, then use the exact filename in <code>image_filename</code>.</li>
        </ul>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
