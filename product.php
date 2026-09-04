<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';

$slug = trim($_GET['slug'] ?? '');
if (!$slug) { header('Location: ' . SITE_URL . '/products.php'); exit; }

$product = getProductBySlug($slug);
if (!$product) { header('HTTP/1.0 404 Not Found'); include __DIR__ . '/includes/404.php'; exit; }

$related       = getRelatedProducts((int)$product['category_id'], (int)$product['id'], 4);
$extraImages   = getProductImages((int)$product['id']);
$mainImageUrl  = productImageUrl($product['image']);

// Build full gallery: main image first, then extras
$gallery = [];
if ($product['image']) $gallery[] = productImageUrl($product['image']);
foreach ($extraImages as $img) $gallery[] = productImageUrl($img['image']);
if (empty($gallery)) $gallery[] = ASSETS_URL . '/img/no-image.jpg';

$hasVideo   = trim((string)($product['video_url'] ?? '')) !== '';
$videoEmbed = $hasVideo ? getYouTubeEmbedUrl(trim($product['video_url'])) : null;

$pageTitle    = $product['meta_title']
    ? seoTitle($product['meta_title'], 'UltraNet Security', 60)
    : seoTitle($product['name'], 'UltraNet Security Karachi', 60);
$metaDesc     = seoDescription(
    $product['meta_description'] ?: ($product['short_desc'] ?: ('Buy ' . $product['name'] . ' in Karachi at best price. UltraNet Security authorized dealer, Hikvision & Dahua.')),
    155
);
$metaKeywords = implode(', ', array_filter([$product['brand'], $product['name'], 'Karachi', 'CCTV', 'security camera']));
$canonicalSlug = '/product/' . $product['slug'];
$ogType       = 'product';
$ogImage      = productImageUrl($product['image']);

$breadcrumbItems = [['name' => 'Home', 'url' => SITE_URL . '/']];
$breadcrumbItems[] = ['name' => 'Products', 'url' => SITE_URL . '/products.php'];
if ($product['category_slug']) {
    $breadcrumbItems[] = ['name' => $product['category_name'], 'url' => SITE_URL . '/products.php?category=' . $product['category_slug']];
}
$breadcrumbItems[] = ['name' => $product['name'], 'url' => SITE_URL . '/product/' . $product['slug']];

$extraSchema = [
    schemaProduct($product),
    schemaBreadcrumb($breadcrumbItems),
];

include __DIR__ . '/includes/header.php';
?>

<!-- Swiper CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">

