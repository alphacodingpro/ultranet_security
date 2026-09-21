<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';

$categorySlug=queryText('category'); $seriesSlug=queryText('series');
$brand=queryText('brand'); $search=queryText('q'); $direct=queryText('direct')==='1';
$pageInput=queryText('page');
$notFound=static function (): void { http_response_code(404); include __DIR__.'/includes/404.php'; exit; };
if ($pageInput!=='' && !preg_match('/^[1-9][0-9]{0,8}$/D',$pageInput)) $notFound();
$currentPage=$pageInput===''?1:(int)$pageInput; $perPage=24;
$rootCategories=getRootCategories();
$categoryObj=$categorySlug!==''?getCategoryBySlug($categorySlug):null;
$seriesObj=$seriesSlug!==''?getCategoryBySlug($seriesSlug):null;
if (($categorySlug!==''&&!$categoryObj)||($seriesSlug!==''&&!$seriesObj)) $notFound();
if($categoryObj&&!empty($categoryObj['parent_id'])){
    $child=$categoryObj; $parent=getCategoryById((int)$child['parent_id']);
    if (!$parent) $notFound();
    $params=['category'=>$parent['slug'],'brand'=>$brand!==''?$brand:($child['brand']??''),'series'=>$child['slug'],'q'=>$search];
    header('Location: '.SITE_URL.catalogPath($params,$currentPage),true,301); exit;
}
if($seriesObj){
    if(!$categoryObj||(int)($seriesObj['parent_id']??0)!==(int)$categoryObj['id']) $notFound();
    $seriesBrand=trim((string)($seriesObj['brand']??''));
    if($seriesBrand!==''){
        if($brand!==''&&strcasecmp($brand,$seriesBrand)!==0) $notFound();
        $brand=$seriesBrand;
    }
    $direct=false;
}
$childCategories=$categoryObj?getChildCategories((int)$categoryObj['id']):[];
$availableBrands=$categoryObj?getCategoryBrands((int)$categoryObj['id']):[];
if($categoryObj&&$brand!==''&&$search===''){
    $matched=false;
    foreach($availableBrands as $knownBrand){if(strcasecmp($brand,$knownBrand)===0){$brand=$knownBrand;$matched=true;break;}}
    if(!$matched) $notFound();
}
$brandSeries=($categoryObj&&$brand!=='')?getChildCategories((int)$categoryObj['id'],$brand):[];
$hasHierarchy=!empty($childCategories);
$showProducts=$search!==''||$seriesObj!==null||$direct||(!$categoryObj&&$brand!=='')||($categoryObj&&(!$hasHierarchy||($brand!==''&&!$brandSeries)));
$productOpts=[];
if($seriesObj)$productOpts['category_id']=(int)$seriesObj['id'];
elseif($categoryObj)$productOpts['category_id']=(int)$categoryObj['id'];
if($brand!=='')$productOpts['brand']=$brand;
if($search!=='')$productOpts['search']=$search;
$totalProducts=$showProducts?countProducts($productOpts):0;
$pagination=paginate($totalProducts,$perPage,$currentPage);
if($currentPage>$pagination['total_pages'] && $currentPage>1) $notFound();
$products=[];
if($showProducts){$productOpts['limit']=$perPage;$productOpts['offset']=$pagination['offset'];$products=getProducts($productOpts);}
$directOpts=['category_id'=>(int)($categoryObj['id']??0)];
if($brand!=='')$directOpts['brand']=$brand;
$directCount=($categoryObj&&$hasHierarchy&&!$showProducts)?countProducts($directOpts):0;

