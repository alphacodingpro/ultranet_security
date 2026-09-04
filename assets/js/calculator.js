/* =============================================
   UltraNet Security – CCTV Calculator Logic
   ============================================= */
(function () {
  const D = CALC_DATA;
  let mode = 'calculate'; // 'calculate' | 'package'

  const $ = id => document.getElementById(id);

  function fmtPrice(n) {
    return 'PKR ' + Math.round(n).toLocaleString();
  }

  /* ── Populate camera model dropdown ── */
  function populateCameraDropdown() {
    const sel = $('cameraProduct');
    sel.innerHTML = '';
    if (!D.cameras.length) {
      sel.innerHTML = '<option value="">No camera products added yet</option>';
      return;
    }
    D.cameras.forEach(c => {
      const opt = document.createElement('option');
      opt.value = c.id;
      opt.textContent = c.name + (c.brand ? ' (' + c.brand + ')' : '') + ' — ' + fmtPrice(c.price);
      sel.appendChild(opt);
    });
  }

  /* ── Radio pill styling ── */
  document.querySelectorAll('.calc-radio-pill').forEach(pill => {
    pill.addEventListener('click', function () {
      const group = this.getAttribute('data-group');
      document.querySelectorAll('.calc-radio-pill[data-group="' + group + '"]').forEach(p => p.classList.remove('selected'));
      this.classList.add('selected');
      this.querySelector('input').checked = true;
      runCalculation();
    });
  });

  /* ── Mode toggle ── */
  function setMode(newMode) {
    mode = newMode;
    $('modeCalculateBtn').classList.toggle('active', mode === 'calculate');
    $('modePackageBtn').classList.toggle('active', mode === 'package');
    $('calcStep2').style.display = mode === 'package' ? 'block' : 'none';
    $('calcOnlyActions').style.display = mode === 'package' ? 'none' : 'block';
    runCalculation();
  }
  $('modeCalculateBtn').addEventListener('click', () => setMode('calculate'));
  $('modePackageBtn').addEventListener('click', () => setMode('package'));
  $('switchToPackageBtn').addEventListener('click', () => setMode('package'));

  /* ── Core calculation ── */
  let lastResult = null;

  function runCalculation() {
    const systemType   = document.querySelector('[name="systemType"]:checked').value;
    const cameraCount  = Math.max(1, parseInt($('cameraCount').value) || 1);
    const resolution   = $('resolutionTier').value;
    const recDays      = parseInt($('recordingDays').value);
    const recMode      = document.querySelector('[name="recMode"]:checked').value;
    const cableLenEach = Math.max(1, parseInt($('cableLength').value) || 1);
    const lensNote     = $('lensType').value;

    const cameraId = $('cameraProduct').value;
    const cameraObj = D.cameras.find(c => String(c.id) === String(cameraId)) || null;
    const camWatts = cameraObj && cameraObj.camera_watts ? parseFloat(cameraObj.camera_watts) : D.settings.defaultCamWatts;

    // Bitrate table (Mbps) — mirrors PHP logic
    const bitrates = { '2mp': 2, '4mp': 4, '5mp': 5, '8mp': 8 };
    const bitrate = bitrates[resolution] || 4;
    const hoursPerDay = recMode === 'motion' ? 24 * 0.4 : 24;
    const requiredGb = Math.ceil((bitrate / 8) * 3600 * hoursPerDay * recDays * cameraCount / 1024);

    const totalCableM = cableLenEach * cameraCount;

    let requiredWatts = 0;
    if (systemType === 'ip') {
      const headroom = D.settings.poeHeadroom / 100;
      requiredWatts = Math.ceil(cameraCount * camWatts * (1 + headroom));
    }

    // ── Pick recorder ──
    const recorderPool = systemType === 'ip' ? D.nvrs : D.dvrs;
    let recorder = recorderPool.filter(r => r.channels >= cameraCount).sort((a, b) => a.channels - b.channels)[0];
    let recorderShortfall = false;
    if (!recorder && recorderPool.length) {
      recorder = recorderPool.slice().sort((a, b) => b.channels - a.channels)[0];
      recorderShortfall = true;
    }

    // ── Pick HDD ──
    let hddPick = D.hdds.filter(h => h.storage_gb >= requiredGb).sort((a, b) => a.storage_gb - b.storage_gb)[0];
    let hddQty = 1, hddShortfall = false;
    if (!hddPick && D.hdds.length) {
      hddPick = D.hdds.slice().sort((a, b) => b.storage_gb - a.storage_gb)[0];
      hddQty = Math.max(1, Math.ceil(requiredGb / hddPick.storage_gb));
      hddShortfall = hddQty > 3; // just a soft warning threshold
    }

    // ── Pick PoE switch (only IP) ──
    let poeSwitch = null, poeShortfall = false;
    if (systemType === 'ip') {
      poeSwitch = D.poeSwitches.filter(p => p.poe_ports >= cameraCount && p.poe_budget_watts >= requiredWatts)
        .sort((a, b) => a.poe_ports - b.poe_ports)[0];
      if (!poeSwitch && D.poeSwitches.length) {
        poeSwitch = D.poeSwitches.slice().sort((a, b) => b.poe_ports - a.poe_ports)[0];
        poeShortfall = poeSwitch.poe_ports < cameraCount || poeSwitch.poe_budget_watts < requiredWatts;
      }
    }

    // ── Cable ──
    const cableWarn = cableLenEach > 100;

    lastResult = {
      systemType, cameraCount, resolution, recDays, recMode, cableLenEach, totalCableM,
      lensNote, cameraObj, requiredGb, requiredWatts, recorder, recorderShortfall,
      hddPick, hddQty, hddShortfall, poeSwitch, poeShortfall, cableWarn,
    };

    renderResults(lastResult);
    if (mode === 'package') renderPackage(lastResult);
  }

  function gbLabel(gb) {
    if (gb >= 1000) return (gb / 1000).toFixed(gb % 1000 === 0 ? 0 : 1) + ' TB';
    return gb + ' GB';
  }

  function renderResults(r) {
    $('resRecorder').innerHTML = r.recorder
      ? r.recorder.name + ' <span>(' + r.recorder.channels + ' CH)</span>'
      : '<span style="font-size:15px;color:var(--muted)">No matching product — add one in Admin</span>';

    $('resHdd').innerHTML = gbLabel(r.requiredGb) + (r.hddQty > 1 ? ' <span>needed (~' + r.hddQty + ' drives)</span>' : ' <span>needed</span>');

    $('poeResultBox').style.display = r.systemType === 'ip' ? 'block' : 'none';
    $('resPoe').innerHTML = r.systemType === 'ip' ? r.requiredWatts + 'W <span>total budget</span>' : '—';

    $('resCable').innerHTML = r.totalCableM.toLocaleString() + ' m <span>total run</span>';

    let warnings = '';
    if (r.recorderShortfall) warnings += '<div class="calc-warn"><i class="fa-solid fa-triangle-exclamation me-1"></i>No single NVR/DVR covers ' + r.cameraCount + ' cameras — you may need 2 recorders.</div>';
    if (r.hddShortfall) warnings += '<div class="calc-warn"><i class="fa-solid fa-triangle-exclamation me-1"></i>Large storage requirement — consider a NAS or RAID setup. Contact us for a custom quote.</div>';
    if (r.poeShortfall) warnings += '<div class="calc-warn"><i class="fa-solid fa-triangle-exclamation me-1"></i>No single PoE switch covers this load — multiple switches recommended.</div>';
    if (r.cableWarn) warnings += '<div class="calc-warn"><i class="fa-solid fa-triangle-exclamation me-1"></i>Cable runs over 100m need a PoE extender or fiber media converter.</div>';
    $('calcWarnings').innerHTML = warnings;
  }

  /* ── Package rendering ── */
  function buildItemsList(r) {
    const items = [];
    if (r.cameraObj) {
      items.push({
        key: 'camera', product_id: r.cameraObj.id, name: r.cameraObj.name,
        image: r.cameraObj.image_url, qty: r.cameraCount,
        unit_price: parseFloat(r.cameraObj.price), meta: r.cameraCount + ' units'
      });
    }
    if (r.recorder) {
      items.push({
        key: 'recorder', product_id: r.recorder.id, name: r.recorder.name,
        image: r.recorder.image_url, qty: 1,
        unit_price: parseFloat(r.recorder.price), meta: r.recorder.channels + ' Channel'
      });
    }
    if (r.hddPick) {
      items.push({
        key: 'hdd', product_id: r.hddPick.id, name: r.hddPick.name,
        image: r.hddPick.image_url, qty: r.hddQty,
        unit_price: parseFloat(r.hddPick.price), meta: r.hddQty + ' x ' + gbLabel(r.hddPick.storage_gb)
      });
    }
    if (r.systemType === 'ip' && r.poeSwitch) {
      items.push({
        key: 'poe_switch', product_id: r.poeSwitch.id, name: r.poeSwitch.name,
        image: r.poeSwitch.image_url, qty: 1,
        unit_price: parseFloat(r.poeSwitch.price), meta: r.poeSwitch.poe_ports + ' Port / ' + r.poeSwitch.poe_budget_watts + 'W'
      });
    }
    if (D.cable) {
      items.push({
        key: 'cable', product_id: D.cable.id, name: D.cable.name,
        image: D.cable.image_url, qty: r.totalCableM,
        unit_price: parseFloat(D.cable.price_per_meter), meta: r.totalCableM + ' metres'
      });
    }
    return items;
  }

  function renderPackage(r) {
    const items = buildItemsList(r);
    const wrap = $('packageItems');
    wrap.innerHTML = '';

    let subtotal = 0;
    items.forEach(it => {
      const lineTotal = it.unit_price * it.qty;
      subtotal += lineTotal;
      const div = document.createElement('div');
      div.className = 'pkg-item';
      div.innerHTML = `
        <img src="${it.image}" alt="${it.name}" onerror="this.src='${ASSETS_URL}/img/no-image.jpg'">
        <div class="pkg-item-info">
          <div class="pkg-item-name">${it.name}</div>
          <div class="pkg-item-meta">${it.meta}</div>
        </div>
        <div class="pkg-item-price">${fmtPrice(lineTotal)}<small>${it.qty} × ${fmtPrice(it.unit_price)}</small></div>
      `;
      wrap.appendChild(div);
    });

    if (!items.length) {
      wrap.innerHTML = '<p class="text-muted small">No products configured yet for this selection. Please contact us for a manual quote.</p>';
    }

    const discPct = D.settings.packageDiscount;
    const discAmt = subtotal * (discPct / 100);
    const grand   = subtotal - discAmt;

    $('totSubtotal').textContent = fmtPrice(subtotal);
    $('discPercent').textContent = discPct;
    $('totDiscount').textContent = '- ' + fmtPrice(discAmt);
    $('totDiscountRow').style.display = discPct > 0 ? 'flex' : 'none';
    $('totGrand').textContent = fmtPrice(grand);

    lastResult._items = items;
    lastResult._subtotal = subtotal;
    lastResult._discPct = discPct;
    lastResult._discAmt = discAmt;
    lastResult._grand = grand;
  }

  /* ── Lead Modal ── */
  $('openLeadModalBtn').addEventListener('click', () => { $('leadModal').style.display = 'flex'; });
  $('closeLeadModalBtn').addEventListener('click', () => { $('leadModal').style.display = 'none'; });
  $('leadModal').addEventListener('click', e => { if (e.target.id === 'leadModal') $('leadModal').style.display = 'none'; });

  $('leadForm').addEventListener('submit', async function (e) {
    e.preventDefault();
    const btn = $('leadSubmitBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Please wait...';

    const payload = {
      client_name: $('leadName').value.trim(),
      client_phone: $('leadPhone').value.trim(),
      client_email: $('leadEmail').value.trim(),
      system_type: lastResult.systemType,
      camera_count: lastResult.cameraCount,
      resolution_label: lastResult.resolution,
      lens_note: lastResult.lensNote,
      recording_days: lastResult.recDays,
      recording_mode: lastResult.recMode,
      cable_length_m: lastResult.totalCableM,
      required_hdd_gb: lastResult.requiredGb,
      required_poe_watts: lastResult.requiredWatts,
      items: lastResult._items,
      subtotal: lastResult._subtotal,
      discount_percent: lastResult._discPct,
      discount_amount: lastResult._discAmt,
      grand_total: lastResult._grand,
    };

    try {
      const res = await fetch(SITE_URL + '/calculator-submit.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
      });
      const data = await res.json();
      if (data.ok) {
        window.location.href = SITE_URL + '/estimate.php?ref=' + data.ref;
      } else {
        alert(data.error || 'Something went wrong. Please try again or WhatsApp us directly.');
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-paper-plane me-1"></i> Get Estimate';
      }
    } catch (err) {
      alert('Network error. Please check your connection and try again.');
      btn.disabled = false;
      btn.innerHTML = '<i class="fa-solid fa-paper-plane me-1"></i> Get Estimate';
    }
  });

  /* ── Cable length live warning ── */
  $('cableLength').addEventListener('input', function () {
    $('cableWarn').textContent = parseInt(this.value) > 100
      ? '⚠ Over 100m — a PoE extender or fiber converter will be needed.'
      : '';
  });

  /* ── Bind recalculation to all inputs ── */
  ['cameraCount','resolutionTier','cameraProduct','lensType','recordingDays','cableLength']
    .forEach(id => $(id).addEventListener('input', runCalculation));
  ['resolutionTier','cameraProduct','lensType','recordingDays'].forEach(id => $(id).addEventListener('change', runCalculation));

  /* ── Init ── */
  populateCameraDropdown();
  runCalculation();
})();
