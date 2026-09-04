<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';

requireAdminLogin();

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $discount = trim($_POST['package_discount_percent'] ?? '5');
    $headroom = trim($_POST['poe_headroom_percent'] ?? '20');
    $camWatts = trim($_POST['default_camera_watts'] ?? '9');

    if (!is_numeric($discount) || $discount < 0 || $discount > 50) $errors[] = 'Discount must be between 0 and 50%.';
    if (!is_numeric($headroom) || $headroom < 0 || $headroom > 100) $errors[] = 'PoE headroom must be between 0 and 100%.';
    if (!is_numeric($camWatts) || $camWatts <= 0) $errors[] = 'Default camera watts must be a positive number.';

    if (empty($errors)) {
        setSetting('package_discount_percent', (string)$discount);
        setSetting('poe_headroom_percent', (string)$headroom);
        setSetting('default_camera_watts', (string)$camWatts);
        setFlash('success', 'Calculator settings updated.');
        header('Location: ' . ADMIN_URL . '/calculator-settings.php');
        exit;
    }
}

$discount = getSetting('package_discount_percent', 5);
$headroom = getSetting('poe_headroom_percent', 20);
$camWatts = getSetting('default_camera_watts', 9);

// Quick stats on products tagged for calculator
$db = getDB();
$counts = [];
foreach (['camera','nvr','dvr','hdd','poe_switch','cable'] as $t) {
    $st = $db->prepare("SELECT COUNT(*) FROM products WHERE product_type=? AND status='active'");
    $st->execute([$t]);
    $counts[$t] = $st->fetchColumn();
}

$adminPageTitle = 'Calculator Settings';
include __DIR__ . '/includes/header.php';
?>

<div class="admin-page-header">
  <h1 class="admin-page-title"><i class="fa-solid fa-gear me-2"></i>Calculator Settings</h1>
</div>

<?php showFlash(); ?>
<?php if ($errors): ?>
<div class="alert alert-danger mb-4"><ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= h($e) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<div class="row g-4">
  <div class="col-lg-6">
    <div class="admin-card mb-4">
      <div class="admin-card-header"><h5><i class="fa-solid fa-sliders me-2"></i>Pricing & Calculation Settings</h5></div>
      <div class="admin-card-body">
        <form method="POST">
          <?php csrfField(); ?>
          <div class="mb-3">
            <label class="form-label fw-600">Package Discount (%)</label>
            <input type="number" name="package_discount_percent" class="form-control" step="0.5" min="0" max="50" value="<?= h($discount) ?>">
            <small class="text-muted">Automatically applied when a client builds a full package via the calculator.</small>
          </div>
          <div class="mb-3">
            <label class="form-label fw-600">PoE Wattage Headroom (%)</label>
            <input type="number" name="poe_headroom_percent" class="form-control" step="1" min="0" max="100" value="<?= h($headroom) ?>">
            <small class="text-muted">Safety margin added on top of raw camera wattage when recommending a PoE switch.</small>
          </div>
          <div class="mb-4">
            <label class="form-label fw-600">Default Camera Power Draw (Watts)</label>
            <input type="number" name="default_camera_watts" class="form-control" step="0.5" min="1" value="<?= h($camWatts) ?>">
            <small class="text-muted">Used when a selected camera product doesn't have its own wattage set.</small>
          </div>
          <button type="submit" class="btn-admin-primary w-100 py-3">
            <i class="fa-solid fa-floppy-disk me-2"></i>Save Settings
          </button>
        </form>
      </div>
    </div>
  </div>

  <div class="col-lg-6">
    <div class="admin-card mb-4">
      <div class="admin-card-header"><h5><i class="fa-solid fa-triangle-exclamation me-2"></i>Calculator Product Status</h5></div>
      <div class="admin-card-body">
        <p class="text-muted small mb-3">
          The calculator picks real products from your catalog. Make sure you have at least
          one active product tagged for each type below (set <strong>Product Type</strong> when
          adding/editing a product).
        </p>
        <?php
        $labels = ['camera'=>'Cameras','nvr'=>'NVR (for IP systems)','dvr'=>'DVR (for Analog systems)','hdd'=>'Hard Disks','poe_switch'=>'PoE Switches','cable'=>'Cable (priced per metre)'];
        foreach ($labels as $type => $label):
          $ok = $counts[$type] > 0;
        ?>
        <div class="d-flex justify-content-between align-items-center py-2" style="border-bottom:1px solid var(--admin-border)">
          <span style="font-size:14px"><?= $label ?></span>
          <?php if ($ok): ?>
          <span class="status-badge status-active"><i class="fa-solid fa-circle-check me-1"></i><?= $counts[$type] ?> product<?= $counts[$type]>1?'s':'' ?></span>
          <?php else: ?>
          <span class="status-badge status-inactive"><i class="fa-solid fa-circle-xmark me-1"></i>None added</span>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
        <a href="<?= ADMIN_URL ?>/product-add.php" class="btn-admin-primary btn-sm mt-3">
          <i class="fa-solid fa-plus me-1"></i>Add Product
        </a>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
