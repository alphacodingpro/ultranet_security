<?php
if (PHP_SAPI !== 'cli-server') { http_response_code(404); exit; }
$path=parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);
if(preg_match('~^/product/([^/]+)$~',$path,$m)) {$_GET['slug']=rawurldecode($m[1]);require dirname(__DIR__).'/product.php';return true;}
if(in_array($path,['/cctv-camera-installation-dha-karachi','/cctv-camera-installation-dha-karachi/'],true)){require dirname(__DIR__).'/cctv-camera-installation-dha-karachi.php';return true;}
if(in_array($path,['/home-cctv-installation-karachi','/home-cctv-installation-karachi/'],true)){require dirname(__DIR__).'/home-cctv-installation-karachi.php';return true;}
if(in_array($path,['/office-cctv-installation-karachi','/office-cctv-installation-karachi/'],true)){require dirname(__DIR__).'/office-cctv-installation-karachi.php';return true;}
if(in_array($path,['/shop-cctv-installation-karachi','/shop-cctv-installation-karachi/'],true)){require dirname(__DIR__).'/shop-cctv-installation-karachi.php';return true;}
if($path==='/sitemap.xml'){require dirname(__DIR__).'/sitemap.php';return true;}
if($path==='/robots.txt'){require dirname(__DIR__).'/robots.php';return true;}
if($path==='/'||is_file(dirname(__DIR__).$path)) return false;
require dirname(__DIR__).'/includes/404.php';
