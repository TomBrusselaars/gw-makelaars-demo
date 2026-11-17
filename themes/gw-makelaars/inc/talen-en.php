<?php
/**
 * Engelse versie van de website (Polylang, taal "en").
 *
 * 1. gw_t(): vertaalt de vaste teksten van het thema (menu, footer, knoppen) naar het Engels als de
 *    Engelse taal actief is. Bestaande teksten blijven de sleutel, dus zonder Polylang verandert niets.
 * 2. Eenmalig (in het beheer): Engelse Home, pagina's Diensten/Nieuws, diensten, vragen, nieuws en
 *    teamleden, gekoppeld aan hun Nederlandse versie. Pas ze daarna gewoon aan in het beheer.
 */

defined( 'ABSPATH' ) || exit;

/** Huidige taal ('nl' als er geen Polylang is). */
function gw_taal() {
	return function_exists( 'pll_current_language' ) && pll_current_language() ? pll_current_language() : 'nl';
}

/** Vaste thema-teksten: Nederlands (sleutel) => Engels. */
function gw_vertalingen_en() {
	return array(
		// Menu en navigatie
		'Home'                       => 'Home',
		'Over ons'                   => 'About us',
		'Aanbod'                     => 'Properties',
		'Diensten'                   => 'Services',
		'Nieuws'                     => 'News',
		'Contact'                    => 'Contact',
		'Terug naar het begin'       => 'Back to the start',
		'Team, werkwijze en regio'   => 'Team, approach and region',
		'Wat er nu beschikbaar is'   => 'What is available now',
		'Aanhuur, verhuur en advies' => 'Renting, letting and advice',
		'Naar de inhoud'             => 'Skip to content',
		'Menu openen'                => 'Open menu',
		'Menu sluiten'               => 'Close menu',
		'Menu'                       => 'Menu',
		// Footer
		'Algemeen contact'           => 'General contact',
		'Kom langs'                  => 'Visit us',
		'Algemene voorwaarden'       => 'Terms and conditions',
		'Cookies'                    => 'Cookies',
		// Kaarten en knoppen
		'Lees meer'                  => 'Read more',
		'Bekijk alle'                => 'View all',
		'Bekijk het aanbod'          => 'View available properties',
		'Volledig aanbod'            => 'Full listings',
		'Neem contact op'            => 'Get in touch',
		'Meer over ons'              => 'More about us',
		'Direct contact'             => 'Direct contact',
		'Er zijn nog geen nieuwsberichten.' => 'There is no news yet.',
		'Niet gevonden'              => 'Not found',
		'Terug naar de homepage'     => 'Back to the homepage',
		'Alles'                      => 'All',
		'Koop'                       => 'For sale',
		'Huur'                       => 'To let',
		'Er is op dit moment geen aanbod dat hieraan voldoet.' => 'There are currently no properties that match.',
	);
}

/** Vertaalt een vaste tekst als de Engelse taal actief is; anders blijft het Nederlands. */
function gw_t( $nl ) {
	if ( 'en' !== gw_taal() ) {
		return $nl;
	}
	$v = gw_vertalingen_en();
	return isset( $v[ $nl ] ) ? $v[ $nl ] : $nl;
}

// Ondertitel van de site (Instellingen → Algemeen) in het Engels.
add_filter( 'option_blogdescription', function ( $w ) {
	return 'en' === gw_taal() && 'Bedrijfsmakelaars in de regio Rotterdam' === $w ? 'Commercial real estate agents in the Rotterdam region' : $w;
} );

/** Blokcommentaar met attributen. */
function gw_blok_markup( $slug, array $attrs = array() ) {
	return '<!-- wp:gw/' . $slug . ( $attrs ? ' ' . wp_json_encode( $attrs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) : '' ) . ' /-->';
}

