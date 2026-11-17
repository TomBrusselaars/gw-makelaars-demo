<?php
/**
 * normalize.php — Realworks BOG v3 -> schone, voorspelbare vorm voor het thema.
 *
 * Eén-op-één port van lib/normalize.js. DIT is de enige plek die je aanpast als
 * Realworks ooit een veldnaam wijzigt. Het thema leest nooit het ruwe schema.
 *
 * Gebaseerd op de ECHTE response van GET /bog/v3/objecten (development-token,
 * voorbeeldobject B100000, geverifieerd op 2026-11-13).
 */

defined( 'ABSPATH' ) || exit;

// ---------------------------------------------------------------------------
// Kleine helpers
// ---------------------------------------------------------------------------

function yc_rw_prettify( $enum ) {
	if ( ! $enum ) {
		return '';
	}
	return ucfirst( str_replace( '_', ' ', strtolower( (string) $enum ) ) );
}

/** Plaatsnamen komen in hoofdletters binnen ("DEN HAAG") -> "Den Haag". */
function yc_rw_plaatsnaam( $plaats ) {
	if ( ! $plaats ) {
		return '';
	}
	$p = str_replace( '_', ' ', (string) $plaats );
	return function_exists( 'mb_convert_case' )
		? mb_convert_case( $p, MB_CASE_TITLE, 'UTF-8' )
		: ucwords( strtolower( $p ) );
}

function yc_rw_getal( $n ) {
	$num = (float) $n;
	return number_format( $num, floor( $num ) == $num ? 0 : 2, ',', '.' );
}

function yc_rw_euro( $n ) {
	if ( null === $n || '' === $n || ! is_numeric( $n ) || 0.0 === (float) $n ) {
		return null;
	}
	return '€ ' . yc_rw_getal( $n );
}

// ---------------------------------------------------------------------------
// Labels
// ---------------------------------------------------------------------------

function yc_rw_status_labels() {
	return array(
		'PROSPECT'                  => 'Prospect',
		'BESCHIKBAAR'               => 'Beschikbaar',
		'ONDER_OPTIE'               => 'Onder optie',
		'ONDER_BOD'                 => 'Onder bod',
		'VERHUURD_ONDER_VOORBEHOUD' => 'Verhuurd o.v.',
		'VERHUURD'                  => 'Verhuurd',
		'VERKOCHT_ONDER_VOORBEHOUD' => 'Verkocht o.v.',
		'VERKOCHT'                  => 'Verkocht',
		'VERKOCHT_BIJ_INSCHRIJVING' => 'Verkocht bij inschrijving',
		'GEVEILD'                   => 'Geveild',
		'IN_AANMELDING'             => 'In aanmelding',
		'INGETROKKEN'               => 'Ingetrokken',
	);
}

/**
 * Welke statussen tellen als "nog op de markt".
 *
 * PROSPECT en IN_AANMELDING zijn nog niet openbaar en komen dus NIET op de site,
 * tenzij RW_TOON_PROSPECT aan staat (alleen bedoeld voor het development-token,
 * waarvan het enige voorbeeldobject de status PROSPECT heeft).
 */
function yc_rw_beschikbaar_statussen() {
	$s = array( 'BESCHIKBAAR', 'ONDER_OPTIE', 'ONDER_BOD', 'VERHUURD_ONDER_VOORBEHOUD', 'VERKOCHT_ONDER_VOORBEHOUD' );
	if ( defined( 'RW_TOON_PROSPECT' ) && RW_TOON_PROSPECT ) {
		$s[] = 'PROSPECT';
	}
	return $s;
}

/** Verhuurd/verkocht -> referentie. */
function yc_rw_referentie_statussen() {
	return array( 'VERHUURD', 'VERKOCHT', 'VERKOCHT_BIJ_INSCHRIJVING', 'GEVEILD' );
}

function yc_rw_functie_labels() {
	return array(
		'WINKELRUIMTE'             => 'Winkelruimte',
		'KANTOORRUIMTE'            => 'Kantoorruimte',
		'BEDRIJFSRUIMTE'           => 'Bedrijfsruimte',
		'HORECA'                   => 'Horeca',
		'BOUWGROND'                => 'Bouwgrond',
		'OVERIGE'                  => 'Overige',
		'LEISURE'                  => 'Leisure',
		'MAATSCHAPPELIJK_VASTGOED' => 'Maatschappelijk vastgoed',
		'VERHARD_BUITENTERREIN'    => 'Verhard buitenterrein',
		'BELEGGING'                => 'Belegging',
	);
}

