=== Advisers Calculators ===
Contributors: 1myadvisers
Donate link: https://myadvisers.in
Tags: financial calculator, emi calculator, sip calculator, loan calculator, tax calculator
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 2.0.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

All-in-one financial toolkit for India — 40 calculators across loans, savings, mutual funds, retirement, tax, insurance, and bonds.

== Description ==

**Advisers Calculators** embeds interactive financial calculators on any page via shortcode, organized into categories, all managed from a single "Advisers Calculators" admin menu:

*Bank & Loans*
* EMI Calculator
* Home Loan EMI Calculator
* Home Loan Eligibility Calculator
* Fixed Deposit — TDR (Interest Payout)
* Fixed Deposit — STDR (Cumulative)
* Recurring Deposit Calculator

*Post Office & Govt. Savings*
* PPF Calculator
* Sukanya Samriddhi Account Calculator
* Senior Citizens Savings Scheme Calculator
* Kisan Vikas Patra Calculator
* Monthly Income Scheme Calculator
* Post Office Time Deposit Calculator
* National Savings Certificate Calculator

*Mutual Funds*
* SIP Calculator
* ELSS Calculator
* SWP Calculator
* Lumpsum Investment Calculator

*Retirement*
* Retirement Corpus Calculator
* NPS Calculator
* EPF Calculator
* VPF Calculator
* Atal Pension Yojana Calculator
* PM-SYM Calculator
* PM Vaya Vandana Yojana Calculator
* Gratuity Calculator

*Tax*
* Income Tax Calculator (Old &amp; New Regime)
* Capital Gains Tax Calculator

*Insurance*
* Life Insurance Maturity Calculator
* Term Life Policy Calculator (Human Life Value)
* Endowment Policy Return Calculator
* Postal Life Insurance (PLI) Estimator
* Rural PLI (RPLI) Estimator
* PM Jeevan Jyoti Bima Yojana Overview
* PM Suraksha Bima Yojana Overview

*Bonds*
* Floating Rate Savings Bonds Calculator
* Sovereign Gold Bond Calculator
* 54EC Capital Gains Bonds Calculator

*General*
* Compound Interest Calculator
* Simple Interest Calculator
* Inflation Calculator

Use `[advisers_calculator]` to show every enabled calculator in a category-tabbed switcher, or `[advisers_calculator type="sip"]` to embed just one.

All math runs client-side in vanilla JS with HTML5 Canvas charts — no external calls, no third-party libraries.

= Disclaimer =

Calculator results are estimates for illustrative and educational purposes only, using standard simplified financial formulas. This does not constitute financial, investment, insurance, or tax advice — always confirm with your bank, fund house, insurer, or a qualified advisor.

== Installation ==

1. Upload the `advisers-calculators` folder to `/wp-content/plugins/`, or install via **Plugins → Add New → Upload Plugin**.
2. Activate through the **Plugins** menu.
3. Go to **Advisers Calculators → Calculators** to enable individual calculators, set default rates, brand color, and currency symbol.
4. Add shortcodes to any page:
   * `[advisers_calculator type="emi"]` — a specific calculator (see the settings page for every slug)
   * `[advisers_calculator]` — a tabbed switcher with every enabled calculator

== Frequently Asked Questions ==

= How do I show a specific calculator? =

Use `[advisers_calculator type="slug"]`, replacing `slug` with one of: `emi`, `sip`, `ppf`, `fd`, `rd`, `life-insurance`, `annuity`, `retirement`, `term-life`, `endowment`, `home-loan-emi`, `home-loan-eligibility`, `lumpsum`. Every shortcode is also listed on the settings page.

= Can I disable calculators I don't need? =

Yes. Untick any calculator on the Calculators settings page — its shortcode will then show a "not available" message instead of the calculator.

= Does this plugin make any external calls? =

No. Every calculation runs entirely client-side in the visitor's browser using vanilla JavaScript; no data is sent to any external server.

== Changelog ==

