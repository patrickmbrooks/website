/**
 * Brooks Law — atmosphere.
 *
 * Sets --wx from scroll progress down the page, which cross-fades the cloud
 * scene from storm to clear. Lifted from the behaviour the editorial pages
 * already had, so it works identically.
 *
 * Bails for reduced motion: the scene still renders, it simply stays in its
 * clear state rather than animating.
 *
 * @since 4.8.0
 */
( function () {
	'use strict';

	var scene = document.querySelector( '.blf-atmos-scene' );

	if ( ! scene ) {
		return;
	}

	var reduce = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	if ( reduce ) {
		document.body.style.setProperty( '--wx', '1' );
		return;
	}

	var ticking = false;

	function update() {
		var doc = document.documentElement;
		var max = doc.scrollHeight - window.innerHeight;
		var v = max > 0 ? ( window.pageYOffset || doc.scrollTop || 0 ) / max : 0;

		if ( v < 0 ) {
			v = 0;
		}
		if ( v > 1 ) {
			v = 1;
		}

		document.body.style.setProperty( '--wx', Math.round( v * 1000 ) / 1000 );
		ticking = false;
	}

	function onScroll() {
		if ( ! ticking ) {
			ticking = true;
			window.requestAnimationFrame( update );
		}
	}

	window.addEventListener( 'scroll', onScroll, { passive: true } );
	window.addEventListener( 'resize', onScroll, { passive: true } );
	update();
}() );
