<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';

requireAdminLogin();

// Must be a POST request with a valid CSRF token — GET-based deletes were
// vulnerable to CSRF (a malicious page could silently trigger a delete
// for a logged-in admin just by loading an <img> tag pointing at the old URL).
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . ADMIN_URL . '/products.php');
    exit;
}
verifyCsrf();

$id = (int)($_POST['id'] ?? 0);
if (!$id) { header('Location: ' . ADMIN_URL . '/products.php'); exit; }

$product = getProductById($id);
if (!$product) { setFlash('error', 'Product not found.'); header('Location: ' . ADMIN_URL . '/products.php'); exit; }

// Delete image file from uploads
if ($product['image'] && file_exists(UPLOAD_DIR . $product['image'])) {
    @unlink(UPLOAD_DIR . $product['image']);
}
deleteAllProductImages($id);

$db = getDB();
$st = $db->prepare('DELETE FROM products WHERE id = ?');
$st->execute([$id]);

setFlash('success', 'Product "' . $product['name'] . '" deleted successfully.');
header('Location: ' . ADMIN_URL . '/products.php');
exit;
