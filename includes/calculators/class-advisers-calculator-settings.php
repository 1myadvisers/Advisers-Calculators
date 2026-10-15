<?php
/**
 * Admin settings page for the Calculators module.
 *
 * @package Advisers_Calculators
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Defines the calculator catalogue and default settings, and renders and
 * saves the "Advisers Calculators" admin settings page via the Settings API.
 *
 * @since 1.0.0
 */
class Advisers_Calculator_Settings {

	/**
	 * Option name under which all Calculators module settings are stored.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	const OPTION_KEY = 'advisers_calculator_settings';

	/**
	 * Return every supported calculator grouped by category.
	 * Structure: category label => array( slug => calculator label ).
	 *
	 * @since 1.0.0
	 *
	 * @return array<string, array<string, string>> Category label as key,
	 *               mapping to an array of calculator slug => calculator label.
	 */
	public static function get_calculator_categories() {
		return array(
			__( 'Bank & Loans', 'advisers-calculators' ) => array(
				'emi'                   => __( 'EMI Calculator', 'advisers-calculators' ),
				'home-loan-emi'         => __( 'Home Loan EMI Calculator', 'advisers-calculators' ),
				'home-loan-eligibility' => __( 'Home Loan Eligibility Calculator', 'advisers-calculators' ),
				'fd-tdr'                => __( 'Fixed Deposit — TDR (Interest Payout)', 'advisers-calculators' ),
				'fd-stdr'               => __( 'Fixed Deposit — STDR (Cumulative)', 'advisers-calculators' ),
				'rd'                    => __( 'Recurring Deposit Calculator', 'advisers-calculators' ),
			),
			__( 'Post Office & Govt. Savings', 'advisers-calculators' ) => array(
				'ppf'   => __( 'PPF Calculator', 'advisers-calculators' ),
				'ssa'   => __( 'Sukanya Samriddhi Account Calculator', 'advisers-calculators' ),
				'scss'  => __( 'Senior Citizens Savings Scheme Calculator', 'advisers-calculators' ),
				'kvp'   => __( 'Kisan Vikas Patra Calculator', 'advisers-calculators' ),
				'mis'   => __( 'Monthly Income Scheme Calculator', 'advisers-calculators' ),
				'td'    => __( 'Post Office Time Deposit Calculator', 'advisers-calculators' ),
				'nsc'   => __( 'National Savings Certificate Calculator', 'advisers-calculators' ),
			),
			__( 'Mutual Funds', 'advisers-calculators' ) => array(
				'sip'     => __( 'SIP Calculator', 'advisers-calculators' ),
				'elss'    => __( 'ELSS Calculator', 'advisers-calculators' ),
				'swp'     => __( 'SWP Calculator', 'advisers-calculators' ),
				'lumpsum' => __( 'Lumpsum Investment Calculator', 'advisers-calculators' ),
			),
			__( 'Retirement', 'advisers-calculators' ) => array(
				'retirement-corpus' => __( 'Retirement Corpus Calculator', 'advisers-calculators' ),
				'nps'               => __( 'NPS Calculator', 'advisers-calculators' ),
				'epf'               => __( 'EPF Calculator', 'advisers-calculators' ),
				'vpf'               => __( 'VPF Calculator', 'advisers-calculators' ),
				'apy'               => __( 'Atal Pension Yojana Calculator', 'advisers-calculators' ),
				'pmsym'             => __( 'PM-SYM Calculator', 'advisers-calculators' ),
				'pmvvy'             => __( 'PM Vaya Vandana Yojana Calculator', 'advisers-calculators' ),
				'gratuity'          => __( 'Gratuity Calculator', 'advisers-calculators' ),
			),
			__( 'Tax', 'advisers-calculators' ) => array(
				'income-tax'     => __( 'Income Tax Calculator', 'advisers-calculators' ),
				'capital-gains'  => __( 'Capital Gains Tax Calculator', 'advisers-calculators' ),
			),
			__( 'Insurance', 'advisers-calculators' ) => array(
				'life-insurance' => __( 'Life Insurance Maturity Calculator', 'advisers-calculators' ),
				'term-life'      => __( 'Term Life Policy Calculator', 'advisers-calculators' ),
				'endowment'      => __( 'Endowment Policy Return Calculator', 'advisers-calculators' ),
				'pli'            => __( 'Postal Life Insurance (PLI) Estimator', 'advisers-calculators' ),
				'rpli'           => __( 'Rural PLI (RPLI) Estimator', 'advisers-calculators' ),
				'pmjjby'         => __( 'PM Jeevan Jyoti Bima Yojana Overview', 'advisers-calculators' ),
				'pmsby'          => __( 'PM Suraksha Bima Yojana Overview', 'advisers-calculators' ),
			),
			__( 'Bonds', 'advisers-calculators' ) => array(
				'frsb'      => __( 'Floating Rate Savings Bonds Calculator', 'advisers-calculators' ),
				'sgb'       => __( 'Sovereign Gold Bond Calculator', 'advisers-calculators' ),
				'bond-54ec' => __( '54EC Capital Gains Bonds Calculator', 'advisers-calculators' ),
			),
			__( 'General', 'advisers-calculators' ) => array(
				'compound-interest' => __( 'Compound Interest Calculator', 'advisers-calculators' ),
				'simple-interest'   => __( 'Simple Interest Calculator', 'advisers-calculators' ),
				'inflation'         => __( 'Inflation Calculator', 'advisers-calculators' ),
			),
		);
	}