= 2.0.1 =
* Fixed Plugin URI (was pointing to a 404 page — now points to the main site).
* Renamed the localized front-end JavaScript global from the generic `myCalculatorData` to `advisersCalculatorData`, per WordPress.org Plugins Team feedback on unique naming (this was leftover from the pre-rename code and wasn't caught by the PHP identifier rename since it uses camelCase with no separator).

= 2.0.0 =
* Renamed the plugin from "My Calculators" to **Advisers Calculators** (slug: `advisers-calculators`) at the request of the WordPress.org Plugins Team, whose review found the previous name built entirely from common words ("my" + "calculators") and not distinctive enough to list.
* Text domain, plugin header, admin menu, all translation strings, CSS classes, JS hooks, and asset filenames updated to match.
* Shortcode renamed from `[my_calculator]` to `[advisers_calculator]`.
* Removed now-unnecessary back-compat constant aliases left over from the old name.

= 1.2.2 =
* Hardened activation for multisite: network-activating the plugin now runs setup on every site in the network (previously only the current site), via the standard `$network_wide` + `get_sites()` pattern.
* New sites created after network activation now get the same default settings automatically (`wp_initialize_site`).

= 1.2.1 =
* Fixed the plugin's text domain to match its WordPress.org slug (`advisers-calculator`) across all translation strings, resolving `TextDomainMismatch` warnings from the Plugin Check tool.
* Updated "Tested up to" to WordPress 7.1.
* Shortened the readme's short description to meet the 150-character limit.
* Removed leftover references to a "Credit Score Checker" module (readme sections, an admin settings-page link, and an uninstall cleanup call) that were not part of this package.
* Hooked up a previously-unused upgrade routine (`advisers_calculators_maybe_upgrade()`) to `plugins_loaded` so future version-based upgrades run automatically.
* Added complete inline documentation (docblocks) across all PHP files per the WordPress PHP documentation standards.

= 1.2.0 =
* Fixed a display bug where every calculator in a category could appear stacked on the page at once instead of just the selected one.
* Redesigned the `[advisers_calculator]` switcher: category tabs at the top, then a dropdown scoped to that category's calculators — selecting one shows only that calculator.
* Hardened the "only one calculator visible" behavior with both an inline style and a `!important` CSS rule, so it holds even under aggressive page caching.

= 1.1.0 =
* Expanded from 13 to 40 calculators, adding Post Office & Govt. Savings (SSA, SCSS, KVP, MIS, Time Deposit, NSC), Mutual Funds (ELSS, SWP), Retirement (NPS, EPF, VPF, Atal Pension Yojana, PM-SYM, PM Vaya Vandana Yojana, Gratuity), Tax (Income Tax with Old/New regime, Capital Gains), Insurance (PLI, RPLI, PMJJBY, PMSBY), Bonds (Floating Rate Savings Bonds, Sovereign Gold Bond, 54EC), and General (Compound Interest, Simple Interest, Inflation).
* Split Fixed Deposit into TDR (interest payout) and STDR (cumulative) variants to match how banks actually offer them.
* Reorganized the `[advisers_calculator]` switcher into category tabs (Bank & Loans, Post Office & Govt. Savings, Mutual Funds, Retirement, Tax, Insurance, Bonds, General) instead of one long flat list.
* Redesigned the front-end styling: refreshed color system, highlighted primary result, polished cards/buttons/charts, better mobile spacing.
* Reorganized the settings page into card sections with rates grouped by category.

= 1.0.0 =
* Merged the standalone "Advisers Calculator" and "My Credits" plugins into one package with a unified "Advisers Calculators" admin menu, shared text domain, and cross-linked settings pages.

== Upgrade Notice ==

= 2.0.0 =
Plugin renamed to Advisers Calculators. If you're updating from "My Calculators," the shortcode changed from [my_calculator] to [advisers_calculator] — update any pages using the old tag.

= 1.2.2 =
Multisite activation hardening only — no changes to shortcodes, settings, or saved data on existing single-site installs.

= 1.2.1 =
Internal fixes only (text domain, readme compliance, documentation) — no changes to shortcodes, settings, or saved data.

= 1.1.0 =
Adds 27 new calculators and a redesigned interface. Existing shortcodes and saved settings continue to work unchanged.

= 1.0.0 =
First release of the merged plugin. If you previously ran "Advisers Calculator" and/or "My Credits" separately, deactivate and delete those, then install this package — your saved settings (option names are unchanged) will carry over automatically.