<style>
  /* Scoped styles for product gallery + video tabs — matches existing site theme */
  .pg-wrap{border-radius:18px;overflow:hidden;border:1px solid var(--border);background:#fff}
  .pg-tabs{display:flex;gap:8px;padding:12px;border-bottom:1px solid var(--border)}
  .pg-tab-btn{background:var(--light-bg);border:1px solid var(--border);color:var(--muted);font-size:13px;font-weight:600;padding:8px 18px;border-radius:8px;cursor:pointer;transition:all .2s;display:flex;align-items:center;gap:6px}
  .pg-tab-btn.active{background:var(--accent);color:#fff;border-color:var(--accent)}
  .pg-tab-btn:hover:not(.active){border-color:var(--accent);color:var(--accent)}
  .pg-panel{display:none}
  .pg-panel.active{display:block}

  .pg-main-swiper{width:100%;aspect-ratio:4/3;background:var(--light-bg)}
  .pg-main-swiper img{width:100%;height:100%;object-fit:cover;display:block}
  .pg-main-swiper .swiper-button-next,.pg-main-swiper .swiper-button-prev{
    background:rgba(10,22,40,.55);width:38px;height:38px;border-radius:50%;color:#fff;transition:background .2s}
  .pg-main-swiper .swiper-button-next:hover,.pg-main-swiper .swiper-button-prev:hover{background:var(--accent)}
  .pg-main-swiper .swiper-button-next::after,.pg-main-swiper .swiper-button-prev::after{font-size:15px;font-weight:700}
  .pg-main-swiper .swiper-pagination-bullet{background:#fff;opacity:.6}
  .pg-main-swiper .swiper-pagination-bullet-active{opacity:1;background:var(--accent)}

  .pg-thumbs{padding:12px;display:flex;gap:8px;overflow-x:auto}
  .pg-thumb{width:66px;height:56px;flex-shrink:0;border-radius:8px;overflow:hidden;border:2px solid var(--border);cursor:pointer;opacity:.65;transition:all .2s}
  .pg-thumb img{width:100%;height:100%;object-fit:cover;display:block}
  .pg-thumb.active,.pg-thumb:hover{border-color:var(--accent);opacity:1}

  .pg-video-frame{width:100%;aspect-ratio:16/9;border:none;display:block;background:#000}

  .prod-detail-badge-sale{position:absolute;top:16px;left:16px;background:var(--accent);color:#fff;font-family:'Bebas Neue',sans-serif;font-size:20px;padding:5px 14px;border-radius:8px;z-index:5}
</style>

<!-- BREADCRUMB -->
<section class="page-hero">
  <div class="container">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-2">
        <li class="breadcrumb-item"><a href="<?= SITE_URL ?>/">Home</a></li>
        <li class="breadcrumb-item"><a href="<?= SITE_URL ?>/products.php">Products</a></li>
        <?php if ($product['category_slug']): ?>
        <li class="breadcrumb-item">
          <a href="<?= SITE_URL ?>/products.php?category=<?= h($product['category_slug']) ?>"><?= h($product['category_name']) ?></a>
        </li>
        <?php endif; ?>
        <li class="breadcrumb-item active" aria-current="page"><?= h($product['name']) ?></li>
      </ol>
    </nav>
  </div>
</section>

<div class="container py-5">
  <div class="row g-5">

    <!-- LEFT: Image Slider + Video Tab -->
    <div class="col-lg-5">
      <div class="pg-wrap">

        <?php if ($hasVideo): ?>
        <div class="pg-tabs">
          <button type="button" class="pg-tab-btn active" data-tab="images">
            <i class="fa-solid fa-images"></i> Photos
          </button>
          <button type="button" class="pg-tab-btn" data-tab="video">
            <i class="fa-brands fa-youtube"></i> Video
          </button>
        </div>
        <?php endif; ?>

        <!-- IMAGES PANEL -->
        <div class="pg-panel active" id="panel-images" style="position:relative">
          <?php if (!empty($product['old_price']) && $product['old_price'] > $product['price']): ?>
          <div class="prod-detail-badge-sale"><?= round((1 - $product['price']/$product['old_price'])*100) ?>% OFF</div>
          <?php endif; ?>

          <div class="swiper pg-main-swiper">
            <div class="swiper-wrapper">
              <?php foreach ($gallery as $imgUrl): ?>
              <div class="swiper-slide">
                <img src="<?= h($imgUrl) ?>" alt="<?= h($product['name']) ?> Karachi"
                     onerror="this.src='<?= ASSETS_URL ?>/img/no-image.jpg'">
              </div>
              <?php endforeach; ?>
            </div>
            <?php if (count($gallery) > 1): ?>
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
            <div class="swiper-pagination"></div>
            <?php endif; ?>
          </div>

          <?php if (count($gallery) > 1): ?>
          <div class="pg-thumbs">
            <?php foreach ($gallery as $i => $imgUrl): ?>
            <div class="pg-thumb <?= $i===0?'active':'' ?>" data-index="<?= $i ?>">
              <img src="<?= h($imgUrl) ?>" alt="thumbnail <?= $i+1 ?>" onerror="this.src='<?= ASSETS_URL ?>/img/no-image.jpg'">
            </div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
        </div>

        <!-- VIDEO PANEL -->
        <?php if ($hasVideo): ?>
        <div class="pg-panel" id="panel-video">
          <?php if ($videoEmbed): ?>
          <iframe class="pg-video-frame" src="<?= h($videoEmbed) ?>" allowfullscreen
                  allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe>
          <?php else: ?>
          <div style="padding:40px;text-align:center;color:var(--muted)">
            <i class="fa-solid fa-triangle-exclamation fa-2x mb-2"></i>
            <p>This video link could not be loaded. Please check the URL in Admin.</p>
          </div>
          <?php endif; ?>
        </div>
        <?php endif; ?>

      </div>
    </div>

    <!-- RIGHT: Info -->
    <div class="col-lg-7">
      <?php if (!empty($product['brand'])): ?>
      <div class="prod-detail-brand">
        <a href="<?= SITE_URL ?>/search.php?q=<?= urlencode($product['brand']) ?>"><?= h($product['brand']) ?></a>
      </div>
      <?php endif; ?>

      <h1 class="prod-detail-title"><?= h($product['name']) ?></h1>

      <?php if (!empty($product['sku'])): ?>
      <p class="prod-detail-sku text-muted small">SKU: <?= h($product['sku']) ?></p>
      <?php endif; ?>

      <div class="prod-detail-price">
        <?= formatPrice($product['price']) ?>
        <?php if (!empty($product['old_price']) && $product['old_price'] > $product['price']): ?>
        <span class="prod-detail-old"><?= formatPrice($product['old_price']) ?></span>
        <?php endif; ?>
      </div>

      <div class="mb-3">
        <?php if ($product['stock_status'] === 'in_stock'): ?>
        <span class="stock-badge in"><i class="fa-solid fa-circle-check me-1"></i>In Stock</span>
        <?php elseif ($product['stock_status'] === 'on_order'): ?>
        <span class="stock-badge order"><i class="fa-solid fa-clock me-1"></i>Available on Order</span>
        <?php else: ?>
        <span class="stock-badge out"><i class="fa-solid fa-circle-xmark me-1"></i>Out of Stock</span>
        <?php endif; ?>
      </div>

      <?php if (!empty($product['short_desc'])): ?>
      <p class="prod-detail-short"><?= h($product['short_desc']) ?></p>
      <?php endif; ?>

      <div class="d-flex gap-3 flex-wrap my-4">
        <a href="https://wa.me/923091243189?text=<?= urlencode('Hi, I want to order: '.$product['name'].' - '.formatPrice($product['price'])) ?>"
           class="btn-red flex-grow-1" style="justify-content:center" target="_blank">
          <i class="fa-brands fa-whatsapp"></i> Order on WhatsApp
        </a>
        <a href="tel:+923091243189" class="btn-ghost flex-grow-1" style="justify-content:center;color:var(--primary);border-color:var(--border)">
          <i class="fa-solid fa-phone"></i> Call to Order
        </a>
      </div>

      <div class="prod-meta-grid">
        <?php if (!empty($product['brand'])): ?>
        <div><span>Brand</span><strong><?= h($product['brand']) ?></strong></div>
        <?php endif; ?>
        <?php if (!empty($product['sku'])): ?>
        <div><span>SKU</span><strong><?= h($product['sku']) ?></strong></div>
        <?php endif; ?>
        <?php if (!empty($product['category_name'])): ?>
        <div><span>Category</span><strong><?= h($product['category_name']) ?></strong></div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Full Description -->
  <?php if (!empty($product['description'])): ?>
  <div class="prod-description mt-5">
    <h2 class="section-title" style="font-size:28px">PRODUCT <span style="color:var(--accent)">DESCRIPTION</span></h2>
    <div class="prod-desc-content"><?= nl2br(h($product['description'])) ?></div>
  </div>
  <?php endif; ?>

  <!-- Related Products -->
  <?php if ($related): ?>
  <div class="mt-5">
    <h2 class="section-title" style="font-size:28px">RELATED <span style="color:var(--accent)">PRODUCTS</span></h2>
    <div class="row g-3 g-md-4 mt-1">
      <?php foreach ($related as $p): ?>
      <div class="col-6 col-md-3">
        <?php include __DIR__ . '/includes/product-card.php'; ?>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>
</div>

<!-- Swiper JS -->
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  // Init main image swiper
  const mainSwiper = new Swiper('.pg-main-swiper', {
    loop: <?= count($gallery) > 1 ? 'true' : 'false' ?>,
    navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' },
    pagination: { el: '.swiper-pagination', clickable: true },
  });

  // Thumbnail click -> slide to that image
  document.querySelectorAll('.pg-thumb').forEach(thumb => {
    thumb.addEventListener('click', function () {
      const idx = parseInt(this.getAttribute('data-index'));
      mainSwiper.slideToLoop ? mainSwiper.slideToLoop(idx) : mainSwiper.slideTo(idx);
      document.querySelectorAll('.pg-thumb').forEach(t => t.classList.remove('active'));
      this.classList.add('active');
    });
  });
  mainSwiper.on('slideChange', function () {
    const real = mainSwiper.realIndex;
    document.querySelectorAll('.pg-thumb').forEach(t => t.classList.remove('active'));
    const active = document.querySelector('.pg-thumb[data-index="' + real + '"]');
    if (active) active.classList.add('active');
  });

  // Tab switching (Photos / Video)
  const tabBtns = document.querySelectorAll('.pg-tab-btn');
  tabBtns.forEach(btn => {
    btn.addEventListener('click', function () {
      const tab = this.getAttribute('data-tab');
      tabBtns.forEach(b => b.classList.remove('active'));
      this.classList.add('active');
      document.querySelectorAll('.pg-panel').forEach(p => p.classList.remove('active'));
      document.getElementById('panel-' + tab).classList.add('active');
      // Update swiper size in case it was hidden
      if (tab === 'images') mainSwiper.update();
    });
  });
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