	/**
	 * Flat slug => label map, derived from the categorized list.
	 *
	 * @since 1.0.0
	 *
	 * @return array<string, string> Calculator slug => calculator label.
	 */
	public static function get_calculator_types() {
		$flat = array();
		foreach ( self::get_calculator_categories() as $category => $calcs ) {
			foreach ( $calcs as $slug => $label ) {
				$flat[ $slug ] = $label;
			}
		}
		return $flat;
	}

	/**
	 * Default settings used on activation and as a fallback.
	 *
	 * @since 1.0.0
	 *
	 * @return array<string, mixed> Default option values, keyed as stored
	 *               under self::OPTION_KEY.
	 */
	public static function get_default_settings() {
		$defaults = array(
			'enabled'         => array_keys( self::get_calculator_types() ), // all enabled by default.
			'primary_color'   => '#0F6E4F',
			'currency_symbol' => '₹',

			// Bank & Loans.
			'default_rate_loan'   => 9.5,
			'default_rate_fd'     => 7.0,
			'default_rate_rd'     => 6.8,

			// Post Office & Govt Savings.
			'default_rate_ppf'  => 7.1,
			'default_rate_ssa'  => 8.2,
			'default_rate_scss' => 8.2,
			'default_rate_kvp'  => 7.5,
			'default_rate_mis'  => 7.4,
			'default_rate_td'   => 7.0,
			'default_rate_nsc'  => 7.7,

			// Mutual Funds.
			'default_rate_sip'   => 12,
			'default_rate_elss'  => 12,
			'default_rate_swp'   => 8,
			'default_rate_lumpsum' => 10,

			// Retirement.
			'default_inflation'    => 6,
			'default_rate_nps'     => 10,
			'default_rate_epf'     => 8.25,
			'default_rate_vpf'     => 8.25,
			'default_rate_apy'     => 8,

			// Tax.
			'default_ltcg_rate' => 12.5,
			'default_stcg_rate' => 20,
			'default_cess_rate' => 4,

			// Insurance.
			'default_rate_annuity'  => 6,
			'default_pli_rate'      => 45,
			'default_rpli_rate'     => 40,

			// Bonds.
			'default_frsb_rate' => 8.05,
			'default_sgb_rate'  => 2.5,
			'default_sgb_gold_growth' => 8,
			'default_54ec_rate' => 5.25,

			// General.
			'default_compound_rate' => 8,
			'default_simple_rate'   => 8,
		);
		return $defaults;
	}

