<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';

requireAdminLogin();

$id      = (int)($_GET['id'] ?? 0);
$product = $id ? getProductById($id) : null;
if (!$product) { setFlash('error','Product not found.'); header('Location:'.ADMIN_URL.'/products.php'); exit; }

$categories   = getAllCategories();
$productImages = getProductImages($id); // extra images from product_images table
$errors       = [];
$input        = $product;

// Handle AJAX delete of a single extra image
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_image_id'])) {
    header('Content-Type: application/json');
    if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        http_response_code(403);
        echo json_encode(['ok' => false, 'error' => 'Security check failed. Please refresh the page.']);
        exit;
    }
    $delId = (int)$_POST['delete_image_id'];
    deleteProductImage($delId, $id);
    echo json_encode(['ok' => true]);
    exit;
}

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
    $slug = $product['slug'];
    if (slugify($input['name']) !== slugify($product['name'])) {
        $slug = slugify($input['name']);
        $st2  = getDB()->prepare('SELECT id FROM products WHERE slug=? AND id!=?');
        $st2->execute([$slug, $id]);
        if ($st2->fetch()) $slug .= '-' . time();
    }

    // Handle new images uploaded
    $primaryImage = $product['image'];
    $newExtraImages = [];

    // Explicit MAIN IMAGE replacement (separate uploader — fixes "main image never changes" bug)
    if (!empty($_FILES['main_image']['name'])) {
        try {
            $newMain = uploadProductImage($_FILES['main_image']);
            if ($primaryImage && file_exists(UPLOAD_DIR . $primaryImage)) {
                @unlink(UPLOAD_DIR . $primaryImage);
            }
            $primaryImage = $newMain;
        } catch (RuntimeException $e) {
            $errors[] = 'Main image: ' . $e->getMessage();
        }
    }

    if (!empty($_FILES['images']['name'][0])) {
        $files = $_FILES['images'];
        $count = count($files['name']);
        // Count existing extra images so we don't exceed 5 total
        $existingCount = count(getProductImages($id));

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
                // If no primary image yet, make first one primary
                if (!$primaryImage) {
                    $primaryImage = $fname;
                } elseif ($existingCount < 4) {
                    $newExtraImages[] = $fname;
                    $existingCount++;
                } else {
                    // Already at 5 total (1 primary + 4 extras), skip
                    @unlink(UPLOAD_DIR . $fname);
                }
            } catch (RuntimeException $e) {
                $errors[] = 'Image ' . ($i + 1) . ': ' . $e->getMessage();
            }
        }
    }

    if (empty($errors)) {
        $db = getDB();
        $db->prepare(
            'UPDATE products SET category_id=?,name=?,slug=?,brand=?,sku=?,short_desc=?,description=?,
             price=?,old_price=?,image=?,video_url=?,stock_status=?,featured=?,status=?,meta_title=?,meta_description=?,
             product_type=?,channels=?,storage_gb=?,poe_ports=?,poe_budget_watts=?,camera_watts=?,price_per_meter=?
             WHERE id=?'
        )->execute([
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
            $id,
        ]);

        // Save new extra images
        $lastOrder = count(getProductImages($id));
        foreach ($newExtraImages as $i => $fname) {
            addProductImage($id, $fname, $lastOrder + $i + 1);
        }

        $product['slug'] = $slug;
        setFlash('success', 'Product updated successfully!');
        header('Location: ' . ADMIN_URL . '/products.php');
        exit;
    }
    // Refresh extra images after potential changes
    $productImages = getProductImages($id);
}

$adminPageTitle = 'Edit Product';
include __DIR__ . '/includes/header.php';
?>

<div class="admin-page-header">
  <h1 class="admin-page-title"><i class="fa-solid fa-pen me-2"></i>Edit Product</h1>
  <div class="d-flex gap-2">
    <a href="<?= SITE_URL ?>/product/<?= h($product['slug']) ?>" target="_blank" class="btn-admin-outline">
      <i class="fa-solid fa-eye me-1"></i>View
    </a>
    <a href="<?= ADMIN_URL ?>/products.php" class="btn-admin-outline">
      <i class="fa-solid fa-arrow-left me-1"></i>Back
    </a>
  </div>
