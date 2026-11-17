<?php
/**
 * Collecties (zoals CMS-collecties in Webflow): Diensten, Veelgestelde vragen en Team.
 * Eén keer invoeren, op meerdere pagina's tonen. De blokken Diensten, FAQ en Team lezen hieruit.
 * Menu's staan onder Weergave → Menu's, nieuws onder Nieuws, contactgegevens onder Aanpassen.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', function () {
	$basis = array(
		'public'              => false,
		'publicly_queryable'  => false,
		'show_ui'             => true,
		'show_in_rest'        => false, // klassiek bewerkscherm: titel, samenvatting, foto, volgorde — geen vrije opmaak
		'exclude_from_search' => true,
		'has_archive'         => false,
		'rewrite'             => false,
		'supports'            => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
	);
	$extra = array(
		'attributes'     => 'Volgorde',
		'featured_image' => 'Foto',
		'set_featured_image' => 'Foto kiezen',
		'remove_featured_image' => 'Foto verwijderen',
		'use_featured_image' => 'Gebruik als foto',
	);
	register_post_type( 'gw_dienst', array_merge( $basis, array(
		'labels'     => array_merge( $extra, array( 'name' => 'Diensten', 'singular_name' => 'Dienst', 'add_new_item' => 'Nieuwe dienst', 'edit_item' => 'Dienst bewerken', 'all_items' => 'Alle diensten', 'menu_name' => 'Diensten' ) ),
		'menu_icon'  => 'dashicons-list-view',
		'menu_position' => 21,
	) ) );
	register_post_type( 'gw_faq', array_merge( $basis, array(
		'labels'     => array_merge( $extra, array( 'name' => 'Veelgestelde vragen', 'singular_name' => 'Vraag', 'add_new_item' => 'Nieuwe vraag', 'edit_item' => 'Vraag bewerken', 'all_items' => 'Alle vragen', 'menu_name' => 'Vragen (FAQ)' ) ),
		'menu_icon'  => 'dashicons-editor-help',
		'menu_position' => 22,
		'supports'   => array( 'title', 'editor', 'page-attributes' ),
		'taxonomies' => array( 'gw_faq_cat' ),
	) ) );
	register_post_type( 'gw_team', array_merge( $basis, array(
		'labels'     => array_merge( $extra, array( 'name' => 'Team', 'singular_name' => 'Teamlid', 'add_new_item' => 'Nieuw teamlid', 'edit_item' => 'Teamlid bewerken', 'all_items' => 'Alle teamleden', 'menu_name' => 'Team' ) ),
		'supports'   => array( 'title', 'thumbnail', 'page-attributes' ),
		'menu_icon'  => 'dashicons-groups',
		'menu_position' => 23,
	) ) );
	register_taxonomy( 'gw_faq_cat', 'gw_faq', array(
		'labels'            => array( 'name' => 'Categorieën', 'singular_name' => 'Categorie', 'menu_name' => 'Categorieën' ),
		'public'            => false,
		'show_ui'           => true,
		'show_admin_column' => true,
		'hierarchical'      => true,
		'rewrite'           => false,
	) );
} );

add_filter( 'gettext', function ( $tekst, $orig ) {
	global $typenow;
	if ( 'Order' === $orig && 0 === strpos( (string) $typenow, 'gw_' ) ) {
		return 'Volgorde (laagste getal eerst)';
	}
	return $tekst;
}, 10, 2 );
add_filter( 'enter_title_here', function ( $t, $post ) {
	$map = array( 'gw_dienst' => 'Naam van de dienst', 'gw_faq' => 'De vraag', 'gw_team' => 'Naam' );
	return $map[ $post->post_type ] ?? $t;
}, 10, 2 );

/** Kop + uitleg boven het tekstveld. */
add_action( 'edit_form_after_title', function ( $post ) {
	$uitleg = array(
		'gw_dienst' => array( 'Omschrijving', 'Korte tekst die bij deze dienst op de website staat. Vet, cursief en een link zijn mogelijk; de opmaak zelf (lettertype, kleuren) staat vast.' ),
		'gw_faq'    => array( 'Antwoord', 'Het antwoord op de vraag. Vet, cursief en een link zijn mogelijk; de opmaak zelf staat vast.' ),
	);
	if ( isset( $uitleg[ $post->post_type ] ) ) {
		printf( '<h2 class="gw-veldkop">%s</h2><p class="description">%s</p>', esc_html( $uitleg[ $post->post_type ][0] ), esc_html( $uitleg[ $post->post_type ][1] ) );
	}
} );

