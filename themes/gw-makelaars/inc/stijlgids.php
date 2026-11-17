<?php
/**
 * Stijlgids voor designers: Weergave → Stijlgids.
 *
 * Toont alle atomen en moleculen met de echte CSS, en laat de tokens (kleur,
 * typografie, ruimte, vormen, hover- en animatie-instellingen) voor de hele
 * website aanpassen. Wijzigingen staan als :root-variabelen over home.css heen,
 * dus knoppen, hovers en reveals op alle pagina's volgen mee.
 * De standaardwaarden worden uit home.css gelezen: één bron van waarheid.
 */

defined( 'ABSPATH' ) || exit;

const GW_TOKENS_OPTIE = 'gw_tokens';

/** Toegestane easing-presets (sleutel => CSS). */
function gw_easings() {
	return array(
		'zacht'   => array( 'Zacht uit (standaard)', 'cubic-bezier(.2, .7, .2, 1)' ),
		'strak'   => array( 'Strak in/uit', 'cubic-bezier(.76, 0, .24, 1)' ),
		'veer'    => array( 'Veerkrachtig', 'cubic-bezier(.34, 1.56, .64, 1)' ),
		'ease'    => array( 'Standaard ease', 'ease' ),
		'lineair' => array( 'Lineair', 'linear' ),
	);
}

