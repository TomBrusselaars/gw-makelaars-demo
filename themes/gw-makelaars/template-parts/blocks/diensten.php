<?php
/** Blok: Diensten — uitklaplijst die vanzelf doorloopt, met foto per dienst. */
defined( 'ABSPATH' ) || exit;
$items = array();
foreach ( gw_collectie( 'gw_dienst' ) as $p ) {
	$items[] = array(
		'titel'      => get_the_title( $p ),
		'tekst'      => gw_rich( $p ),
		'punten'     => (string) get_post_meta( $p->ID, 'gw_punten', true ),
		'afbeelding' => (int) get_post_thumbnail_id( $p ),
	);
}
?>
<section class="section_home-diensten"<?php echo gw_anker( $a ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="padding-global">
		<div class="container-large">
			<div class="home-diensten_card">
				<?php gw_sectiekop( $a['tag'], $a['kop'], array( 'midden' => true, 'klasse' => 'max-width-heading' ) ); ?>
				<?php if ( ! $items ) : gw_leeg_melding( 'diensten', 'Diensten' ); else : ?>
				<div class="home-diensten_layout">
					<div class="home-diensten_list" data-diensten<?php echo gw_reveal(); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
						<?php foreach ( $items as $i => $d ) : ?>
							<div class="home-diensten_item<?php echo 0 === $i ? ' is-open' : ''; ?>">
								<button class="home-diensten_trigger" type="button" aria-expanded="<?php echo 0 === $i ? 'true' : 'false'; ?>">
									<span class="heading-style-h3"><?php echo esc_html( $d['titel'] ); ?></span>
									<?php gw_pijl(); ?>
								</button>
								<div class="home-diensten_body"><div>
									<?php if ( $d['tekst'] ) : ?><div class="text-size-medium rich"><?php echo $d['tekst']; // phpcs:ignore WordPress.Security.EscapeOutput -- gw_rich() filtert met wp_kses ?></div><?php endif; ?>
									<?php $punten = array_filter( array_map( 'trim', explode( "\n", $d['punten'] ) ) ); ?>
									<?php if ( $punten ) : ?>
										<ul class="check-list">
											<?php foreach ( $punten as $punt ) : ?>
												<li><img src="<?php echo esc_url( gw_asset_url( 'check.svg' ) ); ?>" width="14" height="11" alt=""><?php echo esc_html( $punt ); ?></li>
											<?php endforeach; ?>
										</ul>
									<?php endif; ?>
								</div></div>
								<div class="home-diensten_line"><span></span></div>
							</div>
						<?php endforeach; ?>
					</div>
					<div class="home-diensten_visual"<?php echo gw_reveal( 0, 'door' ); // phpcs:ignore WordPress.Security.EscapeOutput ?> data-diensten-beeld>
						<?php foreach ( $items as $i => $d ) : ?>
							<?php echo gw_img( $d['afbeelding'], 'dienst.webp', 'home-diensten_image' . ( 0 === $i ? ' is-active' : '' ), array( 'maat' => 'large', 'sizes' => '(min-width: 992px) 425px, 100vw' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						<?php endforeach; ?>
					</div>
				</div>
				<?php endif; ?>
				<?php
				$knop_url = ! empty( $a['knop_url'] ) ? $a['knop_url'] : '';
				if ( ! $knop_url ) {
					$dp = get_page_by_path( 'diensten' );
					if ( $dp && function_exists( 'pll_get_post' ) && pll_get_post( $dp->ID ) ) {
						$dp = get_post( pll_get_post( $dp->ID ) );
					}
					$knop_url = $dp ? get_permalink( $dp ) : '#diensten';
				}
				if ( ! empty( $a['knop_label'] ) ) :
					?>
					<div class="button-group is-center home-diensten_actie"<?php echo gw_reveal(); // phpcs:ignore WordPress.Security.EscapeOutput ?>><?php gw_tekstlink( $a['knop_label'], $knop_url ); ?></div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>
