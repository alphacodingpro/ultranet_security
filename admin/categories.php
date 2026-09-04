<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';

requireAdminLogin();

$db         = getDB();
$categories = $db->query(
    'SELECT c.*, COUNT(p.id) as product_count FROM categories c
     LEFT JOIN products p ON p.category_id = c.id
     GROUP BY c.id ORDER BY c.name ASC'
)->fetchAll();

$adminPageTitle = 'Categories';
include __DIR__ . '/includes/header.php';
?>

<div class="admin-page-header">
  <h1 class="admin-page-title"><i class="fa-solid fa-layer-group me-2"></i>All Categories</h1>
  <a href="<?= ADMIN_URL ?>/category-add.php" class="btn-admin-primary"><i class="fa-solid fa-plus me-1"></i>Add Category</a>
</div>

<?php showFlash(); ?>

<div class="admin-card">
  <div class="admin-card-body p-0">
    <div class="table-responsive">
      <table class="admin-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Icon</th>
            <th>Name</th>
            <th>Slug (SEO URL)</th>
            <th>Description</th>
            <th>Products</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($categories): ?>
          <?php foreach ($categories as $cat): ?>
          <tr>
            <td class="text-muted small"><?= $cat['id'] ?></td>
            <td><i class="<?= h($cat['icon']) ?> fa-lg" style="color:var(--accent)"></i></td>
            <td><strong><?= h($cat['name']) ?></strong></td>
            <td><code class="small">/products.php?category=<?= h($cat['slug']) ?></code></td>
            <td class="text-muted small"><?= h(substr($cat['description'] ?? '—', 0, 60)) ?></td>
            <td>
              <a href="<?= ADMIN_URL ?>/products.php?cat=<?= $cat['id'] ?>" class="badge bg-primary text-decoration-none">
                <?= $cat['product_count'] ?> products
              </a>
            </td>
            <td>
              <div class="action-btns">
                <a href="<?= SITE_URL ?>/products.php?category=<?= h($cat['slug']) ?>" target="_blank" class="action-btn view" title="View"><i class="fa-solid fa-eye"></i></a>
                <a href="<?= ADMIN_URL ?>/category-edit.php?id=<?= $cat['id'] ?>" class="action-btn edit" title="Edit"><i class="fa-solid fa-pen"></i></a>
                <form method="POST" action="<?= ADMIN_URL ?>/category-delete.php" style="display:inline"
                      onsubmit="return confirm('Delete category \'<?= h(addslashes($cat['name'])) ?>\'? This will also delete all products in it!')">
                  <?php csrfField(); ?>
                  <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                  <button type="submit" class="action-btn delete" title="Delete" style="border:none">
                    <i class="fa-solid fa-trash"></i>
                  </button>
                </form>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php else: ?>
          <tr><td colspan="7" class="text-center py-4 text-muted">No categories yet. <a href="<?= ADMIN_URL ?>/category-add.php">Add first category →</a></td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
