<?php
/**
 * REST endpoint for landing page lead submissions.
 *
 *   POST /wp-json/epif/v1/leads
 *
 * Spam protection: WP nonce, honeypot field, minimum fill time, per-IP rate limit,
 * and Akismet (if active and configured).
 *
 * @package EPIF_Core
 */

defined( 'ABSPATH' ) || exit;

class EPIF_Lead_API {

	const NAMESPACE_V1  = 'epif/v1';
	const MIN_FILL_SECS = 3;

	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
	}

	public static function register_routes() {
		// Fresh nonce for the form. Fetched by JS on submit so full-page caching
		// (LiteSpeed / SpeedyCache) never serves an expired nonce baked into HTML.
		register_rest_route(
			self::NAMESPACE_V1,
			'/token',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'token' ),
				'permission_callback' => '__return_true',
			)
		);

		register_rest_route(
			self::NAMESPACE_V1,
			'/leads',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( __CLASS__, 'handle' ),
				'permission_callback' => '__return_true', // Public form; verified in handle().
				'args'                => array(
					'name'       => array( 'type' => 'string', 'required' => true, 'sanitize_callback' => 'sanitize_text_field' ),
					'email'      => array( 'type' => 'string', 'required' => true, 'sanitize_callback' => 'sanitize_email' ),
					'phone'      => array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ),
					'company'    => array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ),
					'service'    => array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ),
					'message'    => array( 'type' => 'string', 'sanitize_callback' => 'sanitize_textarea_field' ),
					'source_url' => array( 'type' => 'string', 'sanitize_callback' => 'esc_url_raw' ),
					'utm'        => array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ),
					'website'    => array( 'type' => 'string' ), // Honeypot: must stay empty.
					'ts'         => array( 'type' => 'integer' ), // Form render time (unix seconds).
				),
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
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response|WP_Error
	 */
	public static function handle( WP_REST_Request $request ) {
		$nonce = $request->get_header( 'x_wp_nonce' );
		if ( ! $nonce || ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
			return new WP_Error( 'epif_bad_nonce', __( 'Your session expired. Please refresh the page and try again.', 'epif' ), array( 'status' => 403 ) );
		}

		// Bots: honeypot filled or form submitted implausibly fast. Pretend success so they move on.
		$ts = (int) $request->get_param( 'ts' );
		if ( '' !== (string) $request->get_param( 'website' ) || ( $ts && ( time() - $ts ) < self::MIN_FILL_SECS ) ) {
			return self::success();
		}

		$ip_hash = self::ip_hash();
		$rl_key  = 'epif_rl_' . substr( $ip_hash, 0, 32 );
		$count   = (int) get_transient( $rl_key );
		if ( $count >= (int) EPIF_LEAD_RATE_LIMIT ) {
			return new WP_Error( 'epif_rate_limited', __( 'Too many submissions. Please try again later or call us directly.', 'epif' ), array( 'status' => 429 ) );
		}

		$data = array(
			'name'       => trim( (string) $request->get_param( 'name' ) ),
			'email'      => (string) $request->get_param( 'email' ),
			'phone'      => (string) $request->get_param( 'phone' ),
			'company'    => (string) $request->get_param( 'company' ),
			'service'    => (string) $request->get_param( 'service' ),
			'message'    => (string) $request->get_param( 'message' ),
			'source_url' => (string) $request->get_param( 'source_url' ),
			'utm'        => (string) $request->get_param( 'utm' ),
			'ip_hash'    => $ip_hash,
		);

		$errors = self::validate( $data );
		if ( $errors ) {
			return new WP_Error( 'epif_invalid', __( 'Please check the highlighted fields.', 'epif' ), array( 'status' => 422, 'fields' => $errors ) );
		}

		if ( self::is_spam( $data ) ) {
			return self::success();
		}

		$lead_id = EPIF_Leads::create( $data );
		if ( is_wp_error( $lead_id ) ) {
			return new WP_Error( 'epif_store_failed', __( 'Something went wrong. Please call or email us directly.', 'epif' ), array( 'status' => 500 ) );
		}

		set_transient( $rl_key, $count + 1, HOUR_IN_SECONDS );
		self::notify( $lead_id, $data );

		/**
		 * Fires after a landing page lead is stored. Hook CRM / webhook integrations here.
		 *
		 * @param int   $lead_id Lead post ID.
		 * @param array $data    Sanitized lead data.
		 */
		do_action( 'epif_lead_created', $lead_id, $data );

		return self::success();
	}

	/**
	 * @return array Field => error message.
	 */
	private static function validate( array $data ) {
		$errors = array();
		if ( '' === $data['name'] || mb_strlen( $data['name'] ) > 100 ) {
			$errors['name'] = __( 'Please enter your name.', 'epif' );
		}
		if ( ! is_email( $data['email'] ) ) {
			$errors['email'] = __( 'Please enter a valid email address.', 'epif' );
		}
		if ( '' !== $data['phone'] && ! preg_match( '/^[0-9+().\-\s]{7,25}$/', $data['phone'] ) ) {
			$errors['phone'] = __( 'Please enter a valid phone number.', 'epif' );
		}
		if ( mb_strlen( $data['message'] ) > 5000 ) {
			$errors['message'] = __( 'Message is too long (5000 characters max).', 'epif' );
		}
		foreach ( array( 'company', 'service', 'utm' ) as $field ) {
			if ( mb_strlen( $data[ $field ] ) > 200 ) {
				$errors[ $field ] = __( 'This field is too long.', 'epif' );
			}
		}
		return $errors;
	}

	/**
	 * Check with Akismet when the plugin is active and has an API key.
	 */
	private static function is_spam( array $data ) {
		if ( ! class_exists( 'Akismet' ) || ! method_exists( 'Akismet', 'get_api_key' ) || ! Akismet::get_api_key() ) {
			return false;
		}
		$query = array(
			'blog'                 => home_url(),
			'user_ip'              => isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '',
			'user_agent'           => isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '',
			'referrer'             => isset( $_SERVER['HTTP_REFERER'] ) ? esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : '',
			'comment_type'         => 'contact-form',
			'comment_author'       => $data['name'],
			'comment_author_email' => $data['email'],
			'comment_content'      => $data['message'],
		);
		$response = Akismet::http_post( http_build_query( $query ), 'comment-check' );
		return isset( $response[1] ) && 'true' === trim( $response[1] );
	}

	private static function notify( $lead_id, array $data ) {
		$to = EPIF_LEAD_NOTIFY_EMAIL ? EPIF_LEAD_NOTIFY_EMAIL : get_option( 'admin_email' );

		$lines = array(
			'New lead from ' . wp_parse_url( home_url(), PHP_URL_HOST ),
			'',
			'Name:    ' . $data['name'],
			'Email:   ' . $data['email'],
			'Phone:   ' . $data['phone'],
			'Company: ' . $data['company'],
			'Service: ' . $data['service'],
			'Source:  ' . $data['source_url'],
			'UTM:     ' . $data['utm'],
			'',
			$data['message'],
			'',
			'View in dashboard: ' . admin_url( 'post.php?post=' . (int) $lead_id . '&action=edit' ),
		);

		// Sent through GoSMTP when it is configured, so this lands in the inbox, not spam.
		wp_mail(
			$to,
			sprintf( '[EPIF] New lead: %s', $data['name'] ),
			implode( "\n", $lines ),
			array( 'Reply-To: ' . $data['name'] . ' <' . $data['email'] . '>' )
		);
	}

	/**
	 * Salted hash of the visitor IP: enough for rate limiting without storing raw IPs.
	 */
	private static function ip_hash() {
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		return hash_hmac( 'sha256', $ip, wp_salt( 'nonce' ) );
	}

	private static function success() {
		return new WP_REST_Response(
			array(
				'ok'      => true,
				'message' => __( 'Thanks! We received your request and will be in touch within one business day.', 'epif' ),
			),
			201
		);
	}
}
