<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle    = 'CCTV Installation Karachi | UltraNet Security';
$metaDesc     = 'Professional CCTV installation in Karachi by UltraNet Security. Hikvision & Dahua authorized installer for homes, offices & shops. Call 0309-1243189.';
$metaKeywords = 'CCTV installation Karachi, security camera Karachi, Hikvision Karachi, Dahua Karachi, CCTV Manzoor Colony';
$canonicalSlug = '/';

$homeFaqs = [
    ['How much does CCTV installation cost in Karachi?','CCTV installation starts from PKR 15,000 for a 4-camera home package. Contact us at 0309-1243189 for a free site survey and exact quote.'],
    ['Which CCTV brand is best for home in Karachi?','Hikvision and Dahua are the top two brands we recommend. Both offer excellent image quality and local after-sales support.'],
    ['Can I view CCTV cameras on my mobile phone?','Yes! All our systems come with free mobile app setup — Hik-Connect, DMSS or iVMS-4500 on Android and iOS.'],
    ['Do you provide CCTV maintenance in Karachi?','Yes, we offer Annual Maintenance Contracts (AMC) including regular checkups, DVR/NVR servicing, and 24/7 support.'],
    ['How long does CCTV installation take in Karachi?','A standard 4–8 camera home installation is completed in one day. Larger projects are scheduled per timeline.'],
];
$extraSchema = [schemaFaqPage($homeFaqs)];

$featuredProducts = getProducts(['featured' => true]);
$categories       = getAllCategories();

include __DIR__ . '/includes/header.php';
?>

<!-- HERO -->
<section id="home" class="hero" aria-label="Hero section">
  <img class="hero-bg-img" src="<?= ASSETS_URL ?>/img/hero-bg.jpg" alt="CCTV security camera installation background Karachi" width="1600" height="900" loading="eager">
  <div class="hero-overlay"></div>
  <div class="container">
    <div class="row align-items-center g-4">
      <div class="col-lg-7 hero-content">
        <div class="hero-badge"><i class="fa-solid fa-shield-halved"></i> KARACHI'S TRUSTED CCTV EXPERTS</div>
        <h1>PROTECT<br>WHAT <span>MATTERS</span></h1>
        <p class="hero-sub">MOST</p>
        <p class="hero-lead">Professional CCTV installation for homes, offices, shops & factories across Karachi. Hikvision & Dahua authorized installer.<br><strong style="color:#fff">Call: 0309-1243189</strong></p>
        <div class="d-flex gap-3 flex-wrap">
          <a href="<?= SITE_URL ?>/products.php" class="btn-red"><i class="fa-solid fa-camera"></i> View Products</a>
          <a href="https://wa.me/923091243189" class="btn-ghost"><i class="fa-brands fa-whatsapp"></i> WhatsApp Us</a>
        </div>
        <div class="hero-stats">
          <div class="hero-stat"><div class="num">2500<em>+</em></div><div class="lbl">Cameras Installed</div></div>
          <div class="hero-stat"><div class="num">800<em>+</em></div><div class="lbl">Happy Clients</div></div>
          <div class="hero-stat"><div class="num">10<em>+</em></div><div class="lbl">Years Experience</div></div>
          <div class="hero-stat"><div class="num">24<em>/7</em></div><div class="lbl">Support</div></div>
        </div>
      </div>
      <div class="col-lg-5 hero-img-panel">
        <div class="hero-img-wrap">
          <img src="<?= ASSETS_URL ?>/img/hero-camera.jpg" alt="Professional CCTV camera Karachi" width="800" height="600" loading="eager">
          <div class="live-overlay"><span class="live-dot"></span><span>LIVE MONITORING</span></div>
          <div class="hero-cam-badge"><span class="big">2500+</span><span class="sm">CAMERAS INSTALLED</span></div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- TRUST BAR -->