/** De Engelse inhoud van de homepage-blokken. */
function gw_home_blokken_en() {
	return implode( "\n\n", array(
		gw_blok_markup( 'hero', array(
			'regel_voor' => 'Take charge of',
			'accent'     => 'regional',
			'regel_na'   => 'commercial real estate',
			'tekst'      => 'We know the properties in Schiedam, Vlaardingen, Maassluis and the surrounding area. Because we are based here, and because we know the owners.',
			'knop_label' => 'View available properties',
		) ),
		gw_blok_markup( 'aanbod', array( 'tag' => 'Properties', 'kop' => 'What is available now', 'knop_label' => 'Full listings' ) ),
		gw_blok_markup( 'over', array(
			'tag'        => 'About us',
			'kop'        => 'We don\'t just know the streets, we know the property owners.',
			'tekst'      => 'We are a close-knit team of real estate specialists, built on the decades of experience of the Schaub Group. Because we focus exclusively on Schiedam, Vlaardingen, Maassluis and Rotterdam-West, we know exactly what is happening in the region.',
			'stats'      => array(
				array( 'getal' => '35+', 'tekst' => 'Years of experience', 'afbeelding' => 0 ),
				array( 'getal' => '40,000 m²', 'tekst' => 'Successfully brokered', 'afbeelding' => 0 ),
				array( 'getal' => '65%', 'tekst' => 'Off-market deals', 'afbeelding' => 0 ),
			),
			'knop_label' => 'More about us',
			'link_label' => 'Discover our services',
		) ),
		gw_blok_markup( 'diensten', array( 'tag' => 'Services', 'kop' => 'Which side of the door are you on?', 'knop_label' => 'View all services' ) ),
		gw_blok_markup( 'nieuws', array( 'tag' => 'News', 'kop' => 'Latest from the area', 'link_label' => 'View all' ) ),
		gw_blok_markup( 'faq', array( 'tag' => 'FAQ', 'kop' => 'Questions we get asked often' ) ),
		gw_blok_markup( 'cta', array(
			'kop'        => 'Ready to find the right space?',
			'tekst'      => 'Feel free to call before your plan is final. One conversation is usually enough to know whether there is something for you in the region.',
			'knop_label' => 'Get in touch',
		) ),
	) );
}

/** Zoekt een bericht op titel (ongeacht taal). */
function gw_vind_bericht( $type, $titel ) {
	$r = get_posts( array( 'post_type' => $type, 'post_status' => 'any', 'title' => $titel, 'numberposts' => 1, 'suppress_filters' => true ) );
	return $r ? $r[0] : null;
}

/** Maakt een Engelse versie van een Nederlands bericht en koppelt ze. Geeft het nieuwe ID terug. */
function gw_maak_engels( $nl_post, array $velden, array $meta = array() ) {
	if ( ! $nl_post || pll_get_post( $nl_post->ID, 'en' ) ) {
		return 0;
	}
	$id = wp_insert_post( array_merge( array(
		'post_type'   => $nl_post->post_type,
		'post_status' => 'publish',
		'menu_order'  => $nl_post->menu_order,
		'post_date'   => $nl_post->post_date,
	), $velden ) );
	if ( ! $id || is_wp_error( $id ) ) {
		return 0;
	}
	pll_set_post_language( $id, 'en' );
	pll_save_post_translations( array( 'nl' => $nl_post->ID, 'en' => $id ) );
	foreach ( $meta as $k => $w ) {
		update_post_meta( $id, $k, $w );
	}
	$foto = get_post_thumbnail_id( $nl_post->ID );
	if ( $foto ) {
		set_post_thumbnail( $id, $foto );
	}
	return $id;
}

