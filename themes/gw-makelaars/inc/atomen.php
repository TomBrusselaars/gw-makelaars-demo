<?php
/**
 * Atomen en moleculen — de kleinste bouwstenen van het ontwerp, als PHP-functies.
 *
 * Elk onderdeel dat meer dan één keer voorkomt (tag, knop, pijl, tekstlink,
 * contactlijst, sectiekop, afbeelding) staat hier één keer. Blokken, header en
 * footer roepen deze functies aan; de bijbehorende CSS staat in home.css onder
 * "Atomen" en "Moleculen". Zo kan niets uit de pas lopen.
 */

defined( 'ABSPATH' ) || exit;

/** URL van een bestand uit assets/home/ (werkt ook vanuit een child-thema). */
function gw_asset_url( $bestand ) {
	$bestand = ltrim( $bestand, '/' );
	// SVG's in beeld-tags kunnen geen CSS-variabelen lezen. Zijn de merkkleuren in de
	// Stijlgids aangepast, dan krijgt het icoon die kleuren mee (via /?gw_svg=naam).
	if ( '.svg' === substr( $bestand, -4 ) ) {
		$svg = gw_svg_gekleurd( $bestand );
		if ( '' !== $svg ) {
			return add_query_arg( array( 'gw_svg' => $bestand, 'v' => substr( md5( $svg ), 0, 8 ) ), home_url( '/' ) );
		}
	}
	return get_theme_file_uri( 'assets/home/' . $bestand );
}

/**
 * Inhoud van een SVG uit assets/home/ met de ingestelde merkkleuren (olijf, zand, accent),
 * of '' als er niets is aangepast en het bestand dus ongewijzigd gebruikt kan worden.
 */
function gw_svg_gekleurd( $bestand ) {
	static $cache = array();
	if ( isset( $cache[ $bestand ] ) ) {
		return $cache[ $bestand ];
	}
	$cache[ $bestand ] = '';
	$opt = defined( 'GW_TOKENS_OPTIE' ) ? get_option( GW_TOKENS_OPTIE, array() ) : array();
	$pad = get_theme_file_path( 'assets/home/' . $bestand );
	if ( ! is_array( $opt ) || ! $opt || ! is_readable( $pad ) ) {
		return '';
	}
	$oud = (string) file_get_contents( $pad );
	$svg = $oud;
	foreach ( array( '#6E5A0E' => 'olive', '#E5E1D2' => 'sand', '#80DBE9' => 'accent', '#1A1916' => 'menu-close-color', '#3A3C2A' => 'menu-panel-line' ) as $hex => $token ) {
		$nieuw = isset( $opt[ $token ] ) ? sanitize_hex_color( $opt[ $token ] ) : '';
		if ( $nieuw ) {
			$svg = str_ireplace( $hex, $nieuw, $svg );
		}
	}
	if ( $svg !== $oud ) {
		$cache[ $bestand ] = $svg;
	}
	return $cache[ $bestand ];
}

/** Attributen voor binnenkomst-animatie: data-reveal, optioneel "door", optioneel vertraging. */
function gw_reveal( $i = 0, $soort = '' ) {
	return ' data-reveal' . ( $soort ? '="' . esc_attr( $soort ) . '"' : '' ) . ( $i ? ' style="--i:' . (int) $i . '"' : '' );
}

/** id-attribuut voor een sectie (anker). */
function gw_anker( array $a ) {
	return ! empty( $a['anker'] ) ? ' id="' . esc_attr( $a['anker'] ) . '"' : '';
}

/**
 * Afbeelding uit de mediabibliotheek (met srcset en alt-tekst), of — zolang de
 * klant niets koos — de ontwerpfoto uit het thema.
 *
 * @return string Veilige <img>-tag.
 */
