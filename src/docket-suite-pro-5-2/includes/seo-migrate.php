<?php
/**
 * Yoast -> Docket SEO field migration (one-shot, reversible, opt-in).
 *
 * WHY THIS IS SAFE TO SKIP
 * -----------------------
 * seo.php already reads Yoast's own meta keys at render time when its own
 * fields are empty (see seo_title_for(), seo_desc_for(), is_noindexed()).
 * Deactivating Yoast does NOT delete that post meta, so titles, descriptions
 * and noindex flags keep working with or without this migration. Nothing is
 * lost by never running it.
 *
 * WHAT IT IS FOR
 * --------------
 * Copying the values into Docket's own keys so that:
 *   - the Docket SEO meta box shows the real current values instead of
 *     appearing blank (the fallback is invisible in the editor);
 *   - the site no longer depends on Yoast-shaped data if Yoast is ever
 *     deleted outright (delete removes its meta; deactivate does not).
 *
 * BEHAVIOUR
 * ---------
 *   - Runs only when an administrator clicks the button on the Docket SEO
 *     settings screen. Never runs automatically.
 *   - NEVER overwrites a Docket field that already has a value.
 *   - Skips Yoast template values containing %% (e.g. %%title%% %%sep%%
 *     %%sitename%%) — those are patterns, not literal titles.
 *   - Writes a per-post backup of anything it changes, so a single click of
 *     "Undo" restores the previous state exactly.
 *   - Records a completion flag; re-running is harmless (it is idempotent),
 *     but the button reports what it did each time.
 *
 * @package Docket_Suite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Option flag recording the last completed run. */
if ( ! defined( 'DOCKET_SEO_MIGRATED' ) ) {
	define( 'DOCKET_SEO_MIGRATED', 'docket_seo_migrated_v1' );
}

/** Per-post meta key holding the pre-migration snapshot. */
if ( ! defined( 'DOCKET_SEO_MIGRATE_BACKUP' ) ) {
	define( 'DOCKET_SEO_MIGRATE_BACKUP', '_docket_seo_migrate_backup' );
}

/**
 * The field map: Yoast key => Docket key.
 *
 * Focus keyword, readability and SEO scores are deliberately NOT migrated.
 * They are Yoast-only editorial scoring artifacts with no front-end output.
 */
function docket_seo_migrate_map() {
	return array(
		'_yoast_wpseo_title'                => '_docket_seo_title',
		'_yoast_wpseo_metadesc'             => '_docket_seo_desc',
		'_yoast_wpseo_meta-robots-noindex'  => '_docket_seo_noindex',
	);
}

/**
 * Does this Yoast value carry a literal string worth copying?
 *
 * @param string $key   Yoast meta key.
 * @param string $value Raw stored value.
 * @return bool
 */
function docket_seo_migrate_is_copyable( $key, $value ) {
	$value = trim( (string) $value );

	if ( '' === $value ) {
		return false;
	}

	// noindex is stored as '1'; anything else means "not set to noindex".
	if ( '_yoast_wpseo_meta-robots-noindex' === $key ) {
		return '1' === $value;
	}

	// Yoast templates are patterns, not literal text.
	if ( false !== strpos( $value, '%%' ) ) {
		return false;
	}

	return true;
}

/**
 * Run the migration.
 *
 * @param bool $dry_run When true, count what would change without writing.
 * @return array {
 *     @type int $scanned  Posts examined.
 *     @type int $changed  Posts written to.
 *     @type int $fields   Individual fields copied.
 *     @type int $skipped  Fields skipped because Docket already had a value.
 * }
 */
function docket_seo_migrate_run( $dry_run = false ) {
	$map    = docket_seo_migrate_map();
	$result = array(
		'scanned' => 0,
		'changed' => 0,
		'fields'  => 0,
		'skipped' => 0,
	);

	$ids = get_posts(
		array(
			'post_type'        => array( 'page', 'post' ),
			'post_status'      => array( 'publish', 'draft', 'pending', 'private', 'future' ),
			'numberposts'      => -1,
			'fields'           => 'ids',
			'suppress_filters' => true,
		)
	);

	foreach ( $ids as $post_id ) {
		$result['scanned']++;
		$backup  = array();
		$touched = false;

		foreach ( $map as $yoast_key => $docket_key ) {
			$yoast_value = get_post_meta( $post_id, $yoast_key, true );

			if ( ! docket_seo_migrate_is_copyable( $yoast_key, $yoast_value ) ) {
				continue;
			}

			// Never clobber a value already entered on the Docket side.
			$existing = trim( (string) get_post_meta( $post_id, $docket_key, true ) );
			if ( '' !== $existing ) {
				$result['skipped']++;
				continue;
			}

			$backup[ $docket_key ] = $existing; // Always '' here, kept explicit for the undo path.
			$result['fields']++;
			$touched = true;

			if ( ! $dry_run ) {
				$value = ( '_docket_seo_noindex' === $docket_key ) ? 1 : trim( (string) $yoast_value );
				update_post_meta( $post_id, $docket_key, $value );
			}
		}

		if ( $touched ) {
			$result['changed']++;
			if ( ! $dry_run ) {
				update_post_meta( $post_id, DOCKET_SEO_MIGRATE_BACKUP, wp_json_encode( $backup ) );
			}
		}
	}

	if ( ! $dry_run ) {
		update_option(
			DOCKET_SEO_MIGRATED,
			array(
				'time'   => current_time( 'mysql' ),
				'result' => $result,
			),
			false
		);
	}

	return $result;
}

/**
 * Undo the migration: remove every Docket field this migration created,
 * leaving anything typed by hand untouched.
 *
 * @return int Number of posts reverted.
 */
