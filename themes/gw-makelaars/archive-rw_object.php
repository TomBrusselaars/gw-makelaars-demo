<?php
/**
 * Aanbodoverzicht: /aanbod/  (filters als nette URL's: /aanbod/huur/kantoorruimte/, zonder JavaScript).
 * Zelfde onderdelen als het aanbod-blok op de homepage.
 */

defined( 'ABSPATH' ) || exit;

$filters = yc_rw_filters();
$tabs    = array( '' => 'Alles', 'koop' => 'Koop', 'huur' => 'Huur' );
$labels  = yc_rw_functie_labels();
$types   = array( '' => 'Alle typen' ) + array_intersect_key( $labels, array_flip( array( 'KANTOORRUIMTE', 'WINKELRUIMTE', 'BEDRIJFSRUIMTE', 'HORECA', 'BELEGGING' ) ) );

get_header( 'home' );
?>
<main id="main" class="main-wrapper has-nav-space">
	<section id="aanbod" class="section_home-aanbod background-color-sand">
		<div class="padding-global">
			<div class="container-large">
				<div class="home-aanbod_header">
					<?php gw_sectiekop( 'Aanbod', gw_mod( 'aanbod_kop' ), array( 'niveau' => 'h1' ) ); ?>
					<nav class="filter-tabs" aria-label="Koop of huur">
						<?php foreach ( $tabs as $waarde => $label ) : ?>
							<a class="filter-tabs_link<?php echo $filters['aanbod'] === $waarde ? ' is-active' : ''; ?>" href="<?php echo esc_url( yc_rw_filter_url( array( 'aanbod' => $waarde ) ) ); ?>"><?php echo esc_html( $label ); ?></a>
						<?php endforeach; ?>
					</nav>
				</div>
				<nav class="filter-tabs is-wrap object_types" aria-label="Type ruimte">
					<?php foreach ( $types as $type => $label ) : ?>
						<a class="filter-tabs_link<?php echo $filters['functie'] === $type ? ' is-active' : ''; ?>" href="<?php echo esc_url( yc_rw_filter_url( array( 'functie' => $type ) ) ); ?>"><?php echo esc_html( $label ); ?></a>
					<?php endforeach; ?>
				</nav>

				<?php if ( have_posts() ) : ?>
					<div class="home-aanbod_grid">
						<?php
						$n = 0;
						while ( have_posts() ) {
							the_post();
							$o = yc_rw_object();
							if ( $o ) {
								gw_aanbodkaart( $o, get_permalink(), array( 'i' => $n++ ) );
							}
						}
						?>
					</div>
				<?php else : ?>
					<p class="text-size-medium">Geen aanbod gevonden met deze filters. <a class="text-link" href="<?php echo esc_url( get_post_type_archive_link( YC_RW_CPT ) ); ?>"><?php gw_tekstlink_inhoud( 'Toon alles' ); ?></a></p>
				<?php endif; ?>
			</div>
		</div>
	</section>
</main>
<?php
get_footer( 'home' );
