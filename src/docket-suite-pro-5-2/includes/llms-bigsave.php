<?php
/**
 * /llms.txt large-content save.
 *
 * WHY THIS EXISTS
 * ---------------
 * On this host a single POST field is truncated at roughly 6.4 KB. The cut is
 * silent: options.php returns success and the option holds a clipped string.
 * The limit is enforced above PHP (mod_security or a per-directory ini), so a
 * plugin cannot raise it — ini_set() on post_max_size and max_input_vars has
 * no effect at runtime.
 *
 * So this module avoids sending one large field at all. Two paths:
 *
 *   1. Chunked save (default). JavaScript splits the textarea into pieces well
 *      under the cap and posts them one at a time to admin-ajax. The server
 *      accumulates them in a transient and writes the option only when the
 *      final chunk arrives and the reassembled length matches what the browser
 *      said it sent. A mismatch aborts without touching the stored value.
 *
 *   2. File upload. Upload a .txt and its contents become the body. Multipart
 *      file parts are governed by upload_max_filesize, not the field cap, so
 *      this works even if chunking is blocked.
 *
 * Both verify a nonce and manage_options.
 *
 * @package Docket_Suite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const BROOKS_LLMS_CHUNK_TRANSIENT = 'brooks_llms_chunk_buf';

/**
 * Chunk size in bytes. Comfortably under the observed ~6.4 KB field cap.
 */
function brooks_llms_chunk_size() {
	return (int) apply_filters( 'brooks_llms_chunk_size', 3072 );
}

/* -------------------------------------------------------------------------
 * AJAX: receive one chunk
 * ---------------------------------------------------------------------- */
function brooks_llms_ajax_chunk() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => 'Insufficient permissions.' ), 403 );
	}
	check_ajax_referer( 'brooks_llms_chunk', 'nonce' );

	$index = isset( $_POST['index'] ) ? absint( $_POST['index'] ) : 0;
	$total = isset( $_POST['total'] ) ? absint( $_POST['total'] ) : 0;
	$bytes = isset( $_POST['bytes'] ) ? absint( $_POST['bytes'] ) : 0;
	// Raw: this is llms.txt body text, served as text/plain, never rendered.
	$piece = isset( $_POST['chunk'] ) ? (string) wp_unslash( $_POST['chunk'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput

	if ( $total < 1 || $index >= $total ) {
		wp_send_json_error( array( 'message' => 'Bad chunk index.' ), 400 );
	}

	$buf = get_transient( BROOKS_LLMS_CHUNK_TRANSIENT );
	if ( 0 === $index || ! is_array( $buf ) ) {
		$buf = array();
	}
	$buf[ $index ] = $piece;
	set_transient( BROOKS_LLMS_CHUNK_TRANSIENT, $buf, 10 * MINUTE_IN_SECONDS );

	// Not finished yet: acknowledge and wait for the rest.
	if ( count( $buf ) < $total ) {
		wp_send_json_success(
			array(
				'received' => count( $buf ),
				'total'    => $total,
				'done'     => false,
			)
		);
	}

	ksort( $buf, SORT_NUMERIC );
	$content = implode( '', $buf );

	// Integrity check before we touch the stored value.
	if ( $bytes && strlen( $content ) !== $bytes ) {
		delete_transient( BROOKS_LLMS_CHUNK_TRANSIENT );
		wp_send_json_error(
			array(
				'message'  => sprintf(
					'Reassembled %d bytes but the browser sent %d. Nothing was saved — try again.',
					strlen( $content ),
					$bytes
				),
			),
			409
		);
	}

	$content = brooks_llms_sanitize( $content );
	update_option( BROOKS_LLMS_OPTION, $content, false );
	delete_transient( BROOKS_LLMS_CHUNK_TRANSIENT );

	wp_send_json_success(
		array(
			'done'  => true,
			'bytes' => strlen( $content ),
			'links' => substr_count( $content, "\n- " ),
		)
	);
}
add_action( 'wp_ajax_brooks_llms_chunk', 'brooks_llms_ajax_chunk' );

/* -------------------------------------------------------------------------
 * File upload path
 * ---------------------------------------------------------------------- */
function brooks_llms_handle_upload() {
	if ( ! isset( $_POST['brooks_llms_upload'] ) ) {
		return;
	}
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Insufficient permissions.' );
	}
	check_admin_referer( 'brooks_llms_upload_action', 'brooks_llms_upload_nonce' );

	if ( empty( $_FILES['brooks_llms_file']['tmp_name'] ) ) {
		add_settings_error( 'brooks_llms', 'nofile', 'No file was received.', 'error' );
		return;
	}

	$tmp = $_FILES['brooks_llms_file']['tmp_name']; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	if ( ! is_uploaded_file( $tmp ) ) {
		add_settings_error( 'brooks_llms', 'badfile', 'Upload failed.', 'error' );
		return;
	}

	$name = isset( $_FILES['brooks_llms_file']['name'] ) ? sanitize_file_name( wp_unslash( $_FILES['brooks_llms_file']['name'] ) ) : '';
	$ext  = strtolower( pathinfo( $name, PATHINFO_EXTENSION ) );
	if ( ! in_array( $ext, array( 'txt', 'md' ), true ) ) {
		add_settings_error( 'brooks_llms', 'badext', 'Only .txt or .md files are accepted.', 'error' );
		return;
	}

	$content = file_get_contents( $tmp ); // phpcs:ignore WordPress.WP.AlternativeFunctions -- reading an uploaded temp file.
	if ( false === $content ) {
		add_settings_error( 'brooks_llms', 'unreadable', 'Could not read the uploaded file.', 'error' );
		return;
	}

	$content = brooks_llms_sanitize( $content );
	update_option( BROOKS_LLMS_OPTION, $content, false );

	add_settings_error(
		'brooks_llms',
		'uploaded',
		sprintf( 'Saved %s bytes (%s links) from %s.', number_format_i18n( strlen( $content ) ), number_format_i18n( substr_count( $content, "\n- " ) ), $name ),
		'success'
	);
}
add_action( 'admin_init', 'brooks_llms_handle_upload' );

