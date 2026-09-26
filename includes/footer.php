<?php // includes/footer.php ?>
</main><!-- /#main-content -->

<!-- CTA SECTION -->
<section class="cta-sec" aria-label="Call to action">
  <div class="container text-center position-relative" style="z-index:1">
    <p class="section-tag justify-content-center" style="color:rgba(230,57,70,.7)">Free No-Obligation Survey</p>
    <h2>SECURE YOUR PROPERTY TODAY<br>IN <span style="color:var(--accent)">KARACHI</span></h2>
    <p>Call or WhatsApp now for a free site survey. Same day installation available!</p>
    <div class="d-flex gap-3 justify-content-center flex-wrap">
      <a href="tel:+923091243189" class="btn-red"><i class="fa-solid fa-phone"></i> Call: 0309-1243189</a>
      <a href="https://wa.me/923091243189" class="btn-ghost"><i class="fa-brands fa-whatsapp"></i> WhatsApp Now</a>
    </div>
  </div>
</section>

<!-- FOOTER -->
<footer role="contentinfo">
  <div class="container">
    <div class="row g-4">
      <div class="col-lg-4">
        <div class="footer-brand">
          <div class="brand-icon"><i class="fa-solid fa-camera"></i></div>
          UltraNet <span>Security</span>
        </div>
        <p>Karachi's most trusted CCTV installation company. Protecting homes, offices and businesses with genuine, professional security solutions since 2014.</p>
        <p class="mt-2"><i class="fa-solid fa-location-dot me-2" style="color:var(--accent)"></i>House 239, Manzoor Colony, Hill Town, Karachi 75460</p>
        <div class="social-icons">
          <a href="https://wa.me/923091243189" class="social-icon" aria-label="WhatsApp"><i class="fa-brands fa-whatsapp"></i></a>
        </div>
      </div>
      <div class="col-sm-6 col-lg-2">
        <div class="footer-heading">Services</div>
        <ul class="footer-links">
          <li><a href="<?= SITE_URL ?>/home-cctv-installation-karachi"><i class="fa-solid fa-chevron-right"></i> Home CCTV</a></li>
          <li><a href="<?= SITE_URL ?>/office-cctv-installation-karachi"><i class="fa-solid fa-chevron-right"></i> Office CCTV</a></li>
          <li><a href="<?= SITE_URL ?>/shop-cctv-installation-karachi"><i class="fa-solid fa-chevron-right"></i> Shop CCTV</a></li>
          <li><a href="<?= SITE_URL ?>/#services"><i class="fa-solid fa-chevron-right"></i> AMC Services</a></li>
          <li><a href="<?= SITE_URL ?>/cctv-camera-installation-dha-karachi"><i class="fa-solid fa-chevron-right"></i> CCTV in DHA Karachi</a></li>
        </ul>
      </div>
      <div class="col-sm-6 col-lg-2">
        <div class="footer-heading">Products</div>
        <ul class="footer-links">
          <?php
          $footerCats = getFeaturedCategories();
          foreach ($footerCats as $fc): ?>
            <li>
              <a href="<?= SITE_URL ?>/products.php?category=<?= h($fc['slug']) ?>">
                <i class="fa-solid fa-chevron-right"></i> <?= h($fc['name']) ?>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
      <div class="col-lg-4">
        <div class="footer-heading">Contact Us</div>
        <ul class="footer-links">
          <li><a href="tel:+923091243189"><i class="fa-solid fa-phone"></i> 0309-1243189</a></li>
          <li><a href="https://wa.me/923091243189"><i class="fa-brands fa-whatsapp"></i> WhatsApp Us Now</a></li>
          <li><a href="https://maps.app.goo.gl/gEhaSYQ8Da8jSbym9" target="_blank"><i class="fa-solid fa-map-location-dot"></i> View on Google Maps</a></li>
          <li><a href="<?= SITE_URL ?>/#contact"><i class="fa-solid fa-clock"></i> Mon–Sun: 8am – 10pm</a></li>
        </ul>
      </div>
    </div>
    <div class="footer-bottom">
      <p style="margin:0">&copy; <?= date('Y') ?> UltraNet Security Karachi. All Rights Reserved.</p>
      <p style="margin:0">
        <a href="<?= SITE_URL ?>/privacy-policy.php">Privacy Policy</a> &middot;
        <a href="<?= SITE_URL ?>/sitemap.xml">Sitemap</a> &middot;
        CCTV Installation Karachi
      </p>
    </div>
  </div>
</footer>

<!-- SITEWIDE CONTACT SHORTCUTS -->
<div class="wa-float">
  <a href="<?= SITE_URL ?>/calculator.php" class="calc-float-btn" aria-label="Open CCTV package calculator">
    <i class="fa-solid fa-calculator" aria-hidden="true"></i><span>Plan My CCTV</span>
  </a>
  <a href="https://wa.me/923091243189" class="wa-btn" aria-label="WhatsApp Chat">
    <i class="fa-brands fa-whatsapp"></i>
  </a>
  <div class="wa-tooltip"><i class="fa-brands fa-whatsapp me-1"></i> Chat on WhatsApp</div>
</div>

<div class="lead-modal" id="visitorLeadModal" role="dialog" aria-modal="true" aria-labelledby="visitorLeadTitle" aria-describedby="visitorLeadDescription" hidden>
  <div class="lead-modal-backdrop" data-lead-close></div>
  <div class="lead-modal-panel" tabindex="-1">
    <button type="button" class="lead-modal-close" data-lead-close aria-label="Close contact dialog"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
    <div class="lead-modal-icon"><i class="fa-solid fa-shield-halved" aria-hidden="true"></i></div>
    <p class="lead-modal-kicker">UltraNet Security · Karachi</p>
    <h2 id="visitorLeadTitle">Need help choosing CCTV?</h2>
    <p id="visitorLeadDescription">Get advice for your home or office. Leave your phone number or email and our team will contact you.</p>
    <a class="lead-modal-whatsapp" href="https://wa.me/923091243189" target="_blank" rel="noopener noreferrer" id="leadWhatsApp"><i class="fa-brands fa-whatsapp" aria-hidden="true"></i> Chat on WhatsApp</a>
    <div class="lead-modal-divider"><span>or request a callback</span></div>
    <form id="visitorLeadForm" action="<?= SITE_URL ?>/quick-lead-submit.php" method="post">
      <?php csrfField(); ?>
      <div class="lead-modal-honeypot" aria-hidden="true"><label for="lead-website">Leave this empty</label><input id="lead-website" type="text" name="website" tabindex="-1" autocomplete="off"></div>
      <label for="lead-phone">Phone / WhatsApp number</label>
      <input id="lead-phone" type="tel" name="phone" autocomplete="tel" inputmode="tel" maxlength="30" placeholder="03XX XXXXXXX">
      <label for="lead-email">Email address</label>
      <input id="lead-email" type="email" name="email" autocomplete="email" maxlength="150" placeholder="you@example.com">
      <p class="lead-modal-hint">Enter at least one: phone or email.</p>
      <p id="visitorLeadError" class="lead-modal-error" role="alert" hidden></p>
      <button class="lead-modal-submit" type="submit">Send my contact details <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></button>
    </form>
    <p class="lead-modal-privacy">We will only use your details to reply to your enquiry. <a href="<?= SITE_URL ?>/privacy-policy.php">Privacy Policy</a></p>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= ASSETS_URL ?>/js/main.js?v=<?= filemtime(__DIR__.'/../assets/js/main.js') ?>"></script>
</body>
</html>
