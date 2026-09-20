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
if ($id < 1) {
    setFlash('error', 'Invalid category.');
    header('Location: ' . ADMIN_URL . '/categories.php');
    exit;
}

try {
    $db = getDB();
    $st = $db->prepare('UPDATE categories SET featured = IF(featured = 1, 0, 1) WHERE id = ?');
    $st->execute([$id]);
    setFlash('success', 'Homepage category selection updated.');
} catch (PDOException $e) {
    setFlash('error', 'Featured categories database migration has not been run yet.');
}

header('Location: ' . ADMIN_URL . '/categories.php');
exit;
