<?php
/**
 * Brooks Law Essentials — baked-in redirect rules (cleaned Aug 18, 2026).
 *
 * These ship with the plugin as the canonical rule set. On upgrade to 2.0
 * the stored rules are replaced ONCE with this set (the previous value is
 * backed up to the brooks_ess_redirects_backup_101 option). After that,
 * the Settings textarea is the live copy and can be edited freely.
 *
 * @package Brooks_Essentials
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The baked rule set, one rule per line: /source => /destination
 *
 * @return string
 */
function brooks_ess_baked_redirects() {
	return <<<'BROOKS_ESS_RULES'
# === Brooks Law Essentials baked rules (v2.0, cleaned 2026-08-18; 5.2.1: 3 attorney-profile destinations corrected, dead /robert-brooks/ rule removed) ===
# All destinations verified against the 2026-08-18 WordPress export.
# NOTE: /blog/ requires a Posts page (Settings → Reading) — create a page
# with slug "blog" and assign it, or these /blog/ rules land on a 404.

# --- current-site legacy urls ---
/criminal-defense/ => /criminal-defense-2/
/drug-offense-2/ => /drug-offense/
/contact/ => /contact-updated/
/contact-us => /contact-updated/
/contact_us => /contact-updated/
/contactus => /contact-updated/
/resources/ => /resources-updated/
/business-litigation/ => /business-litigation-2/
/attorneys/ => /firm-profile-3/
/our-attorneys/ => /firm-profile-3/
/careers/ => /contact-updated/
/about => /firm-profile-3/
/about-us => /firm-profile-3/
/team => /firm-profile-3/
/services => /criminal-defense-2/
/white-collar-crime => /white-collar-crime-defense/
/employment-discrimination => /civil-litigation/
/animal-cruelty => /criminal-defense-2/
/civil-asset-forfeiture => /drug-offense/
/legal-blog => /blog/
/news-room => /blog/
/criminal-defense/expungement => /expungement/
/criminal-defense/robert-brooks => /firm-profile-3/
/criminal-defense/beth-brooks => /firm-profile-3/
/criminal-defense/criminal-lawyer-patrick-brooks => /patrick-brooks-profile/

# --- old site structure (pre-rebuild urls google still sends) ---
/memphis-criminal-attorney => /criminal-defense-2/
/memphis-criminal-attorney/criminal-defense => /criminal-defense-2/
/memphis-criminal-attorney/criminal-defense/assault => /assault/
/memphis-criminal-attorney/criminal-defense/aggravated-robbery => /criminal-defense-2/
/memphis-criminal-attorney/criminal-defense/drug-offense => /drug-offense/
/memphis-criminal-attorney/criminal-defense/drug-offense/drug-charge => /drug-offense/
/memphis-criminal-attorney/criminal-defense/drug-charge/photo-2 => /drug-offense/
/memphis-criminal-attorney/criminal-defense/dui-defense => /dui/
/memphis-criminal-attorney/criminal-defense/dui-defense/dsc_8941 => /dui/
/memphis-criminal-attorney/criminal-defense/disorderly-conduct => /criminal-defense-2/
/memphis-criminal-attorney/criminal-defense/warrant => /warrant/
/memphis-criminal-attorney/criminal-defense/selling-alcohol-to-minor => /selling-alcohol-to-minor/
/memphis-criminal-attorney/criminal-defense/probation-violation => /probation-violation/
/memphis-criminal-attorney/criminal-defense/order-protection => /order-of-protection/
/memphis-criminal-attorney/criminal-defense/federal-criminal-defense => /federal-criminal-defense/
/memphis-criminal-attorney/criminal-defense/unlawful-weapon => /criminal-defense-2/
/memphis-criminal-attorney/criminal-defense/suspended-drivers-license => /suspended-license/
/memphis-criminal-attorney/criminal-defense/trespass => /criminal-defense-2/
/memphis-criminal-attorney/criminal-defense/expungements => /expungement/
/memphis-criminal-attorney/criminal-defense/burglary => /criminal-defense-2/
/memphis-criminal-attorney/criminal-defense/white-collar-crime => /white-collar-crime-defense/
/memphis-criminal-attorney/criminal-defense/public-intoxication => /criminal-defense-2/
/memphis-criminal-attorney/criminal-defense/stalking => /criminal-defense-2/
/memphis-criminal-attorney/criminal-defense/prostitution => /criminal-defense-2/
/memphis-criminal-attorney/criminal-defense/criminal-lawyer-patrick-brooks => /patrick-brooks-profile/
/memphis-criminal-attorney/criminal-defense/criminal-appeals => /criminal-appeal/
/memphis-criminal-attorney/criminal-defense-lawyers/criminal-appeals => /criminal-appeal/
/memphis-criminal-attorney/criminal-defense__trashed/public-intoxication => /criminal-defense-2/
/memphis-criminal-attorney/criminal-defense__trashed/drug-offense__trashed/drug-charge => /drug-offense/
/memphis-criminal-attorney/criminal-defense__trashed/burglary => /criminal-defense-2/
/memphis-criminal-attorney/criminal-defense__trashed/dui-defense => /dui/
/memphis-criminal-attorney/criminal-defense-lawyers/drug-charge => /drug-offense/
/memphis-criminal-attorney/criminal-defense-lawyers/federal-criminal-defense => /federal-criminal-defense/
/memphis-criminal-attorney/criminal-defense-lawyers/domestic-assault => /domestic-violence/
/memphis-criminal-attorney/criminal-defense-lawyers/traffic-tickets => /speeding-tickets/
/memphis-criminal-attorney/traffic-ticket => /traffic/
/memphis-criminal-attorney/traffic-ticket/cdl-ticket => /cdl-defense/
/memphis-criminal-attorney/traffic-ticket/speeding-ticket => /speeding-tickets/
/memphis-criminal-attorney/traffic-ticket/financial-responsibility-proof-insurance => /traffic/
/memphis-criminal-attorney/traffic-ticket/drivers-license => /cdl-defense/
/memphis-criminal-attorney/automobile-accidents => /personal-injury/
/memphis-criminal-attorney/personal-injury => /personal-injury/
/memphis-criminal-attorney/public-intoxication => /criminal-defense-2/
/memphis-criminal-attorney/burglary => /theft/
/memphis-criminal-attorney/theft => /theft/
/memphis-criminal-attorney/stalking => /criminal-defense-2/
/memphis-criminal-attorney/prostitution => /criminal-defense-2/
/memphis-criminal-attorney/disorderly-conduct => /criminal-defense-2/
/memphis-criminal-attorney/drug-charge => /drug-offense/
/memphis-criminal-attorney/selling-alcohol-to-minor => /selling-alcohol-to-minor/
/memphis-criminal-attorney/suspended-drivers-license => /suspended-license/
/memphis-criminal-attorney/aggravated-robbery => /criminal-defense-2/
/memphis-criminal-attorney/business-litigation => /business-litigation-2/

# --- underscore variants + old dated blog archives ---
/memphis_criminal_attorney => /
/memphis_criminal_attorney/peabody-at-night => /
/memphis_criminal_attorney/criminal-attorneys-in-memphis => /
/memphis_criminal_attorney/tag/criminal-defense-attorney-memphis-tn => /criminal-defense-2/
/memphis_criminal_attorney/aggravated-assault-felony-drug-charge-misdemeanors => /assault/
/memphis_criminal_attorney/find-dui-attorney-memphis-tn => /dui/
/memphis_criminal_attorney/memphis-dui-lawyer => /dui/
/memphis_criminal_attorney/dui-memphis => /dui/
/memphis_criminal_attorney/speeding-tickets-memphis-tn => /speeding-tickets/
/memphis_criminal_attorney/marijuana => /drug-offense/
/memphis_criminal_attorney/2013/11/26 => /blog/
/memphis_criminal_attorney/2013/11/08 => /blog/
/2013/11 => /blog/
/2013/12 => /blog/
/2014/01 => /blog/
/2014/02 => /blog/
/2016/02 => /blog/
/2016/02/09 => /blog/

# --- v2.2 additions (2026-08-23): 404-log rounds 2-3 + dead -2 import artifacts ---
/memphis_criminal_attorney/tag/memphis-criminal-attorney => /criminal-defense-2/
/memphis_criminal_attorney/2014/04/02 => /blog/
/memphis-criminal-attorney/criminal-defense/dsc_2284-2 => /criminal-defense-2/
/memphis-criminal-attorney/criminal-defense__trashed/drug-offense/drug-charge => /drug-offense/
/blog/tag/nsa => /blog/
/blog/tag/week-civil-liberties => /blog/
/home => /
/archive => /blog/
/memphis_criminal_attorney/bench-warrants => /warrant/
/criminal-defense/federal-criminal-defense => /federal-criminal-defense/
/memphis-criminal-attorney/criminal-defense__trashed/selling-alcohol-to-minor => /selling-alcohol-to-minor/
/memphis_criminal_attorney/domestic-assault-attorney-memphis-tn => /domestic-violence/
/memphis_criminal_attorney/marijuana-charges-memphis-shelby-county-tn => /drug-offense/
/memphis_criminal_attorney/criminal-attorney-memphis-tn-patrick-brooks-handles-dui-drugs-domestic-violence-traffic-tickets => /criminal-defense-2/
/memphis_criminal_attorney/find-criminal-attorneys-memphis-tn => /criminal-defense-2/
/memphis-criminal-attorney/trespass => /criminal-defense-2/
/memphis-criminal-attorney/theft/dsc_52430 => /theft/
/criminal-defense/beth-brooks/dsc_1689 => /beth-brooks-profile/
/memphis_criminal_attorney/dui => /dui/
/memphis_criminal_attorney/2013/12/13 => /blog/
/how-much-does-a-criminal-defense-lawyer-cost-memphis-2 => /how-much-does-a-criminal-defense-lawyer-cost-memphis/
/how-much-does-a-cdl-ticket-lawyer-cost-memphis-2 => /how-much-does-a-cdl-ticket-lawyer-cost-memphis/
/2013/09 => /blog/
/2013/12/27 => /blog/
/2014/04 => /blog/
/2014/06 => /blog/
/2015/08 => /blog/
BROOKS_ESS_RULES;
}

