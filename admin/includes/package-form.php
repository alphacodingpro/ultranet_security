<?php
$package = $input ?? [];
function packageInput(array $package, string $key, string $default = ''): string { return h((string)($package[$key] ?? $default)); }
?>
<form method="POST">
  <?php csrfField(); ?>
  <div class="row g-4">
    <div class="col-lg-8">
      <div class="admin-card mb-4"><div class="admin-card-header"><h5><i class="fa-solid fa-box-open me-2"></i>Package Details</h5></div><div class="admin-card-body">
        <div class="mb-3"><label class="form-label fw-600">Package Name <span class="text-danger">*</span></label><input class="form-control" name="name" maxlength="180" required value="<?= packageInput($package,'name') ?>" placeholder="e.g. 4 Camera Home Package"></div>
        <div class="mb-3"><label class="form-label fw-600">Short Description</label><textarea class="form-control" name="short_description" rows="2" maxlength="350" placeholder="Who this package is best for"><?= packageInput($package,'short_description') ?></textarea></div>
        <div class="row g-3 mb-3">
          <div class="col-md-4"><label class="form-label fw-600">System Type</label><select class="form-select" name="system_type"><?php foreach (['analog'=>'Analog','ip'=>'IP / PoE','wireless'=>'Wireless','custom'=>'Custom'] as $value=>$label): ?><option value="<?= $value ?>" <?= ($package['system_type'] ?? 'analog') === $value ? 'selected' : '' ?>><?= $label ?></option><?php endforeach; ?></select></div>
          <div class="col-md-4"><label class="form-label fw-600">Camera Count</label><input class="form-control" type="number" min="0" max="999" name="camera_count" value="<?= packageInput($package,'camera_count') ?>" placeholder="4"></div>
          <div class="col-md-4"><label class="form-label fw-600">Resolution</label><input class="form-control" name="resolution" maxlength="80" value="<?= packageInput($package,'resolution') ?>" placeholder="2MP Full HD"></div>
        </div>
        <div class="row g-3 mb-3">
          <div class="col-md-6"><label class="form-label fw-600">Recorder</label><input class="form-control" name="recorder" maxlength="140" value="<?= packageInput($package,'recorder') ?>" placeholder="4 Channel DVR"></div>
          <div class="col-md-6"><label class="form-label fw-600">Storage</label><input class="form-control" name="storage" maxlength="100" value="<?= packageInput($package,'storage') ?>" placeholder="1TB Surveillance HDD"></div>
        </div>
        <div class="mb-3"><label class="form-label fw-600">Package Items / Features</label><textarea class="form-control" name="features" rows="8" placeholder="4 × 2MP cameras&#10;1 × 4-channel DVR&#10;1 × 1TB surveillance HDD&#10;Mobile viewing setup"><?= packageInput($package,'features') ?></textarea><small class="text-muted">Har item alag line par likhein.</small></div>
        <div><label class="form-label fw-600">Warranty / Support</label><input class="form-control" name="warranty" maxlength="160" value="<?= packageInput($package,'warranty') ?>" placeholder="1 year equipment warranty + installation support"></div>
      </div></div>
      <div class="admin-card"><div class="admin-card-header"><h5><i class="fa-solid fa-magnifying-glass me-2"></i>SEO</h5></div><div class="admin-card-body">
        <div class="mb-3"><label class="form-label fw-600">Meta Title</label><input class="form-control" name="meta_title" maxlength="180" value="<?= packageInput($package,'meta_title') ?>" placeholder="Optional internal SEO title"></div>
        <div><label class="form-label fw-600">Meta Description</label><textarea class="form-control" name="meta_description" rows="3" maxlength="300"><?= packageInput($package,'meta_description') ?></textarea></div>
      </div></div>
    </div>
    <div class="col-lg-4">
      <div class="admin-card mb-4"><div class="admin-card-header"><h5><i class="fa-solid fa-tag me-2"></i>Price</h5></div><div class="admin-card-body">
        <div class="mb-3"><label class="form-label fw-600">Current Price (PKR)</label><input class="form-control" type="number" step="1" min="0" name="price" value="<?= packageInput($package,'price') ?>" placeholder="Leave blank for Request Quote"></div>
        <div class="mb-3"><label class="form-label fw-600">Old Price (PKR)</label><input class="form-control" type="number" step="1" min="0" name="old_price" value="<?= packageInput($package,'old_price') ?>"></div>
        <div><label class="form-label fw-600">Price Label</label><input class="form-control" name="price_note" maxlength="120" value="<?= packageInput($package,'price_note','Starting from') ?>"></div>
      </div></div>
      <div class="admin-card mb-4"><div class="admin-card-header"><h5><i class="fa-solid fa-sliders me-2"></i>Display</h5></div><div class="admin-card-body">
        <div class="mb-3"><label class="form-label fw-600">Badge</label><input class="form-control" name="badge" maxlength="60" value="<?= packageInput($package,'badge') ?>" placeholder="Most Popular"></div>
        <div class="mb-3"><label class="form-label fw-600">Sort Order</label><input class="form-control" type="number" min="0" max="9999" name="sort_order" value="<?= packageInput($package,'sort_order','0') ?>"><small class="text-muted">Lower number shows first.</small></div>
        <div class="mb-3"><label class="form-label fw-600">Status</label><select class="form-select" name="status"><option value="inactive" <?= ($package['status'] ?? 'inactive') === 'inactive' ? 'selected' : '' ?>>Inactive / Draft</option><option value="active" <?= ($package['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active / Public</option></select></div>
        <div class="form-check form-switch"><input class="form-check-input" type="checkbox" id="featured" name="featured" value="1" <?= !empty($package['featured']) ? 'checked' : '' ?>><label class="form-check-label fw-600" for="featured">Featured package</label></div>
      </div></div>
      <button class="btn-admin-primary w-100 justify-content-center py-3" type="submit"><i class="fa-solid fa-floppy-disk"></i><?= h($submitLabel ?? 'Save Package') ?></button>
    </div>
  </div>
</form>
