<?php
/**
 * GW makelaars — thema-functies.
 */

defined( 'ABSPATH' ) || exit;

require_once get_template_directory() . '/inc/atomen.php';
require_once get_template_directory() . '/inc/componenten.php';
require_once get_template_directory() . '/inc/collecties.php';
require_once get_template_directory() . '/inc/blocks.php';
require_once get_template_directory() . '/inc/patronen.php';
require_once get_template_directory() . '/inc/formulier.php';
require_once get_template_directory() . '/inc/stijlgids.php';

add_action( 'after_setup_theme', function () {
	add_theme_support( 'title-tag' );
	add_theme_support( 'html5', array( 'script', 'style', 'search-form', 'gallery', 'caption' ) );
	add_theme_support( 'post-thumbnails' ); // uitgelichte afbeelding bij nieuwsberichten
	register_nav_menus( array(
		'hoofdmenu' => 'Hoofdmenu',
		'footermenu' => 'Footer',
	) );
} );

add_action( 'wp_enqueue_scripts', function () {
	$dir = get_template_directory();
	$uri = get_template_directory_uri();
	wp_enqueue_style( 'gw-home', $uri . '/assets/css/home.css', array(), filemtime( $dir . '/assets/css/home.css' ) );
	wp_enqueue_script( 'gw-home', $uri . '/assets/js/home.js', array(), filemtime( $dir . '/assets/js/home.js' ), array( 'in_footer' => true, 'strategy' => 'defer' ) );

	wp_enqueue_script( 'gw-cursor', $uri . '/assets/js/cursor.js', array(), filemtime( $dir . '/assets/js/cursor.js' ), array( 'in_footer' => true, 'strategy' => 'defer' ) );

	if ( is_singular( 'rw_object' ) && gw_koppeling_actief() ) {
		$o = yc_rw_object();
		wp_enqueue_style( 'leaflet', $uri . '/assets/vendor/leaflet/leaflet.css', array(), '1.9.4' );
		wp_enqueue_script( 'leaflet', $uri . '/assets/vendor/leaflet/leaflet.js', array(), '1.9.4', array( 'in_footer' => true ) );
		wp_enqueue_script( 'gw-object', $uri . '/assets/js/object.js', array( 'leaflet' ), filemtime( $dir . '/assets/js/object.js' ), array( 'in_footer' => true, 'strategy' => 'defer' ) );
		wp_localize_script( 'gw-object', 'GW_OBJECT', array(
			'rest'    => esc_url_raw( rest_url( 'yc-realworks/v1/' ) ),
			'post'    => get_the_ID(),
			'galerij' => $o ? array_map( function ( $f ) {
				return yc_rw_media_src( $f['urlGroot'] ?: $f['url'] );
			}, $o['media']['fotos'] ) : array(),
		) );
	}
} );

/** Markeer de pagina als "JS aan" vóór de eerste verf, zodat animaties niet flitsen. */
add_action( 'wp_head', function () {
	wp_print_inline_script_tag( "document.documentElement.classList.add('js');" );
}, 0 );

/** Staat de Realworks-plugin aan? Het thema blijft ook zonder werken. */
function gw_koppeling_actief() {
	return function_exists( 'yc_rw_object' );
}

function gw_getal( $n ) {
	return number_format( (float) $n, 0, ',', '.' );
}

// ---------------------------------------------------------------------------
// Door de klant te beheren teksten: Weergave → Customizer → "GW makelaars"
// ---------------------------------------------------------------------------

function gw_standaard() {
	return array(
		'contact_naam' => 'Stan Brinkman',
		'contact_rol'  => 'Partner & Makelaar',
		'telefoon'     => '(010) 422 32 20',
		'email'        => 'info@gwmakelaars.nl',
		'adres_regel1' => 'Westplein 5,',
		'adres_regel2' => '3016 BM Rotterdam',
		'aanbod_kop'   => 'Wat er nu beschikbaar is', // kop van het aanbodoverzicht
		'taalkeuze'    => 1,
	);
}

function gw_mod( $key ) {
	$std = gw_standaard();
	return get_theme_mod( 'gw_' . $key, $std[ $key ] ?? '' );
}

