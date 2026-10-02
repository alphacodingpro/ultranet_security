<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
requireAdminLogin();
if($_SERVER['REQUEST_METHOD']!=='POST'){ header('Location: '.ADMIN_URL.'/packages.php'); exit; }
verifyCsrf(); $id=(int)($_POST['id'] ?? 0);
if($id && ensurePackageSchema()){ $st=getDB()->prepare('DELETE FROM packages WHERE id=?'); $st->execute([$id]); setFlash('success','Package deleted.'); }
header('Location: '.ADMIN_URL.'/packages.php'); exit;