	/**
	 * Get current settings merged with defaults (so new options never break old installs).
	 *
	 * @since 1.0.0
	 *
	 * @return array<string, mixed> Saved settings merged over the defaults.
	 */
	public static function get_settings() {
		$saved = get_option( self::OPTION_KEY, array() );
		return wp_parse_args( $saved, self::get_default_settings() );
	}

	/**
	 * Register the "Advisers Calculators" top-level admin menu page and its
	 * "Calculators" submenu item.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function add_settings_page() {
		add_menu_page(
			__( 'Advisers Calculators', 'advisers-calculators' ),
			__( 'Advisers Calculators', 'advisers-calculators' ),
			'manage_options',
			'advisers-calculators',
			array( $this, 'render_settings_page' ),
			'dashicons-calculator',
			58
		);

		add_submenu_page(
			'advisers-calculators',
			__( 'Calculators', 'advisers-calculators' ),
			__( 'Calculators', 'advisers-calculators' ),
			'manage_options',
			'advisers-calculators',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Add a "Settings" link on the Plugins list page.
	 *
	 * @since 1.0.0
	 *
	 * @param string[] $links Existing plugin action links.
	 * @return string[] Action links with the "Settings" link prepended.
	 */
	public function add_settings_link( $links ) {
		$settings_link = '<a href="' . esc_url( admin_url( 'admin.php?page=advisers-calculators' ) ) . '">' . esc_html__( 'Settings', 'advisers-calculators' ) . '</a>';
		array_unshift( $links, $settings_link );
		return $links;
	}

	/**
	 * Register the plugin's setting with the Settings API so it can be
	 * saved via `options.php` and sanitized on save.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register_settings() {
		register_setting(
			'advisers_calculator_settings_group',
			self::OPTION_KEY,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
			)
		);
	}

	/**
	 * All rate field keys, with their human label, grouped identically to
	 * the calculator categories above for the settings-page UI.
	 *
	 * @since 1.0.0
	 *
	 * @return array<string, array<string, string>> Category label as key,
	 *               mapping to an array of settings key => field label.
	 */
	public static function get_rate_field_groups() {
		return array(
			__( 'Bank & Loans', 'advisers-calculators' ) => array(
				'default_rate_loan' => __( 'Loan / EMI interest rate (%)', 'advisers-calculators' ),
				'default_rate_fd'   => __( 'Fixed Deposit rate (%)', 'advisers-calculators' ),
				'default_rate_rd'   => __( 'Recurring Deposit rate (%)', 'advisers-calculators' ),
			),
			__( 'Post Office & Govt. Savings', 'advisers-calculators' ) => array(
				'default_rate_ppf'  => __( 'PPF interest rate (%)', 'advisers-calculators' ),
				'default_rate_ssa'  => __( 'Sukanya Samriddhi rate (%)', 'advisers-calculators' ),
				'default_rate_scss' => __( 'SCSS rate (%)', 'advisers-calculators' ),
				'default_rate_kvp'  => __( 'Kisan Vikas Patra rate (%)', 'advisers-calculators' ),
				'default_rate_mis'  => __( 'Monthly Income Scheme rate (%)', 'advisers-calculators' ),
				'default_rate_td'   => __( 'Post Office Time Deposit rate (%)', 'advisers-calculators' ),
				'default_rate_nsc'  => __( 'NSC rate (%)', 'advisers-calculators' ),
			),
			__( 'Mutual Funds', 'advisers-calculators' ) => array(
				'default_rate_sip'     => __( 'SIP expected annual return (%)', 'advisers-calculators' ),
				'default_rate_elss'    => __( 'ELSS expected annual return (%)', 'advisers-calculators' ),
				'default_rate_swp'     => __( 'SWP expected annual return (%)', 'advisers-calculators' ),
				'default_rate_lumpsum' => __( 'Lumpsum expected annual return (%)', 'advisers-calculators' ),
			),
			__( 'Retirement', 'advisers-calculators' ) => array(
				'default_inflation' => __( 'Assumed inflation rate (%)', 'advisers-calculators' ),
				'default_rate_nps'  => __( 'NPS expected annual return (%)', 'advisers-calculators' ),
				'default_rate_epf'  => __( 'EPF interest rate (%)', 'advisers-calculators' ),
				'default_rate_vpf'  => __( 'VPF interest rate (%)', 'advisers-calculators' ),
				'default_rate_apy'  => __( 'Atal Pension Yojana assumed return (%)', 'advisers-calculators' ),
			),
			__( 'Tax', 'advisers-calculators' ) => array(
				'default_ltcg_rate' => __( 'Long-term capital gains rate — equity (%)', 'advisers-calculators' ),
				'default_stcg_rate' => __( 'Short-term capital gains rate — equity (%)', 'advisers-calculators' ),
				'default_cess_rate' => __( 'Health & education cess (%)', 'advisers-calculators' ),
			),
			__( 'Insurance', 'advisers-calculators' ) => array(
				'default_rate_annuity' => __( 'Annuity / bonus annual return (%)', 'advisers-calculators' ),
				'default_pli_rate'     => __( 'PLI premium per ₹1000 sum assured / year', 'advisers-calculators' ),
				'default_rpli_rate'    => __( 'RPLI premium per ₹1000 sum assured / year', 'advisers-calculators' ),
			),
			__( 'Bonds', 'advisers-calculators' ) => array(
				'default_frsb_rate'       => __( 'Floating Rate Savings Bond rate (%)', 'advisers-calculators' ),
				'default_sgb_rate'        => __( 'Sovereign Gold Bond interest rate (%)', 'advisers-calculators' ),
				'default_sgb_gold_growth' => __( 'Assumed gold price growth (%/yr)', 'advisers-calculators' ),
				'default_54ec_rate'       => __( '54EC capital gains bond rate (%)', 'advisers-calculators' ),
			),
			__( 'General', 'advisers-calculators' ) => array(
				'default_compound_rate' => __( 'Default compound interest rate (%)', 'advisers-calculators' ),
				'default_simple_rate'   => __( 'Default simple interest rate (%)', 'advisers-calculators' ),
			),
		);
	}

