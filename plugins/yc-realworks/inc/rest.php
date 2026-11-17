<?php
/**
 * rest.php — twee kleine endpoints voor de objectpagina.
 *
 *   POST /wp-json/yc-realworks/v1/aanvraag   formulier -> Realworks (relatie + response)
 *   GET  /wp-json/yc-realworks/v1/geocode    adres -> coördinaten (PDOK), bewaard bij het object
 */

defined( 'ABSPATH' ) || exit;

add_action( 'rest_api_init', function () {
	register_rest_route( 'yc-realworks/v1', '/aanvraag', array(
		'methods'             => 'POST',
		'permission_callback' => '__return_true',
		'callback'            => 'yc_rw_rest_aanvraag',
	) );
	register_rest_route( 'yc-realworks/v1', '/geocode', array(
		'methods'             => 'GET',
		'permission_callback' => '__return_true',
		'callback'            => 'yc_rw_rest_geocode',
	) );
} );

/** Maximaal 5 aanvragen per IP-adres per 10 minuten (voorkomt spam en misbruik van de Realworks-koppeling). */
function yc_rw_rate_limit_overschreden() {
	$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	$key = 'yc_rw_rl_' . md5( $ip );
	$n   = (int) get_transient( $key );
	if ( $n >= 5 ) {
		return true;
	}
	set_transient( $key, $n + 1, 10 * MINUTE_IN_SECONDS );
	return false;
}

function yc_rw_rest_aanvraag( WP_REST_Request $req ) {
	if ( yc_rw_rate_limit_overschreden() ) {
		return new WP_Error( 'rw_te_vaak', 'Je hebt te vaak een aanvraag gedaan. Probeer het over een paar minuten opnieuw.', array( 'status' => 429 ) );
	}
	$o = yc_rw_object( (int) $req->get_param( 'post' ) );
	if ( ! $o ) {
		return new WP_Error( 'rw_onbekend', 'Object niet gevonden.', array( 'status' => 404 ) );
	}
	// Honeypot: echte bezoekers laten dit veld leeg.
	if ( '' !== (string) $req->get_param( 'website' ) ) {
		return array( 'ok' => true, 'result' => array( 'mode' => yc_rw_mode() ) );
	}
	$d = array(
		'afdelingscode' => $o['afdelingscode'],
		'objectcode'    => $o['objectcode'],
		'naam'          => sanitize_text_field( (string) $req->get_param( 'naam' ) ),
		'email'         => sanitize_email( (string) $req->get_param( 'email' ) ),
		'telefoon'      => sanitize_text_field( (string) $req->get_param( 'telefoon' ) ),
		'bedrijf'       => sanitize_text_field( (string) $req->get_param( 'bedrijf' ) ),
		'bericht'       => sanitize_textarea_field( (string) $req->get_param( 'bericht' ) ),
	);
	if ( '' === $d['naam'] || ! is_email( $d['email'] ) ) {
		return new WP_Error( 'rw_ongeldig', 'Naam en een geldig e-mailadres zijn verplicht.', array( 'status' => 400 ) );
	}
	$result = yc_rw_stuur_aanvraag( $d );
	if ( is_wp_error( $result ) ) {
		error_log( 'yc-realworks aanvraag mislukt: ' . $result->get_error_message() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions
		return new WP_Error( 'rw_fout', 'Je aanvraag kon niet worden verwerkt. Bel ons gerust: we helpen je direct.', array( 'status' => 502 ) );
	}
	// Alleen de uitkomst, nooit de data die naar Realworks ging.
	return array( 'ok' => true, 'result' => array( 'mode' => $result['mode'] ?? yc_rw_mode() ) );
}

/**
 * Realworks levert geen coördinaten. We zoeken het adres één keer op bij de
 * gratis PDOK Locatieserver en bewaren het resultaat bij het object.
 */
function yc_rw_rest_geocode( WP_REST_Request $req ) {
	$post_id = (int) $req->get_param( 'post' );
	$o       = yc_rw_object( $post_id );
	if ( ! $o ) {
		return array( 'coords' => null );
	}
	$q      = $o['adres']['geocodeQuery'];
	$cached = get_post_meta( $post_id, '_rw_coords', true );
	if ( is_array( $cached ) && ( $cached['q'] ?? '' ) === $q ) {
		return array( 'coords' => $cached['coords'] );
	}

	$res = wp_remote_get(
		'https://api.pdok.nl/bzk/locatieserver/search/v3_1/free?' . http_build_query( array( 'q' => $q, 'fq' => 'type:adres', 'rows' => 1 ) ),
		array( 'timeout' => 8, 'headers' => array( 'Accept' => 'application/json' ) )
	);
	if ( is_wp_error( $res ) || 200 !== (int) wp_remote_retrieve_response_code( $res ) ) {
		return array( 'coords' => null ); // niet bewaren: volgende keer opnieuw proberen
	}
	$data   = json_decode( (string) wp_remote_retrieve_body( $res ), true );
	$doc    = $data['response']['docs'][0] ?? null;
	$coords = null;
	// centroide_ll = "POINT(lon lat)"
	if ( $doc && preg_match( '/POINT\(([-\d.]+)\s+([-\d.]+)\)/', (string) ( $doc['centroide_ll'] ?? '' ), $m ) ) {
		$coords = array( 'lon' => (float) $m[1], 'lat' => (float) $m[2] );
	}
	update_post_meta( $post_id, '_rw_coords', array( 'q' => $q, 'coords' => $coords ) );
	return array( 'coords' => $coords );
}
