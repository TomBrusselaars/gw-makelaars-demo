<?php
/** Blok: Cijferstrook. */
defined( 'ABSPATH' ) || exit;
$donker = 'olive' === $a['kleur'];
?>
<section class="section_cijfers <?php echo $donker ? 'background-color-olive text-color-white' : 'background-color-sand'; ?>"<?php echo gw_anker( $a ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="padding-global">
		<div class="container-large">
			<div class="cijfers_grid">
				<?php foreach ( $a['items'] as $i => $c ) : ?>
					<div class="cijfers_item"<?php echo gw_reveal( $i % 4 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
						<div class="heading-style-h2"><?php echo esc_html( $c['getal'] ); ?></div>
						<div class="text-size-medium"><?php echo esc_html( $c['tekst'] ); ?></div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</section>
