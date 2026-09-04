<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle    = 'Privacy Policy | UltraNet Security Karachi';
$metaDesc     = 'Privacy Policy for UltraNet Security — how we collect, use, and protect your information when you visit our website or request a CCTV installation quote.';
$canonicalSlug = '/privacy-policy.php';

include __DIR__ . '/includes/header.php';
?>

<!-- PAGE HEADER -->
<section class="page-hero">
  <div class="container">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-2">
        <li class="breadcrumb-item"><a href="<?= SITE_URL ?>/">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">Privacy Policy</li>
      </ol>
    </nav>
    <h1 class="page-hero-title">Privacy Policy</h1>
    <p class="page-hero-sub">Last updated: <?= date('d F Y') ?></p>
  </div>
</section>

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-lg-9">

      <div class="prod-desc-content" style="line-height:1.85; font-size:15px;">

        <p>UltraNet Security ("we", "our", or "us") operates this website
        (<?= h(SITE_URL) ?>) to provide information about our CCTV
        installation services in Karachi, showcase our security products,
        and let visitors request quotes and estimates. This Privacy Policy
        explains what information we collect, how we use it, and the
        choices you have.</p>

        <p>By using this website or submitting a form on it, you agree to
        the collection and use of information as described in this
        policy.</p>

        <h3 class="mt-5 mb-3" style="font-family:'Bebas Neue',sans-serif; font-size:24px; color:var(--primary)">
          1. Information We Collect
        </h3>
        <p>We only collect information that you choose to give us. This
        happens in a few specific places on the site:</p>
        <ul>
          <li><strong>Contact / "Get Free Quote" form</strong> — name, phone
          number, email address (optional), the service you're interested
          in, your area in Karachi, number of cameras needed, and any
          message you write.</li>
          <li><strong>CCTV System Calculator</strong> — when you build a
          package and request an estimate, we collect your name, phone
          number, email address (optional), and the system requirements you
          entered (camera count, resolution, recording preferences, etc.)
          so we can prepare and send you an accurate quote.</li>
          <li><strong>Automatically collected data</strong> — like most
          websites, our server automatically logs basic technical
          information such as your IP address, browser type, and pages
          visited, for security and performance purposes (e.g. detecting
          spam or abuse).</li>
        </ul>
        <p>We do <strong>not</strong> ask for or store any payment card
        details, passwords for other services, or sensitive personal
        information through this website.</p>

        <h3 class="mt-5 mb-3" style="font-family:'Bebas Neue',sans-serif; font-size:24px; color:var(--primary)">
          2. How We Use Your Information
        </h3>
        <p>We use the information you provide only to:</p>
        <ul>
          <li>Respond to your enquiry and provide the quote or estimate you requested</li>
          <li>Contact you by phone, WhatsApp, or email about your enquiry</li>
          <li>Schedule a free site survey or installation, if you proceed</li>
          <li>Improve our services and the content on this website</li>
          <li>Keep basic records of enquiries for our own business purposes</li>
        </ul>
        <p>We do not sell, rent, or trade your personal information to
        third parties for marketing purposes.</p>

        <h3 class="mt-5 mb-3" style="font-family:'Bebas Neue',sans-serif; font-size:24px; color:var(--primary)">
          3. Third-Party Services We Use
        </h3>
        <p>To operate this website and respond to you, we rely on a small
        number of trusted third-party services. Each only receives the
        minimum information needed to do its job:</p>
        <ul>
          <li><strong>Google Maps</strong> — used to show our office
          location on the Contact page. Google may set its own cookies
          when the map loads; this is governed by
          <a href="https://policies.google.com/privacy" target="_blank" rel="noopener">Google's Privacy Policy</a>.</li>
          <li><strong>WhatsApp</strong> — our "Chat on WhatsApp" buttons
          open a conversation directly in WhatsApp. Any message you send
          us there is subject to
          <a href="https://www.whatsapp.com/legal/privacy-policy" target="_blank" rel="noopener">WhatsApp's Privacy Policy</a>.</li>
          <li><strong>Email delivery (Gmail SMTP)</strong> — when you
          submit the contact form or calculator, we use Gmail's SMTP
          service to send ourselves a notification email so we can follow
          up with you quickly.</li>
          <li><strong>Google Fonts &amp; content delivery networks</strong>
          — used to load fonts and icon libraries for the site design.</li>
        </ul>

        <h3 class="mt-5 mb-3" style="font-family:'Bebas Neue',sans-serif; font-size:24px; color:var(--primary)">
          4. Cookies
        </h3>
        <p>This website currently uses only essential cookies required for
        the site to function (for example, to remember your session when
        browsing). We do not currently use Google Analytics or advertising
        cookies.</p>
        <p>We may introduce analytics tools such as Google Analytics in
        the future to help us understand how visitors use the site and
        improve it. If we do, this Privacy Policy will be updated in
        advance, and — where required by law — we will ask for your
        consent via a cookie notice before any non-essential cookies are
        set. You can control or disable cookies at any time through your
        browser settings.</p>

        <h3 class="mt-5 mb-3" style="font-family:'Bebas Neue',sans-serif; font-size:24px; color:var(--primary)">
          5. Data Retention
        </h3>
        <p>We keep enquiry and quote request records for as long as
        reasonably necessary to respond to you and maintain our business
        records, or until you ask us to delete them (see Section 7
        below).</p>

        <h3 class="mt-5 mb-3" style="font-family:'Bebas Neue',sans-serif; font-size:24px; color:var(--primary)">
          6. How We Protect Your Information
        </h3>
        <p>We take reasonable technical and organisational measures to
        protect the information you share with us, including secure
        password practices, restricted admin access, and standard web
        security protections. However, no method of transmission over the
        internet is 100% secure, and we cannot guarantee absolute
        security.</p>

        <h3 class="mt-5 mb-3" style="font-family:'Bebas Neue',sans-serif; font-size:24px; color:var(--primary)">
          7. Your Rights
        </h3>
        <p>You can contact us at any time to:</p>
        <ul>
          <li>Ask what information we hold about you</li>
          <li>Ask us to correct inaccurate information</li>
          <li>Ask us to delete your information from our records</li>
          <li>Withdraw consent for us to contact you about a past enquiry</li>
        </ul>
        <p>To make any of these requests, contact us using the details in
        Section 9 below.</p>

        <h3 class="mt-5 mb-3" style="font-family:'Bebas Neue',sans-serif; font-size:24px; color:var(--primary)">
          8. Children's Privacy
        </h3>
        <p>This website is intended for general audiences and is not
        directed at children under 18. We do not knowingly collect
        personal information from children.</p>

        <h3 class="mt-5 mb-3" style="font-family:'Bebas Neue',sans-serif; font-size:24px; color:var(--primary)">
          9. Contact Us
        </h3>
        <p>If you have any questions about this Privacy Policy or how your
        information is handled, please contact us:</p>
        <ul>
          <li><strong>Email:</strong> <a href="mailto:info@ultranetsecurity.com">info@ultranetsecurity.com</a></li>
          <li><strong>Phone / WhatsApp:</strong> <a href="tel:+923091243189">0309-1243189</a></li>
          <li><strong>Address:</strong> House No 239, Manzoor Colony, Hill Town St 10, Karachi 75460</li>
        </ul>

        <h3 class="mt-5 mb-3" style="font-family:'Bebas Neue',sans-serif; font-size:24px; color:var(--primary)">
          10. Changes to This Policy
        </h3>
        <p>We may update this Privacy Policy from time to time to reflect
        changes in our practices or for legal reasons. Any changes will be
        posted on this page with an updated "Last updated" date at the
        top. We encourage you to review this page periodically.</p>

      </div>

      <div class="text-center mt-5">
        <a href="<?= SITE_URL ?>/#contact" class="btn-red d-inline-flex">
          <i class="fa-solid fa-envelope"></i> Have a Question? Contact Us
        </a>
      </div>

    </div>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
