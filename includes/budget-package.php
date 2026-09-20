<?php
// Pure selector: only admin-approved, priced, in-stock IP/PoE equipment is eligible.
function budgetInput(array $input): array
{
    $budget = filter_var($input['budget'] ?? null, FILTER_VALIDATE_FLOAT);
    if ($budget === false || !is_finite((float)$budget) || $budget <= 0 || $budget > 10000000) {
        throw new InvalidArgumentException('Enter a budget between PKR 1 and PKR 10,000,000.');
    }
    $property = $input['property'] ?? '';
    $environment = $input['environment'] ?? '';
    if (!in_array($property, ['home', 'office'], true) || !in_array($environment, ['indoor', 'outdoor', 'both'], true)) {
        throw new InvalidArgumentException('Choose Home or Office and Indoor, Outdoor or Both.');
    }
    return ['budget'=>round((float)$budget, 2), 'property'=>$property, 'environment'=>$environment];
}

function selectBudgetPackage(array $input, array $catalog, float $discount, float $headroom): array
{
    $input = budgetInput($input);
    $discount = max(0, min(50, $discount));
    $headroom = max(20, min(100, $headroom));
    $target = $input['property'] === 'home' ? 4 : 8;
    $days = $input['property'] === 'home' ? 7 : 14;
    $metres = $input['property'] === 'home' ? 20 : 30;
    $pools = [];
    foreach ($catalog as $p) {
        $profile = $p['budget_profile'] ?? [];
        $type = $p['product_type'] ?? '';
        $price = (float)($type === 'cable' ? ($p['price_per_meter'] ?? 0) : ($p['price'] ?? 0));
        if (empty($profile['enabled']) || ($p['status'] ?? '') !== 'active' || ($p['stock_status'] ?? '') !== 'in_stock' || $price <= 0 || !is_finite($price)) continue;
        $p['_unit'] = $price;
        $pools[$type][] = $p;
    }
    foreach (['camera','nvr','hdd','poe_switch','cable','other'] as $type) {
        if (empty($pools[$type])) throw new RuntimeException('A complete priced equipment package is not available right now. Please request a custom quote.');
        usort($pools[$type], static function ($a, $b) { return ($a['_unit'] <=> $b['_unit']) ?: ($a['id'] <=> $b['id']); });
    }
    $best = null;
    $minimum = null;
    // Maximise camera coverage up to the stated home/office starting target,
    // then resolution, then prefer the cheaper complete package.
    for ($count = 1; $count <= $target; $count++) {
        foreach ($pools['camera'] as $camera) {
            $cp = $camera['budget_profile'];
            $env = $cp['environment'] ?? '';
            $mp = (int)($cp['mp'] ?? 0);
            $family = $cp['family'] ?? '';
            if ($family === '' || !in_array($mp, [2,4,5,8], true) || ($env !== 'both' && $env !== $input['environment'])) continue;
            $watts = (float)($camera['camera_watts'] ?? 0);
            if ($watts <= 0) continue;
            $requiredWatts = (int)ceil($count * $watts * (1 + $headroom / 100));
            // Decimal GB, 24/7 H.265 estimate plus 20% storage margin.
            $requiredGb = (int)ceil($mp / 8 * 86400 * $days * $count / 1000 * 1.2);
            $sameFamily = static function ($p) use ($family) { return ($p['budget_profile']['family'] ?? '') === $family; };
            $poe = null; $cable = null; $kit = null;
            foreach ($pools['poe_switch'] as $p) if ($sameFamily($p) && (int)$p['poe_ports'] >= $count && (float)$p['poe_budget_watts'] >= $requiredWatts) { $poe=$p; break; }
            foreach ($pools['cable'] as $p) if ($sameFamily($p)) { $cable=$p; break; }
            foreach ($pools['other'] as $p) if ($sameFamily($p)) { $kit=$p; break; }
            if (!$poe || !$cable || !$kit) continue;
            foreach ($pools['nvr'] as $nvr) {
                $np = $nvr['budget_profile'];
                if (!$sameFamily($nvr) || (int)$nvr['channels'] < $count || (int)($np['mp'] ?? 0) < $mp || (float)($np['mbps'] ?? 0) < $mp * $count * 1.2) continue;
                $hdd = null;
                foreach ($pools['hdd'] as $p) {
                    if ($sameFamily($p) && (int)$p['storage_gb'] >= $requiredGb && (int)$p['storage_gb'] <= (int)($np['max_hdd_gb'] ?? 0)) { $hdd=$p; break; }
                }
                if (!$hdd) continue;
                $items = [];
                foreach ([['camera',$camera,$count],['recorder',$nvr,1],['hdd',$hdd,1],['poe_switch',$poe,1],['cable',$cable,$metres*$count],['accessories',$kit,$count]] as $row) {
                    [$key,$p,$qty] = $row;
                    $items[] = ['key'=>$key,'product_id'=>(int)$p['id'],'name'=>$p['name'],'image'=>$p['image_url'] ?? '', 'qty'=>$qty,'unit_price'=>$p['_unit'],'line_total'=>round($p['_unit']*$qty,2),'meta'=>$key === 'cable' ? $qty.' metres' : $qty.' unit(s)'];
                }
                $subtotal = round(array_sum(array_column($items,'line_total')),2);
                $disc = round($subtotal*$discount/100,2);
                $total = round($subtotal-$disc,2);
                $minimum = $minimum === null ? $total : min($minimum,$total);
                if ($total > $input['budget']) continue;
                if ($best && ($count < $best['camera_count'] || ($count === $best['camera_count'] && ($mp < $best['mp'] || ($mp === $best['mp'] && $total >= $best['grand_total']))))) continue;
                $best = ['input'=>$input,'system_type'=>'ip','camera_count'=>$count,'target_count'=>$target,'mp'=>$mp,'resolution_label'=>$mp.'mp','recording_days'=>$days,'recording_mode'=>'continuous','cable_length_m'=>$metres*$count,'required_hdd_gb'=>$requiredGb,'required_poe_watts'=>$requiredWatts,'items'=>$items,'subtotal'=>$subtotal,'discount_percent'=>$discount,'discount_amount'=>$disc,'grand_total'=>$total,'remaining_budget'=>round($input['budget']-$total,2)];
            }
        }
    }
    if (!$best) {
        if ($minimum !== null) throw new RuntimeException('Budget is too low for a complete equipment package. The smallest matching package starts at PKR '.number_format($minimum,0).'.');
        throw new RuntimeException('No complete compatible package matches this location yet. Please request a custom quote.');
    }
    $best['lens_note'] = ucfirst($input['property']).'/'.ucfirst($input['environment']).'; budget PKR '.$input['budget'].'; equipment only, labour extra; target '.$target.' cameras.';
    $best['fingerprint'] = hash('sha256', json_encode($best));
    return $best;
}

function buildBudgetPackage(array $input): array
{
    $rows = getDB()->query("SELECT * FROM products WHERE status='active' AND stock_status='in_stock' AND product_type IN ('camera','nvr','hdd','poe_switch','cable','other')")->fetchAll();
    foreach ($rows as &$row) {
        $row['budget_profile'] = json_decode((string)getSetting('budget_product_'.$row['id'], '{}'), true) ?: [];
        $row['image_url'] = productImageUrl($row['image']);
    }
    unset($row);
    return selectBudgetPackage($input, $rows, (float)getSetting('package_discount_percent',5), (float)getSetting('poe_headroom_percent',20));
}
