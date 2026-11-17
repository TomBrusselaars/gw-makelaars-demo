/* Eigen cursor: klein vierkantje; groter en doorzichtiger boven links en knoppen;
   vierkant met pijlen boven sleepbare onderdelen (data-cursor="drag").
   Alleen met een muis (geen touch). Uit te zetten met --cursor-enabled: 0 in de Stijlgids.
   data-cursor="native" op een element laat daar de gewone cursor staan (kaart, invoervelden). */
( function () {
	function init( scope ) {
		if ( ! window.matchMedia( '(hover: hover) and (pointer: fine)' ).matches ) { return; }
		if ( getComputedStyle( scope ).getPropertyValue( '--cursor-enabled' ).trim() === '0' ) { return; }
		var rustig = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
		var el = document.createElement( 'div' );
		el.className = 'cursor';
		el.setAttribute( 'aria-hidden', 'true' );
		el.innerHTML = '<span class="cursor_box"><svg class="cursor_icon" viewBox="0 0 20 16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="square"><path class="is-links" d="M19 8H1M5 4 1 8l4 4"/><path class="is-rechts" d="M1 8h18M15 4l4 4-4 4"/></svg></span>';
		( scope === document.documentElement ? document.body : scope ).appendChild( el );

		var x = 0, y = 0, tx = 0, ty = 0, actief = false, bezig = false;
		var linkSel = 'a[href], button, [role="button"], summary, label, [data-cursor="link"]';
		var nativeSel = '[data-cursor="native"], input, textarea, select, [contenteditable="true"]';

		function teken() {
			x += ( tx - x ) * ( rustig ? 1 : 0.25 );
			y += ( ty - y ) * ( rustig ? 1 : 0.25 );
			el.style.transform = 'translate3d(' + x.toFixed( 1 ) + 'px,' + y.toFixed( 1 ) + 'px,0)';
			if ( Math.abs( tx - x ) > 0.3 || Math.abs( ty - y ) > 0.3 ) { requestAnimationFrame( teken ); } else { bezig = false; }
		}
		function stand( doel ) {
			if ( doel.closest( nativeSel ) ) { return 'native'; }
			if ( doel.closest( '[data-cursor="drag"]' ) ) { return 'drag'; }
			if ( doel.closest( linkSel ) ) { return 'link'; }
			return 'standaard';
		}
		var laatste = null, inkNu = '', inkTimer = null, standTimer = null;
		/* Tekstkleur van het omringende blok: inline onderdelen (gekleurd woord, pijl-icoon, afbeelding)
		   tellen niet mee, anders wisselt de kleur bij elk klein element. */
		function inkVan( doel ) {
			var n = doel;
			while ( n && n !== scope && n.nodeType === 1 ) {
				var d = getComputedStyle( n ).display;
				if ( n.namespaceURI.indexOf( 'svg' ) > -1 || d.indexOf( 'inline' ) === 0 || n.tagName === 'IMG' ) { n = n.parentElement; } else { break; }
			}
			return getComputedStyle( n || doel ).color;
		}
		/* Eén kleur: die van de tekst onder de muis, of een vaste kleur uit de Stijlgids.
		   Een nieuwe kleur gaat pas in als de muis er even blijft (geen geflikker in krappe ruimtes). */
		function kleur( doel ) {
			var vast = getComputedStyle( scope ).getPropertyValue( '--cursor-color' ).trim();
			var c = vast && vast !== 'auto' ? vast : inkVan( doel );
			clearTimeout( inkTimer );
			if ( c === inkNu ) { return; }
			inkTimer = setTimeout( function () { inkNu = c; el.style.setProperty( '--cursor-ink', c ); }, inkNu ? 90 : 0 );
		}
		/* Boven een sleepbaar onderdeel: één pijl, afhankelijk van links of rechts van het midden. */
		function kant( doel, e ) {
			var z = doel.closest( '[data-cursor="drag"]' );
			if ( ! z ) { return; }
			var r = z.getBoundingClientRect();
			var kant = e.clientX < r.left + r.width / 2 ? 'links' : 'rechts';
			if ( el.getAttribute( 'data-side' ) !== kant ) { el.setAttribute( 'data-side', kant ); }
		}
		/* Terug naar "standaard" met een korte vertraging: tussen twee naastliggende links springt hij dan niet heen en weer. */
		function zetStand( s ) {
			clearTimeout( standTimer );
			var huidig = el.getAttribute( 'data-state' );
			if ( s === 'standaard' && ( huidig === 'link' || huidig === 'drag' ) ) {
				standTimer = setTimeout( function () { el.setAttribute( 'data-state', 'standaard' ); }, 70 );
			} else if ( huidig !== s ) {
				el.setAttribute( 'data-state', s );
			}
		}
		function zet( doel, e ) {
			if ( doel !== laatste ) { laatste = doel; kleur( doel ); }
			var s = stand( doel );
			if ( s === 'drag' ) { kant( doel, e ); }
			var inheems = s === 'native';
			scope.classList.toggle( 'cursor-native', inheems );
			el.classList.toggle( 'is-visible', ! inheems );
			zetStand( inheems ? 'standaard' : s );
		}
		scope.addEventListener( 'mousemove', function ( e ) {
			tx = e.clientX; ty = e.clientY;
			if ( ! actief ) { actief = true; x = tx; y = ty; scope.classList.add( 'has-cursor' ); }
			zet( e.target, e );
			if ( ! bezig ) { bezig = true; requestAnimationFrame( teken ); }
		}, { passive: true } );
		scope.addEventListener( 'mouseleave', function () { el.classList.remove( 'is-visible' ); scope.classList.remove( 'cursor-native' ); } );
		scope.addEventListener( 'mousedown', function () { el.classList.add( 'is-down' ); } );
		window.addEventListener( 'mouseup', function () { el.classList.remove( 'is-down' ); } );
	}
	window.GWCursor = { init: init };
	if ( ! document.body.classList.contains( 'wp-admin' ) ) { init( document.documentElement ); }
} )();
