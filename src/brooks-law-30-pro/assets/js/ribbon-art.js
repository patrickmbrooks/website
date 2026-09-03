/**
 * Brooks Law — ribbon artwork drift.
 *
 * Shifts the motif inside its ribbon as the band crosses the viewport. The
 * movement is bounded by the ribbon's own overflow, so nothing escapes the
 * band and no layout is affected — only a transform on the SVG.
 *
 * Bails entirely for reduced-motion, and does nothing at all if no ribbon on
 * the page carries artwork.
 *
 * @since 4.2.0
 */
( function () {
	'use strict';

	if ( window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) {
		return;
	}

	var bands = [].slice.call( document.querySelectorAll( '.page-ribbon.has-art' ) );

	if ( ! bands.length ) {
		return;
	}

	var ticking = false;

	function update() {
		var viewport = window.innerHeight || document.documentElement.clientHeight;

		for ( var i = 0; i < bands.length; i++ ) {
			var band = bands[ i ];
			var rect = band.getBoundingClientRect();

			// Skip anything comfortably off-screen.
			if ( rect.bottom < -200 || rect.top > viewport + 200 ) {
				continue;
			}

			var art = band.querySelector( '.ribbon-art' );

			if ( ! art ) {
				continue;
			}

			// -0.5 at the bottom of the viewport, +0.5 at the top.
			var progress = ( rect.top + rect.height / 2 - viewport / 2 ) / viewport;

			if ( progress > 1 ) {
				progress = 1;
			}
			if ( progress < -1 ) {
				progress = -1;
			}

			art.style.transform = 'translate3d(0,' + ( progress * -22 ).toFixed( 1 ) + 'px,0)';
		}

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
