<?php
/**
 * Fired when the plugin is uninstalled (deleted) via the Plugins screen.
 *
 * @package Advisers_Calculators
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Delete this plugin's options for the current site.
 *
 * @since 1.0.0
 *
 * @return void
 */
function advisers_calculators_delete_options() {
	delete_option( 'advisers_calculator_settings' );
	delete_option( 'advisers_calculators_db_version' );
}

advisers_calculators_delete_options();

// Clean up for multisite installs.
if ( is_multisite() ) {
	global $wpdb;
	$advisers_calculators_blog_ids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->blogs}" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	foreach ( $advisers_calculators_blog_ids as $advisers_calculators_blog_id ) {
		switch_to_blog( $advisers_calculators_blog_id );
		advisers_calculators_delete_options();
		restore_current_blog();
	}
}
