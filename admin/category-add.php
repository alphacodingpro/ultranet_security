<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';

requireAdminLogin();

$errors = [];
$input  = ['name'=>'','slug'=>'','description'=>'','icon'=>'fa-solid fa-camera','parent_id'=>0,'brand'=>'','featured'=>0];
$parentCategories = getRootCategories();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $input = [
        'name'        => trim($_POST['name'] ?? ''),
        'slug'        => slugify(trim($_POST['slug'] ?? $_POST['name'] ?? '')),
        'description' => trim($_POST['description'] ?? ''),
        'icon'        => trim($_POST['icon'] ?? 'fa-solid fa-camera'),
        'parent_id'   => (int)($_POST['parent_id'] ?? 0),
        'brand'       => trim($_POST['brand'] ?? ''),
        'featured'    => isset($_POST['featured']) ? 1 : 0,
    ];

    if (!$input['name']) $errors[] = 'Category name is required.';
    if (!$input['slug']) $errors[] = 'Slug is required.';

    if (empty($errors)) {
        $db = getDB();
        // Check slug unique
        $st = $db->prepare('SELECT id FROM categories WHERE slug = ?');
        $st->execute([$input['slug']]);
        if ($st->fetch()) {
            $input['slug'] .= '-' . time();
        }
        $db->prepare('INSERT INTO categories (name,slug,description,icon,parent_id,brand,featured) VALUES (?,?,?,?,?,?,?)')
           ->execute([$input['name'], $input['slug'], $input['description'] ?: null, $input['icon'], $input['parent_id'] ?: null, $input['brand'] ?: null, $input['featured']]);
        setFlash('success', 'Category "' . $input['name'] . '" added!');
        header('Location: ' . ADMIN_URL . '/categories.php');
        exit;
    }
}

$adminPageTitle = 'Add Category';
include __DIR__ . '/includes/header.php';
?>

<div class="admin-page-header">
  <h1 class="admin-page-title"><i class="fa-solid fa-circle-plus me-2"></i>Add Category</h1>
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
            <input type="text" name="name" class="form-control" placeholder="e.g. CCTV Cameras" value="<?= h($input['name']) ?>" required
                   oninput="document.getElementById('slugPreview').value=this.value.toLowerCase().replace(/[^a-z0-9]+/g,'-').replace(/^-|-$/g,'')">
          </div>
          <div class="mb-3">
            <label class="form-label fw-600">SEO Slug <span class="text-danger">*</span></label>
            <input type="text" name="slug" id="slugPreview" class="form-control" placeholder="e.g. cctv-cameras" value="<?= h($input['slug']) ?>" required>
            <small class="text-muted">URL: <?= SITE_URL ?>/products.php?category=<strong id="slugDisplay"><?= h($input['slug']) ?></strong></small>
          </div>
          <div class="mb-3">
            <label class="form-label fw-600">Description</label>
            <textarea name="description" class="form-control" rows="3" placeholder="Brief description of this category"><?= h($input['description']) ?></textarea>
          </div>
          <div class="mb-4">
            <label class="form-label fw-600">Font Awesome Icon Class</label>
            <input type="text" name="icon" class="form-control" value="<?= h($input['icon']) ?>" placeholder="e.g. fa-solid fa-camera">
            <small class="text-muted">Find icons at <a href="https://fontawesome.com/icons" target="_blank">fontawesome.com/icons</a></small>
          </div>
          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label fw-600">Parent Category</label>
              <select name="parent_id" class="form-select">
                <option value="0">None — Main Category</option>
                <?php foreach ($parentCategories as $parent): ?>
                <option value="<?= $parent['id'] ?>" <?= (int)$input['parent_id'] === (int)$parent['id'] ? 'selected' : '' ?>><?= h($parent['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-600">Brand</label>
              <input type="text" name="brand" class="form-control" value="<?= h($input['brand']) ?>" placeholder="e.g. Hikvision">
            </div>
          </div>
          <div class="form-check form-switch mb-4">
            <input class="form-check-input" type="checkbox" role="switch" id="featured" name="featured" value="1" <?= !empty($input['featured']) ? 'checked' : '' ?>>
            <label class="form-check-label fw-600" for="featured">Show this category on homepage</label>
            <div class="small text-muted">Only featured categories are displayed in the homepage category section.</div>
          </div>
          <button type="submit" class="btn-admin-primary w-100 py-3">
            <i class="fa-solid fa-floppy-disk me-2"></i>Save Category
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
document.querySelector('[name="slug"]').addEventListener('input', function () {
  document.getElementById('slugDisplay').textContent = this.value;
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
