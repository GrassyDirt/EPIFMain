<?php
/**
 * Shared protection for public EPIF forms (lead form, newsletter signup):
 * fresh-nonce endpoint, nonce check, honeypot + fill-time bot trap, per-IP rate limits.
 *
 * @package EPIF_Core
 */

defined( 'ABSPATH' ) || exit;

class EPIF_Guard {

	const REST_NAMESPACE = 'epif/v1';
	const MIN_FILL_SECS  = 3;

	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
	}

	public static function register_routes() {
		// Fresh nonce for the forms. Fetched by JS on submit so full-page caching
		// (LiteSpeed / SpeedyCache) never serves an expired nonce baked into HTML.
		register_rest_route(
			self::REST_NAMESPACE,
			'/token',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'token' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	public static function token() {
		$response = new WP_REST_Response( array( 'nonce' => wp_create_nonce( 'wp_rest' ) ) );
		$response->header( 'Cache-Control', 'no-store, private, max-age=0' );
		$response->header( 'X-LiteSpeed-Cache-Control', 'no-cache' );
		return $response;
	}

	/**
	 * REST args shared by every protected form.
	 */
	public static function trap_args() {
		return array(
			'website' => array( 'type' => 'string' ),  // Honeypot: must stay empty.
			'ts'      => array( 'type' => 'integer' ), // Form render time (unix seconds).
		);
	}

	/**
	 * @return WP_Error|null Error when the request's nonce is missing or invalid.
	 */
	public static function check_nonce( WP_REST_Request $request ) {
		$nonce = $request->get_header( 'x_wp_nonce' );
		if ( ! $nonce || ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
			return new WP_Error( 'epif_bad_nonce', __( 'Your session expired. Please refresh the page and try again.', 'epif' ), array( 'status' => 403 ) );
		}
		return null;
	}

	/**
	 * Honeypot filled or form submitted implausibly fast. Callers should pretend success.
	 */
	public static function is_bot( WP_REST_Request $request ) {
		$ts = (int) $request->get_param( 'ts' );
		return '' !== (string) $request->get_param( 'website' ) || ( $ts && ( time() - $ts ) < self::MIN_FILL_SECS );
	}

	/**
	 * Salted hash of the visitor IP: enough for rate limiting without storing raw IPs.
	 */
	public static function ip_hash() {
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		return hash_hmac( 'sha256', $ip, wp_salt( 'nonce' ) );
	}

	/**
	 * Whether this IP has used up its hourly allowance for $bucket.
	 */
	public static function is_rate_limited( $bucket, $limit ) {
		return (int) get_transient( self::rate_key( $bucket ) ) >= (int) $limit;
	}

	/**
	 * Count one submission against this IP's hourly allowance for $bucket.
	 */
	public static function record_hit( $bucket ) {
		$key = self::rate_key( $bucket );
		set_transient( $key, (int) get_transient( $key ) + 1, HOUR_IN_SECONDS );
	}

	private static function rate_key( $bucket ) {
		return 'epif_rl_' . $bucket . '_' . substr( self::ip_hash(), 0, 24 );
	}
}
