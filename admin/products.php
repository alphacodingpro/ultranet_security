<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';

requireAdminLogin();
require_once dirname(__DIR__) . '/includes/admin-product-filters.php';

$db = getDB();

// All filter state is validated once and reused for counts, rows and page links.
$filters = adminProductFilterValues($_GET);
$filterOptions = adminProductFilterOptions();
$compiled = adminProductFiltersSql($filters);
$filterCat = $filters['cat'];
$filterStatus = $filters['status'];
$filterSearch = $filters['q'];
$productPage = max(1, (int)(is_scalar($_GET['page'] ?? 1) ? ($_GET['page'] ?? 1) : 1));
$perPage = 25;
$whereSql = $compiled['where'];
$params = $compiled['params'];
$imageCountSql = adminProductImageCountSql();
$youtubeSql = adminProductYoutubeSql();
$countSql = 'SELECT COUNT(*) AS total, COALESCE(SUM('.$youtubeSql.'),0) AS with_youtube,
    COALESCE(SUM('.$imageCountSql.'=0),0) AS without_images FROM products p WHERE '.$whereSql;
$countSt = $db->prepare($countSql);
$countSt->execute($params);
$filterSummary = $countSt->fetch();
$totalProducts = (int)$filterSummary['total'];
$totalPages = max(1, (int)ceil($totalProducts / $perPage));
$productPage = min($productPage, $totalPages);
$offset = ($productPage - 1) * $perPage;

$sql = 'SELECT p.*, c.name AS category_name, '.$imageCountSql.' AS image_count, '.$youtubeSql.' AS has_youtube FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE ' . $whereSql . '
        ORDER BY p.id DESC
        LIMIT ' . $perPage . ' OFFSET ' . $offset;

$st = $db->prepare($sql);
$st->execute($params);
$products   = $st->fetchAll();
$categories = getAllCategories();
$brands     = getAllBrands();

$pageQuery = array_filter($filters, static fn($value) => $value !== '' && $value !== [] && $value !== 0);
$pageUrl = static function (int $page) use ($pageQuery): string {
    return ADMIN_URL . '/products.php?' . http_build_query(array_merge($pageQuery, ['page' => $page]));
};
$windowStart = max(1, $productPage - 2);
$windowEnd = min($totalPages, $productPage + 2);

$adminPageTitle = 'All Products';
include __DIR__ . '/includes/header.php';
?>

<div class="admin-page-header">
  <h1 class="admin-page-title"><i class="fa-solid fa-camera me-2"></i>All Products <span class="badge bg-secondary"><?= $totalProducts ?></span></h1>
  <div class="d-flex gap-2">
    <a href="<?= ADMIN_URL ?>/product-images.php" class="btn-admin-outline"><i class="fa-solid fa-images me-1"></i>Manage Images</a>
    <a href="<?= ADMIN_URL ?>/product-import.php" class="btn-admin-outline"><i class="fa-solid fa-file-csv me-1"></i>Bulk Upload</a>
    <a href="<?= ADMIN_URL ?>/product-add.php" class="btn-admin-primary"><i class="fa-solid fa-plus me-1"></i>Add Product</a>
  </div>
</div>

<?php showFlash(); ?>

<!-- EXPORT -->
<div class="admin-card mb-4">
  <div class="admin-card-body">
    <form method="GET" action="<?= ADMIN_URL ?>/product-export.php" class="row g-2 align-items-end">
      <div class="col-12 col-md-3"><label class="form-label small mb-1">Export category</label><select name="cat" class="form-select form-select-sm"><option value="">All Categories</option><?php foreach ($categories as $cat): ?><option value="<?= $cat['id'] ?>"><?= h($cat['name']) ?></option><?php endforeach; ?></select></div>
      <div class="col-6 col-md-3"><label class="form-label small mb-1">Export brand</label><select name="brand" class="form-select form-select-sm"><option value="">All Brands</option><?php foreach ($brands as $itemBrand): ?><option value="<?= h($itemBrand) ?>"><?= h($itemBrand) ?></option><?php endforeach; ?></select></div>
      <div class="col-6 col-md-2"><label class="form-label small mb-1">Status</label><select name="status" class="form-select form-select-sm"><option value="">All</option><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
      <div class="col-12 col-md-2"><label class="form-label small mb-1">Search</label><input type="text" name="q" class="form-control form-control-sm" placeholder="Name, brand, SKU"></div>
      <div class="col-12 col-md-2"><button type="submit" class="btn-admin-outline btn-sm w-100"><i class="fa-solid fa-download me-1"></i>Export CSV</button></div>
    </form>
  </div>
