<?php
/** Blok: Hero. Variabele $a = opgeschoonde attributen (inc/componenten.php). */
defined( 'ABSPATH' ) || exit;
$c = gw_contact();
?>
<section class="section_home-hero"<?php echo gw_anker( $a ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="home-hero_visual"<?php echo gw_reveal( 0, 'door' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>><div class="home-hero_shape"><?php echo gw_img( $a['afbeelding'], 'hero.webp', 'home-hero_image', array( 'laden' => 'eager', 'prioriteit' => true, 'maat' => 'full', 'sizes' => '100vw' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div></div>
	<div class="padding-global">
		<div class="container-large">
			<div class="home-hero_content">
				<h1 class="heading-style-h1"<?php echo gw_reveal(); // phpcs:ignore WordPress.Security.EscapeOutput ?>><?php
					echo esc_html( $a['regel_voor'] );
					if ( $a['accent'] ) {
						echo ' <span class="text-color-accent">' . esc_html( $a['accent'] ) . '</span>';
					}
					echo ' ' . esc_html( $a['regel_na'] );
				?></h1>
				<?php if ( $a['tekst'] ) : ?><p class="text-size-medium home-hero_text"<?php echo gw_reveal( 1 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>><?php echo esc_html( $a['tekst'] ); ?></p><?php endif; ?>
				<?php gw_knop( $a['knop_label'], $a['knop_url'], gw_reveal( 2 ) ); ?>
			</div>
		</div>
	</div>
	<?php if ( $c['naam'] ) : ?>
		<a class="home-hero_contact" href="#contact"<?php echo gw_reveal( 6 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
			<?php echo gw_img( $c['foto'], 'stan.webp', 'home-hero_contact-photo', array( 'maat' => 'thumbnail', 'laden' => 'eager' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<span class="home-hero_contact-text">
				<span class="heading-style-h6">Contact <?php echo esc_html( $c['voornaam'] ); ?></span>
				<span class="text-size-medium"><?php echo esc_html( $c['rol'] ); ?></span>
			</span>
		</a>
	<?php endif; ?>
</section>
