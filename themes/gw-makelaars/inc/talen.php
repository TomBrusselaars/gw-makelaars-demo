<?php
/**
 * Talen: de taalkeuze in header en menu werkt met de gratis plug-in Polylang.
 *
 * Dit bestand doet twee dingen als Polylang actief is:
 *  1. Eenmalig Nederlands (standaard) en Engels aanmaken en de bestaande inhoud aan Nederlands koppelen,
 *     zodat je direct kunt beginnen met vertalen (Talen → Polylang in het beheermenu).
 *  2. Diensten, vragen en team vertaalbaar maken (pagina's en berichten zijn dat al).
 * De taalkeuze zelf staat in gw_taalkeuze() (inc/atomen.php): met minimaal twee talen een uitklapmenu.
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'pll_get_post_types', function ( $types ) {
	foreach ( array( 'gw_dienst', 'gw_faq', 'gw_team' ) as $type ) {
		$types[ $type ] = $type;
	}
	return $types;
} );

add_action( 'init', function () {
	if ( get_option( 'gw_talen_geseed' ) || ! function_exists( 'PLL' ) || ! function_exists( 'pll_languages_list' ) ) {
		return;
	}
	$pll = PLL();
	if ( empty( $pll->model ) || empty( $pll->options ) || ! class_exists( 'PLL_Admin_Model' ) ) {
		return;
	}
	update_option( 'gw_talen_geseed', 1 );
	if ( $pll->model->get_languages_list() ) {
		return;
	}
	$model = new PLL_Admin_Model( $pll->options );
	$talen = array(
		array( 'Nederlands', 'nl', 'nl_NL', 'nl' ),
		array( 'English', 'en', 'en_GB', 'gb' ),
	);
	foreach ( $talen as $i => $t ) {
		$model->add_language( array(
			'name'       => $t[0],
			'slug'       => $t[1],
			'locale'     => $t[2],
			'rtl'        => 0,
			'term_group' => $i,
			'flag'       => $t[3],
		) );
	}
	$pll->options['default_lang'] = 'nl';
	$model->clean_languages_cache();
}, 40 );


/**
 * Eenmalig: een Engelse Home als kopie van de Nederlandse, zodat de taalkeuze meteen naar een
 * werkende pagina gaat. De teksten in de blokken staan er nog in het Nederlands: vertaal ze in de editor.
 */
add_action( 'init', function () {
	if ( get_option( 'gw_talen_home_geseed' ) || ! function_exists( 'pll_languages_list' ) || ! function_exists( 'pll_set_post_language' ) ) {
		return;
	}
	if ( ! in_array( 'en', (array) pll_languages_list(), true ) ) {
		return;
	}
	update_option( 'gw_talen_home_geseed', 1 );
	$nl = (int) get_option( 'page_on_front' );
	if ( ! $nl || pll_get_post( $nl, 'en' ) ) {
		return;
	}
	$bron = get_post( $nl );
	$en   = $bron ? wp_insert_post( array(
		'post_type'    => 'page',
		'post_status'  => 'publish',
		'post_title'   => 'Home',
		'post_name'    => 'home-en',
		'post_content' => $bron->post_content,
	) ) : 0;
	if ( $en && ! is_wp_error( $en ) ) {
		pll_set_post_language( $nl, 'nl' );
		pll_set_post_language( $en, 'en' );
		pll_save_post_translations( array( 'nl' => $nl, 'en' => $en ) );
		PLL()->model->clean_languages_cache();
	}
}, 50 );


/**
 * Inhoud zonder taal (bijvoorbeeld de voorbeeldberichten, diensten en vragen die het thema aanmaakt voordat
 * er talen zijn) hoort bij de standaardtaal. Anders verbergt Polylang die op de website.
 * Draait alleen in het beheer en kost één zoekopdracht.
 */
add_action( 'init', function () {
	if ( ! is_admin() || ! function_exists( 'pll_default_language' ) || ! function_exists( 'pll_set_post_language' ) || ! pll_default_language() ) {
		return;
	}
	$ids = get_posts( array(
		'post_type'        => array( 'page', 'post', 'gw_dienst', 'gw_faq', 'gw_team' ),
		'post_status'      => 'any',
		'numberposts'      => 300,
		'fields'           => 'ids',
		'suppress_filters' => true,
	) );
	foreach ( $ids as $id ) {
		if ( ! pll_get_post_language( $id ) ) {
			pll_set_post_language( $id, pll_default_language() );
		}
	}
}, 60 );
