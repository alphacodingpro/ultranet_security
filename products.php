<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';

$categorySlug=trim($_GET['category']??''); $seriesSlug=trim($_GET['series']??'');
$brand=trim($_GET['brand']??''); $search=trim($_GET['q']??'');
$currentPage=max(1,(int)($_GET['page']??1)); $perPage=24;
$rootCategories=getRootCategories();
$categoryObj=$categorySlug?getCategoryBySlug($categorySlug):null;
$seriesObj=$seriesSlug?getCategoryBySlug($seriesSlug):null;

if($categorySlug&&!$categoryObj){header('HTTP/1.0 404 Not Found');include __DIR__.'/includes/404.php';exit;}
if($categoryObj&&!empty($categoryObj['parent_id'])){
    $seriesObj=$categoryObj; $categoryObj=getCategoryById((int)$seriesObj['parent_id']);
    $categorySlug=$categoryObj['slug']??''; $seriesSlug=$seriesObj['slug'];
    if($brand==='')$brand=$seriesObj['brand']??'';
}
if($seriesObj&&(!$categoryObj||(int)($seriesObj['parent_id']??0)!==(int)$categoryObj['id'])){
    header('HTTP/1.0 404 Not Found');include __DIR__.'/includes/404.php';exit;
}

$childCategories=$categoryObj?getChildCategories((int)$categoryObj['id']):[];
$availableBrands=$categoryObj?getCategoryBrands((int)$categoryObj['id']):[];
$brandSeries=($categoryObj&&$brand!=='')?getChildCategories((int)$categoryObj['id'],$brand):[];
$hasHierarchy=!empty($childCategories);
$showProducts=$search!==''||$seriesObj!==null||($categoryObj!==null&&!$hasHierarchy);
$productOpts=[];
if($seriesObj)$productOpts['category_id']=(int)$seriesObj['id'];
elseif($categoryObj&&!$hasHierarchy)$productOpts['category_id']=(int)$categoryObj['id'];
if($brand!==''&&$showProducts)$productOpts['brand']=$brand;
if($search!=='')$productOpts['search']=$search;
$totalProducts=$showProducts?countProducts($productOpts):0;
$pagination=paginate($totalProducts,$perPage,$currentPage); $currentPage=$pagination['current'];
$products=[];
if($showProducts){$productOpts['limit']=$perPage;$productOpts['offset']=$pagination['offset'];$products=getProducts($productOpts);}

