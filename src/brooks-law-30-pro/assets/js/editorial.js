/**
 * Brooks Law Editorial — front-end behaviour.
 *
 * Two jobs, both optional and both fully guarded:
 *
 *   1. Drift the background line art at two different speeds as the
 *      page scrolls, so the river sits behind the Beale Street sign.
 *   2. Flag empty attorney-profile image blocks so the dashed
 *      placeholder renders on browsers without CSS :has() support.
 *
 * Nothing here is required for the page to work. If this file fails
 * to load, the layout, the artwork, and all content are unaffected.
 */
( function () {
	'use strict';

	var reduceMotion = false;

	try {
		reduceMotion =
			window.matchMedia &&
			window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
	} catch ( e ) {
		reduceMotion = false;
	}

	/**
	 * Parallax drift for the two background SVG layers.
	 */
	function initParallax() {
		if ( reduceMotion ) {
			return;
		}

		var layers = document.querySelectorAll( '.blf-sky svg[data-speed]' );
		if ( ! layers.length ) {
			return;
		}

		var ticking = false;

		function apply() {
			ticking = false;

			var y = window.pageYOffset || document.documentElement.scrollTop || 0;

			for ( var i = 0; i < layers.length; i++ ) {
				var speed = parseFloat( layers[ i ].getAttribute( 'data-speed' ) );

				if ( isNaN( speed ) ) {
					continue;
				}

				layers[ i ].style.transform =
					'translate3d(0,' + ( -y * speed ).toFixed( 2 ) + 'px,0)';
			}
		}

		function onScroll() {
			if ( ticking ) {
				return;
			}
			ticking = true;
			window.requestAnimationFrame( apply );
		}

		if ( ! window.requestAnimationFrame ) {
			return;
		}

		window.addEventListener( 'scroll', onScroll, { passive: true } );
		apply();
	}

	/**
	 * Mark empty profile image blocks so the CSS placeholder shows.
	 */
	function initPlaceholders() {
		var figures = document.querySelectorAll(
			'.pb-portrait, .pb-figure-wide'
		);

		for ( var i = 0; i < figures.length; i++ ) {
			var img = figures[ i ].querySelector( 'img' );

			if ( ! img || ! img.getAttribute( 'src' ) ) {
				figures[ i ].classList.add( 'pb-is-empty' );
			} else {
				figures[ i ].classList.remove( 'pb-is-empty' );
			}
		}
	}

	function init() {
		try {
			initParallax();
		} catch ( e ) {
			/* Artwork stays static — harmless. */
		}

		try {
			initPlaceholders();
		} catch ( e ) {
			/* Placeholders stay hidden — harmless. */
		}
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', init, { once: true } );
	} else {
		init();
	}
} )();
