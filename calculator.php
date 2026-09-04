<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle    = 'CCTV Calculator Karachi | UltraNet Security';
$metaDesc     = 'Free CCTV calculator — work out cameras, NVR/DVR channels, HDD storage & PoE wattage needed. Get an instant package estimate from UltraNet Security.';
$canonicalSlug = '/calculator.php';

// Pull all calculator-relevant products (for JS to use client-side)
$cameras    = getProductsByType('camera');
$nvrs       = getProductsByType('nvr');
$dvrs       = getProductsByType('dvr');
$hdds       = getProductsByType('hdd');
$poeSwitches= getProductsByType('poe_switch');
$cableProd  = getCableProduct();

$packageDiscount = (float)getSetting('package_discount_percent', 5);
$defaultCamWatts = (float)getSetting('default_camera_watts', 9);
$poeHeadroom      = (float)getSetting('poe_headroom_percent', 20);

// Build JSON payload for JS (only fields needed, keeps it light)
function pickFields(array $rows, array $fields): array {
    return array_map(function ($r) use ($fields) {
        $o = [];
        foreach ($fields as $f) $o[$f] = $r[$f] ?? null;
        $o['image_url'] = productImageUrl($r['image'] ?? null);
        return $o;
    }, $rows);
}
$jsData = [
    'cameras'     => pickFields($cameras,     ['id','name','brand','price','image','camera_watts','short_desc']),
    'nvrs'        => pickFields($nvrs,        ['id','name','brand','price','image','channels']),
    'dvrs'        => pickFields($dvrs,        ['id','name','brand','price','image','channels']),
    'hdds'        => pickFields($hdds,        ['id','name','brand','price','image','storage_gb']),
    'poeSwitches' => pickFields($poeSwitches, ['id','name','brand','price','image','poe_ports','poe_budget_watts']),
    'cable'       => $cableProd ? pickFields([$cableProd], ['id','name','brand','price_per_meter','image'])[0] : null,
    'settings'    => [
        'packageDiscount' => $packageDiscount,
        'defaultCamWatts' => $defaultCamWatts,
        'poeHeadroom'     => $poeHeadroom,
    ],
];

include __DIR__ . '/includes/header.php';
?>

