<?php
/** Blok: Over ons met drie cijfers en foto's. */
defined( 'ABSPATH' ) || exit;
$veld = gw_componenten()['over']['velden']['stats']['velden']['afbeelding'];
?>
<section class="section_home-over"<?php echo gw_anker( $a ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="padding-global">
		<div class="container-large">
			<?php gw_sectiekop( $a['tag'], $a['kop'], array( 'midden' => true, 'licht' => true, 'tekst' => $a['tekst'] ) ); ?>
			<div class="home-over_stats">
				<?php foreach ( $a['stats'] as $i => $s ) : ?>
					<div class="home-over_stat">
						<div class="home-over_stat-visual"<?php echo gw_reveal( $i, 'door' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>><?php echo gw_img( $s['afbeelding'], gw_standaard_beeld( $veld, $i ), '', array( 'maat' => 'large', 'laden' => 'eager', 'sizes' => '(min-width: 768px) 33vw, 90vw' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
						<div class="home-over_stat-text"<?php echo gw_reveal( $i + 3 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
							<div class="heading-style-h2"><?php echo esc_html( $s['getal'] ); ?></div>
							<div class="text-size-medium"><?php echo esc_html( $s['tekst'] ); ?></div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
			<div class="button-group is-center"<?php echo gw_reveal(); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
				<?php gw_knop( $a['knop_label'], $a['knop_url'] ); ?>
				<?php gw_tekstlink( $a['link_label'], $a['link_url'], true ); ?>
			</div>
		</div>
	</div>
</section>
