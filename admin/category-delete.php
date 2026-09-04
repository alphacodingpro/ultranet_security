<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';

requireAdminLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . ADMIN_URL . '/categories.php');
    exit;
}
verifyCsrf();

$id = (int)($_POST['id'] ?? 0);
if (!$id) { header('Location: ' . ADMIN_URL . '/categories.php'); exit; }

$db  = getDB();
$st  = $db->prepare('SELECT * FROM categories WHERE id=?');
$st->execute([$id]);
$cat = $st->fetch();

if (!$cat) { setFlash('error','Category not found.'); header('Location: '.ADMIN_URL.'/categories.php'); exit; }

// Delete product images (main + extra) for every product in this category
$prods = $db->prepare('SELECT id, image FROM products WHERE category_id=?');
$prods->execute([$id]);
foreach ($prods->fetchAll() as $p) {
    if ($p['image'] && file_exists(UPLOAD_DIR . $p['image'])) {
        @unlink(UPLOAD_DIR . $p['image']);
    }
    deleteAllProductImages((int)$p['id']);
}

$db->prepare('DELETE FROM categories WHERE id=?')->execute([$id]);
setFlash('success', 'Category "' . $cat['name'] . '" and all its products deleted.');
header('Location: ' . ADMIN_URL . '/categories.php');
exit;