<style>
  /* Scoped calculator styles — reuses site color variables, no design changes elsewhere */
  .calc-hero{background:var(--primary);padding:56px 0 40px;position:relative;overflow:hidden}
  .calc-hero h1{font-family:'Bebas Neue',sans-serif;font-size:clamp(32px,5vw,52px);color:#fff;margin-bottom:10px}
  .calc-hero p{color:#8fa8c4;font-size:15px;max-width:560px}
  .calc-mode-toggle{display:flex;gap:10px;margin-top:22px;flex-wrap:wrap}
  .calc-mode-btn{background:rgba(255,255,255,.06);border:1.5px solid rgba(255,255,255,.15);color:#c8d8ea;padding:12px 22px;border-radius:10px;font-size:14px;font-weight:600;cursor:pointer;transition:all .2s;display:flex;align-items:center;gap:8px}
  .calc-mode-btn.active{background:var(--accent);border-color:var(--accent);color:#fff}
  .calc-mode-btn i{font-size:16px}

  .calc-card{background:#fff;border-radius:16px;border:1px solid var(--border);box-shadow:var(--shadow);padding:26px}
  .calc-section-title{font-family:'Bebas Neue',sans-serif;font-size:22px;color:var(--primary);margin-bottom:16px;display:flex;align-items:center;gap:10px}
  .calc-section-title i{color:var(--accent)}
  .calc-label{font-size:13px;font-weight:600;color:var(--primary);margin-bottom:6px;display:block}
  .calc-hint{font-size:12px;color:var(--muted);margin-top:4px}
  .calc-form-control,.calc-form-select{width:100%;border:1px solid var(--border);border-radius:9px;padding:11px 14px;font-size:14px;font-family:'DM Sans',sans-serif}
  .calc-form-control:focus,.calc-form-select:focus{outline:none;border-color:var(--accent);box-shadow:0 0 0 3px rgba(230,57,70,.1)}
  .calc-row{margin-bottom:18px}
  .calc-radio-group{display:flex;gap:10px;flex-wrap:wrap}
  .calc-radio-pill{border:1.5px solid var(--border);border-radius:9px;padding:10px 18px;font-size:13px;font-weight:600;color:var(--muted);cursor:pointer;transition:all .2s}
  .calc-radio-pill input{display:none}
  .calc-radio-pill.selected{border-color:var(--accent);background:rgba(230,57,70,.06);color:var(--accent)}

  .calc-results{position:sticky;top:80px}
  .calc-result-box{background:var(--light-bg);border-radius:12px;padding:16px 18px;margin-bottom:10px}
  .calc-result-box .rlabel{font-size:11px;text-transform:uppercase;letter-spacing:1px;color:var(--muted);font-weight:700}
  .calc-result-box .rvalue{font-family:'Bebas Neue',sans-serif;font-size:26px;color:var(--primary);line-height:1.2}
  .calc-result-box .rvalue span{font-size:14px;font-family:'DM Sans',sans-serif;color:var(--muted);font-weight:400}
  .calc-warn{background:#fff8e6;border:1px solid #f0d060;border-radius:10px;padding:12px 14px;font-size:13px;color:#8a6500;margin-top:10px}

  /* Package product row */
  .pkg-item{display:flex;align-items:center;gap:14px;padding:12px;border:1px solid var(--border);border-radius:12px;margin-bottom:10px;background:#fff}
  .pkg-item img{width:56px;height:56px;object-fit:cover;border-radius:8px;flex-shrink:0;border:1px solid var(--border)}
  .pkg-item-info{flex:1;min-width:0}
  .pkg-item-name{font-size:14px;font-weight:600;color:var(--primary);margin-bottom:2px}
  .pkg-item-meta{font-size:12px;color:var(--muted)}
  .pkg-item-price{font-family:'Bebas Neue',sans-serif;font-size:17px;color:var(--primary);white-space:nowrap}
  .pkg-item-price small{display:block;font-family:'DM Sans',sans-serif;font-size:11px;color:var(--muted);font-weight:400}

  .calc-total-row{display:flex;justify-content:space-between;padding:9px 0;font-size:14px;color:#444}
  .calc-total-row.grand{border-top:2px solid var(--primary);margin-top:8px;padding-top:14px;font-size:20px;font-weight:700;color:var(--primary)}
  .calc-total-row.discount{color:#28a745}

  .calc-btn-primary{background:var(--accent);color:#fff;border:none;padding:14px 26px;border-radius:10px;font-weight:700;font-size:15px;display:inline-flex;align-items:center;gap:9px;cursor:pointer;width:100%;justify-content:center;transition:background .2s}
  .calc-btn-primary:hover{background:#c62836}
  .calc-btn-primary:disabled{background:#c8c8c8;cursor:not-allowed}
  .calc-btn-outline{background:transparent;border:1.5px solid var(--border);color:var(--primary);padding:14px 26px;border-radius:10px;font-weight:600;font-size:14px;cursor:pointer;width:100%}

  #calcStep2{display:none}
  #leadModal{display:none;position:fixed;inset:0;background:rgba(10,22,40,.65);z-index:9998;align-items:center;justify-content:center;padding:20px}
  #leadModal .modal-box{background:#fff;border-radius:16px;padding:30px;max-width:440px;width:100%}
  #leadModal h4{font-family:'Bebas Neue',sans-serif;font-size:24px;margin-bottom:6px;color:var(--primary)}
  #leadModal p{color:var(--muted);font-size:14px;margin-bottom:18px}

  @media(max-width:992px){.calc-results{position:static}}
</style>

<!-- CALC HERO -->
<section class="calc-hero">
  <div class="container">
    <p class="section-tag" style="color:rgba(230,57,70,.7)">Free Tool</p>
    <h1>CCTV SYSTEM <span style="color:var(--accent)">CALCULATOR</span></h1>
    <p>Enter your requirements and instantly calculate the right cameras, NVR/DVR, hard disk storage, and PoE switch wattage for your property in Karachi.</p>

    <div class="calc-mode-toggle">
      <button type="button" class="calc-mode-btn active" data-mode="calculate" id="modeCalculateBtn">
        <i class="fa-solid fa-calculator"></i> Just Calculate
      </button>
      <button type="button" class="calc-mode-btn" data-mode="package" id="modePackageBtn">
        <i class="fa-solid fa-boxes-stacked"></i> Build My Package (with pricing)
      </button>
    </div>
  </div>
</section>

<div class="container py-5">
  <div class="row g-4">

    <!-- LEFT: FORM -->
    <div class="col-lg-7">
      <div class="calc-card mb-4">
        <div class="calc-section-title"><i class="fa-solid fa-sliders"></i> Your Requirements</div>

        <div class="calc-row">
          <label class="calc-label">System Type</label>
          <div class="calc-radio-group">
            <label class="calc-radio-pill selected" data-group="systemType">
              <input type="radio" name="systemType" value="ip" checked> <i class="fa-solid fa-wifi me-1"></i> IP Cameras (NVR + PoE)
            </label>
            <label class="calc-radio-pill" data-group="systemType">
              <input type="radio" name="systemType" value="analog"> <i class="fa-solid fa-video me-1"></i> Analog / HD (DVR)
            </label>
          </div>
        </div>

        <div class="row g-3">
          <div class="col-md-6 calc-row">
            <label class="calc-label">Number of Cameras</label>
            <input type="number" id="cameraCount" class="calc-form-control" value="8" min="1" max="128">
          </div>
          <div class="col-md-6 calc-row">
            <label class="calc-label">Camera Resolution</label>
            <select id="resolutionTier" class="calc-form-select">
              <option value="2mp">2MP (Full HD)</option>
              <option value="4mp" selected>4MP</option>
              <option value="5mp">5MP</option>
              <option value="8mp">8MP (4K)</option>
            </select>
          </div>
        </div>

        <div class="row g-3">
          <div class="col-md-6 calc-row">
            <label class="calc-label">Choose Camera Model</label>
            <select id="cameraProduct" class="calc-form-select"></select>
          </div>
          <div class="col-md-6 calc-row">
            <label class="calc-label">Lens Type</label>
            <select id="lensType" class="calc-form-select">
              <option value="2.8mm Fixed (Wide Angle ~90°)">2.8mm Fixed — Wide angle</option>
              <option value="3.6mm Fixed (Standard ~72°)" selected>3.6mm Fixed — Standard</option>
              <option value="6mm Fixed (Narrow ~45°)">6mm Fixed — Narrow / long view</option>
              <option value="2.8–12mm Varifocal (Adjustable zoom)">2.8–12mm Varifocal — Adjustable</option>
              <option value="8–32mm Varifocal (Long range zoom)">8–32mm Varifocal — Long range</option>
            </select>
            <p class="calc-hint">Advisory note only — included in your estimate PDF.</p>
          </div>
        </div>

        <div class="row g-3">
          <div class="col-md-6 calc-row">
            <label class="calc-label">Recording Retention (days)</label>
            <select id="recordingDays" class="calc-form-select">
              <option value="7">7 Days</option>
              <option value="15">15 Days</option>
              <option value="30" selected>30 Days</option>
              <option value="60">60 Days</option>
              <option value="90">90 Days</option>
            </select>
          </div>
          <div class="col-md-6 calc-row">
            <label class="calc-label">Recording Mode</label>
            <div class="calc-radio-group">
              <label class="calc-radio-pill selected" data-group="recMode">
                <input type="radio" name="recMode" value="continuous" checked> 24/7 Continuous
              </label>
              <label class="calc-radio-pill" data-group="recMode">
                <input type="radio" name="recMode" value="motion"> Motion Only
              </label>
            </div>
          </div>
        </div>

        <div class="calc-row">
          <label class="calc-label">Average Cable Length per Camera (metres)</label>
          <input type="number" id="cableLength" class="calc-form-control" value="25" min="1" max="500">
          <p class="calc-hint" id="cableWarn"></p>
        </div>
      </div>
    </div>

    <!-- RIGHT: RESULTS -->
    <div class="col-lg-5">
      <div class="calc-results">
        <div class="calc-card">
          <div class="calc-section-title"><i class="fa-solid fa-chart-simple"></i> Calculation Results</div>

          <div class="calc-result-box">
            <div class="rlabel">Recommended NVR/DVR</div>
            <div class="rvalue" id="resRecorder">—</div>
          </div>
          <div class="calc-result-box">
            <div class="rlabel">Hard Disk Storage Needed</div>
            <div class="rvalue" id="resHdd">—</div>
          </div>
          <div class="calc-result-box" id="poeResultBox">
            <div class="rlabel">PoE Switch Wattage Needed</div>
            <div class="rvalue" id="resPoe">—</div>
          </div>
          <div class="calc-result-box">
            <div class="rlabel">Total Cable Length</div>
            <div class="rvalue" id="resCable">—</div>
          </div>
          <div id="calcWarnings"></div>
        </div>

        <!-- STEP 2: Package (only shown in package mode) -->
        <div class="calc-card mt-4" id="calcStep2">
          <div class="calc-section-title"><i class="fa-solid fa-boxes-stacked"></i> Your Package</div>
          <div id="packageItems"></div>

          <div class="mt-3">
            <div class="calc-total-row"><span>Subtotal</span><span id="totSubtotal">PKR 0</span></div>
            <div class="calc-total-row discount" id="totDiscountRow"><span>Package Discount (<span id="discPercent">5</span>%)</span><span id="totDiscount">- PKR 0</span></div>
            <div class="calc-total-row grand"><span>Total</span><span id="totGrand">PKR 0</span></div>
          </div>

          <button type="button" class="calc-btn-primary mt-3" id="openLeadModalBtn">
            <i class="fa-solid fa-file-pdf"></i> Get My Estimate PDF
          </button>
        </div>

        <div class="mt-3" id="calcOnlyActions">
          <p class="text-muted small text-center mb-2">Want pricing and a downloadable estimate?</p>
          <button type="button" class="calc-btn-outline" id="switchToPackageBtn">
            <i class="fa-solid fa-boxes-stacked me-1"></i> Build My Package Instead
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- LEAD CAPTURE MODAL -->
<div id="leadModal">
  <div class="modal-box">
    <h4>Get Your Estimate PDF</h4>
    <p>Enter your details — we'll prepare your CCTV package estimate instantly.</p>
    <form id="leadForm">
      <div class="mb-3">
        <label class="calc-label">Full Name *</label>
        <input type="text" id="leadName" class="calc-form-control" required>
      </div>
      <div class="mb-3">
        <label class="calc-label">Phone / WhatsApp *</label>
        <input type="tel" id="leadPhone" class="calc-form-control" required placeholder="03XXXXXXXXX">
      </div>
      <div class="mb-3">
        <label class="calc-label">Email <small class="text-muted">(optional)</small></label>
        <input type="email" id="leadEmail" class="calc-form-control">
      </div>
      <div class="d-flex gap-2">
        <button type="button" class="calc-btn-outline" id="closeLeadModalBtn" style="width:auto;flex:1">Cancel</button>
        <button type="submit" class="calc-btn-primary" style="flex:2" id="leadSubmitBtn">
          <i class="fa-solid fa-paper-plane me-1"></i> Get Estimate
        </button>
      </div>
    </form>
  </div>
</div>

<script>
const CALC_DATA = <?= json_encode($jsData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
const SITE_URL  = "<?= SITE_URL ?>";
const ASSETS_URL = "<?= ASSETS_URL ?>";
</script>
<script src="<?= ASSETS_URL ?>/js/calculator.js"></script>

<?php include __DIR__ . '/includes/footer.php'; ?>
