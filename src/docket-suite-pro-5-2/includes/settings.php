<?php
/**
 * Brooks Essentials — settings screen.
 *
 * Built on the Settings API, so nonce verification and capability checks come
 * from WordPress core rather than being hand-rolled.
 *
 * @package Brooks_Essentials
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the settings page.
 */
function brooks_ess_admin_menu() {
	add_options_page(
		__( 'Brooks Law Firm — Site Essentials', 'docket-suite' ),
		__( 'Site Essentials', 'docket-suite' ),
		'manage_options',
		'brooks-essentials',
		'brooks_ess_render_page'
	);
}
add_action( 'admin_menu', 'brooks_ess_admin_menu' );

/**
 * Register the option and its sanitizer.
 */
function brooks_ess_register_settings() {
	register_setting(
		'brooks_ess_group',
		BROOKS_ESS_OPTION,
		array(
			'type'              => 'array',
			'sanitize_callback' => 'brooks_ess_sanitize',
			'default'           => brooks_ess_defaults(),
		)
	);
}
add_action( 'admin_init', 'brooks_ess_register_settings' );

/**
 * Sanitize everything coming out of the settings form.
 *
 * @param mixed $input Raw submitted values.
 * @return array
 */
function brooks_ess_sanitize( $input ) {
	$defaults = brooks_ess_defaults();
	$clean    = array();

	if ( ! is_array( $input ) ) {
		$input = array();
	}

	// Textarea: redirects — sanitized line by line so no filter can ever
	// collapse the whole box into one line and silently merge rules.
	$clean['redirects'] = isset( $input['redirects'] )
		? brooks_ess_sanitize_redirects( $input['redirects'] )
		: '';

	// Checkboxes.
	foreach ( array( 'log_404', 'disable_comments', 'disable_file_edit', 'disable_xmlrpc', 'delete_on_uninstall', 'robots_takeover', 'track_calls' ) as $key ) {
		$clean[ $key ] = ! empty( $input[ $key ] );
	}

	// Robots.txt.
	$rmode = isset( $input['robots_mode'] ) ? sanitize_text_field( $input['robots_mode'] ) : 'managed';
	$clean['robots_mode'] = in_array( $rmode, array( 'managed', 'virtual', 'append', 'leave' ), true ) ? $rmode : 'managed';
	$mode = isset( $input['ai_crawlers'] ) ? sanitize_text_field( $input['ai_crawlers'] ) : 'allow';
	$clean['ai_crawlers'] = in_array( $mode, array( 'allow', 'disallow', 'omit', 'leave' ), true ) ? $mode : 'allow';
	$clean['sitemap_url']  = isset( $input['sitemap_url'] ) ? esc_url_raw( trim( (string) $input['sitemap_url'] ) ) : '';
	$clean['robots_extra'] = isset( $input['robots_extra'] ) ? sanitize_textarea_field( (string) $input['robots_extra'] ) : '';
	delete_transient( 'brooks_ess_url_ok_' . md5( $clean['sitemap_url'] ) );

	// Firm info fallbacks.
	foreach ( array( 'firm_name', 'firm_phone', 'firm_phone_link', 'firm_cell', 'firm_cell_link', 'firm_address', 'firm_city_state', 'firm_hours' ) as $key ) {
		$clean[ $key ] = isset( $input[ $key ] )
			? sanitize_text_field( $input[ $key ] )
			: $defaults[ $key ];
	}

	$clean['firm_email'] = isset( $input['firm_email'] )
		? sanitize_email( $input['firm_email'] )
		: $defaults['firm_email'];

	// Site verification. docket_verify_sanitize() accepts either the bare
	// token or the whole <meta> tag, because the whole tag is what every one
	// of these services actually shows you on screen.
	foreach ( array_keys( docket_verify_services() ) as $key ) {
		$clean[ $key ] = isset( $input[ $key ] ) ? docket_verify_sanitize( $input[ $key ] ) : '';
	}

	return $clean;
}

/**
 * Sanitize the redirects textarea one line at a time.
 *
 * Line endings are normalized first, then each line is cleaned on its own,
 * so a multi-rule list can never be merged into a single line by a
 * sanitizer. Blank lines are dropped; # comments are kept.
 *
 * @param string $raw Raw textarea value.
 * @return string
 */
