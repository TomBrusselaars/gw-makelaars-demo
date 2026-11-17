/* Objectpagina: lightbox, kaart en aanvraagformulier. Data komt uit GW_OBJECT (functions.php). */
(function () {
  var cfg = window.GW_OBJECT || {};
  var GAL = cfg.galerij || [];

  // ---- Lightbox ----
  var lb = document.getElementById('lb');
  var lbimg = document.getElementById('lbimg');
  var idx = 0;
  function show() { lbimg.src = GAL[idx]; }
  document.querySelectorAll('.object_gallery-item').forEach(function (g) {
    g.addEventListener('click', function () {
      if (!GAL.length) return;
      idx = +g.dataset.i || 0; show(); lb.hidden = false;
    });
  });
  document.querySelectorAll('.object_plans img').forEach(function (im) {
    im.addEventListener('click', function () { lbimg.src = im.dataset.big || im.src; lb.hidden = false; });
  });
  document.getElementById('lbx').onclick = function () { lb.hidden = true; };
  document.getElementById('lbp').onclick = function () { idx = (idx - 1 + GAL.length) % GAL.length; show(); };
  document.getElementById('lbn').onclick = function () { idx = (idx + 1) % GAL.length; show(); };
  lb.addEventListener('click', function (e) { if (e.target === lb) lb.hidden = true; });
  document.addEventListener('keydown', function (e) {
    if (lb.hidden) return;
    if (e.key === 'Escape') lb.hidden = true;
    if (e.key === 'ArrowLeft') document.getElementById('lbp').click();
    if (e.key === 'ArrowRight') document.getElementById('lbn').click();
  });

  // ---- Kaart: coördinaten komen van de eigen site (PDOK, bewaard bij het object) ----
  var mapEl = document.getElementById('map');
  fetch(cfg.rest + 'geocode?post=' + cfg.post)
    .then(function (r) { return r.json(); })
    .then(function (d) {
      if (!d.coords || typeof L === 'undefined') throw new Error('geen coords');
      var map = L.map('map', { scrollWheelZoom: false }).setView([d.coords.lat, d.coords.lon], 16);
      L.tileLayer('https://service.pdok.nl/brt/achtergrondkaart/wmts/v2_0/standaard/EPSG:3857/{z}/{x}/{y}.png', { attribution: 'PDOK / Kadaster', maxZoom: 19 }).addTo(map);
      L.marker([d.coords.lat, d.coords.lon]).addTo(map);
    })
    .catch(function () {
      mapEl.style.display = 'none';
      document.getElementById('map-fallback').hidden = false;
    });

  // ---- Aanvraagformulier -> eigen site -> Realworks ----
  var form = document.getElementById('aanvraag');
  var msg = document.getElementById('fmsg');
  form.addEventListener('submit', function (e) {
    e.preventDefault();
    var btn = form.querySelector('button');
    btn.disabled = true; btn.textContent = 'Versturen…';
    msg.hidden = true;
    var data = { post: cfg.post };
    new FormData(form).forEach(function (v, k) { data[k] = v; });
    fetch(cfg.rest + 'aanvraag', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(data) })
      .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, d: d }; }); })
      .then(function (res) {
        if (!res.ok) throw new Error(res.d.message || 'Er ging iets mis.');
        var result = res.d.result || {};
        msg.className = 'formulier_melding'; msg.hidden = false;
        msg.textContent = result.mode === 'live'
          ? 'Bedankt! Je aanvraag staat in Realworks.'
          : 'Bedankt! (voorbeeldmodus — hieronder wat er naar Realworks zou gaan.)';
        form.reset();
      })
      .catch(function (err) { msg.className = 'formulier_melding is-fout'; msg.hidden = false; msg.textContent = err.message; })
      .then(function () { btn.disabled = false; btn.textContent = 'Informatie aanvragen'; });
  });
})();
