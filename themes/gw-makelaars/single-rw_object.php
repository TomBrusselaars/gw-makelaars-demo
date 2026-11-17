<?php
/**
 * Objectpagina: /aanbod/<plaats>/<straat-nr>/
 * Gegevens uit Realworks (via de yc-realworks-plug-in); opmaak met de componenten van het thema.
 */

defined( 'ABSPATH' ) || exit;

$o = gw_koppeling_actief() ? yc_rw_object() : null;
get_header( 'home' );

if ( ! $o ) :
	?>
	<main id="main" class="main-wrapper has-nav-space">
		<section class="section_tekst-beeld background-color-sand"><div class="padding-global"><div class="container-medium"><div class="tekst-beeld_text">
			<h1 class="heading-style-h2">Dit object is niet (meer) beschikbaar</h1>
			<?php gw_knop( 'Bekijk het aanbod', get_post_type_archive_link( 'rw_object' ) ); ?>
		</div></div></div></section>
	</main>
	<?php
	get_footer( 'home' );
	return;
endif;

$fotos      = $o['media']['fotos'];
$extra      = max( 0, count( $fotos ) - 3 );
$prijs_type = 'koop' === $o['prijs']['type'] ? 'Koopsom' : ( 'huur' === $o['prijs']['type'] ? 'Huurprijs' : 'Prijs' );
$rijen      = array_filter( array(
	'Objectcode'    => $o['objectcode'],
	'Hoofdfunctie'  => $o['hoofdfunctieLabel'],
	'Oppervlakte'   => $o['oppervlakte'] ? gw_getal( $o['oppervlakte'] ) . ' m²' : '',
	'Status'        => $o['statusLabel'],
	$prijs_type     => $o['prijs']['label'],
	'Servicekosten' => $o['prijs']['servicekosten'],
	'Energielabel'  => $o['energielabel'],
	'Bouwjaar'      => $o['bouwjaar'],
	'Gebouwnaam'    => $o['gebouwnaam'],
) );
?>
<main id="main" class="main-wrapper has-nav-space">
	<section class="section_object-kop text-color-white">
		<div class="padding-global">
			<div class="container-large">
				<p class="object_crumbs text-size-medium"><a href="<?php echo esc_url( get_post_type_archive_link( 'rw_object' ) ); ?>">Aanbod</a> › <?php echo esc_html( $o['adres']['plaats'] ); ?> › <?php echo esc_html( $o['adres']['straatNr'] ); ?></p>
				<div class="object_title"<?php echo gw_reveal(); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
					<h1 class="heading-style-h2"><?php echo esc_html( $o['adres']['straatNr'] ); ?></h1>
					<p class="text-size-medium opacity-80"><?php echo esc_html( trim( $o['adres']['postcode'] . ' ' . $o['adres']['plaats'] ) ); ?></p>
				</div>
				<div class="object_gallery"<?php echo gw_reveal( 1 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
					<?php for ( $i = 0; $i < 3; $i++ ) : ?>
						<div class="object_gallery-item<?php echo 0 === $i ? ' is-main' : ''; ?>" data-i="<?php echo (int) $i; ?>">
							<?php if ( isset( $fotos[ $i ] ) ) : ?>
								<img src="<?php echo esc_url( yc_rw_media_src( $fotos[ $i ]['url'] ) ); ?>" alt="<?php echo 0 === $i ? esc_attr( $o['adres']['straatNr'] ) : ''; ?>">
							<?php endif; ?>
							<?php if ( 2 === $i && $extra > 0 ) : ?>
								<span class="object_gallery-more">+<?php echo (int) $extra; ?> foto's</span>
							<?php endif; ?>
						</div>
					<?php endfor; ?>
				</div>
			</div>
		</div>
	</section>

	<section class="section_object-detail background-color-sand">
		<div class="padding-global">
			<div class="container-large">
				<div class="object_layout">
					<div class="object_main">
						<div class="object_chips">
							<?php gw_tag( $o['statusLabel'] ); ?>
							<?php foreach ( $o['functies'] as $f ) : ?>
								<span class="object_chip"><?php echo esc_html( $f['label'] . ( $f['oppervlakte'] ? ' · ' . gw_getal( $f['oppervlakte'] ) . ' m²' : '' ) ); ?></span>
							<?php endforeach; ?>
						</div>

						<?php if ( $o['teksten']['aanbieding'] ) : ?>
							<div class="object_block"><h2 class="heading-style-h4">Omschrijving</h2><p class="text-size-medium object_tekst"><?php echo esc_html( $o['teksten']['aanbieding'] ); ?></p></div>
						<?php endif; ?>

						<div class="object_block">
							<h2 class="heading-style-h4">Kenmerken</h2>
							<dl class="object_kv">
								<?php foreach ( $rijen as $label => $waarde ) : ?>
									<div><dt><?php echo esc_html( $label ); ?></dt><dd><?php echo esc_html( $waarde ); ?></dd></div>
								<?php endforeach; ?>
							</dl>
						</div>

						<?php if ( $o['media']['plattegronden'] ) : ?>
							<div class="object_block">
								<h2 class="heading-style-h4">Plattegronden</h2>
								<div class="object_plans">
									<?php foreach ( $o['media']['plattegronden'] as $p ) : ?>
										<img src="<?php echo esc_url( yc_rw_media_src( $p['url'] ) ); ?>" data-big="<?php echo esc_url( yc_rw_media_src( $p['urlGroot'] ) ); ?>" alt="<?php echo esc_attr( $p['titel'] ?: 'Plattegrond' ); ?>">
									<?php endforeach; ?>
								</div>
							</div>
						<?php endif; ?>

						<?php if ( $o['media']['documenten'] ) : ?>
							<div class="object_block">
								<h2 class="heading-style-h4">Documenten</h2>
								<div class="object_docs">
									<?php foreach ( $o['media']['documenten'] as $d ) : ?>
										<a class="contact-list_item" href="<?php echo esc_url( yc_rw_media_src( $d['url'] ) ); ?>" target="_blank" rel="noopener"><i class="icon-square">PDF</i><?php echo esc_html( $d['titel'] ?: 'Document' ); ?></a>
									<?php endforeach; ?>
								</div>
							</div>
						<?php endif; ?>

						<div class="object_block">
							<h2 class="heading-style-h4">Locatie</h2>
							<div id="map" class="object_map" data-cursor="native"></div>
							<p id="map-fallback" class="object_map-fallback" hidden>Kaart niet beschikbaar — <?php echo esc_html( $o['adres']['volledig'] ); ?></p>
						</div>
					</div>

					<aside class="object_aside">
						<div class="object_prijs heading-style-h3"><?php echo esc_html( $o['prijs']['label'] ); ?></div>
						<p class="text-size-medium opacity-80"><?php echo esc_html( $prijs_type . ( $o['prijs']['servicekosten'] ? ' · servicekosten ' . $o['prijs']['servicekosten'] : '' ) ); ?></p>
						<form id="aanvraag" class="formulier_form object_form">
							<label>Naam *<input id="f-naam" name="naam" required autocomplete="name"></label>
							<label>Bedrijf<input id="f-bedrijf" name="bedrijf" autocomplete="organization"></label>
							<label>E-mail *<input id="f-email" name="email" type="email" required autocomplete="email"></label>
							<label>Telefoon<input id="f-tel" name="telefoon" type="tel" autocomplete="tel"></label>
							<label>Bericht<textarea id="f-bericht" name="bericht" rows="4">Ik ontvang graag meer informatie over <?php echo esc_textarea( $o['adres']['straatNr'] ); ?>.</textarea></label>
							<input class="formulier_hp" name="website" tabindex="-1" autocomplete="off" aria-hidden="true">
							<button class="button" type="submit">Informatie aanvragen</button>
							<p class="formulier_melding" id="fmsg" role="status" hidden></p>
						</form>
					</aside>
				</div>
			</div>
		</div>
	</section>
</main>

<div class="lightbox" id="lb" role="dialog" aria-label="Foto's" hidden>
	<button class="lightbox_knop is-sluit" id="lbx" type="button" aria-label="Sluiten">×</button>
	<button class="lightbox_knop is-prev" id="lbp" type="button" aria-label="Vorige">‹</button>
	<img id="lbimg" alt="">
	<button class="lightbox_knop is-next" id="lbn" type="button" aria-label="Volgende">›</button>
</div>

<?php
get_footer( 'home' );
