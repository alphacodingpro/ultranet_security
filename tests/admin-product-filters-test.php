<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once dirname(__DIR__).'/includes/admin-product-filters.php';
$db=new PDO(getenv('FILTER_TEST_DSN'),getenv('FILTER_TEST_USER'),getenv('FILTER_TEST_PASSWORD'),[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_EMULATE_PREPARES=>false,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
$db->exec('CREATE TABLE products (id INT PRIMARY KEY, name VARCHAR(180), brand VARCHAR(80), sku VARCHAR(80), category_id INT, status VARCHAR(20), product_type VARCHAR(20), short_desc TEXT, description TEXT, price DECIMAL(10,2), image VARCHAR(255), video_url VARCHAR(500)) CHARACTER SET utf8mb4');
$db->exec('CREATE TABLE product_images (id INT AUTO_INCREMENT PRIMARY KEY, product_id INT, image VARCHAR(255)) CHARACTER SET utf8mb4');
$st=$db->prepare('INSERT INTO products VALUES (?,?,?,?,?,?,?,?,?,?,?,?)');
$base=[1,'2 MP Camera','Hikvision','DS-2CD1',1,'active','camera','2MP Full HD Fixed Lens','Face Detection: Yes; Lens Type: Fixed Lens; Lens: 2.8/4/6/8 mm; Resolution: 1920 x 1080;',9999,'main.jpg','https://www.youtube.com/watch?v=abc123'];
$st->execute($base);
$row=$base;$row[0]=2;$row[1]='12MP Camera';$row[3]='DS-2CD2';$row[7]='12MP Varifocal';$row[8]='Lens: 2.8-12 mm; Face Detection: No; Intrusion detection: Yes;';$row[9]=20000;$row[10]='';$row[11]='';$st->execute($row);
$row=$base;$row[0]=3;$row[1]='8MP Camera';$row[3]='DS-2CD3';$row[7]='4K motorized varifocal lens';$row[8]='AcuSense; Human and vehicle detection; Motion Detection 2.0;';$row[9]=0;$row[10]='no-image.jpg';$row[11]='https://youtube.com.evil.test/watch?v=abc';$st->execute($row);
$row=$base;$row[0]=4;$row[1]='NVR supports 2MP cameras';$row[3]='DS-NVR4';$row[6]='nvr';$row[9]=50000;$row[10]='nvr.jpg';$row[11]='https://youtu.be/abc123';$st->execute($row);
$row=$base;$row[0]=5;$row[1]='Camera without specifications';$row[3]='unknown';$row[5]='inactive';$row[7]='';$row[8]='<table><tr><th>Face Detection</th><td>No</td></tr></table>';$row[9]=5000;$row[10]=null;$row[11]='https://vimeo.com/123';$st->execute($row);
$row=$base;$row[0]=6;$row[1]='4MP Camera';$row[3]='DS-4MP';$row[7]='4MP camera';$row[8]='<tr><th>Lens Type</th><td>Fixed Lens</td></tr><tr><th>Face Detection</th><td>Yes</td></tr> Sub-stream: 2 MP; 2560 × 1440; 3.6 mm lens';$row[9]=4999.99;$row[10]='a.jpg';$row[11]='https://www.youtube-nocookie.com/embed/abc';$st->execute($row);
$db->exec("INSERT INTO product_images(product_id,image) VALUES (1,'main.jpg'),(1,'extra.jpg'),(1,'extra.jpg'),(1,' '),(1,'no-image.jpg'),(2,'one.jpg'),(3,'a.jpg'),(3,'b.jpg'),(3,'c.jpg'),(3,'d.jpg'),(3,'e.jpg')");
function check($ok,$message){if(!$ok)throw new RuntimeException($message);}
function ids(array $query): array { global $db; $f=adminProductFilterValues($query);$sql=adminProductFiltersSql($f);$st=$db->prepare('SELECT p.id FROM products p WHERE '.$sql['where'].' ORDER BY p.id');$st->execute($sql['params']);return array_map('intval',$st->fetchAll(PDO::FETCH_COLUMN)); }
function expect(array $q,array $wanted,$message){$actual=ids($q);check($actual===$wanted,$message.' '.json_encode($actual));}
expect(['mp'=>['2']],[1],'MP exact boundary, headline priority and recorder exclusion');
expect(['mp'=>['2','4']],[1,6],'OR within MP');
expect(['mp'=>['2','4'],'images'=>['1']],[6],'AND across groups');
expect(['mp'=>['unknown']],[5],'Unknown MP');
expect(['resolution'=>['1080']],[1],'Resolution camera scope');
expect(['resolution'=>['4k']],[3],'4K');
expect(['resolution'=>['1440']],[6],'Unicode pixel dimensions');
expect(['ai'=>['face']],[1,6],'Exclude unsupported face detection, including HTML');
expect(['ai'=>['unknown']],[5],'No listed positive smart feature');
expect(['ai'=>['acusense','intrusion']],[2,3],'OR across smart options');
expect(['lens'=>['fixed']],[1,6],'Fixed lens from HTML and plain text');
expect(['lens'=>['varifocal']],[2,3],'Varifocal variants');
expect(['focal'=>['2.8-12']],[2],'Focal range');
expect(['focal'=>['4']],[1],'Listed slash-separated lens variants');
expect(['images'=>['0']],[5],'Unique references excluding placeholders');
expect(['images'=>['1']],[2,4,6],'One image, including gallery-only');
expect(['images'=>['2']],[1],'No double-counting primary or duplicate gallery');
expect(['images'=>['5plus']],[3],'Five image bucket');
expect(['youtube'=>['yes']],[1,4,6],'Real YouTube hosts only');
expect(['youtube'=>['no']],[2,3,5],'Absent YouTube including other hosts');
expect(['youtube'=>['yes','no']],[1,2,3,4,5,6],'Both media states');
expect(['price_band'=>['zero','5to10']],[1,3,5],'Price band OR');
expect(['price_band'=>['under5']],[6],'Decimal price boundary');
expect(['min_price'=>'5000','max_price'=>'20000'],[1,2,5],'Inclusive custom prices');
expect(['min_price'=>'20000','max_price'=>'5000'],[],'Reversed range does not broaden');
expect(['status'=>'inactive','youtube'=>['no']],[5],'Existing status retained');
expect(['q'=>'DS-2CD1'],[1],'SKU search');
$f=adminProductFilterValues(['mp'=>[['bad'],'2',"2') OR 1=1 --",'2'],'cat'=>['oops'],'q'=>['oops']]);check($f['mp']===['2'] && $f['cat']===0 && $f['q']==='','Malformed inputs ignored');
$f=adminProductFilterValues(['mp'=>['2','4'],'youtube'=>['yes'],'page'=>2]);parse_str(http_build_query($f),$roundtrip);check(adminProductFilterValues($roundtrip)===$f,'Pagination query preserves arrays');
$f=adminProductFilterValues(['youtube'=>['yes']]);$sql=adminProductFiltersSql($f);$st=$db->prepare('SELECT COUNT(*) AS total, SUM('.adminProductYoutubeSql().') AS with_youtube, SUM('.adminProductImageCountSql().'=0) AS without_images FROM products p WHERE '.$sql['where']);$st->execute($sql['params']);$summary=$st->fetch();check((int)$summary['total']===3 && (int)$summary['with_youtube']===3 && (int)$summary['without_images']===0,'Summary matches filtered rows');
echo "Admin product filter database tests passed.\n";