/** Tekstveld: kleine werkbalk, geen mediaknop, plakken zonder opmaak (zo blijft het ontwerp heel). */
function gw_collectie_scherm() {
	$scherm = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	return $scherm && in_array( $scherm->post_type, array( 'gw_dienst', 'gw_faq' ), true );
}
add_filter( 'wp_editor_settings', function ( $s, $id ) {
	if ( 'content' === $id && gw_collectie_scherm() ) {
		$s['media_buttons'] = false;
		$s['teeny']         = true;
		$s['textarea_rows'] = 8;
		$s['quicktags']     = array( 'buttons' => 'strong,em,link' );
	}
	return $s;
}, 10, 2 );
add_filter( 'teeny_mce_buttons', function ( $b ) {
	return gw_collectie_scherm() ? array( 'bold', 'italic', 'link', 'unlink', 'undo', 'redo' ) : $b;
} );
add_filter( 'tiny_mce_before_init', function ( $i ) {
	if ( gw_collectie_scherm() ) {
		$i['paste_as_text'] = true;
		$i['block_formats'] = 'Alinea=p';
	}
	return $i;
} );

/** Tekst uit het veld, veilig en met alleen de toegestane opmaak (vet, cursief, link, alinea). */
function gw_rich( $p ) {
	$t = '' !== trim( (string) $p->post_content ) ? $p->post_content : $p->post_excerpt;
	$ok = array( 'a' => array( 'href' => true, 'target' => true, 'rel' => true ), 'strong' => array(), 'b' => array(), 'em' => array(), 'i' => array(), 'br' => array() );
	return wpautop( wp_kses( (string) $t, $ok, array( 'http', 'https', 'mailto', 'tel' ) ) );
}

// ---------------------------------------------------------------------------
// Extra velden (meta boxes): beveiligd met nonce en rechtencontrole.
// ---------------------------------------------------------------------------

function gw_meta_velden() {
	return array(
		'gw_dienst' => array( 'Opsomming', 'Korte punten onder de omschrijving, bijvoorbeeld de stappen van de dienst.', array( 'gw_punten' => array( 'Punten', 'rijen', '' ) ) ),
		'gw_team'   => array( 'Gegevens', 'Wat bij de naam op de website staat. Leeg laten wat je niet wilt tonen.', array(
			'gw_functie'  => array( 'Functie', 'text', 'Bijvoorbeeld: Partner & Makelaar' ),
			'gw_telefoon' => array( 'Telefoon', 'text', 'Bijvoorbeeld: (010) 422 32 20' ),
			'gw_email'    => array( 'E-mail', 'email', 'Bijvoorbeeld: naam@bedrijf.nl' ),
		) ),
	);
}

add_action( 'add_meta_boxes', function () {
	foreach ( gw_meta_velden() as $type => $def ) {
		add_meta_box( 'gw_velden', $def[0], function ( $post ) use ( $def ) {
			wp_nonce_field( 'gw_meta_' . $post->post_type, 'gw_meta_nonce' );
			echo '<p class="description">' . esc_html( $def[1] ) . '</p>';
			foreach ( $def[2] as $key => $veld ) {
				$w = (string) get_post_meta( $post->ID, $key, true );
				if ( 'rijen' === $veld[1] ) {
					// Rijen worden door admin-js opgebouwd uit dit veld (één punt per regel); zonder JS blijft het een tekstvak.
					echo '<textarea class="widefat gw-rijen" rows="5" id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '">' . esc_textarea( $w ) . '</textarea>';
					continue;
				}
				echo '<p><label for="' . esc_attr( $key ) . '"><strong>' . esc_html( $veld[0] ) . '</strong></label><br>';
				echo '<input class="widefat" type="' . ( 'email' === $veld[1] ? 'email' : 'text' ) . '" id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" value="' . esc_attr( $w ) . '" placeholder="' . esc_attr( $veld[2] ) . '"></p>';
			}
		}, $type, 'normal', 'default' );
	}
} );

