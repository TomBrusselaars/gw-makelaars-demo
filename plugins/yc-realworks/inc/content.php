<?php
/**
 * content.php — inhoudstype "Aanbod", nette URL's, filters en helpers voor het thema.
 */

defined( 'ABSPATH' ) || exit;

// ---------------------------------------------------------------------------
// Inhoudstype + URL's:  /aanbod/  en  /aanbod/<plaats>/<straat-nr>/
// ---------------------------------------------------------------------------

add_action( 'init', function () {
	register_post_type( YC_RW_CPT, array(
		'labels'       => array(
			'name'          => 'Aanbod',
			'singular_name' => 'Object',
			'menu_name'     => 'Aanbod',
			'all_items'     => 'Alle objecten',
			'edit_item'     => 'Object bekijken',
			'search_items'  => 'Objecten zoeken',
		),
		'public'       => true,
		'has_archive'  => true,
		'show_in_rest' => false,
		'menu_icon'    => 'dashicons-building',
		'supports'     => array( 'title', 'editor' ),
		'rewrite'      => array( 'slug' => 'aanbod', 'with_front' => false ),
		// Inhoud komt uit Realworks: niet handmatig aanmaken.
		'capabilities' => array( 'create_posts' => 'do_not_allow' ),
		'map_meta_cap' => true,
	) );

	// Nette filter-URL's: /aanbod/koop/  /aanbod/kantoorruimte/  /aanbod/huur/kantoorruimte/
	// (moet vóór de objectregel hieronder staan: de eerste passende regel wint).
	$soorten = '(koop|huur)';
	$typen   = '(' . implode( '|', array_keys( yc_rw_functie_slugs() ) ) . ')';
	add_rewrite_rule( '^aanbod/' . $soorten . '/' . $typen . '/?$', 'index.php?post_type=' . YC_RW_CPT . '&rw_aanbod=$matches[1]&rw_functie=$matches[2]', 'top' );
	add_rewrite_rule( '^aanbod/' . $typen . '/?$', 'index.php?post_type=' . YC_RW_CPT . '&rw_functie=$matches[1]', 'top' );
	add_rewrite_rule( '^aanbod/' . $soorten . '/?$', 'index.php?post_type=' . YC_RW_CPT . '&rw_aanbod=$matches[1]', 'top' );

	// /aanbod/rotterdam/schiedamsedijk-100/  ->  post met naam "rotterdam-schiedamsedijk-100"
	add_rewrite_rule(
		'^aanbod/(?!page/|feed/)([^/]+)/([^/]+)/?$',
		'index.php?' . YC_RW_CPT . '=$matches[1]-$matches[2]',
		'top'
	);

	if ( get_option( 'yc_rw_rewrite_version' ) !== YC_RW_VERSION ) {
		flush_rewrite_rules( false );
		update_option( 'yc_rw_rewrite_version', YC_RW_VERSION );
	}
}, 5 );

add_filter( 'post_type_link', function ( $link, $post ) {
	if ( YC_RW_CPT !== $post->post_type || ! get_option( 'permalink_structure' ) ) {
		return $link;
	}
	$plaats = (string) get_post_meta( $post->ID, '_rw_plaats_slug', true );
	if ( '' === $plaats || 0 !== strpos( $post->post_name, $plaats . '-' ) ) {
		return $link;
	}
	return home_url( user_trailingslashit( 'aanbod/' . $plaats . '/' . substr( $post->post_name, strlen( $plaats ) + 1 ) ) );
}, 10, 2 );

// ---------------------------------------------------------------------------
// Helpers voor het thema
// ---------------------------------------------------------------------------

/** Het genormaliseerde object bij een bericht (of null). */
function yc_rw_object( $post_id = 0 ) {
	$post_id = $post_id ?: get_the_ID();
	$json    = get_post_meta( $post_id, '_rw_data', true );
	$data    = $json ? json_decode( $json, true ) : null;
	return is_array( $data ) ? $data : null;
}

/** Mock-links zijn relatief; maak ze absoluut. Realworks-links blijven zoals ze zijn. */
function yc_rw_media_src( $url ) {
	if ( ! $url ) {
		return '';
	}
	return preg_match( '#^https?://#', $url ) ? $url : home_url( $url );
}

function yc_rw_badge_class( $status ) {
	$s = strtoupper( (string) $status );
	if ( 'BESCHIKBAAR' === $s || 'PROSPECT' === $s ) {
		return 'is-beschikbaar';
	}
	if ( 'ONDER_OPTIE' === $s || 'ONDER_BOD' === $s ) {
		return 'is-optie';
	}
	if ( false !== strpos( $s, 'VERHUURD' ) || false !== strpos( $s, 'VERKOCHT' ) ) {
		return 'is-ref';
	}
	return '';
}