function yc_rw_huurconditie_labels() {
	return array(
		'PER_MAAND'                    => 'per maand',
		'PER_JAAR'                     => 'per jaar',
		'PER_VIERKANTE_METERS_PER_JAAR' => 'per m² per jaar', // schrijfwijze volgens de spec
		'PER_VIERKANTE_METER_PER_JAAR'  => 'per m² per jaar',
	);
}

function yc_rw_prijsvoorvoegsel_labels() {
	return array(
		'VRAAGPRIJS'        => 'Vraagprijs',
		'KOOPPRIJS'         => 'Koopprijs',
		'HUURPRIJS'         => 'Huurprijs',
		'PRIJS_OP_AANVRAAG' => 'Prijs op aanvraag',
		'PRIJS_NOTK'        => 'Prijs n.o.t.k.',
	);
}

// ---------------------------------------------------------------------------
// Media
// ---------------------------------------------------------------------------

/**
 * Realworks levert standaard een thumbnail; met resize=4 krijg je het origineel.
 */
function yc_rw_media_varianten( $link ) {
	if ( ! $link ) {
		return array( 'thumb' => null, 'groot' => null );
	}
	$sep = false !== strpos( $link, '?' ) ? '&' : '?';
	return array(
		'thumb' => $link,
		'groot' => $link . $sep . 'resize=4',
	);
}

function yc_rw_verdeel_media( $media ) {
	$out = array( 'hoofdfoto' => null, 'fotos' => array(), 'plattegronden' => array(), 'documenten' => array(), 'video' => null );
	if ( ! is_array( $media ) ) {
		return $out;
	}

	// Alleen vrijgegeven media voor de eigen site tonen
	$zichtbaar = array_values( array_filter( $media, function ( $m ) {
		return is_array( $m ) && false !== ( $m['vrijgave'] ?? null );
	} ) );
	usort( $zichtbaar, function ( $a, $b ) {
		return (int) ( $a['volgnummer'] ?? 0 ) <=> (int) ( $b['volgnummer'] ?? 0 );
	} );

	foreach ( $zichtbaar as $m ) {
		$v     = yc_rw_media_varianten( $m['link'] ?? null );
		$soort = $m['soort'] ?? '';
		$mime  = (string) ( $m['mimetype'] ?? '' );
		$item  = array(
			'soort'        => $soort,
			'titel'        => ( $m['titel'] ?? null ) ?: null,
			'omschrijving' => ( $m['omschrijving'] ?? null ) ?: null,
			'mimetype'     => $mime ?: null,
			'volgnummer'   => (int) ( $m['volgnummer'] ?? 0 ),
			'url'          => $v['thumb'],
			'urlGroot'     => $v['groot'],
		);
		switch ( $soort ) {
			case 'IPIX':
			case 'FLASH':
			case 'SLIDESHOW':
			case 'GELUID':
			case 'CONNECTED_PARTNER':
			case 'QR_CODE':
				break; // niet geschikt voor de website
			case 'HOOFDFOTO':
				$out['hoofdfoto'] = $item;
				array_unshift( $out['fotos'], $item );
				break;
			case 'FOTO':
				$out['fotos'][] = $item;
				break;
			case 'PLATTEGROND':
				$out['plattegronden'][] = $item;
				break;
			case 'VIDEO':
				$out['video'] = $item;
				break;
			case 'DOCUMENT':
				$out['documenten'][] = $item;
				break;
			default:
				// onbekend soort -> bij foto's als het een afbeelding is
				if ( 0 === strpos( $mime, 'image/' ) ) {
					$out['fotos'][] = $item;
				} else {
					$out['documenten'][] = $item;
				}
		}
	}
	if ( ! $out['hoofdfoto'] && $out['fotos'] ) {
		$out['hoofdfoto'] = $out['fotos'][0];
	}
	return $out;
}

// ---------------------------------------------------------------------------
// Prijs / transactie
// ---------------------------------------------------------------------------