</div>

<?php if ($errors): ?>
<div class="alert alert-danger mb-4">
  <ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= h($e) ?></li><?php endforeach; ?></ul>
</div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" id="editForm">
          <?php csrfField(); ?>
  <div class="row g-4">

    <!-- LEFT -->
    <div class="col-lg-8">
      <div class="admin-card mb-4">
        <div class="admin-card-header"><h5><i class="fa-solid fa-info-circle me-2"></i>Product Information</h5></div>
        <div class="admin-card-body">
          <div class="mb-3">
            <label class="form-label fw-600">Product Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" value="<?= h($input['name']) ?>" required>
          </div>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-600">Brand</label>
              <input type="text" name="brand" class="form-control" value="<?= h($input['brand'] ?? '') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-600">SKU / Model No.</label>
              <input type="text" name="sku" class="form-control" value="<?= h($input['sku'] ?? '') ?>">
            </div>
          </div>
          <div class="mb-3 mt-3">
            <label class="form-label fw-600">Short Description</label>
            <input type="text" name="short_desc" class="form-control" maxlength="300" value="<?= h($input['short_desc'] ?? '') ?>">
          </div>
          <div class="mb-0">
            <label class="form-label fw-600">Full Description</label>
            <textarea name="description" class="form-control" rows="6"><?= h($input['description'] ?? '') ?></textarea>
          </div>
        </div>
      </div>

      <!-- IMAGES MANAGER -->
      <div class="admin-card mb-4">
        <div class="admin-card-header">
          <h5><i class="fa-solid fa-images me-2"></i>Product Images</h5>
          <small class="text-muted"><?= 1 + count($productImages) ?>/5 images</small>
        </div>
        <div class="admin-card-body">

          <!-- Current primary image (click to replace) -->
          <div class="mb-3">
            <label class="form-label fw-600">Main Image <small class="text-muted fw-normal">(shown on product cards)</small></label>
            <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap">
              <div id="mainImageWrap" style="position:relative;display:inline-block;cursor:pointer;border-radius:8px;overflow:hidden"
                   onclick="document.getElementById('mainImageInput').click()"
                   onmouseover="document.getElementById('mainImgOverlay').style.opacity=1"
                   onmouseout="document.getElementById('mainImgOverlay').style.opacity=0">
                <img id="mainImagePreview"
                     src="<?= $input['image'] ? h(productImageUrl($input['image'])) : ASSETS_URL.'/img/no-image.jpg' ?>"
                     style="height:110px;width:150px;object-fit:cover;display:block;border:2px solid var(--admin-accent)"
                     onerror="this.src='<?= ASSETS_URL ?>/img/no-image.jpg'">
                <?php if ($input['image']): ?>
                <span style="position:absolute;bottom:0;left:0;right:0;background:var(--admin-accent);color:#fff;font-size:10px;font-weight:700;text-align:center;padding:2px">MAIN</span>
                <?php endif; ?>
                <div id="mainImgOverlay" style="position:absolute;inset:0;background:rgba(10,22,40,.6);color:#fff;display:flex;flex-direction:column;align-items:center;justify-content:center;font-size:12px;font-weight:700;opacity:0;transition:opacity .2s">
                  <i class="fa-solid fa-camera fa-lg mb-1"></i> Click to Change
                </div>
              </div>
              <div>
                <p class="text-muted small mb-1">Click the image to upload a new main photo.</p>
                <p class="text-muted small mb-0" id="mainImageFileName" style="font-weight:600"></p>
              </div>
            </div>
            <input type="file" name="main_image" id="mainImageInput" class="upload-input" accept=".jpg,.jpeg,.png,.webp">
          </div>

          <!-- Extra images with delete buttons -->
          <?php if ($productImages): ?>
          <div class="mb-3">
            <label class="form-label fw-600">Additional Images</label>
            <div class="d-flex flex-wrap gap-2" id="extraImagesContainer">
              <?php foreach ($productImages as $pImg): ?>
              <div class="extra-img-item" id="img-<?= $pImg['id'] ?>" style="position:relative">
                <img src="<?= h(productImageUrl($pImg['image'])) ?>"
                     style="height:90px;width:120px;object-fit:cover;border-radius:8px;border:1px solid var(--admin-border)"
                     onerror="this.src='<?= ASSETS_URL ?>/img/no-image.jpg'">
                <button type="button"
                        onclick="deleteExtraImage(<?= $pImg['id'] ?>, <?= $id ?>)"
                        style="position:absolute;top:-8px;right:-8px;width:22px;height:22px;background:#dc3545;color:#fff;border:none;border-radius:50%;font-size:12px;cursor:pointer;display:flex;align-items:center;justify-content:center;line-height:1"
                        title="Delete this image">
                  <i class="fa-solid fa-xmark"></i>
                </button>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
          <?php endif; ?>

          <!-- Upload new images -->
          <?php $slots = 5 - 1 - count($productImages); ?>
          <?php if ($slots > 0): ?>
          <div class="upload-area" id="uploadArea">
            <i class="fa-solid fa-cloud-arrow-up fa-2x mb-2" style="color:var(--muted)"></i>
            <p class="mb-1"><strong>Add more images</strong> (<?= $slots ?> slot<?= $slots>1?'s':'' ?> remaining)</p>
            <p class="text-muted small mb-0">JPG, PNG, WEBP – Max 5MB each</p>
            <input type="file" name="images[]" id="imageInput" class="upload-input"
                   accept=".jpg,.jpeg,.png,.webp" multiple>
          </div>
          <div id="previewGrid" class="row g-2 mt-2"></div>
          <?php else: ?>
          <div class="alert alert-info py-2 mb-0" style="font-size:14px">
            <i class="fa-solid fa-circle-info me-1"></i>Maximum 5 images reached. Delete an image to upload a new one.
          </div>
          <?php endif; ?>

        </div>
      </div>

      <!-- VIDEO -->
      <div class="admin-card mb-4">
        <div class="admin-card-header">
          <h5><i class="fa-brands fa-youtube me-2" style="color:#FF0000"></i>Product Video <small class="text-muted fw-normal">optional</small></h5>
        </div>
        <div class="admin-card-body">
          <label class="form-label fw-600">YouTube / Video URL</label>
          <input type="url" name="video_url" id="videoUrlInput" class="form-control"
                 placeholder="https://www.youtube.com/watch?v=xxxxxxxxxx"
                 value="<?= h($input['video_url'] ?? '') ?>">
          <small class="text-muted">Leave blank to remove video. Paste YouTube link here.</small>

          <div id="videoPreviewWrap" style="<?= !empty($input['video_url'])?'':'display:none;' ?>margin-top:14px">
            <p class="text-muted small mb-2">Current Video:</p>
            <div class="ratio ratio-16x9" style="max-width:400px;border-radius:10px;overflow:hidden">
              <iframe id="videoPreviewFrame"
                src="<?= !empty($input['video_url']) ? h(getYouTubeEmbedUrl($input['video_url']) ?? '') : '' ?>"
                allowfullscreen></iframe>
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
            <input type="text" name="meta_title" class="form-control" maxlength="180" value="<?= h($input['meta_title'] ?? '') ?>">
          </div>
          <div class="mb-0">
            <label class="form-label fw-600">Meta Description</label>
            <textarea name="meta_description" class="form-control" rows="2" maxlength="300"><?= h($input['meta_description'] ?? '') ?></textarea>
          </div>
        </div>
      </div>
    </div>

    <!-- RIGHT SIDEBAR -->
    <div class="col-lg-4">
      <div class="admin-card mb-4">
        <div class="admin-card-header"><h5><i class="fa-solid fa-sliders me-2"></i>Settings</h5></div>
        <div class="admin-card-body">
          <div class="mb-3">
            <label class="form-label fw-600">Category <span class="text-danger">*</span></label>
            <select name="category_id" class="form-select" required>
              <option value="">— Select —</option>
              <?php foreach ($categories as $cat): ?>
              <option value="<?= $cat['id'] ?>" <?= $input['category_id'] == $cat['id'] ? 'selected' : '' ?>>
                <?= h($cat['name']) ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label fw-600">Price (PKR) <span class="text-danger">*</span></label>
            <input type="number" name="price" class="form-control" step="0.01" min="0" value="<?= h($input['price']) ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-600">Old Price (PKR)</label>
            <input type="number" name="old_price" class="form-control" step="0.01" min="0" value="<?= h($input['old_price'] ?? '') ?>">
          </div>
          <div class="mb-3">
            <label class="form-label fw-600">Stock Status</label>
            <select name="stock_status" class="form-select">
              <option value="in_stock"     <?= ($input['stock_status']==='in_stock'    ?'selected':'') ?>>In Stock</option>
              <option value="out_of_stock" <?= ($input['stock_status']==='out_of_stock'?'selected':'') ?>>Out of Stock</option>
              <option value="on_order"     <?= ($input['stock_status']==='on_order'    ?'selected':'') ?>>On Order</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label fw-600">Status</label>
            <select name="status" class="form-select">
              <option value="active"   <?= ($input['status']==='active'  ?'selected':'') ?>>Active</option>
              <option value="inactive" <?= ($input['status']==='inactive'?'selected':'') ?>>Inactive</option>
            </select>
          </div>
        </div>
      </div>

      <!-- CALCULATOR SPECS -->
      <div class="admin-card mb-4">
        <div class="admin-card-header"><h5><i class="fa-solid fa-calculator me-2"></i>Calculator Specs <small class="text-muted fw-normal">optional</small></h5></div>
        <div class="admin-card-body">
          <div class="mb-3">
            <label class="form-label fw-600">Product Type</label>
            <select name="product_type" id="productTypeSelect" class="form-select">
              <?php $pt = $input['product_type'] ?? 'other'; ?>
              <option value="other"      <?= $pt==='other'?'selected':'' ?>>Other / Not Used in Calculator</option>
              <option value="camera"     <?= $pt==='camera'?'selected':'' ?>>Camera</option>
              <option value="nvr"        <?= $pt==='nvr'?'selected':'' ?>>NVR (IP Recorder)</option>
              <option value="dvr"        <?= $pt==='dvr'?'selected':'' ?>>DVR (Analog Recorder)</option>
              <option value="hdd"        <?= $pt==='hdd'?'selected':'' ?>>Hard Disk (HDD)</option>
              <option value="poe_switch" <?= $pt==='poe_switch'?'selected':'' ?>>PoE Switch</option>
              <option value="cable"      <?= $pt==='cable'?'selected':'' ?>>Cable (priced per metre)</option>
            </select>
            <small class="text-muted">Tag this product so the CCTV Calculator can recommend it automatically.</small>
          </div>

          <div class="calc-spec-group" data-for="camera">
            <label class="form-label fw-600">Camera Power Draw (Watts)</label>
            <input type="number" name="camera_watts" class="form-control" step="0.5" min="0" placeholder="e.g. 7.5" value="<?= h($input['camera_watts'] ?? '') ?>">
          </div>

          <div class="calc-spec-group" data-for="nvr,dvr">
            <label class="form-label fw-600">Number of Channels</label>
            <input type="number" name="channels" class="form-control" min="1" placeholder="e.g. 8" value="<?= h($input['channels'] ?? '') ?>">
          </div>

          <div class="calc-spec-group" data-for="hdd">
            <label class="form-label fw-600">Storage Capacity (GB) <small class="text-muted">1TB = 1000</small></label>
            <input type="number" name="storage_gb" class="form-control" min="1" placeholder="e.g. 1000" value="<?= h($input['storage_gb'] ?? '') ?>">
          </div>

          <div class="calc-spec-group" data-for="poe_switch">
            <div class="row g-3">
              <div class="col-6">
                <label class="form-label fw-600">PoE Ports</label>
                <input type="number" name="poe_ports" class="form-control" min="1" placeholder="e.g. 8" value="<?= h($input['poe_ports'] ?? '') ?>">
              </div>
              <div class="col-6">
                <label class="form-label fw-600">Power Budget (W)</label>
                <input type="number" name="poe_budget_watts" class="form-control" min="1" placeholder="e.g. 120" value="<?= h($input['poe_budget_watts'] ?? '') ?>">
              </div>
            </div>
          </div>

          <div class="calc-spec-group" data-for="cable">
            <label class="form-label fw-600">Price per Metre (PKR)</label>
            <input type="number" name="price_per_meter" class="form-control" step="0.5" min="0" placeholder="e.g. 35" value="<?= h($input['price_per_meter'] ?? '') ?>">
          </div>
        </div>
      </div>

      <div class="admin-card mb-4">
        <div class="admin-card-body">
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="featured" id="featuredCheck" value="1" <?= $input['featured'] ? 'checked':'' ?>>
            <label class="form-check-label fw-600" for="featuredCheck">
              Featured <i class="fa-solid fa-star text-warning ms-1"></i>
            </label>
          </div>
        </div>
      </div>

      <button type="submit" class="btn-admin-primary w-100 py-3">
        <i class="fa-solid fa-floppy-disk me-2"></i>Update Product
      </button>
    </div>
  </div>
