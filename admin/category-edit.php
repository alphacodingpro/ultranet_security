<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';

requireAdminLogin();

$id  = (int)($_GET['id'] ?? 0);
$db  = getDB();
$cat = $id ? $db->prepare('SELECT * FROM categories WHERE id=?') : null;
if ($cat) { $cat->execute([$id]); $cat = $cat->fetch(); }
if (!$cat) { setFlash('error','Category not found.'); header('Location: '.ADMIN_URL.'/categories.php'); exit; }

$errors = [];
$input  = $cat;
$parentCategories = array_values(array_filter(getRootCategories(), static fn(array $item): bool => (int)$item['id'] !== $id));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $input = [
        'name'        => trim($_POST['name'] ?? ''),
        'slug'        => slugify(trim($_POST['slug'] ?? '')),
        'description' => trim($_POST['description'] ?? ''),
        'icon'        => trim($_POST['icon'] ?? 'fa-solid fa-camera'),
        'parent_id'   => (int)($_POST['parent_id'] ?? 0),
        'brand'       => trim($_POST['brand'] ?? ''),
        'featured'    => isset($_POST['featured']) ? 1 : 0,
    ];

    if (!$input['name']) $errors[] = 'Category name is required.';
    if (!$input['slug']) $errors[] = 'Slug is required.';

    if (empty($errors)) {
        // Check slug uniqueness (allow same slug for this category)
        $st = $db->prepare('SELECT id FROM categories WHERE slug=? AND id!=?');
        $st->execute([$input['slug'], $id]);
        if ($st->fetch()) $input['slug'] .= '-' . time();

        $db->prepare('UPDATE categories SET name=?,slug=?,description=?,icon=?,parent_id=?,brand=?,featured=? WHERE id=?')
           ->execute([$input['name'], $input['slug'], $input['description'] ?: null, $input['icon'], $input['parent_id'] ?: null, $input['brand'] ?: null, $input['featured'], $id]);
        setFlash('success', 'Category updated!');
        header('Location: ' . ADMIN_URL . '/categories.php');
        exit;
    }
}

$adminPageTitle = 'Edit Category';
include __DIR__ . '/includes/header.php';
?>

<div class="admin-page-header">
  <h1 class="admin-page-title"><i class="fa-solid fa-pen me-2"></i>Edit Category</h1>
  <a href="<?= ADMIN_URL ?>/categories.php" class="btn-admin-outline"><i class="fa-solid fa-arrow-left me-1"></i>Back</a>
</div>

<?php if ($errors): ?>
<div class="alert alert-danger mb-4"><ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= h($e) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<div class="row justify-content-center">
  <div class="col-lg-7">
    <div class="admin-card">
      <div class="admin-card-body">
        <form method="POST">
          <?php csrfField(); ?>
          <div class="mb-3">
            <label class="form-label fw-600">Category Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" value="<?= h($input['name']) ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-600">SEO Slug <span class="text-danger">*</span></label>
            <input type="text" name="slug" class="form-control" value="<?= h($input['slug']) ?>" required>
            <small class="text-muted">URL: <?= SITE_URL ?>/products.php?category=<?= h($input['slug']) ?></small>
          </div>
          <div class="mb-3">
            <label class="form-label fw-600">Description</label>
            <textarea name="description" class="form-control" rows="3"><?= h($input['description'] ?? '') ?></textarea>
          </div>
          <div class="mb-4">
            <label class="form-label fw-600">Icon Class</label>
            <div class="input-group">
              <span class="input-group-text"><i id="iconPreview" class="<?= h($input['icon']) ?>"></i></span>
              <input type="text" name="icon" class="form-control" value="<?= h($input['icon']) ?>"
                     oninput="document.getElementById('iconPreview').className=this.value">
            </div>
            <small class="text-muted">Browse at <a href="https://fontawesome.com/icons" target="_blank">fontawesome.com/icons</a></small>
          </div>
          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label fw-600">Parent Category</label>
              <select name="parent_id" class="form-select">
                <option value="0">None — Main Category</option>
                <?php foreach ($parentCategories as $parent): ?>
                <option value="<?= $parent['id'] ?>" <?= (int)($input['parent_id'] ?? 0) === (int)$parent['id'] ? 'selected' : '' ?>><?= h($parent['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-600">Brand</label>
              <input type="text" name="brand" class="form-control" value="<?= h($input['brand'] ?? '') ?>" placeholder="e.g. Hikvision">
            </div>
          </div>
          <div class="form-check form-switch mb-4">
            <input class="form-check-input" type="checkbox" role="switch" id="featured" name="featured" value="1" <?= !empty($input['featured']) ? 'checked' : '' ?>>
            <label class="form-check-label fw-600" for="featured">Show this category on homepage</label>
            <div class="small text-muted">Turn this off to hide the category from the homepage without deleting it.</div>
          </div>
          <button type="submit" class="btn-admin-primary w-100 py-3">
            <i class="fa-solid fa-floppy-disk me-2"></i>Update Category
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