function yc_rw_bepaal_prijs( $k, $prijsvermelding = true ) {
	$k     = is_array( $k ) ? $k : array();
	$koop  = (float) ( $k['koopprijs'] ?? 0 );
	$huur  = (float) ( $k['huurprijs'] ?? 0 );
	$reden = strtoupper( (string) ( $k['aanmeldingsreden'] ?? '' ) );

	// aanmeldingsreden: IN_VERKOOP_GENOMEN, IN_VERHUUR_GENOMEN, DIRECT_VERKOCHT,
	// DIRECT_VERHUURD, VEILING, IN_VERKOOP_OF_VERHUUR_GENOMEN, VERKOOP_BIJ_INSCHRIJVING, ...
	$is_huur = $huur > 0 || (bool) preg_match( '/HUUR/', $reden );
	$is_koop = $koop > 0 || (bool) preg_match( '/KOOP|KOCHT|VEILING/', $reden );

	$type = 'onbekend';
	if ( $is_huur && $is_koop ) {
		$type = 'beide';
	} elseif ( $is_huur ) {
		$type = 'huur';
	} elseif ( $is_koop ) {
		$type = 'koop';
	}

	$huurcondities = yc_rw_huurconditie_labels();
	$huurconditie  = $huurcondities[ $k['huurconditie'] ?? '' ] ?? null;
	$koopconditie  = $k['koopconditie'] ?? null;

	// Voorvoegsel van de kant die we tonen (huur gaat voor als beide gevuld zijn)
	$toon_huur   = $is_huur && $huur > 0;
	$voorvoegsel = (string) ( $toon_huur ? ( $k['huurprijsvoorvoegsel'] ?? '' ) : ( $k['koopprijsvoorvoegsel'] ?? '' ) );
	$notk        = 'PRIJS_NOTK' === $voorvoegsel;
	// "Prijs op aanvraag", of de makelaar heeft prijsvermelding uitgezet
	$op_aanvraag = 'PRIJS_OP_AANVRAAG' === $voorvoegsel || ! $prijsvermelding;

	$label = 'Prijs op aanvraag';
	if ( $notk && $prijsvermelding ) {
		$label = 'Prijs n.o.t.k.';
	} elseif ( ! $op_aanvraag ) {
		if ( $toon_huur ) {
			$vanaf = 'VAN' === ( $k['huurprijstype'] ?? '' ) ? 'Vanaf ' : '';
			$label = trim( $vanaf . yc_rw_euro( $huur ) . ' ' . (string) $huurconditie );
		} elseif ( $is_koop && $koop > 0 ) {
			$kk    = 'KOSTEN_KOPER' === $koopconditie ? 'k.k.' : ( 'VRIJ_OP_NAAM' === $koopconditie ? 'v.o.n.' : '' );
			$label = trim( yc_rw_euro( $koop ) . ' ' . $kk );
			if ( 'VRAAGPRIJS' === $voorvoegsel ) {
				$label = 'Vraagprijs: ' . $label;
			}
		}
	}
	$verborgen = $op_aanvraag || $notk;

	$servicekosten = yc_rw_euro( $k['servicekosten'] ?? null );
	if ( $servicekosten && ! empty( $huurcondities[ $k['servicekostenconditie'] ?? '' ] ) ) {
		$servicekosten .= ' ' . $huurcondities[ $k['servicekostenconditie'] ];
	}

	return array(
		'type'          => $type,
		// bedragen niet meegeven als de prijs niet getoond mag worden
		'koopprijs'     => ( $koop && ! $verborgen ) ? $koop : null,
		'huurprijs'     => ( $huur && ! $verborgen ) ? $huur : null,
		'huurconditie'  => $huurconditie,
		'koopconditie'  => $koopconditie ? yc_rw_prettify( $koopconditie ) : null,
		'servicekosten' => $verborgen ? null : $servicekosten,
		'btw'           => true === ( $k['btwVanToepassing'] ?? null ),
		'opAanvraag'    => $verborgen,
		'label'         => $label,
	);
}

// ---------------------------------------------------------------------------
// Functies & oppervlakte
// ---------------------------------------------------------------------------