function gw_img( $id, $standaard, $klasse = '', array $opties = array() ) {
	$id    = absint( $id );
	$maat  = $opties['maat'] ?? 'large';
	$laden = $opties['laden'] ?? 'lazy';
	if ( $id && wp_attachment_is_image( $id ) ) {
		$attr = array( 'class' => trim( $klasse ), 'loading' => $laden );
		if ( 'eager' === $laden && ! empty( $opties['prioriteit'] ) ) {
			$attr['fetchpriority'] = 'high';
		}
		if ( ! empty( $opties['sizes'] ) ) {
			$attr['sizes'] = $opties['sizes'];
		}
		$html = wp_get_attachment_image( $id, $maat, false, $attr );
		if ( $html ) {
			return $html;
		}
	}
	return sprintf(
		'<img class="%s is-default" src="%s" alt="%s" loading="%s"%s>',
		esc_attr( trim( $klasse ) ),
		esc_url( gw_asset_url( $standaard ) ),
		esc_attr( $opties['alt'] ?? '' ),
		esc_attr( $laden ),
		'eager' === $laden && ! empty( $opties['prioriteit'] ) ? ' fetchpriority="high"' : ''
	);
}

/** Ontwerpfoto per item: "stat-%d.webp" wordt stat-1.webp, stat-2.webp, ... */
function gw_standaard_beeld( array $veld, $i ) {
	$s = (string) ( $veld['standaard_beeld'] ?? '' );
	return false !== strpos( $s, '%d' ) ? sprintf( $s, min( $i + 1, (int) ( $veld['standaard_max'] ?? 3 ) ) ) : $s;
}

// ---------------------------------------------------------------------------
// Atomen
// ---------------------------------------------------------------------------

/** Label met bolletje (boven een kop). */
function gw_tag( $label, $licht = false ) {
	if ( '' === (string) $label ) {
		return;
	}
	printf(
		'<div class="tag%s"><img src="%s" width="10" height="8" alt=""><span>%s</span></div>',
		$licht ? ' is-light' : '',
		esc_url( gw_asset_url( $licht ? 'bullet-light.svg' : 'bullet.svg' ) ),
		esc_html( $label )
	);
}

/**
 * Pijl-knop. Eén element: de achtergrond verandert bij hover/open, de pijl
 * erin schuift mee. Er ligt dus niets óver iets anders.
 *
 * @param bool $verticaal Wijst omlaag (open: omhoog), voor accordeons.
 * @param bool $gevuld    Altijd gevuld, niet alleen bij hover.
 */
function gw_pijl( $verticaal = false, $gevuld = false ) {
	printf(
		'<span class="arrow-toggle%s%s" aria-hidden="true"><svg class="arrow-toggle_icon" viewBox="0 0 60.6976 39.6221" focusable="false"><path d="M22.7616 19.7174H38.7783M32.5502 25.946L38.7786 19.7173 32.5502 13.4886" fill="none" stroke="currentColor" stroke-width="1.16269" stroke-linecap="square"/></svg></span>',
		$verticaal ? ' is-vertical' : '',
		$gevuld ? ' is-filled' : ''
	);
}