	/**
	 * Sanitize all settings input.
	 *
	 * @since 1.0.0
	 *
	 * @param array $input Raw input from the settings form.
	 * @return array Sanitized settings.
	 */
	public function sanitize_settings( $input ) {
		$clean   = array();
		$valid   = array_keys( self::get_calculator_types() );
		$enabled = isset( $input['enabled'] ) && is_array( $input['enabled'] ) ? $input['enabled'] : array();

		$clean['enabled'] = array_values( array_intersect( $valid, array_map( 'sanitize_text_field', wp_unslash( $enabled ) ) ) );

		$clean['primary_color']   = isset( $input['primary_color'] ) ? sanitize_hex_color( wp_unslash( $input['primary_color'] ) ) : '#0F6E4F';
		$clean['currency_symbol'] = isset( $input['currency_symbol'] ) ? sanitize_text_field( wp_unslash( $input['currency_symbol'] ) ) : '₹';

		$rate_groups = self::get_rate_field_groups();
		foreach ( $rate_groups as $group => $fields ) {
			foreach ( $fields as $key => $label ) {
				$value         = isset( $input[ $key ] ) ? round( (float) $input[ $key ], 2 ) : 0;
				$clean[ $key ] = max( 0, min( 100, $value ) );
			}
		}

		return $clean;
	}

