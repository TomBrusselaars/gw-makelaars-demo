<?php
/** Blok: Citaat. */
defined( 'ABSPATH' ) || exit;
$donker = 'olive' === $a['kleur'];
?>
<section class="section_quote <?php echo $donker ? 'background-color-olive text-color-white' : 'background-color-sand'; ?>"<?php echo gw_anker( $a ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="padding-global">
		<div class="container-large">
			<figure class="quote_layout" style="margin:0"<?php echo gw_reveal(); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
				<div class="quote_foto"><?php echo gw_img( $a['afbeelding'], 'stan.webp', '', array( 'maat' => 'medium', 'alt' => $a['naam'] ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
				<div class="quote_tekst">
					<blockquote>“<?php echo esc_html( $a['tekst'] ); ?>”</blockquote>
					<figcaption class="quote_naam"><b><?php echo esc_html( $a['naam'] ); ?></b><span class="opacity-80"><?php echo esc_html( $a['functie'] ); ?></span></figcaption>
				</div>
			</figure>
		</div>
	</div>
</section>
