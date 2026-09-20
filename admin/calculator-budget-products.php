<?php
require_once dirname(__DIR__).'/config/config.php';
require_once dirname(__DIR__).'/includes/functions.php';
requireAdminLogin();
$db = getDB();
$errors = [];
$id = max(0, (int)($_GET['id'] ?? 0));
$product = $id ? getProductById($id) : null;
$profile = $product ? (json_decode((string)getSetting('budget_product_'.$id,'{}'),true) ?: []) : [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    if (!$product) $errors[] = 'Select an existing product.';
    $profile = ['enabled'=>isset($_POST['enabled']), 'family'=>trim($_POST['family'] ?? ''), 'environment'=>$_POST['environment'] ?? 'indoor', 'mp'=>(int)($_POST['mp'] ?? 0), 'max_hdd_gb'=>(int)($_POST['max_hdd_gb'] ?? 0), 'mbps'=>(int)($_POST['mbps'] ?? 0)];
    if (!preg_match('/^[a-z0-9-]{1,32}$/', $profile['family'])) $errors[] = 'Compatibility group: use 1–32 lowercase letters, numbers or hyphens.';
    if (!in_array($profile['environment'], ['indoor','outdoor','both'],true)) $errors[] = 'Invalid environment.';
    if ($product && $profile['enabled']) {
        $type = $product['product_type'];
        if (!in_array($type,['camera','nvr','hdd','poe_switch','cable','other'],true)) $errors[]='Only IP/PoE package components are supported.';
        if ($type === 'camera' && (!in_array($profile['mp'],[2,4,5,8],true) || (float)$product['camera_watts'] <= 0)) $errors[]='Set camera resolution here and actual camera watts in Edit Product.';
        if ($type === 'nvr' && ($profile['mp'] < 2 || $profile['mp'] > 64 || $profile['max_hdd_gb'] <= 0 || $profile['max_hdd_gb'] > 100000 || $profile['mbps'] <= 0 || $profile['mbps'] > 10000 || (int)$product['channels'] <= 0)) $errors[]='Set NVR channels in Edit Product, and resolution, single-drive capacity and incoming bandwidth here.';
        if ($type === 'hdd' && (int)$product['storage_gb'] <= 0) $errors[]='Set HDD capacity in Edit Product.';
        if ($type === 'poe_switch' && ((int)$product['poe_ports'] <= 0 || (float)$product['poe_budget_watts'] <= 0)) $errors[]='Set PoE ports and power budget in Edit Product.';
    }
    if (!$errors) {
        setSetting('budget_product_'.$id, json_encode($profile));
        setFlash('success','Budget package eligibility saved.');
        header('Location: '.ADMIN_URL.'/calculator-budget-products.php?id='.$id); exit;
    }
}
$q = trim($_GET['q'] ?? '');
$pageNumber = max(1,(int)($_GET['page'] ?? 1));
$where = "product_type IN ('camera','nvr','hdd','poe_switch','cable','other') AND (name LIKE ? OR sku LIKE ?)";
$params = ['%'.$q.'%','%'.$q.'%'];
$st=$db->prepare('SELECT COUNT(*) FROM products WHERE '.$where);$st->execute($params);
$pages=max(1,(int)ceil($st->fetchColumn()/25)); $pageNumber=min($pages,$pageNumber); $offset=($pageNumber-1)*25;
$st=$db->prepare('SELECT id,name,product_type,price,stock_status FROM products WHERE '.$where.' ORDER BY id DESC LIMIT 25 OFFSET '.$offset);$st->execute($params);$rows=$st->fetchAll();
$adminPageTitle='Budget Package Products'; include __DIR__.'/includes/header.php';
?>
<div class="admin-page-header"><h1 class="admin-page-title">Budget Package Products</h1><a class="btn-admin-outline" href="<?= ADMIN_URL ?>/calculator-settings.php">Calculator Settings</a></div>
<?php showFlash(); ?>
<div class="admin-card mb-4"><div class="admin-card-body">
<p>Approve existing catalog products for automatic <strong>IP/PoE equipment packages</strong>. A package requires a camera, NVR, surveillance HDD, PoE switch, cable and one complete installation accessory kit per camera (Product Type: Other). Set real prices, specifications and stock in Edit Product.</p>
<p>Use the same compatibility group (for example <code>hikvision-ip</code>) only for equipment you have verified works together: H.265 cameras/NVR, standard PoE power, supported SATA surveillance drive, suitable network cable and connectors/mounting kit. All approved components in a group must be interchangeable. Outdoor approval must include the camera's weather rating and installation requirements. Labour is quoted separately.</p>
<p class="mb-0">Default sizing: Home up to 4 cameras, 7 days, 20m cable per camera; Office up to 8 cameras, 14 days, 30m per camera. The selector chooses the largest affordable count, then highest supported resolution. Lower budgets may cover fewer cameras; the client sees this clearly.</p>
</div></div>
<?php foreach ($errors as $error): ?><div class="alert alert-danger"><?= h($error) ?></div><?php endforeach; ?>
<?php if ($product): ?>
<div class="admin-card mb-4"><div class="admin-card-body"><h4><?= h($product['name']) ?></h4>
<a href="<?= ADMIN_URL ?>/product-edit.php?id=<?= $id ?>">Edit price, product type and specifications</a>
<form method="POST" class="mt-3"><?php csrfField(); ?>
<label class="form-label" for="family">Verified compatibility group</label><input id="family" name="family" pattern="[a-z0-9-]{1,32}" maxlength="32" required class="form-control mb-3" value="<?= h($profile['family'] ?? '') ?>" placeholder="hikvision-ip">
<?php if ($product['product_type'] === 'camera'): ?>
<label class="form-label" for="environment">Camera environment</label><select class="form-select mb-3" name="environment" id="environment"><?php foreach (['indoor','outdoor','both'] as $env): ?><option value="<?= $env ?>" <?= ($profile['environment'] ?? '') === $env ? 'selected' : '' ?>><?= ucfirst($env) ?></option><?php endforeach; ?></select>
<label class="form-label" for="mp">Camera resolution (MP)</label><select name="mp" id="mp" class="form-select mb-3"><?php foreach ([2,4,5,8] as $mp): ?><option value="<?= $mp ?>" <?= ($profile['mp'] ?? 0) === $mp ? 'selected' : '' ?>><?= $mp ?> MP</option><?php endforeach; ?></select>
<?php elseif ($product['product_type'] === 'nvr'): ?>
<label class="form-label" for="mp">Maximum supported resolution per channel (MP)</label><input class="form-control mb-3" type="number" name="mp" id="mp" min="2" max="64" required value="<?= (int)($profile['mp'] ?? 0) ?>">
<label class="form-label" for="max_hdd_gb">Maximum supported single HDD capacity (GB)</label><input class="form-control mb-3" type="number" name="max_hdd_gb" id="max_hdd_gb" min="1" max="100000" required value="<?= (int)($profile['max_hdd_gb'] ?? 0) ?>">
<label class="form-label" for="mbps">Incoming bandwidth (Mbps)</label><input class="form-control mb-3" type="number" name="mbps" id="mbps" min="1" max="10000" required value="<?= (int)($profile['mbps'] ?? 0) ?>">
<?php endif; ?>
<div class="form-check mb-3"><input class="form-check-input" type="checkbox" id="enabled" name="enabled" <?= !empty($profile['enabled']) ? 'checked' : '' ?>><label class="form-check-label" for="enabled">Verified compatible with this group — allow automatic selection</label></div>
<button class="btn-admin-primary">Save eligibility</button></form></div></div>
<?php endif; ?>
<form class="d-flex gap-2 mb-3" method="GET"><input class="form-control" name="q" aria-label="Search catalog" placeholder="Search name or SKU" value="<?= h($q) ?>"><button class="btn-admin-primary">Search</button></form>
<div class="admin-card"><div class="table-responsive"><table class="admin-table"><thead><tr><th>Product</th><th>Type</th><th>Budget selection</th><th></th></tr></thead><tbody>
<?php foreach ($rows as $row): $p=json_decode((string)getSetting('budget_product_'.$row['id'],'{}'),true) ?: []; ?>
<tr><td><?= h($row['name']) ?></td><td><?= h($row['product_type']) ?></td><td><?= !empty($p['enabled']) ? 'Approved: '.h($p['family']) : 'Not enabled' ?></td><td><a href="?id=<?= $row['id'] ?>">Configure</a></td></tr>
<?php endforeach; ?></tbody></table></div></div>
<nav class="d-flex gap-3 mt-3" aria-label="Catalog pages"><?php if ($pageNumber>1): ?><a href="?<?= h(http_build_query(['q'=>$q,'page'=>$pageNumber-1])) ?>">Previous</a><?php endif; ?><span>Page <?= $pageNumber ?> of <?= $pages ?></span><?php if ($pageNumber<$pages): ?><a href="?<?= h(http_build_query(['q'=>$q,'page'=>$pageNumber+1])) ?>">Next</a><?php endif; ?></nav>
<?php include __DIR__.'/includes/footer.php'; ?>
