<?php
/**
 * Blokken: de componenten uit componenten.php als bouwstenen in de WordPress-editor.
 *
 * De klant bewerkt een pagina in de gewone editor: blokken slepen, verplaatsen,
 * verwijderen, kopiëren; teksten en foto's wijzig je in het zijpaneel. De
 * weergave op de site komt uit template-parts/blocks/<naam>.php.
 * Geen plug-in nodig en geen build-stap.
 */

defined( 'ABSPATH' ) || exit;

function gw_attributen( array $velden ) {
	$uit = array();
	foreach ( $velden as $k => $d ) {
		if ( 'info' === $d['type'] ) {
			continue;
		}
		switch ( $d['type'] ) {
			case 'number':
			case 'image':
				$uit[ $k ] = array( 'type' => 'integer', 'default' => (int) $d['standaard'] );
				break;
			case 'lijst':
				$uit[ $k ] = array( 'type' => 'array', 'default' => $d['standaard'] );
				break;
			default:
				$uit[ $k ] = array( 'type' => 'string', 'default' => (string) $d['standaard'] );
		}
	}
	return $uit;
}

function gw_render_blok( $slug, array $attrs ) {
	if ( ! isset( gw_componenten()[ $slug ] ) ) {
		return '';
	}
	$a       = gw_attrs( $slug, $attrs );
	$bestand = get_theme_file_path( 'template-parts/blocks/' . $slug . '.php' );
	if ( ! is_readable( $bestand ) ) {
		return '';
	}
	ob_start();
	include $bestand;
	return (string) ob_get_clean();
}

add_action( 'init', function () {
	foreach ( gw_componenten() as $slug => $c ) {
		register_block_type( 'gw/' . $slug, array(
			'api_version'     => 3,
			'title'           => $c['titel'],
			'description'     => $c['omschr'],
			'category'        => 'gw',
			'icon'            => $c['icon'],
			'attributes'      => gw_attributen( $c['velden'] ),
			'supports'        => array( 'html' => false, 'customClassName' => false ),
			'render_callback' => static function ( $attrs ) use ( $slug ) {
				return gw_render_blok( $slug, (array) $attrs );
			},
		) );
	}
} );

add_filter( 'block_categories_all', function ( $cats ) {
	array_unshift( $cats, array( 'slug' => 'gw', 'title' => 'GW makelaars' ) );
	return $cats;
} );

/** Editor: formulier per blok (assets/js/editor.js) en dezelfde stijl als de site. */
add_action( 'enqueue_block_editor_assets', function () {
	$dir = get_template_directory();
	$uri = get_template_directory_uri();
	wp_enqueue_script(
		'gw-editor',
		$uri . '/assets/js/editor.js',
		array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-block-editor', 'wp-server-side-render', 'wp-data' ),
		filemtime( $dir . '/assets/js/editor.js' ),
		true
	);
	$schema = array();
	foreach ( gw_componenten() as $slug => $c ) {
		$schema[ $slug ] = array(
			'titel'  => $c['titel'],
			'icon'   => $c['icon'],
			'omschr' => $c['omschr'],
			'velden' => $c['velden'],
		);
	}
	wp_add_inline_script( 'gw-editor', 'window.GW_COMPONENTEN = ' . wp_json_encode( $schema, JSON_HEX_TAG | JSON_HEX_AMP ) . ';', 'before' );
} );

/**
 * Editor-weergave: dezelfde stylesheet als de site, maar altijd met de desktop-layout.
 * Het werkvlak van de editor is smaller dan de site (het zijpaneel neemt ruimte in);
 * zonder dit springen de tablet- en mobielregels aan en lijkt de pagina anders.
 */
add_action( 'enqueue_block_assets', function () {
	if ( ! is_admin() ) {
		return;
	}
	$dir  = get_template_directory();
	$uri  = get_template_directory_uri();
	$css  = (string) file_get_contents( $dir . '/assets/css/home.css' );
	$css  = preg_replace( '/@media\s*\(max-width:\s*\d+px\)/', '@media (max-width: 0px)', $css );
	$css  = str_replace( 'url(../home/', 'url(' . $uri . '/assets/home/', $css );
	wp_register_style( 'gw-home-editor', false, array(), filemtime( $dir . '/assets/css/home.css' ) );
	wp_enqueue_style( 'gw-home-editor' );
	wp_add_inline_style( 'gw-home-editor', $css . gw_tokens_css() );
	wp_enqueue_style( 'gw-editor', $uri . '/assets/css/editor.css', array( 'gw-home-editor' ), filemtime( $dir . '/assets/css/editor.css' ) );
} );