/**
 * One-time migration on upgrade to 2.0: replace the stored rules with the
 * baked set, backing up whatever was there. Runs once, guarded by a flag.
 */
function brooks_ess_maybe_migrate_redirects() {
	if ( get_option( 'brooks_ess_rules_migrated_20' ) ) {
		return;
	}

	$options = get_option( BROOKS_ESS_OPTION, array() );
	if ( ! is_array( $options ) ) {
		$options = array();
	}

	$old = isset( $options['redirects'] ) ? (string) $options['redirects'] : '';
	if ( '' !== trim( $old ) ) {
		update_option( 'brooks_ess_redirects_backup_101', $old, false );
	}

	$options['redirects'] = brooks_ess_baked_redirects();
	update_option( BROOKS_ESS_OPTION, $options );
	update_option( 'brooks_ess_rules_migrated_20', BROOKS_ESS_VERSION, false );
}
add_action( 'admin_init', 'brooks_ess_maybe_migrate_redirects', 1 );

/**
 * Parse a rule textarea into an ordered source => destination map.
 *
 * @param string $raw Rule text.
 * @return array
 */
function brooks_ess_parse_rule_text( $raw ) {
	$map = array();

	foreach ( preg_split( '/\r\n|\r|\n/', (string) $raw ) as $line ) {
		$line = trim( $line );

		if ( '' === $line || '#' === substr( $line, 0, 1 ) ) {
			continue;
		}

		$parts = preg_split( '/\s*(?:=>|,|\s)\s*/', $line, 2 );
		if ( ! is_array( $parts ) || count( $parts ) < 2 ) {
			continue;
		}

		$from = brooks_ess_normalize_path( $parts[0] );
		$to   = trim( (string) $parts[1] );

		if ( '/' === $from || '' === $to ) {
			continue;
		}

		$map[ $from ] = $to;
	}

	return $map;
}

