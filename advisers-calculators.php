<?php
/**
 * Plugin Name:       Advisers Calculators
 * Plugin URI:        https://myadvisers.in/
 * Description:       All-in-one financial toolkit for India — EMI, SIP, PPF, FD, RD, Insurance, Annuity, Retirement, Home Loan calculators and more. Everything under one settings dashboard.
 * Version:           2.0.1
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Biswajit Barman
 * Author URI:        https://myadvisers.in
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       advisers-calculators
 * Domain Path:       /languages
 *
 * @package Advisers_Calculators
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Core plugin constants used by the Calculators module.
 *
 * @since 1.0.0
 */
define( 'ADVISERS_CALCULATORS_VERSION', '2.0.1' );
define( 'ADVISERS_CALCULATORS_FILE', __FILE__ );
define( 'ADVISERS_CALCULATORS_PATH', plugin_dir_path( __FILE__ ) );
define( 'ADVISERS_CALCULATORS_URL', plugin_dir_url( __FILE__ ) );
define( 'ADVISERS_CALCULATORS_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Module 1: Financial Calculators (EMI, SIP, PPF, FD, RD, etc.)
 *
 * @since 1.0.0
 */
require_once ADVISERS_CALCULATORS_PATH . 'includes/calculators/class-advisers-calculator-loader.php';
require_once ADVISERS_CALCULATORS_PATH . 'includes/calculators/class-advisers-calculator-settings.php';
require_once ADVISERS_CALCULATORS_PATH . 'includes/calculators/class-advisers-calculator-shortcode.php';
require_once ADVISERS_CALCULATORS_PATH . 'includes/calculators/class-advisers-calculator-assets.php';

/**
 * Begins execution of the plugin: wires up the financial calculators module
 * under a "Advisers Calculators" admin menu.
 *
 * This plugin does not alter WordPress's built-in update routines in any way.
 * It relies entirely on core's native plugin update mechanism.
 *
 * @since 1.0.0
 *
 * @return void
 */
function advisers_calculators_run() {
	// Calculators module.
	$calculator_plugin = new Advisers_Calculator_Loader();
	$calculator_plugin->run();
}
add_action( 'plugins_loaded', 'advisers_calculators_run' );

/**
 * Re-run schema setup for sites that already had the plugin active before
 * an update. Currently a no-op for calculators-only mode (no database
 * tables required), but kept — and hooked — so future versions can add
 * upgrade routines (e.g. new options, database tables) without needing a
 * fresh activation.
 *
 * @since 1.0.0
 *
 * @return void
 */
function advisers_calculators_maybe_upgrade() {
	$installed_version = get_option( 'advisers_calculators_db_version', '0' );
	if ( version_compare( $installed_version, ADVISERS_CALCULATORS_VERSION, '>=' ) ) {
		return;
	}
	// No database tables needed for calculators module.
	update_option( 'advisers_calculators_db_version', ADVISERS_CALCULATORS_VERSION );
}
add_action( 'plugins_loaded', 'advisers_calculators_maybe_upgrade' );

/**
 * Code that runs on plugin activation. On a single site, activates
 * immediately. On a multisite network activation, runs the same
 * per-site setup for every site in the network (not just the current
 * one), switching into each site's context in turn.
 *
 * @since 1.2.2
 *
 * @param bool $network_wide Whether the plugin is being network-activated.
 * @return void
 */
function advisers_calculators_activate( $network_wide ) {
	if ( is_multisite() && $network_wide ) {
		foreach ( get_sites( array( 'fields' => 'ids' ) ) as $site_id ) {
			switch_to_blog( $site_id );
			advisers_calculators_single_activate();
			restore_current_blog();
		}
		return;
	}

	advisers_calculators_single_activate();
}
register_activation_hook( __FILE__, 'advisers_calculators_activate' );

/**
 * Per-site activation routine: sets Calculators module defaults for the
 * current site and flushes rewrite rules. Called directly on single-site
 * activation, and once per site (via switch_to_blog()) on a multisite
 * network activation.
 *
 * @since 1.2.2
 *
 * @return void
 */
function advisers_calculators_single_activate() {
	// Calculators module defaults.
	if ( ! get_option( 'advisers_calculator_settings' ) ) {
		add_option( 'advisers_calculator_settings', Advisers_Calculator_Settings::get_default_settings() );
	}

	flush_rewrite_rules();
}

/**
 * Code that runs when a new site is created on a network where this
 * plugin is already network-activated, so the new site gets the same
 * defaults as one activated individually.
 *
 * @since 1.2.2
 *
 * @param WP_Site $new_site The newly created site object.
 * @return void
 */
function advisers_calculators_new_site( $new_site ) {
	if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	if ( ! is_plugin_active_for_network( ADVISERS_CALCULATORS_BASENAME ) ) {
		return;
	}

	switch_to_blog( (int) $new_site->blog_id );
	advisers_calculators_single_activate();
	restore_current_blog();
}
add_action( 'wp_initialize_site', 'advisers_calculators_new_site' );

/**
 * Code that runs on plugin deactivation: flushes rewrite rules. Saved
 * settings are intentionally left in place; they are only removed on
 * uninstall (see uninstall.php).
 *
 * @since 1.0.0
 *
 * @return void
 */
function advisers_calculators_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'advisers_calculators_deactivate' );