function yc_rw_bepaal_functies( $object_blok ) {
	$functies = ( is_array( $object_blok ) && is_array( $object_blok['functies'] ?? null ) ) ? $object_blok['functies'] : array();
	$labels   = yc_rw_functie_labels();
	// het detailblok heeft dezelfde sleutel als de functie, camelCase
	$sleutels = array(
		'WINKELRUIMTE'             => 'winkelruimte',
		'KANTOORRUIMTE'            => 'kantoorruimte',
		'BEDRIJFSRUIMTE'           => 'bedrijfsruimte',
		'HORECA'                   => 'horeca',
		'BOUWGROND'                => 'bouwgrond',
		'OVERIGE'                  => 'overige',
		'LEISURE'                  => 'leisure',
		'MAATSCHAPPELIJK_VASTGOED' => 'maatschappelijkvastgoed',
		'VERHARD_BUITENTERREIN'    => 'verhardBuitenTerrein',
		'BELEGGING'                => 'belegging',
	);

	$out = array();
	$max = 0;
	foreach ( $functies as $f ) {
		if ( ! is_array( $f ) || false === ( $f['actief'] ?? null ) ) {
			continue;
		}
		$type = $f['type'] ?? '';
		$blok = isset( $sleutels[ $type ] ) ? ( $f[ $sleutels[ $type ] ] ?? null ) : null;
		// oppervlakte zit per functietype op een andere plek:
		//  - de meeste: blok.oppervlakte
		//  - bedrijfsruimte: blok.bedrijfshal.oppervlakte
		//  - winkelruimte: anders blok.verkoopVloeroppervlakte
		//  - maatschappelijk vastgoed: som van blok.instellingen[].oppervlakte
		//  - bouwgrond / verhard buitenterrein: geen oppervlakteveld in de spec
		$opp = null;
		if ( is_array( $blok ) ) {
			$opp = $blok['oppervlakte'] ?? ( $blok['bedrijfshal']['oppervlakte'] ?? ( $blok['verkoopVloeroppervlakte'] ?? null ) );
			if ( ! $opp && is_array( $blok['instellingen'] ?? null ) ) {
				$opp = array_sum( array_map( function ( $i ) {
					return (float) ( $i['oppervlakte'] ?? 0 );
				}, $blok['instellingen'] ) );
			}
		}
		$opp = (float) $opp ?: null;
		if ( $opp && $opp > $max ) {
			$max = $opp;
		}
		$out[] = array(
			'type'        => $type,
			'label'       => $labels[ $type ] ?? yc_rw_prettify( $type ),
			'oppervlakte' => $opp,
		);
	}
	return array( 'functies' => $out, 'maxOppervlakte' => $max ?: null );
}

// ---------------------------------------------------------------------------
// Adres
// ---------------------------------------------------------------------------

function yc_rw_bepaal_adres( $a ) {
	$a      = is_array( $a ) ? $a : array();
	$hn     = is_array( $a['huisnummer'] ?? null ) ? $a['huisnummer'] : array();
	$nummer = implode( '', array_filter( array( $hn['hoofdnummer'] ?? '', $hn['toevoeging'] ?? '' ) ) );
	$reeks  = ( ! empty( $hn['reeksBegin'] ) && ! empty( $hn['reeksEind'] ) ) ? $hn['reeksBegin'] . '-' . $hn['reeksEind'] : '';

	$huisnummer = $reeks ?: $nummer;
	$straat     = (string) ( $a['straat'] ?? '' );
	$postcode   = (string) ( $a['postcode'] ?? '' );
	$plaats_ruw = (string) ( $a['plaats'] ?? '' );
	$plaats     = yc_rw_plaatsnaam( $plaats_ruw );
	$straat_nr  = implode( ' ', array_filter( array( $straat, $huisnummer ) ) );

	return array(
		'straat'       => $straat,
		'huisnummer'   => $huisnummer,
		'postcode'     => $postcode,
		'plaats'       => $plaats,
		'plaatsRuw'    => $plaats_ruw,
		'provincie'    => yc_rw_prettify( $a['provincie'] ?? '' ),
		'land'         => ( $a['land'] ?? '' ) ?: 'Nederland',
		'straatNr'     => $straat_nr,
		'volledig'     => implode( ', ', array_filter( array( $straat_nr, implode( ' ', array_filter( array( $postcode, $plaats ) ) ) ) ) ),
		// adres voor geocoding (PDOK)
		'geocodeQuery' => implode( ' ', array_filter( array( $straat_nr, $postcode, $plaats_ruw ) ) ),
	);
}

function yc_rw_raap_tekst( $t ) {
	$t = is_array( $t ) ? $t : array();
	return array(
		'aanbieding' => (string) ( ( $t['aanbiedingstekst'] ?? '' ) ?: ( $t['eigenSiteTekst'] ?? '' ) ),
		'eigenSite'  => (string) ( $t['eigenSiteTekst'] ?? '' ),
		'engels'     => (string) ( $t['aanbiedingstekstEngels'] ?? '' ),
	);
}

// ---------------------------------------------------------------------------
// Hoofd-normalisatie
// ---------------------------------------------------------------------------