function brooks_ess_sanitize_redirects( $raw ) {
	$raw   = str_replace( array( "\r\n", "\r" ), "\n", (string) $raw );
	$lines = array();

	foreach ( explode( "\n", $raw ) as $line ) {
		$line = trim( sanitize_text_field( $line ) );
		if ( '' !== $line ) {
			$lines[] = $line;
		}
	}

	return implode( "\n", $lines );
}

/**
 * Checkbox helper.
 *
 * @param string $key   Setting key.
 * @param string $label Label text.
 * @param string $help  Optional description.
 */
function brooks_ess_checkbox( $key, $label, $help = '' ) {
	printf(
		'<p><label><input type="checkbox" name="%1$s[%2$s]" value="1" %3$s> %4$s</label>%5$s</p>',
		esc_attr( BROOKS_ESS_OPTION ),
		esc_attr( $key ),
		checked( (bool) brooks_ess_get( $key ), true, false ),
		esc_html( $label ),
		'' !== $help ? '<br><span class="description">' . esc_html( $help ) . '</span>' : ''
	);
}

/**
 * Text field helper.
 *
 * @param string $key   Setting key.
 * @param string $label Label text.
 */
function brooks_ess_text( $key, $label ) {
	printf(
		'<tr><th scope="row"><label for="brooks-ess-%2$s">%4$s</label></th><td><input type="text" class="regular-text" id="brooks-ess-%2$s" name="%1$s[%2$s]" value="%3$s"></td></tr>',
		esc_attr( BROOKS_ESS_OPTION ),
		esc_attr( $key ),
		esc_attr( brooks_ess_get( $key ) ),
		esc_html( $label )
	);
}

/**
 * Does a site-relative path resolve to published content?
 *
 * A bare url_to_postid() check is not enough: WordPress returns 0 for the page
 * assigned as the Posts page (Settings → Reading), and for hierarchical
 * pages it can miss in some permalink configurations. Check the three
 * things a redirect destination can legitimately be: a resolvable post,
 * a page by path, or the Posts page.
 *
 * @param string $path Normalised path, e.g. /blog or /dui.
 * @return bool
 */
function brooks_ess_path_is_live( $path ) {
	$path = trim( (string) $path, '/' );
	if ( '' === $path ) {
		return true;
	}

	if ( url_to_postid( home_url( '/' . $path . '/' ) ) > 0 ) {
		return true;
	}

	$page = get_page_by_path( $path, OBJECT, 'page' );
	if ( $page && 'publish' === $page->post_status ) {
		return true;
	}

	$posts_page = (int) get_option( 'page_for_posts' );
	if ( $posts_page > 0 ) {
		$posts_page_path = trim( (string) wp_parse_url( get_permalink( $posts_page ), PHP_URL_PATH ), '/' );
		if ( '' !== $posts_page_path && $posts_page_path === $path ) {
			return true;
		}
	}

	return false;
}

/**
 * Classify every non-comment line of the rule text.
 *
 * The old notice reported (lines − map entries) as "could not be read", which
 * also counted lines that parsed fine but collapsed onto an existing source —
 * e.g. /foo and /foo/, or two spellings of the same path. This tells the
 * truth: which lines are malformed, which are duplicates, which sources are
 * live pages the rule can never fire for, and which destinations do not
 * resolve to a published page or post.
 *
 * Only runs when the settings screen renders.
 *
 * @param string $raw Rule text.
 * @return array{malformed:array,duplicates:array,live_sources:array,dead_targets:array,lines:int,entries:int}
 */
