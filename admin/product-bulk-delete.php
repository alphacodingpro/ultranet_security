<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once __DIR__ . '/includes/delete-selected-products.php';
require_once dirname(__DIR__) . '/includes/admin-product-filters.php';

requireAdminLogin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . ADMIN_URL . '/products.php');
    exit;
}
verifyCsrf();

$query = $_POST['return_query'] ?? '';
$query = is_string($query) && strlen($query) <= 4096 ? $query : '';
parse_str($query, $return);
$filters = adminProductFilterValues($return);
$filters = array_filter($filters, static fn($value) => $value !== '' && $value !== [] && $value !== 0);
$page = $return['page'] ?? '1';
if (is_scalar($page) && preg_match('/\A[1-9][0-9]{0,5}\z/D', (string)$page)) {
    $filters['page'] = (int)$page;
}
$redirect = ADMIN_URL . '/products.php' . ($filters ? '?' . http_build_query($filters) : '');

$rawIds = $_POST['ids'] ?? null;
if (!is_array($rawIds) || count($rawIds) < 1 || count($rawIds) > 25) {
    setFlash('error', 'Select between 1 and 25 products on this page.');
    header('Location: ' . $redirect);
    exit;
}
$ids = [];
foreach ($rawIds as $rawId) {
    if (!is_scalar($rawId) || !preg_match('/\A[1-9][0-9]{0,9}\z/D', (string)$rawId)) {
        setFlash('error', 'Invalid product selection.');
        header('Location: ' . $redirect);
        exit;
    }
    $ids[] = (int)$rawId;
}

try {
    $result = deleteSelectedProducts(getDB(), $ids, UPLOAD_DIR);
    $message = $result['deleted'] . ' product(s) deleted.';
    if ($result['image_errors']) {
        $message .= ' Some uploaded images could not be removed; please check Manage Images.';
    }
    setFlash($result['image_errors'] ? 'error' : 'success', $message);
} catch (Throwable $exception) {
    error_log('Bulk product delete failed: ' . $exception->getMessage());
    setFlash('error', 'Products could not be deleted. Please try again.');
}
header('Location: ' . $redirect);
exit;