function yc_rw_normalize_object( $raw ) {
	if ( ! is_array( $raw ) || ! $raw ) {
		return null;
	}
	$diversen      = $raw['diversen']['diversen'] ?? array();
	$afdelingscode = (string) ( ( $diversen['afdelingscode'] ?? '' ) ?: ( $raw['afdelingscode'] ?? '' ) );
	$objectcode    = (string) ( ( $diversen['objectcode'] ?? '' ) ?: ( $raw['objectcode'] ?? '' ) );
	// let op: status staat op root-niveau; diversen.status is iets anders (woonruimte)
	$status       = (string) ( ( $raw['status'] ?? '' ) ?: ( $diversen['status'] ?? '' ) );
	$hoofdfunctie = (string) ( $raw['kenmerken']['hoofdfunctie'] ?? '' );

	$adres       = yc_rw_bepaal_adres( $raw['adres'] ?? null );
	$f           = yc_rw_bepaal_functies( $raw['object'] ?? null );
	$overdracht  = $raw['financieel']['overdracht'] ?? array();
	$prijs       = yc_rw_bepaal_prijs( $overdracht['koopEnOfHuur'] ?? null, false !== ( $raw['marketing']['publicatie']['prijsvermelding'] ?? null ) );
	$transactie  = $overdracht['transactie'] ?? null;
	$media       = yc_rw_verdeel_media( $raw['media'] ?? null );
	$gd          = $raw['gebouwdetails'] ?? array();
	$st_labels   = yc_rw_status_labels();
	$fn_labels   = yc_rw_functie_labels();

	$plaats_slug = sanitize_title( $adres['plaats'] ?: 'object' );
	$straat_slug = sanitize_title( trim( $adres['straat'] . ' ' . $adres['huisnummer'] ) ) ?: sanitize_title( $objectcode );

	return array(
		// identiteit
		'id'                => $raw['id'] ?? null,
		'afdelingscode'     => $afdelingscode,
		'objectcode'        => $objectcode,
		'ref'               => $afdelingscode . '/' . $objectcode,
		'plaatsSlug'        => $plaats_slug,
		'straatSlug'        => $straat_slug,

		// status
		'status'            => $status,
		'statusLabel'       => $st_labels[ $status ] ?? yc_rw_prettify( $status ),
		'beschikbaar'       => in_array( $status, yc_rw_beschikbaar_statussen(), true ),
		'referentie'        => in_array( $status, yc_rw_referentie_statussen(), true ),
		// vertrouwelijke objecten mogen nooit op de site (zie sync.php)
		'vertrouwelijk'     => true === ( $diversen['vertrouwelijk'] ?? null ),

		// classificatie
		'hoofdfunctie'      => $hoofdfunctie,
		'hoofdfunctieLabel' => $fn_labels[ $hoofdfunctie ] ?? yc_rw_prettify( $hoofdfunctie ),
		'functies'          => $f['functies'],
		'oppervlakte'       => $f['maxOppervlakte'],

		'adres'             => $adres,
		'prijs'             => $prijs,
		'teksten'           => yc_rw_raap_tekst( $raw['teksten'] ?? null ),

		// kenmerken
		// energieklasse: A, A_P, A_PP ... -> A, A+, A++
		'energielabel'      => str_replace( array( '_', 'P' ), array( '', '+' ), (string) ( $gd['energielabel']['energieklasse'] ?? '' ) ) ?: null,
		// bouwjaar1 kan leeg zijn; dan levert Realworks een periode-enum ("TOT_1906")
		'bouwjaar'          => ( $gd['bouwjaar']['bouwjaar1'] ?? null ) ?: ( yc_rw_prettify( $gd['bouwjaar']['bouwperiode'] ?? '' ) ?: null ),
		'gebouwnaam'        => ( $gd['gebouwnaam'] ?? null ) ?: null,

		// media
		'media'             => $media,
		'aantalFotos'       => count( $media['fotos'] ),

		// transactie (datalevel Transactie) — voor referentiepagina's
		'transactie'        => $transactie ? array(
			'transactieprijs'     => yc_rw_euro( $transactie['transactieprijs'] ?? null ),
			'ondertekeningsdatum' => ( $transactie['ondertekeningsdatum'] ?? null ) ?: null,
			'transportdatum'      => ( $transactie['transportdatum'] ?? null ) ?: null,
			'transactiedatum'     => ( $transactie['transactiedatum'] ?? null ) ?: null,
		) : null,

		// marketing
		'publicatiedatum'   => ( $raw['marketing']['publicatiedatum'] ?? null ) ?: null,
		'gewijzigd'         => ( $raw['tijdstipLaatsteWijziging'] ?? null ) ?: null,
	);
}

function yc_rw_normalize_lijst( $response ) {
	$resultaten = ( is_array( $response ) && is_array( $response['resultaten'] ?? null ) ) ? $response['resultaten'] : array();
	return array(
		'objecten' => array_values( array_filter( array_map( 'yc_rw_normalize_object', $resultaten ) ) ),
		'totaal'   => $response['paginering']['totaalAantal'] ?? count( $resultaten ),
		'volgende' => $response['paginering']['volgende'] ?? null,
	);
}