/** Knop met de schuine hoek uit het ontwerp. */
function gw_knop( $label, $url, $reveal = '' ) {
	if ( '' === (string) $label ) {
		return;
	}
	printf( '<a class="button" href="%s"%s>%s</a>', esc_url( $url ?: '#' ), $reveal, esc_html( $label ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- $reveal komt uit gw_reveal().
}

/** Inhoud van een tekstlink: label + pijlvakje. */
function gw_tekstlink_inhoud( $label, $licht = false ) {
	printf(
		'<span>%s</span><i class="text-link_icon"><img src="%s" alt=""></i>',
		esc_html( $label ),
		esc_url( gw_asset_url( $licht ? 'arrow-small.svg' : 'arrow-small-light.svg' ) )
	);
}

/** Tekstlink met onderstreping die inschuift en een pijl die doorschiet. */
function gw_tekstlink( $label, $url, $licht = false ) {
	if ( '' === (string) $label ) {
		return;
	}
	printf( '<a class="text-link%s" href="%s">', $licht ? ' is-light' : '', esc_url( $url ?: '#' ) );
	gw_tekstlink_inhoud( $label, $licht );
	echo '</a>';
}

// ---------------------------------------------------------------------------
// Moleculen
// ---------------------------------------------------------------------------

/**
 * Sectiekop: tag + kop (+ intro).
 *
 * @param array $o midden, licht (witte tekst), niveau (h1/h2), klasse (extra class op de kop), tekst.
 */
function gw_sectiekop( $tag, $kop, array $o = array() ) {
	$niveau = $o['niveau'] ?? 'h2';
	if ( ! in_array( $niveau, array( 'h1', 'h2' ), true ) ) {
		$niveau = 'h2';
	}
	printf(
		'<div class="section-heading%s%s"%s>',
		! empty( $o['midden'] ) ? ' is-center' : '',
		! empty( $o['licht'] ) ? ' text-color-white' : '',
		gw_reveal() // phpcs:ignore WordPress.Security.EscapeOutput
	);
	gw_tag( $tag, ! empty( $o['licht'] ) );
	if ( '' !== (string) $kop ) {
		printf( '<%1$s class="heading-style-h2%2$s">%3$s</%1$s>', $niveau, ! empty( $o['klasse'] ) ? ' ' . esc_attr( $o['klasse'] ) : '', esc_html( $kop ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- $niveau is gecontroleerd.
	}
	if ( ! empty( $o['tekst'] ) ) {
		printf( '<p class="text-size-medium max-width-large">%s</p>', esc_html( $o['tekst'] ) );
	}
	echo '</div>';
}

/**
 * De gekozen contactpersoon (Weergave → Aanpassen → Contactpersoon, een teamlid uit Team).
 * Alles komt uit dat teamlid; wat daar ontbreekt valt terug op de algemene gegevens.
 */
function gw_contactpersoon_post() {
	$id = absint( get_theme_mod( 'gw_contactpersoon' ) );
	if ( $id && function_exists( 'pll_get_post' ) && pll_get_post( $id ) ) {
		$id = (int) pll_get_post( $id ); // vertaalde versie van het teamlid in de huidige taal
	}
	$p  = $id ? get_post( $id ) : null;
	return $p && 'gw_team' === $p->post_type && 'publish' === $p->post_status ? $p : null;
}

function gw_contact() {
	$p       = gw_contactpersoon_post();
	$alg_tel = (string) gw_mod( 'telefoon' );
	$alg_mail = (string) gw_mod( 'email' );
	$naam    = $p ? get_the_title( $p ) : (string) gw_mod( 'contact_naam' );
	$rol     = $p ? (string) get_post_meta( $p->ID, 'gw_functie', true ) : '';
	$tel     = $p ? (string) get_post_meta( $p->ID, 'gw_telefoon', true ) : '';
	$mail    = $p ? (string) get_post_meta( $p->ID, 'gw_email', true ) : '';
	$foto    = $p ? (int) get_post_thumbnail_id( $p ) : 0;
	$tel     = $tel ?: $alg_tel;
	$mail    = is_email( $mail ) ? $mail : $alg_mail;
	return array(
		'naam'     => $naam,
		'voornaam' => (string) strtok( $naam, ' ' ),
		'rol'      => $rol ?: (string) gw_mod( 'contact_rol' ),
		'tel'      => $tel,
		'telhref'  => 'tel:' . preg_replace( '/[^\d+]/', '', $tel ),
		'email'    => $mail,
		'foto'     => $foto ?: absint( get_theme_mod( 'gw_contact_foto' ) ),
		'alg_tel'  => $alg_tel,
		'alg_email' => $alg_mail,
		'adres1'   => (string) gw_mod( 'adres_regel1' ),
		'adres2'   => (string) gw_mod( 'adres_regel2' ),
	);
}

/**
 * Lijst met telefoon en e-mail.
 *
 * @param bool $algemeen true in footer en menu: toont de algemene gegevens, tenzij onder Aanpassen
 *                       "Footer en menu volgen de contactpersoon" aanstaat.
 */
function gw_contactlijst( $algemeen = false ) {
	$c   = gw_contact();
	$tel = $c['tel'];
	$mail = $c['email'];
	if ( $algemeen && ! get_theme_mod( 'gw_footer_volgt_persoon', 1 ) ) {
		$tel  = $c['alg_tel'];
		$mail = $c['alg_email'];
	}
	echo '<div class="contact-list">';
	if ( $tel ) {
		printf( '<a class="contact-list_item" href="%s"><i class="icon-square"><img src="%s" alt=""></i>%s</a>', esc_url( 'tel:' . preg_replace( '/[^\d+]/', '', $tel ) ), esc_url( gw_asset_url( 'phone.svg' ) ), esc_html( $tel ) );
	}
	if ( is_email( $mail ) ) {
		printf( '<a class="contact-list_item" href="%s"><i class="icon-square"><img src="%s" alt=""></i>%s</a>', esc_url( 'mailto:' . $mail ), esc_url( gw_asset_url( 'mail.svg' ) ), esc_html( $mail ) );
	}
	echo '</div>';
}

/** Adresregel met gebouw-icoon. */
function gw_adresregel() {
	$c = gw_contact();
	printf(
		'<span class="contact-list_item is-top"><i class="icon-square"><img src="%s" alt=""></i><span>%s<br>%s</span></span>',
		esc_url( gw_asset_url( 'building.svg' ) ),
		esc_html( $c['adres1'] ),
		esc_html( $c['adres2'] )
	);
}

/** Menu-items uit Weergave → Menu's; zonder menu de standaardlijst. Elk item: [label, url, ondertitel]. */
function gw_menu_items( $locatie, array $standaard ) {
	$locaties = get_nav_menu_locations();
	$items    = ! empty( $locaties[ $locatie ] ) ? wp_get_nav_menu_items( $locaties[ $locatie ] ) : false;
	if ( ! $items ) {
		return $standaard;
	}
	$uit = array();
	foreach ( $items as $it ) {
		if ( ! (int) $it->menu_item_parent ) {
			$uit[] = array( $it->title, $it->url, $it->description );
		}
	}
	return $uit ?: $standaard;
}

/**
 * Aanbodkaart (Realworks-object). Gebruikt op de homepage, het aanbodoverzicht en elders.
 *
 * @param array $o    Genormaliseerd object (yc_rw_object()).
 * @param array $opt  soort (data-aanbod, voor het filter), toon (false = verborgen), i (volgorde voor animatie).
 */
function gw_aanbodkaart( array $o, $url, array $opt = array() ) {
	$foto    = $o['media']['hoofdfoto'] ?? null;
	$functie = $o['functies'][0]['label'] ?? 'Bedrijfsruimte';
	$toon    = $opt['toon'] ?? true;
	printf(
		'<a class="aanbod-card" href="%s" data-aanbod="%s"%s%s>',
		esc_url( $url ),
		esc_attr( $opt['soort'] ?? '' ),
		gw_reveal( ( $opt['i'] ?? 0 ) % 3 ), // phpcs:ignore WordPress.Security.EscapeOutput
		$toon ? '' : ' hidden'
	);
	if ( $foto ) {
		printf( '<img class="aanbod-card_image" loading="lazy" src="%s" alt="%s">', esc_url( yc_rw_media_src( $foto['urlGroot'] ?? $foto['url'] ) ), esc_attr( $o['adres']['straatNr'] ) );
	}
	?>
	<span class="aanbod-card_panel">
		<span class="aanbod-card_summary">
			<span class="aanbod-card_title"><?php echo esc_html( $o['adres']['straatNr'] ); ?></span>
			<span class="aanbod-card_price"><?php echo esc_html( $o['prijs']['label'] ); ?></span>
		</span>
		<span class="aanbod-card_details" aria-hidden="true">
			<span class="aanbod-card_detail"><small><?php echo esc_html( $functie ); ?></small><b><?php echo $o['oppervlakte'] ? esc_html( gw_getal( $o['oppervlakte'] ) ) . 'm²' : '—'; ?></b></span>
			<span class="aanbod-card_detail"><small>Status</small><b><?php echo esc_html( $o['statusLabel'] ); ?></b></span>
			<span class="aanbod-card_detail"><small><?php echo esc_html( $functie ); ?> in</small><b><?php echo esc_html( $o['adres']['plaats'] ); ?></b></span>
		</span>
		<?php gw_pijl( false, true ); ?>
	</span>
	</a>
	<?php
}

/**
 * Taalkeuze in de header. Met de plug-in Polylang (gratis) wordt dit een uitklapmenu met de
 * ingestelde talen; zonder meertaligheid blijft het een vaste weergave van de taal.
 * Aan/uit te zetten onder Weergave → Aanpassen → GW makelaars.
 */
function gw_taalkeuze( $donker = false ) {
	if ( ! get_theme_mod( 'gw_taalkeuze', 1 ) ) {
		return;
	}
	$klasse = 'nav_lang' . ( $donker ? ' is-dark' : '' );
	$pijl   = '<img src="' . esc_url( gw_asset_url( 'chevron.svg' ) ) . '" width="18" height="10" alt="">';
	$talen  = function_exists( 'pll_the_languages' ) ? pll_the_languages( array( 'raw' => 1, 'hide_if_empty' => 0, 'hide_if_no_translation' => 0 ) ) : array();
	if ( ! is_array( $talen ) || count( $talen ) < 2 ) {
		printf( '<span class="%s">%s %s</span>', esc_attr( $klasse ), esc_html( strtoupper( substr( get_locale(), 0, 2 ) ) ), $pijl ); // phpcs:ignore WordPress.Security.EscapeOutput
		return;
	}
	$huidig = '';
	foreach ( $talen as $t ) {
		if ( ! empty( $t['current_lang'] ) ) {
			$huidig = $t['slug'];
		}
	}
	printf( '<div class="%s is-menu"><button class="nav_lang-knop" type="button" aria-haspopup="true">%s %s</button><ul class="nav_lang-lijst">', esc_attr( $klasse ), esc_html( strtoupper( $huidig ) ), $pijl ); // phpcs:ignore WordPress.Security.EscapeOutput
	foreach ( $talen as $t ) {
		printf( '<li><a href="%s" lang="%s" hreflang="%s">%s</a></li>', esc_url( $t['url'] ), esc_attr( $t['locale'] ), esc_attr( $t['slug'] ), esc_html( $t['name'] ) );
	}
	echo '</ul></div>';
}

/** Levert een SVG uit assets/home/ met de ingestelde merkkleuren: /?gw_svg=arrow.svg. Alleen bestaande bestanden in die map. */
add_action( 'template_redirect', function () {
	if ( ! isset( $_GET['gw_svg'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		return;
	}
	$naam = sanitize_file_name( wp_unslash( $_GET['gw_svg'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
	$pad  = get_theme_file_path( 'assets/home/' . $naam );
	if ( '.svg' !== substr( $naam, -4 ) || ! is_readable( $pad ) ) {
		status_header( 404 );
		exit;
	}
	$svg = gw_svg_gekleurd( $naam );
	if ( '' === $svg ) {
		$svg = (string) file_get_contents( $pad );
	}
	nocache_headers();
	header( 'Content-Type: image/svg+xml; charset=utf-8' );
	header( 'Cache-Control: public, max-age=3600' );
	header( 'X-Content-Type-Options: nosniff' );
	echo $svg; // phpcs:ignore WordPress.Security.EscapeOutput
	exit;
}, 0 );
