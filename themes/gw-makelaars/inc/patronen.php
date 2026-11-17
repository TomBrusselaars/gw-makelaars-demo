<?php
/**
 * Kant-en-klare pagina's (patronen): de klant kiest er één bij "Nieuwe pagina" of via
 * de blokkiezer (+ → Patronen → Landingspagina's) en past alleen de teksten en foto's aan.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', function () {
	register_block_pattern_category( 'gw-landing', array( 'label' => 'Landingspagina\'s' ) );

	$blok = static function ( $slug, array $attrs = array() ) {
		return '<!-- wp:gw/' . $slug . ( $attrs ? ' ' . wp_json_encode( $attrs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) : '' ) . ' /-->';
	};

	register_block_pattern( 'gw/landing-dienst', array(
		'title'       => 'Landingspagina — dienst of regio',
		'description' => 'Hero, voordelen, tekst met foto, veelgestelde vragen en contactformulier.',
		'categories'  => array( 'gw-landing' ),
		'postTypes'   => array( 'page' ),
		'content'     => implode( "\n\n", array(
			$blok( 'hero', array( 'regel_voor' => 'Kantoorruimte in', 'accent' => 'Schiedam', 'regel_na' => 'huren of kopen', 'tekst' => 'Korte introductie van deze dienst of regio, in één of twee zinnen.', 'knop_label' => 'Plan een gesprek', 'knop_url' => '#contact' ) ),
			$blok( 'kaarten', array( 'tag' => 'Voordelen', 'kop' => 'Waarom met ons?' ) ),
			$blok( 'tekst-beeld', array( 'tag' => 'Aanpak', 'kop' => 'Zo werken wij', 'tekst' => "Eerste alinea over de aanpak.\n\nTweede alinea met meer detail.", 'knop_label' => 'Meer over ons', 'knop_url' => '/' ) ),
			$blok( 'faq', array( 'kop' => 'Veelgestelde vragen' ) ),
			$blok( 'formulier' ),
		) ),
	) );

	register_block_pattern( 'gw/landing-aanbod', array(
		'title'       => 'Landingspagina — aanbod en actie',
		'description' => 'Hero, live aanbod uit Realworks, actiebalk en contact.',
		'categories'  => array( 'gw-landing' ),
		'postTypes'   => array( 'page' ),
		'content'     => implode( "\n\n", array(
			$blok( 'hero', array( 'regel_voor' => 'Bedrijfsruimte', 'accent' => 'direct', 'regel_na' => 'beschikbaar', 'tekst' => 'Bekijk het actuele aanbod en neem direct contact op.', 'knop_label' => 'Bekijk het aanbod', 'knop_url' => '#aanbod' ) ),
			$blok( 'aanbod' ),
			$blok( 'cta', array( 'kop' => 'Niet gevonden wat je zoekt?', 'tekst' => 'Veel panden komen nooit online. Vertel ons wat je zoekt.' ) ),
		) ),
	) );

	register_block_pattern( 'gw/pagina-werkwijze', array(
		'title'       => 'Pagina — werkwijze en referentie',
		'description' => 'Hero, tekst, cijfers, stappenplan, citaat en contactformulier.',
		'categories'  => array( 'gw-landing' ),
		'postTypes'   => array( 'page' ),
		'content'     => implode( "\n\n", array(
			$blok( 'hero', array( 'regel_voor' => 'Zo werken', 'accent' => 'wij', 'regel_na' => 'voor jou', 'tekst' => 'Een korte introductie van de werkwijze.', 'knop_label' => 'Neem contact op', 'knop_url' => '#contact' ) ),
			$blok( 'tekst-beeld', array( 'tag' => 'Over ons', 'kop' => 'Lokale kennis, persoonlijke aanpak', 'tekst' => "Eerste alinea.\n\nTweede alinea.", 'foto' => 'nee' ) ),
			$blok( 'cijfers' ),
			$blok( 'stappen' ),
			$blok( 'citaat' ),
			$blok( 'formulier' ),
		) ),
	) );
} );