</div>

<?php include __DIR__ . '/includes/product-filter-form.php'; ?>

<?php if ($totalPages > 1): ?>
<!-- TOP PAGINATION: intentionally uses existing admin button styles so it remains visible even with cached CSS -->
<div class="admin-card mb-3">
  <div class="admin-card-body py-3 d-flex align-items-center justify-content-between gap-3 flex-wrap">
    <div class="small text-muted">
      <strong class="text-dark">Page <?= $productPage ?> of <?= $totalPages ?></strong>
      &nbsp;·&nbsp; Showing <?= $offset + 1 ?>–<?= min($offset + $perPage, $totalProducts) ?> of <?= $totalProducts ?> products
    </div>
    <div class="d-flex align-items-center gap-2 flex-wrap" aria-label="Product pages">
      <?php if ($productPage > 1): ?>
      <a class="btn-admin-outline btn-sm" href="<?= h($pageUrl($productPage - 1)) ?>">
        <i class="fa-solid fa-chevron-left me-1"></i>Previous
      </a>
      <?php endif; ?>

      <?php for ($page = $windowStart; $page <= $windowEnd; $page++): ?>
      <a class="<?= $page === $productPage ? 'btn-admin-primary' : 'btn-admin-outline' ?> btn-sm"
         href="<?= h($pageUrl($page)) ?>" <?= $page === $productPage ? 'aria-current="page"' : '' ?>><?= $page ?></a>
      <?php endfor; ?>

      <?php if ($productPage < $totalPages): ?>
      <a class="btn-admin-outline btn-sm" href="<?= h($pageUrl($productPage + 1)) ?>">
        Next<i class="fa-solid fa-chevron-right ms-1"></i>
      </a>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- The form is separate from row action forms; checkboxes target it explicitly. -->
<?php if ($products): ?>
<form id="bulkProductDeleteForm" method="POST" action="<?= ADMIN_URL ?>/product-bulk-delete.php" class="admin-card mb-3">
  <div class="admin-card-body py-3 d-flex align-items-center gap-3 flex-wrap">
    <?php csrfField(); ?>
    <input type="hidden" name="return_query" value="<?= h(http_build_query(array_merge($pageQuery, ['page' => $productPage]))) ?>">
    <span id="bulkProductCount" class="small text-muted" aria-live="polite">Select products on this page</span>
    <button id="bulkProductDeleteButton" type="submit" class="btn btn-danger btn-sm" disabled>
      <i class="fa-solid fa-trash me-1"></i>Delete selected
    </button>
  </div>
</form>
<?php endif; ?>

