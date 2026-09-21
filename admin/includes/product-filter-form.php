<!-- Filter form is independent of the CSV export form above. -->
<div class="admin-card mb-4"><div class="admin-card-body">
  <form method="GET" action="<?= ADMIN_URL ?>/products.php">
    <div class="row g-3 mb-3">
      <div class="col-12 col-md-4"><label for="catalogSearch" class="form-label small">Search</label><input type="search" id="catalogSearch" name="q" maxlength="200" class="form-control" placeholder="Name, brand or SKU" value="<?= h($filterSearch) ?>"></div>
      <div class="col-6 col-md-4"><label for="catalogCategory" class="form-label small">Category</label><select name="cat" id="catalogCategory" class="form-select"><option value="">All Categories</option><?php foreach ($categories as $cat): ?><option value="<?= (int)$cat['id'] ?>" <?= $filterCat == $cat['id'] ? 'selected' : '' ?>><?= h($cat['name']) ?></option><?php endforeach; ?></select></div>
      <div class="col-6 col-md-4"><label for="catalogStatus" class="form-label small">Status</label><select name="status" id="catalogStatus" class="form-select"><option value="">All Status</option><option value="active" <?= $filterStatus==='active'?'selected':'' ?>>Active</option><option value="inactive" <?= $filterStatus==='inactive'?'selected':'' ?>>Inactive</option></select></div>
    </div>
    <div class="row g-3 mb-3">
      <div class="col-6 col-md-3"><label class="form-label small" for="minPrice">Minimum price (optional)</label><input class="form-control" name="min_price" id="minPrice" type="number" min="0" max="99999999.99" step="0.01" value="<?= h($filters['min_price']) ?>"></div>
      <div class="col-6 col-md-3"><label class="form-label small" for="maxPrice">Maximum price (optional)</label><input class="form-control" name="max_price" id="maxPrice" type="number" min="0" max="99999999.99" step="0.01" value="<?= h($filters['max_price']) ?>"></div>
    </div>
    <div class="row g-3">
      <?php foreach ($filterOptions as $key=>$group): ?>
      <div class="col-12 col-md-6 col-xl-3">
        <fieldset class="border rounded p-3 h-100">
          <legend class="float-none w-auto px-1 small fw-bold"><?= h($group['label']) ?></legend>
          <?php foreach ($group['items'] as $value=>$label): $value=(string)$value; $controlId='filter-'.$key.'-'.str_replace('.','-',$value); ?>
          <div class="form-check mb-1">
            <input class="form-check-input" type="checkbox" id="<?= h($controlId) ?>" name="<?= h($key) ?>[]" value="<?= h($value) ?>" <?= in_array($value,$filters[$key],true)?'checked':'' ?>>
            <label class="form-check-label small" for="<?= h($controlId) ?>"><?= h($label) ?></label>
          </div>
          <?php endforeach; ?>
        </fieldset>
      </div>
      <?php endforeach; ?>
    </div>
    <p class="text-muted small mt-3 mb-2">Within a group, any selected option can match. Different groups must all match. MP, resolution, smart features and lens filters match listed camera names/descriptions, not independently verified specifications. Listed focal lengths may describe model variants. Unlisted information is not treated as an unsupported feature.</p>
    <p class="text-muted small">Image totals include the main image and unique gallery images, excluding standard placeholders. They count saved references, not whether an image file is reachable.</p>
    <?php foreach ($compiled['errors'] as $error): ?><p class="text-danger" role="alert"><?= h($error) ?></p><?php endforeach; ?>
    <div class="d-flex gap-2 flex-wrap align-items-center">
      <button type="submit" class="btn-admin-primary"><i class="fa-solid fa-filter me-1"></i>Apply Filters</button>
      <a href="<?= ADMIN_URL ?>/products.php" class="btn-admin-outline">Clear All</a>
      <span class="small text-muted"><?= $totalProducts ?> matching products</span>
    </div>
  </form>
</div></div>
<div class="d-flex gap-2 flex-wrap mb-3" aria-label="Media summary for current filters">
  <span class="badge bg-secondary"><?= (int)$filterSummary['with_youtube'] ?> with YouTube URL</span>
  <span class="badge bg-secondary"><?= $totalProducts-(int)$filterSummary['with_youtube'] ?> without YouTube URL</span>
  <span class="badge bg-warning text-dark"><?= (int)$filterSummary['without_images'] ?> without images</span>
</div>
