<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';

requireAdminLogin();

// Status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    verifyCsrf();
    $id = (int)$_POST['id'];
    $st = $_POST['status'];
    if (in_array($st, ['new','read','replied'], true)) {
        getDB()->prepare('UPDATE contact_messages SET status=? WHERE id=?')->execute([$st, $id]);
    }
    header('Location: ' . ADMIN_URL . '/contact-messages.php' . (!empty($_GET['status']) ? '?status='.$_GET['status'] : ''));
    exit;
}

$statusFilter = $_GET['status'] ?? '';
$messages = getAllContactMessages($statusFilter);

$adminPageTitle = 'Contact Messages';
include __DIR__ . '/includes/header.php';
?>

<div class="admin-page-header">
  <h1 class="admin-page-title"><i class="fa-solid fa-envelope me-2"></i>Contact Form Messages</h1>
  <a href="<?= ADMIN_URL ?>/email-test.php" class="btn-admin-outline">
    <i class="fa-solid fa-paper-plane me-1"></i>Test Email Setup
  </a>
</div>

<?php showFlash(); ?>

<div class="d-flex gap-2 mb-4 flex-wrap">
  <a href="<?= ADMIN_URL ?>/contact-messages.php" class="btn-admin-<?= !$statusFilter?'primary':'outline' ?> btn-sm">All</a>
  <a href="<?= ADMIN_URL ?>/contact-messages.php?status=new" class="btn-admin-<?= $statusFilter==='new'?'primary':'outline' ?> btn-sm">New</a>
  <a href="<?= ADMIN_URL ?>/contact-messages.php?status=read" class="btn-admin-<?= $statusFilter==='read'?'primary':'outline' ?> btn-sm">Read</a>
  <a href="<?= ADMIN_URL ?>/contact-messages.php?status=replied" class="btn-admin-<?= $statusFilter==='replied'?'primary':'outline' ?> btn-sm">Replied</a>
</div>

<?php if ($messages): ?>
<?php foreach ($messages as $m): ?>
<div class="admin-card mb-3">
  <div class="admin-card-body">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
      <div style="flex:1;min-width:240px">
        <div class="d-flex align-items-center gap-2 flex-wrap mb-2">
          <strong style="font-size:15px"><?= h($m['name']) ?></strong>
          <?php if ($m['status']==='new'): ?><span class="status-badge status-order">New</span><?php endif; ?>
          <?php if ($m['email_sent']): ?><span class="status-badge status-active"><i class="fa-solid fa-envelope-circle-check me-1"></i>Emailed</span><?php endif; ?>
          <span class="text-muted small ms-auto"><?= date('d M Y, h:i A', strtotime($m['created_at'])) ?></span>
        </div>
        <div class="row g-2 mb-2" style="font-size:13px">
          <div class="col-auto"><i class="fa-solid fa-phone text-muted me-1"></i><?= h($m['phone']) ?></div>
          <?php if ($m['email']): ?><div class="col-auto"><i class="fa-solid fa-envelope text-muted me-1"></i><?= h($m['email']) ?></div><?php endif; ?>
          <?php if ($m['service']): ?><div class="col-auto"><i class="fa-solid fa-toolbox text-muted me-1"></i><?= h($m['service']) ?></div><?php endif; ?>
          <?php if ($m['area']): ?><div class="col-auto"><i class="fa-solid fa-location-dot text-muted me-1"></i><?= h($m['area']) ?></div><?php endif; ?>
          <?php if ($m['cameras']): ?><div class="col-auto"><i class="fa-solid fa-camera text-muted me-1"></i><?= h($m['cameras']) ?></div><?php endif; ?>
        </div>
        <?php if ($m['message']): ?>
        <p class="mb-0 small" style="background:var(--admin-bg);padding:10px 14px;border-radius:8px;color:#444"><?= nl2br(h($m['message'])) ?></p>
        <?php endif; ?>
      </div>
      <div class="d-flex flex-column gap-2" style="min-width:180px">
        <div class="d-flex gap-2">
          <a href="tel:<?= h($m['phone']) ?>" class="action-btn" style="background:rgba(13,110,253,.1);color:#0d6efd" title="Call"><i class="fa-solid fa-phone"></i></a>
          <a href="https://wa.me/<?= preg_replace('/[^0-9]/','',$m['phone']) ?>" target="_blank" class="action-btn" style="background:rgba(37,211,102,.1);color:#25D366" title="WhatsApp"><i class="fa-brands fa-whatsapp"></i></a>
          <?php if ($m['email']): ?>
          <a href="mailto:<?= h($m['email']) ?>" class="action-btn" style="background:rgba(230,57,70,.1);color:var(--admin-accent)" title="Email"><i class="fa-solid fa-envelope"></i></a>
          <?php endif; ?>
        </div>
        <form method="POST">
          <?php csrfField(); ?>
          <input type="hidden" name="id" value="<?= $m['id'] ?>">
          <input type="hidden" name="update_status" value="1">
          <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="new"     <?= $m['status']==='new'?'selected':'' ?>>New</option>
            <option value="read"    <?= $m['status']==='read'?'selected':'' ?>>Read</option>
            <option value="replied" <?= $m['status']==='replied'?'selected':'' ?>>Replied</option>
          </select>
        </form>
      </div>
    </div>
  </div>
</div>
<?php endforeach; ?>
<?php else: ?>
<div class="admin-card">
  <div class="admin-card-body text-center py-5 text-muted">
    <i class="fa-solid fa-inbox fa-2x mb-3"></i>
    <p class="mb-0">No contact messages yet.</p>
  </div>
</div>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
