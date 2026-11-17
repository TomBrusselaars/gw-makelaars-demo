<?php
/**
 * mock-data.php — voorbeeldobjecten in het ECHTE Realworks BOG v3-schema.
 *
 * Zelfde zes objecten als lib/mock-data.js. Doordat de structuur gelijk is aan
 * de echte API draait normalize.php identiek op mock en op live data.
 */

defined( 'ABSPATH' ) || exit;

/** Mock-media draait op de site zelf (zie inc/content.php). */
function yc_rw_mock_media_link( $obj, $n, $soort, $pdf = false ) {
	return '/?rw_mock_media=1&obj=' . rawurlencode( $obj ) . '&n=' . (int) $n . '&soort=' . $soort . ( $pdf ? '&pdf=1' : '' );
}

function yc_rw_mock_media( $objectcode, $fotos, $plan = 0, $doc = 0 ) {
	$arr = array();
	$vnr = 1;
	$mk  = function ( $soort, $titel, $mime, $link ) use ( &$vnr ) {
		return array(
			'soort' => $soort, 'volgnummer' => $vnr++, 'titel' => $titel, 'mimetype' => $mime,
			'omschrijving' => null, 'vrijgave' => true, 'link' => $link,
		);
	};
	$arr[] = $mk( 'HOOFDFOTO', null, 'image/jpeg', yc_rw_mock_media_link( $objectcode, 1, 'HOOFDFOTO' ) );
	for ( $i = 2; $i <= $fotos; $i++ ) {
		$arr[] = $mk( 'FOTO', null, 'image/jpeg', yc_rw_mock_media_link( $objectcode, $i, 'FOTO' ) );
	}
	for ( $i = 1; $i <= $plan; $i++ ) {
		$arr[] = $mk( 'PLATTEGROND', 'Plattegrond verdieping ' . $i, 'image/png', yc_rw_mock_media_link( $objectcode, $i, 'PLATTEGROND' ) );
	}
	for ( $i = 1; $i <= $doc; $i++ ) {
		$arr[] = $mk( 'DOCUMENT', 1 === $i ? 'Brochure' : 'Document ' . $i, 'application/pdf', yc_rw_mock_media_link( $objectcode, $i, 'DOCUMENT', true ) );
	}
	return $arr;
}

