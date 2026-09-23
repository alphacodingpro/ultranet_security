<?php
require_once __DIR__.'/config/config.php';
require_once __DIR__.'/includes/functions.php';
$canonicalSlug='/cctv-camera-installation-dha-karachi';
$requestPath=parse_url($_SERVER['REQUEST_URI']??'',PHP_URL_PATH);
if($requestPath===$canonicalSlug.'.php'||$requestPath===$canonicalSlug.'/'){
    header('Location: '.SITE_URL.$canonicalSlug,true,301);exit;
}
$pageTitle='CCTV Camera Installation in DHA Karachi | UltraNet Security';
$metaDesc='CCTV installation for homes, shops and offices in DHA Karachi, Phases 1–8. Explore Hikvision and Dahua options. Call for a free site survey and quotation.';
$ogImage=siteImageUrl('service-home.jpg');
$bodyClass='dha-page';
$pageStyles=['dha.css'];
$surveyUrl='https://wa.me/923091243189?text='.rawurlencode('Hi UltraNet Security, I would like a free CCTV site survey in DHA Karachi. Phase: __. Property: home / office / shop. Indoor / outdoor areas: __. Budget: __.');
$faqs=[
 ['Do you install CCTV cameras across DHA Karachi?','We provide CCTV installation enquiries and site surveys across DHA Karachi, Phases 1–8. Share your phase, street and property type so we can confirm the visit and access arrangements.'],
 ['How much does a CCTV system in DHA Karachi cost?','The quotation depends on camera quantity and model, indoor or outdoor coverage, recording storage, cable routes and installation work. Share your budget or use our calculator for an estimate. A site survey is used to confirm the final scope and price.'],
 ['Can I choose Hikvision or Dahua cameras?','Yes. We can plan a system using Hikvision or Dahua products, subject to availability. Camera, recorder and accessory compatibility is checked before the final quotation.'],
 ['Can I view the cameras on my phone?','Compatible systems can be configured for mobile viewing. Remote access requires a suitable internet connection at the property. We discuss account access and demonstrate viewing and playback during handover.'],
 ['Can you upgrade my existing cameras or DVR/NVR?','Share the existing camera and recorder model numbers, the issues you are seeing and any available wiring details. We assess compatibility and whether the equipment or cable runs can be reused before recommending an upgrade.'],
 ['How many days will installation take?','Timing depends on camera count, wiring distances, property access and whether new cable routes are needed. The installation schedule is agreed after the site survey; completion time is not promised before the scope is checked.'],
 ['Is UltraNet Security based in DHA?','Our listed business address is in Manzoor Colony, Karachi. DHA is a service area. Contact us to arrange a visit to your property.'],
];
$extraSchema=[
 schemaBreadcrumb([['name'=>'Home','url'=>SITE_URL.'/'],['name'=>'CCTV Installation in DHA Karachi','url'=>SITE_URL.$canonicalSlug]]),
 ['@context'=>'https://schema.org','@type'=>'Service','@id'=>SITE_URL.$canonicalSlug.'#service','name'=>'CCTV Camera Installation in DHA Karachi','serviceType'=>'CCTV installation and maintenance','url'=>SITE_URL.$canonicalSlug,'description'=>$metaDesc,'provider'=>['@id'=>SITE_URL.'/#business'],'areaServed'=>['@type'=>'Place','name'=>'DHA Karachi, Phases 1–8']],
 schemaFaqPage($faqs),
];
include __DIR__.'/includes/header.php';
?>
<section class="dha-hero">
 <div class="container">
  <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?= SITE_URL ?>/">Home</a></li><li class="breadcrumb-item active" aria-current="page">DHA Karachi</li></ol></nav>
  <div class="row align-items-center g-5">
   <div class="col-lg-6">
    <p class="dha-eyebrow"><i class="fa-solid fa-location-dot" aria-hidden="true"></i> DHA KARACHI · PHASES 1–8</p>
    <h1>CCTV CAMERA INSTALLATION<br>IN <span>DHA KARACHI</span></h1>
    <p class="dha-lead">Know what is happening at your gate, inside your business and around your property.</p>
    <p class="dha-intro">A camera plan built around your home, office or shop—with suitable coverage, recording and mobile viewing. Tell us what you need to protect. We will help you plan the system.</p>
    <div class="d-flex flex-wrap gap-3 mt-4"><a class="btn-red" href="<?= h($surveyUrl) ?>"><i class="fa-brands fa-whatsapp" aria-hidden="true"></i> Request a Free Site Survey</a><a class="btn-ghost" href="tel:+923091243189"><i class="fa-solid fa-phone" aria-hidden="true"></i> 0309-1243189</a></div>
    <div class="dha-hero-notes"><span>Homes &amp; apartments</span><span>Offices &amp; shops</span><span>New systems &amp; upgrades</span></div>
   </div>
   <div class="col-lg-6"><figure class="dha-photo"><img src="<?= h(siteImageUrl('service-home.jpg')) ?>" alt="Outdoor dome security camera mounted on a building wall" width="1200" height="797" fetchpriority="high" loading="eager"><figcaption>Outdoor coverage starts with the right camera position.</figcaption></figure></div>
  </div>
 </div>
