/**
 * Brooks Law — carousel mouse drag.
 *
 * Touch and trackpad already scroll the carousel natively; this adds
 * click-and-drag for a mouse, which is what the "drag / scroll" hint
 * promises. Progressive enhancement only — without it the carousel
 * still scrolls by wheel, keyboard, and scrollbar.
 *
 * @since 4.10.0
 */
( function () {
	'use strict';

	var carousels = document.querySelectorAll( '.is-style-brooks-carousel' );

	if ( ! carousels.length ) {
		return;
	}

	carousels.forEach( function ( wrap ) {
		var track = wrap.querySelector( '.wp-block-group__inner-container' ) || wrap;
		var down = false;
		var startX = 0;
		var startScroll = 0;
		var moved = false;

		track.addEventListener( 'pointerdown', function ( e ) {
			if ( 'mouse' !== e.pointerType ) {
				return;
			}
			down = true;
			moved = false;
			startX = e.clientX;
			startScroll = track.scrollLeft;
			wrap.classList.add( 'is-dragging' );
		} );

		window.addEventListener( 'pointermove', function ( e ) {
			if ( ! down ) {
				return;
			}
			var dx = e.clientX - startX;
			if ( Math.abs( dx ) > 4 ) {
				moved = true;
			}
			track.scrollLeft = startScroll - dx;
		} );

		window.addEventListener( 'pointerup', function () {
			down = false;
			wrap.classList.remove( 'is-dragging' );
		} );

		// A drag should not fire the card link on release.
		track.addEventListener( 'click', function ( e ) {
			if ( moved ) {
				e.preventDefault();
				moved = false;
			}
		}, true );
	} );
}() );
