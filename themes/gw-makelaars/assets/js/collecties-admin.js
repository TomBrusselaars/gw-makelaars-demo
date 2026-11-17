/* Opsomming als losse rijen (toevoegen, verwijderen, verplaatsen) in plaats van "één punt per regel". */
( function () {
	document.querySelectorAll( 'textarea.gw-rijen' ).forEach( function ( ta ) {
		var lijst = document.createElement( 'div' );
		lijst.className = 'gw-rijen-lijst';
		var nieuw = document.createElement( 'button' );
		nieuw.type = 'button';
		nieuw.className = 'button';
		nieuw.textContent = 'Punt toevoegen';
		ta.style.display = 'none';
		ta.parentNode.insertBefore( lijst, ta );
		ta.parentNode.insertBefore( nieuw, ta.nextSibling );

		function sync() {
			ta.value = Array.prototype.map.call( lijst.querySelectorAll( 'input' ), function ( i ) { return i.value.trim(); } )
				.filter( Boolean ).join( '\n' );
		}
		function knop( tekst, label, fn ) {
			var b = document.createElement( 'button' );
			b.type = 'button';
			b.className = 'button';
			b.textContent = tekst;
			b.setAttribute( 'aria-label', label );
			b.addEventListener( 'click', fn );
			return b;
		}
		function rij( waarde ) {
			var r = document.createElement( 'div' );
			r.className = 'gw-rij';
			var i = document.createElement( 'input' );
			i.type = 'text';
			i.value = waarde || '';
			i.placeholder = 'Bijvoorbeeld: Bezichtigen en onderhandelen';
			i.addEventListener( 'input', sync );
			i.addEventListener( 'keydown', function ( e ) {
				if ( e.key === 'Enter' ) { e.preventDefault(); var n = rij( '' ); r.after( n ); n.querySelector( 'input' ).focus(); }
			} );
			r.appendChild( i );
			r.appendChild( knop( '↑', 'Omhoog', function () { if ( r.previousElementSibling ) { lijst.insertBefore( r, r.previousElementSibling ); sync(); } } ) );
			r.appendChild( knop( '↓', 'Omlaag', function () { if ( r.nextElementSibling ) { lijst.insertBefore( r.nextElementSibling, r ); sync(); } } ) );
			r.appendChild( knop( '×', 'Verwijderen', function () { r.remove(); sync(); } ) );
			return r;
		}
		ta.value.split( /\r?\n/ ).filter( Boolean ).forEach( function ( t ) { lijst.appendChild( rij( t ) ); } );
		if ( ! lijst.children.length ) { lijst.appendChild( rij( '' ) ); }
		nieuw.addEventListener( 'click', function () { var r = rij( '' ); lijst.appendChild( r ); r.querySelector( 'input' ).focus(); } );
	} );
} )();