</section>
<nav class="dha-jump" aria-label="On this page"><div class="container d-flex flex-wrap gap-3 gap-md-4"><a href="#dha-solutions">Solutions</a><a href="#dha-packages">Package options</a><a href="#dha-coverage">Phase coverage</a><a href="#dha-process">How it works</a><a href="#dha-faq">FAQs</a></div></nav>
<section class="dha-section" id="dha-solutions"><div class="container">
 <p class="section-tag">PLAN THE COVERAGE FIRST</p><h2 class="section-title">The right view. <span>Where it matters.</span></h2>
 <p class="dha-section-intro">Camera count is only the starting point. We look at the areas you want to see, lighting, recording needs and practical cable routes before finalising equipment.</p>
 <div class="row g-4 mt-1">
 <?php foreach([
 ['fa-house','Homes & apartments','Cover entrances, gates, driveways and agreed common areas. Position cameras around your property boundaries and discuss privacy before installation.'],
 ['fa-building','Offices & commercial spaces','Plan views of reception, entry and exit points, stock areas and parking. Decide who needs live access and who should be able to review recordings.'],
 ['fa-store','Shops & retail','Focus on the entrance, counter and stockroom. Choose views that help you review incidents without relying on one distant camera for every detail.'],
 ['fa-screwdriver-wrench','Repairs, upgrades & maintenance','Investigate recording gaps, camera faults and mobile viewing issues. Existing recorders and wiring are assessed before deciding what should be retained or replaced.'],
 ] as [$icon,$title,$copy]): ?>
 <div class="col-md-6 col-xl-3"><article class="dha-card h-100"><div class="dha-icon"><i class="fa-solid <?= h($icon) ?>" aria-hidden="true"></i></div><h3><?= h($title) ?></h3><p><?= h($copy) ?></p></article></div>
 <?php endforeach; ?>
 </div>
</div></section>
<section class="dha-section dha-soft" id="dha-packages"><div class="container">
 <div class="row align-items-end g-3 mb-4"><div class="col-lg-8"><p class="section-tag">START WITH YOUR PRIORITIES</p><h2 class="section-title">A package that fits <span>your property.</span></h2><p class="dha-section-intro mb-0">These are planning options, not fixed-price bundles. Models, storage, wiring and labour are listed in your quotation after the scope is agreed.</p></div><div class="col-lg-4 text-lg-end"><a class="dha-text-link" href="<?= SITE_URL ?>/calculator.php">Build a budget estimate <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a></div></div>
 <div class="row g-4">
 <?php foreach([
 ['01','Focused coverage','4-camera starting plan',['Discuss four priority viewpoints','Choose indoor and outdoor cameras','Size the recorder and storage to your needs'],'4-camera CCTV plan'],
 ['02','Wider coverage','8-camera starting plan',['Map entrances and additional blind spots','Plan cable routes and recorder capacity','Review recording time and remote access'],'8-camera CCTV plan'],
 ['03','Your property, your plan','Custom system or upgrade',['Work from your budget and site layout','Assess equipment that can be reused','Allow for agreed future expansion'],'custom CCTV system or upgrade'],
 ] as [$number,$title,$sub,$items,$message]): ?>
 <div class="col-lg-4"><article class="dha-package h-100"><span class="dha-step-number"><?= h($number) ?></span><h3><?= h($title) ?></h3><p class="dha-package-sub"><?= h($sub) ?></p><ul><?php foreach($items as $item): ?><li><?= h($item) ?></li><?php endforeach; ?></ul><p class="dha-quote-label">Quoted for your site</p><a class="btn-red" href="<?= h('https://wa.me/923091243189?text='.rawurlencode('Hi, please quote a '.$message.' for my property in DHA Karachi. Phase: __. Budget: __.')) ?>">Discuss this option <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a></article></div>
 <?php endforeach; ?>
 </div>
 <div class="dha-brand-row"><div><strong>Prefer a particular brand?</strong><p>Browse the catalogue, then ask us to check availability and system compatibility.</p></div><div class="d-flex flex-wrap gap-3"><a href="<?= SITE_URL ?>/products.php?q=Hikvision">Hikvision products <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i></a><a href="<?= SITE_URL ?>/products.php?q=Dahua">Dahua products <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i></a></div></div>
