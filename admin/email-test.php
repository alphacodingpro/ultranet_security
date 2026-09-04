<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';

requireAdminLogin();

$result = null;
$error  = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_test'])) {
    verifyCsrf();
    $to = trim($_POST['test_email'] ?? '');
    if (!$to || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address to send the test to.';
    } else {
        require_once dirname(__DIR__) . '/includes/SmtpMailer.php';
        try {
            $mailer = new SmtpMailer(SMTP_HOST, SMTP_PORT, SMTP_ENCRYPTION, SMTP_USERNAME, SMTP_PASSWORD, SMTP_FROM_EMAIL, SMTP_FROM_NAME);
            $body = emailTemplate('Test Email', '<p style="font-size:14px;color:#444">This is a test email from your UltraNet Security admin panel. If you received this, your SMTP settings in <code>.env</code> are working correctly! ✅</p>');
            $mailer->send($to, 'Test Email — UltraNet Security Admin', $body);
            $result = 'success';
        } catch (Throwable $e) {
            $error = $e->getMessage();
            $result = 'fail';
        }
    }
}

$adminPageTitle = 'Email / SMTP Test';
include __DIR__ . '/includes/header.php';
?>

<div class="admin-page-header">
  <h1 class="admin-page-title"><i class="fa-solid fa-paper-plane me-2"></i>Email / SMTP Setup</h1>
  <a href="<?= ADMIN_URL ?>/contact-messages.php" class="btn-admin-outline"><i class="fa-solid fa-arrow-left me-1"></i>Back</a>
</div>

<div class="row g-4">
  <div class="col-lg-6">
    <div class="admin-card mb-4">
      <div class="admin-card-header"><h5><i class="fa-solid fa-gear me-2"></i>Current SMTP Configuration</h5></div>
      <div class="admin-card-body">
        <p class="text-muted small mb-3">These values come from your <code>.env</code> file. Edit them there — not here.</p>
        <table class="admin-table" style="border:1px solid var(--admin-border);border-radius:8px;overflow:hidden">
          <tbody>
            <tr><td style="width:45%">SMTP Host</td><td><strong><?= h(SMTP_HOST) ?></strong></td></tr>
            <tr><td>Port</td><td><strong><?= SMTP_PORT ?></strong></td></tr>
            <tr><td>Encryption</td><td><strong><?= h(strtoupper(SMTP_ENCRYPTION)) ?></strong></td></tr>
            <tr><td>Username</td><td><strong><?= SMTP_USERNAME ? h(SMTP_USERNAME) : '<span class="text-danger">Not set</span>' ?></strong></td></tr>
            <tr><td>Password</td><td><strong><?= SMTP_PASSWORD ? '••••••••••••' . ' (' . strlen(SMTP_PASSWORD) . ' chars)' : '<span class="text-danger">Not set</span>' ?></strong></td></tr>
            <tr><td>From Email</td><td><strong><?= h(SMTP_FROM_EMAIL) ?></strong></td></tr>
            <tr><td>Admin Notify Email</td><td><strong><?= ADMIN_NOTIFY_EMAIL ? h(ADMIN_NOTIFY_EMAIL) : '<span class="text-danger">Not set</span>' ?></strong></td></tr>
          </tbody>
        </table>

        <?php if (!SMTP_USERNAME || !SMTP_PASSWORD): ?>
        <div class="alert alert-danger mt-3 mb-0" style="font-size:13px">
          <i class="fa-solid fa-triangle-exclamation me-1"></i>
          SMTP_USERNAME or SMTP_PASSWORD is missing in your <code>.env</code> file — emails will not send until these are set.
        </div>
        <?php endif; ?>
      </div>
    </div>

    <div class="admin-card">
      <div class="admin-card-header"><h5><i class="fa-solid fa-list-check me-2"></i>Gmail Setup Steps</h5></div>
      <div class="admin-card-body" style="font-size:14px;line-height:1.9">
        <ol class="mb-0 ps-3">
          <li>Go to <a href="https://myaccount.google.com/security" target="_blank">myaccount.google.com/security</a></li>
          <li>Turn on <strong>2-Step Verification</strong> (required for App Passwords)</li>
          <li>Go to <a href="https://myaccount.google.com/apppasswords" target="_blank">myaccount.google.com/apppasswords</a></li>
          <li>Create a new App Password (choose "Mail" as the app)</li>
          <li>Copy the 16-character password Google gives you</li>
          <li>Open your <code>.env</code> file and set:
            <pre style="background:var(--admin-bg);padding:10px 14px;border-radius:8px;font-size:12px;margin-top:6px">SMTP_USERNAME=youraddress@gmail.com
SMTP_PASSWORD=the16charapppassword
SMTP_FROM_EMAIL=youraddress@gmail.com
ADMIN_NOTIFY_EMAIL=youraddress@gmail.com</pre>
          </li>
          <li>Save the file and send a test email below 👉</li>
        </ol>
      </div>
    </div>
  </div>

  <div class="col-lg-6">
    <div class="admin-card">
      <div class="admin-card-header"><h5><i class="fa-solid fa-vial me-2"></i>Send a Test Email</h5></div>
      <div class="admin-card-body">

        <?php if ($result === 'success'): ?>
        <div class="alert alert-success"><i class="fa-solid fa-circle-check me-2"></i>Test email sent successfully! Check the inbox (and spam folder).</div>
        <?php elseif ($result === 'fail'): ?>
        <div class="alert alert-danger">
          <strong><i class="fa-solid fa-circle-xmark me-2"></i>Failed to send email</strong><br>
          <span style="font-size:13px"><?= h($error) ?></span>
        </div>
        <?php endif; ?>

        <form method="POST">
          <?php csrfField(); ?>
          <div class="mb-3">
            <label class="form-label fw-600">Send Test To</label>
            <input type="email" name="test_email" class="form-control" placeholder="your@email.com"
                   value="<?= h($_POST['test_email'] ?? ADMIN_NOTIFY_EMAIL) ?>" required>
          </div>
          <button type="submit" name="send_test" value="1" class="btn-admin-primary w-100 py-3">
            <i class="fa-solid fa-paper-plane me-2"></i>Send Test Email
          </button>
        </form>

        <div class="alert alert-info mt-3 mb-0" style="font-size:13px">
          <i class="fa-solid fa-circle-info me-1"></i>
          Common errors: <strong>"Authentication failed"</strong> almost always means you used your normal Gmail
          password instead of an App Password, or 2-Step Verification isn't enabled yet.
        </div>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
