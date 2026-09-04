<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';

$ref = trim($_GET['ref'] ?? '');
$req = $ref ? getCalculatorRequestByRef($ref) : null;

if (!$req) {
    header('HTTP/1.0 404 Not Found');
    die('<div style="font-family:sans-serif;padding:60px;text-align:center">
         <h2>Estimate not found</h2>
         <p><a href="' . SITE_URL . '/calculator.php">Go back to the calculator</a></p></div>');
}

$items = $req['items'];
$gbLabel = function ($gb) {
    if (!$gb) return '—';
    return $gb >= 1000 ? number_format($gb / 1000, $gb % 1000 === 0 ? 0 : 1) . ' TB' : $gb . ' GB';
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Estimate <?= h($req['ref_code']) ?> | UltraNet Security</title>
<meta name="robots" content="noindex, nofollow">
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
  :root{--primary:#0a1628;--accent:#e63946;--border:#e0e4ef;--muted:#6c757d;--light-bg:#f8f9fc}
  *{box-sizing:border-box;margin:0;padding:0}
  body{font-family:'DM Sans',sans-serif;color:var(--primary);background:#f4f6fb;padding:30px 16px}
  .sheet{max-width:820px;margin:0 auto;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 8px 40px rgba(10,22,40,.1)}

  .est-header{background:var(--primary);padding:32px 40px;display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:16px}
  .est-brand{display:flex;align-items:center;gap:12px}
  .est-brand-icon{width:46px;height:46px;background:var(--accent);border-radius:10px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:20px}
  .est-brand-name{font-family:'Bebas Neue',sans-serif;font-size:26px;color:#fff}
  .est-brand-name span{color:var(--accent)}
  .est-brand-sub{color:#8fa8c4;font-size:12px;margin-top:2px}
  .est-meta{text-align:right;color:#cdd5e0;font-size:13px;line-height:1.8}
  .est-meta .ref{font-family:'Bebas Neue',sans-serif;font-size:20px;color:#fff;letter-spacing:1px}

  .est-body{padding:34px 40px}
  .est-section-title{font-family:'Bebas Neue',sans-serif;font-size:18px;color:var(--primary);margin-bottom:14px;letter-spacing:.5px;display:flex;align-items:center;gap:8px}
  .est-section-title i{color:var(--accent)}

  .est-info-grid{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:30px}
  .est-info-box{background:var(--light-bg);border-radius:10px;padding:16px 18px}
  .est-info-box h4{font-size:11px;text-transform:uppercase;letter-spacing:1px;color:var(--muted);margin-bottom:8px;font-weight:700}
  .est-info-row{display:flex;justify-content:space-between;font-size:13px;padding:4px 0;color:#333}
  .est-info-row strong{color:var(--primary)}

  .est-item{display:flex;align-items:center;gap:14px;padding:14px;border:1px solid var(--border);border-radius:10px;margin-bottom:10px}
  .est-item img{width:54px;height:54px;object-fit:cover;border-radius:8px;border:1px solid var(--border);flex-shrink:0}
  .est-item-info{flex:1}
  .est-item-name{font-size:14px;font-weight:600}
  .est-item-meta{font-size:12px;color:var(--muted)}
  .est-item-price{font-family:'Bebas Neue',sans-serif;font-size:17px;white-space:nowrap;text-align:right}
  .est-item-price small{display:block;font-family:'DM Sans',sans-serif;font-size:11px;color:var(--muted);font-weight:400}

  .est-totals{margin-top:20px;max-width:340px;margin-left:auto}
  .est-total-row{display:flex;justify-content:space-between;padding:7px 0;font-size:14px}
  .est-total-row.discount{color:#28a745}
  .est-total-row.grand{border-top:2px solid var(--primary);margin-top:6px;padding-top:12px;font-size:22px;font-weight:700;font-family:'Bebas Neue',sans-serif}

  .est-footer{background:var(--light-bg);padding:26px 40px;font-size:12px;color:var(--muted);line-height:1.8}
  .est-footer strong{color:var(--primary)}

  .est-actions{max-width:820px;margin:20px auto 0;display:flex;gap:12px;justify-content:center;flex-wrap:wrap}
  .btn-print{background:var(--accent);color:#fff;border:none;padding:13px 28px;border-radius:9px;font-weight:700;font-size:14px;cursor:pointer;display:inline-flex;align-items:center;gap:8px;text-decoration:none}
  .btn-wa{background:#25D366;color:#fff;border:none;padding:13px 28px;border-radius:9px;font-weight:700;font-size:14px;cursor:pointer;display:inline-flex;align-items:center;gap:8px;text-decoration:none}
  .btn-back{background:transparent;border:1.5px solid var(--border);color:var(--primary);padding:13px 28px;border-radius:9px;font-weight:600;font-size:14px;text-decoration:none;display:inline-flex;align-items:center;gap:8px}

  .validity-badge{display:inline-block;background:rgba(230,57,70,.1);color:var(--accent);font-size:11px;font-weight:700;padding:5px 12px;border-radius:50px;margin-top:6px}

  @media print {
    body{background:#fff;padding:0}
    .est-actions{display:none}
    .sheet{box-shadow:none;border-radius:0}
  }
  @media(max-width:640px){
    .est-header,.est-body,.est-footer{padding:22px 20px}
    .est-info-grid{grid-template-columns:1fr}
    .est-meta{text-align:left}
  }
</style>
</head>
<body>

<div class="sheet" id="estimateSheet">

  <div class="est-header">
    <div class="est-brand">
      <div class="est-brand-icon"><i class="fa-solid fa-camera"></i></div>
      <div>
        <div class="est-brand-name">UltraNet <span>Security</span></div>
        <div class="est-brand-sub">CCTV Installation Karachi — 0309-1243189</div>
      </div>
    </div>
    <div class="est-meta">
      <div class="ref">#<?= h($req['ref_code']) ?></div>
      <div><?= date('d M Y', strtotime($req['created_at'])) ?></div>
      <div class="validity-badge"><i class="fa-solid fa-clock me-1"></i>Valid for 15 days</div>
    </div>
  </div>

  <div class="est-body">

    <div class="est-info-grid">
      <div class="est-info-box">
        <h4>Prepared For</h4>
        <div class="est-info-row"><span>Name</span><strong><?= h($req['client_name']) ?></strong></div>
        <div class="est-info-row"><span>Phone</span><strong><?= h($req['client_phone']) ?></strong></div>
        <?php if ($req['client_email']): ?>
        <div class="est-info-row"><span>Email</span><strong><?= h($req['client_email']) ?></strong></div>
        <?php endif; ?>
      </div>
      <div class="est-info-box">
        <h4>System Specification</h4>
        <div class="est-info-row"><span>System Type</span><strong><?= $req['system_type']==='ip' ? 'IP (NVR + PoE)' : 'Analog / HD (DVR)' ?></strong></div>
        <div class="est-info-row"><span>Cameras</span><strong><?= (int)$req['camera_count'] ?> x <?= h(strtoupper($req['resolution_label'] ?? '')) ?></strong></div>
        <div class="est-info-row"><span>Storage Needed</span><strong><?= $gbLabel($req['required_hdd_gb']) ?> (<?= (int)$req['recording_days'] ?> days)</strong></div>
        <?php if ($req['system_type']==='ip'): ?>
        <div class="est-info-row"><span>PoE Budget</span><strong><?= (int)$req['required_poe_watts'] ?> W</strong></div>
        <?php endif; ?>
        <?php if ($req['lens_note']): ?>
        <div class="est-info-row"><span>Lens</span><strong style="font-size:11px"><?= h($req['lens_note']) ?></strong></div>
        <?php endif; ?>
      </div>
    </div>

    <div class="est-section-title"><i class="fa-solid fa-boxes-stacked"></i> Package Items</div>
    <?php foreach ($items as $it): ?>
    <div class="est-item">
      <img src="<?= h($it['image']) ?>" alt="<?= h($it['name']) ?>" onerror="this.src='<?= ASSETS_URL ?>/img/no-image.jpg'">
      <div class="est-item-info">
        <div class="est-item-name"><?= h($it['name']) ?></div>
        <div class="est-item-meta"><?= h($it['meta'] ?? '') ?></div>
      </div>
      <div class="est-item-price">
        <?= formatPrice($it['line_total']) ?>
        <small><?= (int)$it['qty'] ?> × <?= formatPrice($it['unit_price']) ?></small>
      </div>
    </div>
    <?php endforeach; ?>

    <div class="est-totals">
      <div class="est-total-row"><span>Subtotal</span><span><?= formatPrice($req['subtotal']) ?></span></div>
      <?php if ($req['discount_percent'] > 0): ?>
      <div class="est-total-row discount"><span>Package Discount (<?= rtrim(rtrim(number_format($req['discount_percent'],2),'0'),'.') ?>%)</span><span>- <?= formatPrice($req['discount_amount']) ?></span></div>
      <?php endif; ?>
      <div class="est-total-row grand"><span>Total</span><span><?= formatPrice($req['grand_total']) ?></span></div>
    </div>

  </div>

  <div class="est-footer">
    <strong>Note:</strong> This estimate includes hardware only. Professional installation, cabling labour, and configuration are quoted separately based on site survey. Prices are subject to change based on final site requirements. Contact us to schedule your free site survey.<br><br>
    <strong>UltraNet Security</strong> — House No 239, Manzoor Colony, Hill Town St 10, Karachi 75460<br>
    📞 0309-1243189 &nbsp;|&nbsp; 🌐 <?= SITE_URL ?>
  </div>
</div>

<div class="est-actions">
  <button class="btn-print" onclick="window.print()"><i class="fa-solid fa-file-pdf"></i> Download / Print PDF</button>
  <a class="btn-wa" href="https://wa.me/923091243189?text=<?= urlencode('Hi, I received estimate '.$req['ref_code'].'. I would like to proceed.') ?>" target="_blank">
    <i class="fa-brands fa-whatsapp"></i> Discuss on WhatsApp
  </a>
  <a class="btn-back" href="<?= SITE_URL ?>/calculator.php"><i class="fa-solid fa-arrow-left"></i> Back to Calculator</a>
</div>

</body>
</html>