/** Functietype in de URL: WINKELRUIMTE <-> winkelruimte, MAATSCHAPPELIJK_VASTGOED <-> maatschappelijk-vastgoed. */
function yc_rw_functie_slugs() {
	$uit = array();
	foreach ( array_keys( yc_rw_functie_labels() ) as $sleutel ) {
		$uit[ strtolower( str_replace( '_', '-', $sleutel ) ) ] = $sleutel;
	}
	return $uit;
}

// De filters zijn query-variabelen; de nette URL's hierboven vullen ze, ?rw_aanbod=… werkt nog (en wordt doorgestuurd).
add_filter( 'query_vars', function ( $vars ) {
	return array_merge( $vars, array( 'rw_aanbod', 'rw_functie' ) );
} );

/**
 * Actieve filters. De waarde komt uit de URL en wordt strikt gecontroleerd tegen een vaste lijst:
 * alles wat daar niet in staat wordt genegeerd.
 */
function yc_rw_filters() {
	$aanbod  = sanitize_key( (string) get_query_var( 'rw_aanbod' ) );
	$functie = strtoupper( str_replace( '-', '_', sanitize_key( (string) get_query_var( 'rw_functie' ) ) ) );
	return array(
		'aanbod'  => in_array( $aanbod, array( 'koop', 'huur' ), true ) ? $aanbod : '',
		'functie' => array_key_exists( $functie, yc_rw_functie_labels() ) ? $functie : '',
	);
}

/** Nette URL voor een filter-combinatie, bv. /aanbod/huur/kantoorruimte/ (zonder ?-parameters). */
function yc_rw_filter_url( array $patch = array() ) {
	$f      = array_merge( yc_rw_filters(), $patch );
	$slugs  = array_flip( yc_rw_functie_slugs() );
	$delen  = array_filter( array( $f['aanbod'], $slugs[ $f['functie'] ] ?? '' ) );
	$basis  = get_post_type_archive_link( YC_RW_CPT );
	return $delen ? user_trailingslashit( trailingslashit( $basis ) . implode( '/', $delen ) ) : $basis;
}

/** Een filter zonder resultaten is een gewone pagina met een melding, geen 404. */
add_filter( 'pre_handle_404', function ( $pre, $q ) {
	return $q->is_post_type_archive( YC_RW_CPT ) && ! is_paged() ? true : $pre;
}, 10, 2 );

