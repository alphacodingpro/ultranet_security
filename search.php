<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';

$search   = trim($_GET['q'] ?? '');
$products = $search ? getProducts(['search' => $search]) : [];

$pageTitle    = $search ? seoTitle('Search: ' . $search, 'UltraNet Security', 60) : 'Search Products | UltraNet Security';
$metaDesc     = $search
    ? seoDescription('Search results for "' . $search . '" – CCTV cameras, NVR, DVR and accessories in Karachi.', 155)
    : 'Search UltraNet Security\'s catalogue of CCTV cameras, NVR, DVR and accessories available in Karachi.';
$canonicalSlug = '/search.php' . ($search ? '?q=' . urlencode($search) : '');
// Search results are dynamic/thin content — keep them out of the index,
// but still follow links through to the real product pages they list.
$metaRobots = 'noindex, follow';

include __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
  <div class="container">
    <h1 class="page-hero-title">
      <?php if ($search): ?>
        Search Results for "<em style="color:var(--accent)"><?= h($search) ?></em>"
      <?php else: ?>
        Search Products
      <?php endif; ?>
    </h1>
    <?php if ($search): ?>
    <p class="page-hero-sub"><?= count($products) ?> result<?= count($products) != 1 ? 's' : '' ?> found</p>
    <?php endif; ?>
  </div>
</section>

<div class="container py-5">
  <!-- Big search bar -->
  <form action="<?= SITE_URL ?>/search.php" method="GET" class="search-hero-form mb-5">
    <div class="input-group input-group-lg">
      <input type="text" name="q" class="form-control" placeholder="Search cameras, NVR, DVR, Hikvision, Dahua…" value="<?= h($search) ?>">
      <button class="btn" style="background:var(--accent);color:#fff;padding:0 28px" type="submit">
        <i class="fa-solid fa-magnifying-glass me-2"></i>Search
      </button>
    </div>
  </form>

  <?php if (!$search): ?>
  <!-- Show brands to search -->
  <div class="text-center py-3">
    <p class="text-muted mb-3">Quick search by brand:</p>
    <div class="d-flex gap-2 flex-wrap justify-content-center">
      <?php foreach (['Hikvision','Dahua','CP Plus','Uniview','Axis'] as $b): ?>
      <a href="<?= SITE_URL ?>/search.php?q=<?= urlencode($b) ?>" class="brand-box text-decoration-none" style="font-size:15px"><?= h($b) ?></a>
      <?php endforeach; ?>
    </div>
  </div>

  <?php elseif ($products): ?>
  <div class="row g-3 g-md-4">
    <?php foreach ($products as $p): ?>
    <div class="col-6 col-md-4 col-lg-3">
      <?php include __DIR__ . '/includes/product-card.php'; ?>
    </div>
    <?php endforeach; ?>
  </div>

  <?php else: ?>
  <div class="text-center py-5">
    <i class="fa-solid fa-magnifying-glass fa-3x mb-3" style="color:var(--border)"></i>
    <h4>No results for "<?= h($search) ?>"</h4>
    <p class="text-muted">Try different keywords or browse by category.</p>
    <a href="<?= SITE_URL ?>/products.php" class="btn-red mt-3 d-inline-flex">Browse All Products</a>
  </div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
