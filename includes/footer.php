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
          <a href="#" class="social-icon" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
          <a href="https://wa.me/923091243189" class="social-icon" aria-label="WhatsApp"><i class="fa-brands fa-whatsapp"></i></a>
          <a href="#" class="social-icon" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
          <a href="#" class="social-icon" aria-label="YouTube"><i class="fa-brands fa-youtube"></i></a>
        </div>
      </div>
      <div class="col-sm-6 col-lg-2">
        <div class="footer-heading">Services</div>
        <ul class="footer-links">
          <li><a href="<?= SITE_URL ?>/#services"><i class="fa-solid fa-chevron-right"></i> Home CCTV</a></li>
          <li><a href="<?= SITE_URL ?>/#services"><i class="fa-solid fa-chevron-right"></i> Office CCTV</a></li>
          <li><a href="<?= SITE_URL ?>/#services"><i class="fa-solid fa-chevron-right"></i> Shop Security</a></li>
          <li><a href="<?= SITE_URL ?>/#services"><i class="fa-solid fa-chevron-right"></i> AMC Services</a></li>
        </ul>
      </div>
      <div class="col-sm-6 col-lg-2">
        <div class="footer-heading">Products</div>
        <ul class="footer-links">
          <?php
          $footerCats = getAllCategories();
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

<!-- FLOATING WHATSAPP -->
<div class="wa-float">
  <a href="https://wa.me/923091243189" class="wa-btn" aria-label="WhatsApp Chat">
    <i class="fa-brands fa-whatsapp"></i>
  </a>
  <div class="wa-tooltip"><i class="fa-brands fa-whatsapp me-1"></i> Chat on WhatsApp</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= ASSETS_URL ?>/js/main.js"></script>
</body>
</html>
