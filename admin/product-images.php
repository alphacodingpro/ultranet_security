<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';

requireAdminLogin();
$db = getDB();
$perPage = 50;

// Each token identifies one database reference, never a filesystem path supplied by the browser.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $selected = $_POST['images'] ?? [];
    if (!is_array($selected) || count($selected) > $perPage) {
        setFlash('error', 'Select up to 50 images from this page.');
        header('Location: ' . ADMIN_URL . '/product-images.php');
        exit;
    }

    $deleted = 0;
    $failed = 0;
    $filesRemoved = 0;
    foreach (array_unique($selected) as $token) {
        if (!is_string($token) || !preg_match('/^(primary|gallery):([1-9][0-9]*)$/D', $token, $match)) {
            $failed++;
            continue;
        }
        $kind = $match[1];
        $id = (int)$match[2];
        if (!$id) { $failed++; continue; }

        try {
            $db->beginTransaction();
            $lookup = $kind === 'primary'
                ? 'SELECT image FROM products WHERE id = ? FOR UPDATE'
                : 'SELECT image FROM product_images WHERE id = ? FOR UPDATE';
            $st = $db->prepare($lookup);
            $st->execute([$id]);
            $filename = $st->fetchColumn();
            if (!is_string($filename) || $filename === '' ||
                $filename !== basename($filename) ||
                !in_array(strtolower(pathinfo($filename, PATHINFO_EXTENSION)), ALLOWED_EXT, true)) {
                $db->rollBack();
                $failed++;
                continue;
            }

            if ($kind === 'primary') {
                $st = $db->prepare('UPDATE products SET image = NULL WHERE id = ? AND image = ?');
            } else {
                $st = $db->prepare('DELETE FROM product_images WHERE id = ? AND image = ?');
            }
            $st->execute([$id, $filename]);
            if ($st->rowCount() !== 1) {
                $db->rollBack();
                $failed++;
                continue;
            }

            // Imported products can share one filename. Keep the file while any reference remains.
            $references = $db->prepare('SELECT (SELECT COUNT(*) FROM products WHERE image = ?)
                + (SELECT COUNT(*) FROM product_images WHERE image = ?)');
            $references->execute([$filename, $filename]);
            $unreferenced = (int)$references->fetchColumn() === 0;
            $db->commit();
            $deleted++;

            if ($unreferenced && $filename !== 'no-image.jpg') {
                $path = UPLOAD_DIR . $filename;
                if (is_file($path) && !is_link($path)) {
                    if (@unlink($path)) $filesRemoved++;
                    else $failed++; // Database link is gone, but storage was not reclaimed.
                }
            }
        } catch (Throwable $e) {
            if ($db->inTransaction()) $db->rollBack();
            error_log('Bulk product image deletion failed: ' . $e->getMessage());
            $failed++;
        }
    }

    setFlash($failed ? 'error' : 'success',
        $deleted . ' image reference(s) removed; ' . $filesRemoved . ' upload file(s) deleted.' .
        ($failed ? ' ' . $failed . ' item(s) could not be fully removed. Check server permissions/storage.' : ''));
    $returnQuery = $_POST['return_query'] ?? '';
    parse_str(is_string($returnQuery) ? $returnQuery : '', $return);
    $return = array_intersect_key($return, array_flip(['q', 'cat', 'kind', 'page']));
    header('Location: ' . ADMIN_URL . '/product-images.php' . ($return ? '?' . http_build_query($return) : ''));
    exit;
}

$q = trim(is_string($_GET['q'] ?? null) ? $_GET['q'] : '');
$q = substr($q, 0, 100);
$cat = max(0, (int)(is_scalar($_GET['cat'] ?? null) ? $_GET['cat'] : 0));
$kind = in_array($_GET['kind'] ?? '', ['primary', 'gallery'], true) ? $_GET['kind'] : '';
$page = max(1, (int)(is_scalar($_GET['page'] ?? null) ? $_GET['page'] : 1));
$params = [];
$conditions = [];
if ($cat) { $conditions[] = 'p.category_id = ?'; $params[] = $cat; }
if ($q !== '') {
    $conditions[] = '(p.name LIKE ? OR p.sku LIKE ? OR p.brand LIKE ?)';
    array_push($params, '%'.$q.'%', '%'.$q.'%', '%'.$q.'%');
}
$where = $conditions ? ' WHERE ' . implode(' AND ', $conditions) : '';
$primary = "SELECT CONCAT('primary:',p.id) AS image_key, p.id AS product_id, p.name, p.sku, p.image AS filename,
    p.category_id, 'primary' AS kind FROM products p" . $where . ($conditions ? ' AND' : ' WHERE') . " p.image IS NOT NULL AND p.image <> ''";
$gallery = "SELECT CONCAT('gallery:',pi.id) AS image_key, p.id AS product_id, p.name, p.sku, pi.image AS filename,
    p.category_id, 'gallery' AS kind FROM product_images pi JOIN products p ON p.id = pi.product_id" . $where;