</form>

<script>
// Image previews
const imageInput  = document.getElementById('imageInput');
const previewGrid = document.getElementById('previewGrid');
const uploadArea  = document.getElementById('uploadArea');

if (uploadArea) {
  uploadArea.addEventListener('click', () => imageInput.click());
  ['dragover','dragenter'].forEach(ev => uploadArea.addEventListener(ev, e => { e.preventDefault(); uploadArea.classList.add('drag-over'); }));
  ['dragleave','drop'].forEach(ev => uploadArea.addEventListener(ev, () => uploadArea.classList.remove('drag-over')));

  if (imageInput) {
    imageInput.addEventListener('change', () => showPreviews(imageInput.files));
  }
}

function showPreviews(files) {
  if (!previewGrid) return;
  previewGrid.innerHTML = '';
  const max = Math.min(files.length, <?= $slots ?>);
  for (let i = 0; i < max; i++) {
    const reader = new FileReader();
    reader.onload = ev => {
      const col = document.createElement('div');
      col.className = 'col-4';
      col.innerHTML = `<img src="${ev.target.result}" style="width:100%;height:80px;object-fit:cover;border-radius:7px;border:1px solid var(--admin-border)">`;
      previewGrid.appendChild(col);
    };
    reader.readAsDataURL(files[i]);
  }
}

