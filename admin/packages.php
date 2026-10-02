<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
requireAdminLogin();
$schemaReady = ensurePackageSchema();
$packages = $schemaReady ? getDB()->query('SELECT * FROM packages ORDER BY featured DESC, sort_order ASC, id DESC')->fetchAll() : [];
$adminPageTitle = 'CCTV Packages';
include __DIR__ . '/includes/header.php';
?>
<div class="admin-page-header"><h1 class="admin-page-title"><i class="fa-solid fa-box-open me-2"></i>CCTV Packages</h1><div class="d-flex gap-2"><a href="<?= SITE_URL ?>/packages/" target="_blank" class="btn-admin-outline"><i class="fa-solid fa-eye"></i>Public Page</a><a href="<?= ADMIN_URL ?>/package-add.php" class="btn-admin-primary"><i class="fa-solid fa-plus"></i>Add Package</a></div></div>
<?php showFlash(); ?>
<?php if (!$schemaReady): ?><div class="alert alert-danger">The package database table could not be created. Import <code>database/migration_packages.sql</code> in phpMyAdmin.</div><?php endif; ?>
<div class="admin-card"><div class="admin-card-body p-0"><div class="table-responsive"><table class="admin-table"><thead><tr><th>Order</th><th>Package</th><th>Type</th><th>Cameras</th><th>Price</th><th>Status</th><th>Featured</th><th>Actions</th></tr></thead><tbody>
<?php if ($packages): foreach ($packages as $package): ?>
<tr><td><?= (int)$package['sort_order'] ?></td><td><strong><?= h($package['name']) ?></strong><?php if ($package['badge']): ?><br><span class="badge bg-dark mt-1"><?= h($package['badge']) ?></span><?php endif; ?></td><td><?= h(strtoupper($package['system_type'])) ?></td><td><?= $package['camera_count'] !== null ? (int)$package['camera_count'] : '—' ?></td><td><?= $package['price'] !== null ? formatPrice((float)$package['price']) : '<span class="text-muted">Quote only</span>' ?></td><td><span class="status-badge status-<?= $package['status'] === 'active' ? 'active' : 'inactive' ?>"><?= h(ucfirst($package['status'])) ?></span></td><td><?= $package['featured'] ? '<i class="fa-solid fa-star text-warning"></i>' : '—' ?></td><td><div class="action-btns"><a class="action-btn edit" href="<?= ADMIN_URL ?>/package-edit.php?id=<?= (int)$package['id'] ?>" title="Edit"><i class="fa-solid fa-pen"></i></a><form method="POST" action="<?= ADMIN_URL ?>/package-delete.php" onsubmit="return confirm('Delete this package?')"><?php csrfField(); ?><input type="hidden" name="id" value="<?= (int)$package['id'] ?>"><button class="action-btn delete border-0" title="Delete"><i class="fa-solid fa-trash"></i></button></form></div></td></tr>
<?php endforeach; else: ?><tr><td colspan="8" class="text-center py-5 text-muted">No packages have been added yet. <a href="<?= ADMIN_URL ?>/package-add.php">Add the first package →</a></td></tr><?php endif; ?>
</tbody></table></div></div></div>
<?php include __DIR__ . '/includes/footer.php'; ?>
