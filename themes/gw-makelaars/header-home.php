<?php
/**
 * Header (Figma-ontwerp): logo, taalkeuze en menuknop over de hero, plus het
 * uitklapmenu (Figma › menu_open-active-state, node 135:2628).
 * Menu-items: Weergave → Menu's → locatie "Hoofdmenu" (ondertitel = omschrijving van het item).
 */

defined( 'ABSPATH' ) || exit;
$archief = function_exists( 'yc_rw_query' ) ? get_post_type_archive_link( YC_RW_CPT ) : home_url( '/#aanbod' );
$menu    = gw_menu_items( 'hoofdmenu', array(
	array( 'Home', home_url( '/' ), 'Terug naar het begin' ),
	array( 'Over ons', home_url( '/#over-ons' ), 'Team, werkwijze en regio' ),
	array( 'Aanbod', $archief, 'Wat er nu beschikbaar is' ),
	array( 'Diensten', home_url( '/#diensten' ), 'Aanhuur, verhuur en advies' ),
) );
$c = gw_contact();
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'is-home-design' ); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#main">Naar de inhoud</a>
<div class="page-wrapper">
	<header class="nav_component">
		<a class="nav_logo" href="<?php echo esc_url( home_url( '/' ) ); ?>"><img src="<?php echo esc_url( gw_asset_url( 'logo-header.svg' ) ); ?>" width="105" height="48" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>"></a>
		<div class="nav_right">
			<?php gw_taalkeuze(); ?>
			<button class="nav_menu-button" type="button" aria-expanded="false" aria-controls="nav-menu" aria-label="Menu openen">
				<img class="nav_menu-shape" src="<?php echo esc_url( gw_asset_url( 'menu-shape.svg' ) ); ?>" width="85" height="58.5" alt="">
				<img class="nav_menu-lines" src="<?php echo esc_url( gw_asset_url( 'menu-lines.svg' ) ); ?>" width="30" height="15" alt="">
			</button>
		</div>
	</header>

	<div class="nav_backdrop" data-menu-sluit></div>
	<div class="nav_menu" id="nav-menu" role="dialog" aria-label="Menu" aria-hidden="true">
		<img class="nav_menu-panel" src="<?php echo esc_url( gw_asset_url( 'menu-panel.svg' ) ); ?>" alt="">
		<div class="nav_menu-top">
			<?php gw_taalkeuze( true ); ?>
			<button class="nav_menu-close" type="button" aria-label="Menu sluiten" data-menu-sluit><img src="<?php echo esc_url( gw_asset_url( 'menu-close.svg' ) ); ?>" width="23" height="23" alt=""></button>
		</div>
		<nav class="nav_menu-links" aria-label="Hoofdmenu">
			<?php foreach ( $menu as $i => $m ) : ?>
				<a class="nav_menu-link" href="<?php echo esc_url( $m[1] ); ?>" style="--i:<?php echo (int) $i; ?>">
					<span class="nav_menu-title"><?php echo esc_html( $m[0] ); ?></span>
					<?php if ( $m[2] ) : ?>
						<span class="nav_menu-sub"><span><img src="<?php echo esc_url( gw_asset_url( 'menu-arrow.svg' ) ); ?>" width="13" height="17" alt=""><?php echo esc_html( $m[2] ); ?></span></span>
					<?php endif; ?>
				</a>
			<?php endforeach; ?>
		</nav>
		<div class="nav_menu-contact">
			<div class="footer_block">
				<div class="footer_heading">Kom langs</div>
				<?php gw_adresregel(); ?>
			</div>
			<div class="footer_block">
				<div class="footer_heading">Direct contact</div>
				<?php gw_contactlijst( true ); ?>
			</div>
		</div>
	</div>
