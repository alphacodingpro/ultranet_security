<?php
if (!defined('SITE_URL')) {
    require_once __DIR__ . '/../config/config.php';
    require_once __DIR__ . '/../includes/functions.php';
}
http_response_code(404);
$pageTitle    = '404 – Page Not Found | UltraNet Security';
$metaDesc     = 'The page you are looking for could not be found on UltraNet Security.';
$canonicalSlug = '/' . ltrim($_SERVER['REQUEST_URI'] ?? '', '/');
// A 404 page should never be indexed — it has no unique content and
// duplicating the canonical of every broken URL would confuse crawlers.
$metaRobots = 'noindex, follow';
include __DIR__ . '/../includes/header.php';
?>
<div class="container text-center py-5 my-5">
  <div style="font-family:'Bebas Neue',sans-serif;font-size:140px;color:#e0e4ef;line-height:1">404</div>
  <h1 style="font-size:32px;margin-bottom:12px">Page Not Found</h1>
  <p class="text-muted mb-4">The page you are looking for does not exist or has been moved.</p>
  <div class="d-flex gap-3 justify-content-center flex-wrap">
    <a href="<?= SITE_URL ?>/" class="btn-red"><i class="fa-solid fa-house me-1"></i>Go Home</a>
    <a href="<?= SITE_URL ?>/products.php" class="btn-ghost" style="color:var(--primary);border-color:var(--border)"><i class="fa-solid fa-camera me-1"></i>Browse Products</a>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
