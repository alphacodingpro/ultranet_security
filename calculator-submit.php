<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';

header('Content-Type: application/json');

$raw  = file_get_contents('php://input');
$body = json_decode($raw, true);

function respondError(string $msg): void {
    echo json_encode(['ok' => false, 'error' => $msg]);
    exit;
}

// Rate limit: max 5 submissions per 5 minutes per IP (blocks spam/bot flooding)
if (!checkRateLimit('calculator_form', 5, 300)) {
    respondError('Too many requests. Please wait a few minutes and try again, or WhatsApp us directly.');
}

if (!$body) respondError('Invalid request.');

$name  = trim($body['client_name']  ?? '');
$phone = trim($body['client_phone'] ?? '');
$email = trim($body['client_email'] ?? '');

if (!$name)  respondError('Please enter your name.');
if (!$phone) respondError('Please enter your phone number.');
if (strlen($phone) < 7) respondError('Please enter a valid phone number.');
if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) respondError('Please enter a valid email address.');

$systemType    = ($body['system_type'] ?? 'ip') === 'analog' ? 'analog' : 'ip';
$cameraCount   = max(1, (int)($body['camera_count'] ?? 1));
$resolution    = $body['resolution_label'] ?? '4mp';
$lensNote      = trim($body['lens_note'] ?? '');
$recDays       = max(1, (int)($body['recording_days'] ?? 30));
$recMode       = ($body['recording_mode'] ?? 'continuous') === 'motion' ? 'motion' : 'continuous';
$cableTotalM   = max(0, (int)($body['cable_length_m'] ?? 0));

$clientItems = is_array($body['items'] ?? null) ? $body['items'] : [];
if (empty($clientItems)) respondError('No package items selected.');

// ── SERVER-SIDE RECALCULATION (never trust client prices) ──
$requiredGb = calcRequiredHddGb($cameraCount, $resolution, $recDays, $recMode);

$db = getDB();
$finalItems = [];
$subtotal   = 0.0;

foreach ($clientItems as $ci) {
    $pid = (int)($ci['product_id'] ?? 0);
    $qty = max(1, (int)($ci['qty'] ?? 1));
    if (!$pid) continue;

    $st = $db->prepare("SELECT * FROM products WHERE id = ? AND status = 'active'");
    $st->execute([$pid]);
    $p = $st->fetch();
    if (!$p) continue;

    $unitPrice = (float)$p['price'];
    // Cable uses price_per_meter instead
    if ($p['product_type'] === 'cable' && $p['price_per_meter']) {
        $unitPrice = (float)$p['price_per_meter'];
    }
    $lineTotal = $unitPrice * $qty;
    $subtotal += $lineTotal;

    $finalItems[] = [
        'key'        => $ci['key'] ?? $p['product_type'],
        'product_id' => $p['id'],
        'name'       => $p['name'],
        'image'      => productImageUrl($p['image']),
        'qty'        => $qty,
        'unit_price' => $unitPrice,
        'line_total' => $lineTotal,
        'meta'       => $ci['meta'] ?? '',
    ];
}

if (empty($finalItems)) respondError('Selected products are no longer available.');

$discountPct = (float)getSetting('package_discount_percent', 5);
$discountAmt = round($subtotal * ($discountPct / 100), 2);
$grandTotal  = round($subtotal - $discountAmt, 2);

$requiredWatts = 0;
if ($systemType === 'ip') {
    $camItem = null;
    foreach ($finalItems as $it) { if ($it['key'] === 'camera') { $camItem = $it; break; } }
    $wattsPer = 9.0;
    if ($camItem) {
        $stw = $db->prepare('SELECT camera_watts FROM products WHERE id = ?');
        $stw->execute([$camItem['product_id']]);
        $w = $stw->fetchColumn();
        if ($w) $wattsPer = (float)$w;
    }
    $requiredWatts = calcRequiredPoeWatts($cameraCount, $wattsPer);
}

try {
    $ref = saveCalculatorRequest([
        'client_name'      => $name,
        'client_phone'     => $phone,
        'client_email'     => $email,
        'system_type'      => $systemType,
        'camera_count'     => $cameraCount,
        'resolution_label' => strtoupper($resolution),
        'lens_note'        => $lensNote,
        'recording_days'   => $recDays,
        'recording_mode'   => $recMode,
        'cable_length_m'   => $cableTotalM,
        'required_hdd_gb'  => $requiredGb,
        'required_poe_watts' => $requiredWatts,
        'items'            => $finalItems,
        'subtotal'         => round($subtotal, 2),
        'discount_percent' => $discountPct,
        'discount_amount'  => $discountAmt,
        'grand_total'      => $grandTotal,
    ]);

    // Notify admin by email (never blocks the response if it fails)
    if (ADMIN_NOTIFY_EMAIL) {
        $rows = '';
        $fields = [
            'Reference'   => $ref,
            'Name'        => $name,
            'Phone'       => $phone,
            'Email'       => $email ?: '—',
            'System Type' => $systemType === 'ip' ? 'IP (NVR+PoE)' : 'Analog (DVR)',
            'Cameras'     => $cameraCount,
            'Grand Total' => formatPrice($grandTotal),
        ];
        foreach ($fields as $label => $val) {
            $rows .= '<tr><td style="padding:6px 10px;color:#6c757d;font-size:13px">' . htmlspecialchars($label) . '</td>'
                   . '<td style="padding:6px 10px;font-size:13px;font-weight:600;color:#0a1628">' . htmlspecialchars((string)$val) . '</td></tr>';
        }
        $body = '<table style="width:100%;border-collapse:collapse">' . $rows . '</table>'
              . '<p style="margin-top:18px"><a href="' . ADMIN_URL . '/calculator-requests.php" style="background:#e63946;color:#fff;padding:10px 20px;border-radius:8px;text-decoration:none;font-size:13px;font-weight:600;display:inline-block">View in Admin Panel</a></p>';
        $html = emailTemplate('New CCTV Package Request', $body);
        sendNotificationEmail(ADMIN_NOTIFY_EMAIL, 'New Package Request from ' . $name . ' — UltraNet Security', $html, $email ?: null, $name);
    }

    echo json_encode(['ok' => true, 'ref' => $ref]);
} catch (Exception $e) {
    respondError('Could not save your request. Please try again or WhatsApp us directly.');
}
