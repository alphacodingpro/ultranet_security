<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';

requireAdminLogin();

$db = getDB();

// Filters
$filterCat    = (int)($_GET['cat'] ?? 0);
$filterStatus = $_GET['status'] ?? '';
$filterSearch = trim($_GET['q'] ?? '');

$where  = ['1=1'];
$params = [];

if ($filterCat) {
    $where[]  = 'p.category_id = ?';
    $params[] = $filterCat;
}
if ($filterStatus) {
    $where[]  = 'p.status = ?';
    $params[] = $filterStatus;
}
if ($filterSearch) {
    $where[]  = '(p.name LIKE ? OR p.brand LIKE ?)';
    $kw       = '%' . $filterSearch . '%';
    $params[] = $kw;
    $params[] = $kw;
}

$sql = 'SELECT p.*, c.name AS category_name FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE ' . implode(' AND ', $where) . '
        ORDER BY p.id DESC';

$st = $db->prepare($sql);
$st->execute($params);
$products   = $st->fetchAll();
$categories = getAllCategories();

$adminPageTitle = 'All Products';
include __DIR__ . '/includes/header.php';
?>

<div class="admin-page-header">
  <h1 class="admin-page-title"><i class="fa-solid fa-camera me-2"></i>All Products <span class="badge bg-secondary"><?= count($products) ?></span></h1>
  <a href="<?= ADMIN_URL ?>/product-add.php" class="btn-admin-primary"><i class="fa-solid fa-plus me-1"></i>Add Product</a>
</div>

<?php showFlash(); ?>

<!-- FILTERS -->
<div class="admin-card mb-4">
  <div class="admin-card-body">
    <form method="GET" class="row g-2 align-items-end">
      <div class="col-12 col-md-4">
        <input type="text" name="q" class="form-control form-control-sm" placeholder="Search by name or brand…" value="<?= h($filterSearch) ?>">
      </div>
      <div class="col-6 col-md-3">
        <select name="cat" class="form-select form-select-sm">
          <option value="">All Categories</option>
          <?php foreach ($categories as $cat): ?>
          <option value="<?= $cat['id'] ?>" <?= $filterCat == $cat['id'] ? 'selected' : '' ?>><?= h($cat['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-6 col-md-2">
        <select name="status" class="form-select form-select-sm">
          <option value="">All Status</option>
          <option value="active"   <?= $filterStatus==='active'   ? 'selected':'' ?>>Active</option>
          <option value="inactive" <?= $filterStatus==='inactive' ? 'selected':'' ?>>Inactive</option>
        </select>
      </div>
      <div class="col-12 col-md-3 d-flex gap-2">
        <button type="submit" class="btn-admin-primary btn-sm flex-grow-1"><i class="fa-solid fa-filter me-1"></i>Filter</button>
        <a href="<?= ADMIN_URL ?>/products.php" class="btn-admin-outline btn-sm">Clear</a>
      </div>
    </form>
  </div>
</div>

<!-- TABLE -->
<div class="admin-card">
  <div class="admin-card-body p-0">
    <div class="table-responsive">
      <table class="admin-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Image</th>
            <th>Name / Brand</th>
            <th>Category</th>
            <th>Calc Type</th>
            <th>Price</th>
            <th>Stock</th>
            <th>Status</th>
            <th>Featured</th>
            <th style="min-width:120px">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($products): ?>
          <?php foreach ($products as $p): ?>
          <tr>
            <td class="text-muted small"><?= $p['id'] ?></td>
            <td>
              <img src="<?= h(productImageUrl($p['image'])) ?>" alt="<?= h($p['name']) ?>"
                   class="admin-prod-thumb"
                   onerror="this.src='<?= ASSETS_URL ?>/img/no-image.jpg'">
            </td>
            <td>
              <strong><?= h($p['name']) ?></strong>
              <?php if ($p['brand']): ?><br><small class="text-muted"><?= h($p['brand']) ?></small><?php endif; ?>
              <?php if ($p['sku']): ?><br><small class="text-muted">SKU: <?= h($p['sku']) ?></small><?php endif; ?>
            </td>
            <td><?= h($p['category_name'] ?? '—') ?></td>
            <td>
              <?php if (!empty($p['product_type']) && $p['product_type'] !== 'other'): ?>
              <span class="badge-featured"><?= h(ucfirst(str_replace('_',' ',$p['product_type']))) ?></span>
              <?php else: ?>
              <span class="text-muted small">—</span>
              <?php endif; ?>
            </td>
            <td>
              <strong><?= formatPrice($p['price']) ?></strong>
              <?php if ($p['old_price']): ?><br><small class="text-muted"><del><?= formatPrice($p['old_price']) ?></del></small><?php endif; ?>
            </td>
            <td>
              <?php
              $sClass = ['in_stock'=>'status-active','out_of_stock'=>'status-inactive','on_order'=>'status-order'];
              $sLabel = ['in_stock'=>'In Stock','out_of_stock'=>'Out of Stock','on_order'=>'On Order'];
              ?>
              <span class="status-badge <?= $sClass[$p['stock_status']] ?? '' ?>"><?= $sLabel[$p['stock_status']] ?? '' ?></span>
            </td>
            <td>
              <span class="status-badge <?= $p['status']==='active' ? 'status-active':'status-inactive' ?>"><?= ucfirst($p['status']) ?></span>
            </td>
            <td class="text-center">
              <?php if ($p['featured']): ?>
                <i class="fa-solid fa-star text-warning"></i>
              <?php else: ?>
                <i class="fa-regular fa-star text-muted"></i>
              <?php endif; ?>
            </td>
            <td>
              <div class="action-btns">
                <a href="<?= SITE_URL ?>/product/<?= h($p['slug']) ?>" target="_blank" class="action-btn view" title="View on site"><i class="fa-solid fa-eye"></i></a>
                <a href="<?= ADMIN_URL ?>/product-edit.php?id=<?= $p['id'] ?>" class="action-btn edit" title="Edit"><i class="fa-solid fa-pen"></i></a>
                <form method="POST" action="<?= ADMIN_URL ?>/product-delete.php" style="display:inline"
                      onsubmit="return confirm('Are you sure you want to delete this product?')">
                  <?php csrfField(); ?>
                  <input type="hidden" name="id" value="<?= $p['id'] ?>">
                  <button type="submit" class="action-btn delete" title="Delete" style="border:none"><i class="fa-solid fa-trash"></i></button>
                </form>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php else: ?>
          <tr><td colspan="10" class="text-center py-4 text-muted">No products found. <a href="<?= ADMIN_URL ?>/product-add.php">Add your first product →</a></td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