/**
 * One-time re-sync on upgrade to 2.1: MERGE the baked set into the stored rules
 * instead of replacing them. Baked rules win for their own sources (so
 * corrected destinations propagate), while any rule an administrator added by
 * hand is preserved. Runs once, guarded by its own flag; the prior value is
 * backed up to brooks_ess_redirects_backup_21.
 */
function brooks_ess_maybe_resync_redirects() {
	if ( get_option( 'brooks_ess_rules_migrated_21' ) ) {
		return;
	}

	$options = get_option( BROOKS_ESS_OPTION, array() );
	if ( ! is_array( $options ) ) {
		$options = array();
	}

	$stored_raw = isset( $options['redirects'] ) ? (string) $options['redirects'] : '';

	// Start from what is stored (preserves order and manual additions),
	// then overlay the baked rules so baked destinations win for baked sources.
	$merged = brooks_ess_parse_rule_text( $stored_raw );
	foreach ( brooks_ess_parse_rule_text( brooks_ess_baked_redirects() ) as $from => $to ) {
		$merged[ $from ] = $to;
	}

	$lines = array( '# === Brooks Law Essentials rules (v2.1 re-sync ' . gmdate( 'Y-m-d' ) . ') ===' );
	foreach ( $merged as $from => $to ) {
		$lines[] = $from . ' => ' . $to;
	}

	if ( '' !== trim( $stored_raw ) ) {
		update_option( 'brooks_ess_redirects_backup_21', $stored_raw, false );
	}

	$options['redirects'] = implode( "\n", $lines );
	update_option( BROOKS_ESS_OPTION, $options );
	update_option( 'brooks_ess_rules_migrated_21', BROOKS_ESS_VERSION, false );
}
add_action( 'admin_init', 'brooks_ess_maybe_resync_redirects', 2 );


