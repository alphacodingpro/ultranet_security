<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';
$categories = getAllCategories();
$byId = array_column($categories, null, 'id');
$products = getDB()->query("SELECT slug, image, name, category_id, updated_at, created_at FROM products WHERE status='active' ORDER BY id")->fetchAll();
$populated = [];
foreach ($products as $p) {
    $id = (int)$p['category_id'];
    $populated[$id] = true;
    if (!empty($byId[$id]['parent_id'])) $populated[(int)$byId[$id]['parent_id']] = true;
}
header('Content-Type: application/xml; charset=utf-8');
function sitemapXml(string $value): string { return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8'); }
function sitemapEntry(string $path, ?string $lastmod = null, ?string $image = null): void {
    echo '<url><loc>'.sitemapXml(SITE_URL.$path).'</loc>';
    if ($lastmod) echo '<lastmod>'.$lastmod.'</lastmod>';
    if ($image) echo '<image:image><image:loc>'.sitemapXml($image).'</image:loc></image:image>';
    echo "</url>\n";
}
echo '<?xml version="1.0" encoding="UTF-8"?>'."\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">'."\n";
// Do not invent daily modification dates for unchanged static pages.
foreach (['/', '/products.php', '/calculator.php', '/privacy-policy.php', '/cctv-camera-installation-dha-karachi', '/home-cctv-installation-karachi'] as $path) sitemapEntry($path);
foreach ($categories as $category) {
    if (!isset($populated[(int)$category['id']])) continue;
    $parent = $byId[(int)($category['parent_id'] ?? 0)] ?? null;
    // Listing content also changes when products are edited; omit incomplete category dates.
    sitemapEntry(categoryCatalogPath($category, $parent));
}
foreach ($products as $p) {
    sitemapEntry('/product/'.$p['slug'], sitemapDate($p['updated_at'] ?: $p['created_at']), $p['image'] ? productImageUrl($p['image']) : null);
}
echo '</urlset>';