<div class="trust-bar">
  <div class="container">
    <div class="d-flex flex-wrap justify-content-center gap-4">
      <div class="trust-item"><i class="fa-solid fa-certificate"></i> Hikvision Authorized Partner</div>
      <div class="trust-item"><i class="fa-solid fa-award"></i> Dahua Certified Installer</div>
      <div class="trust-item"><i class="fa-solid fa-shield-halved"></i> 1 Year Warranty</div>
      <div class="trust-item"><i class="fa-solid fa-screwdriver-wrench"></i> Free Site Survey</div>
      <div class="trust-item"><i class="fa-solid fa-bolt"></i> Same Day Installation</div>
    </div>
  </div>
</div>

<!-- CATEGORIES -->
<section class="py-5" style="background:var(--light-bg)">
  <div class="container">
    <div class="text-center mb-4 reveal">
      <p class="section-tag justify-content-center">Shop By Category</p>
      <h2 class="section-title">PRODUCT <span style="color:var(--accent)">CATEGORIES</span></h2>
    </div>
    <div class="row g-3 justify-content-center">
      <?php foreach ($categories as $cat): ?>
      <div class="col-6 col-md-3 reveal">
        <a href="<?= SITE_URL ?>/products.php?category=<?= h($cat['slug']) ?>" class="cat-card text-decoration-none">
          <div class="cat-icon"><i class="<?= h($cat['icon']) ?>"></i></div>
          <div class="cat-name"><?= h($cat['name']) ?></div>
          <?php if ($cat['description']): ?>
          <div class="cat-desc"><?= h($cat['description']) ?></div>
          <?php endif; ?>
        </a>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- FEATURED PRODUCTS -->
<?php if ($featuredProducts): ?>
<section class="py-5">
  <div class="container">
    <div class="d-flex justify-content-between align-items-end mb-4 reveal">
      <div>
        <p class="section-tag">Top Picks</p>
        <h2 class="section-title mb-0">FEATURED <span style="color:var(--accent)">PRODUCTS</span></h2>
      </div>
      <a href="<?= SITE_URL ?>/products.php" class="btn-red d-none d-md-inline-flex" style="padding:10px 20px;font-size:14px">
        View All <i class="fa-solid fa-arrow-right ms-1"></i>
      </a>
    </div>
    <div class="row g-4">
      <?php foreach ($featuredProducts as $p): ?>
      <div class="col-6 col-md-4 col-lg-3 reveal">
        <?php include __DIR__ . '/includes/product-card.php'; ?>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="text-center mt-4 d-md-none reveal">
      <a href="<?= SITE_URL ?>/products.php" class="btn-red">View All Products</a>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- SERVICES -->
<!-- CALCULATOR PROMO BANNER (reuses existing cta-sec style) -->
<section class="cta-sec" aria-label="CCTV Calculator promo" style="padding:56px 0">
  <div class="container text-center position-relative" style="z-index:1">
    <p class="section-tag justify-content-center" style="color:rgba(230,57,70,.7)">Free Tool</p>
    <h2 style="font-size:clamp(28px,4vw,42px)">NOT SURE WHAT <span style="color:var(--accent)">YOU NEED?</span></h2>
    <p>Use our free CCTV System Calculator — enter your requirements and get an instant package estimate with real pricing.</p>
    <a href="<?= SITE_URL ?>/calculator.php" class="btn-red d-inline-flex">
      <i class="fa-solid fa-calculator"></i> Try the CCTV Calculator
    </a>
  </div>
</section>

