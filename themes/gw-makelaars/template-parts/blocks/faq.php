<?php
/** Blok: Veelgestelde vragen (accordeon). */
defined( 'ABSPATH' ) || exit;
$args = array();
if ( $a['categorie'] ) {
	$args['tax_query'] = array( array( 'taxonomy' => 'gw_faq_cat', 'field' => 'slug', 'terms' => sanitize_title( $a['categorie'] ) ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
}
$items = array();
foreach ( gw_collectie( 'gw_faq', $args ) as $p ) {
	$items[] = array( 'vraag' => get_the_title( $p ), 'antwoord' => gw_rich( $p ) );
}
?>
<section class="section_home-faq"<?php echo gw_anker( $a ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="padding-global">
		<div class="container-medium">
			<?php gw_sectiekop( $a['tag'], $a['kop'], array( 'midden' => true, 'klasse' => 'max-width-heading' ) ); ?>
			<?php if ( ! $items ) : gw_leeg_melding( 'vragen', 'Vragen (FAQ)' ); else : ?>
			<div class="faq_list" data-faq<?php echo gw_reveal(); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
				<?php foreach ( $items as $i => $v ) : $open = ( $i + 1 ) === $a['open']; ?>
					<div class="faq_item<?php echo $open ? ' is-open' : ''; ?>">
						<button class="faq_question" type="button" aria-expanded="<?php echo $open ? 'true' : 'false'; ?>">
							<span class="heading-style-h4"><?php echo esc_html( $v['vraag'] ); ?></span>
							<?php gw_pijl( true ); ?>
						</button>
						<div class="faq_answer"><div><div class="text-size-medium rich"><?php echo $v['antwoord']; // phpcs:ignore WordPress.Security.EscapeOutput -- gw_rich() filtert met wp_kses ?></div></div></div>
					</div>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>
		</div>
	</div>
</section>