$queryBase=array_filter(['category'=>$categorySlug,'brand'=>$brand,'series'=>$seriesSlug,'q'=>$search],static fn($v):bool=>$v!=='');
$pageUrl=static fn(int $page):string=>SITE_URL.'/products.php?'.http_build_query(array_merge($queryBase,['page'=>$page]));
$trail=[['name'=>'Home','url'=>SITE_URL.'/'],['name'=>'Products','url'=>SITE_URL.'/products.php']];
if($categoryObj)$trail[]=['name'=>$categoryObj['name'],'url'=>SITE_URL.'/products.php?category='.urlencode($categoryObj['slug'])];
if($brand!=='')$trail[]=['name'=>$brand,'url'=>SITE_URL.'/products.php?category='.urlencode($categorySlug).'&brand='.urlencode($brand)];
if($seriesObj)$trail[]=['name'=>$seriesObj['name'],'url'=>SITE_URL.'/products.php?'.http_build_query($queryBase)];
$metaRobots=($brand!==''||$search!=='')?'noindex, follow':'index, follow'; $extraSchema=[schemaBreadcrumb($trail)];
if($seriesObj){$pageTitle=seoTitle($seriesObj['name'].' Karachi','UltraNet Security',60);$metaDesc=seoDescription('Browse '.$seriesObj['name'].' products by '.$brand.' in Karachi.',155);}
elseif($categoryObj&&$brand!==''){$pageTitle=seoTitle($brand.' '.$categoryObj['name'],'UltraNet Security',60);$metaDesc=seoDescription('Choose a '.$brand.' product series from UltraNet Security Karachi.',155);}
elseif($categoryObj){$pageTitle=seoTitle($categoryObj['name'].' Karachi','UltraNet Security',60);$metaDesc=seoDescription('Choose a brand and browse '.$categoryObj['name'].' from UltraNet Security Karachi.',155);}
elseif($search!==''){$pageTitle=seoTitle('Search: '.$search,'UltraNet Security',60);$metaDesc=seoDescription('Search results for '.$search.' on UltraNet Security Karachi.',155);}
else{$pageTitle='CCTV Products Karachi | UltraNet Security';$metaDesc='Browse CCTV cameras, NVR, DVR and accessories by category, brand and product series.';}
$canonicalSlug='/products.php'.($queryBase?'?'.http_build_query($queryBase):'');
include __DIR__.'/includes/header.php';
?>
<section class="page-hero"><div class="container">
<nav aria-label="breadcrumb"><ol class="breadcrumb mb-2"><?php foreach($trail as $i=>$item): ?><li class="breadcrumb-item <?= $i===count($trail)-1?'active':'' ?>"><?php if($i===count($trail)-1): ?><?= h($item['name']) ?><?php else: ?><a href="<?= h($item['url']) ?>"><?= h($item['name']) ?></a><?php endif; ?></li><?php endforeach; ?></ol></nav>
<h1 class="page-hero-title"><?php if($seriesObj)echo h($seriesObj['name']);elseif($brand!=='')echo h($brand.' '.($categoryObj['name']??'Products'));elseif($categoryObj)echo h($categoryObj['name']);elseif($search!=='')echo 'Search: <em style="color:var(--accent)">'.h($search).'</em>';else echo 'Choose Product Category'; ?></h1>
<p class="page-hero-sub"><?= $showProducts?$totalProducts.' products found':'Select an option below to continue' ?></p>
</div></section>

<div class="container py-5"><div class="row g-4">
<aside class="col-lg-3"><div class="filter-box">
<div class="filter-section"><h6 class="filter-heading">Search</h6><form action="<?= SITE_URL ?>/products.php" method="GET"><div class="input-group"><input type="text" name="q" class="form-control form-control-sm" placeholder="Search products…" value="<?= h($search) ?>"><button class="btn btn-sm" style="background:var(--accent);color:#fff"><i class="fa-solid fa-magnifying-glass"></i></button></div></form></div>
<div class="filter-section"><h6 class="filter-heading">Main Categories</h6><ul class="filter-list"><?php foreach($rootCategories as $cat): ?><li><a href="<?= SITE_URL ?>/products.php?category=<?= urlencode($cat['slug']) ?>" class="<?= $categoryObj&&(int)$categoryObj['id']===(int)$cat['id']?'active':'' ?>"><i class="<?= h($cat['icon']) ?> me-2"></i><?= h($cat['name']) ?></a></li><?php endforeach; ?></ul></div>
<?php if($categoryObj&&$availableBrands): ?><div class="filter-section"><h6 class="filter-heading">Brands</h6><ul class="filter-list"><?php foreach($availableBrands as $itemBrand): ?><li><a href="<?= SITE_URL ?>/products.php?category=<?= urlencode($categorySlug) ?>&brand=<?= urlencode($itemBrand) ?>" class="<?= $brand===$itemBrand?'active':'' ?>"><?= h($itemBrand) ?></a></li><?php endforeach; ?></ul></div><?php endif; ?>
<?php if($brandSeries): ?><div class="filter-section"><h6 class="filter-heading"><?= h($brand) ?> Series</h6><ul class="filter-list"><?php foreach($brandSeries as $series): ?><li><a href="<?= SITE_URL ?>/products.php?<?= http_build_query(['category'=>$categorySlug,'brand'=>$brand,'series'=>$series['slug']]) ?>" class="<?= $seriesObj&&(int)$seriesObj['id']===(int)$series['id']?'active':'' ?>"><?= h($series['name']) ?></a></li><?php endforeach; ?></ul></div><?php endif; ?>
<?php if($categorySlug||$brand||$seriesSlug||$search): ?><a href="<?= SITE_URL ?>/products.php" class="btn btn-sm w-100 mt-2" style="border:1px solid var(--border);color:var(--muted)"><i class="fa-solid fa-xmark me-1"></i>Start Again</a><?php endif; ?>
</div></aside>