$parts = [];
$queryParams = [];
if ($kind !== 'gallery') { $parts[] = $primary; array_push($queryParams, ...$params); }
if ($kind !== 'primary') { $parts[] = $gallery; array_push($queryParams, ...$params); }
$combined = implode(' UNION ALL ', $parts);
$count = $db->prepare('SELECT COUNT(*) FROM (' . $combined . ') image_rows');
$count->execute($queryParams);
$total = (int)$count->fetchColumn();
$pages = max(1, (int)ceil($total / $perPage));
$page = min($page, $pages);
$offset = ($page - 1) * $perPage;
$rows = $db->prepare('SELECT * FROM (' . $combined . ') image_rows ORDER BY product_id DESC, kind, image_key LIMIT ' . $perPage . ' OFFSET ' . $offset);
$rows->execute($queryParams);
$images = $rows->fetchAll();
$categories = getAllCategories();
$query = ['q' => $q, 'cat' => $cat, 'kind' => $kind, 'page' => $page];
$pageUrl = static function (int $target) use ($query): string {
    return ADMIN_URL . '/product-images.php?' . http_build_query(array_merge($query, ['page' => $target]));
};
$adminPageTitle = 'Manage Product Images';
include __DIR__ . '/includes/header.php';
?>
<div class="admin-page-header">
  <h1 class="admin-page-title"><i class="fa-solid fa-images me-2"></i>Manage Product Images</h1>
  <a class="btn-admin-outline" href="<?= ADMIN_URL ?>/products.php">Back to Products</a>
</div>
<?php showFlash(); ?>
<div class="admin-card mb-3"><div class="admin-card-body">
  <form method="GET" class="row g-2 align-items-end">
    <div class="col-md-5"><label class="form-label" for="image-search">Product name, SKU or brand</label><input id="image-search" class="form-control" name="q" value="<?= h($q) ?>"></div>
    <div class="col-md-3"><label class="form-label" for="image-category">Category</label><select id="image-category" class="form-select" name="cat"><option value="0">All Categories</option><?php foreach ($categories as $category): ?><option value="<?= (int)$category['id'] ?>" <?= $cat === (int)$category['id'] ? 'selected' : '' ?>><?= h($category['name']) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-2"><label class="form-label" for="image-kind">Image type</label><select id="image-kind" class="form-select" name="kind"><option value="">All images</option><option value="primary" <?= $kind === 'primary' ? 'selected' : '' ?>>Main</option><option value="gallery" <?= $kind === 'gallery' ? 'selected' : '' ?>>Gallery</option></select></div>
    <div class="col-md-2"><button class="btn-admin-primary w-100" type="submit">Filter</button></div>
  </form>
</div></div>
<p class="text-muted small">Showing <?= $total ? $offset + 1 : 0 ?>–<?= min($offset + $perPage, $total) ?> of <?= $total ?> images. Select images on this page, then confirm deletion. Products remain in the catalog.</p>
<form method="POST" id="bulk-image-form" onsubmit="return confirm('Delete the selected image references and unused uploaded files? This cannot be undone.');">
  <?php csrfField(); ?>
  <input type="hidden" name="return_query" value="<?= h(http_build_query($query)) ?>">
  <div class="admin-card"><div class="admin-card-body p-0"><div class="table-responsive"><table class="admin-table">
    <thead><tr><th><input type="checkbox" id="select-all-images" aria-label="Select all images on this page"></th><th>Photo</th><th>Product</th><th>Type</th><th>Filename</th></tr></thead>
    <tbody>
    <?php foreach ($images as $image): ?>
      <tr>
        <td><input class="image-choice" type="checkbox" name="images[]" value="<?= h($image['image_key']) ?>" aria-label="Select <?= h($image['kind'] . ' image of ' . $image['name']) ?>"></td>
        <td><img class="admin-prod-thumb" src="<?= h(productImageUrl($image['filename'])) ?>" alt="" onerror="this.src='<?= ASSETS_URL ?>/img/no-image.jpg'"></td>
        <td><a href="<?= ADMIN_URL ?>/product-edit.php?id=<?= (int)$image['product_id'] ?>"><?= h($image['name']) ?></a><?php if ($image['sku']): ?><br><small class="text-muted"><?= h($image['sku']) ?></small><?php endif; ?></td>
        <td><?= $image['kind'] === 'primary' ? 'Main' : 'Gallery' ?></td>
        <td class="small text-muted"><?= h($image['filename']) ?></td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$images): ?><tr><td colspan="5" class="text-center py-4">No images found.</td></tr><?php endif; ?>
    </tbody>
  </table></div></div></div>
  <?php if ($images): ?><div class="mt-3"><button type="submit" class="btn btn-danger" id="bulk-image-delete" disabled><i class="fa-solid fa-trash me-1"></i>Delete selected images (<span id="selected-count">0</span>)</button></div><?php endif; ?>
</form>
<?php if ($pages > 1): ?><nav class="d-flex justify-content-center align-items-center gap-3 my-4" aria-label="Image pages">
  <?php if ($page > 1): ?><a class="btn-admin-outline" href="<?= h($pageUrl($page - 1)) ?>">Previous</a><?php endif; ?>
  <span>Page <?= $page ?> of <?= $pages ?></span>
  <?php if ($page < $pages): ?><a class="btn-admin-outline" href="<?= h($pageUrl($page + 1)) ?>">Next</a><?php endif; ?>
</nav><?php endif; ?>
<script>
(() => {
  const choices = Array.from(document.querySelectorAll('.image-choice'));
  const all = document.getElementById('select-all-images');
  const button = document.getElementById('bulk-image-delete');
  const count = document.getElementById('selected-count');
  const update = () => {
    const selected = choices.filter(item => item.checked).length;
    if (button) button.disabled = selected === 0;
    if (count) count.textContent = selected;
    all.checked = choices.length > 0 && selected === choices.length;
    all.indeterminate = selected > 0 && selected < choices.length;
  };
  all.addEventListener('change', () => { choices.forEach(item => { item.checked = all.checked; }); update(); });
  choices.forEach(item => item.addEventListener('change', update));
})();
</script>
<?php include __DIR__ . '/includes/footer.php'; ?>
