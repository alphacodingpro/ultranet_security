<?php
// includes/product-card.php
// Expects: $p = product array from DB
?>
<div class="prod-card h-100">
  <a href="<?= SITE_URL ?>/product/<?= h($p['slug']) ?>" class="prod-img-link">
    <img src="<?= h(productImageUrl($p['image'])) ?>"
         alt="<?= h($p['name']) ?> - <?= h($p['brand'] ?? '') ?> Karachi"
         width="400" height="300" loading="lazy"
         onerror="this.src='<?= ASSETS_URL ?>/img/no-image.jpg'">
    <?php if (!empty($p['old_price']) && $p['old_price'] > $p['price']): ?>
    <span class="prod-badge-sale">SALE</span>
    <?php endif; ?>
    <?php if ($p['stock_status'] === 'out_of_stock'): ?>
    <span class="prod-badge-out">Out of Stock</span>
    <?php endif; ?>
  </a>
  <div class="prod-body">
    <?php if (!empty($p['brand'])): ?>
    <div class="prod-brand">
      <a href="<?= SITE_URL ?>/search.php?q=<?= urlencode($p['brand']) ?>"><?= h($p['brand']) ?></a>
    </div>
    <?php endif; ?>
    <h3 class="prod-title">
      <a href="<?= SITE_URL ?>/product/<?= h($p['slug']) ?>"><?= h($p['name']) ?></a>
    </h3>
    <?php if (!empty($p['short_desc'])): ?>
    <p class="prod-desc"><?= h($p['short_desc']) ?></p>
    <?php endif; ?>
    <div class="prod-footer">
      <div class="prod-price">
        <?= formatPrice($p['price']) ?>
        <?php if (!empty($p['old_price']) && $p['old_price'] > $p['price']): ?>
        <span class="prod-old-price"><?= formatPrice($p['old_price']) ?></span>
        <?php endif; ?>
      </div>
      <a href="https://wa.me/923091243189?text=<?= urlencode('Hi, I am interested in: '.$p['name'].' ('.formatPrice($p['price']).')') ?>"
         class="prod-wa-btn" target="_blank" aria-label="Enquire on WhatsApp">
        <i class="fa-brands fa-whatsapp"></i>
      </a>
    </div>
  </div>
</div>