function brooks_ess_rule_diagnostics( $raw ) {
	$out = array(
		'malformed'    => array(),
		'duplicates'   => array(),
		'live_sources' => array(),
		'dead_targets' => array(),
		'lines'        => 0,
		'entries'      => 0,
	);

	$seen = array();

	foreach ( preg_split( '/\r\n|\r|\n/', (string) $raw ) as $i => $line ) {
		$line = trim( $line );
		if ( '' === $line || '#' === substr( $line, 0, 1 ) ) {
			continue;
		}
		++$out['lines'];
		$n = $i + 1;

		$parts = preg_split( '/\s*(?:=>|,|\s)\s*/', $line, 2 );
		if ( ! is_array( $parts ) || count( $parts ) < 2 ) {
			$out['malformed'][] = array( $n, $line, __( 'no destination found — needs /old-page/ => /new-page/', 'docket-suite' ) );
			continue;
		}

		$from = brooks_ess_normalize_path( $parts[0] );
		$to   = trim( (string) $parts[1] );

		if ( '/' === $from ) {
			$out['malformed'][] = array( $n, $line, __( 'source normalises to the home page, which is never redirected', 'docket-suite' ) );
			continue;
		}
		if ( '' === $to ) {
			$out['malformed'][] = array( $n, $line, __( 'empty destination', 'docket-suite' ) );
			continue;
		}

		if ( isset( $seen[ $from ] ) ) {
			$out['duplicates'][] = array( $n, $line, sprintf( /* translators: %d: line number */ __( 'same source as line %d after normalising — harmless, the later line wins', 'docket-suite' ), $seen[ $from ] ) );
			continue;
		}
		$seen[ $from ] = $n;
		++$out['entries'];

		if ( function_exists( 'url_to_postid' ) ) {
			if ( brooks_ess_path_is_live( $from ) ) {
				$out['live_sources'][] = array( $n, $line, __( 'source is a published page; fallback rules only fire on a 404, so this never runs', 'docket-suite' ) );
			}
			if ( ! preg_match( '#^https?://#i', $to ) ) {
				$target_path = brooks_ess_normalize_path( $to );
				if ( '/' !== $target_path && ! brooks_ess_path_is_live( $target_path ) ) {
					$out['dead_targets'][] = array( $n, $line, __( 'destination does not resolve to a published page or post — visitors will land on a 404', 'docket-suite' ) );
				}
			}
		}
	}

	return $out;
}

/**
 * Render one diagnostics group as a small table.
 *
 * @param string $heading Group heading.
 * @param array  $rows    Rows of [line, text, note].
 * @param string $css_class notice-warning | notice-error | notice-info.
 */
function brooks_ess_render_rule_group( $heading, $rows, $css_class ) {
	if ( empty( $rows ) ) {
		return;
	}
	echo '<div class="notice ' . esc_attr( $css_class ) . ' inline" style="max-width:44em"><p><strong>' . esc_html( $heading ) . '</strong></p><table class="widefat striped"><tbody>';
	foreach ( $rows as $row ) {
		printf(
			'<tr><td style="width:4em">%1$s</td><td><code>%2$s</code><br><span class="description">%3$s</span></td></tr>',
			esc_html( 'L' . $row[0] ),
			esc_html( $row[1] ),
			esc_html( $row[2] )
		);
	}
	echo '</tbody></table></div>';
}

/**
 * Render the settings page.
 */
