<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';

requireAdminLogin();

$db = getDB();
$categoryId = (int)($_GET['cat'] ?? 0);
$brand = trim($_GET['brand'] ?? '');
$status = $_GET['status'] ?? '';
$search = trim($_GET['q'] ?? '');

$where = ['1=1'];
$params = [];
if ($categoryId) { $where[] = 'p.category_id = ?'; $params[] = $categoryId; }
if ($brand !== '') { $where[] = 'p.brand = ?'; $params[] = $brand; }
if (in_array($status, ['active', 'inactive'], true)) { $where[] = 'p.status = ?'; $params[] = $status; }
if ($search !== '') {
    $where[] = '(p.name LIKE ? OR p.brand LIKE ? OR p.sku LIKE ?)';
    $like = '%' . $search . '%';
    array_push($params, $like, $like, $like);
}

$query = 'SELECT p.*, c.name AS category_name, c.slug AS category_slug
          FROM products p LEFT JOIN categories c ON p.category_id = c.id
          WHERE ' . implode(' AND ', $where) . ' ORDER BY p.id DESC';
$stmt = $db->prepare($query);
$stmt->execute($params);

$filename = 'ultranet-products-' . date('Y-m-d-His') . '.csv';
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('X-Content-Type-Options: nosniff');

$out = fopen('php://output', 'w');
fwrite($out, "\xEF\xBB\xBF");
fputcsv($out, ['name','category','category_slug','brand','sku','short_desc','description','price','old_price','image_filename','video_url','stock_status','featured','status','meta_title','meta_description','product_type','channels','storage_gb','poe_ports','poe_budget_watts','camera_watts','price_per_meter']);
while ($product = $stmt->fetch()) {
    fputcsv($out, [
        $product['name'], $product['category_name'], $product['category_slug'], $product['brand'], $product['sku'],
        $product['short_desc'], $product['description'], $product['price'], $product['old_price'], $product['image'],
        $product['video_url'], $product['stock_status'], $product['featured'], $product['status'], $product['meta_title'],
        $product['meta_description'], $product['product_type'], $product['channels'], $product['storage_gb'],
        $product['poe_ports'], $product['poe_budget_watts'], $product['camera_watts'], $product['price_per_meter']
    ]);
}
fclose($out);
exit;
