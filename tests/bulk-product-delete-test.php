<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/admin/includes/delete-selected-products.php';

$db = getDB();
$directory = sys_get_temp_dir() . '/ultranet-bulk-delete-' . bin2hex(random_bytes(6));
if (!mkdir($directory, 0700)) throw new RuntimeException('Could not create test upload directory.');
$shared = 'bulk-shared-' . bin2hex(random_bytes(5)) . '.jpg';
$single = 'bulk-single-' . bin2hex(random_bytes(5)) . '.png';
file_put_contents($directory . '/' . $shared, 'shared');
file_put_contents($directory . '/' . $single, 'single');
$insert = $db->prepare('INSERT INTO products (category_id,name,slug,price,image) VALUES (101,?,?,0,?)');
$gallery = $db->prepare('INSERT INTO product_images (product_id,image) VALUES (?,?)');
$created = [];
try {
    foreach ([['Bulk shared first', $shared], ['Bulk shared second', $shared], ['Bulk gallery owner', $single]] as $row) {
        $insert->execute([$row[0], 'bulk-test-' . bin2hex(random_bytes(6)), $row[1]]);
        $created[] = (int)$db->lastInsertId();
    }
    $gallery->execute([$created[2], $shared]);
    $result = deleteSelectedProducts($db, [$created[0], $created[2]], $directory);
    if ($result['deleted'] !== 2 || !is_file($directory . '/' . $shared) || file_exists($directory . '/' . $single)) {
        throw new RuntimeException('Bulk delete failed shared image or unique image cleanup.');
    }
    $count = $db->prepare('SELECT COUNT(*) FROM product_images WHERE product_id = ?');
    $count->execute([$created[2]]);
    if ((int)$count->fetchColumn() !== 0) throw new RuntimeException('Gallery entries not cascaded.');
    $result = deleteSelectedProducts($db, [$created[1]], $directory);
    if ($result['deleted'] !== 1 || file_exists($directory . '/' . $shared)) {
        throw new RuntimeException('Last image reference did not remove uploaded image.');
    }
    $outside = sys_get_temp_dir() . '/bulk-delete-keep-' . bin2hex(random_bytes(6)) . '.png';
    file_put_contents($outside, 'keep');
    try {
        $insert->execute(['Bulk unsafe', 'bulk-test-' . bin2hex(random_bytes(6)), '../' . basename($outside)]);
        $unsafeId = (int)$db->lastInsertId();
        $created[] = $unsafeId;
        deleteSelectedProducts($db, [$unsafeId], $directory);
        if (!is_file($outside)) throw new RuntimeException('Unsafe image path removed a file outside uploads.');
    } finally {
        @unlink($outside);
    }
    echo "Bulk product deletion regressions passed.\n";
} finally {
    if ($created) {
        $db->exec('DELETE FROM products WHERE id IN (' . implode(',', $created) . ')');
    }
    @unlink($directory . '/' . $shared);
    @unlink($directory . '/' . $single);
    @rmdir($directory);
}
