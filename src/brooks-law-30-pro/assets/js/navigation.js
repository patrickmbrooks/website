/**
 * Brooks Law v2 — navigation.
 *
 * Progressive enhancement over CSS :hover / :focus-within:
 * 1. Mobile menu toggle with aria-expanded.
 * 2. Injected sub-menu toggle buttons so pointer, keyboard, and touch
 *    users can all open dropdowns.
 * 3. Escape and outside-click close open menus.
 */
( function () {
	'use strict';

	var toggle = document.querySelector( '.menu-toggle' );
	var nav = document.querySelector( '.main-navigation' );

	if ( ! nav ) {
		return;
	}

	/* ---- Mobile menu toggle ---- */
	if ( toggle ) {
		toggle.addEventListener( 'click', function () {
			var expanded = toggle.getAttribute( 'aria-expanded' ) === 'true';
			toggle.setAttribute( 'aria-expanded', expanded ? 'false' : 'true' );
			nav.classList.toggle( 'toggled', ! expanded );
		} );
	}

	/* ---- Sub-menu toggle buttons ---- */
	var parents = nav.querySelectorAll( '.menu-item-has-children' );

	Array.prototype.forEach.call( parents, function ( li ) {
		var link = li.querySelector( ':scope > a' );
		var label = link ? link.textContent.trim() : '';
		var button = document.createElement( 'button' );

		button.className = 'submenu-toggle';
		button.setAttribute( 'type', 'button' );
		button.setAttribute( 'aria-expanded', 'false' );

		var sr = document.createElement( 'span' );
		sr.className = 'screen-reader-text';
		sr.textContent = label ? label + ' submenu' : 'Submenu';
		button.appendChild( sr );

		var submenu = li.querySelector( ':scope > .sub-menu' );
		if ( link && submenu ) {
			li.insertBefore( button, submenu );
		} else {
			return;
		}

		button.addEventListener( 'click', function () {
			var isOpen = li.classList.contains( 'open' );

			// Close any sibling that is open.
			Array.prototype.forEach.call( parents, function ( other ) {
				if ( other !== li ) {
					other.classList.remove( 'open' );
					var otherBtn = other.querySelector( ':scope > .submenu-toggle' );
					if ( otherBtn ) {
						otherBtn.setAttribute( 'aria-expanded', 'false' );
					}
				}
			} );

			li.classList.toggle( 'open', ! isOpen );
			button.setAttribute( 'aria-expanded', isOpen ? 'false' : 'true' );
		} );
	} );

	function closeAll() {
		Array.prototype.forEach.call( parents, function ( li ) {
			li.classList.remove( 'open' );
			var btn = li.querySelector( ':scope > .submenu-toggle' );
			if ( btn ) {
				btn.setAttribute( 'aria-expanded', 'false' );
			}
		} );
	}

	/* ---- Escape closes menus and returns focus sensibly ---- */
	document.addEventListener( 'keydown', function ( event ) {
		if ( event.key !== 'Escape' && event.key !== 'Esc' ) {
			return;
		}
		closeAll();
		if ( toggle && nav.classList.contains( 'toggled' ) ) {
			nav.classList.remove( 'toggled' );
			toggle.setAttribute( 'aria-expanded', 'false' );
			toggle.focus();
		}
	} );

	/* ---- Click outside closes dropdowns ---- */
	document.addEventListener( 'click', function ( event ) {
		if ( ! nav.contains( event.target ) ) {
			closeAll();
		}
	} );

	/* ---- On small screens, close the panel after choosing a link ---- */
	nav.addEventListener( 'click', function ( event ) {
		var target = event.target;
		if ( target && target.tagName === 'A' && window.innerWidth < 960 && toggle ) {
			nav.classList.remove( 'toggled' );
			toggle.setAttribute( 'aria-expanded', 'false' );
		}
	} );
} )();