/**
 * One-time re-sync on upgrade to 2.2: MERGE the baked set (now including the
 * Aug-23 404-log rules) into the stored rules. Same semantics as the 2.1
 * re-sync: baked rules win for their own sources, manual rules preserved,
 * prior value backed up. Guarded by its own flag.
 */
function brooks_ess_maybe_resync_redirects_22() {
	if ( get_option( 'brooks_ess_rules_migrated_22' ) ) {
		return;
	}

	$options = get_option( BROOKS_ESS_OPTION, array() );
	if ( ! is_array( $options ) ) {
		$options = array();
	}

	$stored_raw = isset( $options['redirects'] ) ? (string) $options['redirects'] : '';

	$merged = brooks_ess_parse_rule_text( $stored_raw );
	foreach ( brooks_ess_parse_rule_text( brooks_ess_baked_redirects() ) as $from => $to ) {
		$merged[ $from ] = $to;
	}

	$lines = array( '# === Brooks Law Essentials rules (v2.2 re-sync ' . gmdate( 'Y-m-d' ) . ') ===' );
	foreach ( $merged as $from => $to ) {
		$lines[] = $from . ' => ' . $to;
	}

	if ( '' !== trim( $stored_raw ) ) {
		update_option( 'brooks_ess_redirects_backup_22', $stored_raw, false );
	}

	$options['redirects'] = implode( "\n", $lines );
	update_option( BROOKS_ESS_OPTION, $options );
	update_option( 'brooks_ess_rules_migrated_22', BROOKS_ESS_VERSION, false );
}
add_action( 'admin_init', 'brooks_ess_maybe_resync_redirects_22', 3 );
