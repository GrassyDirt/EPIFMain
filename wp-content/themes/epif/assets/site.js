/**
 * EPIF front end: location strip + ZIP personalization, instant estimate,
 * service/partner preselection. No dependencies.
 *
 * The HTML always ships the default all-areas version (what crawlers see).
 * This script only swaps content after load, and only from a ZIP the visitor
 * typed (saved in localStorage). IP lookup (geo.php, spec §3) can call
 * epifApplyPlace() later.
 */
( function () {
	'use strict';

	var data = window.EPIF_AREAS || { places: {}, zips: {}, prices: {} };
	var STATES = { MD: 'Maryland', PA: 'Pennsylvania', DC: 'Washington, DC' };
	var KEY = 'epif_zip';

	function $( sel, root ) { return ( root || document ).querySelector( sel ); }
	function $$( sel, root ) { return Array.prototype.slice.call( ( root || document ).querySelectorAll( sel ) ); }
	function store( v ) { try { if ( v ) { localStorage.setItem( KEY, v ); } else { localStorage.removeItem( KEY ); } } catch ( e ) {} }
	function load() { try { return localStorage.getItem( KEY ); } catch ( e ) { return null; } }

	function lookup( zip ) {
		if ( ! /^\d{5}$/.test( zip ) ) { return null; }
		var key = data.zips[ zip ] || ( zip.indexOf( '200' ) === 0 ? 'washington-dc' : '' );
		return key ? { key: key, p: data.places[ key ] } : { outside: true };
	}

	var strip = $( '[data-epif-strip]' );
	var defaultStrip = strip ? $( '[data-epif-strip-text]', strip ).textContent : '';
	var col = '';

	function apply( hit ) {
		var h1 = $( '[data-epif-h1]' ), eyebrow = $( '[data-epif-eyebrow]' ), note = $( '[data-epif-price-note]' );
		var text = strip && $( '[data-epif-strip-text]', strip ), btn = strip && $( '[data-epif-zip-toggle]', strip );
		var close = strip && $( '[data-epif-banner-close]', strip );
		col = '';
		if ( strip ) { strip.classList.remove( 'is-outside' ); close.hidden = true; }

		if ( hit && hit.p ) {
			var town = hit.p[ 0 ], st = hit.p[ 1 ], county = hit.p[ 2 ], rural = hit.p[ 3 ];
			var label = st === 'DC' ? 'Washington, DC' : town + ', ' + st;
			col = st.toLowerCase();
			if ( h1 ) { h1.textContent = 'Junk removal in ' + label + ', priced from your photos'; }
			if ( eyebrow ) { eyebrow.textContent = st === 'DC' ? 'Washington, DC' : county + ', ' + STATES[ st ]; }
			if ( note ) { note.textContent = 'Showing ' + STATES[ st ] + ' prices for ' + label + '.'; }
			if ( text ) { text.textContent = 'Showing prices and services for ' + label + ' · ' + county; btn.textContent = 'Not in ' + town + '? Change ZIP'; }
			$$( '[data-epif-barn]' ).forEach( function ( el ) {
				el.hidden = ! rural;
				if ( el.classList.contains( 'epif-card' ) ) { el.style.order = rural ? '0' : '2'; }
			} );
			var tag = $( '[data-epif-barn-tag]' );
			if ( tag ) { tag.textContent = rural ? 'Featured in ' + county : 'Rural counties'; }
		} else {
			$$( '[data-epif-barn]' ).forEach( function ( el ) { el.hidden = false; if ( el.classList.contains( 'epif-card' ) ) { el.style.order = '2'; } } );
			if ( text ) { text.textContent = defaultStrip; btn.textContent = 'Enter your ZIP for local prices'; }
			if ( hit && hit.outside && strip ) {
				strip.classList.add( 'is-outside' );
				close.hidden = false;
				text.innerHTML = '<strong>Outside our pickup area?</strong> <a href="https://epifservices.shop">Shop nationwide at epifservices.shop</a> · <a href="#listing-tool">Listing tool pre-order interest</a> · Virginia is coming soon.';
			}
		}
		$$( '[data-epif-col]' ).forEach( function ( el ) { el.classList.toggle( 'is-yours', !! col && el.getAttribute( 'data-epif-col' ) === col ); } );
		estimate();
	}
	window.epifApplyPlace = apply;

	// ZIP popover.
	if ( strip ) {
		var form = $( '[data-epif-zip-form]', strip ), err = $( '[data-epif-zip-error]', strip );
		$$( '[data-epif-zip-toggle]' ).forEach( function ( b ) {
			b.addEventListener( 'click', function () {
				form.hidden = ! form.hidden;
				$$( '[data-epif-zip-toggle]' ).forEach( function ( x ) { x.setAttribute( 'aria-expanded', String( ! form.hidden ) ); } );
				if ( ! form.hidden ) { strip.scrollIntoView( { block: 'start' } ); form.zip.focus(); }
			} );
		} );
		form.addEventListener( 'submit', function ( e ) {
			e.preventDefault();
			var zip = form.zip.value.trim(), hit = lookup( zip );
			if ( ! hit ) { err.textContent = 'Enter a 5-digit ZIP code.'; return; }
			err.textContent = '';
			store( zip );
			form.hidden = true;
			apply( hit );
		} );
		$( '[data-epif-banner-close]', strip ).addEventListener( 'click', function () { apply( null ); } );
	}

	// Instant estimate. Uses prices from functions.php; null prices mean "not published".
	var est = $( '[data-epif-estimate]' );
	function estimate() {
		if ( ! est ) { return; }
		var c = col || 'md', tier = ( $( 'input[name=tier]:checked', est ) || {} ).value;
		var base = data.prices[ tier ] && data.prices[ tier ][ c ];
		var out = $( '[data-epif-est-value]', est );
		if ( base == null ) { out.textContent = 'Send photos for a price'; return; }
		var total = Number( base );
		$$( 'input[name=item]:checked', est ).forEach( function ( i ) {
			var p = data.prices[ i.value ] && data.prices[ i.value ][ c ];
			if ( p != null ) { total += Number( p ); }
		} );
		out.textContent = '$' + Math.round( total ) + ' – $' + Math.round( total * 1.35 );
	}
	if ( est ) { est.addEventListener( 'change', estimate ); est.addEventListener( 'submit', function ( e ) { e.preventDefault(); } ); }

	// "Get a quote" / "Discuss consignment" buttons preselect the service in the form they jump to.
	function preselect( sectionSel, value ) {
		var sel = $( sectionSel + ' select[name=service]' );
		if ( sel ) { sel.value = value; }
	}
	$$( '[data-epif-service]' ).forEach( function ( a ) { a.addEventListener( 'click', function () { preselect( '#quote', a.getAttribute( 'data-epif-service' ) ); } ); } );
	$$( '[data-epif-partner]' ).forEach( function ( a ) {
		a.addEventListener( 'click', function () {
			preselect( '#partners', a.getAttribute( 'data-epif-partner' ) );
			var msg = $( '#partners textarea[name=message]' );
			if ( msg && ! msg.value ) { msg.value = 'Consignment or buyout: '; }
		} );
	} );

	// Sticky mobile bar: hide while the quote form is on screen.
	var sticky = $( '[data-epif-sticky]' ), quote = $( '#quote' );
	if ( sticky && quote && 'IntersectionObserver' in window ) {
		new IntersectionObserver( function ( entries ) { sticky.classList.toggle( 'is-hidden', entries[ 0 ].isIntersecting ); } ).observe( quote );
	}

	var saved = load();
	if ( saved ) { apply( lookup( saved ) ); } else { estimate(); }
}() );
