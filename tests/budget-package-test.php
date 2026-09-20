<?php
require_once dirname(__DIR__).'/includes/budget-package.php';
function check($condition, $message) { if (!$condition) throw new RuntimeException($message); }
function rejects($call, $message) { try { $call(); } catch (RuntimeException $e) { return; } throw new RuntimeException($message); }
function product($id,$type,$price,$spec=[]) {
    return array_replace_recursive(['id'=>$id,'name'=>$type.' '.$id,'product_type'=>$type,'price'=>$price,'status'=>'active','stock_status'=>'in_stock','budget_profile'=>['enabled'=>true,'family'=>'test-ip']],$spec);
}
$catalog = [
    product(1,'camera',5000,['camera_watts'=>6,'budget_profile'=>['environment'=>'both','mp'=>2]]),
    product(2,'nvr',12000,['channels'=>8,'budget_profile'=>['mp'=>8,'max_hdd_gb'=>8000,'mbps'=>80]]),
    product(3,'hdd',8000,['storage_gb'=>2000]),
    product(4,'poe_switch',5000,['poe_ports'=>8,'poe_budget_watts'=>120]),
    product(5,'cable',1,['price_per_meter'=>40]),
    product(6,'other',500)
];
$input=['budget'=>100000,'property'=>'home','environment'=>'outdoor'];
$r=selectBudgetPackage($input,$catalog,5,20);
check($r['camera_count']===4,'Home camera target');
check(count($r['items'])===6,'Complete equipment bill');
check($r['grand_total']===47690.0,'Correct discount and quantities');
check($r['grand_total']<=$input['budget'],'Budget ceiling');
check(strlen($r['lens_note'])<=120,'Database note length');
$r2=selectBudgetPackage(array_merge($input,['budget'=>35000]),$catalog,5,20);
check($r2['camera_count']===1,'Lower budget reduces count');
rejects(function()use($input,$catalog){selectBudgetPackage(array_merge($input,['budget'=>100]),$catalog,5,20);},'Reject unaffordable package');
foreach (['camera','nvr','hdd','poe_switch','cable','other'] as $type) {
    $missing=array_values(array_filter($catalog,static function($p)use($type){return $p['product_type']!==$type;}));
    rejects(function()use($input,$missing){selectBudgetPackage($input,$missing,5,20);},'Reject missing '.$type);
}
foreach (['price','stock','approval','environment','family','capacity','poe','bandwidth','resolution'] as $case) {
    $bad=$catalog;
    if($case==='price')$bad[0]['price']=0;
    if($case==='stock')$bad[0]['stock_status']='out_of_stock';
    if($case==='approval')$bad[0]['budget_profile']['enabled']=false;
    if($case==='environment')$bad[0]['budget_profile']['environment']='indoor';
    if($case==='family')$bad[1]['budget_profile']['family']='incompatible';
    if($case==='capacity')$bad[1]['budget_profile']['max_hdd_gb']=1000;
    if($case==='poe')$bad[3]['poe_budget_watts']=1;
    if($case==='bandwidth')$bad[1]['budget_profile']['mbps']=1;
    if($case==='resolution')$bad[1]['budget_profile']['mp']=1;
    rejects(function()use($input,$bad){selectBudgetPackage($input,$bad,5,20);},'Reject '.$case);
}
$office=selectBudgetPackage(array_merge($input,['budget'=>200000,'property'=>'office','environment'=>'both']),$catalog,5,20);
check($office['camera_count']===5,'Storage constraint must reduce office count, never under-size HDD');
check($office['required_hdd_gb']<=2000,'Office HDD is sufficient');
$changed=$catalog;$changed[0]['price']+=1;
check(selectBudgetPackage($input,$changed,5,20)['fingerprint']!==$r['fingerprint'],'Invalidate estimate when price changes');
$better=$catalog;$better[]=product(7,'camera',7000,['camera_watts'=>6,'budget_profile'=>['environment'=>'both','mp'=>4]]);
check(selectBudgetPackage($input,$better,5,20)['mp']===4,'Prefer higher resolution when count and budget allow');
echo "Budget selector tests passed.\n";
