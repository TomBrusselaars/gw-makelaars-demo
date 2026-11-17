<?php
/** Blok: Contact-afsluiter. Persoon en gegevens komen uit de Customizer (één bron voor de hele site). */
defined( 'ABSPATH' ) || exit;
$c = gw_contact();
?>
<section class="section_home-cta"<?php echo gw_anker( $a ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="padding-global">
		<div class="container-medium">
			<div class="home-cta_layout text-color-white">
				<div class="home-cta_text"<?php echo gw_reveal(); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
					<h2 class="heading-style-h2"><?php echo esc_html( $a['kop'] ); ?></h2>
					<?php if ( $a['tekst'] ) : ?><p class="text-size-medium"><?php echo esc_html( $a['tekst'] ); ?></p><?php endif; ?>
				</div>
				<?php if ( 'ja' === $a['persoon'] ) : ?>
				<div class="home-cta_person">
						<?php echo gw_img( $c['foto'], 'stan.webp', 'home-cta_photo', array( 'maat' => 'medium', 'alt' => $c['naam'] ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						<div class="home-cta_details"<?php echo gw_reveal( 2 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
							<div>
								<div class="heading-style-h6"><?php echo esc_html( $c['naam'] ); ?></div>
								<div class="text-size-medium opacity-80"><?php echo esc_html( $c['rol'] ); ?></div>
							</div>
							<?php gw_contactlijst(); ?>
						</div>
					</div>
				<?php else : ?>
					<div class="home-cta_knop"><?php gw_knop( $a['knop_label'], $a['knop_url'], gw_reveal( 2 ) ); ?></div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>
