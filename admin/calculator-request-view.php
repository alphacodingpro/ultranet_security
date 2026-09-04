<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';

requireAdminLogin();

$id  = (int)($_GET['id'] ?? 0);
$req = $id ? getCalculatorRequestById($id) : null;
if (!$req) { setFlash('error','Request not found.'); header('Location:'.ADMIN_URL.'/calculator-requests.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status'])) {
    verifyCsrf();
    getDB()->prepare('UPDATE calculator_requests SET status=? WHERE id=?')->execute([$_POST['status'], $id]);
    setFlash('success', 'Status updated.');
    header('Location: ' . ADMIN_URL . '/calculator-request-view.php?id=' . $id);
    exit;
}

$gbLabel = function ($gb) {
    if (!$gb) return '—';
    return $gb >= 1000 ? number_format($gb/1000, $gb%1000===0?0:1).' TB' : $gb.' GB';
};

$adminPageTitle = 'Request ' . $req['ref_code'];
include __DIR__ . '/includes/header.php';
?>

<div class="admin-page-header">
  <h1 class="admin-page-title"><i class="fa-solid fa-calculator me-2"></i><?= h($req['ref_code']) ?></h1>
  <div class="d-flex gap-2">
    <a href="<?= SITE_URL ?>/estimate.php?ref=<?= h($req['ref_code']) ?>" target="_blank" class="btn-admin-outline">
      <i class="fa-solid fa-file-pdf me-1"></i>View Estimate PDF
    </a>
    <a href="<?= ADMIN_URL ?>/calculator-requests.php" class="btn-admin-outline">
      <i class="fa-solid fa-arrow-left me-1"></i>Back
    </a>
  </div>
</div>

<?php showFlash(); ?>

