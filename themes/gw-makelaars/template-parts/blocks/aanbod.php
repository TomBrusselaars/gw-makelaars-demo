<?php
/** Blok: Aanbod uit Realworks, met filter dat zonder herladen werkt. */
defined( 'ABSPATH' ) || exit;

$actief  = function_exists( 'yc_rw_query' );
$filters = $actief ? yc_rw_filters() : array( 'aanbod' => '' );
$query   = $actief ? yc_rw_query() : null; // alles; het filter verbergt kaarten
$archief = $actief ? get_post_type_archive_link( YC_RW_CPT ) : '#';
$tabs    = array( '' => gw_t( 'Alles' ), 'koop' => gw_t( 'Koop' ), 'huur' => gw_t( 'Huur' ) );
$totaal  = 0;
?>
<section class="section_home-aanbod background-color-sand"<?php echo gw_anker( $a ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="padding-global">
		<div class="container-large">
			<div class="home-aanbod_header">
				<?php gw_sectiekop( $a['tag'], $a['kop'] ); ?>
				<nav class="filter-tabs" aria-label="Filter aanbod" data-aanbod-filter><span class="filter-tabs_pill" aria-hidden="true"></span>
					<?php foreach ( $tabs as $waarde => $label ) : ?>
						<a class="filter-tabs_link<?php echo $filters['aanbod'] === $waarde ? ' is-active' : ''; ?>" data-waarde="<?php echo esc_attr( $waarde ); ?>" href="<?php echo esc_url( function_exists( 'yc_rw_filter_url' ) ? yc_rw_filter_url( array( 'aanbod' => $waarde, 'functie' => '' ) ) : home_url( '/' ) ); ?>"><?php echo esc_html( $label ); ?></a>
					<?php endforeach; ?>
				</nav>
			</div>

			<?php if ( $query && $query->have_posts() ) : ?>
				<div class="home-aanbod_grid">
					<?php
					$n = 0;
					while ( $query->have_posts() && $n < 60 ) :
						$query->the_post();
						$o = yc_rw_object();
						if ( ! $o ) {
							continue;
						}
						$n++;
						$soort   = strtolower( implode( ' ', array_map( 'strval', (array) get_post_meta( get_the_ID(), '_rw_aanbod' ) ) ) );
						$past    = '' === $filters['aanbod'] || false !== strpos( $soort, $filters['aanbod'] );
						$totaal += $past ? 1 : 0;
						$toon    = $past && $totaal <= $a['max'];
						gw_aanbodkaart( $o, get_permalink(), array( 'soort' => $soort, 'toon' => $toon, 'i' => $n - 1 ) );
						?>
					<?php endwhile; wp_reset_postdata(); ?>
				</div>
				<div class="button-group is-center"<?php echo gw_reveal(); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
					<a class="button" href="<?php echo esc_url( $archief ); ?>" data-aanbod-knop><?php echo esc_html( $a['knop_label'] ); ?> (<span data-aanbod-aantal><?php echo (int) $totaal; ?></span>)</a>
				</div>
			<?php else : ?>
				<p class="text-size-medium"><?php echo esc_html( gw_t( 'Er is op dit moment geen aanbod dat hieraan voldoet.' ) ); ?></p>
			<?php endif; ?>
		</div>
	</div>
</section>