<section id="services" class="services" aria-label="CCTV Services Karachi">
  <div class="container">
    <div class="text-center mb-5 reveal">
      <p class="section-tag justify-content-center">What We Offer</p>
      <h2 class="section-title">CCTV Services in <span style="color:var(--accent)">Karachi</span></h2>
      <p class="section-sub mx-auto">Complete security camera installation and maintenance for homes, offices and businesses across all Karachi.</p>
    </div>
    <div class="row g-4">
      <?php
      $services = [
        ['img'=>'service-home.jpg',   'tag'=>'HOME SECURITY',    'icon'=>'fa-house',        'title'=>'Home CCTV Installation',       'desc'=>'Secure your home with HD cameras, night vision, mobile app viewing. Packages from PKR 15,000.'],
        ['img'=>'service-office.jpg', 'tag'=>'OFFICE SECURITY',  'icon'=>'fa-building',     'title'=>'Office & Commercial CCTV',     'desc'=>'Multi-camera systems for offices, plazas & warehouses with NVR/DVR and remote monitoring.'],
        ['img'=>'service-shop.jpg',   'tag'=>'RETAIL SECURITY',  'icon'=>'fa-shop',         'title'=>'Shop & Retail Security',       'desc'=>'POS area monitoring, anti-theft cameras, entry/exit security for shops and retail stores.'],
        ['img'=>'service-amc.jpg',    'tag'=>'AMC SERVICE',      'icon'=>'fa-gear',         'title'=>'CCTV AMC & Maintenance',       'desc'=>'Annual Maintenance Contracts with regular checkups, DVR servicing and 24/7 emergency support.'],
        ['img'=>'service-ip.jpg',     'tag'=>'IP & WIRELESS',    'icon'=>'fa-wifi',         'title'=>'IP & Wireless Camera Systems', 'desc'=>'PoE IP cameras, wireless installation, PTZ cameras, AI cameras with face detection.'],
        ['img'=>'service-access.jpg', 'tag'=>'ACCESS CONTROL',   'icon'=>'fa-door-closed',  'title'=>'Access Control Systems',       'desc'=>'Biometric attendance, card access, electric door locks and video door phones.'],
      ];
      foreach ($services as $svc): ?>
      <div class="col-md-6 col-lg-4 reveal">
        <article class="svc-card">
          <div class="svc-img">
            <img src="<?= ASSETS_URL ?>/img/<?= h($svc['img']) ?>" alt="<?= h($svc['title']) ?> Karachi" width="600" height="400" loading="lazy">
            <div class="svc-img-overlay"></div>
            <div class="svc-img-tag"><?= h($svc['tag']) ?></div>
          </div>
          <div class="svc-body">
            <div class="svc-icon"><i class="fa-solid <?= h($svc['icon']) ?>"></i></div>
            <h3><?= h($svc['title']) ?></h3>
            <p><?= h($svc['desc']) ?></p>
            <a href="<?= SITE_URL ?>/#contact" class="learn">Get Free Quote <i class="fa-solid fa-arrow-right"></i></a>
          </div>
        </article>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- WHY US -->
<section class="why-us">
  <div class="container">
    <div class="row align-items-center g-5">
      <div class="col-lg-5 reveal">
        <div class="why-img-wrap">
          <img src="<?= ASSETS_URL ?>/img/why-us.jpg" alt="CCTV security monitoring room Karachi" width="700" height="900" loading="lazy">
          <div class="why-img-badge"><div class="big">10+</div><div class="sm">YEARS IN KARACHI</div></div>
        </div>
      </div>
      <div class="col-lg-7">
        <p class="section-tag reveal">Why UltraNet Security?</p>
        <h2 class="section-title reveal">KARACHI'S #1<br><span style="color:var(--accent)">CCTV INSTALLER</span></h2>
        <p class="section-sub reveal">We don't just sell cameras — we deliver complete peace of mind with expert installation, genuine products and lifelong support.</p>
        <div class="mt-3">
          <?php
          $whys = [
            ['fa-certificate','100% Genuine Products','Original Hikvision, Dahua & CP Plus cameras with official warranty.'],
            ['fa-users','Expert Technicians','Certified engineers with 10+ years of field experience.'],
            ['fa-bolt','Same Day Installation','Residential CCTV systems completed in 1 day.'],
            ['fa-headset','24/7 After-Sales Support','Round-the-clock remote and on-site support.'],
            ['fa-tags','Best Market Prices','Competitive pricing with zero hidden charges.'],
            ['fa-shield-halved','1 Year Warranty','Full 1-year warranty on workmanship & equipment.'],
          ];
          foreach ($whys as [$ico, $title, $desc]): ?>
          <div class="why-card reveal">
            <h4><i class="fa-solid <?= $ico ?>"></i> <?= $title ?></h4>
            <p><?= $desc ?></p>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- BRANDS -->
