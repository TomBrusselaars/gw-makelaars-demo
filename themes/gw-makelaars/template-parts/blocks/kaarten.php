<?php
/** Blok: Kaarten-raster. */
defined( 'ABSPATH' ) || exit;
$donker = 'olive' === $a['kleur'];
?>
<section class="section_kaarten <?php echo $donker ? 'background-color-olive text-color-white' : 'background-color-sand'; ?>"<?php echo gw_anker( $a ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="padding-global">
		<div class="container-large">
			<?php gw_sectiekop( $a['tag'], $a['kop'], array( 'licht' => $donker ) ); ?>
			<div class="kaarten_grid" style="--kolommen:<?php echo (int) $a['kolommen']; ?>">
				<?php foreach ( $a['items'] as $i => $k ) : $tag = $k['link_url'] ? 'a' : 'div'; ?>
					<<?php echo $tag; // phpcs:ignore WordPress.Security.EscapeOutput -- vaste waarde a/div ?> class="kaart"<?php echo $k['link_url'] ? ' href="' . esc_url( $k['link_url'] ) . '"' : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo gw_reveal( $i % 4 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
						<?php if ( 'ja' === $a['foto'] ) : ?><span class="kaart_visual"><?php echo gw_img( $k['afbeelding'], 'dienst.webp', '', array( 'sizes' => '(min-width: 992px) 30vw, 90vw' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span><?php endif; ?>
						<span class="kaart_title"><?php echo esc_html( $k['titel'] ); ?></span>
						<?php if ( $k['tekst'] ) : ?><span class="kaart_text"><?php echo esc_html( $k['tekst'] ); ?></span><?php endif; ?>
					</<?php echo $tag; // phpcs:ignore WordPress.Security.EscapeOutput ?>>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</section>
