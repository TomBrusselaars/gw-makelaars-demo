<?php
/**
 * client.php — de enige plek die met Realworks BOG v3 praat.
 *
 * In de Webflow-variant was hiervoor een apart "doorgeefluik" nodig. Hier is
 * WordPress zelf de server: het token staat in wp-config.php en verlaat de
 * server nooit; uitgaande calls komen van het (vaste) IP van de hosting.
 *
 *   LIVE : RW_TOKEN gezet  -> echte calls naar api.realworks.nl
 *   MOCK : anders          -> voorbeelddata (inc/mock-data.php)
 *
 *   Header : Authorization: rwauth <token>
 *   GET  /bog/v3/objecten
 *   POST /bog/v3/relaties
 *   POST /bog/v3/response
 */

defined( 'ABSPATH' ) || exit;

function yc_rw_token() {
	return defined( 'RW_TOKEN' ) ? (string) RW_TOKEN : (string) getenv( 'RW_TOKEN' );
}

function yc_rw_afdelingscode() {
	return defined( 'RW_AFDELINGSCODE' ) ? (string) RW_AFDELINGSCODE : (string) getenv( 'RW_AFDELINGSCODE' );
}

function yc_rw_api_base() {
	return defined( 'RW_API_BASE' ) ? untrailingslashit( (string) RW_API_BASE ) : 'https://api.realworks.nl';
}

function yc_rw_live() {
	return '' !== yc_rw_token();
}

function yc_rw_mode() {
	return yc_rw_live() ? 'live' : 'mock';
}

/** @return array|WP_Error */
function yc_rw_fetch( $path_or_url, array $args = array() ) {
	$url = 0 === strpos( $path_or_url, 'http' ) ? $path_or_url : yc_rw_api_base() . $path_or_url;
	$res = wp_remote_request( $url, array_merge( array(
		'method'  => 'GET',
		'timeout' => 20,
		'headers' => array(
			'Authorization' => 'rwauth ' . yc_rw_token(),
			'Accept'        => 'application/json;charset=UTF-8',
			'Content-Type'  => 'application/json',
		),
	), $args ) );

	if ( is_wp_error( $res ) ) {
		return $res;
	}
	$code = (int) wp_remote_retrieve_response_code( $res );
	$body = (string) wp_remote_retrieve_body( $res );
	if ( 429 === $code ) {
		return new WP_Error( 'rw_429', 'Realworks rate limit (429) — te veel verzoeken.' );
	}
	if ( $code < 200 || $code >= 300 ) {
		return new WP_Error( 'rw_' . $code, 'Realworks ' . $code . ': ' . substr( $body, 0, 300 ) );
	}
	$data = json_decode( $body, true );
	return is_array( $data ) ? $data : new WP_Error( 'rw_json', 'Onleesbaar antwoord van Realworks.' );
}

/**
 * Alle objecten ophalen (ruw schema), inclusief vervolgpagina's.
 *
 * @return array|WP_Error  [ 'resultaten' => [...] ]
 */
function yc_rw_get_objecten( array $filters = array() ) {
	$f = array_merge( array( 'actief' => 'true', 'aantal' => '100' ), $filters );
	if ( yc_rw_afdelingscode() && empty( $f['afdelingscode'] ) ) {
		$f['afdelingscode'] = yc_rw_afdelingscode();
	}

	if ( ! yc_rw_live() ) {
		return yc_rw_mock_lijst( $f );
	}

	// Paginering (geverifieerd tegen de spec + FAQ van het Developer Portal):
	//  - max. 100 resultaten per aanroep (parameter `aantal`);
	//  - `paginering.volgende` is een URI naar de volgende pagina (kan ontbreken);
	//  - anders: `vanaf=<id van het laatste object>` geeft de volgende reeks.
	$alle   = array();
	$aantal = (int) $f['aantal'];
	$query  = array_filter( $f, 'strlen' );
	$next   = '/bog/v3/objecten?' . http_build_query( $query );
	$gezien = array();
	for ( $i = 0; $i < 50 && $next; $i++ ) {
		$data = yc_rw_fetch( $next );
		if ( is_wp_error( $data ) ) {
			return $data;
		}
		$pagina = (array) ( $data['resultaten'] ?? array() );
		$nieuw  = 0;
		foreach ( $pagina as $obj ) {
			$id = $obj['id'] ?? null;
			if ( null !== $id && isset( $gezien[ $id ] ) ) {
				continue; // dubbel object over paginagrenzen heen
			}
			if ( null !== $id ) {
				$gezien[ $id ] = true;
			}
			$alle[] = $obj;
			$nieuw++;
		}
		$volg   = $data['paginering']['volgende'] ?? null;
		$totaal = (int) ( $data['paginering']['totaalAantal'] ?? 0 );
		$laatste = $pagina ? end( $pagina ) : null;
		// het token gaat alleen naar Realworks zelf, nooit naar een andere host
		if ( is_string( $volg ) && ( 0 === strpos( $volg, yc_rw_api_base() . '/' ) || 0 === strpos( $volg, '/' ) ) ) {
			$next = $volg;
		} elseif ( $nieuw > 0 && count( $pagina ) >= $aantal && count( $alle ) < $totaal && isset( $laatste['id'] ) ) {
			$next = '/bog/v3/objecten?' . http_build_query( array_merge( $query, array( 'vanaf' => $laatste['id'] ) ) );
		} else {
			$next = null;
		}
	}
	return array( 'resultaten' => $alle, 'paginering' => array( 'totaalAantal' => count( $alle ) ) );
}

