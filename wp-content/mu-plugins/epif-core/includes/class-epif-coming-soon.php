<?php
/**
 * Coming-soon mode (EPIF_COMING_SOON in wp-config.php).
 *
 * While on, visitors see the theme's "coming-soon" template on the homepage and every
 * other page redirects there, except the legal pages. Logged-in editors see the real
 * site, and can preview the coming-soon page at /?epif_preview=coming-soon.
 *
 * Turning it off: set EPIF_COMING_SOON to false, then purge the page cache
 * (LiteSpeed Cache → Purge All).
 *
 * @package EPIF_Core
 */

defined( 'ABSPATH' ) || exit;

class EPIF_Coming_Soon {

	public static function init() {
		if ( ! EPIF_COMING_SOON ) {
			return;
		}
		add_action( 'template_redirect', array( __CLASS__, 'gate' ), 99 );
		add_filter( 'frontpage_template_hierarchy', array( __CLASS__, 'template' ) );
		add_action( 'admin_bar_menu', array( __CLASS__, 'admin_bar' ), 100 );
	}

	/**
	 * Whether the current request should get the coming-soon experience.
	 */
	private static function applies() {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return true;
		}
		return isset( $_GET['epif_preview'] ) && 'coming-soon' === $_GET['epif_preview']; // phpcs:ignore WordPress.Security.NonceVerification
	}

	public static function gate() {
		if ( ! self::applies() || is_front_page() || is_feed() || is_robots() || is_favicon() ) {
			return;
		}

		/**
		 * Filters page IDs that stay reachable while coming-soon mode is on.
		 *
		 * @param int[] $ids Legal page IDs by default.
		 */
		$allowed = (array) apply_filters( 'epif_coming_soon_allowed_ids', EPIF_Legal::page_ids() );
		if ( $allowed && is_page( $allowed ) ) { // is_page( array() ) is true for every page.
			return;
		}

		wp_safe_redirect( home_url( '/' ), 302, 'EPIF coming soon' );
		exit;
	}

	/**
	 * Use templates/coming-soon.html (if the theme has one) for the homepage.
	 */
	public static function template( $templates ) {
		if ( self::applies() ) {
			array_unshift( $templates, 'coming-soon.php' );
		}
		return $templates;
	}

	public static function admin_bar( WP_Admin_Bar $bar ) {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return;
		}
		$bar->add_node(
			array(
				'id'    => 'epif-coming-soon',
				'title' => esc_html__( 'Coming soon: ON', 'epif' ),
				'href'  => add_query_arg( 'epif_preview', 'coming-soon', home_url( '/' ) ),
				'meta'  => array( 'title' => esc_attr__( 'Visitors see the coming-soon page. Click to preview it.', 'epif' ) ),
			)
		);
	}
}
