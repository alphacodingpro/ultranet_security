<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';

// Already logged in → redirect to dashboard
if (isAdminLoggedIn()) {
    header('Location: ' . ADMIN_URL . '/');
    exit;
}

$error = '';
$clientIp = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$lockedFor = isLoginLocked($clientIp);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $lockedFor === 0) {
    verifyCsrf();
    $user = trim($_POST['username'] ?? '');
    $pass = trim($_POST['password'] ?? '');
    if ($user && $pass) {
        if (adminLogin($user, $pass)) {
            clearLoginAttempts($clientIp);
            header('Location: ' . ADMIN_URL . '/');
            exit;
        } else {
            registerFailedLogin($clientIp);
            $lockedFor = isLoginLocked($clientIp);
            $error = $lockedFor > 0
                ? 'Too many failed attempts. Please try again in ' . ceil($lockedFor / 60) . ' minute(s).'
                : 'Invalid username or password.';
        }
    } else {
        $error = 'Please enter both username and password.';
    }
} elseif ($lockedFor > 0) {
    $error = 'Too many failed attempts. Please try again in ' . ceil($lockedFor / 60) . ' minute(s).';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login | <?= SITE_NAME ?></title>
  <meta name="robots" content="noindex, nofollow">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/admin.css">
</head>
<body class="admin-login-page">

<div class="login-wrap">
  <div class="login-card">
    <div class="login-logo">
      <div class="login-icon"><i class="fa-solid fa-camera"></i></div>
      <div class="login-brand">UltraNet <span>Security</span></div>
      <p class="login-subtitle">Admin Panel</p>
    </div>

    <?php if ($error): ?>
    <div class="alert alert-danger py-2 px-3 mb-3 small">
      <i class="fa-solid fa-circle-exclamation me-1"></i><?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <form method="POST" action="">
      <?php csrfField(); ?>
      <div class="mb-3">
        <label for="username" class="form-label">Username</label>
        <div class="input-group">
          <span class="input-group-text"><i class="fa-solid fa-user"></i></span>
          <input type="text" id="username" name="username" class="form-control" placeholder="Enter username"
                 value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" autofocus required <?= $lockedFor>0?'disabled':'' ?>>
        </div>
      </div>
      <div class="mb-4">
        <label for="password" class="form-label">Password</label>
        <div class="input-group">
          <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
          <input type="password" id="password" name="password" class="form-control" placeholder="Enter password" required <?= $lockedFor>0?'disabled':'' ?>>
          <button type="button" class="input-group-text toggle-pass" onclick="togglePass()">
            <i class="fa-solid fa-eye" id="eyeIcon"></i>
          </button>
        </div>
      </div>
      <button type="submit" class="btn-admin-login w-100" <?= $lockedFor>0?'disabled':'' ?>>
        <i class="fa-solid fa-right-to-bracket me-2"></i>Login to Admin Panel
      </button>
    </form>

    <div class="mt-3 text-center">
      <a href="<?= SITE_URL ?>/" class="text-muted small text-decoration-none">
        <i class="fa-solid fa-arrow-left me-1"></i>Back to Website
      </a>
    </div>
  </div>
</div>

<script>
function togglePass() {
  const inp = document.getElementById('password');
  const ico = document.getElementById('eyeIcon');
  if (inp.type === 'password') {
    inp.type = 'text';
    ico.className = 'fa-solid fa-eye-slash';
  } else {
    inp.type = 'password';
    ico.className = 'fa-solid fa-eye';
  }
}
</script>
</body>
</html>
