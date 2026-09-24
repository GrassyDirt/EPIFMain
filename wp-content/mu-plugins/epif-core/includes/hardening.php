<?php
/**
 * Security defaults for epifservices.com.
 *
 * Complements Loginizer (login brute-force protection); does not duplicate it.
 *
 * @package EPIF_Core
 */

defined( 'ABSPATH' ) || exit;

// XML-RPC is a common brute-force / pingback-DDoS target and nothing on this site uses it.
// Refuse the endpoint outright (the xmlrpc_enabled filter alone still leaves system.* and pingbacks open).
if ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) {
	status_header( 403 );
	exit;
}
add_filter( 'xmlrpc_enabled', '__return_false' );
add_filter( 'xmlrpc_methods', '__return_empty_array' );
add_filter( 'pings_open', '__return_false', 20 );
add_filter(
	'wp_headers',
	static function ( $headers ) {
		unset( $headers['X-Pingback'] );
		return $headers;
	}
);

// Don't advertise the WordPress version.
remove_action( 'wp_head', 'wp_generator' );
add_filter( 'the_generator', '__return_empty_string' );

// Block username enumeration via /?author=N for visitors.
add_action(
	'template_redirect',
	static function () {
		if ( ! is_user_logged_in() && isset( $_GET['author'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			wp_safe_redirect( home_url( '/' ), 301 );
			exit;
		}
	}
);

// ...and via the REST API users endpoint.
add_filter(
	'rest_endpoints',
	static function ( $endpoints ) {
		if ( ! is_user_logged_in() ) {
			unset( $endpoints['/wp/v2/users'], $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );
		}
		return $endpoints;
	}
);

// Generic login error so attackers can't tell whether a username exists.
add_filter(
	'login_errors',
	static function () {
		return __( 'Invalid login details.', 'epif' );
	}
);

// Baseline security headers. Set EPIF_HSTS to true in wp-config.php once HTTPS is confirmed everywhere.
add_action(
	'send_headers',
	static function () {
		if ( headers_sent() ) {
			return;
		}
		header( 'X-Content-Type-Options: nosniff' );
		header( 'X-Frame-Options: SAMEORIGIN' );
		header( 'Referrer-Policy: strict-origin-when-cross-origin' );
		header( 'Permissions-Policy: camera=(), microphone=(), geolocation=(), interest-cohort=()' );
		if ( defined( 'EPIF_HSTS' ) && EPIF_HSTS && is_ssl() ) {
			header( 'Strict-Transport-Security: max-age=31536000' );
		}
	}
);

// Comments off everywhere: this is a business site, not a blog, and open comment forms
// mostly attract spam. (Also keeps the Cookie Policy accurate: no comment cookies.)
add_filter( 'comments_open', '__return_false', 20 );
add_filter( 'comments_array', '__return_empty_array' );
add_action(
	'init',
	static function () {
		foreach ( get_post_types() as $post_type ) {
			remove_post_type_support( $post_type, 'comments' );
			remove_post_type_support( $post_type, 'trackbacks' );
		}
	},
	100
);
add_action(
	'admin_menu',
	static function () {
		remove_menu_page( 'edit-comments.php' );
	}
);
add_action(
	'admin_bar_menu',
	static function ( $bar ) {
		$bar->remove_node( 'comments' );
	},
	999
);
