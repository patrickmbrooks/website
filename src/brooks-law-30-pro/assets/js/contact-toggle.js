/**
 * Brooks Law 3.1 — text/call toggle.
 *
 * Two jobs:
 *   1. A matter chip sets what the text button will send.
 *   2. On desktops, where sms: links open nothing, the text button copies the
 *      number instead of looking broken.
 *
 * Without this file the toggle still works — the text button just stays on
 * whichever matter the page rendered with.
 */
(function () {
	'use strict';

	var toggles = document.querySelectorAll('[data-contact-toggle]');
	if (!toggles.length) {
		return;
	}

	// A device with no touch and no coarse pointer will not open a messaging
	// app from an sms: link, so the text side changes behaviour there.
	var canText = ('ontouchstart' in window) ||
		navigator.maxTouchPoints > 0 ||
		(window.matchMedia && window.matchMedia('(pointer: coarse)').matches);

	Array.prototype.forEach.call(toggles, function (toggle) {
		var chips = toggle.querySelectorAll('.ct-chip');
		var text = toggle.querySelector('[data-ct-text]');

		if (text) {
			Array.prototype.forEach.call(chips, function (chip) {
				chip.addEventListener('click', function () {
					Array.prototype.forEach.call(chips, function (other) {
						other.setAttribute('aria-pressed', other === chip ? 'true' : 'false');
					});

					var sms = chip.getAttribute('data-sms');
					if (sms) {
						text.setAttribute('href', sms);
					}
				});
			});
		}

		if (text && !canText) {
			toggle.classList.add('ct-no-sms');
			text.addEventListener('click', function (event) {
				var number = text.getAttribute('data-number') || '';
				if (!number) {
					return;
				}

				event.preventDefault();

				var label = toggle.querySelector('[data-ct-text-label]');
				var said = label ? label.textContent : '';

				var done = function (message) {
					if (!label) {
						return;
					}
					label.textContent = message;
					window.setTimeout(function () {
						label.textContent = said;
					}, 2600);
				};

				if (navigator.clipboard && navigator.clipboard.writeText) {
					navigator.clipboard.writeText(number).then(
						function () {
							done(text.getAttribute('data-copied') || 'Number copied');
						},
						function () {
							done(text.getAttribute('data-copy-failed') || 'Text us at this number');
						}
					);
				} else {
					done(text.getAttribute('data-copy-failed') || 'Text us at this number');
				}
			});
		}
	});
})();
