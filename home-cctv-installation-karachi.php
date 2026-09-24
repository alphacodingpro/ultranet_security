<?php
require_once __DIR__.'/config/config.php';
require_once __DIR__.'/includes/functions.php';

$canonicalSlug='/home-cctv-installation-karachi';
$requestPath=parse_url($_SERVER['REQUEST_URI']??'', PHP_URL_PATH);
if($requestPath===$canonicalSlug.'.php'||$requestPath===$canonicalSlug.'/'){
    header('Location: '.SITE_URL.$canonicalSlug, true, 301);
    exit;
}
$pageTitle='Home CCTV Installation Karachi | Camera Planning Guide';
$metaDesc='Plan CCTV for a home in Karachi: camera positions, indoor and outdoor coverage, night vision, recording, mobile viewing and installation. Practical FAQs and a site survey.';
$ogImage=siteImageUrl('service-home.jpg');
$pageStyles=['home-cctv.css'];
$bodyClass='homecam-page';
$surveyUrl='https://wa.me/923091243189?text='.rawurlencode('Hello UltraNet Security, I need a home CCTV survey in Karachi. Area: __. House/apartment: __. Entrances/floors: __. Existing cameras: __. Budget: __.');
$faqs=[
 ['How many CCTV cameras does a house need?','There is no reliable one-size-fits-all count. Start with entrances, gate and parking, then add side access, stairs, courtyard or roof only where needed. Camera positions and viewing angles matter more than a headline number. We confirm the count after seeing the layout.'],
 ['Where should cameras be placed at home?','Prioritise the main gate, entry door and other real access paths. Set each view to capture useful detail at the distance that matters; do not assume one wide camera can identify faces across the whole property. Avoid pointing into neighbours’ private spaces.'],
 ['Should I choose 2 MP or 4 MP cameras?','Resolution is only one factor. A 4 MP camera can show more detail if the lens, lighting, viewing distance and recorder support it, but also needs more storage and bandwidth. For a narrow entrance, a suitable lens and position can matter more than increasing megapixels.'],
 ['Are wired cameras better than Wi-Fi cameras for a house?','For a permanent multi-camera system, a properly installed wired IP/PoE connection usually gives a more predictable connection. Wi-Fi can suit some spots if coverage, power and interference are checked. Most Wi-Fi cameras still need a power source.'],
 ['What is the difference between an NVR and a DVR?','An NVR commonly records IP cameras over a network, often with PoE for compatible wired cameras. A DVR typically records compatible analogue cameras using coaxial cabling. We check your existing camera models and wiring before suggesting an upgrade.'],
 ['Will the cameras record if the internet is down?','A correctly configured local recorder can continue recording while the home internet is down, provided the cameras, recorder and power remain available. Remote viewing and phone notifications need an internet connection. We demonstrate playback during handover.'],
 ['Can I see my home CCTV on my phone?','Compatible cameras and recorders can be set up for mobile live view and playback. You need a suitable connection at home and on your phone. Keep the app account under your control, use a strong password, and enable extra account security if supported.'],
 ['How long are CCTV recordings stored?','Retention depends on camera count, resolution, frame rate, compression, daily recording schedule and hard-drive capacity. Continuous recording uses more storage than event-based recording. We estimate retention from your chosen settings rather than promising a fixed number of days for every home.'],
 ['Do CCTV cameras work at night?','Many cameras include infrared or low-light modes, but night-time results depend on distance, available light, lens and mounting. A camera behind glass can reflect its own infrared light. We check lighting and, when possible, test the important night views before finalising positions.'],
 ['What happens during a power outage?','Cameras and the recorder stop if they lose power. A properly sized UPS can provide temporary backup for the recorder, PoE switch and other required equipment; runtime depends on the load and battery. We can include power backup in the system plan.'],
 ['Can you use my existing cameras or cables?','Sometimes. Send the camera and DVR/NVR model numbers, sample footage and information about the cables. We check compatibility, cable condition and recorder capacity before deciding which parts are worth keeping.'],
 ['How much does home CCTV installation cost in Karachi?','The total depends on the number and type of cameras, recorder, storage, cable distances, mounting work and any power backup. A budget estimate is a starting point; the final quotation lists the agreed equipment and installation scope after a site survey.'],
 ['How long does installation take?','The schedule depends on your property layout, camera count, cable routes and access for drilling or surface wiring. We discuss the schedule once the camera positions and work scope are agreed.'],
 ['Can you install cameras inside bedrooms or shared family areas?','We plan views around your household’s privacy needs and avoid placing cameras in sensitive private areas. Tell us who needs access and which rooms must remain outside coverage. For apartments or shared buildings, check building rules before mounting cameras in common areas.'],
 ['Do all home CCTV cameras record audio?','No. Audio depends on the exact camera model, recorder compatibility and settings. If audio is important to you, ask us to confirm it against the specific model before purchase. Consider the privacy of everyone who may be recorded.'],
 ['Who controls the CCTV app and passwords after installation?','You should control the owner account and recovery details. At handover, we explain live view, recording playback and how to change credentials. Avoid sharing the main account password when a separate user account is available.'],
];
$extraSchema=[
 schemaBreadcrumb([['name'=>'Home','url'=>SITE_URL.'/'],['name'=>'Home CCTV Installation','url'=>SITE_URL.$canonicalSlug]]),
 ['@context'=>'https://schema.org','@type'=>'Service','@id'=>SITE_URL.$canonicalSlug.'#service',
  'name'=>'Home CCTV Installation in Karachi','serviceType'=>'Home CCTV planning and installation',
  'url'=>SITE_URL.$canonicalSlug,'description'=>$metaDesc,'provider'=>['@id'=>SITE_URL.'/#business'],
  'areaServed'=>['@type'=>'City','name'=>'Karachi']],
];
include __DIR__.'/includes/header.php';
?>
<section class="homecam-hero">
 <div class="container">
  <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?= SITE_URL ?>/">Home</a></li><li class="breadcrumb-item active" aria-current="page">Home CCTV Installation</li></ol></nav>
  <div class="row align-items-center g-5">
   <div class="col-lg-6">
    <p class="homecam-kicker">HOME SECURITY · KARACHI</p>
    <h1>HOME CCTV<br><span>INSTALLATION</span> IN KARACHI</h1>
    <p class="homecam-lead">A useful home CCTV system starts with the right camera views, reliable recording and simple access for your family.</p>
    <p class="homecam-sub">See how we plan entrances, outdoor areas, night views, storage and mobile access before recommending equipment for a house or apartment.</p>
    <div class="d-flex flex-wrap gap-3"><a class="btn-red" href="<?= h($surveyUrl) ?>"><i class="fa-brands fa-whatsapp" aria-hidden="true"></i> Request a Home Survey</a><a class="btn-ghost" href="<?= SITE_URL ?>/calculator.php">Estimate a Package</a></div>
   </div>
   <div class="col-lg-6"><figure class="homecam-hero-photo"><img src="<?= h(siteImageUrl('service-home.jpg')) ?>" alt="Outdoor security camera installed near a building entrance" width="1200" height="797" fetchpriority="high"><figcaption>Clear coverage begins with a sensible position and viewing angle.</figcaption></figure></div>
  </div>
 </div>