<section class="brands">
  <div class="container">
    <div class="text-center mb-4 reveal">
      <p class="section-tag justify-content-center">Brands We Install</p>
      <h2 class="section-title" style="font-size:36px">AUTHORIZED PARTNERS</h2>
    </div>
    <div class="row g-3 justify-content-center reveal">
      <?php foreach (['HIKVISION','DAHUA','CP PLUS','UNIVIEW','AXIS','HANWHA'] as $b): ?>
      <div class="col-6 col-md-2">
        <a href="<?= SITE_URL ?>/search.php?q=<?= urlencode($b) ?>" class="brand-box text-decoration-none"><?= $b ?></a>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- COUNTERS -->
<section class="counter-section">
  <div class="container">
    <div class="row g-4 text-center">
      <?php
      $counters = [['2500','Cameras Installed','fa-video'],['800','Satisfied Clients','fa-face-smile'],['10','Years Experience','fa-calendar'],['50','Areas Covered','fa-map-location-dot']];
      foreach ($counters as [$n,$l,$i]): ?>
      <div class="col-6 col-md-3 reveal">
        <div class="counter-item">
          <div class="counter-num" data-target="<?= $n ?>">0<em>+</em></div>
          <div class="counter-label"><i class="fa-solid <?= $i ?> me-1" style="color:var(--accent)"></i><?= $l ?></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- AREAS -->
<section id="areas" class="areas">
  <div class="container">
    <div class="text-center mb-5 reveal">
      <p class="section-tag justify-content-center">Coverage</p>
      <h2 class="section-title">WE SERVE ALL <span style="color:var(--accent)">KARACHI</span></h2>
    </div>
    <div class="d-flex flex-wrap gap-3 justify-content-center reveal">
      <?php foreach (['DHA Karachi','Clifton','Gulshan-e-Iqbal','PECHS','North Nazimabad','Gulistan-e-Johar','Manzoor Colony','Hill Town','Korangi','Malir','Landhi','Federal B Area','Nazimabad','Orangi Town','Saddar','Scheme 33','Bahria Town','Surjani Town','Keamari','Site Area'] as $area): ?>
      <span class="area-tag"><i class="fa-solid fa-location-dot"></i> <?= h($area) ?></span>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- FAQ -->
<section id="faq" class="faq">
  <div class="container">
    <div class="row align-items-start g-5">
      <div class="col-lg-5 reveal">
        <p class="section-tag">FAQ</p>
        <h2 class="section-title">COMMON <span style="color:var(--accent)">QUESTIONS</span></h2>
        <p class="section-sub">Everything you need to know about CCTV installation in Karachi.</p>
        <a href="https://wa.me/923091243189" class="btn-red mt-4 d-inline-flex"><i class="fa-brands fa-whatsapp"></i> Ask on WhatsApp</a>
      </div>
      <div class="col-lg-7 reveal">
        <div class="accordion" id="faqAcc">
          <?php
          $faqs = $homeFaqs;
          foreach ($faqs as $i => [$q,$a]): ?>
          <div class="accordion-item">
            <h3 class="accordion-header">
              <button class="accordion-button <?= $i>0?'collapsed':'' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#f<?= $i ?>">
                <?= h($q) ?>
              </button>
            </h3>
            <div id="f<?= $i ?>" class="accordion-collapse collapse <?= $i===0?'show':'' ?>" data-bs-parent="#faqAcc">
              <div class="accordion-body" style="font-size:14px;color:var(--muted)"><?= h($a) ?></div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- CONTACT -->
