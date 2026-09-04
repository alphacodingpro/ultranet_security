<?php
/**
 * sitemap.php — Dynamically generated XML sitemap.
 * Served at /sitemap.xml via the .htaccess rewrite rule below:
 *     RewriteRule ^sitemap\.xml$ sitemap.php [L]
 *
 * Automatically includes every active product and category, with
 * lastmod dates pulled from the database — no manual editing needed
 * when products are added, edited, or removed.
 */
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';

header('Content-Type: application/xml; charset=utf-8');

$db = getDB();

function xmlDate($value): string
{
    return $value ? date('Y-m-d', strtotime($value)) : date('Y-m-d');
}

$categories = getAllCategories();
$products   = $db->query(
    "SELECT slug, image, name, updated_at, created_at FROM products WHERE status='active' ORDER BY updated_at DESC"
)->fetchAll();

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">

  <!-- Static pages -->
  <url>
    <loc><?= SITE_URL ?>/</loc>
    <lastmod><?= date('Y-m-d') ?></lastmod>
    <changefreq>weekly</changefreq>
    <priority>1.0</priority>
    <image:image>
      <image:loc><?= ASSETS_URL ?>/img/hero-bg.jpg</image:loc>
      <image:title>CCTV Installation Karachi - UltraNet Security</image:title>
    </image:image>
  </url>
  <url>
    <loc><?= SITE_URL ?>/products.php</loc>
    <lastmod><?= date('Y-m-d') ?></lastmod>
    <changefreq>daily</changefreq>
    <priority>0.9</priority>
  </url>
  <url>
    <loc><?= SITE_URL ?>/calculator.php</loc>
    <lastmod><?= date('Y-m-d') ?></lastmod>
    <changefreq>monthly</changefreq>
    <priority>0.8</priority>
  </url>
  <url>
    <loc><?= SITE_URL ?>/privacy-policy.php</loc>
    <lastmod><?= date('Y-m-d') ?></lastmod>
    <changefreq>yearly</changefreq>
    <priority>0.3</priority>
  </url>

  <!-- Category pages -->
  <?php foreach ($categories as $cat): ?>
  <url>
    <loc><?= SITE_URL ?>/products.php?category=<?= h($cat['slug']) ?></loc>
    <lastmod><?= xmlDate($cat['updated_at'] ?? null) ?></lastmod>
    <changefreq>weekly</changefreq>
    <priority>0.8</priority>
  </url>
  <?php endforeach; ?>

  <!-- Product pages (every active product, auto-included) -->
  <?php foreach ($products as $p): ?>
  <url>
    <loc><?= SITE_URL ?>/product/<?= h($p['slug']) ?></loc>
    <lastmod><?= xmlDate($p['updated_at'] ?: $p['created_at']) ?></lastmod>
    <changefreq>weekly</changefreq>
    <priority>0.7</priority>
    <?php if ($p['image']): ?>
    <image:image>
      <image:loc><?= h(productImageUrl($p['image'])) ?></image:loc>
      <image:title><?= h($p['name']) ?></image:title>
    </image:image>
    <?php endif; ?>
  </url>
  <?php endforeach; ?>

</urlset>
