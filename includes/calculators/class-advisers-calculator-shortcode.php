<?php
/**
 * Shortcode handling: renders calculator markup; all math runs in JS.
 *
 * @package Advisers_Calculators
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the `[advisers_calculator]` shortcode and renders its markup. Every
 * calculator's field configuration (labels, input types, defaults) is
 * defined here; the actual arithmetic runs client-side in JavaScript
 * (see assets/calculators/js/advisers-calculator.js) — this class only outputs
 * the form and result containers that script populates.
 *
 * @since 1.0.0
 */
class Advisers_Calculator_Shortcode {

	/**
	 * Register the `[advisers_calculator]` shortcode.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register_shortcodes() {
		add_shortcode( 'advisers_calculator', array( $this, 'render_shortcode' ) );
	}

	/**
	 * Field configuration for every calculator type.
	 *
	 * Each entry is keyed by calculator slug and contains a translated
	 * 'label' (and optional 'note'), plus a 'fields' array where each
	 * field is a tuple of: translated field label, HTML input type,
	 * default value, min value, and step increment.
	 *
	 * @since 1.0.0
	 *
	 * @return array<string, array> Calculator slug => configuration array
	 *               with 'label' (string), optional 'note' (string), and
	 *               'fields' (array of field-key => [ label, input type,
	 *               default, min, step ]).
	 */
	private function get_field_config() {
		$s = Advisers_Calculator_Settings::get_settings();

		return array(
			// ---------------- Bank & Loans ----------------
			'emi' => array(
				'label'  => __( 'EMI Calculator', 'advisers-calculators' ),
				'note'   => __( 'Standard reducing-balance EMI for any loan.', 'advisers-calculators' ),
				'fields' => array(
					'principal' => array( __( 'Loan Amount', 'advisers-calculators' ), 'number', 1000000, 1, 1 ),
					'rate'      => array( __( 'Annual Interest Rate (%)', 'advisers-calculators' ), 'number', $s['default_rate_loan'], 0.01, 0.01 ),
					'years'     => array( __( 'Loan Tenure (Years)', 'advisers-calculators' ), 'number', 5, 1, 1 ),
				),
			),
			'home-loan-emi' => array(
				'label'  => __( 'Home Loan EMI Calculator', 'advisers-calculators' ),
				'fields' => array(
					'principal' => array( __( 'Home Loan Amount', 'advisers-calculators' ), 'number', 3500000, 1, 1 ),
					'rate'      => array( __( 'Annual Interest Rate (%)', 'advisers-calculators' ), 'number', $s['default_rate_loan'], 0.01, 0.01 ),
					'years'     => array( __( 'Loan Tenure (Years)', 'advisers-calculators' ), 'number', 20, 1, 1 ),
				),
			),
			'home-loan-eligibility' => array(
				'label'  => __( 'Home Loan Eligibility Calculator', 'advisers-calculators' ),
				'fields' => array(
					'monthlyIncome' => array( __( 'Net Monthly Income', 'advisers-calculators' ), 'number', 80000, 1, 1 ),
					'existingEmi'   => array( __( 'Existing Monthly EMI (if any)', 'advisers-calculators' ), 'number', 0, 0, 1 ),
					'rate'          => array( __( 'Annual Interest Rate (%)', 'advisers-calculators' ), 'number', $s['default_rate_loan'], 0.01, 0.01 ),
					'years'         => array( __( 'Loan Tenure (Years)', 'advisers-calculators' ), 'number', 20, 1, 1 ),
					'foirPercent'   => array( __( 'Max EMI as % of Income (FOIR)', 'advisers-calculators' ), 'number', 50, 1, 1 ),
				),
			),
			'fd-tdr' => array(
				'label'  => __( 'Fixed Deposit — TDR (Interest Payout)', 'advisers-calculators' ),
				'note'   => __( 'Non-cumulative FD; interest is paid out periodically.', 'advisers-calculators' ),
				'fields' => array(
					'principal' => array( __( 'Deposit Amount', 'advisers-calculators' ), 'number', 100000, 1, 1 ),
					'rate'      => array( __( 'Annual Interest Rate (%)', 'advisers-calculators' ), 'number', $s['default_rate_fd'], 0.01, 0.01 ),
					'years'     => array( __( 'Tenure (Years)', 'advisers-calculators' ), 'number', 5, 1, 0.5 ),
				),
			),
			'fd-stdr' => array(
				'label'  => __( 'Fixed Deposit — STDR (Cumulative)', 'advisers-calculators' ),
				'note'   => __( 'Cumulative FD, quarterly compounding.', 'advisers-calculators' ),
				'fields' => array(
					'principal' => array( __( 'Deposit Amount', 'advisers-calculators' ), 'number', 100000, 1, 1 ),
					'rate'      => array( __( 'Annual Interest Rate (%)', 'advisers-calculators' ), 'number', $s['default_rate_fd'], 0.01, 0.01 ),
					'years'     => array( __( 'Tenure (Years)', 'advisers-calculators' ), 'number', 5, 1, 0.5 ),
				),
			),
			'rd' => array(
				'label'  => __( 'Recurring Deposit Calculator', 'advisers-calculators' ),
				'fields' => array(
					'monthly' => array( __( 'Monthly Deposit', 'advisers-calculators' ), 'number', 5000, 1, 1 ),
					'rate'    => array( __( 'Annual Interest Rate (%)', 'advisers-calculators' ), 'number', $s['default_rate_rd'], 0.01, 0.01 ),
					'years'   => array( __( 'Tenure (Years)', 'advisers-calculators' ), 'number', 5, 1, 1 ),
				),
			),

			// ---------------- Post Office & Govt Savings ----------------
			'ppf' => array(
				'label'  => __( 'PPF Calculator', 'advisers-calculators' ),
				'note'   => __( 'Annual deposit limit is ₹1,50,000. Minimum lock-in is 15 years.', 'advisers-calculators' ),
				'fields' => array(
					'yearly' => array( __( 'Yearly Deposit', 'advisers-calculators' ), 'number', 150000, 1, 1 ),
					'rate'   => array( __( 'Annual Interest Rate (%)', 'advisers-calculators' ), 'number', $s['default_rate_ppf'], 0.01, 0.01 ),
					'years'  => array( __( 'Duration (Years)', 'advisers-calculators' ), 'number', 15, 1, 1 ),
				),
			),
			'ssa' => array(
				'label'  => __( 'Sukanya Samriddhi Account Calculator', 'advisers-calculators' ),
				'note'   => __( 'Deposits made for 15 years; matures 21 years from account opening.', 'advisers-calculators' ),
				'fields' => array(
					'yearly'      => array( __( 'Yearly Deposit', 'advisers-calculators' ), 'number', 100000, 1, 1 ),
					'rate'        => array( __( 'Annual Interest Rate (%)', 'advisers-calculators' ), 'number', $s['default_rate_ssa'], 0.01, 0.01 ),
					'depositYears'=> array( __( 'Deposit Period (Years)', 'advisers-calculators' ), 'number', 15, 1, 1 ),
					'totalYears'  => array( __( 'Total Maturity Period (Years)', 'advisers-calculators' ), 'number', 21, 1, 1 ),
				),
			),
			'scss' => array(
				'label'  => __( 'Senior Citizens Savings Scheme Calculator', 'advisers-calculators' ),
				'note'   => __( 'Fixed 5-year tenure, quarterly interest payout. Max deposit ₹30,00,000.', 'advisers-calculators' ),
				'fields' => array(
					'principal' => array( __( 'Deposit Amount', 'advisers-calculators' ), 'number', 1500000, 1, 1 ),
					'rate'      => array( __( 'Annual Interest Rate (%)', 'advisers-calculators' ), 'number', $s['default_rate_scss'], 0.01, 0.01 ),
				),
			),
			'kvp' => array(
				'label'  => __( 'Kisan Vikas Patra Calculator', 'advisers-calculators' ),
				'note'   => __( 'Investment doubles over a fixed government-notified period.', 'advisers-calculators' ),
				'fields' => array(
					'principal' => array( __( 'Investment Amount', 'advisers-calculators' ), 'number', 100000, 1, 1 ),
					'rate'      => array( __( 'Annual Interest Rate (%)', 'advisers-calculators' ), 'number', $s['default_rate_kvp'], 0.01, 0.01 ),
				),
			),
			'mis' => array(
				'label'  => __( 'Monthly Income Scheme Calculator', 'advisers-calculators' ),
				'note'   => __( '5-year post office scheme. Max deposit ₹9,00,000 single / ₹15,00,000 joint.', 'advisers-calculators' ),
				'fields' => array(
					'principal' => array( __( 'Deposit Amount', 'advisers-calculators' ), 'number', 900000, 1, 1 ),
					'rate'      => array( __( 'Annual Interest Rate (%)', 'advisers-calculators' ), 'number', $s['default_rate_mis'], 0.01, 0.01 ),
				),
			),
			'td' => array(
				'label'  => __( 'Post Office Time Deposit Calculator', 'advisers-calculators' ),
				'fields' => array(
					'principal' => array( __( 'Deposit Amount', 'advisers-calculators' ), 'number', 100000, 1, 1 ),
					'rate'      => array( __( 'Annual Interest Rate (%)', 'advisers-calculators' ), 'number', $s['default_rate_td'], 0.01, 0.01 ),
					'years'     => array( __( 'Tenure (Years)', 'advisers-calculators' ), 'number', 5, 1, 1 ),
				),
			),
			'nsc' => array(
				'label'  => __( 'National Savings Certificate Calculator', 'advisers-calculators' ),
				'note'   => __( 'Fixed 5-year tenure, compounded annually.', 'advisers-calculators' ),
				'fields' => array(
					'principal' => array( __( 'Investment Amount', 'advisers-calculators' ), 'number', 100000, 1, 1 ),
					'rate'      => array( __( 'Annual Interest Rate (%)', 'advisers-calculators' ), 'number', $s['default_rate_nsc'], 0.01, 0.01 ),
				),
			),

			// ---------------- Mutual Funds ----------------
			'sip' => array(
				'label'  => __( 'SIP Calculator', 'advisers-calculators' ),
				'fields' => array(
					'monthly' => array( __( 'Monthly Investment', 'advisers-calculators' ), 'number', 10000, 1, 1 ),
					'rate'    => array( __( 'Expected Annual Return (%)', 'advisers-calculators' ), 'number', $s['default_rate_sip'], 0.01, 0.01 ),
					'years'   => array( __( 'Investment Period (Years)', 'advisers-calculators' ), 'number', 10, 1, 1 ),
				),
			),
			'elss' => array(
				'label'  => __( 'ELSS Calculator', 'advisers-calculators' ),
				'note'   => __( 'Mandatory 3-year lock-in. Qualifies for Section 80C under the old tax regime.', 'advisers-calculators' ),
				'fields' => array(
					'monthly' => array( __( 'Monthly SIP Investment', 'advisers-calculators' ), 'number', 12500, 1, 1 ),
					'rate'    => array( __( 'Expected Annual Return (%)', 'advisers-calculators' ), 'number', $s['default_rate_elss'], 0.01, 0.01 ),
					'years'   => array( __( 'Investment Period (Years)', 'advisers-calculators' ), 'number', 3, 1, 1 ),
				),
			),
			'swp' => array(
				'label'  => __( 'SWP Calculator', 'advisers-calculators' ),
				'note'   => __( 'Estimates how long a corpus lasts with fixed monthly withdrawals.', 'advisers-calculators' ),
				'fields' => array(
					'corpus'     => array( __( 'Starting Corpus', 'advisers-calculators' ), 'number', 2000000, 1, 1 ),
					'withdrawal' => array( __( 'Monthly Withdrawal', 'advisers-calculators' ), 'number', 15000, 1, 1 ),
					'rate'       => array( __( 'Expected Annual Return (%)', 'advisers-calculators' ), 'number', $s['default_rate_swp'], 0.01, 0.01 ),
				),
			),
			'lumpsum' => array(
				'label'  => __( 'Lumpsum Investment Calculator', 'advisers-calculators' ),
				'fields' => array(
					'principal' => array( __( 'Investment Amount', 'advisers-calculators' ), 'number', 100000, 1, 1 ),
					'rate'      => array( __( 'Expected Annual Return (%)', 'advisers-calculators' ), 'number', $s['default_rate_lumpsum'], 0.01, 0.01 ),
					'years'     => array( __( 'Investment Period (Years)', 'advisers-calculators' ), 'number', 10, 1, 1 ),
				),
			),

			// ---------------- Retirement ----------------
			'retirement-corpus' => array(
				'label'  => __( 'Retirement Corpus Calculator', 'advisers-calculators' ),
				'fields' => array(
					'currentExpense' => array( __( 'Current Monthly Expense', 'advisers-calculators' ), 'number', 40000, 1, 1 ),
					'currentAge'     => array( __( 'Current Age', 'advisers-calculators' ), 'number', 30, 1, 1 ),
					'retireAge'      => array( __( 'Retirement Age', 'advisers-calculators' ), 'number', 60, 1, 1 ),
					'lifeExpectancy' => array( __( 'Life Expectancy (Age)', 'advisers-calculators' ), 'number', 85, 1, 1 ),
					'inflation'      => array( __( 'Expected Inflation (%)', 'advisers-calculators' ), 'number', $s['default_inflation'], 0.01, 0.01 ),
					'postReturn'     => array( __( 'Post-Retirement Return Rate (%)', 'advisers-calculators' ), 'number', 7, 0.01, 0.01 ),
				),
			),
			'nps' => array(
				'label'  => __( 'NPS Calculator', 'advisers-calculators' ),
				'note'   => __( 'At least 40% of the corpus must be used to buy an annuity.', 'advisers-calculators' ),
				'fields' => array(
					'monthly'     => array( __( 'Monthly Contribution', 'advisers-calculators' ), 'number', 5000, 1, 1 ),
					'rate'        => array( __( 'Expected Annual Return (%)', 'advisers-calculators' ), 'number', $s['default_rate_nps'], 0.01, 0.01 ),
					'years'       => array( __( 'Years to Retirement', 'advisers-calculators' ), 'number', 25, 1, 1 ),
					'annuityPct'  => array( __( 'Annuity Purchase (%, min 40)', 'advisers-calculators' ), 'number', 40, 40, 1 ),
					'annuityRate' => array( __( 'Assumed Annuity Rate (%)', 'advisers-calculators' ), 'number', 6, 0.01, 0.01 ),
				),
			),
			'epf' => array(
				'label'  => __( 'EPF Calculator', 'advisers-calculators' ),
				'fields' => array(
					'basic'         => array( __( 'Basic + DA (Monthly)', 'advisers-calculators' ), 'number', 30000, 1, 1 ),
					'employeePct'   => array( __( 'Employee Contribution (%)', 'advisers-calculators' ), 'number', 12, 0.01, 0.01 ),
					'employerPct'   => array( __( 'Employer Contribution to EPF (%)', 'advisers-calculators' ), 'number', 3.67, 0.01, 0.01 ),
					'rate'          => array( __( 'EPF Interest Rate (%)', 'advisers-calculators' ), 'number', $s['default_rate_epf'], 0.01, 0.01 ),
					'years'         => array( __( 'Years to Retirement', 'advisers-calculators' ), 'number', 25, 1, 1 ),
					'increment'     => array( __( 'Annual Salary Increment (%)', 'advisers-calculators' ), 'number', 5, 0.01, 0.01 ),
				),
			),
			'vpf' => array(
				'label'  => __( 'VPF Calculator', 'advisers-calculators' ),
				'fields' => array(
					'monthly' => array( __( 'Voluntary Monthly Contribution', 'advisers-calculators' ), 'number', 10000, 1, 1 ),
					'rate'    => array( __( 'VPF Interest Rate (%)', 'advisers-calculators' ), 'number', $s['default_rate_vpf'], 0.01, 0.01 ),
					'years'   => array( __( 'Years to Retirement', 'advisers-calculators' ), 'number', 20, 1, 1 ),
				),
			),
			'apy' => array(
				'label'  => __( 'Atal Pension Yojana Calculator', 'advisers-calculators' ),
				'note'   => __( 'Approximate model — actual APY contribution slabs are fixed by government notification. Check pfrda.org.in for the exact figure.', 'advisers-calculators' ),
				'fields' => array(
					'targetPension' => array( __( 'Target Monthly Pension (₹1000–5000)', 'advisers-calculators' ), 'number', 5000, 1000, 1 ),
					'age'           => array( __( 'Current Age', 'advisers-calculators' ), 'number', 30, 18, 1 ),
					'rate'          => array( __( 'Assumed Return (%)', 'advisers-calculators' ), 'number', $s['default_rate_apy'], 0.01, 0.01 ),
				),
			),
			'pmsym' => array(
				'label'  => __( 'PM-SYM Calculator', 'advisers-calculators' ),
				'note'   => __( 'Approximate model — actual PM-SYM contribution slabs (₹55–₹200/month) are fixed by age.', 'advisers-calculators' ),
				'fields' => array(
					'age'  => array( __( 'Current Age (18–40)', 'advisers-calculators' ), 'number', 25, 18, 1 ),
					'rate' => array( __( 'Assumed Fund Return (%)', 'advisers-calculators' ), 'number', 8, 0.01, 0.01 ),
				),
			),
			'pmvvy' => array(
				'label'  => __( 'PM Vaya Vandana Yojana Calculator', 'advisers-calculators' ),
				'note'   => __( 'Administered by LIC; 10-year policy term, purchase price refunded on maturity.', 'advisers-calculators' ),
				'fields' => array(
					'purchasePrice' => array( __( 'Purchase Price (Lump Sum)', 'advisers-calculators' ), 'number', 1500000, 1, 1 ),
					'rate'          => array( __( 'Assured Return Rate (%)', 'advisers-calculators' ), 'number', 7.4, 0.01, 0.01 ),
				),
			),
			'gratuity' => array(
				'label'  => __( 'Gratuity Calculator', 'advisers-calculators' ),
				'note'   => __( 'Formula: (15 × last drawn salary × years of service) / 26. Exemption capped at ₹20,00,000.', 'advisers-calculators' ),
				'fields' => array(
					'salary' => array( __( 'Last Drawn Monthly Salary (Basic + DA)', 'advisers-calculators' ), 'number', 60000, 1, 1 ),
					'years'  => array( __( 'Years of Service', 'advisers-calculators' ), 'number', 10, 1, 1 ),
				),
			),

			// ---------------- Tax ----------------
			'income-tax' => array(
				'label'  => __( 'Income Tax Calculator', 'advisers-calculators' ),
				'note'   => __( 'Illustrative slabs — verify current rates against the latest CBDT notification. Not tax advice.', 'advisers-calculators' ),
				'fields' => array(
					'income'      => array( __( 'Gross Annual Income', 'advisers-calculators' ), 'number', 1500000, 1, 1 ),
					'stdDeduction'=> array( __( 'Standard Deduction', 'advisers-calculators' ), 'number', 75000, 0, 1 ),
					'deductions'  => array( __( 'Other Deductions (80C, 80D, HRA — Old Regime only)', 'advisers-calculators' ), 'number', 150000, 0, 1 ),
					'regime'      => array( __( 'Regime: 1 = New, 0 = Old', 'advisers-calculators' ), 'number', 1, 0, 1 ),
					'cess'        => array( __( 'Health &amp; Education Cess (%)', 'advisers-calculators' ), 'number', $s['default_cess_rate'], 0.01, 0.01 ),
				),
			),
			'capital-gains' => array(
				'label'  => __( 'Capital Gains Tax Calculator', 'advisers-calculators' ),
				'note'   => __( 'Assumes listed equity / equity mutual fund treatment (&gt;12 months = long-term). Not tax advice.', 'advisers-calculators' ),
				'fields' => array(
					'purchaseValue' => array( __( 'Purchase Value', 'advisers-calculators' ), 'number', 100000, 1, 1 ),
					'saleValue'     => array( __( 'Sale Value', 'advisers-calculators' ), 'number', 180000, 1, 1 ),
					'holdingMonths' => array( __( 'Holding Period (Months)', 'advisers-calculators' ), 'number', 18, 1, 1 ),
					'ltcgRate'      => array( __( 'LTCG Rate — Equity (%)', 'advisers-calculators' ), 'number', $s['default_ltcg_rate'], 0.01, 0.01 ),
					'stcgRate'      => array( __( 'STCG Rate — Equity (%)', 'advisers-calculators' ), 'number', $s['default_stcg_rate'], 0.01, 0.01 ),
					'exemption'     => array( __( 'LTCG Exemption Limit (₹/year)', 'advisers-calculators' ), 'number', 125000, 0, 1 ),
				),
			),

			// ---------------- Insurance ----------------
			'life-insurance' => array(
				'label'  => __( 'Life Insurance Maturity Calculator', 'advisers-calculators' ),
				'fields' => array(
					'sumAssured' => array( __( 'Sum Assured', 'advisers-calculators' ), 'number', 1000000, 1, 1 ),
					'premium'    => array( __( 'Annual Premium', 'advisers-calculators' ), 'number', 25000, 1, 1 ),
					'years'      => array( __( 'Policy Term (Years)', 'advisers-calculators' ), 'number', 20, 1, 1 ),
					'bonusRate'  => array( __( 'Expected Annual Bonus Rate (%)', 'advisers-calculators' ), 'number', $s['default_rate_annuity'], 0.01, 0.01 ),
				),
			),
			'term-life' => array(
				'label'  => __( 'Term Life Policy Calculator (Human Life Value)', 'advisers-calculators' ),
				'fields' => array(
					'annualIncome'  => array( __( 'Current Annual Income', 'advisers-calculators' ), 'number', 800000, 1, 1 ),
					'age'           => array( __( 'Current Age', 'advisers-calculators' ), 'number', 30, 1, 1 ),
					'retireAge'     => array( __( 'Expected Retirement Age', 'advisers-calculators' ), 'number', 60, 1, 1 ),
					'existingCover' => array( __( 'Existing Life Cover (if any)', 'advisers-calculators' ), 'number', 0, 0, 1 ),
				),
			),
			'endowment' => array(
				'label'  => __( 'Endowment Policy Return Calculator', 'advisers-calculators' ),
				'fields' => array(
					'premium'    => array( __( 'Annual Premium', 'advisers-calculators' ), 'number', 30000, 1, 1 ),
					'years'      => array( __( 'Policy Term (Years)', 'advisers-calculators' ), 'number', 15, 1, 1 ),
					'returnRate' => array( __( 'Expected Annual Return (%)', 'advisers-calculators' ), 'number', 5.5, 0.01, 0.01 ),
				),
			),
			'pli' => array(
				'label'  => __( 'Postal Life Insurance (PLI) Estimator', 'advisers-calculators' ),
				'note'   => __( 'Approximate — actual premiums depend on age and policy type. Use the official India Post calculator for an exact quote.', 'advisers-calculators' ),
				'fields' => array(
					'sumAssured' => array( __( 'Sum Assured', 'advisers-calculators' ), 'number', 500000, 1, 1 ),
					'ratePer1000'=> array( __( 'Premium Rate per ₹1000/year', 'advisers-calculators' ), 'number', $s['default_pli_rate'], 0.01, 0.01 ),
				),
			),
			'rpli' => array(
				'label'  => __( 'Rural PLI (RPLI) Estimator', 'advisers-calculators' ),
				'note'   => __( 'Approximate — use the official India Post calculator for an exact quote.', 'advisers-calculators' ),
				'fields' => array(
					'sumAssured' => array( __( 'Sum Assured', 'advisers-calculators' ), 'number', 300000, 1, 1 ),
					'ratePer1000'=> array( __( 'Premium Rate per ₹1000/year', 'advisers-calculators' ), 'number', $s['default_rpli_rate'], 0.01, 0.01 ),
				),
			),
			'pmjjby' => array(
				'label'  => __( 'PM Jeevan Jyoti Bima Yojana Overview', 'advisers-calculators' ),
				'note'   => __( 'Fixed ₹2,00,000 cover for a fixed ₹436/year premium, ages 18–50 (renewable to 55).', 'advisers-calculators' ),
				'fields' => array(
					'years' => array( __( 'Number of Years Enrolled', 'advisers-calculators' ), 'number', 1, 1, 1 ),
				),
			),
			'pmsby' => array(
				'label'  => __( 'PM Suraksha Bima Yojana Overview', 'advisers-calculators' ),
				'note'   => __( 'Fixed ₹2,00,000 accidental cover for a fixed ₹20/year premium, ages 18–70.', 'advisers-calculators' ),
				'fields' => array(
					'years' => array( __( 'Number of Years Enrolled', 'advisers-calculators' ), 'number', 1, 1, 1 ),
				),
			),

			// ---------------- Bonds ----------------
			'frsb' => array(
				'label'  => __( 'Floating Rate Savings Bonds Calculator', 'advisers-calculators' ),
				'note'   => __( 'Rate resets every 6 months in line with NSC rate + spread; actual payouts will vary.', 'advisers-calculators' ),
				'fields' => array(
					'principal' => array( __( 'Investment Amount', 'advisers-calculators' ), 'number', 100000, 1, 1 ),
					'rate'      => array( __( 'Current Floating Rate (%)', 'advisers-calculators' ), 'number', $s['default_frsb_rate'], 0.01, 0.01 ),
					'years'     => array( __( 'Tenure (Years)', 'advisers-calculators' ), 'number', 7, 1, 1 ),
				),
			),
			'sgb' => array(
				'label'  => __( 'Sovereign Gold Bond Calculator', 'advisers-calculators' ),
				'note'   => __( 'Redemption value tracks the gold price at maturity — unpredictable. Capital gains at maturity are tax-exempt for individuals.', 'advisers-calculators' ),
				'fields' => array(
					'principal'   => array( __( 'Investment Amount', 'advisers-calculators' ), 'number', 100000, 1, 1 ),
					'interestRate'=> array( __( 'Annual Interest Rate (%)', 'advisers-calculators' ), 'number', $s['default_sgb_rate'], 0.01, 0.01 ),
					'goldGrowth'  => array( __( 'Assumed Gold Price Growth (%/yr)', 'advisers-calculators' ), 'number', $s['default_sgb_gold_growth'], 0.01, 0.01 ),
					'years'       => array( __( 'Holding Period (Years)', 'advisers-calculators' ), 'number', 8, 1, 1 ),
				),
			),
			'bond-54ec' => array(
				'label'  => __( '54EC Capital Gains Bonds Calculator', 'advisers-calculators' ),
				'note'   => __( '5-year lock-in; exempts invested capital gain up to ₹50,00,000. Interest earned is taxable.', 'advisers-calculators' ),
				'fields' => array(
					'principal' => array( __( 'Investment Amount', 'advisers-calculators' ), 'number', 5000000, 1, 1 ),
					'rate'      => array( __( 'Interest Rate (%)', 'advisers-calculators' ), 'number', $s['default_54ec_rate'], 0.01, 0.01 ),
				),
			),

			// ---------------- General ----------------
			'compound-interest' => array(
				'label'  => __( 'Compound Interest Calculator', 'advisers-calculators' ),
				'fields' => array(
					'principal'    => array( __( 'Principal', 'advisers-calculators' ), 'number', 100000, 1, 1 ),
					'rate'         => array( __( 'Annual Interest Rate (%)', 'advisers-calculators' ), 'number', $s['default_compound_rate'], 0.01, 0.01 ),
					'years'        => array( __( 'Duration (Years)', 'advisers-calculators' ), 'number', 5, 1, 1 ),
					'compounding'  => array( __( 'Compoundings per Year (1/2/4/12)', 'advisers-calculators' ), 'number', 4, 1, 1 ),
				),
			),
			'simple-interest' => array(
				'label'  => __( 'Simple Interest Calculator', 'advisers-calculators' ),
				'fields' => array(
					'principal' => array( __( 'Principal', 'advisers-calculators' ), 'number', 100000, 1, 1 ),
					'rate'      => array( __( 'Annual Interest Rate (%)', 'advisers-calculators' ), 'number', $s['default_simple_rate'], 0.01, 0.01 ),
					'years'     => array( __( 'Duration (Years)', 'advisers-calculators' ), 'number', 5, 1, 1 ),
				),
			),
			'inflation' => array(
				'label'  => __( 'Inflation Calculator', 'advisers-calculators' ),
				'fields' => array(
					'amount' => array( __( 'Current Amount / Cost', 'advisers-calculators' ), 'number', 100000, 1, 1 ),
					'rate'   => array( __( 'Assumed Inflation Rate (%)', 'advisers-calculators' ), 'number', $s['default_inflation'], 0.01, 0.01 ),
					'years'  => array( __( 'Duration (Years)', 'advisers-calculators' ), 'number', 10, 1, 1 ),
				),
			),
		);
	}