// ---------------------------------------------------------------------------
// Startinhoud: bij een nieuwe site wordt "Home" aangemaakt met alle blokken.
// Een bestaande startpagina blijft ongemoeid.
// ---------------------------------------------------------------------------

function gw_homepage_blokken() {
	return implode( "\n\n", array_map( static function ( $slug ) {
		return '<!-- wp:gw/' . $slug . ' /-->';
	}, array( 'hero', 'aanbod', 'over', 'diensten', 'nieuws', 'faq', 'cta' ) ) );
}

add_action( 'init', function () {
	if ( get_option( 'gw_home_geseed' ) ) {
		return;
	}
	update_option( 'gw_home_geseed', 1 );
	if ( 'page' === get_option( 'show_on_front' ) && get_option( 'page_on_front' ) ) {
		return;
	}
	$admin = get_users( array( 'role' => 'administrator', 'number' => 1, 'fields' => 'ID' ) );
	$id    = wp_insert_post( array(
		'post_type'    => 'page',
		'post_status'  => 'publish',
		'post_title'   => 'Home',
		'post_name'    => 'home',
		'post_author'  => $admin ? (int) $admin[0] : 0,
		'post_content' => gw_homepage_blokken(),
	) );
	if ( $id && ! is_wp_error( $id ) ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $id );
	}
}, 20 );

/** Eenmalig: een pagina "Nieuws" als overzicht van alle berichten (doel van "Bekijk alle"). */
add_action( 'init', function () {
	if ( get_option( 'gw_nieuws_geseed' ) ) {
		return;
	}
	update_option( 'gw_nieuws_geseed', 1 );
	if ( get_option( 'page_for_posts' ) ) {
		return;
	}
	$id = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'Nieuws', 'post_name' => 'nieuws' ) );
	if ( $id && ! is_wp_error( $id ) ) {
		update_option( 'page_for_posts', $id );
	}
}, 25 );

/** Pagina's met blokken: geen automatische <p>/<br> eromheen. */
function gw_blokken_tonen() {
	if ( has_blocks() ) {
		remove_filter( 'the_content', 'wpautop' );
	}
	the_content();
}

// ---------------------------------------------------------------------------
// Opgeruimde blokkiezer: de klant kiest alleen uit ontworpen onderdelen.
// ---------------------------------------------------------------------------

/**
 * Pagina's: alleen onze blokken (geen vrije alinea's, kolommen, knoppen, embeds, enz.).
 * Berichten (nieuws): een kleine set tekstblokken voor het artikel zelf.
 * Bestaande inhoud met andere blokken blijft gewoon werken en te bewerken.
 */
add_filter( 'allowed_block_types_all', function ( $toegestaan, $context ) {
	$type = isset( $context->post->post_type ) ? $context->post->post_type : '';
	$eigen = array_map( static function ( $slug ) {
		return 'gw/' . $slug;
	}, array_keys( gw_componenten() ) );
	if ( 'page' === $type ) {
		return $eigen;
	}
	if ( 'post' === $type ) {
		return array( 'core/paragraph', 'core/heading', 'core/list', 'core/list-item', 'core/image', 'core/quote', 'core/separator' );
	}
	return $toegestaan;
}, 10, 2 );

// Geen standaardpatronen van WordPress, geen patronen van wordpress.org, geen Openverse-fotobank,
// geen code-editor: alleen wat voor deze site is gemaakt.
add_action( 'after_setup_theme', function () {
	remove_theme_support( 'core-block-patterns' );
} );
add_filter( 'should_load_remote_block_patterns', '__return_false' );
add_filter( 'block_editor_settings_all', function ( $settings ) {
	$settings['enableOpenverseMediaCategory'] = false;
	$settings['codeEditingEnabled']           = false;
	return $settings;
} );