/** Welke tokens zijn instelbaar: sleutel => [label, type, groep]. Types: color, length, css, font, ease. */
function gw_token_schema() {
	$t = array();
	foreach ( array( 'olive' => 'Olijf (hoofdkleur, tekst)', 'sand' => 'Zand (lichte vlakken)', 'accent' => 'Accent (turquoise)', 'white' => 'Wit' ) as $k => $l ) {
		$t[ $k ] = array( $l, 'color', 'Kleuren' );
	}
	$t['font-serif']  = array( 'Kopfont (serif)', 'font', 'Typografie' );
	$t['font-sans']   = array( 'Tekstfont (sans)', 'font', 'Typografie' );
	foreach ( array( 'heading-h1' => 'Kop H1', 'heading-h2' => 'Kop H2', 'heading-h3' => 'Kop H3', 'heading-h4' => 'Kop H4', 'heading-h6' => 'Kop H6' ) as $k => $l ) {
		$t[ $k ] = array( $l . ' (bijv. clamp(2rem, 5vw, 4rem))', 'css', 'Typografie' );
	}
	foreach ( array( 'text-s' => 'Tekst klein', 'text-m' => 'Tekst normaal', 'text-l' => 'Tekst groot' ) as $k => $l ) {
		$t[ $k ] = array( $l, 'length', 'Typografie' );
	}
	foreach ( array( '3xs', '2xs', 'xs', 's', 'm', 'l', 'xl', '2xl', '3xl', '4xl', '5xl' ) as $m ) {
		$t[ 'space-' . $m ] = array( 'Ruimte ' . $m, 'length', 'Ruimte' );
	}
	$t['gutter'] = array( 'Zijmarge pagina (desktop)', 'css', 'Ruimte' );
	foreach ( array( 'xs' => 'Radius klein (iconen)', 's' => 'Radius foto/knop', 'm' => 'Radius kaart', 'l' => 'Radius sectie' ) as $k => $l ) {
		$t[ 'radius-' . $k ] = array( $l, 'length', 'Vormen' );
	}
	$t['button-bg']       = array( 'Knop: achtergrond', 'color', 'Componenten' );
	$t['button-color']    = array( 'Knop: tekstkleur', 'color', 'Componenten' );
	$t['button-hover-bg'] = array( 'Knop: achtergrond bij hover', 'color', 'Componenten' );
	$t['button-height']   = array( 'Knop: hoogte', 'length', 'Componenten' );
	$t['button-padding-x'] = array( 'Knop: ruimte links/rechts', 'length', 'Componenten' );
	$t['button-radius']   = array( 'Knop: afronding', 'length', 'Componenten' );
	$t['button-chamfer']  = array( 'Knop: afgeschuinde hoek (0 = recht)', 'length', 'Componenten' );
	$t['button-weight']   = array( 'Knop: letterdikte (400/500/700)', 'length', 'Componenten' );
	$t['card-panel-bg']   = array( 'Aanbodkaart: kleur vlak', 'color', 'Componenten' );
	$t['card-overlay']    = array( 'Aanbodkaart: verdonkering bij hover', 'css', 'Componenten' );
	$t['section-space']   = array( 'Sectie: verticale ruimte', 'css', 'Componenten' );
	$t['cursor-enabled']      = array( 'Cursor: aan (1) of uit (0)', 'length', 'Cursor' );
	$t['cursor-size']         = array( 'Cursor: formaat standaard', 'length', 'Cursor' );
	$t['cursor-hover-size']   = array( 'Cursor: formaat boven links/knoppen', 'length', 'Cursor' );
	$t['cursor-hover-opacity'] = array( 'Cursor: doorzichtigheid boven links (0–1)', 'length', 'Cursor' );
	$t['cursor-drag-size']    = array( 'Cursor: formaat boven sleepbaar', 'length', 'Cursor' );
	$t['cursor-color']        = array( 'Cursor: kleur (auto = tekstkleur, of bijv. #6e5a0e)', 'colorauto', 'Cursor' );
	$t['cursor-radius']       = array( 'Cursor: afronding (0 = scherp vierkant)', 'length', 'Cursor' );
	foreach ( array( '' => 'groot scherm', '-s' => 'tablet en telefoon' ) as $suffix => $naam ) {
		$t[ 'hero-top-x' . $suffix ]    = array( 'Hero-vorm, ' . $naam . ': begin bovenrand (% van links)', 'length', 'Hero-vorm' );
		$t[ 'hero-left-h' . $suffix ]   = array( 'Hero-vorm, ' . $naam . ': hoogte linkerrand (%)', 'length', 'Hero-vorm' );
		$t[ 'hero-right-h' . $suffix ]  = array( 'Hero-vorm, ' . $naam . ': hoogte rechterrand (%)', 'length', 'Hero-vorm' );
		$t[ 'hero-bottom-x' . $suffix ] = array( 'Hero-vorm, ' . $naam . ': einde onderrand (% van links)', 'length', 'Hero-vorm' );
	}
	$t['hover-lift']      = array( 'Hover: knop zweeft omhoog', 'length', 'Beweging' );
	$t['hover-zoom']      = array( 'Hover: fotozoom (1 = uit, 1.06 = standaard)', 'length', 'Beweging' );
	$t['motion-fast']     = array( 'Snelheid kleine hovers', 'length', 'Beweging' );
	$t['motion-base']     = array( 'Snelheid grotere hovers', 'length', 'Beweging' );
	$t['reveal-distance'] = array( 'Binnenkomst: afstand', 'length', 'Beweging' );
	$t['reveal-duration'] = array( 'Binnenkomst: duur', 'length', 'Beweging' );
	$t['ease-out']        = array( 'Beweging: versnelling', 'ease', 'Beweging' );
	return $t;
}

/** Standaardwaarden: de :root-variabelen uit home.css. */
function gw_token_standaard() {
	static $d = null;
	if ( null === $d ) {
		$css = (string) file_get_contents( get_template_directory() . '/assets/css/home.css' );
		$d   = array();
		if ( preg_match( '/:root\s*\{(.*?)\n\}/s', $css, $m ) && preg_match_all( '/--([a-z0-9-]+):\s*([^;]+);/', $m[1], $r, PREG_SET_ORDER ) ) {
			foreach ( $r as $x ) {
				$d[ $x[1] ] = trim( $x[2] );
			}
		}
	}
	return $d;
}

