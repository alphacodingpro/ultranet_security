<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once dirname(__DIR__).'/includes/functions.php';
$db=getDB();
$db->exec(file_get_contents(dirname(__DIR__).'/database/schema.sql'));
$db->exec("INSERT INTO categories(id,name,slug,parent_id,brand) VALUES (100,'Test Cameras','test-cameras',NULL,NULL),(101,'Hikvision Series','test-series',100,'Hikvision'),(102,'Empty','empty-category',NULL,NULL),(103,'Mixed Series','mixed-series',100,NULL)");
$st=$db->prepare('INSERT INTO products(category_id,name,slug,brand,sku,short_desc,price,old_price,image,status) VALUES (?,?,?,?,?,?,?,?,?,?)');
for($i=1;$i<=26;$i++) $st->execute([101,'Test Camera '.$i,'test-camera-'.$i,'Hikvision','TEST-'.$i,'2 MP fixed lens camera',$i===1?0:10000,15000,'camera.jpg','active']);
$st->execute([100,'Legacy Camera','legacy-camera','Dahua','LEGACY','4 MP camera',20000,0,'camera.jpg','active']);
$st->execute([100,'Direct Hikvision','direct-camera','Hikvision','DIRECT','2 MP camera',15000,0,'camera.jpg','active']);
$st->execute([103,'Mixed camera','mixed-camera','Dahua','MIXED','2 MP',15000,0,'camera.jpg','active']);
$st->execute([101,'Inactive camera','inactive-camera','Hikvision','INACTIVE','2 MP',10000,0,'camera.jpg','inactive']);
function check($condition,$message){if(!$condition)throw new RuntimeException($message);}
check(catalogPath(['category'=>'a b'],2)==='/products.php?category=a%20b&page=2','Page canonical');
check(catalogPath([],1)==='/products.php','No page one duplicate');
check(sitemapDate('invalid')===null && sitemapDate('2999-01-01')===null,'No invented dates');
check(categoryCatalogPath(['slug'=>'test-series','parent_id'=>100,'brand'=>'Hikvision'],['slug'=>'test-cameras'])==='/products.php?category=test-cameras&brand=Hikvision&series=test-series','Hierarchy canonical');
$p=getProductBySlug('test-camera-1');check(!isset(schemaProduct($p)['offers']),'Unknown price is not free');
$p['price']=12345.67;check(schemaProduct($p)['offers']['price']==='12345.67','Real price retained');
check(productPriceLabel(0)==='Price on request','Public unknown price label');
check(in_array('Dahua',getCategoryBrands(100),true),'Direct parent brands available');
check(count(getChildCategories(100,'Dahua'))===1,'Infer mixed series brand');
echo "SEO database and schema regressions passed.\n";
