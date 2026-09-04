<?php
/**
 * robots.php — Dynamically generated robots.txt.
 * Served at /robots.txt via the .htaccess rewrite rule.
 * Always points to the correct sitemap URL for whatever domain
 * is configured in SITE_URL — no manual editing needed after
 * moving from localhost to a live domain.
 */
require_once __DIR__ . '/config/config.php';

header('Content-Type: text/plain; charset=utf-8');
?>
User-agent: *
Allow: /
Disallow: /admin/
Disallow: /config/
Disallow: /includes/
Disallow: /database/
Disallow: /patch/
Disallow: /.env

Sitemap: <?= SITE_URL ?>/sitemap.xml
