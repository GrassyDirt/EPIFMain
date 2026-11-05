/**
 * EPIF — live countdown timers.
 *
 * Every element with class .epif-timer counts down from whatever it already
 * says, so a timer can be edited straight in the WordPress editor — type
 * "2h 30m" or "01:12:44" or "3d 4h" and the front end starts there.
 *
 * When real auctions are wired up (WooCommerce + an auction plugin, or a
 * custom API), give the element a data-epif-end attribute holding an ISO
 * timestamp and it will use that instead:
 *
 *     <p class="epif-timer" data-epif-end="2026-09-01T18:00:00Z">1h 12m</p>
 */
( function () {
	'use strict';

	var SEL = '.epif-timer';

	/**
	 * Turn "1h 12m", "22m", "3d 4h", "01:12:44" or "00:21" into seconds.
	 */
	function parseDuration( text ) {
		var s = String( text || '' ).trim().toLowerCase();
		if ( ! s ) {
			return 0;
		}

		// Clock form: hh:mm:ss or mm:ss
		if ( /^\d{1,3}(:\d{2}){1,2}$/.test( s ) ) {
			var parts = s.split( ':' ).map( Number );
			if ( parts.length === 3 ) {
				return parts[ 0 ] * 3600 + parts[ 1 ] * 60 + parts[ 2 ];
			}
			return parts[ 0 ] * 60 + parts[ 1 ];
		}

		// Unit form: 3d 4h 12m 30s (any subset, any order)
		var total = 0;
		var found = false;
		var units = { d: 86400, h: 3600, m: 60, s: 1 };
		s.replace( /(\d+(?:\.\d+)?)\s*([dhms])/g, function ( _, n, u ) {
			total += parseFloat( n ) * units[ u ];
			found = true;
			return '';
		} );

		return found ? Math.round( total ) : 0;
	}

	function format( secs ) {
		if ( secs <= 0 ) {
			return 'Ended';
		}

		var d = Math.floor( secs / 86400 );
		var h = Math.floor( ( secs % 86400 ) / 3600 );
		var m = Math.floor( ( secs % 3600 ) / 60 );
		var s = Math.floor( secs % 60 );
		var pad = function ( n ) {
			return n < 10 ? '0' + n : String( n );
		};

		if ( d > 0 ) {
			return d + 'd ' + h + 'h';
		}
		if ( h > 0 ) {
			return h + 'h ' + pad( m ) + 'm';
		}
		// Under an hour, show seconds ticking — this is the urgency moment.
		return pad( m ) + ':' + pad( s );
	}

	function init() {
		var nodes = Array.prototype.slice.call( document.querySelectorAll( SEL ) );
		if ( ! nodes.length ) {
			return;
		}

		var timers = nodes.map( function ( el ) {
			var end = el.getAttribute( 'data-epif-end' );
			var endsAt;

			if ( end ) {
				endsAt = Date.parse( end );
			}
			if ( ! endsAt || isNaN( endsAt ) ) {
				endsAt = Date.now() + parseDuration( el.textContent ) * 1000;
			}

			// Screen readers shouldn't hear every tick.
			el.setAttribute( 'aria-live', 'off' );

			return { el: el, endsAt: endsAt };
		} ).filter( function ( t ) {
			return t.endsAt > Date.now();
		} );

		if ( ! timers.length ) {
			return;
		}

		function tick() {
			var now = Date.now();
			var alive = 0;

			timers.forEach( function ( t ) {
				var left = Math.round( ( t.endsAt - now ) / 1000 );
				t.el.textContent = format( left );
				t.el.classList.toggle( 'epif-timer--urgent', left > 0 && left < 600 );
				t.el.classList.toggle( 'epif-timer--ended', left <= 0 );
				if ( left > 0 ) {
					alive++;
				}
			} );

			if ( alive ) {
				window.setTimeout( tick, 1000 );
			}
		}

		tick();
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();