add_action( 'customize_register', function ( $wp_customize ) {
	$velden = array(
		'gw_merk' => array( 'Contact & merk (voor de hele site)', array(
			'contact_naam' => array( 'Naam contactpersoon', 'text' ),
			'contact_rol'  => array( 'Functie contactpersoon', 'text' ),
			'telefoon'     => array( 'Telefoonnummer', 'text' ),
			'email'        => array( 'E-mailadres', 'email' ),
			'adres_regel1' => array( 'Adres — regel 1', 'text' ),
			'adres_regel2' => array( 'Adres — regel 2', 'text' ),
			'taalkeuze'    => array( 'Taalkeuze tonen in de header', 'checkbox' ),
		) ),
		'gw_links' => array( 'Social media & juridische links', array(
			'social_facebook'  => array( 'Facebook-pagina (URL)', 'url' ),
			'social_linkedin'  => array( 'LinkedIn-pagina (URL)', 'url' ),
			'social_funda'     => array( 'Funda Business-pagina (URL)', 'url' ),
			'link_voorwaarden' => array( 'Algemene voorwaarden (URL)', 'url' ),
			'link_cookies'     => array( 'Cookies (URL)', 'url' ),
		) ),
		'gw_aanbod' => array( 'Aanbodoverzicht', array(
			'aanbod_kop' => array( 'Kop boven het aanbod (/aanbod/)', 'text' ),
		) ),
	);
	$schoon = array(
		'email' => 'sanitize_email',
		'url'   => 'esc_url_raw',
		'textarea' => 'sanitize_textarea_field',
		'text'  => 'sanitize_text_field',
		'checkbox' => 'rest_sanitize_boolean',
	);
	$std = gw_standaard();
	$wp_customize->add_panel( 'gw', array( 'title' => 'GW makelaars', 'priority' => 20, 'description' => 'Teksten en foto\'s van de homepage wijzig je in Pagina\'s → Home (blokken). Hier staat wat op de hele site terugkomt.' ) );
	foreach ( $velden as $id => $sectie ) {
		$wp_customize->add_section( $id, array( 'title' => $sectie[0], 'panel' => 'gw' ) );
		foreach ( $sectie[1] as $key => $def ) {
			$wp_customize->add_setting( 'gw_' . $key, array( 'default' => $std[ $key ] ?? '', 'sanitize_callback' => $schoon[ $def[1] ] ) );
			$wp_customize->add_control( 'gw_' . $key, array(
				'label'   => $def[0],
				'section' => $id,
				'type'    => $def[1],
			) );
		}
	}
	// Contactpersoon: één keuze uit Team, gebruikt in hero, afsluiter en (optioneel) footer en menu.
	$keuzes = array( 0 => 'Geen (gegevens hieronder gebruiken)' );
	foreach ( gw_collectie( 'gw_team' ) as $lid ) {
		$keuzes[ $lid->ID ] = get_the_title( $lid );
	}
	$wp_customize->add_setting( 'gw_contactpersoon', array( 'default' => 0, 'sanitize_callback' => static function ( $w ) {
		$p = get_post( absint( $w ) );
		return $p && 'gw_team' === $p->post_type ? $p->ID : 0;
	} ) );
	$wp_customize->add_control( 'gw_contactpersoon', array(
		'label'       => 'Contactpersoon',
		'description' => 'Wie we laten zien in het kaartje van de hero, in de afsluiter en (hieronder) in footer en menu. Teamleden beheer je onder Team.',
		'section'     => 'gw_merk',
		'type'        => 'select',
		'choices'     => $keuzes,
		'priority'    => 1,
	) );
	$wp_customize->add_setting( 'gw_footer_volgt_persoon', array( 'default' => 1, 'sanitize_callback' => 'rest_sanitize_boolean' ) );
	$wp_customize->add_control( 'gw_footer_volgt_persoon', array(
		'label'       => 'Footer en menu tonen telefoon en e-mail van de contactpersoon',
		'description' => 'Uit: footer en menu tonen de algemene gegevens hieronder.',
		'section'     => 'gw_merk',
		'type'        => 'checkbox',
		'priority'    => 2,
	) );
	$wp_customize->add_setting( 'gw_contact_foto', array( 'default' => 0, 'sanitize_callback' => 'absint' ) );
	$wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, 'gw_contact_foto', array(
		'label'     => 'Portret (alleen als er geen contactpersoon uit Team gekozen is)',
		'section'   => 'gw_merk',
		'mime_type' => 'image',
	) ) );
} );

// ---------------------------------------------------------------------------
// Template-helpers
// ---------------------------------------------------------------------------

// Structured data op de objectpagina (vindbaarheid)
add_action( 'wp_head', function () {
	if ( ! is_singular( 'rw_object' ) || ! gw_koppeling_actief() ) {
		return;
	}
	$o = yc_rw_object();
	if ( ! $o ) {
		return;
	}
	$ld = array(
		'@context'    => 'https://schema.org',
		'@type'       => 'RealEstateListing',
		'name'        => $o['adres']['volledig'],
		'url'         => get_permalink(),
		'description' => wp_trim_words( $o['teksten']['aanbieding'], 40 ),
		'image'       => $o['media']['hoofdfoto'] ? yc_rw_media_src( $o['media']['hoofdfoto']['urlGroot'] ) : null,
		'address'     => array(
			'@type'           => 'PostalAddress',
			'streetAddress'   => $o['adres']['straatNr'],
			'postalCode'      => $o['adres']['postcode'],
			'addressLocality' => $o['adres']['plaats'],
			'addressCountry'  => 'NL',
		),
	);
	echo '<meta name="description" content="' . esc_attr( wp_trim_words( $o['teksten']['aanbieding'], 25 ) ) . '">' . "\n";
	echo '<script type="application/ld+json">' . wp_json_encode( array_filter( $ld ) ) . '</script>' . "\n";
} );
