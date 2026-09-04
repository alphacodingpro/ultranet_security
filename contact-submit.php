<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';

header('Content-Type: application/json');

function respond(bool $ok, string $msg = ''): void {
    echo json_encode(['ok' => $ok, 'message' => $msg]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') respond(false, 'Invalid request method.');

// Rate limit: max 5 submissions per 5 minutes per IP (blocks spam/bot flooding)
if (!checkRateLimit('contact_form', 5, 300)) {
    respond(false, 'Too many requests. Please wait a few minutes and try again, or WhatsApp us directly.');
}

$name    = trim($_POST['name'] ?? '');
$phone   = trim($_POST['phone'] ?? '');
$email   = trim($_POST['email'] ?? '');
$service = trim($_POST['service'] ?? '');
$area    = trim($_POST['area'] ?? '');
$cameras = trim($_POST['cameras'] ?? '');
$message = trim($_POST['message'] ?? '');

if (!$name)  respond(false, 'Please enter your name.');
if (!$phone) respond(false, 'Please enter your phone number.');
if (strlen($phone) < 7) respond(false, 'Please enter a valid phone number.');
if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) respond(false, 'Please enter a valid email address.');

// Basic honeypot spam protection (invisible field, humans leave it blank)
if (!empty($_POST['website'])) {
    // Silently pretend success to bots
    respond(true, 'Thank you! We will contact you shortly.');
}

try {
    $id = saveContactMessage([
        'name'    => $name,
        'phone'   => $phone,
        'email'   => $email,
        'service' => $service,
        'area'    => $area,
        'cameras' => $cameras,
        'message' => $message,
    ]);
} catch (Exception $e) {
    respond(false, 'Could not save your message. Please try again or WhatsApp us directly.');
}

// Send email notification to admin (never blocks the response on failure)
if (ADMIN_NOTIFY_EMAIL) {
    $rows = '';
    $fields = [
        'Name'     => $name,
        'Phone'    => $phone,
        'Email'    => $email ?: '—',
        'Service'  => $service ?: '—',
        'Area'     => $area ?: '—',
        'Cameras'  => $cameras ?: '—',
    ];
    foreach ($fields as $label => $val) {
        $rows .= '<tr><td style="padding:6px 10px;color:#6c757d;font-size:13px">' . htmlspecialchars($label) . '</td>'
               . '<td style="padding:6px 10px;font-size:13px;font-weight:600;color:#0a1628">' . htmlspecialchars($val) . '</td></tr>';
    }
    $msgHtml = $message
        ? '<p style="font-size:13px;color:#444;background:#f8f9fc;padding:12px;border-radius:8px;margin-top:14px">' . nl2br(htmlspecialchars($message)) . '</p>'
        : '';

    $body = '<table style="width:100%;border-collapse:collapse">' . $rows . '</table>' . $msgHtml
          . '<p style="margin-top:18px"><a href="' . ADMIN_URL . '/contact-messages.php" style="background:#e63946;color:#fff;padding:10px 20px;border-radius:8px;text-decoration:none;font-size:13px;font-weight:600;display:inline-block">View in Admin Panel</a></p>';

    $html = emailTemplate('New Contact Form Enquiry', $body);
    $sent = sendNotificationEmail(ADMIN_NOTIFY_EMAIL, 'New Enquiry from ' . $name . ' — UltraNet Security', $html, $email ?: null, $name);
    if ($sent) markContactEmailSent($id);
}

respond(true, 'Thank you! We will contact you within 1 hour on WhatsApp.');