/** Kleurwaarde als hex, ook als de standaard naar een ander token verwijst (var(--accent)). */
function gw_token_hex( $w, array $std ) {
	if ( preg_match( '/^var\(--([a-z0-9-]+)\)$/', $w, $m ) && isset( $std[ $m[1] ] ) ) {
		return gw_token_hex( $std[ $m[1] ], $std );
	}
	return sanitize_hex_color( $w ) ?: '#ffffff';
}

function gw_token_schoon( $type, $w ) {
	$w = trim( (string) $w );
	switch ( $type ) {
		case 'color':
			return (string) sanitize_hex_color( $w );
		case 'colorauto':
			return 'auto' === strtolower( $w ) ? 'auto' : (string) sanitize_hex_color( $w );
		case 'length':
			return preg_match( '/^-?\d*\.?\d+(px|rem|em|s|ms|%|vw)?$/', $w ) ? $w : '';
		case 'css':
			return preg_match( '/^[a-z0-9.,%()\s+\-*\/]+$/i', $w ) && ! preg_match( '/url|import|expression/i', $w ) ? $w : '';
		case 'font':
			return preg_match( '/^[\w\s,"\'\-]+$/u', $w ) ? $w : '';
		case 'ease':
			$e = gw_easings();
			return isset( $e[ $w ] ) ? $e[ $w ][1] : '';
	}
	return '';
}

/** :root-regel met de ingestelde afwijkingen (leeg als er niets is aangepast). */
function gw_tokens_css() {
	$opt = get_option( GW_TOKENS_OPTIE, array() );
	if ( ! is_array( $opt ) || ! $opt ) {
		return '';
	}
	$schema = gw_token_schema();
	$regels = '';
	foreach ( $opt as $k => $w ) {
		if ( isset( $schema[ $k ] ) && '' !== ( $v = gw_token_schoon( 'ease' === $schema[ $k ][1] ? 'css' : $schema[ $k ][1], $w ) ) ) {
			$regels .= '--' . $k . ':' . $v . ';';
		}
	}
	return $regels ? ':root{' . $regels . '}' : '';
}

add_action( 'wp_enqueue_scripts', function () {
	if ( gw_tokens_css() && wp_style_is( 'gw-home', 'enqueued' ) ) {
		wp_add_inline_style( 'gw-home', gw_tokens_css() );
	}
}, 30 );

// ---------------------------------------------------------------------------
// Admin-pagina
// ---------------------------------------------------------------------------

add_action( 'admin_menu', function () {
	add_theme_page( 'Stijlgids', 'Stijlgids', 'manage_options', 'gw-stijlgids', 'gw_stijlgids_pagina' );
} );

add_action( 'admin_enqueue_scripts', function ( $hook ) {
	if ( 'appearance_page_gw-stijlgids' !== $hook ) {
		return;
	}
	$uri = get_template_directory_uri();
	$dir = get_template_directory();
	wp_enqueue_style( 'gw-home', $uri . '/assets/css/home.css', array(), filemtime( $dir . '/assets/css/home.css' ) );
	wp_enqueue_style( 'gw-stijlgids', $uri . '/assets/css/stijlgids.css', array( 'gw-home' ), filemtime( $dir . '/assets/css/stijlgids.css' ) );
	if ( gw_tokens_css() ) {
		wp_add_inline_style( 'gw-home', gw_tokens_css() );
	}
	wp_enqueue_script( 'gw-cursor', $uri . '/assets/js/cursor.js', array(), filemtime( $dir . '/assets/js/cursor.js' ), true );
	wp_enqueue_script( 'gw-stijlgids', $uri . '/assets/js/stijlgids.js', array( 'gw-cursor' ), filemtime( $dir . '/assets/js/stijlgids.js' ), true );
} );

