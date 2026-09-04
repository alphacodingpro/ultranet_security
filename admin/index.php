<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';

requireAdminLogin();

$db = getDB();
$totalProducts   = $db->query('SELECT COUNT(*) FROM products')->fetchColumn();
$activeProducts  = $db->query('SELECT COUNT(*) FROM products WHERE status="active"')->fetchColumn();
$totalCats       = $db->query('SELECT COUNT(*) FROM categories')->fetchColumn();
$featuredCount   = $db->query('SELECT COUNT(*) FROM products WHERE featured=1')->fetchColumn();
try {
    $calcNewCount = $db->query("SELECT COUNT(*) FROM calculator_requests WHERE status='new'")->fetchColumn();
    $calcTotalCount = $db->query("SELECT COUNT(*) FROM calculator_requests")->fetchColumn();
} catch (Exception $e) { $calcNewCount = 0; $calcTotalCount = 0; }
try {
    $msgNewCount = $db->query("SELECT COUNT(*) FROM contact_messages WHERE status='new'")->fetchColumn();
    $msgTotalCount = $db->query("SELECT COUNT(*) FROM contact_messages")->fetchColumn();
} catch (Exception $e) { $msgNewCount = 0; $msgTotalCount = 0; }
$recentProducts  = $db->query('SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id=c.id ORDER BY p.created_at DESC LIMIT 8')->fetchAll();

$adminPageTitle = 'Dashboard';
include __DIR__ . '/includes/header.php';
?>

<!-- PAGE HEADER -->
<div class="admin-page-header">
  <h1 class="admin-page-title"><i class="fa-solid fa-gauge-high me-2"></i>Dashboard</h1>
  <a href="<?= ADMIN_URL ?>/product-add.php" class="btn-admin-primary">
    <i class="fa-solid fa-plus me-1"></i> Add Product
  </a>
</div>

<?php showFlash(); ?>

<!-- STAT CARDS -->
<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="stat-card" style="border-left:4px solid #e63946">
      <div class="stat-icon" style="background:rgba(230,57,70,.1);color:#e63946"><i class="fa-solid fa-camera"></i></div>
      <div class="stat-info">
        <div class="stat-num"><?= $totalProducts ?></div>
        <div class="stat-label">Total Products</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card" style="border-left:4px solid #28a745">
      <div class="stat-icon" style="background:rgba(40,167,69,.1);color:#28a745"><i class="fa-solid fa-circle-check"></i></div>
      <div class="stat-info">
        <div class="stat-num"><?= $activeProducts ?></div>
        <div class="stat-label">Active Products</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card" style="border-left:4px solid #0d6efd">
      <div class="stat-icon" style="background:rgba(13,110,253,.1);color:#0d6efd"><i class="fa-solid fa-layer-group"></i></div>
      <div class="stat-info">
        <div class="stat-num"><?= $totalCats ?></div>
        <div class="stat-label">Categories</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card" style="border-left:4px solid #f4a261">
      <div class="stat-icon" style="background:rgba(244,162,97,.1);color:#f4a261"><i class="fa-solid fa-star"></i></div>
      <div class="stat-info">
        <div class="stat-num"><?= $featuredCount ?></div>
        <div class="stat-label">Featured</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card" style="border-left:4px solid #9b59b6">
      <div class="stat-icon" style="background:rgba(155,89,182,.1);color:#9b59b6"><i class="fa-solid fa-calculator"></i></div>
      <div class="stat-info">
        <div class="stat-num"><?= $calcTotalCount ?></div>
        <div class="stat-label">Calculator Leads <?= $calcNewCount > 0 ? '<span class="badge-featured">'.$calcNewCount.' new</span>' : '' ?></div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card" style="border-left:4px solid #17a2b8">
      <div class="stat-icon" style="background:rgba(23,162,184,.1);color:#17a2b8"><i class="fa-solid fa-envelope"></i></div>
      <div class="stat-info">
        <div class="stat-num"><?= $msgTotalCount ?></div>
        <div class="stat-label">Contact Messages <?= $msgNewCount > 0 ? '<span class="badge-featured">'.$msgNewCount.' new</span>' : '' ?></div>
      </div>
    </div>
  </div>