/** Oude links (?rw_aanbod=koop&rw_functie=KANTOORRUIMTE) sturen door naar de nette URL. */
add_action( 'template_redirect', function () {
	if ( ! is_post_type_archive( YC_RW_CPT ) || ! get_option( 'permalink_structure' ) ) {
		return;
	}
	if ( ! isset( $_GET['rw_aanbod'] ) && ! isset( $_GET['rw_functie'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification -- alleen doorsturen, geen wijziging
		return;
	}
	wp_safe_redirect( yc_rw_filter_url(), 301 ); // yc_rw_filters() controleert de waarden tegen de vaste lijst
	exit;
} );

function yc_rw_meta_query( array $filters, $alleen_beschikbaar = true ) {
	$mq = array();
	if ( $alleen_beschikbaar ) {
		$mq[] = array( 'key' => '_rw_beschikbaar', 'value' => '1' );
	}
	if ( ! empty( $filters['aanbod'] ) ) {
		$mq[] = array( 'key' => '_rw_aanbod', 'value' => $filters['aanbod'] );
	}
	if ( ! empty( $filters['functie'] ) ) {
		$mq[] = array( 'key' => '_rw_functie', 'value' => $filters['functie'] );
	}
	return $mq;
}

/** Aanbod opvragen vanuit een willekeurige template (bv. de homepage). */
function yc_rw_query( array $filters = array(), $aantal = -1 ) {
	return new WP_Query( array(
		'post_type'      => YC_RW_CPT,
		'post_status'    => 'publish',
		'posts_per_page' => $aantal,
		'meta_query'     => yc_rw_meta_query( $filters ),
		'no_found_rows'  => true,
	) );
}

// Overzichtspagina /aanbod/: alleen beschikbaar aanbod + filters uit de URL
add_action( 'pre_get_posts', function ( $q ) {
	if ( is_admin() || ! $q->is_main_query() || ! $q->is_post_type_archive( YC_RW_CPT ) ) {
		return;
	}
	$q->set( 'posts_per_page', -1 );
	$q->set( 'meta_query', yc_rw_meta_query( yc_rw_filters() ) );
} );

// ---------------------------------------------------------------------------
// Mock-media (alleen in mock-modus): SVG-placeholders + mini-PDF
// ---------------------------------------------------------------------------

add_action( 'init', function () {
	// Alleen in voorbeeldmodus; op de live-site bestaat dit endpoint niet.
	if ( empty( $_GET['rw_mock_media'] ) || 'live' === yc_rw_mode() ) {
		return;
	}
	$obj   = preg_replace( '/[^A-Za-z0-9_-]/', '', (string) ( $_GET['obj'] ?? 'object' ) );
	$n     = (int) ( $_GET['n'] ?? 1 );
	$soort = preg_replace( '/[^A-Z]/', '', (string) ( $_GET['soort'] ?? 'FOTO' ) );
	$groot = '4' === ( $_GET['resize'] ?? '' );

	if ( ! empty( $_GET['pdf'] ) ) {
		header( 'Content-Type: application/pdf' );
		header( 'Content-Disposition: inline; filename="brochure.pdf"' );
		echo yc_rw_mini_pdf( 'Mock brochure - ' . $obj . ' doc ' . $n ); // phpcs:ignore
		exit;
	}

	$h = 0;
	foreach ( str_split( $obj . $soort ) as $c ) {
		$h = ( $h * 31 + ord( $c ) ) % 360;
	}
	$w  = $groot ? 1600 : 640;
	$hh = $groot ? 1067 : 427;

	header( 'X-Content-Type-Options: nosniff' );
	header( 'Content-Type: image/svg+xml' );
	header( 'Cache-Control: public, max-age=3600' );
	if ( 'PLATTEGROND' === $soort ) {
		echo '<svg xmlns="http://www.w3.org/2000/svg" width="' . $w . '" height="' . $hh . '" viewBox="0 0 640 427">'
			. '<rect width="640" height="427" fill="#f4f4f1"/>'
			. '<g stroke="#2b2b2b" stroke-width="3" fill="none"><rect x="80" y="60" width="480" height="300"/>'
			. '<line x1="320" y1="60" x2="320" y2="360"/><line x1="80" y1="210" x2="320" y2="210"/>'
			. '<rect x="360" y="90" width="160" height="110" stroke-width="2"/></g>'
			. '<text x="320" y="400" text-anchor="middle" font-family="Arial" font-size="18" fill="#555">Plattegrond ' . $n . ' — ' . $obj . '</text></svg>';
		exit;
	}
	$titel = 'HOOFDFOTO' === $soort ? 'Hoofdfoto' : 'Foto ' . $n;
	echo '<svg xmlns="http://www.w3.org/2000/svg" width="' . $w . '" height="' . $hh . '" viewBox="0 0 640 427">'
		. '<defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1">'
		. '<stop offset="0" stop-color="hsl(' . $h . ',35%,42%)"/><stop offset="1" stop-color="hsl(' . ( ( $h + 40 ) % 360 ) . ',40%,30%)"/>'
		. '</linearGradient></defs><rect width="640" height="427" fill="url(#g)"/>'
		. '<rect x="40" y="250" width="560" height="140" fill="rgba(255,255,255,.08)"/>'
		. '<rect x="90" y="150" width="120" height="240" fill="rgba(0,0,0,.12)"/>'
		. '<rect x="430" y="120" width="140" height="270" fill="rgba(0,0,0,.15)"/>'
		. '<text x="320" y="210" text-anchor="middle" font-family="Georgia" font-size="34" fill="#fff" opacity=".95">' . $titel . '</text>'
		. '<text x="320" y="245" text-anchor="middle" font-family="Arial" font-size="16" fill="#fff" opacity=".75">' . $obj . ' · ' . ( $groot ? 'origineel (resize=4)' : 'thumbnail' ) . '</text></svg>';
	exit;
}, 1 );

/** Minimale, geldige PDF (1 pagina met tekst). */
function yc_rw_mini_pdf( $label ) {
	$text    = preg_replace( '/[^A-Za-z0-9 .\-]/', '', $label );
	$content = 'BT /F1 20 Tf 60 740 Td (' . $text . ') Tj ET';
	$objs    = array(
		'1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj',
		'2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj',
		'3 0 obj<</Type/Page/Parent 2 0 R/MediaBox[0 0 595 842]/Resources<</Font<</F1 4 0 R>>>>/Contents 5 0 R>>endobj',
		'4 0 obj<</Type/Font/Subtype/Type1/BaseFont/Helvetica>>endobj',
		'5 0 obj<</Length ' . strlen( $content ) . ">>stream\n" . $content . "\nendstream endobj",
	);
	$pdf     = "%PDF-1.4\n";
	$offsets = array();
	foreach ( $objs as $o ) {
		$offsets[] = strlen( $pdf );
		$pdf      .= $o . "\n";
	}
	$xref = strlen( $pdf );
	$pdf .= 'xref' . "\n0 " . ( count( $objs ) + 1 ) . "\n0000000000 65535 f \n";
	foreach ( $offsets as $off ) {
		$pdf .= str_pad( (string) $off, 10, '0', STR_PAD_LEFT ) . " 00000 n \n";
	}
	return $pdf . 'trailer<</Size ' . ( count( $objs ) + 1 ) . "/Root 1 0 R>>\nstartxref\n" . $xref . "\n%%EOF";
}