add_action( 'admin_post_gw_stijl_opslaan', function () {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Geen toegang.' );
	}
	check_admin_referer( 'gw_stijl' );
	$schema = gw_token_schema();
	$nieuw  = array();
	if ( isset( $_POST['reset'] ) ) {
		delete_option( GW_TOKENS_OPTIE );
	} else {
		$in = isset( $_POST['t'] ) && is_array( $_POST['t'] ) ? wp_unslash( $_POST['t'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- per veld opgeschoond hieronder
		foreach ( $schema as $k => $def ) {
			$w = isset( $in[ $k ] ) ? gw_token_schoon( $def[1], is_scalar( $in[ $k ] ) ? (string) $in[ $k ] : '' ) : '';
			if ( '' !== $w ) {
				$nieuw[ $k ] = $w;
			}
		}
		update_option( GW_TOKENS_OPTIE, $nieuw );
	}
	wp_safe_redirect( admin_url( 'themes.php?page=gw-stijlgids&opgeslagen=1' ) );
	exit;
} );

function gw_stijlgids_pagina() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$schema = gw_token_schema();
	$std    = gw_token_standaard();
	$opt    = (array) get_option( GW_TOKENS_OPTIE, array() );
	$groepen = array();
	foreach ( $schema as $k => $def ) {
		$groepen[ $def[2] ][ $k ] = $def;
	}
	$kaart = gw_img( 0, 'dienst.webp', '' );
	?>
	<div class="wrap gw-stijl">
		<h1>Stijlgids</h1>
		<?php if ( isset( $_GET['opgeslagen'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification ?>
			<div class="notice notice-success is-dismissible"><p>Opgeslagen. De hele website gebruikt nu deze waarden.</p></div>
		<?php endif; ?>
		<p>Links stel je de waarden van het ontwerpsysteem in, rechts zie je direct het effect op de echte onderdelen. Beweeg over de onderdelen om hover-animaties te testen. Leeg laten = standaardwaarde uit <code>home.css</code>.</p>

		<div class="gw-stijl__kolommen">
			<form class="gw-stijl__form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="gw_stijl_opslaan">
				<?php wp_nonce_field( 'gw_stijl' ); ?>
				<?php foreach ( $groepen as $groep => $velden ) : ?>
					<details class="gw-stijl__groep" <?php echo in_array( $groep, array( 'Kleuren', 'Beweging' ), true ) ? 'open' : ''; ?>>
						<summary><?php echo esc_html( $groep ); ?></summary>
						<?php foreach ( $velden as $k => $def ) : $waarde = $opt[ $k ] ?? ''; ?>
							<label class="gw-stijl__veld">
								<span><?php echo esc_html( $def[0] ); ?></span>
								<?php if ( 'ease' === $def[1] ) : ?>
									<select name="t[<?php echo esc_attr( $k ); ?>]" data-token="<?php echo esc_attr( $k ); ?>">
										<?php foreach ( gw_easings() as $ek => $e ) : ?>
											<option value="<?php echo esc_attr( $ek ); ?>" data-css="<?php echo esc_attr( $e[1] ); ?>" <?php selected( $waarde === $e[1] || ( '' === $waarde && 'zacht' === $ek ) ); ?>><?php echo esc_html( $e[0] ); ?></option>
										<?php endforeach; ?>
									</select>
								<?php else : ?>
									<span class="gw-stijl__invoer">
										<?php if ( 'color' === $def[1] ) : ?><input type="color" value="<?php echo esc_attr( gw_token_hex( $waarde ?: $std[ $k ], $std ) ); ?>" data-kleurkiezer><?php endif; ?>
										<input type="text" name="t[<?php echo esc_attr( $k ); ?>]" value="<?php echo esc_attr( $waarde ); ?>" placeholder="<?php echo esc_attr( $std[ $k ] ?? '' ); ?>" data-token="<?php echo esc_attr( $k ); ?>">
									</span>
								<?php endif; ?>
							</label>
						<?php endforeach; ?>
					</details>
				<?php endforeach; ?>
				<p class="submit">
					<button class="button button-primary" type="submit">Opslaan voor de hele site</button>
					<button class="button" type="submit" name="reset" value="1" onclick="return confirm('Alle aanpassingen terugzetten naar de standaardwaarden?');">Alles terugzetten</button>
				</p>
			</form>

			<div class="gw-stijl__preview is-home-design" id="gw-preview">
				<section class="gw-stijl__vlak">
					<h2 class="gw-stijl__titel">Koppen en tekst</h2>
					<p class="heading-style-h1">Kop H1</p>
					<p class="heading-style-h2">Kop H2</p>
					<p class="heading-style-h3">Kop H3</p>
					<p class="heading-style-h4">Kop H4</p>
					<p class="heading-style-h6">Kop H6</p>
					<p class="text-size-medium">Lopende tekst. Wij kennen de panden in Schiedam, Vlaardingen, Maassluis en omstreken.</p>
					<div class="gw-stijl__rij"><?php gw_tag( 'Label' ); ?></div>
				</section>
				<section class="gw-stijl__vlak">
					<h2 class="gw-stijl__titel">Kleuren</h2>
					<div class="gw-stijl__rij">
						<?php foreach ( array( 'olive', 'sand', 'accent', 'white' ) as $k ) : ?>
							<span class="gw-stijl__kleur" style="background:var(--<?php echo esc_attr( $k ); ?>)"><?php echo esc_html( $k ); ?></span>
						<?php endforeach; ?>
					</div>
				</section>
				<section class="gw-stijl__vlak">
					<h2 class="gw-stijl__titel">Knoppen en links <small>(beweeg eroverheen)</small></h2>
					<div class="gw-stijl__rij">
						<?php gw_knop( 'Primaire knop', '#' ); ?>
						<?php gw_tekstlink( 'Tekstlink', '#' ); ?>
						<a class="gw-stijl__pijlrij" href="#" onclick="return false"><?php gw_pijl(); ?></a>
						<a class="gw-stijl__pijlrij" href="#" onclick="return false"><?php gw_pijl( true ); ?></a>
						<?php gw_pijl( false, true ); ?>
					</div>
				</section>
				<section class="gw-stijl__vlak">
					<h2 class="gw-stijl__titel">Kaart</h2>
					<a class="kaart gw-stijl__kaart" href="#" onclick="return false"><span class="kaart_visual"><?php echo $kaart; // phpcs:ignore WordPress.Security.EscapeOutput ?></span><span class="kaart_title">Kaarttitel</span><span class="kaart_text">Korte toelichting bij de kaart.</span></a>
				</section>
				<section class="gw-stijl__vlak is-donker">
					<h2 class="gw-stijl__titel">Op een donkere ondergrond</h2>
					<?php gw_tag( 'Label', true ); ?>
					<p class="heading-style-h3">Witte kop op olijf</p>
					<div class="gw-stijl__rij"><?php gw_knop( 'Knop', '#' ); ?><?php gw_tekstlink( 'Tekstlink', '#', true ); ?></div>
				</section>
				<section class="gw-stijl__vlak">
					<h2 class="gw-stijl__titel">Cursor <small>(beweeg over dit voorbeeldvlak, de links en de sleepstrook)</small></h2>
					<a class="text-link" href="#" onclick="return false"><?php gw_tekstlink_inhoud( 'Link: cursor wordt groter' ); ?></a>
					<div class="gw-stijl__slider" data-cursor="drag">Sleepbaar onderdeel (slider)</div>
				</section>
				<section class="gw-stijl__vlak">
					<h2 class="gw-stijl__titel">Binnenkomst-animatie</h2>
					<button type="button" class="button" id="gw-speel">Speel af</button>
					<div class="gw-stijl__demo" id="gw-demo">Zo komt een blok binnen.</div>
				</section>
			</div>
		</div>
	</div>
	<?php
}
