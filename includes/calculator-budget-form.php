<section id="budgetPackage" class="container pt-5" aria-labelledby="budgetTitle">
  <div class="calc-card">
    <h2 class="calc-section-title" id="budgetTitle"><i class="fa-solid fa-wallet"></i>Build a Package Within My Budget</h2>
    <p>Tell us your budget and location. We will select available cameras and matching equipment from our catalog.</p>
    <form id="budgetForm">
      <div class="row g-3 mb-3">
        <div class="col-md-4"><label class="calc-label" for="budgetAmount">Equipment budget (PKR)</label><input id="budgetAmount" class="calc-form-control" type="number" min="1" max="10000000" step="0.01" placeholder="e.g. 100000" required></div>
        <div class="col-md-4"><label class="calc-label" for="budgetProperty">Property</label><select id="budgetProperty" class="calc-form-select"><option value="home">Home</option><option value="office">Office</option></select></div>
        <div class="col-md-4"><label class="calc-label" for="budgetEnvironment">Camera location</label><select id="budgetEnvironment" class="calc-form-select"><option value="indoor">Indoor</option><option value="outdoor">Outdoor</option><option value="both">Indoor and Outdoor</option></select></div>
      </div>
      <p class="calc-hint">Starting plan: Home up to 4 cameras with 7-day recording and 20m cable each; Office up to 8 cameras with 14-day recording and 30m cable each. Camera count depends on your budget. Recording duration and site coverage are estimates. Installation labour is quoted separately.</p>
      <button type="submit" class="calc-btn-primary" id="budgetBuildBtn">Find My Package</button>
    </form>
    <p id="budgetMessage" class="mt-3" role="status" aria-live="polite"></p>
    <div id="budgetResult" hidden>
      <h3 id="budgetSummary" class="mt-4"></h3>
      <p id="budgetAssumptions"></p>
      <div id="budgetItems"></div>
      <p id="budgetTotals" class="fw-bold mt-3"></p>
      <p class="calc-hint">Includes cameras, NVR, surveillance HDD, PoE switch, cable and accessory kits. Equipment estimate only; site survey confirms coverage, cable runs and installation labour.</p>
      <details><summary class="mb-3">Get this package estimate</summary>
        <form id="budgetLeadForm">
          <div class="row g-3 mb-3">
            <div class="col-md-4"><label class="calc-label" for="budgetName">Name</label><input id="budgetName" class="calc-form-control" required maxlength="120" autocomplete="name"></div>
            <div class="col-md-4"><label class="calc-label" for="budgetPhone">Phone / WhatsApp</label><input id="budgetPhone" class="calc-form-control" type="tel" required minlength="7" maxlength="30" autocomplete="tel"></div>
            <div class="col-md-4"><label class="calc-label" for="budgetEmail">Email (optional)</label><input id="budgetEmail" class="calc-form-control" type="email" maxlength="190" autocomplete="email"></div>
          </div>
          <button class="calc-btn-primary" id="budgetEstimateBtn">Get My Estimate PDF</button>
          <p id="budgetLeadMessage" role="status" aria-live="polite" class="mt-2"></p>
        </form>
      </details>
    </div>
  </div>
</section>
