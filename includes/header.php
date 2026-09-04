<?php
// includes/header.php — Called at the top of every public page
// Variables expected (with defaults):
//   $pageTitle       string  – <title> tag content
//   $metaDesc        string  – meta description
//   $metaKeywords    string  – meta keywords
//   $canonicalSlug   string  – appended to SITE_URL for canonical
//   $bodyClass       string  – extra class on <body>

$pageTitle    = $pageTitle    ?? (SITE_NAME . ' | CCTV Installation Karachi');
$metaDesc     = $metaDesc     ?? 'UltraNet Security – Professional CCTV installation in Karachi. Hikvision & Dahua authorized installer. Call 0309-1243189.';
$metaKeywords = $metaKeywords ?? 'CCTV installation Karachi, security camera Karachi, Hikvision, Dahua, UltraNet Security';
$canonicalUrl = SITE_URL . ($canonicalSlug ?? '/');
$bodyClass    = $bodyClass    ?? '';
$metaRobots   = $metaRobots   ?? 'index, follow';
$ogType       = $ogType       ?? 'website';
$ogImage      = $ogImage      ?? (ASSETS_URL . '/img/og-image.jpg');
$extraSchema  = $extraSchema  ?? []; // array of JSON-LD arrays, page-specific (Product, FAQPage, Breadcrumb...)
$allCategories = getAllCategories();
$searchQuery  = h($_GET['q'] ?? '');
?>
<!DOCTYPE html>
<html lang="en-PK">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= h($pageTitle) ?></title>
  <meta name="description"  content="<?= h($metaDesc) ?>">
  <meta name="keywords"     content="<?= h($metaKeywords) ?>">
  <meta name="robots"       content="<?= h($metaRobots) ?>">
  <meta name="googlebot"    content="<?= h($metaRobots) ?>">
  <meta name="author"       content="UltraNet Security Karachi">
  <meta name="geo.region"   content="PK-SD">
  <meta name="geo.placename" content="Karachi">
  <meta name="geo.position" content="24.8607;67.0011">
  <meta name="ICBM"         content="24.8607, 67.0011">
  <link rel="canonical"     href="<?= h($canonicalUrl) ?>">

  <!-- Open Graph -->
  <meta property="og:type"        content="<?= h($ogType) ?>">
  <meta property="og:title"       content="<?= h($pageTitle) ?>">
  <meta property="og:description" content="<?= h($metaDesc) ?>">
  <meta property="og:url"         content="<?= h($canonicalUrl) ?>">
  <meta property="og:image"       content="<?= h($ogImage) ?>">
  <meta property="og:site_name"   content="<?= SITE_NAME ?>">
  <meta property="og:locale"      content="en_PK">

  <!-- Twitter Card -->
  <meta name="twitter:card"        content="summary_large_image">
  <meta name="twitter:title"       content="<?= h($pageTitle) ?>">
  <meta name="twitter:description" content="<?= h($metaDesc) ?>">
  <meta name="twitter:image"       content="<?= h($ogImage) ?>">

  <!-- Favicons -->
  <link rel="icon"             type="image/png" sizes="32x32" href="<?= ASSETS_URL ?>/img/favicon-32.png">
  <link rel="icon"             type="image/png" sizes="16x16" href="<?= ASSETS_URL ?>/img/favicon-16.png">
  <link rel="apple-touch-icon" sizes="180x180"                href="<?= ASSETS_URL ?>/img/favicon-180.png">
  <link rel="manifest"         href="<?= SITE_URL ?>/site.webmanifest">
  <meta name="theme-color"    content="#e63946">

  <!-- Preconnect -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="preconnect" href="https://cdn.jsdelivr.net">

  <!-- Bootstrap + FA + Fonts -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">

  <!-- Main CSS -->
  <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/style.css">

  <!-- ══════════ STRUCTURED DATA: LocalBusiness (sitewide) ══════════ -->
  <script type="application/ld+json">
  <?= json_encode([
    "@context" => "https://schema.org",
    "@type" => "LocalBusiness",
    "@id" => SITE_URL . "/#business",
    "name" => "UltraNet Security",
    "image" => ASSETS_URL . "/img/og-image.jpg",
    "url" => SITE_URL . "/",
    "telephone" => "+92-309-1243189",
    "priceRange" => "PKR 15,000 - PKR 200,000",
    "address" => [
      "@type" => "PostalAddress",
      "streetAddress" => "House No 239, Manzoor Colony, Hill Town St 10",
      "addressLocality" => "Karachi",
      "addressRegion" => "Sindh",
      "postalCode" => "75460",
      "addressCountry" => "PK"
    ],
    "geo" => ["@type" => "GeoCoordinates", "latitude" => "24.8607", "longitude" => "67.0011"],
    "openingHoursSpecification" => [
      "@type" => "OpeningHoursSpecification",
      "dayOfWeek" => ["Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday"],
      "opens" => "08:00", "closes" => "22:00"
    ],
    "areaServed" => ["Karachi","DHA Karachi","Clifton","Gulshan-e-Iqbal","PECHS","North Nazimabad","Manzoor Colony"],
    "sameAs" => ["https://wa.me/923091243189"]
  ], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>
  </script>

  <!-- ══════════ STRUCTURED DATA: page-specific (Product / FAQ / Breadcrumb) ══════════ -->
  <?php foreach ($extraSchema as $schemaBlock): ?>
  <script type="application/ld+json">
  <?= json_encode($schemaBlock, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>
  </script>
  <?php endforeach; ?>
</head>
<body class="<?= h($bodyClass) ?>">

<a href="#main-content" class="skip-link">Skip to main content</a>

<!-- TOPBAR -->
<div class="topbar">
  <div class="container">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
      <div class="d-flex gap-3 flex-wrap">
        <a href="tel:+923091243189"><i class="fa-solid fa-phone me-1"></i>0309-1243189</a>
        <a href="https://wa.me/923091243189"><i class="fa-brands fa-whatsapp me-1"></i>WhatsApp</a>
      </div>
      <div class="d-flex gap-3 flex-wrap">
        <span><i class="fa-solid fa-clock me-1"></i>Mon–Sun: 8am – 10pm</span>
        <span><i class="fa-solid fa-location-dot me-1"></i>Manzoor Colony, Karachi</span>
      </div>
    </div>
  </div>
</div>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg" aria-label="Main navigation">
  <div class="container">
    <a class="navbar-brand" href="<?= SITE_URL ?>/">
      <div class="brand-icon"><i class="fa-solid fa-camera"></i></div>
      UltraNet <span>Security</span>
    </a>
    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu" aria-label="Toggle navigation">
      <i class="fa-solid fa-bars fs-4 text-dark"></i>
    </button>
    <div class="collapse navbar-collapse" id="navMenu">
      <ul class="navbar-nav me-auto align-items-lg-center">
        <li class="nav-item"><a class="nav-link" href="<?= SITE_URL ?>/">Home</a></li>
        <!-- Products dropdown -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="<?= SITE_URL ?>/products.php" id="prodDrop" role="button" data-bs-toggle="dropdown">Products</a>
          <ul class="dropdown-menu" aria-labelledby="prodDrop">
            <li><a class="dropdown-item" href="<?= SITE_URL ?>/products.php">All Products</a></li>
            <li><hr class="dropdown-divider"></li>
            <?php foreach ($allCategories as $cat): ?>
              <li>
                <a class="dropdown-item" href="<?= SITE_URL ?>/products.php?category=<?= h($cat['slug']) ?>">
                  <i class="<?= h($cat['icon']) ?> me-2 text-danger"></i><?= h($cat['name']) ?>
                </a>
              </li>
            <?php endforeach; ?>
          </ul>
        </li>
        <li class="nav-item"><a class="nav-link" href="<?= SITE_URL ?>/#services">Services</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= SITE_URL ?>/calculator.php"><i class="fa-solid fa-calculator me-1"></i>Calculator</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= SITE_URL ?>/#areas">Areas</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= SITE_URL ?>/#contact">Contact</a></li>
      </ul>
      <!-- Search form -->
      <form class="d-flex me-2" action="<?= SITE_URL ?>/search.php" method="GET" role="search">
        <div class="input-group nav-search">
          <input type="text" name="q" class="form-control" placeholder="Search cameras, NVR, DVR…"
                 value="<?= $searchQuery ?>" aria-label="Search products">
          <button class="btn btn-search" type="submit" aria-label="Search">
            <i class="fa-solid fa-magnifying-glass"></i>
          </button>
        </div>
      </form>
      <a class="nav-link cta-btn" href="tel:+923091243189">
        <i class="fa-solid fa-phone me-1"></i>Call Now
      </a>
    </div>
  </div>
</nav>

<main id="main-content">
