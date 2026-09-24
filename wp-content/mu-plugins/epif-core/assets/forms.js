/**
 * EPIF forms: submits any <form class="epif-form" data-epif-endpoint="…"> to
 * /wp-json/epif/v1/<endpoint> without a page reload.
 */
( function () {
	'use strict';

	var cfg = window.EPIF_FORMS;
	if ( ! cfg ) {
		return;
	}

	var loadedAt = Math.floor( Date.now() / 1000 );

	// Conversion events fired on success, when the matching tag is installed.
	var EVENTS = {
		generate_lead: { gtag: 'generate_lead', fbq: 'Lead' },
		sign_up: { gtag: 'sign_up', fbq: 'CompleteRegistration' },
	};

	// Carry campaign parameters from the landing URL into the submission.
	function utm() {
		var params = new URLSearchParams( window.location.search );
		var keys = [ 'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'gclid', 'fbclid' ];
		return keys
			.filter( function ( k ) { return params.get( k ); } )
			.map( function ( k ) { return k + '=' + params.get( k ); } )
			.join( '&' );
	}

	function setStatus( form, text, isError ) {
		var el = form.querySelector( '.epif-status' );
		el.textContent = text;
		el.classList.toggle( 'is-error', !! isError );
	}

	function clearErrors( form ) {
		form.querySelectorAll( '[aria-invalid]' ).forEach( function ( el ) {
			el.removeAttribute( 'aria-invalid' );
		} );
	}

	function markErrors( form, fields ) {
		Object.keys( fields || {} ).forEach( function ( name ) {
			var el = form.elements[ name ];
			if ( el ) {
				el.setAttribute( 'aria-invalid', 'true' );
			}
		} );
		var first = form.querySelector( '[aria-invalid="true"]' );
		if ( first ) {
			first.focus();
		}
	}

	function track( name ) {
		var ev = EVENTS[ name ];
		if ( ! ev ) {
			return;
		}
		if ( typeof window.gtag === 'function' ) {
			window.gtag( 'event', ev.gtag );
		}
		if ( typeof window.fbq === 'function' ) {
			window.fbq( 'track', ev.fbq );
		}
	}

	function submit( form ) {
		var button = form.querySelector( 'button[type="submit"]' );
		clearErrors( form );

		if ( ! form.checkValidity() ) {
			var invalid = {};
			Array.prototype.forEach.call( form.elements, function ( el ) {
				if ( el.name && el.validity && ! el.validity.valid ) {
					invalid[ el.name ] = true;
				}
			} );
			markErrors( form, invalid );
			setStatus( form, 'Please complete the highlighted fields.', true );
			return;
		}

		var data = {};
		new FormData( form ).forEach( function ( value, key ) {
			data[ key ] = value;
		} );
		data.source_url = window.location.href.split( '#' )[ 0 ];
		data.utm = utm();
		data.ts = loadedAt;

		button.disabled = true;
		setStatus( form, 'Sending…' );

		fetch( cfg.restBase + 'token', { credentials: 'same-origin', cache: 'no-store' } )
			.then( function ( r ) { return r.json(); } )
			.then( function ( t ) {
				return fetch( cfg.restBase + form.dataset.epifEndpoint, {
					method: 'POST',
					credentials: 'same-origin',
					headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': t.nonce },
					body: JSON.stringify( data ),
				} );
			} )
			.then( function ( r ) {
				return r.json().then( function ( body ) { return { ok: r.ok, body: body }; } );
			} )
			.then( function ( res ) {
				if ( res.ok ) {
					form.reset();
					setStatus( form, res.body.message );
					form.dispatchEvent( new CustomEvent( 'epif:success', { bubbles: true } ) );
					track( form.dataset.epifEvent );
				} else {
					markErrors( form, res.body.data && res.body.data.fields );
					setStatus( form, res.body.message || 'Something went wrong. Please try again.', true );
				}
			} )
			.catch( function () {
				setStatus( form, 'Network error. Please try again.', true );
			} )
			.finally( function () {
				button.disabled = false;
			} );
	}

	document.addEventListener( 'submit', function ( e ) {
		var form = e.target.closest( '.epif-form[data-epif-endpoint]' );
		if ( form ) {
			e.preventDefault();
			submit( form );
		}
	} );
} )();
