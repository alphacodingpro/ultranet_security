<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';

requireAdminLogin();

$categories = getAllCategories();
$errors     = [];
$input      = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $input = [
        'name'             => trim($_POST['name'] ?? ''),
        'category_id'      => (int)($_POST['category_id'] ?? 0),
        'brand'            => trim($_POST['brand'] ?? ''),
        'sku'              => trim($_POST['sku'] ?? ''),
        'short_desc'       => trim($_POST['short_desc'] ?? ''),
        'description'      => trim($_POST['description'] ?? ''),
        'price'            => trim($_POST['price'] ?? ''),
        'old_price'        => trim($_POST['old_price'] ?? ''),
        'video_url'        => trim($_POST['video_url'] ?? ''),
        'stock_status'     => $_POST['stock_status'] ?? 'in_stock',
        'featured'         => isset($_POST['featured']) ? 1 : 0,
        'status'           => $_POST['status'] ?? 'active',
        'meta_title'       => trim($_POST['meta_title'] ?? ''),
        'meta_description' => trim($_POST['meta_description'] ?? ''),
        'product_type'      => $_POST['product_type'] ?? 'other',
        'channels'          => trim($_POST['channels'] ?? ''),
        'storage_gb'        => trim($_POST['storage_gb'] ?? ''),
        'poe_ports'         => trim($_POST['poe_ports'] ?? ''),
        'poe_budget_watts'  => trim($_POST['poe_budget_watts'] ?? ''),
        'camera_watts'      => trim($_POST['camera_watts'] ?? ''),
        'price_per_meter'   => trim($_POST['price_per_meter'] ?? ''),
    ];

    if (!$input['name'])        $errors[] = 'Product name is required.';
    if (!$input['category_id']) $errors[] = 'Please select a category.';
    if (!is_numeric($input['price']) || $input['price'] < 0) $errors[] = 'Valid price is required.';

    // Slug
    $db   = getDB();
    $slug = slugify($input['name']);
    $st   = $db->prepare('SELECT id FROM products WHERE slug = ?');
    $st->execute([$slug]);
    if ($st->fetch()) $slug .= '-' . time();

    // Primary image (first uploaded file or first in multi-upload)
    $primaryImage = null;
    $extraImages  = [];

    // Handle multiple image uploads (input name="images[]")
    if (!empty($_FILES['images']['name'][0])) {
        $files = $_FILES['images'];
        $count = count($files['name']);
        for ($i = 0; $i < $count; $i++) {
            if ($files['error'][$i] !== UPLOAD_ERR_OK) continue;
            $singleFile = [
                'name'     => $files['name'][$i],
                'type'     => $files['type'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'error'    => $files['error'][$i],
                'size'     => $files['size'][$i],
            ];
            try {
                $fname = uploadProductImage($singleFile);
                if ($primaryImage === null) {
                    $primaryImage = $fname;
                } else {
                    $extraImages[] = $fname;
                }
            } catch (RuntimeException $e) {
                $errors[] = 'Image ' . ($i + 1) . ': ' . $e->getMessage();
            }
        }
    }

    if (empty($errors)) {
        $st = $db->prepare(
            'INSERT INTO products
             (category_id,name,slug,brand,sku,short_desc,description,price,old_price,image,video_url,stock_status,featured,status,meta_title,meta_description,
              product_type,channels,storage_gb,poe_ports,poe_budget_watts,camera_watts,price_per_meter)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)'
        );
        $st->execute([
            $input['category_id'], $input['name'], $slug,
            $input['brand'] ?: null, $input['sku'] ?: null,
            $input['short_desc'] ?: null, $input['description'] ?: null,
            (float)$input['price'],
            $input['old_price'] !== '' ? (float)$input['old_price'] : null,
            $primaryImage,
            $input['video_url'] ?: null,
            $input['stock_status'], $input['featured'], $input['status'],
            $input['meta_title'] ?: null, $input['meta_description'] ?: null,
            $input['product_type'],
            $input['channels'] !== '' ? (int)$input['channels'] : null,
            $input['storage_gb'] !== '' ? (int)$input['storage_gb'] : null,
            $input['poe_ports'] !== '' ? (int)$input['poe_ports'] : null,
            $input['poe_budget_watts'] !== '' ? (int)$input['poe_budget_watts'] : null,
            $input['camera_watts'] !== '' ? (float)$input['camera_watts'] : null,
            $input['price_per_meter'] !== '' ? (float)$input['price_per_meter'] : null,
        ]);
        $newId = (int)$db->lastInsertId();

        // Save extra images to product_images table
        foreach ($extraImages as $order => $fname) {
            addProductImage($newId, $fname, $order + 1);
        }

        setFlash('success', 'Product "' . $input['name'] . '" added successfully!');
        header('Location: ' . ADMIN_URL . '/products.php');
        exit;
    }
}