</div>

<!-- QUICK ACTIONS -->
<div class="row g-3 mb-4">
  <div class="col-12">
    <div class="admin-card">
      <div class="admin-card-header"><h5><i class="fa-solid fa-bolt me-2"></i>Quick Actions</h5></div>
      <div class="admin-card-body">
        <div class="d-flex flex-wrap gap-2">
          <a href="<?= ADMIN_URL ?>/product-add.php" class="btn-admin-primary"><i class="fa-solid fa-plus me-1"></i>Add Product</a>
          <a href="<?= ADMIN_URL ?>/category-add.php" class="btn-admin-secondary"><i class="fa-solid fa-plus me-1"></i>Add Category</a>
          <a href="<?= ADMIN_URL ?>/products.php" class="btn-admin-secondary"><i class="fa-solid fa-list me-1"></i>Manage Products</a>
          <a href="<?= ADMIN_URL ?>/calculator-requests.php" class="btn-admin-secondary"><i class="fa-solid fa-calculator me-1"></i>Calculator Leads</a>
          <a href="<?= ADMIN_URL ?>/contact-messages.php" class="btn-admin-secondary"><i class="fa-solid fa-envelope me-1"></i>Contact Messages</a>
          <a href="<?= ADMIN_URL ?>/categories.php" class="btn-admin-secondary"><i class="fa-solid fa-layer-group me-1"></i>Manage Categories</a>
          <a href="<?= SITE_URL ?>/" target="_blank" class="btn-admin-outline"><i class="fa-solid fa-globe me-1"></i>View Site</a>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- RECENT PRODUCTS TABLE -->
<div class="admin-card">
  <div class="admin-card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0"><i class="fa-solid fa-clock-rotate-left me-2"></i>Recent Products</h5>
    <a href="<?= ADMIN_URL ?>/products.php" class="btn-admin-sm">View All</a>
  </div>
  <div class="admin-card-body p-0">
    <div class="table-responsive">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Image</th>
            <th>Name</th>
            <th>Category</th>
            <th>Price</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($recentProducts as $p): ?>
          <tr>
            <td>
              <img src="<?= h(productImageUrl($p['image'])) ?>" alt="<?= h($p['name']) ?>"
                   class="admin-prod-thumb"
                   onerror="this.src='<?= ASSETS_URL ?>/img/no-image.jpg'">
            </td>
            <td>
              <strong><?= h($p['name']) ?></strong>
              <?php if ($p['featured']): ?><span class="badge-featured ms-1">Featured</span><?php endif; ?>
              <?php if (!empty($p['brand'])): ?><br><small class="text-muted"><?= h($p['brand']) ?></small><?php endif; ?>
            </td>
            <td><?= h($p['category_name'] ?? '—') ?></td>
            <td><strong><?= formatPrice($p['price']) ?></strong></td>
            <td>
              <span class="status-badge <?= $p['status'] === 'active' ? 'status-active' : 'status-inactive' ?>">
                <?= ucfirst($p['status']) ?>
              </span>
            </td>
            <td>
              <div class="action-btns">
                <a href="<?= SITE_URL ?>/product/<?= h($p['slug']) ?>" target="_blank" class="action-btn view" title="View"><i class="fa-solid fa-eye"></i></a>
                <a href="<?= ADMIN_URL ?>/product-edit.php?id=<?= $p['id'] ?>" class="action-btn edit" title="Edit"><i class="fa-solid fa-pen"></i></a>
                <form method="POST" action="<?= ADMIN_URL ?>/product-delete.php" style="display:inline"
                      onsubmit="return confirm('Delete this product?')">
                  <?php csrfField(); ?>
                  <input type="hidden" name="id" value="<?= $p['id'] ?>">
                  <button type="submit" class="action-btn delete" title="Delete" style="border:none"><i class="fa-solid fa-trash"></i></button>
                </form>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