<section id="contact" class="contact">
  <div class="container">
    <div class="text-center mb-5 reveal">
      <p class="section-tag justify-content-center">Get In Touch</p>
      <h2 class="section-title">CONTACT <span style="color:var(--accent)">US</span></h2>
      <p class="section-sub mx-auto">Get a free quote for CCTV installation anywhere in Karachi. We respond within 1 hour.</p>
    </div>
    <div class="row g-4">
      <div class="col-lg-5 reveal">
        <div class="contact-card">
          <?php
          $contactItems = [
            ['fa-phone','Phone / WhatsApp','<a href="tel:+923091243189" style="color:var(--primary);font-weight:600;text-decoration:none">0309-1243189</a>'],
            ['fa-location-dot','Our Office','House No 239, Manzoor Colony<br>Hill Town St 10, Karachi – 75460'],
            ['fa-clock','Working Hours','Monday – Sunday: 8:00 AM – 10:00 PM'],
            ['fa-brands fa-whatsapp','WhatsApp','<a href="https://wa.me/923091243189" style="color:var(--accent);font-weight:600;text-decoration:none">Chat with us instantly →</a>'],
          ];
          foreach ($contactItems as [$ico,$title,$val]): ?>
          <div class="c-item">
            <div class="c-icon"><i class="<?= strpos($ico,'fa-brands')===false?'fa-solid ':'' ?><?= $ico ?>"></i></div>
            <div><h5><?= $title ?></h5><p><?= $val ?></p></div>
          </div>
          <?php endforeach; ?>
          <div class="map-wrap">
            <iframe src="https://maps.google.com/maps?q=Manzoor+Colony+Hill+Town+Karachi&t=&z=15&ie=UTF8&iwloc=&output=embed"
              width="100%" height="220" style="border:0;display:block" allowfullscreen="" loading="lazy"
              title="UltraNet Security location"></iframe>
          </div>
          <a href="https://maps.app.goo.gl/gEhaSYQ8Da8jSbym9" target="_blank" rel="noopener"
             class="btn-red mt-3 d-flex justify-content-center" style="font-size:14px">
            <i class="fa-solid fa-map-location-dot"></i> Open in Google Maps
          </a>
        </div>
      </div>
      <div class="col-lg-7 reveal">
        <div class="contact-card">
          <h4 class="mb-4" style="font-size:21px"><i class="fa-solid fa-paper-plane me-2" style="color:var(--accent)"></i>Get Free Quote</h4>
          <form id="contactForm" data-action="<?= SITE_URL ?>/contact-submit.php" novalidate>
            <div class="row g-3">
              <div class="col-md-6"><input type="text" name="name" class="form-control" placeholder="Your Full Name *" required></div>
              <div class="col-md-6"><input type="tel" name="phone" class="form-control" placeholder="Phone / WhatsApp *" required></div>
              <div class="col-md-6"><input type="email" name="email" class="form-control" placeholder="Email Address (optional)"></div>
              <div class="col-md-6">
                <select name="service" class="form-select">
                  <option value="">Service Required</option>
                  <?php foreach (['Home CCTV Installation','Office / Commercial CCTV','Shop Security System','IP Camera System','CCTV Maintenance / AMC','Access Control','Other'] as $s): ?>
                  <option><?= h($s) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-6">
                <select name="area" class="form-select">
                  <option value="">Select Your Area</option>
                  <?php foreach (['DHA Karachi','Clifton','Gulshan-e-Iqbal','PECHS','North Nazimabad','Manzoor Colony / Hill Town','Other Karachi Area'] as $a): ?>
                  <option><?= h($a) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-6">
                <select name="cameras" class="form-select">
                  <option value="">No. of Cameras</option>
                  <?php foreach (['2–4 Cameras','4–8 Cameras','8–16 Cameras','16+ Cameras','Not Sure'] as $c): ?>
                  <option><?= h($c) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-12"><textarea name="message" class="form-control" rows="3" placeholder="Additional details or questions..."></textarea></div>
              <!-- Honeypot spam trap — hidden from real users -->
              <div style="position:absolute;left:-9999px" aria-hidden="true">
                <input type="text" name="website" tabindex="-1" autocomplete="off">
              </div>
              <div class="col-12">
                <button type="submit" class="btn-submit" id="contactSubmitBtn"><i class="fa-solid fa-paper-plane"></i> Send Enquiry</button>
              </div>
            </div>
          </form>
          <div id="successMsg" role="status" style="display:none;background:#d4edda;color:#155724;padding:16px;border-radius:10px;margin-top:14px;font-weight:600;text-align:center">
            <i class="fa-solid fa-circle-check me-2"></i> Thank you! We'll contact you within 1 hour on WhatsApp.
          </div>
          <div id="errorMsg" role="alert" style="display:none;background:#fdf3f3;color:#c0392b;padding:14px;border-radius:10px;margin-top:14px;font-weight:600;text-align:center;font-size:14px"></div>
        </div>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
