<?php
/**
 * Componenten (organismen) — het register.
 *
 * Elke homepage-sectie is een blok in de WordPress-editor. Dit bestand is de
 * enige plek die beschrijft welke velden een blok heeft; de editor bouwt zijn
 * formulier hieruit en de veiligheidscontrole bij het tonen leest hetzelfde.
 * Een nieuw blok maken = hier een regel toevoegen + een template in
 * template-parts/blocks/<naam>.php.
 *
 * Veldtypes: text, textarea, url, number, select, image, lijst (herhaalbare items).
 */

defined( 'ABSPATH' ) || exit;

function gw_componenten() {
	static $c = null;
	if ( null !== $c ) {
		return $c;
	}

	$tekst  = static function ( $label, $std = '', array $x = array() ) { return array_merge( array( 'type' => 'text', 'label' => $label, 'standaard' => $std ), $x ); };
	$lang   = static function ( $label, $std = '', array $x = array() ) { return array_merge( array( 'type' => 'textarea', 'label' => $label, 'standaard' => $std ), $x ); };
	$url    = static function ( $label, $std = '' ) { return array( 'type' => 'url', 'label' => $label, 'standaard' => $std ); };
	$getal  = static function ( $label, $std, $min, $max ) { return array( 'type' => 'number', 'label' => $label, 'standaard' => $std, 'min' => $min, 'max' => $max ); };
	$keuze  = static function ( $label, $std, array $opties ) { return array( 'type' => 'select', 'label' => $label, 'standaard' => $std, 'opties' => $opties ); };
	$beeld  = static function ( $label, $ontwerp, array $x = array() ) { return array_merge( array( 'type' => 'image', 'label' => $label, 'standaard' => 0, 'standaard_beeld' => $ontwerp ), $x ); };
	$info   = static function ( $label, $tekst, $url = '' ) { return array( 'type' => 'info', 'label' => $label, 'tekst' => $tekst, 'url' => $url, 'groep' => 'Contactpersoon' ); };
	$lijst  = static function ( $label, array $velden, array $items, $titelveld ) { return array( 'type' => 'lijst', 'label' => $label, 'velden' => $velden, 'standaard' => $items, 'titelveld' => $titelveld, 'max' => 12 ); };

	$c = array(
		'hero'        => array(
			'titel'  => 'Hero',
			'icon'   => 'cover-image',
			'omschr' => 'Grote openingsfoto met kop, tekst en knop.',
			'anker'  => '',
			'velden' => array(
				'afbeelding'  => $beeld( 'Hoofdfoto (krijgt automatisch de schuine vorm)', 'hero.webp' ),
				'regel_voor'  => $tekst( 'Kop — begin', 'Regie over' ),
				'accent'      => $tekst( 'Kop — woord in accentkleur', 'regionale' ),
				'regel_na'    => $tekst( 'Kop — einde', 'bedrijfshuisvesting' ),
				'tekst'       => $lang( 'Intro', 'Wij kennen de panden in Schiedam, Vlaardingen, Maassluis en omstreken. Omdat we er zelf zitten, en omdat we de eigenaren kennen.' ),
				'knop_label'  => $tekst( 'Knop — tekst', 'Bekijk het aanbod' ),
				'knop_url'    => $url( 'Knop — link', '#aanbod' ),
				'persoon'     => $info( 'Getoonde contactpersoon (kaartje rechtsonder)', 'Kies de persoon onder Weergave → Aanpassen → GW makelaars → Contactpersoon. Alle teamleden beheer je onder Team. De keuze geldt ook voor de afsluiter en de footer.', admin_url( 'customize.php?autofocus[control]=gw_contactpersoon' ) ),
			),
		),
		'aanbod'      => array(
			'titel'  => 'Aanbod (Realworks)',
			'icon'   => 'building',
			'omschr' => 'Live aanbod uit Realworks met filter. De objecten zelf beheer je in Realworks.',
			'anker'  => 'aanbod',
			'velden' => array(
				'tag'        => $tekst( 'Label boven de kop', 'Aanbod' ),
				'kop'        => $tekst( 'Kop', 'Wat er nu beschikbaar is' ),
				'max'        => $getal( 'Aantal objecten tonen', 9, 3, 30 ),
				'knop_label' => $tekst( 'Knop onder het aanbod', 'Volledig aanbod' ),
			),
		),
		'over'        => array(
			'titel'  => 'Over ons + cijfers',
			'icon'   => 'groups',
			'omschr' => 'Kop, intro en drie cijfers met foto.',
			'anker'  => 'over-ons',
			'velden' => array(
				'tag'        => $tekst( 'Label boven de kop', 'Over ons' ),
				'kop'        => $tekst( 'Kop', 'We kennen niet alleen de straten, maar ook de pandeigenaren.' ),
				'tekst'      => $lang( 'Intro', 'Wij zijn een hecht team van vastgoedspecialisten, met de decennialange ervaring van de Schaub Groep als stevige basis. Omdat we ons uitsluitend focussen op Schiedam, Vlaardingen, Maassluis en Rotterdam-West, weten we precies wat er speelt in de regio.' ),
				'stats'      => $lijst(
					'Cijfers',
					array(
						'getal'      => $tekst( 'Getal', '' ),
						'tekst'      => $tekst( 'Omschrijving', '' ),
						'afbeelding' => $beeld( 'Foto (krijgt automatisch de schuine vorm)', 'stat-%d.webp', array( 'standaard_max' => 3 ) ),
					),
					array(
						array( 'getal' => '35+', 'tekst' => 'Jaar ervaring', 'afbeelding' => 0 ),
						array( 'getal' => '40.000m²', 'tekst' => 'Succesvol bemiddeld', 'afbeelding' => 0 ),
						array( 'getal' => '65%', 'tekst' => 'Off-market deals', 'afbeelding' => 0 ),
					),
					'getal'
				),
				'knop_label' => $tekst( 'Knop — tekst', 'Meer over ons' ),
				'knop_url'   => $url( 'Knop — link', '#over-ons' ),
				'link_label' => $tekst( 'Tekstlink — tekst', 'Ontdek diensten' ),
				'link_url'   => $url( 'Tekstlink — link', '#diensten' ),
			),
		),
		'diensten'    => array(
			'titel'  => 'Diensten',
			'icon'   => 'list-view',
			'omschr' => 'Uitklaplijst met foto per dienst; loopt vanzelf door. De diensten beheer je onder Diensten in het beheermenu.',
			'anker'  => 'diensten',
			'velden' => array(
				'tag' => $tekst( 'Label boven de kop', 'Diensten' ),
				'kop' => $tekst( 'Kop', 'Aan welke kant van de deur sta je?' ),
				'knop_label' => $tekst( 'Tekstlink onder de lijst — tekst (leeg = geen link)', 'Bekijk alle diensten' ),
				'knop_url'   => $url( 'Tekstlink onder de lijst — link (leeg = pagina Diensten)', '' ),
			),
		),
		'nieuws'      => array(
			'titel'  => 'Nieuws',
			'icon'   => 'megaphone',
			'omschr' => 'Sleepbare kaarten met de nieuwste berichten uit Nieuws (zonder uitgelichte afbeelding verschijnt een standaardfoto). Een nieuwe site start met vier voorbeeldberichten uit het ontwerp.',
			'anker'  => 'nieuws',
			'velden' => array(
				'tag'        => $tekst( 'Label boven de kop', 'Nieuws' ),
				'kop'        => $tekst( 'Kop', 'Actueel uit de omgeving' ),
				'link_label' => $tekst( 'Tekstlink — tekst', 'Bekijk alle' ),
				'link_url'   => $url( 'Tekstlink — link (leeg = berichtenpagina)', '' ),
				'aantal'     => $getal( 'Aantal berichten', 6, 3, 12 ),
			),
		),
		'faq'         => array(
			'titel'  => 'Veelgestelde vragen',
			'icon'   => 'editor-help',
			'omschr' => 'Accordeon met vragen en antwoorden. De vragen beheer je onder Vragen (FAQ) in het beheermenu.',
			'anker'  => 'faq',
			'velden' => array(
				'tag'       => $tekst( 'Label boven de kop', 'FAQ' ),
				'kop'       => $tekst( 'Kop', 'Deze vragen krijgen we vaker' ),
				'categorie' => $tekst( 'Alleen deze categorie tonen (korte naam; leeg = alle vragen)', '' ),
				'open'      => $getal( 'Standaard geopend (nummer, 0 = geen)', 2, 0, 12 ),
			),
		),
		'cta'         => array(
			'titel'  => 'Contact / actie',
			'icon'   => 'phone',
			'omschr' => 'Afsluiter met kop en tekst, met de contactpersoon (uit Weergave → Aanpassen → GW makelaars) of met een knop.',
			'anker'  => 'contact',
			'velden' => array(
				'kop'   => $tekst( 'Kop', 'Klaar om de juiste ruimte te vinden?' ),
				'tekst' => $lang( 'Tekst', 'Bel gerust voordat je plan rond is. Eén gesprek is meestal genoeg om te weten of er iets voor je is in de regio.' ),
				'persoon'    => $keuze( 'Rechts tonen', 'ja', array( 'ja' => 'contactpersoon met telefoon en e-mail', 'nee' => 'een knop' ) ),
				'persoon_info' => $info( 'Welke contactpersoon?', 'Wordt gekozen onder Weergave → Aanpassen → GW makelaars → Contactpersoon (uit Team).', admin_url( 'customize.php?autofocus[control]=gw_contactpersoon' ) ),
				'knop_label' => $tekst( 'Knop — tekst (bij "een knop")', 'Neem contact op' ),
				'knop_url'   => $url( 'Knop — link (bij "een knop")', '#contact' ),
			),
		),
		'kaarten'     => array(
			'titel'  => 'Kaarten (raster)',
			'icon'   => 'grid-view',
			'omschr' => 'Raster met foto, titel, tekst en optionele link: voor diensten, team, voordelen of projecten.',
			'anker'  => '',
			'velden' => array(
				'tag'       => $tekst( 'Label boven de kop', '' ),
				'kop'       => $tekst( 'Kop', 'Onze diensten' ),
				'kolommen'  => $keuze( 'Aantal kolommen', '3', array( '2' => '2', '3' => '3', '4' => '4' ) ),
				'kleur'     => $keuze( 'Achtergrond', 'sand', array( 'sand' => 'zand', 'olive' => 'olijf (witte tekst)' ) ),
				'foto'      => $keuze( 'Foto per kaart', 'ja', array( 'ja' => 'tonen', 'nee' => 'niet tonen (alleen tekst)' ) ),
				'items'     => $lijst(
					'Kaarten',
					array(
						'titel'      => $tekst( 'Titel', '' ),
						'tekst'      => $lang( 'Tekst', '', array( 'rijen' => 3 ) ),
						'link_url'   => $url( 'Link (leeg = geen link)', '' ),
						'afbeelding' => $beeld( 'Foto', 'dienst.webp' ),
					),
					array(
						array( 'titel' => 'Eerste kaart', 'tekst' => 'Korte toelichting bij deze kaart.', 'link_url' => '', 'afbeelding' => 0 ),
						array( 'titel' => 'Tweede kaart', 'tekst' => 'Korte toelichting bij deze kaart.', 'link_url' => '', 'afbeelding' => 0 ),
						array( 'titel' => 'Derde kaart', 'tekst' => 'Korte toelichting bij deze kaart.', 'link_url' => '', 'afbeelding' => 0 ),
					),
					'titel'
				),
			),
		),
		'formulier'   => array(
			'titel'  => 'Contactformulier',
			'icon'   => 'email',
			'omschr' => 'Formulier dat naar het e-mailadres uit Weergave → Aanpassen → GW makelaars gaat.',
			'anker'  => 'contact',
			'velden' => array(
				'tag'        => $tekst( 'Label boven de kop', 'Contact' ),
				'kop'        => $tekst( 'Kop', 'Stel je vraag' ),
				'tekst'      => $lang( 'Tekst naast het formulier', 'Laat je gegevens achter, dan nemen we binnen één werkdag contact met je op.', array( 'rijen' => 3 ) ),
				'knop_label' => $tekst( 'Verzendknop', 'Versturen' ),
				'succes'     => $tekst( 'Bedankt-melding', 'Bedankt! We nemen snel contact met je op.' ),
			),
		),
		'stappen'     => array(
			'titel'  => 'Stappenplan',
			'icon'   => 'editor-ol',
			'omschr' => 'Genummerde stappen naast elkaar: werkwijze, proces of aanpak.',
			'anker'  => '',
			'velden' => array(
				'tag'      => $tekst( 'Label boven de kop', 'Werkwijze' ),
				'kop'      => $tekst( 'Kop', 'In drie stappen geregeld' ),
				'kolommen' => $keuze( 'Aantal kolommen', '3', array( '2' => '2', '3' => '3', '4' => '4' ) ),
				'kleur'    => $keuze( 'Achtergrond', 'sand', array( 'sand' => 'zand', 'olive' => 'olijf (witte tekst)' ) ),
				'items'    => $lijst(
					'Stappen',
					array(
						'titel' => $tekst( 'Titel', '' ),
						'tekst' => $lang( 'Tekst', '', array( 'rijen' => 3 ) ),
					),
					array(
						array( 'titel' => 'Kennismaken', 'tekst' => 'We bespreken je wensen en de mogelijkheden.' ),
						array( 'titel' => 'Zoeken en bezichtigen', 'tekst' => 'We selecteren passende panden en plannen bezichtigingen.' ),
						array( 'titel' => 'Afronden', 'tekst' => 'We onderhandelen en regelen de overdracht.' ),
					),
					'titel'
				),
			),
		),
		'cijfers'     => array(
			'titel'  => 'Cijferstrook',
			'icon'   => 'chart-bar',
			'omschr' => 'Brede strook met drie tot vier kerncijfers.',
			'anker'  => '',
			'velden' => array(
				'kleur' => $keuze( 'Achtergrond', 'olive', array( 'olive' => 'olijf (witte tekst)', 'sand' => 'zand' ) ),
				'items' => $lijst(
					'Cijfers',
					array(
						'getal' => $tekst( 'Getal', '' ),
						'tekst' => $tekst( 'Omschrijving', '' ),
					),
					array(
						array( 'getal' => '35+', 'tekst' => 'Jaar ervaring' ),
						array( 'getal' => '40.000m²', 'tekst' => 'Succesvol bemiddeld' ),
						array( 'getal' => '65%', 'tekst' => 'Off-market deals' ),
					),
					'getal'
				),
			),
		),
		'citaat'      => array(
			'titel'  => 'Citaat / referentie',
			'icon'   => 'format-quote',
			'omschr' => 'Uitgelicht citaat van een klant, met portret.',
			'anker'  => '',
			'velden' => array(
				'tekst'      => $lang( 'Citaat', 'Dankzij GW vonden we binnen twee weken precies de ruimte die we zochten.', array( 'rijen' => 3 ) ),
				'naam'       => $tekst( 'Naam', 'Voornaam Achternaam' ),
				'functie'    => $tekst( 'Functie en bedrijf', 'Directeur, Bedrijfsnaam' ),
				'afbeelding' => $beeld( 'Portret', 'stan.webp' ),
				'kleur'      => $keuze( 'Achtergrond', 'sand', array( 'sand' => 'zand', 'olive' => 'olijf (witte tekst)' ) ),
			),
		),
		'team'        => array(
			'titel'  => 'Team',
			'icon'   => 'groups',
			'omschr' => 'Teamleden met portret, functie en contactgegevens. De teamleden beheer je onder Team in het beheermenu.',
			'anker'  => '',
			'velden' => array(
				'tag'      => $tekst( 'Label boven de kop', 'Team' ),
				'kop'      => $tekst( 'Kop', 'Maak kennis met ons team' ),
				'kolommen' => $keuze( 'Aantal kolommen', '3', array( '2' => '2', '3' => '3', '4' => '4' ) ),
				'kleur'    => $keuze( 'Achtergrond', 'sand', array( 'sand' => 'zand', 'olive' => 'olijf (witte tekst)' ) ),
			),
		),
		'tekst-beeld' => array(
			'titel'  => 'Tekst (met of zonder foto)',
			'icon'   => 'align-pull-left',
			'omschr' => 'Algemene sectie voor losse pagina\'s: kop, tekst en knop, met of zonder foto ernaast.',
			'anker'  => '',
			'velden' => array(
				'tag'        => $tekst( 'Label boven de kop', '' ),
				'kop'        => $tekst( 'Kop', 'Kop van deze sectie' ),
				'niveau'     => $keuze( 'Kopniveau (h1 alleen bovenaan de pagina)', 'h2', array( 'h2' => 'h2 — sectiekop', 'h1' => 'h1 — paginatitel' ) ),
				'tekst'      => $lang( 'Tekst', '', array( 'rijen' => 6 ) ),
				'knop_label' => $tekst( 'Knop — tekst', '' ),
				'knop_url'   => $url( 'Knop — link', '' ),
				'foto'       => $keuze( 'Foto', 'ja', array( 'ja' => 'tonen', 'nee' => 'geen foto (alleen tekst)' ) ),
				'afbeelding' => $beeld( 'Foto', 'dienst.webp' ),
				'richting'   => $keuze( 'Foto staat', 'rechts', array( 'rechts' => 'rechts', 'links' => 'links' ) ),
				'kleur'      => $keuze( 'Achtergrond', 'sand', array( 'sand' => 'zand', 'olive' => 'olijf (witte tekst)' ) ),
			),
		),
	);

	// Elk blok krijgt een anker-veld: een id waarmee je ernaar kunt linken (#aanbod).
	foreach ( $c as $slug => $comp ) {
		$c[ $slug ]['velden']['anker'] = array(
			'type'      => 'text',
			'label'     => 'Anker (id om naar deze sectie te linken)',
			'standaard' => $comp['anker'],
			'groep'     => 'Geavanceerd',
		);
	}
	return $c;
}

