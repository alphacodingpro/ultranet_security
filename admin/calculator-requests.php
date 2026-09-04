<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';

requireAdminLogin();

// Quick status update from list (dropdown auto-submit)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    verifyCsrf();
    $id  = (int)$_POST['id'];
    $st  = $_POST['status'];
    if (in_array($st, ['new','contacted','closed'], true)) {
        getDB()->prepare('UPDATE calculator_requests SET status=? WHERE id=?')->execute([$st, $id]);
        setFlash('success', 'Status updated.');
    }
    header('Location: ' . ADMIN_URL . '/calculator-requests.php');
    exit;
}

$statusFilter = $_GET['status'] ?? '';
$requests = getAllCalculatorRequests($statusFilter);

$adminPageTitle = 'Calculator Requests';
include __DIR__ . '/includes/header.php';
?>

<div class="admin-page-header">
  <h1 class="admin-page-title"><i class="fa-solid fa-calculator me-2"></i>Client Calculator Requests</h1>
</div>

<?php showFlash(); ?>

<!-- FILTER TABS -->
<div class="d-flex gap-2 mb-4 flex-wrap">
  <a href="<?= ADMIN_URL ?>/calculator-requests.php" class="btn-admin-<?= !$statusFilter?'primary':'outline' ?> btn-sm">All</a>
  <a href="<?= ADMIN_URL ?>/calculator-requests.php?status=new" class="btn-admin-<?= $statusFilter==='new'?'primary':'outline' ?> btn-sm">New</a>
  <a href="<?= ADMIN_URL ?>/calculator-requests.php?status=contacted" class="btn-admin-<?= $statusFilter==='contacted'?'primary':'outline' ?> btn-sm">Contacted</a>
  <a href="<?= ADMIN_URL ?>/calculator-requests.php?status=closed" class="btn-admin-<?= $statusFilter==='closed'?'primary':'outline' ?> btn-sm">Closed</a>
</div>

<div class="admin-card">
  <div class="admin-card-body p-0">
    <div class="table-responsive">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Ref</th>
            <th>Client</th>
            <th>System</th>
            <th>Cameras</th>
            <th>Total</th>
            <th>Status</th>
            <th>Date</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($requests): ?>
          <?php foreach ($requests as $r): ?>
          <tr>
            <td><code class="small"><?= h($r['ref_code']) ?></code></td>
            <td>
              <strong><?= h($r['client_name']) ?></strong><br>
              <small class="text-muted"><?= h($r['client_phone']) ?></small>
            </td>
            <td><?= $r['system_type']==='ip' ? 'IP / NVR' : 'Analog / DVR' ?></td>
            <td><?= (int)$r['camera_count'] ?></td>
            <td><strong><?= formatPrice($r['grand_total']) ?></strong></td>
            <td>
              <form method="POST" class="d-inline">
          <?php csrfField(); ?>
                <input type="hidden" name="id" value="<?= $r['id'] ?>">
                <input type="hidden" name="update_status" value="1">
                <select name="status" class="form-select form-select-sm status-select-<?= $r['status'] ?>"
                        onchange="this.form.submit()" style="font-size:12px;padding:4px 8px;width:auto">
                  <option value="new"       <?= $r['status']==='new'?'selected':'' ?>>New</option>
                  <option value="contacted" <?= $r['status']==='contacted'?'selected':'' ?>>Contacted</option>
                  <option value="closed"    <?= $r['status']==='closed'?'selected':'' ?>>Closed</option>
                </select>
              </form>
            </td>
            <td class="text-muted small"><?= date('d M, h:i A', strtotime($r['created_at'])) ?></td>
            <td>
              <div class="action-btns">
                <a href="<?= ADMIN_URL ?>/calculator-request-view.php?id=<?= $r['id'] ?>" class="action-btn view" title="View Details">
                  <i class="fa-solid fa-eye"></i>
                </a>
                <a href="<?= SITE_URL ?>/estimate.php?ref=<?= h($r['ref_code']) ?>" target="_blank" class="action-btn edit" title="View Estimate PDF">
                  <i class="fa-solid fa-file-pdf"></i>
                </a>
                <a href="https://wa.me/<?= preg_replace('/[^0-9]/','',$r['client_phone']) ?>" target="_blank" class="action-btn" style="background:rgba(37,211,102,.1);color:#25D366" title="WhatsApp Client">
                  <i class="fa-brands fa-whatsapp"></i>
                </a>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php else: ?>
          <tr><td colspan="8" class="text-center py-4 text-muted">No calculator requests yet.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
