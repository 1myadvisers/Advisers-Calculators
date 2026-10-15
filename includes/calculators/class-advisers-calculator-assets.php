<?php
/**
 * Handles enqueueing of CSS/JS assets.
 *
 * @package Advisers_Calculators
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers and enqueues the Calculators module's front-end and admin
 * CSS/JS, and passes calculator settings to the front-end script via
 * wp_localize_script().
 *
 * @since 1.0.0
 */
class Advisers_Calculator_Assets {

	/**
	 * Enqueue front-end assets only when the `[advisers_calculator]` shortcode is
	 * present on the current singular page/post.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function enqueue_public_assets() {
		if ( ! is_singular() ) {
			return;
		}

		global $post;
		if ( empty( $post->post_content ) || ! has_shortcode( $post->post_content, 'advisers_calculator' ) ) {
			return;
		}

		wp_enqueue_style(
			'advisers-calculator-public',
			ADVISERS_CALCULATORS_URL . 'assets/calculators/css/advisers-calculator.css',
			array(),
			ADVISERS_CALCULATORS_VERSION
		);

		wp_enqueue_script(
			'advisers-calculator-public',
			ADVISERS_CALCULATORS_URL . 'assets/calculators/js/advisers-calculator.js',
			array(),
			ADVISERS_CALCULATORS_VERSION,
			true
		);

		$settings = Advisers_Calculator_Settings::get_settings();

		wp_localize_script(
			'advisers-calculator-public',
			'advisersCalculatorData',
			array(
				'currency'   => $settings['currency_symbol'],
				'color'      => $settings['primary_color'],
				'rates'      => array(
					'sip'      => (float) $settings['default_rate_sip'],
					'ppf'      => (float) $settings['default_rate_ppf'],
					'fd'       => (float) $settings['default_rate_fd'],
					'rd'       => (float) $settings['default_rate_rd'],
					'loan'     => (float) $settings['default_rate_loan'],
					'annuity'  => (float) $settings['default_rate_annuity'],
					'inflation'=> (float) $settings['default_inflation'],
				),
				'i18n'       => array(
					'principal'   => __( 'Principal / Invested Amount', 'advisers-calculators' ),
					'interest'    => __( 'Total Interest / Returns', 'advisers-calculators' ),
					'totalPayable'=> __( 'Total Payable', 'advisers-calculators' ),
					'maturity'    => __( 'Maturity Value', 'advisers-calculators' ),
					'monthlyEmi'  => __( 'Monthly EMI', 'advisers-calculators' ),
					'invalid'     => __( 'Please enter valid, positive numbers in all fields.', 'advisers-calculators' ),
				),
			)
		);
	}

	/**
	 * Enqueue admin assets only on this plugin's settings screen.
	 *
	 * @since 1.0.0
	 *
	 * @param string $hook Current admin page hook suffix, as passed by the
	 *                     `admin_enqueue_scripts` action.
	 * @return void
	 */
	public function enqueue_admin_assets( $hook ) {
		if ( 'toplevel_page_advisers-calculators' !== $hook ) {
			return;
		}

		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );

		wp_enqueue_style(
			'advisers-calculator-admin',
			ADVISERS_CALCULATORS_URL . 'assets/calculators/css/advisers-calculator-admin.css',
			array( 'wp-color-picker' ),
			ADVISERS_CALCULATORS_VERSION
		);

		wp_enqueue_script(
			'advisers-calculator-admin',
			ADVISERS_CALCULATORS_URL . 'assets/calculators/js/advisers-calculator-admin.js',
			array( 'wp-color-picker', 'jquery' ),
			ADVISERS_CALCULATORS_VERSION,
			true
		);
	}
}