// Delete extra image via fetch (no page reload)
const CSRF_TOKEN = "<?= h(csrfToken()) ?>";
async function deleteExtraImage(imgId, productId) {
  if (!confirm('Delete this image?')) return;
  const form = new FormData();
  form.append('delete_image_id', imgId);
  form.append('csrf_token', CSRF_TOKEN);
  const res = await fetch(window.location.href, { method: 'POST', body: form });
  const data = await res.json();
  if (data.ok) {
    const el = document.getElementById('img-' + imgId);
    if (el) el.remove();
  }
}

// Video URL preview
document.getElementById('videoUrlInput').addEventListener('input', function() {
  const url   = this.value.trim();
  const wrap  = document.getElementById('videoPreviewWrap');
  const frame = document.getElementById('videoPreviewFrame');
  let yt = url.match(/(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/|shorts\/))([a-zA-Z0-9_-]{11})/);
  if (!yt) yt = url.match(/[?&]v=([a-zA-Z0-9_-]{11})/);
  if (yt) {
    frame.src = 'https://www.youtube.com/embed/' + yt[1] + '?rel=0&modestbranding=1';
    wrap.style.display = 'block';
  } else if (!url) {
    frame.src = '';
    wrap.style.display = 'none';
  }
});

// ── Main Image: instant preview when a new file is chosen ──
document.getElementById('mainImageInput').addEventListener('change', function (e) {
  const file = e.target.files[0];
  if (!file) return;
  const reader = new FileReader();
  reader.onload = ev => {
    document.getElementById('mainImagePreview').src = ev.target.result;
  };
  reader.readAsDataURL(file);
  document.getElementById('mainImageFileName').textContent = '✓ New image selected: ' + file.name + ' (will be saved on Update)';
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