/** Bouw een ruw object in het Realworks-schema. */
function yc_rw_mock_obj( array $o ) {
	$o = array_merge( array(
		'toevoeging' => null, 'provincie' => 'ZUID_HOLLAND',
		'koopprijs' => 0, 'huurprijs' => 0, 'huurconditie' => null, 'koopconditie' => null,
		'koopprijsvoorvoegsel' => null, 'huurprijsvoorvoegsel' => null, 'servicekosten' => null,
		'btw' => true, 'energieklasse' => 'A', 'bouwjaar' => '1998', 'gebouwnaam' => null,
		'plan' => 0, 'doc' => 0, 'publicatiedatum' => '2025-06-01 06:00:00',
		'vertrouwelijk' => false, 'prijsvermelding' => true, 'huurprijstype' => null,
		'gewijzigd' => '2025-11-01 09:00:00', 'transactie' => null,
	), $o );

	return array(
		'id'           => $o['id'],
		'status'       => $o['status'],
		'bedrijfscode' => $o['afdelingscode'],
		'kenmerken'    => array( 'hoofdfunctie' => $o['hoofdfunctie'] ),
		'adres'        => array(
			'aanvullendOpAdres' => null,
			'straat'            => $o['straat'],
			'huisnummer'        => array( 'hoofdnummer' => (string) $o['hoofdnummer'], 'toevoeging' => $o['toevoeging'], 'reeksBegin' => null, 'reeksEind' => null ),
			'land'              => 'Nederland',
			'provincie'         => $o['provincie'],
			'postcode'          => $o['postcode'],
			'plaats'            => $o['plaats'],
		),
		'diversen'     => array(
			'diversen' => array(
				'objectcode' => $o['objectcode'], 'afdelingscode' => $o['afdelingscode'],
				'actief' => 'INGETROKKEN' !== $o['status'], 'vertrouwelijk' => $o['vertrouwelijk'],
				'status' => 'GEEN_WOONRUIMTE_AANWEZIG', 'type' => null, 'bouwvorm' => 'BESTAANDE_BOUW',
				'invoerdatum' => '2025-05-20', 'wijzigingsdatum' => $o['gewijzigd'],
			),
			'kadaster' => array(),
		),
		'financieel'   => array(
			'overdracht' => array(
				'koopEnOfHuur' => array(
					'koopprijs' => $o['koopprijs'], 'huurprijs' => $o['huurprijs'],
					'huurconditie' => $o['huurconditie'], 'koopconditie' => $o['koopconditie'],
					'koopprijsvoorvoegsel' => $o['koopprijsvoorvoegsel'], 'huurprijsvoorvoegsel' => $o['huurprijsvoorvoegsel'],
					'servicekosten' => $o['servicekosten'], 'btwVanToepassing' => $o['btw'],
					'huurprijstype' => $o['huurprijstype'],
					'aanmeldingsreden' => $o['aanmeldingsreden'], 'aanvaarding' => 'IN_OVERLEG',
				),
				'transactie'   => $o['transactie'] ?: array(),
			),
		),
		'gebouwdetails' => array(
			'energielabel' => array( 'energieklasse' => $o['energieklasse'], 'energieindex' => null, 'epc' => null, 'energieEinddatum' => null ),
			'bouwjaar'     => array( 'bouwjaar1' => $o['bouwjaar'], 'bouwperiode' => null, 'bogInAanbouw' => false ),
			'gebouwnaam'   => $o['gebouwnaam'],
		),
		'teksten'      => array(
			'aanbiedingstekst' => $o['aanbiedingstekst'], 'eigenSiteTekst' => null, 'aanbiedingstekstEngels' => null,
		),
		'object'       => array( 'functies' => $o['functies'] ),
		'media'        => yc_rw_mock_media( $o['objectcode'], $o['fotos'], $o['plan'], $o['doc'] ),
		'marketing'    => array( 'publicatiedatum' => $o['publicatiedatum'], 'publicatie' => array( 'prijsvermelding' => $o['prijsvermelding'] ) ),
		'tijdstipLaatsteWijziging' => $o['gewijzigd'],
	);
}

