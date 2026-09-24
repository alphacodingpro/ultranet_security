<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function quickLeadResponse(int $status, bool $ok, string $message): void
{
    http_response_code($status);
    echo json_encode(['ok' => $ok, 'message' => $message]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    quickLeadResponse(405, false, 'Please submit the form.');
}

$token = $_POST['csrf_token'] ?? '';
if (!is_string($token) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
    quickLeadResponse(403, false, 'Session expired. Refresh the page and try again.');
}

// Invisible field: genuine visitors leave it empty.
if (!empty($_POST['website'])) {
    quickLeadResponse(200, true, 'Thank you.');
}
if (!checkRateLimit('quick_lead', 3, 600)) {
    quickLeadResponse(429, false, 'Too many attempts. Please try again later or contact us on WhatsApp.');
}

$phone = $_POST['phone'] ?? '';
$email = $_POST['email'] ?? '';
if (!is_string($phone) || !is_string($email)) {
    quickLeadResponse(400, false, 'Enter a valid phone number or email address.');
}
$phone = trim($phone);
$email = trim($email);
if ($phone === '' && $email === '') {
    quickLeadResponse(400, false, 'Enter your phone number or email address.');
}
if ($phone !== '' && (!preg_match('/^[0-9+()\-\s]{7,30}$/D', $phone) || strlen(preg_replace('/\D/', '', $phone)) < 7)) {
    quickLeadResponse(400, false, 'Enter a valid phone number.');
}
if ($email !== '' && (strlen($email) > 150 || !filter_var($email, FILTER_VALIDATE_EMAIL))) {
    quickLeadResponse(400, false, 'Enter a valid email address.');
}

try {
    $id = saveContactMessage([
        'name' => 'Website visitor',
        'phone' => $phone,
        'email' => $email,
        'service' => 'Quick enquiry',
        'area' => '',
        'cameras' => '',
        'message' => 'Visitor requested a callback from the website popup.',
        'source' => 'sitewide_popup',
    ]);
} catch (Throwable $e) {
    error_log('[UltraNet] Quick lead could not be saved: ' . $e->getMessage());
    quickLeadResponse(500, false, 'Could not save your contact details. Please try again or WhatsApp us.');
}

// A failed SMTP attempt never discards a saved enquiry; email_sent stays 0 in Admin.
if (ADMIN_NOTIFY_EMAIL) {
    $contactRows = '<tr><td style="padding:8px;color:#6c757d">Phone</td><td style="padding:8px">' . htmlspecialchars($phone ?: 'Not provided', ENT_QUOTES, 'UTF-8') . '</td></tr>'
        . '<tr><td style="padding:8px;color:#6c757d">Email</td><td style="padding:8px">' . htmlspecialchars($email ?: 'Not provided', ENT_QUOTES, 'UTF-8') . '</td></tr>';
    $body = '<p>A visitor requested contact using the website popup.</p><table>' . $contactRows . '</table>'
        . '<p><a href="' . htmlspecialchars(ADMIN_URL . '/contact-messages.php', ENT_QUOTES, 'UTF-8') . '">View in Admin Panel</a></p>';
    if (sendNotificationEmail(ADMIN_NOTIFY_EMAIL, 'Website popup enquiry — UltraNet Security',
        emailTemplate('New Website Enquiry', $body), $email ?: null, 'Website visitor')) {
        try { markContactEmailSent($id); } catch (Throwable $e) {
            error_log('[UltraNet] Quick lead email sent, but status update failed: ' . $e->getMessage());
        }
    }
}

quickLeadResponse(200, true, 'Thanks! We will contact you soon.');
