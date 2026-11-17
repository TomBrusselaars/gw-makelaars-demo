<?php
/**
 * sync.php — Realworks -> inhoudstype "Aanbod".
 *
 * Draait elk uur via WP-Cron, handmatig via Aanbod → Koppeling, en één keer
 * automatisch direct na installatie. Bezoekers raken Realworks dus nooit:
 * pagina's komen uit de eigen database (snel, en geen rate-limit-risico).
 *
 * Foto's worden NIET geïmporteerd maar rechtstreeks van images.realworks.nl
 * geladen; er is dus geen limiet op het aantal foto's per object.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'yc_rw_sync_event', 'yc_rw_sync' );

// Eerste keer (nog nooit gesynchroniseerd): meteen vullen.
add_action( 'init', function () {
	if ( false === get_option( 'yc_rw_last_sync' ) && ! get_transient( 'yc_rw_sync_lock' ) ) {
		yc_rw_sync();
	}
	if ( ! wp_next_scheduled( 'yc_rw_sync_event' ) ) {
		wp_schedule_event( time() + HOUR_IN_SECONDS, 'hourly', 'yc_rw_sync_event' );
	}
}, 50 );

/**
 * @return array Samenvatting: mode, tijd, nieuw, bijgewerkt, ongewijzigd, verwijderd, fout.
 */
function yc_rw_sync() {
	set_transient( 'yc_rw_sync_lock', 1, 5 * MINUTE_IN_SECONDS );

	$rapport = array(
		'mode' => yc_rw_mode(), 'tijd' => time(),
		'nieuw' => 0, 'bijgewerkt' => 0, 'ongewijzigd' => 0, 'verwijderd' => 0, 'overgeslagen' => 0, 'fout' => null,
	);

	$raw = yc_rw_get_objecten();
	if ( is_wp_error( $raw ) ) {
		// Bij een fout NIETS weggooien: het bestaande aanbod blijft staan.
		$rapport['fout'] = $raw->get_error_message();
		update_option( 'yc_rw_last_sync', $rapport, false );
		delete_transient( 'yc_rw_sync_lock' );
		return $rapport;
	}

	$lijst = yc_rw_normalize_lijst( $raw );

	// Bestaande berichten: ref -> post-ID
	$bestaand = array();
	$ids      = get_posts( array(
		'post_type' => YC_RW_CPT, 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids',
	) );
	foreach ( $ids as $id ) {
		$bestaand[ (string) get_post_meta( $id, '_rw_ref', true ) ] = $id;
	}

	$gezien = array();
	foreach ( $lijst['objecten'] as $o ) {
		// Alleen wat openbaar mag zijn krijgt een pagina: beschikbaar aanbod en
		// referenties. Vertrouwelijk, prospect, in aanmelding en ingetrokken niet
		// (een bestaand bericht gaat dan naar de prullenbak).
		if ( '' === $o['objectcode'] || $o['vertrouwelijk'] || ! ( $o['beschikbaar'] || $o['referentie'] ) ) {
			$rapport['overgeslagen']++;
			continue;
		}
		$ref      = $o['ref'];
		$gezien[] = $ref;
		$json     = wp_json_encode( $o );
		$hash     = md5( $json );
		$post_id  = $bestaand[ $ref ] ?? 0;

		if ( $post_id && get_post_meta( $post_id, '_rw_hash', true ) === $hash && 'publish' === get_post_status( $post_id ) ) {
			$rapport['ongewijzigd']++;
			continue;
		}

		$postarr = array(
			'ID'           => $post_id,
			'post_type'    => YC_RW_CPT,
			'post_status'  => 'publish',
			'post_title'   => $o['adres']['straatNr'] ?: $o['objectcode'],
			'post_name'    => $o['plaatsSlug'] . '-' . $o['straatSlug'],
			'post_content' => $o['teksten']['aanbieding'],
		);
		if ( $o['publicatiedatum'] && strtotime( $o['publicatiedatum'] ) ) {
			$postarr['post_date'] = gmdate( 'Y-m-d H:i:s', strtotime( $o['publicatiedatum'] ) );
		}
		$post_id = wp_insert_post( wp_slash( $postarr ), true );
		if ( is_wp_error( $post_id ) ) {
			$rapport['fout'] = $ref . ': ' . $post_id->get_error_message();
			continue;
		}

		update_post_meta( $post_id, '_rw_ref', $ref );
		update_post_meta( $post_id, '_rw_hash', $hash );
		update_post_meta( $post_id, '_rw_data', wp_slash( $json ) );
		update_post_meta( $post_id, '_rw_plaats_slug', $o['plaatsSlug'] );
		update_post_meta( $post_id, '_rw_status', $o['status'] );
		update_post_meta( $post_id, '_rw_beschikbaar', $o['beschikbaar'] ? '1' : '0' );

		// Filtervelden (meerdere waarden per object mogelijk)
		delete_post_meta( $post_id, '_rw_aanbod' );
		foreach ( 'beide' === $o['prijs']['type'] ? array( 'koop', 'huur' ) : array( $o['prijs']['type'] ) as $t ) {
			add_post_meta( $post_id, '_rw_aanbod', $t );
		}
		delete_post_meta( $post_id, '_rw_functie' );
		$typen = array_unique( array_filter( array_merge( array( $o['hoofdfunctie'] ), wp_list_pluck( $o['functies'], 'type' ) ) ) );
		foreach ( $typen as $t ) {
			add_post_meta( $post_id, '_rw_functie', $t );
		}

		$rapport[ isset( $bestaand[ $ref ] ) ? 'bijgewerkt' : 'nieuw' ]++;
	}

	// Niet meer in Realworks -> naar de prullenbak (herstelbaar)
	foreach ( $bestaand as $ref => $id ) {
		if ( ! in_array( $ref, $gezien, true ) && 'trash' !== get_post_status( $id ) ) {
			wp_trash_post( $id );
			$rapport['verwijderd']++;
		}
	}

	update_option( 'yc_rw_last_sync', $rapport, false );
	delete_transient( 'yc_rw_sync_lock' );
	return $rapport;
}
