<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
requireAdminLogin();
if (!ensurePackageSchema()) { setFlash('error','Package table unavailable.'); header('Location: '.ADMIN_URL.'/packages.php'); exit; }
$id=(int)($_GET['id'] ?? 0); $db=getDB(); $st=$db->prepare('SELECT * FROM packages WHERE id=?'); $st->execute([$id]); $input=$st->fetch();
if(!$input){ setFlash('error','Package not found.'); header('Location: '.ADMIN_URL.'/packages.php'); exit; }
$errors=[];
if ($_SERVER['REQUEST_METHOD']==='POST') {
  verifyCsrf();
  foreach (['name','badge','short_description','system_type','camera_count','resolution','recorder','storage','features','price','old_price','price_note','warranty','sort_order','status','meta_title','meta_description'] as $key) $input[$key]=trim($_POST[$key] ?? '');
  $input['featured']=isset($_POST['featured'])?1:0;
  if($input['name']==='') $errors[]='Package name is required.';
  if(!in_array($input['system_type'],['analog','ip','wireless','custom'],true)) $errors[]='Invalid system type.';
  if(!in_array($input['status'],['active','inactive'],true)) $errors[]='Invalid status.';
  foreach(['price','old_price'] as $key) if($input[$key]!=='' && (!is_numeric($input[$key]) || (float)$input[$key]<0)) $errors[]='Valid '.str_replace('_',' ',$key).' is required.';
  if(!$errors){
    $slug=slugify($input['name']); $check=$db->prepare('SELECT id FROM packages WHERE slug=? AND id!=?'); $check->execute([$slug,$id]); if($check->fetch()) $slug.='-'.$id;
    $st=$db->prepare('UPDATE packages SET name=?,slug=?,badge=?,short_description=?,system_type=?,camera_count=?,resolution=?,recorder=?,storage=?,features=?,price=?,old_price=?,price_note=?,warranty=?,featured=?,sort_order=?,status=?,meta_title=?,meta_description=? WHERE id=?');
    $st->execute([$input['name'],$slug,$input['badge']?:null,$input['short_description']?:null,$input['system_type'],$input['camera_count']!==''?(int)$input['camera_count']:null,$input['resolution']?:null,$input['recorder']?:null,$input['storage']?:null,$input['features']?:null,$input['price']!==''?(float)$input['price']:null,$input['old_price']!==''?(float)$input['old_price']:null,$input['price_note']?:null,$input['warranty']?:null,$input['featured'],(int)$input['sort_order'],$input['status'],$input['meta_title']?:null,$input['meta_description']?:null,$id]);
    setFlash('success','Package updated successfully.'); header('Location: '.ADMIN_URL.'/packages.php'); exit;
  }
}
$adminPageTitle='Edit Package'; include __DIR__.'/includes/header.php';
?>
<div class="admin-page-header"><h1 class="admin-page-title"><i class="fa-solid fa-pen me-2"></i>Edit CCTV Package</h1><a href="<?= ADMIN_URL ?>/packages.php" class="btn-admin-outline"><i class="fa-solid fa-arrow-left"></i>Back</a></div>
<?php if($errors): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach($errors as $error): ?><li><?= h($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<?php $submitLabel='Update Package'; include __DIR__.'/includes/package-form.php'; include __DIR__.'/includes/footer.php'; ?>
