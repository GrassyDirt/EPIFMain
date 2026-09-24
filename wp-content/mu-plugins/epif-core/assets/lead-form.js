/**
 * EPIF lead form: submits to /wp-json/epif/v1/leads without a page reload.
 */
( function () {
	'use strict';

	var cfg = window.EPIF_LEAD;
	if ( ! cfg ) {
		return;
	}

	var loadedAt = Math.floor( Date.now() / 1000 );

	// Carry campaign parameters from the landing URL into the lead.
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
			setStatus( form, 'Please fill in the required fields.', true );
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

		fetch( cfg.tokenUrl, { credentials: 'same-origin', cache: 'no-store' } )
			.then( function ( r ) { return r.json(); } )
			.then( function ( t ) {
				return fetch( cfg.endpoint, {
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
					form.dispatchEvent( new CustomEvent( 'epif:lead', { bubbles: true } ) );
					// Conversion tracking hooks (fire only if the tag is installed).
					if ( typeof window.gtag === 'function' ) {
						window.gtag( 'event', 'generate_lead' );
					}
					if ( typeof window.fbq === 'function' ) {
						window.fbq( 'track', 'Lead' );
					}
				} else {
					markErrors( form, res.body.data && res.body.data.fields );
					setStatus( form, res.body.message || 'Something went wrong. Please try again.', true );
				}
			} )
			.catch( function () {
				setStatus( form, 'Network error. Please try again or contact us directly.', true );
			} )
			.finally( function () {
				button.disabled = false;
			} );
	}

	document.addEventListener( 'submit', function ( e ) {
		var form = e.target.closest( '.epif-lead-form' );
		if ( form ) {
			e.preventDefault();
			submit( form );
		}
	} );
} )();
