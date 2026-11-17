<?php
/**
 * admin.php — Aanbod → Koppeling: status van de koppeling + knop "Nu synchroniseren".
 * Het token zelf is hier bewust NIET in te vullen of te zien (staat in wp-config.php).
 */

defined( 'ABSPATH' ) || exit;

add_action( 'admin_menu', function () {
	add_submenu_page(
		'edit.php?post_type=' . YC_RW_CPT,
		'Realworks-koppeling',
		'Koppeling',
		'manage_options',
		'yc-realworks',
		'yc_rw_admin_page'
	);
} );

add_action( 'admin_post_yc_rw_sync', function () {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Geen toegang.' );
	}
	check_admin_referer( 'yc_rw_sync' );
	yc_rw_sync();
	wp_safe_redirect( admin_url( 'edit.php?post_type=' . YC_RW_CPT . '&page=yc-realworks&gesynct=1' ) );
	exit;
} );

function yc_rw_admin_page() {
	$r      = get_option( 'yc_rw_last_sync' );
	$aantal = wp_count_posts( YC_RW_CPT );
	?>
	<div class="wrap">
		<h1>Realworks-koppeling</h1>
		<?php if ( isset( $_GET['gesynct'] ) ) : ?>
			<div class="notice notice-success"><p>Synchronisatie uitgevoerd.</p></div>
		<?php endif; ?>
		<table class="form-table" role="presentation">
			<tr><th>Modus</th><td>
				<?php if ( yc_rw_live() ) : ?>
					<strong style="color:#2f6f4e">LIVE</strong> — echte Realworks-data
				<?php else : ?>
					<strong>Voorbeelddata</strong> — geen token ingesteld. Zet <code>define( 'RW_TOKEN', '…' );</code> in <code>wp-config.php</code> om live te gaan.
				<?php endif; ?>
			</td></tr>
			<tr><th>Vestiging</th><td><?php echo yc_rw_afdelingscode() ? esc_html( yc_rw_afdelingscode() ) : 'Alle vestigingen die het token mag zien'; ?></td></tr>
			<tr><th>Objecten op de site</th><td><?php echo (int) ( $aantal->publish ?? 0 ); ?></td></tr>
			<tr><th>Laatste synchronisatie</th><td>
				<?php if ( is_array( $r ) ) : ?>
					<?php echo esc_html( wp_date( 'j F Y H:i', $r['tijd'] ) ); ?> —
					<?php echo (int) $r['nieuw']; ?> nieuw, <?php echo (int) $r['bijgewerkt']; ?> bijgewerkt,
					<?php echo (int) $r['ongewijzigd']; ?> ongewijzigd, <?php echo (int) $r['verwijderd']; ?> verwijderd,
					<?php echo (int) ( $r['overgeslagen'] ?? 0 ); ?> niet openbaar (overgeslagen)
					<?php if ( $r['fout'] ) : ?>
						<br><span style="color:#9a3030">Fout: <?php echo esc_html( $r['fout'] ); ?></span>
					<?php endif; ?>
				<?php else : ?>
					Nog niet uitgevoerd
				<?php endif; ?>
			</td></tr>
			<tr><th>Automatisch</th><td>Elk uur. Wijzigingen in Realworks staan dus binnen een uur op de site.</td></tr>
		</table>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="yc_rw_sync">
			<?php wp_nonce_field( 'yc_rw_sync' ); ?>
			<?php submit_button( 'Nu synchroniseren' ); ?>
		</form>
	</div>
	<?php
}
