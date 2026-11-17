<?php
/** Blok: Team — teamleden uit de collectie Team. */
defined( 'ABSPATH' ) || exit;
$donker = 'olive' === $a['kleur'];
$leden  = gw_collectie( 'gw_team' );
?>
<section class="section_kaarten <?php echo $donker ? 'background-color-olive text-color-white' : 'background-color-sand'; ?>"<?php echo gw_anker( $a ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="padding-global">
		<div class="container-large">
			<?php gw_sectiekop( $a['tag'], $a['kop'], array( 'licht' => $donker ) ); ?>
			<?php if ( ! $leden ) : gw_leeg_melding( 'teamleden', 'Team' ); else : ?>
			<div class="kaarten_grid is-portret" style="--kolommen:<?php echo (int) $a['kolommen']; ?>">
				<?php foreach ( $leden as $i => $p ) : $tel = (string) get_post_meta( $p->ID, 'gw_telefoon', true ); $mail = (string) get_post_meta( $p->ID, 'gw_email', true ); ?>
					<div class="kaart"<?php echo gw_reveal( $i % 4 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
						<span class="kaart_visual"><?php echo gw_img( get_post_thumbnail_id( $p ), 'stan.webp', '', array( 'maat' => 'large', 'alt' => get_the_title( $p ), 'sizes' => '(min-width: 992px) 25vw, 90vw' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
						<span class="kaart_title"><?php echo esc_html( get_the_title( $p ) ); ?></span>
						<span class="kaart_text"><?php echo esc_html( (string) get_post_meta( $p->ID, 'gw_functie', true ) ); ?></span>
						<?php if ( $tel || is_email( $mail ) ) : ?>
							<span class="kaart_links">
								<?php if ( $tel ) : ?><a href="<?php echo esc_url( 'tel:' . preg_replace( '/[^\d+]/', '', $tel ) ); ?>"><?php echo esc_html( $tel ); ?></a><?php endif; ?>
								<?php if ( is_email( $mail ) ) : ?><a href="<?php echo esc_url( 'mailto:' . $mail ); ?>"><?php echo esc_html( $mail ); ?></a><?php endif; ?>
							</span>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>
		</div>
	</div>
</section>