</section>
<nav class="homecam-jump" aria-label="On this page"><div class="container d-flex flex-wrap gap-3 gap-md-4"><a href="#plan">Our design process</a><a href="#coverage">Coverage map</a><a href="#equipment">Equipment choices</a><a href="#handover">Installation</a><a href="#questions">Common questions</a></div></nav>

<section class="homecam-section" id="plan"><div class="container"><div class="row g-4 align-items-end mb-4"><div class="col-lg-8"><p class="section-tag">START WITH THE PROPERTY</p><h2 class="section-title">How we design a <span>home security system.</span></h2><p class="homecam-intro">Before choosing a camera count, we ask what you need to see, where a person can enter, and what evidence would actually be useful. A wide overview and a close entrance view solve different problems.</p></div><div class="col-lg-4"><p class="homecam-callout">Your plan should state <strong>camera positions, recorder, storage, cable routes, app access and power</strong> before installation begins.</p></div></div>
 <ol class="homecam-steps row g-3">
 <?php foreach([
  ['01','Walk the site','Map gates, doors, parking, stairs and side access. Discuss which areas must remain private.'],
  ['02','Plan useful views','Choose viewing angles and lens options for the distance and detail needed at each location.'],
  ['03','Size the system','Check camera and recorder compatibility, recording schedule, storage, network and power requirements.'],
  ['04','Agree the work','Confirm mounting points, cable routes, equipment list and quotation before installation.'],
 ] as [$number,$title,$description]): ?><li class="col-md-6 col-xl-3"><article class="homecam-step h-100"><span><?= h($number) ?></span><h3><?= h($title) ?></h3><p><?= h($description) ?></p></article></li><?php endforeach; ?></ol>
</div></section>

