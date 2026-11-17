/* Stijlgids: wijzigingen direct in het voorbeeld tonen (opslaan gebeurt via het formulier). */
( function () {
	var preview = document.getElementById( 'gw-preview' );
	if ( ! preview ) { return; }
	function zet( el ) {
		var token = el.getAttribute( 'data-token' );
		var waarde = el.tagName === 'SELECT' ? el.options[ el.selectedIndex ].getAttribute( 'data-css' ) : el.value.trim();
		if ( waarde ) { preview.style.setProperty( '--' + token, waarde ); } else { preview.style.removeProperty( '--' + token ); }
	}
	document.querySelectorAll( '[data-token]' ).forEach( function ( el ) {
		el.addEventListener( 'input', function () { zet( el ); } );
		el.addEventListener( 'change', function () { zet( el ); } );
	} );
	document.querySelectorAll( '[data-kleurkiezer]' ).forEach( function ( k ) {
		k.addEventListener( 'input', function () {
			var tekst = k.parentNode.querySelector( 'input[type=text]' );
			tekst.value = k.value;
			zet( tekst );
		} );
	} );
	if ( window.GWCursor ) { window.GWCursor.init( preview ); } // de cursor werkt binnen het voorbeeld
	var demo = document.getElementById( 'gw-demo' );
	document.getElementById( 'gw-speel' ).addEventListener( 'click', function () {
		demo.classList.remove( 'is-in' );
		void demo.offsetWidth;
		requestAnimationFrame( function () { demo.classList.add( 'is-in' ); } );
	} );
	demo.classList.add( 'is-in' );
} )();