add_action( 'init', function () {
	if ( ! is_admin() || get_option( 'gw_talen_en_v1' ) || ! function_exists( 'pll_languages_list' ) || ! function_exists( 'pll_save_post_translations' ) ) {
		return;
	}
	if ( ! in_array( 'en', (array) pll_languages_list(), true ) ) {
		return;
	}
	update_option( 'gw_talen_en_v1', 1 );

	// Home
	$nl_home = (int) get_option( 'page_on_front' );
	$en_home = $nl_home ? (int) pll_get_post( $nl_home, 'en' ) : 0;
	if ( $en_home ) {
		wp_update_post( array( 'ID' => $en_home, 'post_content' => gw_home_blokken_en() ) );
	}

	// Pagina's: Diensten en Nieuws
	$pagina = static function ( $slug, $titel_en, $slug_en, $inhoud ) {
		$nl = get_page_by_path( $slug );
		return gw_maak_engels( $nl, array( 'post_title' => $titel_en, 'post_name' => $slug_en, 'post_content' => $inhoud ) );
	};
	$pagina( 'diensten', 'Services', 'services', gw_blok_markup( 'diensten', array( 'tag' => 'Services', 'kop' => 'Which side of the door are you on?', 'knop_label' => '' ) ) );
	$pagina( 'nieuws', 'News', 'news', '' );

	// Diensten
	$diensten = array(
		'Aanhuur en aankoop'    => array( 'Renting and buying', 'From search profile to key handover. Also for new-build and off-market properties.', "Clarify needs and requirements\nViewings and negotiation\nContract check and handover" ),
		'Verhuur en verkoop'    => array( 'Letting and selling', 'From valuation to signed contract. We bring your property to the attention of the right party.', "Valuation and strategy\nPresentation and viewings\nNegotiation and closing" ),
		'Advies en beleggingen' => array( 'Advice and investments', 'Independent advice on returns, appraisal and portfolio, based on knowledge of the region.', "Appraisal and market analysis\nPurchase and sale of investments\nRedevelopment advice" ),
	);
	foreach ( $diensten as $nl => $d ) {
		gw_maak_engels( gw_vind_bericht( 'gw_dienst', $nl ), array( 'post_title' => $d[0], 'post_content' => $d[1] ), array( 'gw_punten' => $d[2] ) );
	}

	// Veelgestelde vragen
	$faq = array(
		'Wat voor bedrijfsruimtes bieden jullie aan?'      => array( 'What kinds of commercial property do you offer?', 'Offices, shops, industrial units and hospitality spaces in Schiedam, Vlaardingen, Maassluis and Rotterdam-West.' ),
		'Kan ik een bedrijfsruimte eerst bezichtigen?'     => array( 'Can I view a property first?', 'Yes. Seen a space that looks interesting? Get in touch and we will arrange a suitable time for a viewing together.' ),
		'Welke kosten komen er naast de huurprijs nog bij?' => array( 'What costs are there on top of the rent?', 'Usually service charges, VAT and a deposit or bank guarantee. For each property we list this for you in advance.' ),
		'Hoe werkt het huren van een bedrijfsruimte?'      => array( 'How does renting commercial property work?', 'After a viewing we make a proposal, negotiate the terms and put everything in a lease agreement.' ),
		'Zijn de huurvoorwaarden altijd hetzelfde?'        => array( 'Are the lease terms always the same?', 'No. Term, notice period and indexation differ per property and are often negotiable.' ),
		'Kunnen bedrijfsruimtes worden aangepast?'         => array( 'Can commercial spaces be adapted?', 'Often yes. In consultation with the owner we look at what is possible and who bears which costs.' ),
	);
	foreach ( $faq as $nl => $f ) {
		gw_maak_engels( gw_vind_bericht( 'gw_faq', $nl ), array( 'post_title' => $f[0], 'post_content' => $f[1] ) );
	}

	// Team: functie vertalen
	foreach ( get_posts( array( 'post_type' => 'gw_team', 'post_status' => 'any', 'numberposts' => 50, 'suppress_filters' => true ) ) as $lid ) {
		if ( 'nl' !== pll_get_post_language( $lid->ID ) ) {
			continue;
		}
		$functie = (string) get_post_meta( $lid->ID, 'gw_functie', true );
		gw_maak_engels( $lid, array( 'post_title' => $lid->post_title ), array(
			'gw_functie'  => 'Partner & Makelaar' === $functie ? 'Partner & Broker' : $functie,
			'gw_telefoon' => (string) get_post_meta( $lid->ID, 'gw_telefoon', true ),
			'gw_email'    => (string) get_post_meta( $lid->ID, 'gw_email', true ),
		) );
	}

	// Nieuws (categorie eerst vertalen)
	$cats = array(
		'Tips & advies'       => array( 'Tips & advice', 'tips-advice' ),
		'Klantverhaal'        => array( 'Client story', 'client-story' ),
		'Gebiedsontwikkeling' => array( 'Area development', 'area-development' ),
	);
	$cat_en = array();
	foreach ( $cats as $nl => $c ) {
		$nl_term = get_term_by( 'name', $nl, 'category' );
		if ( ! $nl_term ) {
			continue;
		}
		$nieuw = wp_insert_term( $c[0], 'category', array( 'slug' => $c[1] ) );
		if ( ! is_wp_error( $nieuw ) ) {
			pll_set_term_language( (int) $nieuw['term_id'], 'en' );
			pll_save_term_translations( array( 'nl' => (int) $nl_term->term_id, 'en' => (int) $nieuw['term_id'] ) );
			$cat_en[ $nl ] = (int) $nieuw['term_id'];
		}
	}
	$alinea = static function ( $tekst ) {
		return implode( "\n\n", array_map( static function ( $a ) {
			return "<!-- wp:paragraph -->\n<p>" . esc_html( $a ) . "</p>\n<!-- /wp:paragraph -->";
		}, explode( "\n\n", $tekst ) ) );
	};
	$nieuws = array(
		'Waar let je op bij het huren van een bedrijfsruimte?' => array( 'Tips & advies', 'What to look out for when renting commercial property', 'A practical guide to the key points, from location and accessibility to contract and sustainability.', "Renting commercial property is an important step. With the right preparation you avoid surprises later on.\n\nPay attention to the location and accessibility for customers, employees and suppliers. Look at parking, public transport and the surroundings.\n\nDiscuss the lease terms in advance: term, notice period, indexation and who bears which costs. We are happy to help you set this out clearly." ),
		'Van idee naar groei in Vlaardingen'                  => array( 'Klantverhaal', 'From idea to growth in Vlaardingen', 'How a local entrepreneur found the perfect commercial space and was able to realise their ambitions thanks to its central location.', "What started as an idea grew into a business with a permanent place in Vlaardingen.\n\nThe entrepreneur was looking for a space that could grow along, in a location that was easy to reach for customers. Thanks to our knowledge of the region and the owners, a suitable property was on the table within a few weeks.\n\nThe central location made the difference: short travel times and plenty of visibility." ),
		'Vlaardingen in volle ontwikkeling'                   => array( 'Gebiedsontwikkeling', 'Vlaardingen in full development', 'On the plans, projects and investments that will make Vlaardingen even more attractive to entrepreneurs in the coming years', "Vlaardingen has a number of important developments ahead in the coming years.\n\nNew residential and business areas, improved accessibility and investments in public space make the city more attractive to entrepreneurs.\n\nWhat does this mean for your business premises? We are happy to set out the opportunities for you." ),
		'Duurzaam verhuren: wat levert het op?'               => array( 'Tips & advies', 'Sustainable letting: what does it deliver?', 'Energy label, insulation and running costs: how to make a property more attractive to tenants and future-proof.', "Sustainability is becoming more and more important to tenants, and more and more profitable for landlords.\n\nA better energy label lowers running costs and makes a property more attractive. Laws and regulations are also moving towards stricter requirements.\n\nWe advise you on the measures with the best return." ),
	);
	foreach ( $nieuws as $nl => $n ) {
		$id = gw_maak_engels( gw_vind_bericht( 'post', $nl ), array( 'post_title' => $n[1], 'post_excerpt' => $n[2], 'post_content' => $alinea( $n[3] ) ) );
		if ( $id && isset( $cat_en[ $n[0] ] ) ) {
			wp_set_post_categories( $id, array( $cat_en[ $n[0] ] ) );
		}
	}
	if ( function_exists( 'PLL' ) && PLL() && isset( PLL()->model ) ) {
		PLL()->model->clean_languages_cache();
	}
}, 70 );