$adminPageTitle = 'Add Product';
include __DIR__ . '/includes/header.php';
?>

<div class="admin-page-header">
  <h1 class="admin-page-title"><i class="fa-solid fa-circle-plus me-2"></i>Add New Product</h1>
  <a href="<?= ADMIN_URL ?>/products.php" class="btn-admin-outline"><i class="fa-solid fa-arrow-left me-1"></i>Back</a>
</div>

<?php if ($errors): ?>
<div class="alert alert-danger mb-4">
  <ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= h($e) ?></li><?php endforeach; ?></ul>
</div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
          <?php csrfField(); ?>
  <div class="row g-4">

    <!-- LEFT -->
    <div class="col-lg-8">
      <div class="admin-card mb-4">
        <div class="admin-card-header"><h5><i class="fa-solid fa-info-circle me-2"></i>Product Information</h5></div>
        <div class="admin-card-body">
          <div class="mb-3">
            <label class="form-label fw-600">Product Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" placeholder="e.g. Hikvision 2MP Dome Camera" value="<?= h($input['name'] ?? '') ?>" required>
          </div>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-600">Brand</label>
              <input type="text" name="brand" class="form-control" placeholder="e.g. Hikvision" value="<?= h($input['brand'] ?? '') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-600">SKU / Model No.</label>
              <input type="text" name="sku" class="form-control" placeholder="e.g. DS-2CD1143G0" value="<?= h($input['sku'] ?? '') ?>">
            </div>
          </div>
          <div class="mb-3 mt-3">
            <label class="form-label fw-600">Short Description</label>
            <input type="text" name="short_desc" class="form-control" maxlength="300"
                   placeholder="One line summary shown on product card"
                   value="<?= h($input['short_desc'] ?? '') ?>">
          </div>
          <div class="mb-0">
            <label class="form-label fw-600">Full Description</label>
            <textarea name="description" class="form-control" rows="6"
                      placeholder="Detailed product description..."><?= h($input['description'] ?? '') ?></textarea>
          </div>
        </div>
      </div>

      <!-- IMAGES -->
      <div class="admin-card mb-4">
        <div class="admin-card-header"><h5><i class="fa-solid fa-images me-2"></i>Product Images <small class="text-muted fw-normal">(up to 5 images)</small></h5></div>
        <div class="admin-card-body">
          <div class="upload-area" id="uploadArea">
            <i class="fa-solid fa-cloud-arrow-up fa-2x mb-2" style="color:var(--muted)"></i>
            <p class="mb-1"><strong>Click to select images</strong> or drag & drop</p>
            <p class="text-muted small mb-0">JPG, PNG, WEBP – Max 5MB each – First image = main product image</p>
            <input type="file" name="images[]" id="imageInput" class="upload-input"
                   accept=".jpg,.jpeg,.png,.webp" multiple>
          </div>
          <!-- Preview grid -->
          <div id="previewGrid" class="row g-2 mt-2"></div>
          <small class="text-muted">First image will be the main product image shown on cards.</small>
        </div>
      </div>

      <!-- VIDEO -->
      <div class="admin-card mb-4">
        <div class="admin-card-header"><h5><i class="fa-brands fa-youtube me-2" style="color:#FF0000"></i>Product Video <small class="text-muted fw-normal">optional</small></h5></div>
        <div class="admin-card-body">
          <label class="form-label fw-600">YouTube / Video URL</label>
          <input type="url" name="video_url" class="form-control"
                 placeholder="e.g. https://www.youtube.com/watch?v=xxxxxxxxxx"
                 value="<?= h($input['video_url'] ?? '') ?>">
          <small class="text-muted">Supports: YouTube, youtu.be short links. Video will show in a tab alongside product images.</small>

          <!-- Live preview -->
          <div id="videoPreviewWrap" style="display:none;margin-top:14px">
            <p class="text-muted small mb-2">Video Preview:</p>
            <div class="ratio ratio-16x9" style="max-width:400px;border-radius:10px;overflow:hidden">
              <iframe id="videoPreviewFrame" src="" allowfullscreen></iframe>
            </div>
          </div>
        </div>
      </div>

      <!-- SEO -->
      <div class="admin-card">
        <div class="admin-card-header"><h5><i class="fa-solid fa-magnifying-glass me-2"></i>SEO Settings</h5></div>
        <div class="admin-card-body">
          <div class="mb-3">
            <label class="form-label fw-600">Meta Title</label>
            <input type="text" name="meta_title" class="form-control" maxlength="180"
                   placeholder="e.g. Buy Hikvision 2MP Dome Camera in Karachi"
                   value="<?= h($input['meta_title'] ?? '') ?>">
          </div>
          <div class="mb-0">
            <label class="form-label fw-600">Meta Description</label>
            <textarea name="meta_description" class="form-control" rows="2" maxlength="300"
                      placeholder="Brief description for search engines..."><?= h($input['meta_description'] ?? '') ?></textarea>
          </div>
        </div>
      </div>
    </div>

    <!-- RIGHT SIDEBAR -->
    <div class="col-lg-4">
      <div class="admin-card mb-4">
        <div class="admin-card-header"><h5><i class="fa-solid fa-sliders me-2"></i>Product Settings</h5></div>
        <div class="admin-card-body">
          <div class="mb-3">
            <label class="form-label fw-600">Category <span class="text-danger">*</span></label>
            <select name="category_id" class="form-select" required>
              <option value="">— Select —</option>
              <?php foreach ($categories as $cat): ?>
              <option value="<?= $cat['id'] ?>" <?= ($input['category_id'] ?? 0) == $cat['id'] ? 'selected':'' ?>>
                <?= h($cat['name']) ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label fw-600">Price (PKR) <span class="text-danger">*</span></label>
            <input type="number" name="price" class="form-control" step="0.01" min="0"
                   placeholder="e.g. 4500" value="<?= h($input['price'] ?? '') ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-600">Old Price (PKR) <small class="text-muted">optional</small></label>
            <input type="number" name="old_price" class="form-control" step="0.01" min="0"
                   placeholder="e.g. 5200" value="<?= h($input['old_price'] ?? '') ?>">
          </div>
          <div class="mb-3">
            <label class="form-label fw-600">Stock Status</label>
            <select name="stock_status" class="form-select">
              <option value="in_stock">In Stock</option>
              <option value="out_of_stock">Out of Stock</option>
              <option value="on_order">Available on Order</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label fw-600">Status</label>
            <select name="status" class="form-select">
              <option value="active">Active (visible)</option>
              <option value="inactive">Inactive (hidden)</option>
            </select>
          </div>
        </div>
      </div>

      <!-- CALCULATOR SPECS -->
      <div class="admin-card mb-4" id="calcSpecsCard">
        <div class="admin-card-header"><h5><i class="fa-solid fa-calculator me-2"></i>Calculator Specs <small class="text-muted fw-normal">optional</small></h5></div>
        <div class="admin-card-body">
          <div class="mb-3">
            <label class="form-label fw-600">Product Type</label>
            <select name="product_type" id="productTypeSelect" class="form-select">
              <option value="other">Other / Not Used in Calculator</option>
              <option value="camera">Camera</option>
              <option value="nvr">NVR (IP Recorder)</option>
              <option value="dvr">DVR (Analog Recorder)</option>
              <option value="hdd">Hard Disk (HDD)</option>
              <option value="poe_switch">PoE Switch</option>
              <option value="cable">Cable (priced per metre)</option>
            </select>
            <small class="text-muted">Tag this product so the CCTV Calculator can recommend it automatically.</small>
          </div>

          <div class="calc-spec-group" data-for="camera" style="display:none">
            <label class="form-label fw-600">Camera Power Draw (Watts)</label>
            <input type="number" name="camera_watts" class="form-control" step="0.5" min="0" placeholder="e.g. 7.5">
          </div>

          <div class="calc-spec-group" data-for="nvr,dvr" style="display:none">
            <label class="form-label fw-600">Number of Channels</label>
            <input type="number" name="channels" class="form-control" min="1" placeholder="e.g. 8">
          </div>

          <div class="calc-spec-group" data-for="hdd" style="display:none">
            <label class="form-label fw-600">Storage Capacity (GB) <small class="text-muted">1TB = 1000</small></label>
            <input type="number" name="storage_gb" class="form-control" min="1" placeholder="e.g. 1000">
          </div>

          <div class="calc-spec-group" data-for="poe_switch" style="display:none">
            <div class="row g-3">
              <div class="col-6">
                <label class="form-label fw-600">PoE Ports</label>
                <input type="number" name="poe_ports" class="form-control" min="1" placeholder="e.g. 8">
              </div>
              <div class="col-6">
                <label class="form-label fw-600">Power Budget (Watts)</label>
                <input type="number" name="poe_budget_watts" class="form-control" min="1" placeholder="e.g. 120">
              </div>
            </div>
          </div>

          <div class="calc-spec-group" data-for="cable" style="display:none">
            <label class="form-label fw-600">Price per Metre (PKR)</label>
            <input type="number" name="price_per_meter" class="form-control" step="0.5" min="0" placeholder="e.g. 35">
          </div>
        </div>
      </div>

      <div class="admin-card mb-4">
        <div class="admin-card-body">
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="featured" id="featuredCheck" value="1">
            <label class="form-check-label fw-600" for="featuredCheck">
              Featured <i class="fa-solid fa-star text-warning ms-1"></i>
            </label>
          </div>
        </div>
      </div>

      <button type="submit" class="btn-admin-primary w-100 py-3">
        <i class="fa-solid fa-floppy-disk me-2"></i>Save Product
      </button>
    </div>
  </div>