function brooks_ess_render_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$theme_active = function_exists( 'brooks_law_get_option' );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Brooks Law Firm — Site Essentials', 'docket-suite' ); ?></h1>

		<?php if ( isset( $_GET['brooks_ess_cleared'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Display-only flag, no state change. ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e( '404 log cleared.', 'docket-suite' ); ?></p></div>
		<?php endif; ?>

		<form method="post" action="options.php">
			<?php settings_fields( 'brooks_ess_group' ); ?>

			<h2><?php esc_html_e( 'Fallback redirects', 'docket-suite' ); ?></h2>
			<p class="description" style="max-width:44em">
				<?php esc_html_e( 'One per line, old path first: /old-page/ => /new-page/. These fire only when a page would otherwise show "not found," so they can never shadow a live page or conflict with a redirect plugin. Lines beginning with # are ignored.', 'docket-suite' ); ?>
			</p>
			<p>
				<textarea name="<?php echo esc_attr( BROOKS_ESS_OPTION ); ?>[redirects]" rows="8" class="large-text code" placeholder="# One rule per line, for example:&#10;# /old-page/ =&gt; /new-page/"><?php echo esc_textarea( brooks_ess_get( 'redirects' ) ); ?></textarea>
			</p>

			<?php
			$brooks_ess_map = brooks_ess_redirect_map();
			$brooks_ess_rule_lines = 0;
			foreach ( preg_split( '/\r\n|\r|\n/', (string) brooks_ess_get( 'redirects' ) ) as $brooks_ess_line ) {
				$brooks_ess_line = trim( $brooks_ess_line );
				if ( '' !== $brooks_ess_line && '#' !== substr( $brooks_ess_line, 0, 1 ) ) {
					$brooks_ess_rule_lines++;
				}
			}
			if ( empty( $brooks_ess_map ) ) :
				?>
				<p class="description"><strong><?php esc_html_e( 'No redirect rules are saved yet.', 'docket-suite' ); ?></strong> <?php esc_html_e( 'Gray text in the box above is only an example — it disappears when you type and is not a saved rule.', 'docket-suite' ); ?></p>
			<?php else : ?>
				<h4 style="margin-bottom:4px"><?php /* translators: %d: number of active redirect rules */ printf( esc_html( _n( '%d active rule', '%d active rules', count( $brooks_ess_map ), 'docket-suite' ) ), (int) count( $brooks_ess_map ) ); ?></h4>
				<table class="widefat striped" style="max-width:44em">
					<tbody>
					<?php foreach ( $brooks_ess_map as $brooks_ess_from => $brooks_ess_to ) : ?>
						<tr><td><code><?php echo esc_html( $brooks_ess_from ); ?></code></td><td>&#8594;</td><td><code><?php echo esc_html( $brooks_ess_to ); ?></code></td></tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
			<?php
			$brooks_ess_diag = brooks_ess_rule_diagnostics( brooks_ess_get( 'redirects' ) );
			brooks_ess_render_rule_group( __( 'Malformed lines — these are NOT active rules', 'docket-suite' ), $brooks_ess_diag['malformed'], 'notice-error' );
			brooks_ess_render_rule_group( __( 'Broken destinations — active rules that send visitors to a 404', 'docket-suite' ), $brooks_ess_diag['dead_targets'], 'notice-error' );
			brooks_ess_render_rule_group( __( 'Duplicate sources — parsed fine, collapsed into one rule', 'docket-suite' ), $brooks_ess_diag['duplicates'], 'notice-info' );
			brooks_ess_render_rule_group( __( 'Dead weight — sources that are live pages, so the rule can never fire', 'docket-suite' ), $brooks_ess_diag['live_sources'], 'notice-info' );
			?>

			<?php brooks_ess_checkbox( 'log_404', __( 'Keep a log of pages that were not found', 'docket-suite' ), __( 'Capped at 50 entries. Scanner noise is filtered out.', 'docket-suite' ) ); ?>

			<hr>

			<h2><?php esc_html_e( 'Robots.txt', 'docket-suite' ); ?></h2>
			<p class="description" style="max-width:44em">
				<?php esc_html_e( 'v3 writes and maintains a real robots.txt file in the site root (recommended on this stack: a static file is served directly by the web server, so Cloudflare and plugin layers can never flatten or mangle it again). Every line passes a built-in syntax linter before it is written.', 'docket-suite' ); ?>
			</p>
			<?php
			$rmode        = brooks_ess_get( 'robots_mode' );
			$ai           = brooks_ess_get( 'ai_crawlers' );
			$write_status = brooks_ess_robots_write_status();
			?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'Mode', 'docket-suite' ); ?></th>
					<td>
						<select name="<?php echo esc_attr( BROOKS_ESS_OPTION ); ?>[robots_mode]">
							<option value="managed" <?php selected( $rmode, 'managed' ); ?>><?php esc_html_e( 'Managed file — plugin writes & maintains a physical robots.txt (recommended)', 'docket-suite' ); ?></option>
							<option value="virtual" <?php selected( $rmode, 'virtual' ); ?>><?php esc_html_e( 'Virtual replace — clean WordPress-served robots.txt, no file written', 'docket-suite' ); ?></option>
							<option value="append" <?php selected( $rmode, 'append' ); ?>><?php esc_html_e( 'Virtual append — keep other output, add our rules after (legacy)', 'docket-suite' ); ?></option>
							<option value="leave" <?php selected( $rmode, 'leave' ); ?>><?php esc_html_e( 'Leave robots.txt alone', 'docket-suite' ); ?></option>
						</select>
						<?php if ( 'foreign' === $write_status ) : ?>
							<div class="notice notice-warning inline"><p>
								<?php esc_html_e( 'A physical robots.txt exists that this plugin did not create (this is where the broken "Disallow: Sitemap:" line lives). Tick the box below and Save to let the plugin replace it with a clean, linted file.', 'docket-suite' ); ?>
							</p></div>
							<p><label><input type="checkbox" name="<?php echo esc_attr( BROOKS_ESS_OPTION ); ?>[robots_takeover]" value="1" <?php checked( (bool) brooks_ess_get( 'robots_takeover' ) ); ?>> <?php esc_html_e( 'Yes — replace the existing robots.txt with the managed version', 'docket-suite' ); ?></label></p>
						<?php elseif ( 'unwritable' === $write_status ) : ?>
							<div class="notice notice-error inline"><p>
								<?php esc_html_e( 'The site root is not writable, so the managed file cannot be created. Fix permissions or switch to Virtual replace mode.', 'docket-suite' ); ?>
							</p></div>
						<?php elseif ( 'ok' === $write_status && brooks_ess_robots_is_ours() ) : ?>
							<p class="description">✓ <?php esc_html_e( 'Managed file is in place and self-heals daily if anything rewrites it.', 'docket-suite' ); ?></p>
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'AI crawlers', 'docket-suite' ); ?></th>
					<td>
						<select name="<?php echo esc_attr( BROOKS_ESS_OPTION ); ?>[ai_crawlers]">
							<option value="allow" <?php selected( $ai, 'allow' ); ?>><?php esc_html_e( 'Explicitly allow (firm stays quotable by AI assistants)', 'docket-suite' ); ?></option>
							<option value="disallow" <?php selected( $ai, 'disallow' ); ?>><?php esc_html_e( 'Block (opt out of training and citation)', 'docket-suite' ); ?></option>
							<option value="omit" <?php selected( in_array( $ai, array( 'omit', 'leave' ), true ) ? 'omit' : $ai, 'omit' ); ?>><?php esc_html_e( 'No explicit rules (default-allow applies anyway)', 'docket-suite' ); ?></option>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="be_sitemap"><?php esc_html_e( 'Sitemap URL', 'docket-suite' ); ?></label></th>
					<td>
						<input type="url" id="be_sitemap" name="<?php echo esc_attr( BROOKS_ESS_OPTION ); ?>[sitemap_url]" value="<?php echo esc_attr( (string) brooks_ess_get( 'sitemap_url' ) ); ?>" class="regular-text" placeholder="<?php echo esc_attr( home_url( '/sitemap_index.xml' ) ); ?>">
						<p class="description"><?php esc_html_e( 'Blank = auto-detect (your live /sitemap_index.xml is found automatically). The URL is verified once a day and dropped from robots.txt if it ever stops responding, so a dead sitemap is never advertised.', 'docket-suite' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="be_rextra"><?php esc_html_e( 'Extra rules (advanced)', 'docket-suite' ); ?></label></th>
					<td>
						<textarea id="be_rextra" name="<?php echo esc_attr( BROOKS_ESS_OPTION ); ?>[robots_extra]" rows="3" class="large-text code"><?php echo esc_textarea( (string) brooks_ess_get( 'robots_extra' ) ); ?></textarea>
						<p class="description"><?php esc_html_e( 'One directive per line. The linter silently drops anything invalid.', 'docket-suite' ); ?></p>
					</td>
				</tr>
			</table>

			<h3><?php esc_html_e( 'Preview — the exact file content', 'docket-suite' ); ?></h3>
			<pre style="background:#1d2327;color:#9fd3a0;padding:12px 16px;border-radius:4px;max-width:760px;overflow:auto;line-height:1.5;white-space:pre"><?php echo esc_html( brooks_ess_robots_content() ); ?></pre>
			<p class="description">
				<?php
				printf(
					/* translators: %s: robots.txt link. */
					esc_html__( 'Verify live at %s after saving (hard-refresh; Cloudflare may cache the old copy for a few minutes — purge its cache to see the change immediately).', 'docket-suite' ),
					'<a href="' . esc_url( home_url( '/robots.txt' ) ) . '" target="_blank" rel="noopener">' . esc_html( home_url( '/robots.txt' ) ) . '</a>'
				);
				?>
			</p>

			<hr>

			<h2><?php esc_html_e( 'Call & text tracking', 'docket-suite' ); ?></h2>
			<?php brooks_ess_checkbox( 'track_calls', __( 'Send call/text link clicks to Google Analytics 4', 'docket-suite' ), __( 'Fires call_click / text_click events with the number and page on every tel:/sms: link sitewide. Requires GA4 (gtag.js) already installed; silently inert otherwise. One tiny deferred listener, no vendor script.', 'docket-suite' ) ); ?>

			<hr>

			<h2><?php esc_html_e( 'Comments and hardening', 'docket-suite' ); ?></h2>
			<?php
			brooks_ess_checkbox( 'disable_comments', __( 'Turn off comments site-wide', 'docket-suite' ), __( 'Existing comments are hidden, not deleted.', 'docket-suite' ) );
			brooks_ess_checkbox( 'disable_file_edit', __( 'Block the built-in theme and plugin file editors', 'docket-suite' ), __( 'You can still edit files over SFTP.', 'docket-suite' ) );
			brooks_ess_checkbox( 'disable_xmlrpc', __( 'Turn off XML-RPC', 'docket-suite' ), __( 'Reduces brute-force surface. Leave off if you use the WordPress mobile app or Jetpack.', 'docket-suite' ) );
			?>

			<hr>

			<h2><?php esc_html_e( 'Search-engine verification', 'docket-suite' ); ?></h2>
			<p class="description" style="max-width:44em">
				<?php esc_html_e( 'Each of these services will offer you a file to upload to the web root, a DNS record, or a meta tag. Paste the meta tag here and you never have to put a file in the web root or remember what is in a DNS panel. You can paste the whole tag or just the code inside it — both work.', 'docket-suite' ); ?>
			</p>
			<table class="form-table" role="presentation">
				<?php
				foreach ( docket_verify_services() as $verify_key => $verify_service ) {
					brooks_ess_text( $verify_key, $verify_service[0] );
					printf(
						'<tr><td colspan="2" style="padding-top:0"><p class="description" style="margin-left:0">%s</p></td></tr>',
						esc_html( $verify_service[2] )
					);
				}
				?>
			</table>
			<p class="description" style="max-width:44em">
				<?php esc_html_e( 'These tags are printed on every page and stay in place whether or not any other SEO plugin is active — which is the point: a verification that disappears when a plugin is switched off is a verification you will lose without noticing.', 'docket-suite' ); ?>
			</p>

			<hr>

			<h2><?php esc_html_e( 'Firm information', 'docket-suite' ); ?></h2>
			<?php if ( $theme_active ) : ?>
				<div class="notice notice-info inline"><p>
					<?php esc_html_e( 'The Brooks Law theme is active, so its Customizer values are used and these fields are ignored. They exist so the shortcodes keep working if you ever change themes — fill them in as a backup.', 'docket-suite' ); ?>
				</p></div>
			<?php endif; ?>
			<table class="form-table" role="presentation">
				<?php
				brooks_ess_text( 'firm_name', __( 'Firm name', 'docket-suite' ) );
				brooks_ess_text( 'firm_phone', __( 'Office phone (display)', 'docket-suite' ) );
				brooks_ess_text( 'firm_phone_link', __( 'Office phone (dial format)', 'docket-suite' ) );
				brooks_ess_text( 'firm_cell', __( 'Criminal line (display)', 'docket-suite' ) );
				brooks_ess_text( 'firm_cell_link', __( 'Criminal line (dial format)', 'docket-suite' ) );
				brooks_ess_text( 'firm_email', __( 'Email', 'docket-suite' ) );
				brooks_ess_text( 'firm_address', __( 'Street address', 'docket-suite' ) );
				brooks_ess_text( 'firm_city_state', __( 'City, state, ZIP', 'docket-suite' ) );
				brooks_ess_text( 'firm_hours', __( 'Office hours', 'docket-suite' ) );
				?>
			</table>

			<h3><?php esc_html_e( 'Shortcodes', 'docket-suite' ); ?></h3>
			<p class="description"><?php esc_html_e( 'Paste these into any page or post instead of typing details by hand. Change the number once here and it updates everywhere.', 'docket-suite' ); ?></p>
			<table class="widefat striped" style="max-width:52em">
				<tbody>
					<tr><td><code>[brooks_phone]</code></td><td><?php esc_html_e( 'Office number, click to call', 'docket-suite' ); ?></td></tr>
					<tr><td><code>[brooks_phone line="criminal"]</code></td><td><?php esc_html_e( 'Criminal line', 'docket-suite' ); ?></td></tr>
					<tr><td><code>[brooks_phone link="no"]</code></td><td><?php esc_html_e( 'Plain text, no link', 'docket-suite' ); ?></td></tr>
					<tr><td><code>[brooks_call_button]</code></td><td><?php esc_html_e( 'Call button', 'docket-suite' ); ?></td></tr>
					<tr><td><code>[brooks_call_button line="criminal" text="Call or text now"]</code></td><td><?php esc_html_e( 'Call button, custom label', 'docket-suite' ); ?></td></tr>
					<tr><td><code>[brooks_address]</code></td><td><?php esc_html_e( 'Address on two lines', 'docket-suite' ); ?></td></tr>
					<tr><td><code>[brooks_address format="inline"]</code></td><td><?php esc_html_e( 'Address on one line', 'docket-suite' ); ?></td></tr>
					<tr><td><code>[brooks_email]</code></td><td><?php esc_html_e( 'Email, obfuscated against scrapers', 'docket-suite' ); ?></td></tr>
					<tr><td><code>[brooks_hours]</code></td><td><?php esc_html_e( 'Office hours', 'docket-suite' ); ?></td></tr>
					<tr><td><code>[brooks_year]</code></td><td><?php esc_html_e( 'Current year, for disclaimers', 'docket-suite' ); ?></td></tr>
				</tbody>
			</table>

			<hr>

			<?php brooks_ess_checkbox( 'delete_on_uninstall', __( 'Delete these settings if the plugin is deleted', 'docket-suite' ), __( 'Leave unchecked to keep your redirects if you ever reinstall.', 'docket-suite' ) ); ?>

			<?php submit_button(); ?>
		</form>

		<hr>

		<h2><?php esc_html_e( 'Pages not found', 'docket-suite' ); ?></h2>
		<?php brooks_ess_render_404_log(); ?>
	</div>
	<?php
}

/**
 * Render the 404 log table.
 */
function brooks_ess_render_404_log() {
	$log = get_option( BROOKS_ESS_404_LOG, array() );

	if ( ! is_array( $log ) || empty( $log ) ) {
		echo '<p>' . esc_html__( 'Nothing logged yet. Anything that shows up here is a candidate for a redirect above.', 'docket-suite' ) . '</p>';
		return;
	}

	uasort(
		$log,
		function ( $a, $b ) {
			$a_hits = isset( $a['hits'] ) ? (int) $a['hits'] : 0;
			$b_hits = isset( $b['hits'] ) ? (int) $b['hits'] : 0;
			return $b_hits - $a_hits;
		}
	);
	?>
	<table class="widefat striped" style="max-width:60em">
		<thead>
			<tr>
				<th scope="col"><?php esc_html_e( 'Path', 'docket-suite' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Hits', 'docket-suite' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Last seen', 'docket-suite' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Came from', 'docket-suite' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $log as $path => $data ) : ?>
				<tr>
					<td><code><?php echo esc_html( $path ); ?></code></td>
					<td><?php echo esc_html( isset( $data['hits'] ) ? (int) $data['hits'] : 0 ); ?></td>
					<td>
						<?php
						echo esc_html(
							isset( $data['last'] )
								? wp_date( 'M j, Y g:i a', (int) $data['last'] )
								: '—'
						);
						?>
					</td>
					<td><?php echo esc_html( ! empty( $data['referer'] ) ? $data['referer'] : '—' ); ?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<form method="post" action="">
		<?php wp_nonce_field( 'brooks_ess_clear_log' ); ?>
		<p><button type="submit" name="brooks_ess_clear_log" value="1" class="button"><?php esc_html_e( 'Clear log', 'docket-suite' ); ?></button></p>
	</form>
	<?php
}

/**
 * Settings link on the Plugins screen.
 *
 * @param array $links Existing action links.
 * @return array
 */
function brooks_ess_action_links( $links ) {
	$settings = sprintf(
		'<a href="%1$s">%2$s</a>',
		esc_url( admin_url( 'options-general.php?page=brooks-essentials' ) ),
		esc_html__( 'Settings', 'docket-suite' )
	);

	array_unshift( $links, $settings );

	return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( BROOKS_ESS_FILE ), 'brooks_ess_action_links' );
