<?php
/**
 * Newsletter signups.
 *
 *   POST /wp-json/epif/v1/subscribe     { email, consent }
 *   GET  /?epif_nl=confirm&sid=&token=  (link in the confirmation email)
 *   GET  /?epif_nl=unsubscribe&sid=&sig=
 *
 * Double opt-in by default (EPIF_NEWSLETTER_DOUBLE_OPTIN): a subscriber is only
 * "confirmed" after clicking the emailed link. Subscribers are stored privately under
 * Subscribers in the dashboard and can be exported to CSV for any email platform
 * (Mailchimp, Brevo, ConvertKit, …).
 *
 * Form: [epif_newsletter_form button="Notify me"]
 *
 * @package EPIF_Core
 */

defined( 'ABSPATH' ) || exit;

class EPIF_Newsletter {

	const POST_TYPE      = 'epif_subscriber';
	const RESEND_SECONDS = 600; // Min gap between confirmation emails to one address.
	const PENDING_DAYS   = 30;  // Unconfirmed signups are deleted after this (see Privacy Policy).

	const CONSENT_TEXT = 'I agree to receive email updates from %s. I can unsubscribe at any time.';

	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_post_type' ) );
		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
		add_action( 'template_redirect', array( __CLASS__, 'handle_link' ), 0 );
		add_shortcode( 'epif_newsletter_form', array( __CLASS__, 'render_form' ) );

		add_filter( 'manage_' . self::POST_TYPE . '_posts_columns', array( __CLASS__, 'columns' ) );
		add_action( 'manage_' . self::POST_TYPE . '_posts_custom_column', array( __CLASS__, 'column_content' ), 10, 2 );
		add_action( 'admin_notices', array( __CLASS__, 'export_button' ) );
		add_action( 'admin_post_epif_export_subscribers', array( __CLASS__, 'export_csv' ) );

