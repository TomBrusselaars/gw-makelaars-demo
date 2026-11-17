<?php
/**
 * Gewone pagina's, opgebouwd met dezelfde blokken als de homepage.
 * Begint de pagina niet met een Hero, dan krijgt hij ruimte onder de navigatie.
 */

defined( 'ABSPATH' ) || exit;

get_header( 'home' );
?>
<main id="main" class="main-wrapper<?php echo has_block( 'gw/hero' ) ? '' : ' has-nav-space'; ?>">
	<?php
	while ( have_posts() ) {
		the_post();
		if ( has_blocks() ) {
			gw_blokken_tonen();
		} else {
			echo '<section class="section_tekst-beeld background-color-sand"><div class="padding-global"><div class="container-medium"><h1 class="heading-style-h2">' . esc_html( get_the_title() ) . '</h1><div class="tekst-beeld_text">';
			the_content();
			echo '</div></div></div></section>';
		}
	}
	?>
</main>
<?php
get_footer( 'home' );