</form>

<script>
// ── Multiple Image Preview ──
const imageInput  = document.getElementById('imageInput');
const previewGrid = document.getElementById('previewGrid');
const uploadArea  = document.getElementById('uploadArea');

uploadArea.addEventListener('click', () => imageInput.click());
['dragover','dragenter'].forEach(ev => uploadArea.addEventListener(ev, e => { e.preventDefault(); uploadArea.classList.add('drag-over'); }));
['dragleave','drop'].forEach(ev => uploadArea.addEventListener(ev, () => uploadArea.classList.remove('drag-over')));
uploadArea.addEventListener('drop', e => {
  e.preventDefault();
  imageInput.files = e.dataTransfer.files;
  showPreviews(e.dataTransfer.files);
});

imageInput.addEventListener('change', () => showPreviews(imageInput.files));

function showPreviews(files) {
  previewGrid.innerHTML = '';
  const max = Math.min(files.length, 5);
  for (let i = 0; i < max; i++) {
    const file = files[i];
    const reader = new FileReader();
    reader.onload = ev => {
      const col = document.createElement('div');
      col.className = 'col-4 col-md-3';
      col.innerHTML = `
        <div style="position:relative;border-radius:8px;overflow:hidden;border:2px solid ${i===0?'var(--admin-accent)':'var(--admin-border)'}">
          <img src="${ev.target.result}" style="width:100%;height:90px;object-fit:cover;display:block">
          ${i===0?'<span style="position:absolute;bottom:0;left:0;right:0;background:var(--admin-accent);color:#fff;font-size:10px;font-weight:700;text-align:center;padding:3px;letter-spacing:1px">MAIN</span>':''}
        </div>`;
      previewGrid.appendChild(col);
    };
    reader.readAsDataURL(file);
  }
  if (files.length > 5) {
    previewGrid.insertAdjacentHTML('beforeend',
      '<div class="col-12"><small class="text-danger">Only first 5 images will be uploaded.</small></div>');
  }
}

