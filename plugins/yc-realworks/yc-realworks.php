<?php
/**
 * Plugin Name: Yes Content — Realworks BOG-koppeling
 * Description: Haalt het bedrijfsaanbod op uit Realworks (BOG v3), bewaart het als inhoudstype "Aanbod" en stuurt aanvragen terug. Zonder token draait alles op voorbeelddata.
 * Version: 0.1.0
 * Author: Yes Content
 * Requires PHP: 7.4
 *
 * Token instellen (NOOIT in de database of in git):
 *   in wp-config.php ->  define( 'RW_TOKEN', '...' );
 *   optioneel         ->  define( 'RW_AFDELINGSCODE', '936358' );
 *
 * Per label (Schaub, GW, ...) is dit dezelfde plugin; alleen het token,
 * de afdelingscode en het thema verschillen.
 */

defined( 'ABSPATH' ) || exit;

define( 'YC_RW_VERSION', '0.2.1' );
define( 'YC_RW_DIR', plugin_dir_path( __FILE__ ) );
define( 'YC_RW_CPT', 'rw_object' );

require_once YC_RW_DIR . 'inc/normalize.php';
require_once YC_RW_DIR . 'inc/mock-data.php';
require_once YC_RW_DIR . 'inc/client.php';
require_once YC_RW_DIR . 'inc/content.php';
require_once YC_RW_DIR . 'inc/sync.php';
require_once YC_RW_DIR . 'inc/rest.php';
require_once YC_RW_DIR . 'inc/admin.php';

register_activation_hook( __FILE__, 'yc_rw_activate' );
register_deactivation_hook( __FILE__, 'yc_rw_deactivate' );

function yc_rw_activate() {
	if ( ! wp_next_scheduled( 'yc_rw_sync_event' ) ) {
		wp_schedule_event( time() + 60, 'hourly', 'yc_rw_sync_event' );
	}
	delete_option( 'yc_rw_rewrite_version' ); // forceer nieuwe URL-regels
}

function yc_rw_deactivate() {
	wp_clear_scheduled_hook( 'yc_rw_sync_event' );
}