/**
 * Bouw de berichten voor een aanvraag, exact volgens de BOG v3-spec
 * (schema's RelatieCreatePayload / ParticulierPayload / BedrijfPayload /
 * ContactPersonPayload en BogResponseV3; nagelopen op 2026-11-16).
 *
 *  - zonder bedrijfsnaam: één relatie van het type PARTICULIER;
 *  - met bedrijfsnaam:    eerst BEDRIJF, daarna CONTACTPERSOON gekoppeld via companyId.
 *
 * Realworks zoekt zelf eerst een bestaande relatie (op e-mail, telefoon of
 * bedrijfsnaam) voordat er een nieuwe wordt aangemaakt.
 */
function yc_rw_aanvraag_berichten( array $d ) {
	// Het formulier heeft één naamveld; Realworks wil minimaal een achternaam.
	$delen      = preg_split( '/\s+/', trim( $d['naam'] ), 2 );
	$roepnaam   = isset( $delen[1] ) ? $delen[0] : null;
	$achternaam = $delen[1] ?? $delen[0];

	$basis   = array( 'afdelingscode' => $d['afdelingscode'], 'email' => $d['email'] );
	$persoon = array_filter( array(
		'achternaam'     => $achternaam,
		'roepnaam'       => $roepnaam,
		'telefoonnummer' => $d['telefoon'],
	) );

	$relaties = array();
	if ( '' !== $d['bedrijf'] ) {
		$relaties[] = array_merge( $basis, array( 'relatiesoort' => 'BEDRIJF', 'bedrijfsnaam' => $d['bedrijf'] ) );
		$relaties[] = array_merge( $basis, $persoon, array( 'relatiesoort' => 'CONTACTPERSOON', 'companyId' => '(entityId van het bedrijf)' ) );
	} else {
		$relaties[] = array_merge( $basis, $persoon, array( 'relatiesoort' => 'PARTICULIER' ) );
	}

	$response = array(
		'basis'    => array_filter( array(
			'afdelingscode' => $d['afdelingscode'],
			'objectcode'    => $d['objectcode'],
			// optioneel: vaste ontvanger -> define( 'RW_MEDEWERKER_ID', 12345 );
			'medewerkerId'  => defined( 'RW_MEDEWERKER_ID' ) ? (int) RW_MEDEWERKER_ID : null,
			'referral'      => 'Website',
		) ),
		'response' => array(
			'relatieId'   => '(entityId van de relatie)',
			'responstype' => $d['responstype'] ?? 'CONTACTAANVRAAG',
			'opmerking'   => $d['bericht'],
		),
	);
	return array( 'relaties' => $relaties, 'response' => $response );
}

/** POST naar Realworks; geeft het entityId uit het antwoord (SyncWritePathReply) of een fout. */
function yc_rw_schrijf( $pad, array $body ) {
	$r = yc_rw_fetch( $pad, array( 'method' => 'POST', 'body' => wp_json_encode( $body ) ) );
	if ( is_wp_error( $r ) ) {
		return $r;
	}
	if ( 'OK' !== ( $r['result'] ?? '' ) ) {
		$fout = implode( '; ', (array) ( $r['foutmeldingen'] ?? array() ) ) ?: ( 'resultaat ' . ( $r['result'] ?? 'onbekend' ) );
		return new WP_Error( 'rw_schrijf', 'Realworks weigerde het bericht: ' . $fout );
	}
	return $r['entityId'] ?? 0;
}

/**
 * Aanvraag terugsturen: relatie(s) aanmaken -> response koppelen aan het object.
 *
 * @return array|WP_Error
 */
function yc_rw_stuur_aanvraag( array $d ) {
	$b = yc_rw_aanvraag_berichten( $d );

	if ( ! yc_rw_live() ) {
		// In mock-modus NIET echt versturen — alleen laten zien wat er zou gebeuren.
		return array(
			'mode'        => 'mock',
			'verzonden'   => false,
			'toelichting' => 'Voorbeeldmodus: deze berichten zouden naar Realworks gaan.',
			'stap1'       => array( 'endpoint' => 'POST /bog/v3/relaties', 'berichten' => $b['relaties'] ),
			'stap2'       => array( 'endpoint' => 'POST /bog/v3/response', 'bericht' => $b['response'] ),
		);
	}

	$relatie_id = 0;
	foreach ( $b['relaties'] as $relatie ) {
		if ( 'CONTACTPERSOON' === $relatie['relatiesoort'] ) {
			$relatie['companyId'] = (string) $relatie_id; // id van het zojuist aangemaakte bedrijf
		}
		$relatie_id = yc_rw_schrijf( '/bog/v3/relaties', $relatie );
		if ( is_wp_error( $relatie_id ) ) {
			return $relatie_id;
		}
	}

	$b['response']['response']['relatieId'] = (int) $relatie_id;
	$ok = yc_rw_schrijf( '/bog/v3/response', $b['response'] );
	if ( is_wp_error( $ok ) ) {
		return $ok;
	}
	return array( 'mode' => 'live', 'verzonden' => true, 'relatieId' => (int) $relatie_id );
}
