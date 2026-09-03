/**
 * Brooks Law v2 — Customizer live preview.
 * Instant updates for the fields people fiddle with most.
 */
( function ( wp ) {
	'use strict';

	if ( ! wp || ! wp.customize ) {
		return;
	}

	function bindText( settingId, selector ) {
		wp.customize( settingId, function ( value ) {
			value.bind( function ( to ) {
				var el = document.querySelector( selector );
				if ( el ) {
					el.textContent = to;
				}
			} );
		} );
	}

	bindText( 'blogname', '.brand .name a' );
	bindText( 'firm_tagline', '.brand .tag' );
	bindText( 'hero_heading', '.hero-heading' );
	bindText( 'hero_lead', '.hero-lead' );
} )( window.wp );
