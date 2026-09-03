<?php
/**
 * Brooks Essentials — firm info shortcodes.
 *
 * The point: if a phone number is typed into page content, changing it later
 * means hunting through every page. If it is a shortcode, it changes in one
 * place — and unlike a theme template tag, it keeps working after a theme
 * change instead of printing raw text into the page.
 *
 * @package Brooks_Essentials
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Digits-and-plus tel: value.
 *
 * @param string $number Display number.
 * @return string
 */
function brooks_ess_tel( $number ) {
	$clean = preg_replace( '/[^0-9+]/', '', (string) $number );

	if ( '' === $clean ) {
		return '';
	}

	if ( '+' !== substr( $clean, 0, 1 ) && 10 === strlen( $clean ) ) {
		$clean = '+1' . $clean;
	}

	return $clean;
}

/**
 * [brooks_phone] — office number, linked by default.
 * [brooks_phone line="criminal"] — the criminal line.
 * [brooks_phone link="no"] — plain text.
 *
 * @param array $atts Shortcode attributes.
 * @return string
 */
function brooks_ess_sc_phone( $atts ) {
	$atts = shortcode_atts(
		array(
			'line' => 'office',
			'link' => 'yes',
		),
		$atts,
		'brooks_phone'
	);

	if ( 'criminal' === $atts['line'] || 'cell' === $atts['line'] ) {
		$display = brooks_ess_firm( 'firm_cell' );
		$dial    = brooks_ess_tel( brooks_ess_firm( 'firm_cell_link' ) );
	} else {
		$display = brooks_ess_firm( 'firm_phone' );
		$dial    = brooks_ess_tel( brooks_ess_firm( 'firm_phone_link' ) );
	}

	if ( '' === $display ) {
		return '';
	}

	if ( 'no' === $atts['link'] || '' === $dial ) {
		return esc_html( $display );
	}

	return sprintf(
		'<a href="tel:%1$s">%2$s</a>',
		esc_attr( $dial ),
		esc_html( $display )
	);
}
add_shortcode( 'brooks_phone', 'brooks_ess_sc_phone' );

/**
 * [brooks_call_button] — a call-to-action button.
 * [brooks_call_button line="criminal" text="Call or text now"]
 *
 * @param array $atts Shortcode attributes.
 * @return string
 */
function brooks_ess_sc_call_button( $atts ) {
	$atts = shortcode_atts(
		array(
			'line'  => 'office',
			'text'  => '',
			'class' => 'btn btn-brass',
		),
		$atts,
		'brooks_call_button'
	);

	if ( 'criminal' === $atts['line'] || 'cell' === $atts['line'] ) {
		$display = brooks_ess_firm( 'firm_cell' );
		$dial    = brooks_ess_tel( brooks_ess_firm( 'firm_cell_link' ) );
	} else {
		$display = brooks_ess_firm( 'firm_phone' );
		$dial    = brooks_ess_tel( brooks_ess_firm( 'firm_phone_link' ) );
	}

	if ( '' === $dial ) {
		return '';
	}

	/* translators: %s: formatted phone number */
	$label = '' !== $atts['text'] ? $atts['text'] : sprintf( __( 'Call %s', 'docket-suite' ), $display );

	return sprintf(
		'<a class="%1$s" href="tel:%2$s">%3$s</a>',
		esc_attr( $atts['class'] ),
		esc_attr( $dial ),
		esc_html( $label )
	);
}
add_shortcode( 'brooks_call_button', 'brooks_ess_sc_call_button' );

/**
 * [brooks_address] — street plus city/state.
 * [brooks_address format="inline"] — comma-separated on one line.
 *
 * @param array $atts Shortcode attributes.
 * @return string
 */
function brooks_ess_sc_address( $atts ) {
	$atts = shortcode_atts(
		array( 'format' => 'block' ),
		$atts,
		'brooks_address'
	);

	$street = brooks_ess_firm( 'firm_address' );
	$city   = brooks_ess_firm( 'firm_city_state' );

	if ( '' === $street && '' === $city ) {
		return '';
	}

	if ( 'inline' === $atts['format'] ) {
		return esc_html( trim( $street . ', ' . $city, ', ' ) );
	}

	return sprintf(
		'<span class="brooks-address">%1$s<br>%2$s</span>',
		esc_html( $street ),
		esc_html( $city )
	);
}
add_shortcode( 'brooks_address', 'brooks_ess_sc_address' );

/**
 * [brooks_email] — mailto link.
 *
 * @param array $atts Shortcode attributes.
 * @return string
 */
function brooks_ess_sc_email( $atts ) {
	$atts = shortcode_atts(
		array( 'link' => 'yes' ),
		$atts,
		'brooks_email'
	);

	$email = brooks_ess_firm( 'firm_email' );

	if ( '' === $email ) {
		return '';
	}

	if ( 'no' === $atts['link'] ) {
		return esc_html( antispambot( $email ) );
	}

	return sprintf(
		'<a href="mailto:%1$s">%2$s</a>',
		esc_attr( antispambot( $email, 1 ) ),
		esc_html( antispambot( $email ) )
	);
}
add_shortcode( 'brooks_email', 'brooks_ess_sc_email' );

/**
 * [brooks_hours]
 *
 * @return string
 */
function brooks_ess_sc_hours() {
	return esc_html( brooks_ess_firm( 'firm_hours' ) );
}
add_shortcode( 'brooks_hours', 'brooks_ess_sc_hours' );

/**
 * [brooks_year] — current year, for disclaimers that should never go stale.
 *
 * @return string
 */
function brooks_ess_sc_year() {
	return esc_html( gmdate( 'Y' ) );
}
add_shortcode( 'brooks_year', 'brooks_ess_sc_year' );

/**
 * [brooks_text] — criminal line as an sms: link (v3).
 */
function brooks_ess_sc_text() {
	$display = brooks_ess_firm( 'firm_cell' );
	$link    = brooks_ess_firm( 'firm_cell_link' );
	$clean   = preg_replace( '/[^0-9+]/', '', '' !== $link ? $link : $display );
	if ( '+' !== substr( (string) $clean, 0, 1 ) && 10 === strlen( (string) $clean ) ) {
		$clean = '+1' . $clean;
	}
	return sprintf( '<a href="sms:%1$s">%2$s</a>', esc_attr( (string) $clean ), esc_html( $display ) );
}
add_shortcode( 'brooks_text', 'brooks_ess_sc_text' );

/**
 * GA4 call/text click tracking (v3, opt-in). One delegated listener in the
 * footer; no-ops silently when gtag is absent, so it can never error.
 */
function brooks_ess_call_tracking() {
	if ( is_admin() || ! brooks_ess_get( 'track_calls' ) ) {
		return;
	}
	?>
	<script id="brooks-ess-call-tracking">
	(function(){
		'use strict';
		document.addEventListener('click', function (e) {
			var a = e.target && e.target.closest ? e.target.closest('a[href^="tel:"],a[href^="sms:"]') : null;
			if (!a || typeof window.gtag !== 'function') { return; }
			var href = a.getAttribute('href') || '';
			var sms = href.indexOf('sms:') === 0;
			window.gtag('event', sms ? 'text_click' : 'call_click', {
				phone_number: href.replace(/^(tel:|sms:)/, ''),
				link_text: (a.textContent || '').trim().slice(0, 60),
				page_path: location.pathname
			});
		}, { passive: true });
	})();
	</script>
	<?php
}
add_action( 'wp_footer', 'brooks_ess_call_tracking', 99 );