<main class="col-lg-9">
<?php if(!$categoryObj&&$search===''): ?><div class="row g-3"><?php foreach($rootCategories as $cat): ?><div class="col-6 col-md-4"><a href="<?= SITE_URL ?>/products.php?category=<?= urlencode($cat['slug']) ?>" class="cat-card text-decoration-none h-100"><div class="cat-icon"><i class="<?= h($cat['icon']) ?>"></i></div><div class="cat-name"><?= h($cat['name']) ?></div><?php if($cat['description']): ?><div class="cat-desc"><?= h($cat['description']) ?></div><?php endif; ?></a></div><?php endforeach; ?></div>
<?php elseif($categoryObj&&$hasHierarchy&&$brand===''): ?><h3 class="mb-4">Select a brand</h3><div class="row g-3"><?php foreach($availableBrands as $itemBrand): ?><div class="col-6 col-md-4"><a class="cat-card text-decoration-none h-100" href="<?= SITE_URL ?>/products.php?category=<?= urlencode($categorySlug) ?>&brand=<?= urlencode($itemBrand) ?>"><div class="cat-icon"><i class="fa-solid fa-tag"></i></div><div class="cat-name"><?= h($itemBrand) ?></div><div class="cat-desc">View <?= h($itemBrand) ?> categories</div></a></div><?php endforeach; ?></div>
<?php elseif($categoryObj&&$hasHierarchy&&$brand!==''&&!$seriesObj): ?><h3 class="mb-4">Select <?= h($brand) ?> category</h3><div class="row g-3"><?php foreach($brandSeries as $series): ?><div class="col-6 col-md-4"><a class="cat-card text-decoration-none h-100" href="<?= SITE_URL ?>/products.php?<?= http_build_query(['category'=>$categorySlug,'brand'=>$brand,'series'=>$series['slug']]) ?>"><div class="cat-icon"><i class="<?= h($series['icon']) ?>"></i></div><div class="cat-name"><?= h($series['name']) ?></div><?php if($series['description']): ?><div class="cat-desc"><?= h($series['description']) ?></div><?php endif; ?></a></div><?php endforeach; ?></div>
<?php elseif($products): ?><div class="row g-3 g-md-4" id="productGrid"><?php foreach($products as $p): ?><div class="col-6 col-md-4"><?php include __DIR__.'/includes/product-card.php'; ?></div><?php endforeach; ?></div>
<?php if($pagination['total_pages']>1): ?><nav class="product-pagination mt-5" aria-label="Product pages"><a class="product-page-link <?= $currentPage<=1?'disabled':'' ?>" href="<?= $currentPage>1?h($pageUrl($currentPage-1)):'#' ?>"><i class="fa-solid fa-chevron-left"></i></a><?php for($page=max(1,$currentPage-2);$page<=min($pagination['total_pages'],$currentPage+2);$page++): ?><a class="product-page-link <?= $page===$currentPage?'active':'' ?>" href="<?= h($pageUrl($page)) ?>"><?= $page ?></a><?php endfor; ?><a class="product-page-link <?= $currentPage>=$pagination['total_pages']?'disabled':'' ?>" href="<?= $currentPage<$pagination['total_pages']?h($pageUrl($currentPage+1)):'#' ?>"><i class="fa-solid fa-chevron-right"></i></a></nav><?php endif; ?>
<?php else: ?><div class="text-center py-5"><i class="fa-solid fa-camera-slash fa-3x mb-3" style="color:var(--border)"></i><h4>No products found</h4><p class="text-muted">Try another category, brand or search term.</p></div><?php endif; ?>
</main></div></div>
<?php include __DIR__.'/includes/footer.php'; ?>
