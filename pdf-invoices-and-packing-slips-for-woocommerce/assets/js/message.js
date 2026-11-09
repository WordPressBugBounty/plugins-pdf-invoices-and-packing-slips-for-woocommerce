/**
 * Deactivation feedback popup (Acowebs feedback UI kit).
 *
 * Opens when this plugin's "Deactivate" link on plugins.php is clicked. Never blocks deactivation:
 * "Send" follows the deactivate link as soon as the AJAX request finishes, or after 3 seconds,
 * whichever comes first. "Skip" deactivates immediately and sends nothing.
 *
 * Needs the `apifwDeactivation` object (ajaxUrl, nonce, slug) from wp_localize_script().
 */
( function () {
	'use strict';

	function init() {
		var cfg = window.apifwDeactivation;
		var overlay = document.getElementById( 'apifw-deactivation' );
		if ( ! cfg || ! overlay ) {
			return;
		}

		var form = overlay.querySelector( 'form' );
		var sendBtn = overlay.querySelector( '[data-acofb-action="send"]' );
		var consent = form.querySelector( 'input[name="consent"]' );
		var contactInputs = form.querySelectorAll( 'input[name="name"], input[name="email"]' );
		var deactivateUrl = '';
		var opener = null;
		var sent = false;

		function findLink() {
			return document.querySelector( 'tr[data-slug="' + cfg.slug + '"] .deactivate a' ) ||
				document.getElementById( 'deactivate-' + cfg.slug );
		}

		function deactivate() {
			if ( deactivateUrl ) {
				window.location.href = deactivateUrl;
			}
		}

		// Unticking the contact box hides the fields; disabling them also keeps them out of validation.
		function syncContact() {
			for ( var i = 0; i < contactInputs.length; i++ ) {
				contactInputs[ i ].disabled = ! consent.checked;
			}
		}

		function focusables() {
			return overlay.querySelectorAll( 'a[href], button:not([disabled]), textarea:not([disabled]), input:not([disabled])' );
		}

		function visible( el ) {
			return !! ( el.offsetWidth || el.offsetHeight || el.getClientRects().length );
		}

		function open( link ) {
			deactivateUrl = link.getAttribute( 'href' );
			opener = link;
			overlay.hidden = false;
			document.addEventListener( 'keydown', onKeydown );
			var first = form.querySelector( 'input[name="reason"]' );
			if ( first ) {
				first.focus();
			}
		}

		function close() {
			overlay.hidden = true;
			document.removeEventListener( 'keydown', onKeydown );
			form.reset();
			syncContact();
			if ( opener ) {
				opener.focus();
			}
		}

		function onKeydown( e ) {
			if ( 'Escape' === e.key ) {
				e.preventDefault();
				close();
				return;
			}
			// Keep keyboard focus inside the dialog.
			if ( 'Tab' === e.key ) {
				var items = Array.prototype.filter.call( focusables(), visible );
				if ( ! items.length ) {
					return;
				}
				var firstEl = items[ 0 ];
				var lastEl = items[ items.length - 1 ];
				if ( e.shiftKey && document.activeElement === firstEl ) {
					e.preventDefault();
					lastEl.focus();
				} else if ( ! e.shiftKey && document.activeElement === lastEl ) {
					e.preventDefault();
					firstEl.focus();
				}
			}
		}

		// Open on this plugin's Deactivate link only.
		document.addEventListener( 'click', function ( e ) {
			var link = findLink();
			if ( link && e.target.closest && e.target.closest( 'a' ) === link ) {
				e.preventDefault();
				open( link );
			}
		} );

		// Click on the dark backdrop closes.
		overlay.addEventListener( 'click', function ( e ) {
			if ( e.target === overlay ) {
				close();
			}
		} );

		overlay.querySelector( '[data-acofb-action="cancel"]' ).addEventListener( 'click', close );

		overlay.querySelector( '[data-acofb-action="skip"]' ).addEventListener( 'click', function () {
			deactivate(); // Sends nothing.
		} );

		consent.addEventListener( 'change', syncContact );

		form.addEventListener( 'submit', function ( e ) {
			e.preventDefault();
			if ( sent ) {
				return;
			}
			// Requires a reason (and a valid email while the contact box is ticked).
			if ( ! form.checkValidity() ) {
				form.reportValidity();
				return;
			}

			var reason = form.querySelector( 'input[name="reason"]:checked' );
			// Only the details box under the selected reason is sent.
			var details = reason.parentNode.querySelector( 'textarea[name="reason_details"]' );

			var body = new URLSearchParams();
			body.append( 'action', 'apifw_deactivation_feedback' );
			body.append( 'nonce', cfg.nonce );
			body.append( 'reason', reason.value );
			body.append( 'reason_details', details ? details.value : '' );
			body.append( 'consent', consent.checked ? '1' : '0' );
			if ( consent.checked ) {
				body.append( 'name', form.querySelector( 'input[name="name"]' ).value );
				body.append( 'email', form.querySelector( 'input[name="email"]' ).value );
			}

			sent = true;
			sendBtn.disabled = true;

			// Never block deactivation: go when the request ends, or after 3 seconds.
			setTimeout( deactivate, 3000 );
			fetch( cfg.ajaxUrl, { method: 'POST', credentials: 'same-origin', body: body } )
				.then( deactivate, deactivate );
		} );

		syncContact();
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
}() );
