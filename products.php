<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';

$categorySlug = trim($_GET['category'] ?? '');
$brand        = trim($_GET['brand'] ?? '');
$search       = trim($_GET['q'] ?? '');

$categoryObj  = $categorySlug ? getCategoryBySlug($categorySlug) : null;
$brands       = getAllBrands();
$categories   = getAllCategories();

// A category slug was given in the URL but doesn't exist — return a real
// 404 instead of silently showing "all products" with a 200 status
// (a soft-404 that search engines penalize and that wastes crawl budget).
if ($categorySlug && !$categoryObj) {
    header('HTTP/1.0 404 Not Found');
    include __DIR__ . '/includes/404.php';
    exit;
}

// Build filter opts
$opts = [];
if ($categoryObj)  $opts['category_id'] = (int)$categoryObj['id'];
if ($brand)        $opts['brand']       = $brand;
if ($search)       $opts['search']      = $search;

$products = getProducts($opts);

// Page title / robots — only the clean "all products" and clean category
// pages are indexable. Search-query and brand-filter variants are noindexed
// (they're thin/duplicate-content facets, not unique pages worth ranking).
$metaRobots  = 'index, follow';
$extraSchema = [];

if ($search) {
    $pageTitle    = seoTitle('Search: ' . $search, 'UltraNet Security', 60);
    $metaDesc     = seoDescription('Search results for "' . $search . '" on UltraNet Security Karachi — CCTV cameras, NVR, DVR and accessories.', 155);
    $canonicalSlug = '/products.php?q=' . urlencode($search);
    $metaRobots   = 'noindex, follow';
} elseif ($brand) {
    $pageTitle    = seoTitle($brand . ' Products', 'UltraNet Security', 60);
    $metaDesc     = seoDescription('Browse ' . $brand . ' CCTV products available from UltraNet Security in Karachi.', 155);
    $canonicalSlug = '/products.php?brand=' . urlencode($brand) . ($categorySlug ? '&category=' . $categorySlug : '');
    $metaRobots   = 'noindex, follow';
} elseif ($categoryObj) {
    $pageTitle    = seoTitle($categoryObj['name'] . ' Karachi', 'UltraNet Security', 60);
    $metaDesc     = seoDescription('Buy ' . $categoryObj['name'] . ' in Karachi from UltraNet Security. Hikvision, Dahua & more, genuine products, best prices.', 155);
    $canonicalSlug = '/products.php?category=' . $categorySlug;
    $breadcrumbItems = [
        ['name' => 'Home', 'url' => SITE_URL . '/'],
        ['name' => 'Products', 'url' => SITE_URL . '/products.php'],
        ['name' => $categoryObj['name'], 'url' => SITE_URL . '/products.php?category=' . $categorySlug],
    ];
    $extraSchema[] = schemaBreadcrumb($breadcrumbItems);
    if ($products) $extraSchema[] = schemaItemList($products, $categoryObj['name'] . ' — UltraNet Security');
} else {
    $pageTitle    = 'CCTV Products Karachi | UltraNet Security';
    $metaDesc     = 'Shop genuine Hikvision & Dahua CCTV cameras, NVR, DVR and accessories in Karachi at best prices. UltraNet Security authorized dealer.';
    $canonicalSlug = '/products.php';
    $breadcrumbItems = [
        ['name' => 'Home', 'url' => SITE_URL . '/'],
        ['name' => 'Products', 'url' => SITE_URL . '/products.php'],
    ];
    $extraSchema[] = schemaBreadcrumb($breadcrumbItems);
    if ($products) $extraSchema[] = schemaItemList($products, 'All Products — UltraNet Security');
}

include __DIR__ . '/includes/header.php';
?>