<!-- TABLE -->
<div class="admin-card">
  <div class="admin-card-body p-0">
    <div class="table-responsive">
      <table class="admin-table">
        <thead>
          <tr>
            <th scope="col"><input type="checkbox" id="selectAllProducts" class="form-check-input" aria-label="Select all products on this page" <?= $products ? '' : 'disabled' ?>></th>
            <th>#</th>
            <th>Image</th>
            <th>Name / Brand</th>
            <th>Category</th>
            <th>Calc Type</th>
            <th>Price</th>
            <th>Images</th>
            <th>YouTube</th>
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
            <td><input type="checkbox" class="form-check-input product-bulk-select" form="bulkProductDeleteForm" name="ids[]" value="<?= (int)$p['id'] ?>" aria-label="Select <?= h($p['name']) ?>"></td>
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
            <td><span class="badge bg-<?= (int)$p['image_count'] === 0 ? 'warning text-dark' : 'secondary' ?>"><?= (int)$p['image_count'] ?></span></td>
            <td><span class="small <?= $p['has_youtube'] ? 'text-success' : 'text-muted' ?>"><?= $p['has_youtube'] ? 'Added' : 'Not added' ?></span></td>
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
          <tr><td colspan="13" class="text-center py-4 text-muted">No products match these filters. <a href="<?= ADMIN_URL ?>/products.php">Clear filters</a></td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php if ($totalPages > 1): ?>
<div class="admin-pagination-wrap">
  <div class="admin-pagination-info">
    Showing <?= $totalProducts ? $offset + 1 : 0 ?>–<?= min($offset + $perPage, $totalProducts) ?> of <?= $totalProducts ?> products
  </div>
  <nav class="admin-pagination" aria-label="Product pages">
    <a class="admin-page-link <?= $productPage <= 1 ? 'disabled' : '' ?>"
       href="<?= $productPage > 1 ? h($pageUrl($productPage - 1)) : '#' ?>" aria-label="Previous page">
      <i class="fa-solid fa-chevron-left"></i>
    </a>

    <?php if ($windowStart > 1): ?>
      <a class="admin-page-link" href="<?= h($pageUrl(1)) ?>">1</a>
      <?php if ($windowStart > 2): ?><span class="admin-page-dots">…</span><?php endif; ?>
    <?php endif; ?>

    <?php for ($page = $windowStart; $page <= $windowEnd; $page++): ?>
      <a class="admin-page-link <?= $page === $productPage ? 'active' : '' ?>"
         href="<?= h($pageUrl($page)) ?>" <?= $page === $productPage ? 'aria-current="page"' : '' ?>><?= $page ?></a>
    <?php endfor; ?>

    <?php if ($windowEnd < $totalPages): ?>
      <?php if ($windowEnd < $totalPages - 1): ?><span class="admin-page-dots">…</span><?php endif; ?>
      <a class="admin-page-link" href="<?= h($pageUrl($totalPages)) ?>"><?= $totalPages ?></a>
    <?php endif; ?>

    <a class="admin-page-link <?= $productPage >= $totalPages ? 'disabled' : '' ?>"
       href="<?= $productPage < $totalPages ? h($pageUrl($productPage + 1)) : '#' ?>" aria-label="Next page">
      <i class="fa-solid fa-chevron-right"></i>
    </a>
  </nav>
</div>
<?php endif; ?>

<script>
(() => {
  const form = document.getElementById('bulkProductDeleteForm');
  if (!form) return;
  const boxes = [...document.querySelectorAll('.product-bulk-select')];
  const all = document.getElementById('selectAllProducts');
  const button = document.getElementById('bulkProductDeleteButton');
  const count = document.getElementById('bulkProductCount');
  const update = () => {
    const selected = boxes.filter(box => box.checked).length;
    button.disabled = selected === 0;
    count.textContent = selected ? selected + ' product' + (selected === 1 ? '' : 's') + ' selected on this page' : 'Select products on this page';
    all.checked = boxes.length > 0 && selected === boxes.length;
    all.indeterminate = selected > 0 && selected < boxes.length;
  };
  all.addEventListener('change', () => { boxes.forEach(box => { box.checked = all.checked; }); update(); });
  boxes.forEach(box => box.addEventListener('change', update));
  form.addEventListener('submit', event => {
    const selected = boxes.filter(box => box.checked).length;
    if (!selected || !confirm('Permanently delete ' + selected + ' selected product' + (selected === 1 ? '' : 's') + ' and their unused uploaded images? This cannot be undone.')) event.preventDefault();
  });
  update();
})();
</script>
<?php include __DIR__ . '/includes/footer.php'; ?>
