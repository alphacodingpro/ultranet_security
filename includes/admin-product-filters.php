<?php
// Admin-only catalog filters. Every SQL fragment is fixed here; values are bound.
function adminProductFilterOptions(): array
{
    return [
        'price_band'=>['label'=>'Price (PKR)','items'=>['zero'=>'0 / Price not set','under5'=>'>0 to <5,000','5to10'=>'5,000–9,999','10to20'=>'10,000–19,999','20to50'=>'20,000–49,999','50plus'=>'50,000+']],
        'mp'=>['label'=>'Camera megapixels','items'=>['1'=>'1 MP','2'=>'2 MP','3'=>'3 MP','4'=>'4 MP','5'=>'5 MP','6'=>'6 MP','8'=>'8 MP','12'=>'12 MP','other'=>'Other MP','unknown'=>'Not listed']],
        'resolution'=>['label'=>'Listed camera resolution','items'=>['720'=>'720p / 1280×720','1080'=>'1080p / Full HD','1440'=>'1440p / 2560×1440','4k'=>'4K / 3840×2160','unknown'=>'Other / Not listed']],
        'ai'=>['label'=>'AI / Smart features','items'=>['acusense'=>'AcuSense','wizsense'=>'WizSense','deep'=>'Deep learning / AI','human'=>'Human / vehicle detection','face'=>'Face detection / recognition','line'=>'Line crossing','intrusion'=>'Intrusion detection','anpr'=>'ANPR / Number plate','tracking'=>'Auto tracking','motion2'=>'Motion Detection 2.0','unknown'=>'No listed match']],
        'lens'=>['label'=>'Camera lens type','items'=>['fixed'=>'Fixed lens','varifocal'=>'Varifocal lens','motorized'=>'Motorized lens','fisheye'=>'Fisheye','unknown'=>'Other / Not listed']],
        'focal'=>['label'=>'Listed focal lengths','items'=>['2.8'=>'2.8 mm','3.6'=>'3.6 mm','4'=>'4 mm','6'=>'6 mm','8'=>'8 mm','12'=>'12 mm','2.8-12'=>'2.8–12 mm range','unknown'=>'Other / Not listed']],
        'images'=>['label'=>'Number of images','items'=>['0'=>'No images','1'=>'1 image','2'=>'2 images','3'=>'3 images','4'=>'4 images','5plus'=>'5+ images']],
        'youtube'=>['label'=>'YouTube URL','items'=>['yes'=>'YouTube URL added','no'=>'No YouTube URL']],
    ];
}

function adminProductFilterValues(array $query): array
{
    $result=[];
    foreach (adminProductFilterOptions() as $key=>$group) {
        $raw=$query[$key] ?? [];
        if (!is_array($raw)) $raw=[$raw];
        $result[$key]=[];
        foreach ($raw as $value) {
            if (is_scalar($value) && array_key_exists((string)$value,$group['items'])) $result[$key][]=(string)$value;
        }
        $result[$key]=array_values(array_unique($result[$key]));
    }
    foreach (['q','status','cat','min_price','max_price'] as $key) $result[$key]=isset($query[$key]) && is_scalar($query[$key]) ? trim((string)$query[$key]) : '';
    $result['cat']=max(0,(int)$result['cat']);
    if (!in_array($result['status'],['active','inactive'],true)) $result['status']='';
    $result['q']=substr($result['q'],0,200);
    return $result;
}

function adminProductImageCountSql(): string
{
    // Count unique catalog image references, not placeholder files or duplicates.
    return "(SELECT COUNT(DISTINCT media.filename) FROM (
        SELECT id AS product_id, TRIM(image) AS filename FROM products
        UNION ALL SELECT product_id, TRIM(image) AS filename FROM product_images
    ) media WHERE media.product_id=p.id AND media.filename IS NOT NULL AND media.filename<>''
    AND LOWER(media.filename) NOT IN ('no-image.jpg','no-image.png')
    AND LOWER(media.filename) NOT LIKE '%/no-image.jpg' AND LOWER(media.filename) NOT LIKE '%/no-image.png')";
}

