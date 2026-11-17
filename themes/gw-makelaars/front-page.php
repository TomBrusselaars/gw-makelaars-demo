<?php
/**
 * Homepage. De inhoud bestaat uit blokken (inc/blocks.php): in WordPress
 * Pagina's → Home sleep, verwijder en bewerk je de secties.
 * Ontwerp: Figma › Schaub › Home (node 135:2017), Client-First class-namen.
 */

defined( 'ABSPATH' ) || exit;

get_header( 'home' );
?>
<main id="main" class="main-wrapper">
	<?php
	while ( have_posts() ) {
		the_post();
		gw_blokken_tonen();
	}
	?>
</main>
<?php
get_footer( 'home' );