<!-- PAGE HEADER -->
<section class="page-hero">
  <div class="container">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-2">
        <li class="breadcrumb-item"><a href="<?= SITE_URL ?>/">Home</a></li>
        <li class="breadcrumb-item <?= !$categoryObj?'active':'' ?>"><a href="<?= SITE_URL ?>/products.php">Products</a></li>
        <?php if ($categoryObj): ?>
        <li class="breadcrumb-item active" aria-current="page"><?= h($categoryObj['name']) ?></li>
        <?php endif; ?>
      </ol>
    </nav>
    <h1 class="page-hero-title">
      <?php if ($categoryObj): echo h($categoryObj['name']);
      elseif ($search): echo 'Search: <em style="color:var(--accent)">' . h($search) . '</em>';
      else: echo 'All Products'; endif; ?>
    </h1>
    <p class="page-hero-sub"><?= count($products) ?> product<?= count($products) != 1 ? 's' : '' ?> found</p>
  </div>
</section>

<div class="container py-5">
  <div class="row g-4">

    <!-- SIDEBAR FILTERS -->
    <div class="col-lg-3">
      <div class="filter-box">
        <!-- Search -->
        <div class="filter-section">
          <h6 class="filter-heading">Search</h6>
          <form action="<?= SITE_URL ?>/products.php" method="GET">
            <div class="input-group">
              <input type="text" name="q" class="form-control form-control-sm" placeholder="Search products…" value="<?= h($search) ?>">
              <button class="btn btn-sm" style="background:var(--accent);color:#fff" type="submit">
                <i class="fa-solid fa-magnifying-glass"></i>
              </button>
            </div>
          </form>
        </div>

        <!-- Categories -->
        <div class="filter-section">
          <h6 class="filter-heading">Categories</h6>
          <ul class="filter-list">
            <li>
              <a href="<?= SITE_URL ?>/products.php" class="<?= !$categorySlug ? 'active' : '' ?>">
                <i class="fa-solid fa-border-all me-2"></i>All Products
              </a>
            </li>
            <?php foreach ($categories as $cat): ?>
            <li>
              <a href="<?= SITE_URL ?>/products.php?category=<?= h($cat['slug']) ?>"
                 class="<?= $categorySlug === $cat['slug'] ? 'active' : '' ?>">
                <i class="<?= h($cat['icon']) ?> me-2"></i><?= h($cat['name']) ?>
              </a>
            </li>
            <?php endforeach; ?>
          </ul>
        </div>

        <!-- Brands -->
        <?php if ($brands): ?>
        <div class="filter-section">
          <h6 class="filter-heading">Brand</h6>
          <ul class="filter-list">
            <?php foreach ($brands as $b): ?>
            <li>
              <a href="<?= SITE_URL ?>/products.php?brand=<?= urlencode($b) ?><?= $categorySlug ? '&category='.$categorySlug : '' ?>"
                 class="<?= $brand === $b ? 'active' : '' ?>">
                <?= h($b) ?>
              </a>
            </li>
            <?php endforeach; ?>
          </ul>
        </div>
        <?php endif; ?>

        <?php if ($categorySlug || $brand || $search): ?>
        <a href="<?= SITE_URL ?>/products.php" class="btn btn-sm w-100 mt-2" style="border:1px solid var(--border);color:var(--muted)">
          <i class="fa-solid fa-xmark me-1"></i>Clear Filters
        </a>
        <?php endif; ?>
      </div>
    </div>

    <!-- PRODUCT GRID -->
    <div class="col-lg-9">
      <?php if ($products): ?>
      <div class="row g-3 g-md-4" id="productGrid">
        <?php foreach ($products as $p): ?>
        <div class="col-6 col-md-4">
          <?php include __DIR__ . '/includes/product-card.php'; ?>
        </div>
        <?php endforeach; ?>
      </div>
      <?php else: ?>
      <div class="text-center py-5">
        <i class="fa-solid fa-camera-slash fa-3x mb-3" style="color:var(--border)"></i>
        <h4>No products found</h4>
        <p class="text-muted">Try a different search term or category.</p>
        <a href="<?= SITE_URL ?>/products.php" class="btn-red mt-2 d-inline-flex">View All Products</a>
      </div>
      <?php endif; ?>
    </div>

  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
