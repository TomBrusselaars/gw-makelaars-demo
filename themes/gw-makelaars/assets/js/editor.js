/* Editor: één generiek formulier voor alle blokken, opgebouwd uit het schema in
   inc/componenten.php (window.GW_COMPONENTEN). Geen build-stap nodig. */
( function ( wp ) {
	var el = wp.element.createElement;
	var Fragment = wp.element.Fragment;
	var be = wp.blockEditor;
	var c = wp.components;
	var useSelect = wp.data.useSelect;
	var SSR = wp.serverSideRender;
	var schema = window.GW_COMPONENTEN || {};

	function metWaarde( obj, sleutel, waarde ) {
		var kopie = Object.assign( {}, obj );
		kopie[ sleutel ] = waarde;
		return kopie;
	}

	function leegItem( velden ) {
		var item = {};
		Object.keys( velden ).forEach( function ( k ) {
			var t = velden[ k ].type;
			item[ k ] = t === 'image' || t === 'number' ? 0 : '';
		} );
		return item;
	}

	function BeeldVeld( p ) {
		var media = useSelect( function ( select ) {
			return p.value ? select( 'core' ).getMedia( p.value ) : null;
		}, [ p.value ] );
		var maten = media && media.media_details && media.media_details.sizes;
		var src = maten && maten.medium ? maten.medium.source_url : ( media && media.source_url );
		return el( 'div', { className: 'gw-veld' },
			el( 'span', { className: 'gw-veld__label' }, p.def.label ),
			src ? el( 'img', { src: src, className: 'gw-veld__preview', alt: '' } ) : el( 'p', { className: 'gw-veld__hint' }, 'Nu: de foto uit het ontwerp' ),
			el( be.MediaUploadCheck, null,
				el( be.MediaUpload, {
					allowedTypes: [ 'image' ],
					value: p.value,
					onSelect: function ( m ) { p.onChange( m.id ); },
					render: function ( o ) {
						return el( c.Button, { variant: 'secondary', onClick: o.open }, p.value ? 'Foto vervangen' : 'Foto kiezen' );
					}
				} )
			),
			p.value ? el( c.Button, { variant: 'link', isDestructive: true, onClick: function () { p.onChange( 0 ); } }, 'Terug naar ontwerpfoto' ) : null
		);
	}

	function Veld( p ) {
		var d = p.def;
		var basis = { label: d.label, value: p.value, onChange: p.onChange, __nextHasNoMarginBottom: true };
		if ( d.type === 'info' ) {
			return el( 'div', { className: 'gw-veld' },
				el( 'span', { className: 'gw-veld__label' }, d.label ),
				el( 'p', { className: 'gw-veld__hint' }, d.tekst ),
				d.url ? el( 'a', { href: d.url, target: '_blank', rel: 'noopener' }, 'Contactpersoon kiezen' ) : null
			);
		}
		if ( d.type === 'image' ) { return el( BeeldVeld, p ); }
		if ( d.type === 'lijst' ) { return el( Lijst, p ); }
		if ( d.type === 'textarea' ) { return el( c.TextareaControl, Object.assign( basis, { rows: d.rijen || 4 } ) ); }
		if ( d.type === 'select' ) {
			basis.options = Object.keys( d.opties ).map( function ( k ) { return { value: k, label: d.opties[ k ] }; } );
			return el( c.SelectControl, basis );
		}
		if ( d.type === 'number' ) {
			basis.type = 'number';
			basis.min = d.min;
			basis.max = d.max;
			basis.onChange = function ( v ) { p.onChange( parseInt( v, 10 ) || 0 ); };
			return el( c.TextControl, basis );
		}
		basis.type = d.type === 'url' ? 'url' : 'text';
		return el( c.TextControl, basis );
	}

	function Lijst( p ) {
		var d = p.def;
		var items = Array.isArray( p.value ) ? p.value : [];
		function zet( nieuw ) { p.onChange( nieuw ); }
		function verplaats( i, richting ) {
			var j = i + richting;
			if ( j < 0 || j >= items.length ) { return; }
			var kopie = items.slice();
			var tmp = kopie[ i ];
			kopie[ i ] = kopie[ j ];
			kopie[ j ] = tmp;
			zet( kopie );
		}
		return el( 'div', { className: 'gw-lijst' },
			items.map( function ( item, i ) {
				var titel = ( item && item[ d.titelveld ] ) || '(leeg)';
				return el( c.PanelBody, { key: i, title: ( i + 1 ) + '. ' + titel, initialOpen: false },
					Object.keys( d.velden ).map( function ( k ) {
						return el( Veld, {
							key: k,
							def: d.velden[ k ],
							value: item[ k ],
							onChange: function ( w ) {
								zet( items.map( function ( it, j ) { return j === i ? metWaarde( it, k, w ) : it; } ) );
							}
						} );
					} ),
					el( 'div', { className: 'gw-lijst__acties' },
						el( c.Button, { icon: 'arrow-up-alt2', label: 'Omhoog', disabled: i === 0, onClick: function () { verplaats( i, -1 ); } } ),
						el( c.Button, { icon: 'arrow-down-alt2', label: 'Omlaag', disabled: i === items.length - 1, onClick: function () { verplaats( i, 1 ); } } ),
						el( c.Button, { icon: 'trash', label: 'Verwijderen', isDestructive: true, onClick: function () { zet( items.filter( function ( x, j ) { return j !== i; } ) ); } } )
					)
				);
			} ),
			items.length < d.max ? el( c.Button, { variant: 'secondary', onClick: function () { zet( items.concat( [ leegItem( d.velden ) ] ) ); } }, 'Item toevoegen' ) : null
		);
	}

	function maakEdit( slug, comp ) {
		return function ( props ) {
			var a = props.attributes;
			var groepen = {};
			var volgorde = [];
			Object.keys( comp.velden ).forEach( function ( k ) {
				var d = comp.velden[ k ];
				var g = d.type === 'lijst' ? d.label : ( d.groep || 'Inhoud' );
				if ( ! groepen[ g ] ) { groepen[ g ] = []; volgorde.push( g ); }
				groepen[ g ].push( k );
			} );
			var panelen = volgorde.map( function ( g, n ) {
				return el( c.PanelBody, { key: g, title: g, initialOpen: n < 2 && g !== 'Geavanceerd' },
					groepen[ g ].map( function ( k ) {
						return el( Veld, {
							key: k,
							def: comp.velden[ k ],
							value: a[ k ],
							onChange: function ( w ) { var o = {}; o[ k ] = w; props.setAttributes( o ); }
						} );
					} )
				);
			} );
			return el( Fragment, null,
				el( be.InspectorControls, null, panelen ),
				el( 'div', be.useBlockProps( { className: 'is-home-design gw-blok' } ),
					el( SSR, { block: 'gw/' + slug, attributes: a, httpMethod: 'POST' } )
				)
			);
		};
	}

	Object.keys( schema ).forEach( function ( slug ) {
		var comp = schema[ slug ];
		wp.blocks.registerBlockType( 'gw/' + slug, {
			title: comp.titel,
			icon: comp.icon,
			category: 'gw',
			description: comp.omschr,
			edit: maakEdit( slug, comp ),
			save: function () { return null; }
		} );
	} );

	/* Werkvlak: de pagina wordt altijd op 1440 px breed getoond en verkleind naar de beschikbare
	   ruimte, zodat de editor er hetzelfde uitziet als de site (de layout reageert op de breedte). */
	var DESKTOP = 1440;
	function schaalWerkvlak() {
		var f = document.querySelector( 'iframe[name="editor-canvas"]' );
		if ( ! f || ! f.parentElement ) { return; }
		var box = f.parentElement;
		var w = box.clientWidth;
		var h = box.clientHeight;
		if ( ! w || ! h ) { return; }
		var k = Math.min( 1, w / DESKTOP );
		var sleutel = Math.round( w ) + 'x' + Math.round( h );
		if ( f.getAttribute( 'data-gw-schaal' ) === sleutel && f.style.width === DESKTOP + 'px' ) { return; }
		f.setAttribute( 'data-gw-schaal', sleutel );
		box.style.overflow = 'hidden';
		f.style.width = DESKTOP + 'px';
		f.style.maxWidth = 'none';
		f.style.height = Math.ceil( h / k ) + 'px';
		f.style.transformOrigin = '0 0';
		f.style.transform = k < 1 ? 'scale(' + k + ')' : 'none';
	}
	window.setInterval( schaalWerkvlak, 400 );
	window.addEventListener( 'resize', schaalWerkvlak );
} )( window.wp );