$queryBase=array_filter(['category'=>$categorySlug,'brand'=>$brand,'series'=>$seriesSlug,'direct'=>$direct?'1':'','q'=>$search],static fn($v):bool=>$v!=='');
$pageUrl=static fn(int $page):string=>SITE_URL.catalogPath($queryBase,$page);
$trail=[['name'=>'Home','url'=>SITE_URL.'/'],['name'=>'Products','url'=>SITE_URL.'/products.php']];
if($categoryObj)$trail[]=['name'=>$categoryObj['name'],'url'=>SITE_URL.'/products.php?category='.urlencode($categoryObj['slug'])];
if($brand!=='')$trail[]=['name'=>$brand,'url'=>SITE_URL.'/products.php?category='.urlencode($categorySlug).'&brand='.urlencode($brand)];
if($seriesObj)$trail[]=['name'=>$seriesObj['name'],'url'=>SITE_URL.'/products.php?'.http_build_query($queryBase)];
$categoryEmpty=false;
if($categoryObj&&!$showProducts){
    $categoryIds=array_merge([(int)$categoryObj['id']],array_map('intval',array_column($brand!==''?$brandSeries:$childCategories,'id')));
    $emptySql="SELECT COUNT(*) FROM products WHERE status='active' AND category_id IN (".implode(',',array_fill(0,count($categoryIds),'?')).")";
    if($brand!==''){$emptySql.=' AND brand=?';$categoryIds[]=$brand;}
    $emptySt=getDB()->prepare($emptySql);$emptySt->execute($categoryIds);$categoryEmpty=(int)$emptySt->fetchColumn()===0;
}
$filtered=$search!==''||$direct||($brand!==''&&(!$categoryObj||!$hasHierarchy))||($seriesObj&&empty($seriesObj['brand'])&&$brand!=='');
$metaRobots=($categoryEmpty||$filtered||($showProducts&&$totalProducts===0))?'noindex, follow':'index, follow'; $extraSchema=[schemaBreadcrumb($trail)];
if($seriesObj){$pageTitle=seoTitle($seriesObj['name'].' Karachi','UltraNet Security',60);$metaDesc=seoDescription('Browse '.$seriesObj['name'].' products'.($brand!==''?' by '.$brand:'').' in Karachi.',155);}
elseif($categoryObj&&$brand!==''){$pageTitle=seoTitle($brand.' '.$categoryObj['name'],'UltraNet Security',60);$metaDesc=seoDescription('Choose a '.$brand.' product series from UltraNet Security Karachi.',155);}
elseif($categoryObj){$pageTitle=seoTitle($categoryObj['name'].' Karachi','UltraNet Security',60);$metaDesc=seoDescription('Choose a brand and browse '.$categoryObj['name'].' from UltraNet Security Karachi.',155);}
elseif($search!==''){$pageTitle=seoTitle('Search: '.$search,'UltraNet Security',60);$metaDesc=seoDescription('Search results for '.$search.' on UltraNet Security Karachi.',155);}
else{$pageTitle='CCTV Products Karachi | UltraNet Security';$metaDesc='Browse CCTV cameras, NVR, DVR and accessories by category, brand and product series.';}
$canonicalSlug=catalogPath($queryBase,$currentPage);
if($currentPage>1){$pageTitle=seoTitle(($seriesObj['name']??$categoryObj['name']??'Products'),'Page '.$currentPage.' | UltraNet Security',70);$metaDesc=seoDescription('Page '.$currentPage.'. '.$metaDesc,170);}
include __DIR__.'/includes/header.php';
?>
<section class="page-hero"><div class="container">
<nav aria-label="breadcrumb"><ol class="breadcrumb mb-2"><?php foreach($trail as $i=>$item): ?><li class="breadcrumb-item <?= $i===count($trail)-1?'active':'' ?>"><?php if($i===count($trail)-1): ?><?= h($item['name']) ?><?php else: ?><a href="<?= h($item['url']) ?>"><?= h($item['name']) ?></a><?php endif; ?></li><?php endforeach; ?></ol></nav>
<h1 class="page-hero-title"><?php if($seriesObj)echo h($seriesObj['name']);elseif($brand!=='')echo h($brand.' '.($categoryObj['name']??'Products'));elseif($categoryObj)echo h($categoryObj['name']);elseif($search!=='')echo 'Search: <em style="color:var(--accent)">'.h($search).'</em>';else echo 'Choose Product Category'; ?></h1>
<p class="page-hero-sub"><?= $showProducts?$totalProducts.' products found':'Select an option below to continue' ?></p>
</div></section>

<div class="container py-5"><div class="row g-4">
<aside class="col-lg-3"><div class="filter-box">
<div class="filter-section"><h6 class="filter-heading">Search</h6><form action="<?= SITE_URL ?>/products.php" method="GET"><div class="input-group"><input type="text" name="q" class="form-control form-control-sm" placeholder="Search products…" value="<?= h($search) ?>"><button aria-label="Search products" class="btn btn-sm" style="background:var(--accent);color:#fff"><i class="fa-solid fa-magnifying-glass"></i></button></div></form></div>
<div class="filter-section"><h6 class="filter-heading">Main Categories</h6><ul class="filter-list"><?php foreach($rootCategories as $cat): ?><li><a href="<?= SITE_URL ?>/products.php?category=<?= urlencode($cat['slug']) ?>" class="<?= $categoryObj&&(int)$categoryObj['id']===(int)$cat['id']?'active':'' ?>"><i class="<?= h($cat['icon']) ?> me-2"></i><?= h($cat['name']) ?></a></li><?php endforeach; ?></ul></div>
<?php if($categoryObj&&$availableBrands): ?><div class="filter-section"><h6 class="filter-heading">Brands</h6><ul class="filter-list"><?php foreach($availableBrands as $itemBrand): ?><li><a href="<?= SITE_URL ?>/products.php?category=<?= urlencode($categorySlug) ?>&brand=<?= urlencode($itemBrand) ?>" class="<?= $brand===$itemBrand?'active':'' ?>"><?= h($itemBrand) ?></a></li><?php endforeach; ?></ul></div><?php endif; ?>
<?php if($brandSeries): ?><div class="filter-section"><h6 class="filter-heading"><?= h($brand) ?> Series</h6><ul class="filter-list"><?php foreach($brandSeries as $series): ?><li><a href="<?= SITE_URL ?>/products.php?<?= h(http_build_query(['category'=>$categorySlug,'brand'=>$brand,'series'=>$series['slug']])) ?>" class="<?= $seriesObj&&(int)$seriesObj['id']===(int)$series['id']?'active':'' ?>"><?= h($series['name']) ?></a></li><?php endforeach; ?></ul></div><?php endif; ?>
<?php if($categorySlug||$brand||$seriesSlug||$search): ?><a href="<?= SITE_URL ?>/products.php" class="btn btn-sm w-100 mt-2" style="border:1px solid var(--border);color:var(--muted)"><i class="fa-solid fa-xmark me-1"></i>Start Again</a><?php endif; ?>
</div></aside>

