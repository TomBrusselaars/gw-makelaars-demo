/* Homepage: binnenkomst-animaties, menu, aanbodfilter, diensten, FAQ en nieuws-slider. */
(function () {
  var root = document.documentElement;
  var lijstVan = function (sel, el) { return Array.prototype.slice.call((el || document).querySelectorAll(sel)); };

  // ---- Binnenkomst: elementen met data-reveal komen in beeld zodra je erlangs scrollt ----
  var reveals = lijstVan('[data-reveal]');
  if ('IntersectionObserver' in window) {
    // Een "deur" is dichtgeknipt en telt daardoor zelf niet als zichtbaar:
    // daarom kijken we bij deuren naar het omringende element.
    var doelen = new Map();
    reveals.forEach(function (el) {
      var doel = el.getAttribute('data-reveal') === 'door' ? el.parentElement : el;
      if (!doelen.has(doel)) doelen.set(doel, []);
      doelen.get(doel).push(el);
    });
    var io = new IntersectionObserver(function (items) {
      items.forEach(function (it) {
        if (!it.isIntersecting) return;
        doelen.get(it.target).forEach(function (el) {
          // een foto-deur opent pas als de foto klaar is, zodat hij niet "inpopt" tijdens het openen
          var img = el.getAttribute('data-reveal') === 'door' ? el.querySelector('img') : null;
          if (img && img.decode && !img.complete) {
            Promise.race([img.decode(), new Promise(function (r) { setTimeout(r, 1500); })]).catch(function () {}).then(function () { el.classList.add('is-inview'); });
          } else {
            el.classList.add('is-inview');
          }
        });
        io.unobserve(it.target);
      });
    }, { rootMargin: '0px 0px -8% 0px', threshold: 0.05 });
    // heel even wachten, zodat de hero bij het laden zichtbaar "opengaat"
    setTimeout(function () { doelen.forEach(function (els, doel) { io.observe(doel); }); }, 120);
  } else {
    reveals.forEach(function (el) { el.classList.add('is-inview'); });
  }

  // ---- Menu ----
  var knop = document.querySelector('.nav_menu-button');
  var menu = document.getElementById('nav-menu');
  if (knop && menu) {
    // Het paneel groeit precies vanuit de menuknop: positie en maat van de knop meten.
    var meet = function () {
      var k = knop.getBoundingClientRect(), p = menu.getBoundingClientRect();
      if (!p.width || !p.height) return;
      menu.style.setProperty('--menu-sx', k.width / p.width);
      menu.style.setProperty('--menu-sy', k.height / p.height);
      menu.style.setProperty('--menu-tx', (k.right - p.right) + 'px');
      menu.style.setProperty('--menu-ty', (k.top - p.top) + 'px');
    };
    meet();
    window.addEventListener('resize', meet);
    // Terwijl het menu open is, kan niets erachter bediend worden (toetsenbord of schermlezer).
    var achtergrond = lijstVan('.main-wrapper, .footer_component, .nav_component');
    var zet = function (open) {
      if (open) meet();
      achtergrond.forEach(function (e) { if (open) e.setAttribute('inert', ''); else e.removeAttribute('inert'); });
      root.classList.toggle('is-menu-open', open);
      knop.setAttribute('aria-expanded', open ? 'true' : 'false');
      menu.setAttribute('aria-hidden', open ? 'false' : 'true');
      if (open) { var sluit = menu.querySelector('.nav_menu-close'); if (sluit) sluit.focus({ preventScroll: true }); }
      else if (document.activeElement && menu.contains(document.activeElement)) knop.focus({ preventScroll: true });
    };
    knop.addEventListener('click', function () { zet(true); });
    lijstVan('[data-menu-sluit]').forEach(function (el) { el.addEventListener('click', function () { zet(false); }); });
    menu.addEventListener('click', function (e) { if (e.target.closest('a')) zet(false); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') zet(false); });
  }

  // ---- Aanbodfilter: zonder herladen, met een pil die naar de keuze schuift ----
  var filter = document.querySelector('[data-aanbod-filter]');
  if (filter) {
    var links = lijstVan('.filter-tabs_link', filter);
    var pil = filter.querySelector('.filter-tabs_pill');
    var kaarten = lijstVan('.aanbod-card');
    var aantal = document.querySelector('[data-aanbod-aantal]');
    var plaatsPil = function () {
      var actief = filter.querySelector('.is-active') || links[0];
      pil.style.width = actief.offsetWidth + 'px';
      pil.style.transform = 'translateX(' + actief.offsetLeft + 'px)';
    };
    filter.classList.add('has-pill');
    plaatsPil();
    window.addEventListener('resize', plaatsPil);
    if (document.fonts && document.fonts.ready) document.fonts.ready.then(plaatsPil);
    links.forEach(function (link) {
      link.addEventListener('click', function (e) {
        e.preventDefault();
        var waarde = link.getAttribute('data-waarde');
        links.forEach(function (l) { l.classList.toggle('is-active', l === link); });
        plaatsPil();
        var n = 0;
        kaarten.forEach(function (k) {
          var past = !waarde || (k.getAttribute('data-aanbod') || '').indexOf(waarde) !== -1;
          if (past) n++;
          var toon = past && n <= 9;
          k.hidden = !toon;
          k.classList.remove('is-wissel');
          if (toon) {
            k.classList.add('is-inview');
            k.style.setProperty('--i', (n - 1) % 9);
            void k.offsetWidth; // animatie opnieuw starten
            k.classList.add('is-wissel');
          }
        });
        if (aantal) aantal.textContent = n;
      });
    });
  }

  function open(items, item, triggerSel) {
    items.forEach(function (it) {
      var aan = it === item;
      it.classList.toggle('is-open', aan);
      it.querySelector(triggerSel).setAttribute('aria-expanded', aan ? 'true' : 'false');
    });
  }

  // ---- Diensten: één open, loopt vanzelf door tot de bezoeker zelf kiest; de foto wisselt mee ----
  var lijst = document.querySelector('[data-diensten]');
  if (lijst) {
    var items = lijstVan('.home-diensten_item', lijst);
    var beelden = lijstVan('[data-diensten-beeld] .home-diensten_image');
    var rustig = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var kies = function (i) {
      open(items, items[i], '.home-diensten_trigger');
      beelden.forEach(function (b, j) { b.classList.toggle('is-active', j === i); });
    };
    items.forEach(function (it, i) {
      it.querySelector('.home-diensten_trigger').addEventListener('click', function () { lijst.classList.remove('is-running'); kies(i); });
      it.querySelector('.home-diensten_line span').addEventListener('animationend', function () {
        if (lijst.classList.contains('is-running')) kies((i + 1) % items.length);
      });
    });
    if (!rustig && 'IntersectionObserver' in window) {
      new IntersectionObserver(function (e, obs) {
        if (e[0].isIntersecting) { lijst.classList.add('is-running'); obs.disconnect(); }
      }, { threshold: 0.2 }).observe(lijst);
    }
  }

  // ---- FAQ: klik opent, nog een klik sluit ----
  var faq = document.querySelector('[data-faq]');
  if (faq) {
    var vragen = lijstVan('.faq_item', faq);
    vragen.forEach(function (it) {
      it.querySelector('.faq_question').addEventListener('click', function () {
        open(vragen, it.classList.contains('is-open') ? null : it, '.faq_question');
      });
    });
  }

  // ---- Nieuws: start bij de tweede kaart (zoals in het ontwerp) en sleepbaar met de muis ----
  var track = document.querySelector('[data-nieuws]');
  if (track) {
    var tweede = track.children[1];
    if (tweede && track.hasAttribute('data-start')) track.scrollLeft = tweede.offsetLeft - track.children[0].offsetLeft;
    var sleept = false, bewoog = false, startX = 0, startScroll = 0;
    track.addEventListener('pointerdown', function (e) {
      if (e.pointerType !== 'mouse') return;
      sleept = true; bewoog = false; startX = e.clientX; startScroll = track.scrollLeft;
    });
    window.addEventListener('pointermove', function (e) {
      if (!sleept) return;
      var dx = e.clientX - startX;
      if (Math.abs(dx) > 4) { bewoog = true; track.classList.add('is-dragging'); }
      track.scrollLeft = startScroll - dx;
    });
    window.addEventListener('pointerup', function () { sleept = false; track.classList.remove('is-dragging'); });
    track.addEventListener('click', function (e) { if (bewoog) { e.preventDefault(); bewoog = false; } }, true);
  }
})();
