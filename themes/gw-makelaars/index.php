<?php
/**
 * Terugval-template: nieuwsberichten, berichtenoverzicht, zoekresultaten en 404.
 */

defined( 'ABSPATH' ) || exit;

get_header( 'home' );
?>
<main id="main" class="main-wrapper has-nav-space">
	<section class="section_tekst-beeld background-color-sand">
		<div class="padding-global">
			<div class="container-medium">
				<?php if ( is_singular() ) : ?>
					<?php while ( have_posts() ) : the_post(); ?>
						<article class="tekst-beeld_text">
							<?php gw_tag( get_the_date( 'j F Y' ) ); ?>
							<h1 class="heading-style-h2"><?php the_title(); ?></h1>
							<?php if ( has_post_thumbnail() ) : ?><div class="tekst-beeld_visual"><?php the_post_thumbnail( 'large' ); ?></div><?php endif; ?>
							<div class="object_tekst text-size-medium"><?php the_content(); ?></div>
						</article>
					<?php endwhile; ?>
				<?php elseif ( have_posts() ) : ?>
					<h1 class="heading-style-h2">Nieuws</h1>
					<div class="kaarten_grid" style="--kolommen:2">
						<?php while ( have_posts() ) : the_post(); ?>
							<a class="kaart" href="<?php the_permalink(); ?>"<?php echo gw_reveal(); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
								<?php if ( has_post_thumbnail() ) : ?><span class="kaart_visual"><?php the_post_thumbnail( 'large' ); ?></span><?php endif; ?>
								<span class="kaart_title"><?php the_title(); ?></span>
								<span class="kaart_text"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 24 ) ); ?></span>
							</a>
						<?php endwhile; ?>
					</div>
				<?php elseif ( is_home() ) : ?>
					<div class="tekst-beeld_text">
						<h1 class="heading-style-h2">Nieuws</h1>
						<p class="text-size-medium">Er zijn nog geen nieuwsberichten.</p>
					</div>
				<?php else : ?>
					<div class="tekst-beeld_text">
						<h1 class="heading-style-h2">Niet gevonden</h1>
						<?php gw_knop( 'Terug naar de homepage', home_url( '/' ) ); ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</section>
</main>
<?php
get_footer( 'home' );