		add_action( 'epif_newsletter_cleanup', array( __CLASS__, 'cleanup' ) );
		add_action(
			'init',
			static function () {
				if ( ! wp_next_scheduled( 'epif_newsletter_cleanup' ) ) {
					wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'epif_newsletter_cleanup' );
				}
			}
		);
	}

	/* ---------------------------------------------------------------- Storage */

	public static function register_post_type() {
		register_post_type(
			self::POST_TYPE,
			array(
				'labels'              => array(
					'name'          => __( 'Subscribers', 'epif' ),
					'singular_name' => __( 'Subscriber', 'epif' ),
					'all_items'     => __( 'Subscribers', 'epif' ),
					'search_items'  => __( 'Search Subscribers', 'epif' ),
					'not_found'     => __( 'No subscribers yet.', 'epif' ),
				),
				'public'              => false,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'show_in_rest'        => false,
				'exclude_from_search' => true,
				'publicly_queryable'  => false,
				'menu_position'       => 26,
				'menu_icon'           => 'dashicons-megaphone',
				'supports'            => array( 'title' ),
				'capabilities'        => array( 'create_posts' => 'do_not_allow' ),
				'map_meta_cap'        => true,
			)
		);
	}

	private static function find( $email ) {
		$ids = get_posts(
			array(
				'post_type'        => self::POST_TYPE,
				'post_status'      => 'any',
				'meta_key'         => '_epif_email', // phpcs:ignore WordPress.DB.SlowDBQuery -- small table, exact match.
				'meta_value'       => $email, // phpcs:ignore WordPress.DB.SlowDBQuery
				'fields'           => 'ids',
				'posts_per_page'   => 1,
				'suppress_filters' => true,
			)
		);
		return $ids ? (int) $ids[0] : 0;
	}

	/**
	 * Daily: permanently delete signups that were never confirmed.
	 */
	public static function cleanup() {
		$ids = get_posts(
			array(
				'post_type'      => self::POST_TYPE,
				'post_status'    => 'any',
				'fields'         => 'ids',
				'posts_per_page' => 200,
				'meta_key'       => '_epif_status', // phpcs:ignore WordPress.DB.SlowDBQuery
				'meta_value'     => 'pending', // phpcs:ignore WordPress.DB.SlowDBQuery
				'date_query'     => array( array( 'before' => self::PENDING_DAYS . ' days ago', 'column' => 'post_date_gmt' ) ),
			)
		);
		foreach ( $ids as $id ) {
			wp_delete_post( $id, true );
		}
	}

	private static function status( $sid ) {
		return (string) get_post_meta( $sid, '_epif_status', true );
	}

	private static function set_status( $sid, $status ) {
		update_post_meta( $sid, '_epif_status', $status );
		update_post_meta( $sid, '_epif_' . $status . '_at', gmdate( 'c' ) );
	}

	/* ---------------------------------------------------------------- REST */

	public static function register_routes() {
		register_rest_route(
			EPIF_Guard::REST_NAMESPACE,
			'/subscribe',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( __CLASS__, 'subscribe' ),
				'permission_callback' => '__return_true', // Public form; verified in subscribe().
				'args'                => EPIF_Guard::trap_args() + array(
					'email'      => array( 'type' => 'string', 'required' => true, 'sanitize_callback' => 'sanitize_email' ),
					'consent'    => array( 'type' => 'string' ),
					'source_url' => array( 'type' => 'string', 'sanitize_callback' => 'esc_url_raw' ),
					'utm'        => array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ),
				),
			)
		);
	}

	/**
	 * @return WP_REST_Response|WP_Error
	 */
	public static function subscribe( WP_REST_Request $request ) {
		$error = EPIF_Guard::check_nonce( $request );
		if ( $error ) {
			return $error;
		}
		if ( EPIF_Guard::is_bot( $request ) ) {
			return self::success();
		}
		// Counts every attempt, so the form can't be used to flood someone's inbox.
		if ( EPIF_Guard::is_rate_limited( 'nl', EPIF_NEWSLETTER_RATE_LIMIT ) ) {
			return new WP_Error( 'epif_rate_limited', __( 'Too many attempts. Please try again later.', 'epif' ), array( 'status' => 429 ) );
		}

		$email   = strtolower( (string) $request->get_param( 'email' ) );
		$consent = in_array( (string) $request->get_param( 'consent' ), array( 'on', '1', 'true', 'yes' ), true );

		$errors = array();
		if ( ! is_email( $email ) ) {
			$errors['email'] = __( 'Please enter a valid email address.', 'epif' );
		}
		if ( ! $consent ) {
			$errors['consent'] = __( 'Please tick the box to agree to receive emails.', 'epif' );
		}
		if ( $errors ) {
			return new WP_Error( 'epif_invalid', reset( $errors ), array( 'status' => 422, 'fields' => $errors ) );
		}

		EPIF_Guard::record_hit( 'nl' );

		$sid = self::find( $email );
		if ( $sid && 'confirmed' === self::status( $sid ) ) {
			// Already subscribed. Same answer as a new signup so the form can't be used
			// to discover who is on the list.
			return self::success();
		}

		if ( ! $sid ) {
			$sid = wp_insert_post(
				array(
					'post_type'   => self::POST_TYPE,
					'post_status' => 'private',
					'post_title'  => $email,
					'meta_input'  => array(
						'_epif_email'      => $email,
						'_epif_source_url' => (string) $request->get_param( 'source_url' ),
						'_epif_utm'        => mb_substr( (string) $request->get_param( 'utm' ), 0, 200 ),
					),
				),
				true
			);
			if ( is_wp_error( $sid ) ) {
				return new WP_Error( 'epif_store_failed', __( 'Something went wrong. Please try again later.', 'epif' ), array( 'status' => 500 ) );
			}
		}

		// Proof of consent: what they agreed to, when, and a hashed IP.
		update_post_meta( $sid, '_epif_consent_text', sprintf( self::CONSENT_TEXT, EPIF_Business::get( 'brand' ) ) );
		update_post_meta( $sid, '_epif_consent_at', gmdate( 'c' ) );
		update_post_meta( $sid, '_epif_ip_hash', EPIF_Guard::ip_hash() );

		if ( EPIF_NEWSLETTER_DOUBLE_OPTIN ) {
			self::set_status( $sid, 'pending' );
			self::send_confirmation( $sid, $email );
		} else {
			self::confirm( $sid );
		}

		return self::success();
	}

	private static function success() {
		$message = EPIF_NEWSLETTER_DOUBLE_OPTIN
			? __( 'Almost done! Check your inbox and click the link to confirm your subscription.', 'epif' )
			: __( "You're on the list! We'll let you know when we launch.", 'epif' );
		return new WP_REST_Response( array( 'ok' => true, 'message' => $message ), 201 );
	}

	private static function confirm( $sid ) {
		self::set_status( $sid, 'confirmed' );
		delete_post_meta( $sid, '_epif_token_hash' );

		/**
		 * Fires when a subscriber confirms. Hook an email-platform sync here.
		 *
		 * @param int    $sid   Subscriber post ID.
		 * @param string $email Subscriber email.
		 */
		do_action( 'epif_subscriber_confirmed', $sid, get_post_meta( $sid, '_epif_email', true ) );
	}

	/* ---------------------------------------------------------------- Emails */

	private static function send_confirmation( $sid, $email ) {
		$last = (int) get_post_meta( $sid, '_epif_confirm_sent', true );
		if ( $last && ( time() - $last ) < self::RESEND_SECONDS ) {
			return;
		}

		$token = wp_generate_password( 32, false );
		update_post_meta( $sid, '_epif_token_hash', wp_hash( $token ) );
		update_post_meta( $sid, '_epif_confirm_sent', time() );

		$brand   = EPIF_Business::get( 'brand' );
		$confirm = add_query_arg( array( 'epif_nl' => 'confirm', 'sid' => $sid, 'token' => $token ), home_url( '/' ) );

		$lines = array(
			sprintf( 'Thanks for signing up for updates from %s!', $brand ),
			'',
			'Please confirm your email address by opening this link:',
			$confirm,
			'',
			"If you didn't sign up, ignore this email and you won't hear from us again.",
			'',
			'--',
			$brand,
		);
		if ( ! EPIF_Business::is_placeholder( 'address' ) ) {
			$lines[] = EPIF_Business::get( 'address' );
		}
		$lines[] = 'Unsubscribe: ' . self::unsubscribe_url( $sid );

		wp_mail( $email, sprintf( 'Confirm your subscription to %s', $brand ), implode( "\n", $lines ) );
	}

	/**
	 * Signed one-click unsubscribe link for a subscriber. Include it in every newsletter.
	 */
	public static function unsubscribe_url( $sid ) {
		return add_query_arg( array( 'epif_nl' => 'unsubscribe', 'sid' => (int) $sid, 'sig' => self::signature( $sid ) ), home_url( '/' ) );
	}

	private static function signature( $sid ) {
		return substr( hash_hmac( 'sha256', 'unsub|' . (int) $sid . '|' . get_post_meta( $sid, '_epif_email', true ), wp_salt( 'auth' ) ), 0, 32 );
	}

	/* ---------------------------------------------------------------- Email links */

	public static function handle_link() {
		// phpcs:disable WordPress.Security.NonceVerification -- signed links from email.
		if ( empty( $_GET['epif_nl'] ) || empty( $_GET['sid'] ) ) {
			return;
		}
		$action = sanitize_key( wp_unslash( $_GET['epif_nl'] ) );
		$sid    = absint( $_GET['sid'] );
		$valid  = $sid && self::POST_TYPE === get_post_type( $sid );
		$result = 'invalid';

		if ( $valid && 'confirm' === $action && isset( $_GET['token'] ) ) {
			$token = sanitize_text_field( wp_unslash( $_GET['token'] ) );
			$hash  = (string) get_post_meta( $sid, '_epif_token_hash', true );
			if ( 'confirmed' === self::status( $sid ) ) {
				$result = 'confirmed';
			} elseif ( $hash && hash_equals( $hash, wp_hash( $token ) ) ) {
				self::confirm( $sid );
				$result = 'confirmed';
			}
		} elseif ( $valid && 'unsubscribe' === $action && isset( $_GET['sig'] ) ) {
			if ( hash_equals( self::signature( $sid ), sanitize_text_field( wp_unslash( $_GET['sig'] ) ) ) ) {
				self::set_status( $sid, 'unsubscribed' );
				delete_post_meta( $sid, '_epif_token_hash' );
				do_action( 'epif_subscriber_unsubscribed', $sid, get_post_meta( $sid, '_epif_email', true ) );
				$result = 'unsubscribed';
			}
		}
		// phpcs:enable

		nocache_headers();
		wp_safe_redirect( add_query_arg( 'epif_nl_msg', $result, home_url( '/' ) ) . '#newsletter' );
		exit;
	}

	/* ---------------------------------------------------------------- Form */

	public static function render_form( $atts ) {
		$atts = shortcode_atts( array( 'button' => __( 'Notify me', 'epif' ) ), $atts, 'epif_newsletter_form' );

		EPIF_Forms::enqueue();
		$uid = wp_unique_id( 'epif-nl-' );

		$notices = array(
			'confirmed'    => __( "You're confirmed. Thanks for subscribing!", 'epif' ),
			'unsubscribed' => __( "You've been unsubscribed and won't receive further emails.", 'epif' ),
			'invalid'      => __( 'That link is invalid or has expired. Please sign up again.', 'epif' ),
		);
		$msg = isset( $_GET['epif_nl_msg'] ) ? sanitize_key( wp_unslash( $_GET['epif_nl_msg'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification

		$privacy = EPIF_Legal::url( 'privacy-policy' );
		$consent = esc_html( sprintf( self::CONSENT_TEXT, EPIF_Business::get( 'brand' ) ) );
		if ( $privacy ) {
			/* translators: %s: Privacy Policy link. */
			$consent .= ' ' . sprintf( __( 'See our %s.', 'epif' ), '<a href="' . esc_url( $privacy ) . '">' . esc_html__( 'Privacy Policy', 'epif' ) . '</a>' );
		}

		ob_start();
		?>
		<form class="epif-form epif-newsletter-form" id="<?php echo esc_attr( $uid ); ?>" data-epif-endpoint="subscribe" data-epif-event="sign_up" novalidate>
			<?php if ( isset( $notices[ $msg ] ) ) : ?>
				<p class="epif-notice<?php echo 'invalid' === $msg ? ' is-error' : ''; ?>" role="status"><?php echo esc_html( $notices[ $msg ] ); ?></p>
			<?php endif; ?>
			<div class="epif-inline">
				<label class="screen-reader-text" for="<?php echo esc_attr( $uid ); ?>-email"><?php esc_html_e( 'Email address', 'epif' ); ?></label>
				<input id="<?php echo esc_attr( $uid ); ?>-email" name="email" type="email" autocomplete="email" required maxlength="254" placeholder="<?php esc_attr_e( 'you@example.com', 'epif' ); ?>">
				<button type="submit" class="wp-element-button"><?php echo esc_html( $atts['button'] ); ?></button>
			</div>
			<div class="epif-check">
				<input id="<?php echo esc_attr( $uid ); ?>-consent" name="consent" type="checkbox" required>
				<label for="<?php echo esc_attr( $uid ); ?>-consent"><?php echo $consent; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped above. ?></label>
			</div>
			<div class="epif-hp" aria-hidden="true">
				<label for="<?php echo esc_attr( $uid ); ?>-website">Website</label>
				<input id="<?php echo esc_attr( $uid ); ?>-website" name="website" type="text" tabindex="-1" autocomplete="off">
			</div>
			<p class="epif-status" role="status" aria-live="polite"></p>
		</form>
		<?php
		return ob_get_clean();
	}

	/* ---------------------------------------------------------------- Admin */

	public static function columns( $columns ) {
		return array(
			'cb'          => $columns['cb'],
			'title'       => __( 'Email', 'epif' ),
			'epif_status' => __( 'Status', 'epif' ),
			'epif_source' => __( 'Source', 'epif' ),
			'date'        => __( 'Signed up', 'epif' ),
		);
	}

	public static function column_content( $column, $post_id ) {
		if ( 'epif_status' === $column ) {
			echo esc_html( ucfirst( self::status( $post_id ) ) );
		} elseif ( 'epif_source' === $column ) {
			echo esc_html( (string) get_post_meta( $post_id, '_epif_utm', true ) );
		}
	}

	public static function export_button() {
		$screen = get_current_screen();
		if ( ! $screen || 'edit-' . self::POST_TYPE !== $screen->id || ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$url = wp_nonce_url( admin_url( 'admin-post.php?action=epif_export_subscribers' ), 'epif_export_subscribers' );
		printf(
			'<div class="notice notice-info"><p>%s <a class="button" href="%s">%s</a></p></div>',
			esc_html__( 'Export confirmed subscribers to import into your email platform.', 'epif' ),
			esc_url( $url ),
			esc_html__( 'Download CSV', 'epif' )
		);
	}

	public static function export_csv() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Not allowed.', 'epif' ), 403 );
		}
		check_admin_referer( 'epif_export_subscribers' );

		$ids = get_posts(
			array(
				'post_type'      => self::POST_TYPE,
				'post_status'    => 'any',
				'fields'         => 'ids',
				'posts_per_page' => -1,
				'meta_key'       => '_epif_status', // phpcs:ignore WordPress.DB.SlowDBQuery
				'meta_value'     => 'confirmed', // phpcs:ignore WordPress.DB.SlowDBQuery
				'orderby'        => 'date',
				'order'          => 'ASC',
			)
		);

		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=epif-subscribers-' . gmdate( 'Y-m-d' ) . '.csv' );

		$out = fopen( 'php://output', 'w' );
		fputcsv( $out, array( 'email', 'status', 'signed_up', 'confirmed_at', 'consent_text', 'source_url', 'utm' ) );
		foreach ( $ids as $id ) {
			$row = array(
				get_post_meta( $id, '_epif_email', true ),
				self::status( $id ),
				get_post_field( 'post_date_gmt', $id ),
				get_post_meta( $id, '_epif_confirmed_at', true ),
				get_post_meta( $id, '_epif_consent_text', true ),
				get_post_meta( $id, '_epif_source_url', true ),
				get_post_meta( $id, '_epif_utm', true ),
			);
			// Neutralise spreadsheet formulas (CSV injection).
			$row = array_map(
				static function ( $cell ) {
					$cell = (string) $cell;
					return ( '' !== $cell && in_array( $cell[0], array( '=', '+', '-', '@' ), true ) ) ? "'" . $cell : $cell;
				},
				$row
			);
			fputcsv( $out, $row );
		}
		fclose( $out ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		exit;
	}
}
