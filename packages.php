<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';

$packages = getPublicPackages();
$pageTitle = 'CCTV Camera Packages in Karachi | UltraNet Security';
$metaDesc = 'Compare CCTV camera packages for homes, shops and offices in Karachi. See cameras, recorder, storage and installation details, then request a WhatsApp quote.';
$metaKeywords = 'CCTV camera packages Karachi, CCTV package price Karachi, camera installation package, Hikvision package Karachi, Dahua package Karachi';
$canonicalSlug = '/packages/';
$bodyClass = 'packages-page';
$listItems = [];
foreach ($packages as $index => $package) {
    $item = [
        '@type' => 'Service',
        'name' => $package['name'],
        'description' => $package['short_description'] ?: ('CCTV installation package for Karachi with ' . ((int)$package['camera_count'] ?: 'custom') . ' cameras.'),
        'areaServed' => ['@type' => 'City', 'name' => 'Karachi'],
        'provider' => ['@id' => SITE_URL . '/#business'],
    ];
    if ($package['price'] !== null) {
        $item['offers'] = ['@type' => 'Offer', 'priceCurrency' => 'PKR', 'price' => (float)$package['price'], 'availability' => 'https://schema.org/InStock'];
    }
    $listItems[] = ['@type' => 'ListItem', 'position' => $index + 1, 'item' => $item];
}
$extraSchema = [[
    '@context' => 'https://schema.org',
    '@type' => 'ItemList',
    'name' => 'CCTV Camera Packages in Karachi',
    'itemListElement' => $listItems,
]];
include __DIR__ . '/includes/header.php';
?>

<section class="package-hero">
  <div class="container">
    <div class="row align-items-center g-4">
      <div class="col-lg-8">
        <span class="package-eyebrow"><i class="fa-solid fa-location-dot"></i> CCTV packages for Karachi</span>
        <h1>Complete CCTV Packages<br><span>For Home & Business</span></h1>
        <p>Compare cameras, recorders, surveillance storage, mobile viewing and installation support in one place.</p>
        <div class="d-flex flex-wrap gap-3">
          <a class="btn-package-primary" href="https://wa.me/923091243189?text=<?= rawurlencode('Hello UltraNet Security, I would like a quote for a CCTV package in Karachi.') ?>" target="_blank" rel="noopener"><i class="fa-brands fa-whatsapp"></i> WhatsApp Quote</a>
          <a class="btn-package-outline" href="tel:+923091243189"><i class="fa-solid fa-phone"></i> 0309-1243189</a>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="package-trust-card">
          <strong>Every package includes</strong>
          <span><i class="fa-solid fa-circle-check"></i> Requirement assessment</span>
          <span><i class="fa-solid fa-circle-check"></i> Professional installation option</span>
          <span><i class="fa-solid fa-circle-check"></i> Mobile viewing configuration</span>
          <span><i class="fa-solid fa-circle-check"></i> Karachi after-sales support</span>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="package-list-section">
  <div class="container">
    <div class="text-center package-section-title">
      <span class="package-section-kicker">Compare your options</span>
      <h2>Choose the Right CCTV Package</h2>
      <p>Your final quotation is confirmed according to the camera model, cable length, recording storage and site conditions.</p>
    </div>

    <?php if ($packages): ?>
    <div class="row g-4 justify-content-center">
      <?php foreach ($packages as $package):
        $message = 'Hello UltraNet Security, I would like a quote for the "' . $package['name'] . '" package. Location: Karachi.';
      ?>
      <div class="col-md-6 col-xl-4">
        <article class="package-card <?= !empty($package['featured']) ? 'featured' : '' ?>">
          <?php if (!empty($package['badge'])): ?><div class="package-badge"><?= h($package['badge']) ?></div><?php endif; ?>
          <div class="package-card-head">
            <span class="package-type"><?= h(strtoupper($package['system_type'])) ?> CCTV</span>
            <h2><?= h($package['name']) ?></h2>
            <?php if ($package['short_description']): ?><p><?= h($package['short_description']) ?></p><?php endif; ?>
          </div>
          <div class="package-specs">
            <?php if ($package['camera_count']): ?><div><i class="fa-solid fa-video"></i><span><small>Cameras</small><strong><?= (int)$package['camera_count'] ?> Cameras</strong></span></div><?php endif; ?>
            <?php if ($package['resolution']): ?><div><i class="fa-solid fa-expand"></i><span><small>Resolution</small><strong><?= h($package['resolution']) ?></strong></span></div><?php endif; ?>
            <?php if ($package['recorder']): ?><div><i class="fa-solid fa-hard-drive"></i><span><small>Recorder</small><strong><?= h($package['recorder']) ?></strong></span></div><?php endif; ?>
            <?php if ($package['storage']): ?><div><i class="fa-solid fa-database"></i><span><small>Storage</small><strong><?= h($package['storage']) ?></strong></span></div><?php endif; ?>
          </div>
          <?php $features = packageFeatureLines($package['features']); if ($features): ?>
          <ul class="package-features">
            <?php foreach ($features as $feature): ?><li><i class="fa-solid fa-check"></i><?= h($feature) ?></li><?php endforeach; ?>
          </ul>
          <?php endif; ?>
          <?php if ($package['warranty']): ?><div class="package-warranty"><i class="fa-solid fa-shield-halved"></i><?= h($package['warranty']) ?></div><?php endif; ?>
          <div class="package-price">
            <?php if ($package['price'] !== null): ?>
              <small><?= h($package['price_note'] ?: 'Starting from') ?></small>
              <div><?php if ($package['old_price'] !== null): ?><del><?= formatPrice((float)$package['old_price']) ?></del><?php endif; ?><strong><?= formatPrice((float)$package['price']) ?></strong></div>
            <?php else: ?>
              <small>Price</small><strong>Request latest quote</strong>
            <?php endif; ?>
          </div>
          <a class="package-quote-btn" href="https://wa.me/923091243189?text=<?= rawurlencode($message) ?>" target="_blank" rel="noopener"><i class="fa-brands fa-whatsapp"></i> Get This Package</a>
        </article>
      </div>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="package-empty">
      <i class="fa-solid fa-sliders"></i>
      <h2>Need a different camera setup?</h2>
      <p>Share the number of cameras, required recording days and property size. We will recommend a suitable custom package and provide the current Karachi price.</p>
      <a class="btn-package-primary" href="https://wa.me/923091243189?text=<?= rawurlencode('Hello UltraNet Security, I would like a quote for a custom CCTV package.') ?>" target="_blank" rel="noopener"><i class="fa-brands fa-whatsapp"></i> Ask on WhatsApp</a>
    </div>
    <?php endif; ?>
  </div>
</section>

<section class="package-notes">
  <div class="container">
    <div class="row g-4">
      <div class="col-lg-7"><span class="package-section-kicker">Transparent quotation</span><h2>What determines the final price?</h2><p>The cable route, camera model, night-vision range, hard-drive capacity, installation height and any civil work affect the final quotation. We confirm an exact written quote after reviewing the site details.</p></div>
      <div class="col-lg-5"><div class="package-note-box"><i class="fa-solid fa-calculator"></i><div><strong>Need a custom calculation?</strong><p>Select your camera count and recording requirements.</p><a href="<?= SITE_URL ?>/calculator.php">Open CCTV Calculator <i class="fa-solid fa-arrow-right"></i></a></div></div></div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
