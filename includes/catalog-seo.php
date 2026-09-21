<?php
// Shared canonical URLs: page links, sitemap and breadcrumbs must agree.
function queryText(string $key): string
{
    return isset($_GET[$key]) && is_scalar($_GET[$key]) ? trim((string)$_GET[$key]) : '';
}
function catalogPath(array $params = [], int $page = 1): string
{
    $query = [];
    foreach (['category', 'brand', 'series', 'direct', 'q'] as $key) {
        if (isset($params[$key]) && is_scalar($params[$key]) && (string)$params[$key] !== '') {
            $query[$key] = (string)$params[$key];
        }
    }
    if ($page > 1) $query['page'] = $page;
    return '/products.php' . ($query ? '?' . http_build_query($query, '', '&', PHP_QUERY_RFC3986) : '');
}
function categoryCatalogPath(array $category, ?array $parent = null): string
{
    if (!empty($category['parent_id']) && $parent) {
        return catalogPath(['category' => $parent['slug'], 'brand' => $category['brand'] ?? '', 'series' => $category['slug']]);
    }
    return catalogPath(['category' => $category['slug']]);
}
function productPriceLabel($price): string
{
    return (float)$price > 0 ? formatPrice((float)$price) : 'Price on request';
}
function sitemapDate($value): ?string
{
    if (!$value || ($time = strtotime((string)$value)) === false || $time > time()) return null;
    return gmdate('Y-m-d', $time);
}
