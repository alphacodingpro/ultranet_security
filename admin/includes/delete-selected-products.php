<?php
/**
 * Remove selected catalog rows atomically. Uploaded files are removed only after
 * the database commit and only if no remaining product uses the filename.
 *
 * @return array{deleted:int,image_errors:int}
 */
function deleteSelectedProducts(PDO $db, array $ids, string $uploadDir): array
{
    if (!$ids || count($ids) > 25) {
        throw new InvalidArgumentException('Select between 1 and 25 products.');
    }
    foreach ($ids as $id) {
        if (!is_int($id) || $id < 1) {
            throw new InvalidArgumentException('Invalid product selection.');
        }
    }
    $ids = array_values(array_unique($ids));
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $images = [];
    $deleted = 0;

    $db->beginTransaction();
    try {
        $products = $db->prepare("SELECT id, image FROM products WHERE id IN ($placeholders) FOR UPDATE");
        $products->execute($ids);
        $found = $products->fetchAll(PDO::FETCH_ASSOC);
        if ($found) {
            $foundIds = array_map(static fn($row): int => (int)$row['id'], $found);
            foreach ($found as $row) {
                $images[] = $row['image'];
            }
            $foundPlaceholders = implode(',', array_fill(0, count($foundIds), '?'));
            $gallery = $db->prepare("SELECT image FROM product_images WHERE product_id IN ($foundPlaceholders)");
            $gallery->execute($foundIds);
            array_push($images, ...$gallery->fetchAll(PDO::FETCH_COLUMN));
            $delete = $db->prepare("DELETE FROM products WHERE id IN ($foundPlaceholders)");
            $delete->execute($foundIds);
            $deleted = $delete->rowCount();
        }
        $db->commit();
    } catch (Throwable $exception) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        throw $exception;
    }

    $remainingProduct = $db->prepare('SELECT 1 FROM products WHERE image = ? LIMIT 1');
    $remainingGallery = $db->prepare('SELECT 1 FROM product_images WHERE image = ? LIMIT 1');
    $imageErrors = 0;
    foreach (array_unique(array_filter($images, 'is_string')) as $filename) {
        // Database values are untrusted; never follow a path out of the upload directory.
        if (!preg_match('/\A[A-Za-z0-9][A-Za-z0-9._-]*\.(?:jpe?g|png|webp|gif|avif)\z/iD', $filename)
            || in_array(strtolower($filename), ['no-image.jpg', 'no-image.png'], true)) {
            continue;
        }
        $remainingProduct->execute([$filename]);
        if ($remainingProduct->fetchColumn()) {
            $remainingProduct->closeCursor();
            continue;
        }
        $remainingProduct->closeCursor();
        $remainingGallery->execute([$filename]);
        if ($remainingGallery->fetchColumn()) {
            $remainingGallery->closeCursor();
            continue;
        }
        $remainingGallery->closeCursor();
        $path = rtrim($uploadDir, '/\\') . DIRECTORY_SEPARATOR . $filename;
        if (is_link($path)) {
            continue;
        }
        if (is_file($path) && !@unlink($path)) {
            $imageErrors++;
        }
    }
    return ['deleted' => $deleted, 'image_errors' => $imageErrors];
}
