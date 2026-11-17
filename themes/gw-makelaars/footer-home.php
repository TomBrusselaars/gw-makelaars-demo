<?php
/**
 * Footer (Figma-ontwerp). Gegevens: Weergave → Aanpassen → GW makelaars.
 * Links: Weergave → Menu's → locatie "Footer".
 */

defined( 'ABSPATH' ) || exit;
$archief = function_exists( 'yc_rw_query' ) ? get_post_type_archive_link( YC_RW_CPT ) : home_url( '/#aanbod' );
$menu    = gw_menu_items( 'footermenu', array(
	array( gw_t( 'Home' ), home_url( '/' ), '' ),
	array( gw_t( 'Over ons' ), home_url( '/#over-ons' ), '' ),
	array( gw_t( 'Aanbod' ), $archief, '' ),
	array( gw_t( 'Diensten' ), home_url( '/#diensten' ), '' ),
	array( gw_t( 'Nieuws' ), home_url( '/#nieuws' ), '' ),
	array( gw_t( 'Contact' ), home_url( '/#contact' ), '' ),
) );
$social = array();
foreach ( array( 'facebook' => 'Facebook', 'linkedin' => 'LinkedIn', 'funda' => 'Funda Business' ) as $sleutel => $label ) {
	$u = (string) get_theme_mod( 'gw_social_' . $sleutel );
	if ( $u ) {
		$social[ $label ] = $u;
	}
}
?>
	<footer class="footer_component">
		<div class="footer_card"<?php echo gw_reveal(); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
			<div class="footer_left">
				<a class="footer_logo" href="<?php echo esc_url( home_url( '/' ) ); ?>"><img src="<?php echo esc_url( gw_asset_url( 'logo-footer.svg' ) ); ?>" width="240" height="58" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>"></a>
				<div class="footer_columns">
					<div class="footer_column">
						<div class="footer_block">
							<div class="footer_heading"><?php echo esc_html( gw_t( 'Algemeen contact' ) ); ?></div>
							<?php gw_contactlijst( true ); ?>
						</div>
						<?php if ( $social ) : ?>
							<div class="footer_block">
								<div class="footer_heading">Volg ons online</div>
								<div class="footer_social">
									<?php foreach ( $social as $label => $u ) : ?>
										<a href="<?php echo esc_url( $u ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $label ); ?></a>
									<?php endforeach; ?>
								</div>
							</div>
						<?php endif; ?>
					</div>
					<div class="footer_column">
						<div class="footer_block">
							<div class="footer_heading"><?php echo esc_html( gw_t( 'Kom langs' ) ); ?></div>
							<div class="contact-list"><?php gw_adresregel(); ?></div>
						</div>
					</div>
				</div>
			</div>
			<nav class="footer_nav" aria-label="Footer">
				<?php foreach ( $menu as $m ) : ?>
					<a class="footer_nav-link" href="<?php echo esc_url( $m[1] ); ?>"><span class="heading-style-h3"><?php echo esc_html( $m[0] ); ?></span><?php gw_pijl(); ?></a>
				<?php endforeach; ?>
			</nav>
			<div class="footer_bottom">
				<span>© <?php echo esc_html( gmdate( 'Y' ) . ' ' . get_bloginfo( 'name' ) ); ?></span>
				<span class="footer_bottom-links">
					<?php foreach ( array( 'voorwaarden' => 'Algemene voorwaarden', 'cookies' => 'Cookies' ) as $sleutel => $label ) : $u = (string) get_theme_mod( 'gw_link_' . $sleutel ); ?>
						<?php if ( $u ) : ?><a href="<?php echo esc_url( $u ); ?>"><?php echo esc_html( gw_t( $label ) ); ?></a><?php endif; ?>
					<?php endforeach; ?>
				</span>
			</div>
		</div>
	</footer>
</div><!-- /.page-wrapper -->
<?php wp_footer(); ?>
</body>
</html>