<section class="homecam-section homecam-muted" id="coverage"><div class="container"><div class="row g-5 align-items-center"><div class="col-lg-5"><p class="section-tag">CAMERA PLACEMENT</p><h2 class="section-title">Protect the access points.<br><span>Respect private spaces.</span></h2><p class="homecam-intro">The positions below are a planning checklist, not a fixed bundle. A survey decides what your property actually needs.</p><a class="homecam-link" href="<?= SITE_URL ?>/calculator.php">Explore camera count and budget <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a></div><div class="col-lg-7"><div class="homecam-grid">
 <?php foreach([
  ['fa-door-open','Main entrance','A view of the doorway and approach with usable detail, including after dark.'],
  ['fa-car','Gate and parking','Check vehicles, gate movement and any blind spots without aiming into neighbours’ homes.'],
  ['fa-stairs','Stairs and shared paths','Cover movement between floors where appropriate, with family privacy agreed first.'],
  ['fa-house-chimney','Side access and roof','Assess exposed approaches, weather protection and safe mounting points.'],
 ] as [$icon,$title,$description]): ?><article class="homecam-area"><i class="fa-solid <?= h($icon) ?>" aria-hidden="true"></i><div><h3><?= h($title) ?></h3><p><?= h($description) ?></p></div></article><?php endforeach; ?>
 </div></div></div></div></section>

<section class="homecam-section" id="equipment"><div class="container"><p class="section-tag">THE RIGHT PARTS WORK TOGETHER</p><h2 class="section-title">What goes into a <span>home CCTV package?</span></h2><p class="homecam-intro mb-4">A quote should explain each part, not just list a megapixel number. Products and models depend on availability and compatibility.</p><div class="row g-3">
 <?php foreach([
  ['01','Cameras and lenses','Indoor or weather-suitable outdoor cameras selected for placement, light and useful detail.'],
  ['02','Recorder and hard drive','Compatible NVR or DVR, with storage sized to your camera settings and target retention.'],
  ['03','Cabling and network','Cable routes, PoE or suitable power, network connection and protected connectors.'],
  ['04','Mobile and power','Account setup, playback demonstration and optional backup power sized for the equipment.'],
 ] as [$number,$title,$description]): ?><article class="col-md-6 col-xl-3"><div class="homecam-part h-100"><span><?= h($number) ?></span><h3><?= h($title) ?></h3><p><?= h($description) ?></p></div></article><?php endforeach; ?></div>
 <div class="homecam-note"><strong>Already have a CCTV system?</strong><p>Send us your camera and recorder model numbers. We can check whether the existing equipment or wiring can be reused before suggesting replacements.</p><a href="<?= SITE_URL ?>/products.php">Browse camera products <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a></div>
</div></section>

<section class="homecam-section homecam-muted" id="handover"><div class="container"><div class="row g-5 align-items-center"><div class="col-lg-6"><p class="section-tag">SURVEY TO HANDOVER</p><h2 class="section-title">Installation is more than<br><span>mounting cameras.</span></h2><p class="homecam-intro">We agree the camera views and cable routes, fit the system, configure recording, then show you how to use it. The final scope and timing are confirmed after the property is checked.</p><div class="d-flex flex-wrap gap-3"><a class="btn-red" href="<?= h($surveyUrl) ?>">Discuss Your Home</a><a class="homecam-link align-self-center" href="tel:+923091243189">Call 0309-1243189</a></div></div><div class="col-lg-6"><ol class="homecam-handover"><li><strong>Before:</strong> confirm locations, privacy boundaries, recorder position, power and quotation.</li><li><strong>During:</strong> route cables, install cameras and connect compatible equipment.</li><li><strong>Test:</strong> check each view, recording, playback and mobile access.</li><li><strong>Handover:</strong> explain account ownership, passwords and everyday use.</li></ol></div></div></div></section>

<section class="homecam-section" id="questions"><div class="container"><div class="row g-5"><div class="col-lg-4"><p class="section-tag">HOME CCTV FAQS</p><h2 class="section-title">Questions homeowners <span>ask us.</span></h2><p class="homecam-intro">These are general answers. A site survey checks your actual layout, equipment and budget.</p><a class="homecam-link" href="<?= SITE_URL ?>/cctv-camera-installation-dha-karachi">Looking for CCTV in DHA Karachi? <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a></div><div class="col-lg-8"><?php foreach($faqs as [$question,$answer]): ?><details class="homecam-faq"><summary><?= h($question) ?></summary><p><?= h($answer) ?></p></details><?php endforeach; ?></div></div></div></section>

<section class="homecam-end"><div class="container"><div class="row align-items-center g-4"><div class="col-lg-7"><p class="homecam-kicker">LET’S PLAN YOUR HOME</p><h2>Tell us what you want to protect.</h2><p>Share your Karachi area, house or apartment type, access points and budget. We will discuss a suitable survey and a clear equipment quotation.</p></div><div class="col-lg-5 d-flex flex-wrap gap-3 justify-content-lg-end"><a class="btn-red" href="<?= h($surveyUrl) ?>"><i class="fa-brands fa-whatsapp" aria-hidden="true"></i> WhatsApp for a Survey</a><a class="btn-ghost" href="tel:+923091243189">Call Us</a></div></div></div></section>
<?php include __DIR__.'/includes/footer.php'; ?>