/* -------------------------------------------------------------------------
 * Admin UI fragment, called from the settings screen
 * ---------------------------------------------------------------------- */
function brooks_llms_render_bigsave() {
	$chunk = brooks_llms_chunk_size();
	?>
	<h2>Save a large body</h2>
	<p class="description">
		This server truncates a single form field at about 6.4&nbsp;KB, silently. Either method below avoids that.
		Use one of these instead of the normal Save button when the body is larger than a few kilobytes.
	</p>

	<p>
		<button type="button" class="button button-primary" id="brooks-llms-chunk-save">Save body in chunks</button>
		<span id="brooks-llms-chunk-status" style="margin-left:10px;"></span>
	</p>
	<p class="description">
		Sends the Manual body box above in <?php echo esc_html( number_format_i18n( $chunk ) ); ?>-byte pieces and
		reassembles them here. The stored value is only replaced if the reassembled length matches.
	</p>

	<hr />

	<p><strong>Or upload a file</strong> — its contents replace the Manual body.</p>
	<form method="post" enctype="multipart/form-data">
		<?php wp_nonce_field( 'brooks_llms_upload_action', 'brooks_llms_upload_nonce' ); ?>
		<input type="file" name="brooks_llms_file" accept=".txt,.md" />
		<button type="submit" name="brooks_llms_upload" value="1" class="button">Upload and save</button>
	</form>

	<script>
	( function () {
		var btn = document.getElementById( 'brooks-llms-chunk-save' );
		if ( ! btn ) { return; }
		var out   = document.getElementById( 'brooks-llms-chunk-status' );
		var field = document.querySelector( 'textarea[name="<?php echo esc_js( BROOKS_LLMS_OPTION ); ?>"]' );
		var SIZE  = <?php echo (int) $chunk; ?>;
		var AJAX  = <?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>;
		var NONCE = <?php echo wp_json_encode( wp_create_nonce( 'brooks_llms_chunk' ) ); ?>;

		function byteLen( s ) { return new TextEncoder().encode( s ).length; }

		btn.addEventListener( 'click', function () {
			if ( ! field ) { return; }
			var text  = field.value;
			var bytes = byteLen( text );
			// Split on byte boundaries that are also character boundaries.
			var parts = [], buf = '', n = 0;
			for ( var i = 0; i < text.length; i++ ) {
				var ch = text[ i ];
				var l  = byteLen( ch );
				if ( n + l > SIZE ) { parts.push( buf ); buf = ''; n = 0; }
				buf += ch; n += l;
			}
			if ( buf ) { parts.push( buf ); }
			if ( ! parts.length ) { parts = [ '' ]; }

			btn.disabled = true;
			var idx = 0;

			function send() {
				out.textContent = 'Sending ' + ( idx + 1 ) + ' of ' + parts.length + '…';
				var body = new URLSearchParams();
				body.append( 'action', 'brooks_llms_chunk' );
				body.append( 'nonce', NONCE );
				body.append( 'index', idx );
				body.append( 'total', parts.length );
				body.append( 'bytes', bytes );
				body.append( 'chunk', parts[ idx ] );

				fetch( AJAX, {
					method: 'POST',
					credentials: 'same-origin',
					headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
					body: body.toString()
				} )
				.then( function ( r ) { return r.json(); } )
				.then( function ( j ) {
					if ( ! j || ! j.success ) {
						out.textContent = 'Failed: ' + ( j && j.data && j.data.message ? j.data.message : 'unknown error' );
						out.style.color = '#a00';
						btn.disabled = false;
						return;
					}
					if ( j.data.done ) {
						out.textContent = 'Saved ' + j.data.bytes.toLocaleString() + ' bytes, ' + j.data.links + ' links. Purge Cloudflare, then reload this page.';
						out.style.color = '#0a0';
						btn.disabled = false;
						return;
					}
					idx++;
					send();
				} )
				.catch( function ( e ) {
					out.textContent = 'Network error: ' + e;
					out.style.color = '#a00';
					btn.disabled = false;
				} );
			}
			send();
		} );
	}() );
	</script>
	<?php
}