function adminProductYoutubeSql(): string
{
    return "(LOWER(TRIM(COALESCE(p.video_url,''))) REGEXP '^(https?://)?((www|m|music)[.])?(youtube[.]com|youtu[.]be|youtube-nocookie[.]com)/[^[:space:]]+')";
}

function adminProductFiltersSql(array $f): array
{
    $where=['1=1']; $params=[]; $errors=[];
    if ($f['cat']) { $where[]='p.category_id=?'; $params[]=$f['cat']; }
    if ($f['status']!=='') { $where[]='p.status=?'; $params[]=$f['status']; }
    if ($f['q']!=='') { $where[]='(p.name LIKE ? OR p.brand LIKE ? OR p.sku LIKE ?)'; $kw='%'.$f['q'].'%'; array_push($params,$kw,$kw,$kw); }
    $prices=[];
    foreach (['min_price','max_price'] as $key) {
        if ($f[$key]==='') continue;
        if (!is_numeric($f[$key]) || !is_finite((float)$f[$key]) || (float)$f[$key]<0 || (float)$f[$key]>99999999.99) $errors[]='Enter a valid non-negative price.';
        else $prices[$key]=(float)$f[$key];
    }
    if (isset($prices['min_price'],$prices['max_price']) && $prices['min_price']>$prices['max_price']) $errors[]='Minimum price cannot exceed maximum price.';
    if ($errors) $where[]='1=0';
    foreach ($prices as $key=>$value) { $where[]='p.price'.($key==='min_price'?'>=':'<=').'?'; $params[]=$value; }
    $priceSql=['zero'=>'p.price=0','under5'=>'(p.price>0 AND p.price<5000)','5to10'=>'(p.price>=5000 AND p.price<10000)','10to20'=>'(p.price>=10000 AND p.price<20000)','20to50'=>'(p.price>=20000 AND p.price<50000)','50plus'=>'p.price>=50000'];
    if ($f['price_band']) $where[]='('.implode(' OR ',array_map(static function($v)use($priceSql){return $priceSql[$v];},$f['price_band'])).')';

    $text="LOWER(CONCAT_WS(' ',p.name,p.short_desc,p.description))";
    $camera="(p.product_type='camera' OR (p.product_type='other' AND LOWER(CONCAT_WS(' ',p.name,p.short_desc)) REGEXP '(^|[^a-z])camera(s)?([^a-z]|$)'))";
    $sep='([[:space:]:=-]|<[^>]*>)*';
    $mpAny='(^|[^0-9.])[0-9]+([.][0-9]+)?[[:space:]-]*(mp|mega[ -]?pixels?)([^a-z]|$)';
    // Prefer the headline MP rather than lower sub-stream specifications.
    $mpText="LOWER(CASE WHEN LOWER(p.name) REGEXP '".$mpAny."' THEN p.name WHEN LOWER(COALESCE(p.short_desc,'')) REGEXP '".$mpAny."' THEN p.short_desc ELSE COALESCE(p.description,'') END)";
    $patterns=[
        'mp'=>[],
        'resolution'=>['720'=>'(^|[^0-9])720p|1280[[:space:]x×*]+720([^0-9]|$)','1080'=>'(^|[^0-9])1080p|full[ -]?hd|1920[[:space:]x×*]+1080([^0-9]|$)','1440'=>'(^|[^0-9])1440p|2560[[:space:]x×*]+1440([^0-9]|$)','4k'=>'(^|[^0-9a-z])4k([^0-9a-z]|$)|2160p|3840[[:space:]x×*]+2160([^0-9]|$)'],
        'ai'=>['acusense'=>'acusense','wizsense'=>'wizsense','deep'=>'deep[ -]?learning|artificial intelligence|(^|[^a-z])ai([^a-z]|$)','human'=>'human.{0,20}(detect|classif)|vehicle.{0,20}(detect|classif)|human/vehicle','face'=>'face[ -]?(detection|recognition)|facial[ -]?recognition','line'=>'line[ -]?crossing','intrusion'=>'intrusion[ -]?detection','anpr'=>'(^|[^a-z])(anpr|lpr)([^a-z]|$)|(license|licence|number)[ -]?plate[ -]?(recognition|detection)','tracking'=>'auto[ -]?tracking|automatic tracking','motion2'=>'motion detection[[:space:]]*2[.]0'],
        'lens'=>['fixed'=>'fixed[ -]?(focal[ -]?length[ -]?)?lens|lens[ -]?type'.$sep.'fixed|fixed[ -]?focal','varifocal'=>'vari[ -]?focal','motorized'=>'motori[sz]ed.{0,20}(lens|vari[ -]?focal)|lens[ -]?type'.$sep.'motori[sz]ed','fisheye'=>'fish[ -]?eye'],
        'focal'=>[]
    ];
    foreach ([1,2,3,4,5,6,8,12] as $mp) $patterns['mp'][(string)$mp]='(^|[^0-9.])'.$mp.'([.]0)?[[:space:]-]*(mp|mega[ -]?pixels?)([^a-z]|$)';
    foreach (['2.8','3.6','4','6','8','12'] as $mm) $patterns['focal'][$mm]='(^|[^0-9.])'.str_replace('.','[.]',$mm).'([.]0)?[[:space:]]*mm([^a-z]|$)|(^|[^0-9.])'.str_replace('.','[.]',$mm).'([.]0)?(/[0-9.]+)+[[:space:]]*mm([^a-z]|$)';
    $patterns['focal']['2.8-12']='(^|[^0-9.])2[.]8[[:space:]]*[-–~][[:space:]]*12([.]0)?[[:space:]]*mm';
    foreach ($patterns as $group=>$choices) {
        if (!$f[$group]) continue;
        $field=$group==='mp'?$mpText:$text;
        $parts=[]; $partParams=[];
        foreach ($f[$group] as $value) {
            if ($value==='unknown') {
                if ($group==='ai') {
                    $known=[];
                    foreach ($choices as $pattern) {
                        $negative='(no|without|does not support)[[:space:]]+('.$pattern.')|('.$pattern.')'.$sep.'(no|none|not supported|not available|unsupported|false)([^a-z]|$)';
                        $known[]="($field REGEXP ? AND NOT ($field REGEXP ?))";
                        array_push($partParams,$pattern,$negative);
                    }
                    $parts[]='NOT ('.implode(' OR ',$known).')';
                } else {
                    $parts[]="NOT ($field REGEXP ?)";
                    $partParams[]=$group==='mp'?$mpAny:'('.implode(')|(',$choices).')';
                }
            } elseif ($value==='other' && $group==='mp') {
                $parts[]="($field REGEXP ? AND NOT ($field REGEXP ?))";
                $partParams[]=$mpAny; $partParams[]='('.implode(')|(',$choices).')';
            } else {
                $pattern=$choices[$value];
                $condition="$field REGEXP ?"; $partParams[]=$pattern;
                if ($group==='ai') {
                    $negative='(no|without|does not support)[[:space:]]+('.$pattern.')|('.$pattern.')'.$sep.'(no|none|not supported|not available|unsupported|false)([^a-z]|$)';
                    $condition="($condition AND NOT ($field REGEXP ?))"; $partParams[]=$negative;
                }
                $parts[]=$condition;
            }
        }
        $where[]=$camera; $where[]='('.implode(' OR ',$parts).')'; array_push($params,...$partParams);
    }
    if ($f['images']) {
        $count=adminProductImageCountSql(); $parts=[];
        foreach ($f['images'] as $n) $parts[]=$count.($n==='5plus'?'>=5':'='.(int)$n);
        $where[]='('.implode(' OR ',$parts).')';
    }
    if (count($f['youtube'])===1) $where[]=($f['youtube'][0]==='no'?'NOT ':'').adminProductYoutubeSql();
    return ['where'=>implode(' AND ',$where),'params'=>$params,'errors'=>array_values(array_unique($errors))];
}