// ── Video URL Preview ──
document.querySelector('[name="video_url"]').addEventListener('input', function() {
  const url = this.value.trim();
  const wrap = document.getElementById('videoPreviewWrap');
  const frame = document.getElementById('videoPreviewFrame');

  // Extract YouTube ID
  let ytMatch = url.match(/(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/|shorts\/))([a-zA-Z0-9_-]{11})/);
  if (!ytMatch) ytMatch = url.match(/[?&]v=([a-zA-Z0-9_-]{11})/); // v= anywhere in query string
  if (ytMatch) {
    frame.src = 'https://www.youtube.com/embed/' + ytMatch[1] + '?rel=0&modestbranding=1';
    wrap.style.display = 'block';
  } else if (url === '') {
    wrap.style.display = 'none';
    frame.src = '';
  }
});

// ── Calculator Specs: show/hide fields based on product type ──
const typeSelect = document.getElementById('productTypeSelect');
function updateSpecVisibility() {
  const val = typeSelect.value;
  document.querySelectorAll('.calc-spec-group').forEach(g => {
    const types = g.getAttribute('data-for').split(',');
    g.style.display = types.includes(val) ? 'block' : 'none';
  });
}
typeSelect.addEventListener('change', updateSpecVisibility);
updateSpecVisibility();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