</div></section>
<section class="dha-section" id="dha-coverage"><div class="container"><div class="row g-5 align-items-center">
 <div class="col-lg-6"><p class="section-tag">ONE LOCAL SERVICE PAGE</p><h2 class="section-title">Serving DHA Karachi.<br><span>Phases 1 through 8.</span></h2><p class="dha-section-intro">Send your DHA phase, street, property type and preferred visit time. We will confirm access arrangements and discuss your coverage priorities before the survey.</p><div class="dha-phases" aria-label="DHA phases served"><?php for($phase=1;$phase<=8;$phase++): ?><span><i class="fa-solid fa-location-dot" aria-hidden="true"></i> Phase <?= $phase ?></span><?php endfor; ?></div><p class="dha-small mt-3">For apartments, offices and managed buildings, please confirm permission for camera mounting and cable routes with the property management.</p></div>
 <div class="col-lg-6"><div class="dha-card"><h3>What we check at your DHA property</h3><dl class="dha-checks"><dt>Gates, parking &amp; outdoor areas</dt><dd>Viewing angles, lighting, mounting points and weather exposure.</dd><dt>Entrances, counters &amp; shared spaces</dt><dd>Required detail, privacy boundaries and areas that should not be recorded.</dd><dt>Recording &amp; internet</dt><dd>Desired retention time, recorder location, power and mobile viewing requirements.</dd><dt>Existing wiring &amp; building access</dt><dd>Cable condition, practical routes and any restrictions on drilling or installation hours.</dd></dl></div></div>
</div></div></section>
<section class="dha-section dha-soft" id="dha-process"><div class="container"><p class="section-tag">FROM ENQUIRY TO HANDOVER</p><h2 class="section-title">A clear plan. <span>Before installation.</span></h2><ol class="dha-process row g-4">
 <?php foreach([['Tell us what you need','Share your phase, property type, priority areas and budget on WhatsApp or by phone.'],['Survey & quotation','Agree the camera positions, equipment, cable routes, storage and installation scope.'],['Install & configure','Fit the agreed system, configure recording and set up compatible mobile access.'],['Test & hand over','Review camera views and playback together, and explain account access and everyday operation.']] as $i=>[$title,$copy]): ?><li class="col-md-6 col-lg-3"><div class="dha-card h-100"><span class="dha-step-number">0<?= $i+1 ?></span><h3><?= h($title) ?></h3><p><?= h($copy) ?></p></div></li><?php endforeach; ?>
</ol></div></section>
<section class="dha-section" id="dha-faq"><div class="container"><div class="row g-5"><div class="col-lg-4"><p class="section-tag">BEFORE YOU BOOK</p><h2 class="section-title">Your CCTV<br><span>questions, answered.</span></h2><p class="dha-section-intro">Already have a system? Send the model numbers and a brief description of the problem with your enquiry.</p><a class="dha-text-link" href="<?= SITE_URL ?>/products.php">Browse all products <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a></div><div class="col-lg-8"><?php foreach($faqs as [$question,$answer]): ?><details class="dha-faq"><summary><?= h($question) ?></summary><p><?= h($answer) ?></p></details><?php endforeach; ?></div></div></div></section>
<section class="dha-enquiry"><div class="container"><div class="row align-items-center g-4"><div class="col-lg-7"><p class="dha-eyebrow">LET’S PLAN YOUR COVERAGE</p><h2>Tell us about your DHA property.</h2><p>Share your phase, budget and the areas you want to monitor. Request a free site survey and a quotation for the equipment and work you actually need.</p></div><div class="col-lg-5 d-flex flex-wrap gap-3 justify-content-lg-end"><a class="btn-red" href="<?= h($surveyUrl) ?>"><i class="fa-brands fa-whatsapp" aria-hidden="true"></i> WhatsApp for a Survey</a><a class="btn-ghost" href="tel:+923091243189">Call 0309-1243189</a></div></div></div></section>
<?php include __DIR__.'/includes/footer.php'; ?>