<div class="col-lg-9">
<?php if($directCount>0): ?><p><a class="btn-red d-inline-flex" href="<?= h(SITE_URL.catalogPath(['category'=>$categorySlug,'brand'=>$brand,'direct'=>'1'])) ?>">Other <?= h($brand?:$categoryObj['name']) ?> products (<?= $directCount ?>)</a></p><?php endif; ?>
<?php if(!$showProducts&&!$categoryObj): ?><div class="row g-3"><?php foreach($rootCategories as $cat): ?><div class="col-6 col-md-4"><a href="<?= SITE_URL ?>/products.php?category=<?= urlencode($cat['slug']) ?>" class="cat-card text-decoration-none h-100"><div class="cat-icon"><i class="<?= h($cat['icon']) ?>"></i></div><div class="cat-name"><?= h($cat['name']) ?></div><?php if($cat['description']): ?><div class="cat-desc"><?= h($cat['description']) ?></div><?php endif; ?></a></div><?php endforeach; ?></div>
<?php elseif(!$showProducts&&$categoryObj&&$hasHierarchy&&$brand===''): ?><h3 class="mb-4">Select a brand</h3><div class="row g-3"><?php foreach($availableBrands as $itemBrand): ?><div class="col-6 col-md-4"><a class="cat-card text-decoration-none h-100" href="<?= SITE_URL ?>/products.php?category=<?= urlencode($categorySlug) ?>&brand=<?= urlencode($itemBrand) ?>"><div class="cat-icon"><i class="fa-solid fa-tag"></i></div><div class="cat-name"><?= h($itemBrand) ?></div><div class="cat-desc">View <?= h($itemBrand) ?> categories</div></a></div><?php endforeach; ?></div>
<?php elseif(!$showProducts&&$categoryObj&&$hasHierarchy&&$brand!==''&&!$seriesObj): ?><h3 class="mb-4">Select <?= h($brand) ?> category</h3><div class="row g-3"><?php foreach($brandSeries as $series): ?><div class="col-6 col-md-4"><a class="cat-card text-decoration-none h-100" href="<?= SITE_URL ?>/products.php?<?= h(http_build_query(['category'=>$categorySlug,'brand'=>$brand,'series'=>$series['slug']])) ?>"><div class="cat-icon"><i class="<?= h($series['icon']) ?>"></i></div><div class="cat-name"><?= h($series['name']) ?></div><?php if($series['description']): ?><div class="cat-desc"><?= h($series['description']) ?></div><?php endif; ?></a></div><?php endforeach; ?></div>
<?php elseif($products): ?><div class="row g-3 g-md-4" id="productGrid"><?php foreach($products as $p): ?><div class="col-6 col-md-4"><?php include __DIR__.'/includes/product-card.php'; ?></div><?php endforeach; ?></div>
<?php if($pagination['total_pages']>1): ?><nav class="product-pagination mt-5" aria-label="Product pages"><a aria-label="Previous page" class="product-page-link <?= $currentPage<=1?'disabled':'' ?>" href="<?= $currentPage>1?h($pageUrl($currentPage-1)):'#' ?>"><i class="fa-solid fa-chevron-left"></i></a><?php for($page=max(1,$currentPage-2);$page<=min($pagination['total_pages'],$currentPage+2);$page++): ?><a class="product-page-link <?= $page===$currentPage?'active':'' ?>" <?= $page===$currentPage?'aria-current="page"':'' ?> href="<?= h($pageUrl($page)) ?>"><?= $page ?></a><?php endfor; ?><a aria-label="Next page" class="product-page-link <?= $currentPage>=$pagination['total_pages']?'disabled':'' ?>" href="<?= $currentPage<$pagination['total_pages']?h($pageUrl($currentPage+1)):'#' ?>"><i class="fa-solid fa-chevron-right"></i></a></nav><?php endif; ?>
<?php else: ?><div class="text-center py-5"><i class="fa-solid fa-camera-slash fa-3x mb-3" style="color:var(--border)"></i><h4>No products found</h4><p class="text-muted">Try another category, brand or search term.</p></div><?php endif; ?>
</div></div></div>
<?php include __DIR__.'/includes/footer.php'; ?>