<div class="row g-4">
  <div class="col-lg-8">

    <!-- Client Info -->
    <div class="admin-card mb-4">
      <div class="admin-card-header"><h5><i class="fa-solid fa-user me-2"></i>Client Information</h5></div>
      <div class="admin-card-body">
        <div class="row g-3">
          <div class="col-md-4"><small class="text-muted d-block">Name</small><strong><?= h($req['client_name']) ?></strong></div>
          <div class="col-md-4"><small class="text-muted d-block">Phone</small><strong><?= h($req['client_phone']) ?></strong></div>
          <div class="col-md-4"><small class="text-muted d-block">Email</small><strong><?= h($req['client_email'] ?: '—') ?></strong></div>
        </div>
        <div class="d-flex gap-2 mt-3">
          <a href="tel:<?= h($req['client_phone']) ?>" class="btn-admin-secondary btn-sm"><i class="fa-solid fa-phone me-1"></i>Call</a>
          <a href="https://wa.me/<?= preg_replace('/[^0-9]/','',$req['client_phone']) ?>" target="_blank" class="btn-admin-secondary btn-sm"><i class="fa-brands fa-whatsapp me-1"></i>WhatsApp</a>
          <?php if ($req['client_email']): ?>
          <a href="mailto:<?= h($req['client_email']) ?>" class="btn-admin-secondary btn-sm"><i class="fa-solid fa-envelope me-1"></i>Email</a>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Requirements -->
    <div class="admin-card mb-4">
      <div class="admin-card-header"><h5><i class="fa-solid fa-sliders me-2"></i>System Requirements</h5></div>
      <div class="admin-card-body">
        <div class="row g-3">
          <div class="col-md-4"><small class="text-muted d-block">System Type</small><strong><?= $req['system_type']==='ip'?'IP (NVR+PoE)':'Analog (DVR)' ?></strong></div>
          <div class="col-md-4"><small class="text-muted d-block">Cameras</small><strong><?= (int)$req['camera_count'] ?></strong></div>
          <div class="col-md-4"><small class="text-muted d-block">Resolution</small><strong><?= h(strtoupper($req['resolution_label'] ?? '—')) ?></strong></div>
          <div class="col-md-4"><small class="text-muted d-block">Recording Days</small><strong><?= (int)$req['recording_days'] ?> days</strong></div>
          <div class="col-md-4"><small class="text-muted d-block">Recording Mode</small><strong><?= ucfirst($req['recording_mode']) ?></strong></div>
          <div class="col-md-4"><small class="text-muted d-block">Required Storage</small><strong><?= $gbLabel($req['required_hdd_gb']) ?></strong></div>
          <?php if ($req['system_type']==='ip'): ?>
          <div class="col-md-4"><small class="text-muted d-block">PoE Budget Needed</small><strong><?= (int)$req['required_poe_watts'] ?> W</strong></div>
          <?php endif; ?>
          <div class="col-md-4"><small class="text-muted d-block">Total Cable</small><strong><?= (int)$req['cable_length_m'] ?> m</strong></div>
          <?php if ($req['lens_note']): ?>
          <div class="col-md-8"><small class="text-muted d-block">Lens Preference</small><strong><?= h($req['lens_note']) ?></strong></div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Package Items -->
    <div class="admin-card">
      <div class="admin-card-header"><h5><i class="fa-solid fa-boxes-stacked me-2"></i>Package Items</h5></div>
      <div class="admin-card-body">
        <?php foreach ($req['items'] as $it): ?>
        <div class="d-flex align-items-center gap-3 mb-3 pb-3" style="border-bottom:1px solid var(--admin-border)">
          <img src="<?= h($it['image']) ?>" style="width:50px;height:50px;object-fit:cover;border-radius:8px;border:1px solid var(--admin-border)"
               onerror="this.src='<?= ASSETS_URL ?>/img/no-image.jpg'">
          <div class="flex-grow-1">
            <strong style="font-size:14px"><?= h($it['name']) ?></strong><br>
            <small class="text-muted"><?= h($it['meta'] ?? '') ?></small>
          </div>
          <div class="text-end">
            <strong><?= formatPrice($it['line_total']) ?></strong><br>
            <small class="text-muted"><?= (int)$it['qty'] ?> × <?= formatPrice($it['unit_price']) ?></small>
          </div>
        </div>
        <?php endforeach; ?>
        <div class="d-flex justify-content-between pt-2" style="font-size:14px">
          <span>Subtotal</span><strong><?= formatPrice($req['subtotal']) ?></strong>
        </div>
        <?php if ($req['discount_percent'] > 0): ?>
        <div class="d-flex justify-content-between pt-1" style="font-size:14px;color:#28a745">
          <span>Discount (<?= rtrim(rtrim(number_format($req['discount_percent'],2),'0'),'.') ?>%)</span>
          <strong>- <?= formatPrice($req['discount_amount']) ?></strong>
        </div>
        <?php endif; ?>
        <div class="d-flex justify-content-between pt-2 mt-2" style="border-top:2px solid var(--admin-primary);font-size:20px;font-weight:700">
          <span>Total</span><span><?= formatPrice($req['grand_total']) ?></span>
        </div>
      </div>
    </div>

  </div>

  <!-- Sidebar: Status -->
  <div class="col-lg-4">
    <div class="admin-card">
      <div class="admin-card-header"><h5><i class="fa-solid fa-flag me-2"></i>Status</h5></div>
      <div class="admin-card-body">
        <form method="POST">
          <?php csrfField(); ?>
          <select name="status" class="form-select mb-3" onchange="this.form.submit()">
            <option value="new"       <?= $req['status']==='new'?'selected':'' ?>>New</option>
            <option value="contacted" <?= $req['status']==='contacted'?'selected':'' ?>>Contacted</option>
            <option value="closed"    <?= $req['status']==='closed'?'selected':'' ?>>Closed (Won/Lost)</option>
          </select>
        </form>
        <p class="text-muted small mb-0">Received: <?= date('d M Y, h:i A', strtotime($req['created_at'])) ?></p>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
