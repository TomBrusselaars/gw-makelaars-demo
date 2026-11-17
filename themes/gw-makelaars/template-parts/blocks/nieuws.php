<?php
/** Blok: Nieuws — de nieuwste berichten met uitgelichte afbeelding. */
defined( 'ABSPATH' ) || exit;

$kaarten   = array();
$berichten = get_posts( array( 'numberposts' => $a['aantal'], 'no_found_rows' => true ) );
foreach ( $berichten as $b ) {
	$cat       = get_the_category( $b->ID );
	$kaarten[] = array(
		'cat'   => $cat ? $cat[0]->name : 'Nieuws',
		'datum' => strtolower( wp_date( 'j M Y', get_post_timestamp( $b ) ) ),
		'titel' => get_the_title( $b ),
		'tekst' => wp_trim_words( get_the_excerpt( $b ), 20 ),
		'foto'  => has_post_thumbnail( $b ) ? get_the_post_thumbnail( $b, 'large', array( 'class' => 'nieuws-card_image', 'loading' => 'lazy' ) ) : '<img class="nieuws-card_image" src="' . esc_url( gw_asset_url( 'nieuws-1.webp' ) ) . '" loading="lazy" alt="">',
		'url'   => get_permalink( $b ),
	);
}
$alle_url = $a['link_url'] ?: ( get_option( 'page_for_posts' ) ? get_permalink( (int) get_option( 'page_for_posts' ) ) : '#nieuws' );
// vijf kaarten zoals in het ontwerp: de laatste schuift links buiten beeld
$start  = count( $kaarten ) >= 4;
$weer   = $start ? array_merge( array( end( $kaarten ) ), $kaarten ) : $kaarten;
?>
<section class="section_home-nieuws"<?php echo gw_anker( $a ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="padding-global">
		<div class="container-large">
			<div class="home-nieuws_header">
				<?php gw_sectiekop( $a['tag'], $a['kop'] ); ?>
				<?php gw_tekstlink( $a['link_label'], $alle_url ); ?>
			</div>
		</div>
	</div>
	<?php if ( ! $kaarten ) : ?><div class="padding-global"><div class="container-large"><?php gw_leeg_melding( 'nieuwsberichten', 'Nieuws' ); ?></div></div><?php else : ?>
	<div class="home-nieuws_track" data-nieuws data-cursor="drag"<?php echo $start ? ' data-start="1"' : ''; ?>>
		<?php foreach ( $weer as $i => $k ) : ?>
			<a class="nieuws-card" href="<?php echo esc_url( $k['url'] ); ?>"<?php echo gw_reveal( $i ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
				<span class="nieuws-card_visual"><?php echo $k['foto']; // phpcs:ignore WordPress.Security.EscapeOutput -- get_the_post_thumbnail / eigen escaping ?></span>
				<span class="nieuws-card_panel">
					<span class="nieuws-card_meta"><span><?php echo esc_html( $k['cat'] ); ?></span><img src="<?php echo esc_url( gw_asset_url( 'bullet-small.svg' ) ); ?>" width="7.5" height="6" alt=""><span><?php echo esc_html( $k['datum'] ); ?></span></span>
					<span class="nieuws-card_title"><?php echo esc_html( $k['titel'] ); ?></span>
					<span class="nieuws-card_text"><?php echo esc_html( $k['tekst'] ); ?></span>
					<span class="nieuws-card_footer text-link"><?php gw_tekstlink_inhoud( 'Lees meer' ); ?></span>
				</span>
			</a>
		<?php endforeach; ?>
	</div>
	<?php endif; ?>
</section>