	/**
	 * Render the `[advisers_calculator]` shortcode.
	 *
	 * With no `type` attribute, renders the full category-tabbed switcher
	 * for every enabled calculator. With `type="slug"`, renders just that
	 * one calculator (or an error message if the slug is unknown or its
	 * calculator has been disabled in settings).
	 *
	 * @since 1.0.0
	 *
	 * @param array $atts {
	 *     Shortcode attributes.
	 *
	 *     @type string $type Calculator slug to render alone. Default '' (renders the switcher).
	 * }
	 * @return string Shortcode HTML output.
	 */
	public function render_shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'type' => '',
			),
			$atts,
			'advisers_calculator'
		);

		$settings   = Advisers_Calculator_Settings::get_settings();
		$all_fields = $this->get_field_config();
		$enabled    = $settings['enabled'];
		$type       = sanitize_key( $atts['type'] );

		ob_start();

		echo '<div class="advisers-calculator-wrap">';

		if ( '' !== $type ) {
			if ( ! isset( $all_fields[ $type ] ) || ! in_array( $type, $enabled, true ) ) {
				echo '<p class="advisers-calculator-error">' . esc_html__( 'This calculator is not available.', 'advisers-calculators' ) . '</p>';
				echo '</div>';
				return ob_get_clean();
			}
			$this->render_single_calculator( $type, $all_fields[ $type ] );
		} else {
			$this->render_categorized_calculators( $all_fields, $enabled );
		}

		echo '</div>';

		return ob_get_clean();
	}

	/**
	 * Render a single calculator block: its input form and the (initially
	 * empty) result/chart containers that the front-end script fills in.
	 *
	 * @since 1.0.0
	 *
	 * @param string $type   Calculator slug.
	 * @param array  $config Field configuration for the calculator, as
	 *                       returned by get_field_config() for this slug.
	 * @return void
	 */
	private function render_single_calculator( $type, $config ) {
		$uid = 'mc-' . $type . '-' . wp_unique_id();
		?>
		<div class="advisers-calculator-box" id="<?php echo esc_attr( $uid ); ?>" data-calc-type="<?php echo esc_attr( $type ); ?>">
			<h3 class="advisers-calculator-title"><?php echo esc_html( $config['label'] ); ?></h3>
			<?php if ( ! empty( $config['note'] ) ) : ?>
				<p class="advisers-calculator-note"><?php echo wp_kses_post( $config['note'] ); ?></p>
			<?php endif; ?>
			<form class="advisers-calculator-form" onsubmit="return false;">
				<?php foreach ( $config['fields'] as $key => $field ) :
					list( $label, $input_type, $default, $min, $step ) = $field;
					$field_id = $uid . '-' . $key;
					?>
					<div class="advisers-calculator-field">
						<label for="<?php echo esc_attr( $field_id ); ?>"><?php echo esc_html( $label ); ?></label>
						<input
							type="<?php echo esc_attr( $input_type ); ?>"
							id="<?php echo esc_attr( $field_id ); ?>"
							name="<?php echo esc_attr( $key ); ?>"
							data-field="<?php echo esc_attr( $key ); ?>"
							value="<?php echo esc_attr( $default ); ?>"
							min="<?php echo esc_attr( $min ); ?>"
							step="<?php echo esc_attr( $step ); ?>"
							inputmode="decimal"
						/>
					</div>
				<?php endforeach; ?>
				<button type="button" class="advisers-calculator-submit advisers-calculator-calculate-btn">
					<?php esc_html_e( 'Calculate', 'advisers-calculators' ); ?>
				</button>
			</form>

			<div class="advisers-calculator-results" aria-live="polite">
				<div class="advisers-calculator-summary"></div>
				<div class="advisers-calculator-chart-wrap">
					<canvas class="advisers-calculator-chart" width="280" height="280"></canvas>
					<ul class="advisers-calculator-legend"></ul>
				</div>
				<div class="advisers-calculator-table-wrap"></div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render category tabs; clicking a category reveals a dropdown scoped to
	 * that category's calculators, and only the chosen calculator's panel is
	 * shown at any time. Panels also carry an inline `display:none` so they
	 * stay hidden even if a stale/cached copy of the CSS is being served.
	 *
	 * @since 1.2.0
	 *
	 * @param array $all_fields Field config for every calculator type, as
	 *                          returned by get_field_config().
	 * @param array $enabled    List of enabled calculator slugs.
	 * @return void
	 */
	private function render_categorized_calculators( $all_fields, $enabled ) {
		$uid        = 'mc-picker-' . wp_unique_id();
		$categories = Advisers_Calculator_Settings::get_calculator_categories();
		$first_cat  = true;
		?>
		<div class="advisers-calculator-picker" id="<?php echo esc_attr( $uid ); ?>">
			<div class="advisers-calculator-cat-nav" role="tablist">
				<?php foreach ( $categories as $cat_label => $calcs ) :
					$has_enabled = false;
					foreach ( $calcs as $slug => $label ) {
						if ( in_array( $slug, $enabled, true ) && isset( $all_fields[ $slug ] ) ) {
							$has_enabled = true;
							break;
						}
					}
					if ( ! $has_enabled ) {
						continue;
					}
					$cat_slug = sanitize_title( $cat_label );
					?>
					<button type="button" class="advisers-calculator-cat-btn<?php echo $first_cat ? ' is-active' : ''; ?>" data-cat="<?php echo esc_attr( $cat_slug ); ?>">
						<?php echo esc_html( $cat_label ); ?>
					</button>
					<?php $first_cat = false; ?>
				<?php endforeach; ?>
			</div>

			<?php
			$first_cat = true;
			foreach ( $categories as $cat_label => $calcs ) :
				$cat_calcs = array();
				foreach ( $calcs as $slug => $label ) {
					if ( in_array( $slug, $enabled, true ) && isset( $all_fields[ $slug ] ) ) {
						$cat_calcs[ $slug ] = $all_fields[ $slug ];
					}
				}
				if ( empty( $cat_calcs ) ) {
					continue;
				}
				$cat_slug   = sanitize_title( $cat_label );
				$select_id  = $uid . '-' . $cat_slug . '-select';
				?>
				<div class="advisers-calculator-cat-panel<?php echo $first_cat ? ' is-active' : ''; ?>"
					data-cat-panel="<?php echo esc_attr( $cat_slug ); ?>"
					<?php echo $first_cat ? '' : 'style="display:none;"'; ?>>

					<label class="advisers-calculator-picker-label" for="<?php echo esc_attr( $select_id ); ?>">
						<?php esc_html_e( 'Choose a calculator', 'advisers-calculators' ); ?>
					</label>
					<select class="advisers-calculator-select" id="<?php echo esc_attr( $select_id ); ?>">
						<option value="" selected="selected" disabled="disabled">
							<?php esc_html_e( '— Select a calculator —', 'advisers-calculators' ); ?>
						</option>
						<?php foreach ( $cat_calcs as $slug => $config ) : ?>
							<option value="<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $config['label'] ); ?></option>
						<?php endforeach; ?>
					</select>

					<div class="advisers-calculator-empty-state">
						<?php esc_html_e( 'Pick a calculator above to get started.', 'advisers-calculators' ); ?>
					</div>

					<div class="advisers-calculator-panels">
						<?php foreach ( $cat_calcs as $slug => $config ) : ?>
							<div class="advisers-calculator-single-panel" data-calc-panel="<?php echo esc_attr( $slug ); ?>" style="display:none;">
								<?php $this->render_single_calculator( $slug, $config ); ?>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
				<?php $first_cat = false; ?>
			<?php endforeach; ?>
		</div>
		<?php
	}
}