// ---------------------------------------------------------------------------
// Opschonen: alles wat uit de editor komt wordt bij het tonen opnieuw gecontroleerd.
// ---------------------------------------------------------------------------

function gw_veld_schoon( array $d, $w ) {
	switch ( $d['type'] ) {
		case 'textarea':
			return sanitize_textarea_field( is_scalar( $w ) ? (string) $w : '' );
		case 'url':
			return esc_url_raw( is_scalar( $w ) ? (string) $w : '' );
		case 'select':
			return is_scalar( $w ) && array_key_exists( (string) $w, $d['opties'] ) ? (string) $w : $d['standaard'];
		case 'number':
			return max( (int) $d['min'], min( (int) $d['max'], (int) $w ) );
		case 'image':
			return absint( $w );
		case 'lijst':
			$uit = array();
			foreach ( array_slice( is_array( $w ) ? $w : array(), 0, (int) $d['max'] ) as $item ) {
				$rij = array();
				foreach ( $d['velden'] as $k => $sub ) {
					$rij[ $k ] = gw_veld_schoon( $sub, is_array( $item ) && isset( $item[ $k ] ) ? $item[ $k ] : $sub['standaard'] );
				}
				$uit[] = $rij;
			}
			return $uit;
		default:
			return sanitize_text_field( is_scalar( $w ) ? (string) $w : '' );
	}
}

/** Attributen van een blok: standaardwaarden aangevuld en opgeschoond. */
function gw_attrs( $slug, array $attrs ) {
	$comp = gw_componenten();
	$uit  = array();
	foreach ( $comp[ $slug ]['velden'] as $k => $d ) {
		if ( 'info' === $d['type'] ) {
			continue;
		}
		$uit[ $k ] = gw_veld_schoon( $d, array_key_exists( $k, $attrs ) ? $attrs[ $k ] : $d['standaard'] );
	}
	if ( isset( $uit['anker'] ) ) {
		$uit['anker'] = sanitize_title( $uit['anker'] );
	}
	return $uit;
}