function docket_seo_migrate_undo() {
	$reverted = 0;

	$ids = get_posts(
		array(
			'post_type'        => array( 'page', 'post' ),
			'post_status'      => 'any',
			'numberposts'      => -1,
			'fields'           => 'ids',
			'meta_key'         => DOCKET_SEO_MIGRATE_BACKUP, // phpcs:ignore WordPress.DB.SlowDBQuery
			'suppress_filters' => true,
		)
	);

	foreach ( $ids as $post_id ) {
		$raw    = get_post_meta( $post_id, DOCKET_SEO_MIGRATE_BACKUP, true );
		$backup = json_decode( (string) $raw, true );

		if ( is_array( $backup ) ) {
			foreach ( $backup as $docket_key => $previous ) {
				if ( '' === trim( (string) $previous ) ) {
					delete_post_meta( $post_id, $docket_key );
				} else {
					update_post_meta( $post_id, $docket_key, $previous );
				}
			}
			$reverted++;
		}

		delete_post_meta( $post_id, DOCKET_SEO_MIGRATE_BACKUP );
	}

	delete_option( DOCKET_SEO_MIGRATED );

	return $reverted;
}

/**
 * Handle the button presses from the Docket SEO settings screen.
 */
function docket_seo_migrate_handle_actions() {
	if ( ! isset( $_POST['docket_seo_migrate_action'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		return;
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	check_admin_referer( 'docket_seo_migrate' );

	$action = sanitize_key( wp_unslash( $_POST['docket_seo_migrate_action'] ) );

	if ( 'preview' === $action ) {
		$r = docket_seo_migrate_run( true );
		set_transient(
			'docket_seo_migrate_notice',
			sprintf(
				/* translators: 1: posts scanned, 2: posts affected, 3: fields to copy, 4: fields skipped */
				__( 'Preview only — nothing was written. Scanned %1$d posts and pages: %2$d would be updated, %3$d fields copied, %4$d skipped because a Docket value already exists.', 'docket-suite' ),
				$r['scanned'],
				$r['changed'],
				$r['fields'],
				$r['skipped']
			),
			60
		);
	} elseif ( 'run' === $action ) {
		$r = docket_seo_migrate_run( false );
		set_transient(
			'docket_seo_migrate_notice',
			sprintf(
				/* translators: 1: posts scanned, 2: posts updated, 3: fields copied, 4: fields skipped */
				__( 'Migration complete. Scanned %1$d posts and pages: %2$d updated, %3$d fields copied, %4$d skipped. Use Undo below to reverse this exactly.', 'docket-suite' ),
				$r['scanned'],
				$r['changed'],
				$r['fields'],
				$r['skipped']
			),
			60
		);
	} elseif ( 'undo' === $action ) {
		$n = docket_seo_migrate_undo();
		set_transient(
			'docket_seo_migrate_notice',
			sprintf(
				/* translators: %d: posts reverted */
				__( 'Migration reversed on %d posts and pages. Docket fields created by the migration were removed; Yoast values are untouched and the render-time fallback still applies.', 'docket-suite' ),
				$n
			),
			60
		);
	}

	wp_safe_redirect( add_query_arg( 'page', 'docket-seo', admin_url( 'options-general.php' ) ) );
	exit;
}
add_action( 'admin_init', 'docket_seo_migrate_handle_actions' );

/**
 * Show the result of the last button press.
 */
function docket_seo_migrate_notice() {
	$message = get_transient( 'docket_seo_migrate_notice' );
	if ( ! $message ) {
		return;
	}
	delete_transient( 'docket_seo_migrate_notice' );
	echo '<div class="notice notice-info is-dismissible"><p>' . esc_html( $message ) . '</p></div>';
}
add_action( 'admin_notices', 'docket_seo_migrate_notice' );

/**
 * The migration panel, printed on the Docket SEO settings screen.
 */
function docket_seo_migrate_panel() {
	$done = get_option( DOCKET_SEO_MIGRATED );
	?>
	<hr>
	<h2><?php esc_html_e( 'Import fields from Yoast', 'docket-suite' ); ?></h2>
	<p>
		<?php esc_html_e( 'Optional. Docket SEO already falls back to Yoast\'s stored titles, descriptions and noindex flags when its own fields are empty, so your pages keep their metadata whether or not you run this. Importing copies those values into Docket\'s own fields so they appear in the editor and no longer depend on Yoast data being present.', 'docket-suite' ); ?>
	</p>
	<p>
		<strong><?php esc_html_e( 'Existing Docket values are never overwritten.', 'docket-suite' ); ?></strong>
		<?php esc_html_e( 'Yoast template values such as %%title%% %%sep%% %%sitename%% are skipped, as are focus keywords and readability scores.', 'docket-suite' ); ?>
	</p>
	<?php if ( $done && ! empty( $done['time'] ) ) : ?>
		<p><em>
			<?php
			printf(
				/* translators: %s: date and time of the last migration run */
				esc_html__( 'Last run: %s', 'docket-suite' ),
				esc_html( $done['time'] )
			);
			?>
		</em></p>
	<?php endif; ?>
	<form method="post">
		<?php wp_nonce_field( 'docket_seo_migrate' ); ?>
		<p>
			<button type="submit" name="docket_seo_migrate_action" value="preview" class="button">
				<?php esc_html_e( 'Preview (writes nothing)', 'docket-suite' ); ?>
			</button>
			<button type="submit" name="docket_seo_migrate_action" value="run" class="button button-primary">
				<?php esc_html_e( 'Import from Yoast', 'docket-suite' ); ?>
			</button>
			<?php if ( $done ) : ?>
				<button type="submit" name="docket_seo_migrate_action" value="undo" class="button">
					<?php esc_html_e( 'Undo import', 'docket-suite' ); ?>
				</button>
			<?php endif; ?>
		</p>
	</form>
	<?php
}
