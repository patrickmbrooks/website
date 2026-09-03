/**
 * Brooks Law — return-to-top.
 *
 * The control is a real anchor to #top, so it works without this script; all
 * this adds is showing it once the visitor has scrolled, and smooth scrolling
 * for those who have not asked for reduced motion.
 *
 * @since 4.12.0
 */
( function () {
	'use strict';

	var btn = document.querySelector( '[data-blf-top]' );

	if ( ! btn ) {
		return;
	}

	var reduce = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
	var ticking = false;

	function update() {
		var y = window.pageYOffset || document.documentElement.scrollTop || 0;
		btn.classList.toggle( 'is-visible', y > window.innerHeight * 0.9 );
		ticking = false;
	}

	window.addEventListener( 'scroll', function () {
		if ( ! ticking ) {
			ticking = true;
			window.requestAnimationFrame( update );
		}
	}, { passive: true } );

	btn.addEventListener( 'click', function ( e ) {
		e.preventDefault();
		window.scrollTo( { top: 0, behavior: reduce ? 'auto' : 'smooth' } );

		// Move focus to the top of the document, so keyboard and screen-reader
		// users land where the page visually went.
		var target = document.getElementById( 'top' ) || document.querySelector( 'main' ) || document.body;
		target.setAttribute( 'tabindex', '-1' );
		target.focus( { preventScroll: true } );
	} );

	update();
}() );