function yc_rw_mock_objecten() {
	// Functie-blokken (met oppervlakte) zoals in het echte schema
	$kantoor = function ( $opp ) { return array( 'type' => 'KANTOORRUIMTE', 'actief' => true, 'kantoorruimte' => array( 'oppervlakte' => $opp ) ); };
	$winkel  = function ( $opp ) { return array( 'type' => 'WINKELRUIMTE', 'actief' => true, 'winkelruimte' => array( 'oppervlakte' => $opp, 'verkoopVloeroppervlakte' => $opp ) ); };
	$bedrijf = function ( $opp ) { return array( 'type' => 'BEDRIJFSRUIMTE', 'actief' => true, 'bedrijfsruimte' => array( 'bedrijfshal' => array( 'oppervlakte' => $opp ) ) ); };
	$horeca  = function ( $opp ) { return array( 'type' => 'HORECA', 'actief' => true, 'horeca' => array( 'oppervlakte' => $opp ) ); };
	$belegg  = function ( $opp ) { return array( 'type' => 'BELEGGING', 'actief' => true, 'belegging' => array( 'oppervlakte' => $opp ) ); };

	// Eén koppeling, twee vestigingen (Schaub Rotterdam + GW Vlaardingen)
	$schaub = '936358';
	$gw     = '940012';

	return array(
		yc_rw_mock_obj( array(
			'id' => 408901, 'afdelingscode' => $schaub, 'objectcode' => 'B100001', 'status' => 'BESCHIKBAAR',
			'hoofdfunctie' => 'KANTOORRUIMTE', 'functies' => array( $kantoor( 245 ) ),
			'straat' => 'Van Vollenhovenstraat', 'hoofdnummer' => 7, 'postcode' => '3016 BG', 'plaats' => 'ROTTERDAM',
			'publicatiedatum' => '2025-10-28 06:00:00',
			'huurprijs' => 195, 'huurconditie' => 'PER_VIERKANTE_METERS_PER_JAAR', 'aanmeldingsreden' => 'IN_VERHUUR_GENOMEN',
			'servicekosten' => 45, 'energieklasse' => 'A', 'bouwjaar' => '1905', 'gebouwnaam' => 'Scheepvaartkwartier',
			'aanbiedingstekst' => 'Representatieve kantoorruimte in het geliefde Scheepvaartkwartier te Rotterdam. Dit karakteristieke pand biedt circa 245 m² verdeeld over twee verdiepingen, met hoge plafonds, authentieke details en moderne voorzieningen. Op loopafstand van het Park, de Erasmus MC en uitstekend bereikbaar.',
			'fotos' => 18, 'plan' => 2, 'doc' => 2,
		) ),
		yc_rw_mock_obj( array(
			'id' => 408902, 'afdelingscode' => $schaub, 'objectcode' => 'B100002', 'status' => 'ONDER_OPTIE',
			'hoofdfunctie' => 'WINKELRUIMTE', 'functies' => array( $winkel( 120 ), $horeca( 60 ) ),
			'straat' => 'Blaak', 'hoofdnummer' => 34, 'postcode' => '3011 TA', 'plaats' => 'ROTTERDAM',
			'publicatiedatum' => '2025-09-15 06:00:00',
			'huurprijs' => 4500, 'huurconditie' => 'PER_MAAND', 'aanmeldingsreden' => 'IN_VERHUUR_GENOMEN',
			'servicekosten' => 350, 'energieklasse' => 'B', 'bouwjaar' => '1988',
			'aanbiedingstekst' => 'Multifunctionele winkel-/horecaruimte op een A1-locatie aan de Blaak, direct bij de Markthal en metrostation Blaak. Circa 120 m² winkel met 60 m² horecabestemming. Grote etalagepui, veel loop.',
			'fotos' => 12, 'plan' => 1, 'doc' => 1,
		) ),
		yc_rw_mock_obj( array(
			'id' => 408903, 'afdelingscode' => $schaub, 'objectcode' => 'B100003', 'status' => 'BESCHIKBAAR',
			'hoofdfunctie' => 'BEDRIJFSRUIMTE', 'functies' => array( $bedrijf( 1850 ), $kantoor( 220 ) ),
			'straat' => 'Schiedamsedijk', 'hoofdnummer' => 100, 'postcode' => '3011 ED', 'plaats' => 'ROTTERDAM',
			'publicatiedatum' => '2025-08-20 06:00:00',
			'koopprijs' => 2650000, 'koopconditie' => 'KOSTEN_KOPER', 'koopprijsvoorvoegsel' => 'VRAAGPRIJS',
			'aanmeldingsreden' => 'IN_VERKOOP_GENOMEN', 'energieklasse' => 'C', 'bouwjaar' => '1975',
			'aanbiedingstekst' => 'Zelfstandige bedrijfsruimte van circa 1.850 m² met bijbehorende kantoorruimte van 220 m². Vrije hoogte 6 meter, meerdere overheaddeuren, eigen terrein met parkeergelegenheid. Uitstekend bereikbaar via de Maastunnel en A16.',
			// bewust 33 foto's: in Webflow CMS raakt dit de limiet van 25, hier niet
			'fotos' => 33, 'plan' => 3, 'doc' => 1,
		) ),
		yc_rw_mock_obj( array(
			'id' => 408904, 'afdelingscode' => $schaub, 'objectcode' => 'B100004', 'status' => 'BESCHIKBAAR',
			'hoofdfunctie' => 'KANTOORRUIMTE', 'functies' => array( $kantoor( 90 ) ),
			'straat' => 'Oostplein', 'hoofdnummer' => 20, 'postcode' => '3011 KT', 'plaats' => 'ROTTERDAM',
			'publicatiedatum' => '2025-10-10 06:00:00',
			'huurprijs' => 1350, 'huurconditie' => 'PER_MAAND', 'aanmeldingsreden' => 'IN_VERHUUR_GENOMEN',
			'servicekosten' => 150, 'energieklasse' => 'A', 'bouwjaar' => '2012',
			'aanbiedingstekst' => 'Instapklare kantoorunit van circa 90 m² nabij het Oostplein. Open indeling, pantry, airconditioning en glasvezel aanwezig. Ideaal voor een startend bedrijf of satellietkantoor.',
			'fotos' => 7, 'plan' => 1, 'doc' => 1,
		) ),
		yc_rw_mock_obj( array(
			'id' => 408905, 'afdelingscode' => $schaub, 'objectcode' => 'B100005', 'status' => 'VERKOCHT',
			'hoofdfunctie' => 'BELEGGING', 'functies' => array( $belegg( 1400 ) ),
			'straat' => 'Coolsingel', 'hoofdnummer' => 120, 'postcode' => '3011 AG', 'plaats' => 'ROTTERDAM',
			'koopprijs' => 4950000, 'koopconditie' => 'KOSTEN_KOPER', 'koopprijsvoorvoegsel' => 'KOOPPRIJS',
			'aanmeldingsreden' => 'IN_VERKOOP_GENOMEN', 'energieklasse' => 'A', 'bouwjaar' => '2001',
			'aanbiedingstekst' => 'Gemengd beleggingsobject aan de Coolsingel met winkel- en kantoorruimte. Volledig verhuurd aan solide huurders. Verkocht in 2025.',
			'fotos' => 10,
			'publicatiedatum' => '2025-01-15 06:00:00', 'gewijzigd' => '2025-09-20 14:00:00',
			'transactie' => array( 'transactieprijs' => 4950000, 'ondertekeningsdatum' => '2025-09-12', 'transportdatum' => '2025-10-01', 'transactiedatum' => '2025-09-12' ),
		) ),
		yc_rw_mock_obj( array(
			'id' => 408906, 'afdelingscode' => $gw, 'objectcode' => 'G200001', 'status' => 'VERHUURD',
			'hoofdfunctie' => 'KANTOORRUIMTE', 'functies' => array( $kantoor( 310 ) ),
			'straat' => 'Westhavenkade', 'hoofdnummer' => 80, 'postcode' => '3131 AE', 'plaats' => 'VLAARDINGEN',
			'huurprijs' => 145, 'huurconditie' => 'PER_VIERKANTE_METERS_PER_JAAR', 'aanmeldingsreden' => 'IN_VERHUUR_GENOMEN',
			'energieklasse' => 'B', 'bouwjaar' => '1992',
			'aanbiedingstekst' => 'Kantoorruimte aan de historische Westhavenkade in Vlaardingen. Circa 310 m² met uitzicht op de haven. Recent verhuurd — getoond als referentie (GW Makelaars).',
			'fotos' => 9, 'plan' => 1,
			'publicatiedatum' => '2025-03-01 06:00:00', 'gewijzigd' => '2025-10-10 11:00:00',
			'transactie' => array( 'transactieprijs' => null, 'ondertekeningsdatum' => '2025-10-05', 'transportdatum' => null, 'transactiedatum' => '2025-10-05' ),
		) ),
		// --- Randgevallen uit de officiële spec (toegevoegd na de controle van 2026-11-16) ---
		yc_rw_mock_obj( array( // prijs n.o.t.k. + energielabel A++ + "vanaf"-huur wordt niet getoond
			'id' => 408907, 'afdelingscode' => $gw, 'objectcode' => 'G200002', 'status' => 'BESCHIKBAAR',
			'hoofdfunctie' => 'BEDRIJFSRUIMTE', 'functies' => array( $bedrijf( 640 ) ),
			'straat' => 'Deltaweg', 'hoofdnummer' => 12, 'postcode' => '3133 KM', 'plaats' => 'VLAARDINGEN',
			'koopprijs' => 895000, 'koopconditie' => 'KOSTEN_KOPER', 'koopprijsvoorvoegsel' => 'PRIJS_NOTK',
			'aanmeldingsreden' => 'IN_VERKOOP_GENOMEN', 'energieklasse' => 'A_PP', 'bouwjaar' => '2019',
			'aanbiedingstekst' => 'Moderne bedrijfsunit van circa 640 m² op bedrijventerrein Vergulde Hand. Prijs nader overeen te komen.',
			'fotos' => 5, 'publicatiedatum' => '2025-10-20 06:00:00',
		) ),
		yc_rw_mock_obj( array( // huur "vanaf" + prijsvermelding uit -> prijs mag niet zichtbaar zijn
			'id' => 408908, 'afdelingscode' => $gw, 'objectcode' => 'G200003', 'status' => 'BESCHIKBAAR',
			'hoofdfunctie' => 'KANTOORRUIMTE', 'functies' => array( $kantoor( 180 ) ),
			'straat' => 'Hoogstraat', 'hoofdnummer' => 55, 'postcode' => '3131 BL', 'plaats' => 'VLAARDINGEN',
			'huurprijs' => 2100, 'huurconditie' => 'PER_MAAND', 'aanmeldingsreden' => 'IN_VERHUUR_GENOMEN',
			'prijsvermelding' => false,
			'aanbiedingstekst' => 'Kantoorverdieping van circa 180 m² in het centrum van Vlaardingen.',
			'fotos' => 4, 'publicatiedatum' => '2025-10-18 06:00:00',
		) ),
		yc_rw_mock_obj( array( // vertrouwelijk -> mag NOOIT op de site komen
			'id' => 408909, 'afdelingscode' => $schaub, 'objectcode' => 'B100009', 'status' => 'BESCHIKBAAR',
			'hoofdfunctie' => 'BELEGGING', 'functies' => array( $belegg( 2200 ) ),
			'straat' => 'Geheimeweg', 'hoofdnummer' => 1, 'postcode' => '3011 AA', 'plaats' => 'ROTTERDAM',
			'koopprijs' => 7500000, 'koopconditie' => 'KOSTEN_KOPER', 'koopprijsvoorvoegsel' => 'VRAAGPRIJS',
			'aanmeldingsreden' => 'IN_VERKOOP_GENOMEN', 'vertrouwelijk' => true,
			'aanbiedingstekst' => 'Vertrouwelijk object.', 'fotos' => 2,
		) ),
		yc_rw_mock_obj( array( // prospect -> nog niet openbaar, niet in het aanbod
			'id' => 408910, 'afdelingscode' => $schaub, 'objectcode' => 'B100010', 'status' => 'PROSPECT',
			'hoofdfunctie' => 'WINKELRUIMTE', 'functies' => array( $winkel( 75 ) ),
			'straat' => 'Prospectlaan', 'hoofdnummer' => 3, 'postcode' => '3012 AB', 'plaats' => 'ROTTERDAM',
			'huurprijs' => 1800, 'huurconditie' => 'PER_MAAND', 'huurprijstype' => 'VAN', 'aanmeldingsreden' => 'IN_VERHUUR_GENOMEN',
			'aanbiedingstekst' => 'Nog niet in de verkoop.', 'fotos' => 2,
		) ),
	);
}

function yc_rw_mock_lijst( array $filters = array() ) {
	$res = yc_rw_mock_objecten();
	if ( ! empty( $filters['afdelingscode'] ) ) {
		$code = (string) $filters['afdelingscode'];
		$res  = array_filter( $res, function ( $o ) use ( $code ) {
			return $o['diversen']['diversen']['afdelingscode'] === $code;
		} );
	}
	if ( 'true' === ( $filters['actief'] ?? '' ) ) {
		$res = array_filter( $res, function ( $o ) {
			return true === $o['diversen']['diversen']['actief'];
		} );
	}
	$res = array_values( $res );
	return array( 'resultaten' => $res, 'paginering' => array( 'totaalAantal' => count( $res ) ) );
}
