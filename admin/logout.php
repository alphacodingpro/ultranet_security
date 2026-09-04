<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
adminLogout();
header('Location: ' . ADMIN_URL . '/login.php');
exit;