	/**
	 * Render the full settings page markup.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$settings   = self::get_settings();
		$categories = self::get_calculator_categories();
		$rate_groups = self::get_rate_field_groups();
		?>
		<div class="wrap advisers-calculator-admin-wrap">
			<div class="mc-admin-header">
				<h1><?php esc_html_e( 'Advisers Calculators', 'advisers-calculators' ); ?></h1>
				<p><?php esc_html_e( 'Manage which financial calculators are available on your site, set default rates, and grab the shortcode for each calculator.', 'advisers-calculators' ); ?></p>
			</div>

			<form method="post" action="options.php">
				<?php settings_fields( 'advisers_calculator_settings_group' ); ?>

				<div class="mc-admin-card">
					<h2><?php esc_html_e( 'General', 'advisers-calculators' ); ?></h2>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><label for="mc_primary_color"><?php esc_html_e( 'Primary Color', 'advisers-calculators' ); ?></label></th>
							<td><input type="text" id="mc_primary_color" class="advisers-calculator-color-field" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[primary_color]" value="<?php echo esc_attr( $settings['primary_color'] ); ?>" /></td>
						</tr>
						<tr>
							<th scope="row"><label for="mc_currency_symbol"><?php esc_html_e( 'Currency Symbol', 'advisers-calculators' ); ?></label></th>
							<td><input type="text" id="mc_currency_symbol" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[currency_symbol]" value="<?php echo esc_attr( $settings['currency_symbol'] ); ?>" class="small-text" /></td>
						</tr>
					</table>
				</div>

				<div class="mc-admin-card">
					<h2><?php esc_html_e( 'Enabled Calculators &amp; Shortcodes', 'advisers-calculators' ); ?></h2>
					<p class="description">
						<?php esc_html_e( 'Show every enabled calculator at once, organized into tabs by category:', 'advisers-calculators' ); ?>
						<code>[advisers_calculator]</code>
						—
						<?php esc_html_e( 'or embed just one using its shortcode below.', 'advisers-calculators' ); ?>
					</p>

					<?php foreach ( $categories as $category => $calcs ) : ?>
						<h3 class="mc-admin-category-heading"><?php echo esc_html( $category ); ?></h3>
						<table class="widefat striped advisers-calculator-types-table">
							<thead>
								<tr>
									<th style="width:70px;"><?php esc_html_e( 'Enabled', 'advisers-calculators' ); ?></th>
									<th><?php esc_html_e( 'Calculator', 'advisers-calculators' ); ?></th>
									<th><?php esc_html_e( 'Shortcode', 'advisers-calculators' ); ?></th>
								</tr>
							</thead>
							<tbody>
							<?php foreach ( $calcs as $slug => $label ) : ?>
								<tr>
									<td>
										<input type="checkbox"
											name="<?php echo esc_attr( self::OPTION_KEY ); ?>[enabled][]"
											value="<?php echo esc_attr( $slug ); ?>"
											<?php checked( in_array( $slug, $settings['enabled'], true ) ); ?> />
									</td>
									<td><?php echo esc_html( $label ); ?></td>
									<td><code>[advisers_calculator type="<?php echo esc_attr( $slug ); ?>"]</code></td>
								</tr>
							<?php endforeach; ?>
							</tbody>
						</table>
					<?php endforeach; ?>
				</div>

				<div class="mc-admin-card">
					<h2><?php esc_html_e( 'Default Rates', 'advisers-calculators' ); ?></h2>
					<p class="description">
						<?php esc_html_e( 'These pre-fill each calculator. Visitors can still override the value for a single calculation. Government scheme rates change periodically — keep these current.', 'advisers-calculators' ); ?>
					</p>
					<?php foreach ( $rate_groups as $group => $fields ) : ?>
						<h3 class="mc-admin-category-heading"><?php echo esc_html( $group ); ?></h3>
						<table class="form-table" role="presentation">
							<?php foreach ( $fields as $key => $label ) : ?>
								<tr>
									<th scope="row"><label for="mc_<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label></th>
									<td><input type="number" step="0.01" id="mc_<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( $settings[ $key ] ); ?>" class="small-text" /></td>
								</tr>
							<?php endforeach; ?>
						</table>
					<?php endforeach; ?>
				</div>

				<?php submit_button(); ?>
			</form>

			<hr/>
			<p>
				<strong><?php esc_html_e( 'Plugin by', 'advisers-calculators' ); ?></strong>
				Biswajit Barman — <a href="https://myadvisers.in" target="_blank" rel="noopener noreferrer">myadvisers.in</a>
				&middot; <?php echo esc_html( sprintf( /* translators: %s: plugin version */ __( 'Version %s', 'advisers-calculators' ), ADVISERS_CALCULATORS_VERSION ) ); ?>
			</p>
		</div>
		<?php
	}
}