add_action( 'admin_enqueue_scripts', function ( $hook ) {
	$scherm = get_current_screen();
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) || ! $scherm || 0 !== strpos( (string) $scherm->post_type, 'gw_' ) ) {
		return;
	}
	$dir = get_template_directory();
	$uri = get_template_directory_uri();
	wp_enqueue_script( 'gw-collecties', $uri . '/assets/js/collecties-admin.js', array(), filemtime( $dir . '/assets/js/collecties-admin.js' ), true );
	wp_enqueue_style( 'gw-collecties', $uri . '/assets/css/collecties-admin.css', array(), filemtime( $dir . '/assets/css/collecties-admin.css' ) );
} );

add_action( 'save_post', function ( $post_id, $post ) {
	$velden = gw_meta_velden()[ $post->post_type ][2] ?? null;
	if ( ! $velden || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( ! isset( $_POST['gw_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['gw_meta_nonce'] ) ), 'gw_meta_' . $post->post_type ) ) {
		return;
	}
	foreach ( $velden as $key => $def ) {
		$w = isset( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- hieronder per type opgeschoond
		$w = 'rijen' === $def[1] ? sanitize_textarea_field( $w ) : ( 'email' === $def[1] ? sanitize_email( $w ) : sanitize_text_field( $w ) );
		update_post_meta( $post_id, $key, $w );
	}
}, 10, 2 );

/** Items uit een collectie, op volgorde (Volgorde-veld, dan datum). */
function gw_collectie( $type, array $args = array() ) {
	return get_posts( array_merge( array(
		'post_type'     => $type,
		'post_status'   => 'publish',
		'numberposts'   => 50,
		'orderby'       => array( 'menu_order' => 'ASC', 'date' => 'ASC' ),
		'no_found_rows' => true,
	), $args ) );
}

/** Melding voor redacteuren als een collectie nog leeg is (bezoekers zien niets). */
function gw_leeg_melding( $naam, $menu ) {
	if ( current_user_can( 'edit_posts' ) ) {
		echo '<p class="text-size-medium">Nog geen ' . esc_html( $naam ) . '. Voeg ze toe onder “' . esc_html( $menu ) . '” in het beheermenu.</p>';
	}
}

// ---------------------------------------------------------------------------
// Startinhoud: de huidige diensten en vragen als echte items (eenmalig).
// ---------------------------------------------------------------------------

add_action( 'init', function () {
	if ( get_option( 'gw_collecties_geseed' ) ) {
		return;
	}
	update_option( 'gw_collecties_geseed', 1 );
	$diensten = array(
		array( 'Aanhuur en aankoop', 'Van zoekprofiel tot sleuteloverdracht. Ook voor nieuwbouw en off-market panden.', "Behoefte en eisen scherpstellen\nBezichtigen en onderhandelen\nContractcheck en oplevering" ),
		array( 'Verhuur en verkoop', 'Van waardebepaling tot getekend contract. Wij brengen je pand bij de juiste partij onder de aandacht.', "Waardebepaling en strategie\nPresentatie en bezichtigingen\nOnderhandelen en afronden" ),
		array( 'Advies en beleggingen', 'Onafhankelijk advies over rendement, taxatie en portefeuille, gebaseerd op kennis van de regio.', "Taxatie en marktanalyse\nAan- en verkoop van beleggingen\nAdvies over herontwikkeling" ),
	);
	foreach ( $diensten as $i => $d ) {
		wp_insert_post( array( 'post_type' => 'gw_dienst', 'post_status' => 'publish', 'post_title' => $d[0], 'post_content' => $d[1], 'menu_order' => $i, 'meta_input' => array( 'gw_punten' => $d[2] ) ) );
	}
	$faq = array(
		array( 'Wat voor bedrijfsruimtes bieden jullie aan?', 'Kantoren, winkels, bedrijfshallen en horecaruimtes in Schiedam, Vlaardingen, Maassluis en Rotterdam-West.' ),
		array( 'Kan ik een bedrijfsruimte eerst bezichtigen?', 'Ja. Heb je een ruimte gezien die interessant lijkt? Neem contact met ons op, dan plannen we samen een geschikt moment voor een bezichtiging.' ),
		array( 'Welke kosten komen er naast de huurprijs nog bij?', 'Meestal servicekosten, btw en een waarborgsom of bankgarantie. Per pand zetten we dit vooraf voor je op een rij.' ),
		array( 'Hoe werkt het huren van een bedrijfsruimte?', 'Na een bezichtiging doen we een voorstel, onderhandelen we over de voorwaarden en leggen we alles vast in een huurovereenkomst.' ),
		array( 'Zijn de huurvoorwaarden altijd hetzelfde?', 'Nee. Looptijd, opzegtermijn en indexering verschillen per pand en zijn vaak bespreekbaar.' ),
		array( 'Kunnen bedrijfsruimtes worden aangepast?', 'Vaak wel. In overleg met de eigenaar kijken we wat er mogelijk is en wie welke kosten draagt.' ),
	);
	foreach ( $faq as $i => $f ) {
		wp_insert_post( array( 'post_type' => 'gw_faq', 'post_status' => 'publish', 'post_title' => $f[0], 'post_content' => $f[1], 'menu_order' => $i ) );
	}
}, 30 );

// ---------------------------------------------------------------------------
// Beheermenu opruimen: "Berichten" heet Nieuws, reacties worden niet gebruikt.
// ---------------------------------------------------------------------------

add_filter( 'post_type_labels_post', function ( $l ) {
	$l->name = 'Nieuws';
	$l->menu_name = 'Nieuws';
	$l->all_items = 'Alle nieuwsberichten';
	$l->add_new = 'Nieuw bericht';
	return $l;
} );
add_action( 'admin_menu', function () {
	remove_menu_page( 'edit-comments.php' );
}, 99 );

/** Eenmalig: teksten die eerder in "samenvatting" stonden verhuizen naar het tekstveld. */
add_action( 'admin_init', function () {
	if ( get_option( 'gw_excerpt_gemigreerd' ) ) {
		return;
	}
	update_option( 'gw_excerpt_gemigreerd', 1 );
	foreach ( array( 'gw_dienst', 'gw_faq' ) as $type ) {
		foreach ( gw_collectie( $type, array( 'post_status' => 'any', 'numberposts' => 200 ) ) as $p ) {
			if ( '' === trim( $p->post_content ) && '' !== trim( $p->post_excerpt ) ) {
				wp_update_post( array( 'ID' => $p->ID, 'post_content' => $p->post_excerpt, 'post_excerpt' => '' ) );
			}
		}
	}
} );

/** Eenmalig: de huidige contactpersoon als teamlid, en meteen gekozen als contactpersoon van de site. */
add_action( 'init', function () {
	if ( get_option( 'gw_team_geseed' ) ) {
		return;
	}
	update_option( 'gw_team_geseed', 1 );
	if ( gw_collectie( 'gw_team' ) || get_theme_mod( 'gw_contactpersoon' ) ) {
		return;
	}
	$id = wp_insert_post( array(
		'post_type'   => 'gw_team',
		'post_status' => 'publish',
		'post_title'  => (string) gw_mod( 'contact_naam' ),
		'meta_input'  => array(
			'gw_functie'  => (string) gw_mod( 'contact_rol' ),
			'gw_telefoon' => (string) gw_mod( 'telefoon' ),
			'gw_email'    => (string) gw_mod( 'email' ),
		),
	) );
	if ( $id && ! is_wp_error( $id ) ) {
		set_theme_mod( 'gw_contactpersoon', $id );
	}
}, 35 );

// ---------------------------------------------------------------------------
// Voorbeeldnieuws: de vier berichten uit het ontwerp, als echte berichten met uitgelichte foto.
// ---------------------------------------------------------------------------

/** Kopieert een afbeelding uit het thema naar de mediabibliotheek. */
function gw_importeer_afbeelding( $bestand, $post_id, $titel ) {
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';
	$bron = get_theme_file_path( 'assets/home/' . $bestand );
	if ( ! is_readable( $bron ) ) {
		return 0;
	}
	$upload = wp_upload_bits( $bestand, null, (string) file_get_contents( $bron ) );
	if ( ! empty( $upload['error'] ) ) {
		return 0;
	}
	$id = wp_insert_attachment( array( 'post_mime_type' => 'image/webp', 'post_title' => $titel, 'post_status' => 'inherit' ), $upload['file'], $post_id );
	if ( ! $id || is_wp_error( $id ) ) {
		return 0;
	}
	wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $upload['file'] ) );
	return (int) $id;
}

add_action( 'init', function () {
	if ( get_option( 'gw_nieuws_voorbeeld_v2' ) ) {
		return;
	}
	update_option( 'gw_nieuws_voorbeeld_v2', 1 );
	// Alleen op een site zonder eigen nieuws; het standaardbericht van WordPress ("Hello world!") verdwijnt.
	$hallo    = get_page_by_path( 'hello-world', OBJECT, 'post' );
	$bestaand = get_posts( array( 'post_type' => 'post', 'post_status' => 'any', 'numberposts' => 5, 'fields' => 'ids', 'post__not_in' => $hallo ? array( $hallo->ID ) : array() ) );
	if ( $bestaand ) {
		return;
	}
	if ( $hallo ) {
		wp_delete_post( $hallo->ID, true );
	}
	$berichten = array(
		array( 'Tips & advies', '2026-09-15 09:00:00', 'Waar let je op bij het huren van een bedrijfsruimte?', 'Een praktische gids met de belangrijkste aandachtspunten, van locatie en bereikbaarheid tot contact en verduurzaming.', 'nieuws-1.webp', "Een bedrijfsruimte huren is een belangrijke stap. Met de juiste voorbereiding voorkom je verrassingen achteraf.\n\nLet op de locatie en bereikbaarheid voor klanten, medewerkers en leveranciers. Kijk naar parkeermogelijkheden, openbaar vervoer en de omgeving.\n\nBespreek de huurvoorwaarden vooraf: looptijd, opzegtermijn, indexering en wie welke kosten draagt. Wij helpen je graag om dit helder op een rij te zetten." ),
		array( 'Klantverhaal', '2026-09-08 09:00:00', 'Van idee naar groei in Vlaardingen', 'Hoe een lokale ondernemer de perfecte bedrijfsruimte vond en zijn ambities kon waarmaken dankzij de centrale ligging.', 'nieuws-2.webp', "Wat begon als een idee, groeide uit tot een bedrijf met een vaste plek in Vlaardingen.\n\nDe ondernemer zocht een ruimte die kon meegroeien, op een locatie die goed bereikbaar was voor klanten. Dankzij onze kennis van de regio en de eigenaren lag er binnen enkele weken een passend aanbod.\n\nDe centrale ligging maakte het verschil: korte reistijden en volop zichtbaarheid." ),
		array( 'Gebiedsontwikkeling', '2026-08-26 09:00:00', 'Vlaardingen in volle ontwikkeling', 'Over de plannen, projecten en investeringen die Vlaardingen de komende jaren nog aantrekkelijker maken voor ondernemers', 'nieuws-3.webp', "Vlaardingen staat de komende jaren een aantal belangrijke ontwikkelingen te wachten.\n\nNieuwe woon- en werkgebieden, verbeterde bereikbaarheid en investeringen in de openbare ruimte maken de stad aantrekkelijker voor ondernemers.\n\nWat betekent dit voor je bedrijfshuisvesting? We zetten de kansen graag voor je op een rij." ),
		array( 'Tips & advies', '2026-08-12 09:00:00', 'Duurzaam verhuren: wat levert het op?', 'Energielabel, isolatie en gebruikskosten: zo maak je een pand aantrekkelijker voor huurders en toekomstbestendig.', 'nieuws-4.webp', "Duurzaamheid wordt voor huurders steeds belangrijker, en voor verhuurders steeds lonender.\n\nEen beter energielabel verlaagt de gebruikskosten en maakt een pand aantrekkelijker. Ook wet- en regelgeving schuift op richting strengere eisen.\n\nWe adviseren je over de maatregelen met het beste rendement." ),
	);
	foreach ( $berichten as $b ) {
		$cat = term_exists( $b[0], 'category' ) ?: wp_insert_term( $b[0], 'category' );
		$inhoud = implode( "\n\n", array_map( static function ( $alinea ) {
			return "<!-- wp:paragraph -->\n<p>" . esc_html( $alinea ) . "</p>\n<!-- /wp:paragraph -->";
		}, explode( "\n\n", $b[5] ) ) );
		$id = wp_insert_post( array(
			'post_type'     => 'post',
			'post_status'   => 'publish',
			'post_title'    => $b[2],
			'post_excerpt'  => $b[3],
			'post_content'  => $inhoud,
			'post_date'     => $b[1],
			'post_category' => is_array( $cat ) ? array( (int) $cat['term_id'] ) : array(),
		) );
		if ( $id && ! is_wp_error( $id ) ) {
			$foto = gw_importeer_afbeelding( $b[4], $id, $b[2] );
			if ( $foto ) {
				set_post_thumbnail( $id, $foto );
			}
		}
	}
}, 45 );
