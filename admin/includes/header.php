<?php
// admin/includes/header.php
requireAdminLogin();
$adminName = $_SESSION['admin_name'] ?? 'Admin';
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= h($adminPageTitle ?? 'Admin Panel') ?> | <?= SITE_NAME ?></title>
  <meta name="robots" content="noindex, nofollow">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/admin.css">
</head>
<body class="admin-body">

<!-- TOP NAVBAR -->
<nav class="admin-topbar">
  <div class="admin-topbar-left">
    <button class="sidebar-toggle" id="sidebarToggle">
      <i class="fa-solid fa-bars"></i>
    </button>
    <a href="<?= ADMIN_URL ?>/" class="admin-brand">
      <i class="fa-solid fa-camera"></i>
      <span>UltraNet <em>Admin</em></span>
    </a>
  </div>
  <div class="admin-topbar-right">
    <a href="<?= SITE_URL ?>/" target="_blank" class="admin-topbar-link">
      <i class="fa-solid fa-globe"></i><span class="d-none d-md-inline ms-1">View Site</span>
    </a>
    <div class="admin-user-info">
      <div class="admin-avatar"><i class="fa-solid fa-user"></i></div>
      <span class="d-none d-md-inline"><?= h($adminName) ?></span>
    </div>
    <a href="<?= ADMIN_URL ?>/logout.php" class="admin-topbar-link text-danger" title="Logout">
      <i class="fa-solid fa-right-from-bracket"></i>
    </a>
  </div>
</nav>

<!-- SIDEBAR -->
<aside class="admin-sidebar" id="adminSidebar">
  <ul class="sidebar-nav">
    <li>
      <a href="<?= ADMIN_URL ?>/" class="<?= $currentPage === 'index.php' ? 'active' : '' ?>">
        <i class="fa-solid fa-gauge-high"></i> Dashboard
      </a>
    </li>
    <li class="sidebar-section">PRODUCTS</li>
    <li>
      <a href="<?= ADMIN_URL ?>/products.php" class="<?= in_array($currentPage,['products.php','product-add.php','product-edit.php']) ? 'active' : '' ?>">
        <i class="fa-solid fa-camera"></i> All Products
      </a>
    </li>
    <li>
      <a href="<?= ADMIN_URL ?>/product-add.php" class="<?= $currentPage === 'product-add.php' ? 'active' : '' ?>">
        <i class="fa-solid fa-circle-plus"></i> Add Product
      </a>
    </li>
    <li class="sidebar-section">CATEGORIES</li>
    <li>
      <a href="<?= ADMIN_URL ?>/categories.php" class="<?= in_array($currentPage,['categories.php','category-add.php','category-edit.php']) ? 'active' : '' ?>">
        <i class="fa-solid fa-layer-group"></i> All Categories
      </a>
    </li>
    <li>
      <a href="<?= ADMIN_URL ?>/category-add.php" class="<?= $currentPage === 'category-add.php' ? 'active' : '' ?>">
        <i class="fa-solid fa-circle-plus"></i> Add Category
      </a>
    </li>
    <li class="sidebar-section">CALCULATOR</li>
    <li>
      <a href="<?= ADMIN_URL ?>/calculator-requests.php" class="<?= in_array($currentPage,['calculator-requests.php','calculator-request-view.php']) ? 'active' : '' ?>">
        <i class="fa-solid fa-calculator"></i> Client Requests
        <?php
        try {
          $newCnt = getDB()->query("SELECT COUNT(*) FROM calculator_requests WHERE status='new'")->fetchColumn();
          if ($newCnt > 0) echo '<span class="badge-featured ms-1">' . $newCnt . '</span>';
        } catch (Exception $e) {}
        ?>
      </a>
    </li>
    <li>
      <a href="<?= ADMIN_URL ?>/calculator-settings.php" class="<?= $currentPage === 'calculator-settings.php' ? 'active' : '' ?>">
        <i class="fa-solid fa-gear"></i> Calculator Settings
      </a>
    </li>
    <li class="sidebar-section">INBOX</li>
    <li>
      <a href="<?= ADMIN_URL ?>/contact-messages.php" class="<?= $currentPage === 'contact-messages.php' ? 'active' : '' ?>">
        <i class="fa-solid fa-envelope"></i> Contact Messages
        <?php
        try {
          $newMsgCnt = countNewContactMessages();
          if ($newMsgCnt > 0) echo '<span class="badge-featured ms-1">' . $newMsgCnt . '</span>';
        } catch (Exception $e) {}
        ?>
      </a>
    </li>
    <li>
      <a href="<?= ADMIN_URL ?>/email-test.php" class="<?= $currentPage === 'email-test.php' ? 'active' : '' ?>">
        <i class="fa-solid fa-paper-plane"></i> Email / SMTP Setup
      </a>
    </li>
    <li class="sidebar-section">SITE</li>
    <li>
      <a href="<?= SITE_URL ?>/" target="_blank">
        <i class="fa-solid fa-globe"></i> View Website
      </a>
    </li>
    <li>
      <a href="<?= ADMIN_URL ?>/logout.php" class="text-danger-link">
        <i class="fa-solid fa-right-from-bracket"></i> Logout
      </a>
    </li>
  </ul>
</aside>

<!-- MAIN CONTENT AREA -->
<div class="admin-main" id="adminMain">
  <div class="admin-content">
