<?php
/** Blok: Tekst + foto, voor losse pagina's. */
defined( 'ABSPATH' ) || exit;
$donker = 'olive' === $a['kleur'];
?>
<section class="section_tekst-beeld <?php echo $donker ? 'background-color-olive text-color-white' : 'background-color-sand'; ?>"<?php echo gw_anker( $a ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="padding-global">
		<div class="container-large">
			<?php $met_foto = 'ja' === $a['foto']; ?>
			<div class="tekst-beeld_layout<?php echo 'links' === $a['richting'] ? ' is-reversed' : ''; echo $met_foto ? '' : ' is-text-only'; ?>">
				<div class="tekst-beeld_text">
					<?php gw_sectiekop( $a['tag'], $a['kop'], array( 'niveau' => $a['niveau'], 'licht' => $donker ) ); ?>
					<?php foreach ( array_filter( array_map( 'trim', preg_split( '/\R{2,}/', $a['tekst'] ) ) ) as $alinea ) : ?>
						<p class="text-size-medium"<?php echo gw_reveal( 1 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>><?php echo esc_html( $alinea ); ?></p>
					<?php endforeach; ?>
					<?php gw_knop( $a['knop_label'], $a['knop_url'], gw_reveal( 2 ) ); ?>
				</div>
				<?php if ( $met_foto ) : ?><div class="tekst-beeld_visual"<?php echo gw_reveal( 0, 'door' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>><?php echo gw_img( $a['afbeelding'], 'dienst.webp', '', array( 'sizes' => '(min-width: 992px) 40vw, 100vw' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div><?php endif; ?>
			</div>
		</div>
	</div>
</section>
