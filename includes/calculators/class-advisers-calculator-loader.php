<?php
/**
 * Registers all hooks for the plugin.
 *
 * @package Advisers_Calculators
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wires the Calculators module's settings, shortcode, and asset classes
 * into WordPress's hook system.
 *
 * @since 1.0.0
 */
class Advisers_Calculator_Loader {

	/**
	 * Run the loader: wire up settings, shortcodes, and assets.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function run() {
		$settings = new Advisers_Calculator_Settings();
		add_action( 'admin_menu', array( $settings, 'add_settings_page' ) );
		add_action( 'admin_init', array( $settings, 'register_settings' ) );
		add_filter( 'plugin_action_links_' . ADVISERS_CALCULATORS_BASENAME, array( $settings, 'add_settings_link' ) );

		$shortcode = new Advisers_Calculator_Shortcode();
		add_action( 'init', array( $shortcode, 'register_shortcodes' ) );

		$assets = new Advisers_Calculator_Assets();
		add_action( 'wp_enqueue_scripts', array( $assets, 'enqueue_public_assets' ) );
		add_action( 'admin_enqueue_scripts', array( $assets, 'enqueue_admin_assets' ) );

		// Lightweight, non-blocking compatibility notice (no external calls).
		add_action( 'admin_notices', array( $this, 'maybe_show_compat_notice' ) );
	}

	/**
	 * Show an admin notice only if running on an unusually old PHP version,
	 * to help admins keep the site compatible with current and upcoming releases.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function maybe_show_compat_notice() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}
		if ( version_compare( PHP_VERSION, '7.4', '<' ) ) {
			echo '<div class="notice notice-warning"><p>' .
				esc_html__( 'Advisers Calculator works best on PHP 7.4 or newer. Please ask your host to upgrade PHP for better security and performance.', 'advisers-calculators' ) .
				'</p></div>';
		}
	}
}
