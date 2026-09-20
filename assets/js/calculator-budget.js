(function () {
  'use strict';
  const $ = id => document.getElementById(id);
  const money = value => 'PKR ' + Number(value).toLocaleString('en-PK', {maximumFractionDigits: 2});
  let selected = null;
  let revision = 0;
  function invalidate() {
    revision++;
    selected = null;
    $('budgetResult').hidden = true;
    $('budgetMessage').textContent = '';
    $('budgetLeadMessage').textContent = '';
  }
  ['budgetAmount','budgetProperty','budgetEnvironment'].forEach(id => $(id).addEventListener('input', invalidate));
  function render(p) {
    $('budgetSummary').textContent = p.camera_count + ' × ' + p.mp + 'MP cameras — ' + money(p.grand_total);
    $('budgetAssumptions').textContent = (p.camera_count < p.target_count ? 'Your budget covers ' + p.camera_count + ' of the suggested ' + p.target_count + ' cameras. ' : '') + p.recording_days + ' days estimated continuous recording; ' + p.cable_length_m + 'm total cable. ' + (p.input.environment === 'both' ? 'Selected cameras are approved for both indoor and outdoor use.' : 'Selected for ' + p.input.environment + ' use.');
    $('budgetItems').replaceChildren();
    p.items.forEach(item => {
      const row = document.createElement('div'); row.className = 'pkg-item';
      const info = document.createElement('div'); info.className = 'pkg-item-info';
      const title = document.createElement('div'); title.className = 'pkg-item-name'; title.textContent = item.name;
      const detail = document.createElement('div'); detail.className = 'pkg-item-meta'; detail.textContent = item.qty + ' × ' + money(item.unit_price) + (item.key === 'cable' ? ' per metre' : '');
      const price = document.createElement('div'); price.className = 'pkg-item-price'; price.textContent = money(item.line_total);
      info.append(title, detail); row.append(info, price); $('budgetItems').append(row);
    });
    $('budgetTotals').textContent = 'Subtotal: ' + money(p.subtotal) + ' · Discount (' + p.discount_percent + '%): ' + money(p.discount_amount) + ' · Equipment total: ' + money(p.grand_total) + ' · Budget remaining: ' + money(p.remaining_budget);
    $('budgetResult').hidden = false;
  }
  $('budgetForm').addEventListener('submit', async event => {
    event.preventDefault(); invalidate();
    const requestRevision = revision;
    const input = {budget: $('budgetAmount').value, property: $('budgetProperty').value, environment: $('budgetEnvironment').value};
    $('budgetBuildBtn').disabled = true;
    $('budgetMessage').textContent = 'Checking available equipment…';
    try {
      const response = await fetch(SITE_URL + '/calculator-budget.php', {method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(input)});
      const data = await response.json();
      if (requestRevision !== revision) return;
      if (!data.ok) throw new Error(data.error || 'Unable to build a package.');
      selected = data.package;
      render(selected);
      $('budgetMessage').textContent = 'Package found within your equipment budget.';
    } catch (error) {
      if (requestRevision === revision) $('budgetMessage').textContent = error.message || 'Connection failed. Please try again.';
    } finally { $('budgetBuildBtn').disabled = false; }
  });
  $('budgetLeadForm').addEventListener('submit', async event => {
    event.preventDefault();
    if (!selected) return;
    $('budgetEstimateBtn').disabled = true;
    $('budgetLeadMessage').textContent = 'Checking prices and preparing your estimate…';
    try {
      const response = await fetch(SITE_URL + '/calculator-submit.php', {method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({client_name:$('budgetName').value.trim(), client_phone:$('budgetPhone').value.trim(), client_email:$('budgetEmail').value.trim(), budget_request:selected.input, budget_fingerprint:selected.fingerprint})});
      const data = await response.json();
      if (!data.ok) throw new Error(data.error || 'Unable to save your estimate.');
      window.location.href = SITE_URL + '/estimate.php?ref=' + encodeURIComponent(data.ref);
    } catch (error) { $('budgetLeadMessage').textContent = error.message || 'Connection failed. Please try again.'; }
    finally { $('budgetEstimateBtn').disabled = false; }
  });
})();
