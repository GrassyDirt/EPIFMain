<?php
/**
 * Shared front-end assets for EPIF forms (lead form, newsletter).
 *
 * @package EPIF_Core
 */

defined( 'ABSPATH' ) || exit;

class EPIF_Forms {

	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'register_assets' ) );
		add_filter( 'render_block_core/shortcode', array( __CLASS__, 'expand_in_block' ) );
	}

	public static function register_assets() {
		$base = content_url( 'mu-plugins/epif-core/assets/' );
		wp_register_script( 'epif-forms', $base . 'forms.js', array(), EPIF_CORE_VERSION, array( 'strategy' => 'defer', 'in_footer' => true ) );
		wp_localize_script(
			'epif-forms',
			'EPIF_FORMS',
			array(
				'restBase' => esc_url_raw( rest_url( EPIF_Guard::REST_NAMESPACE . '/' ) ),
			)
		);
		wp_register_style( 'epif-forms', $base . 'forms.css', array(), EPIF_CORE_VERSION );
	}

	/**
	 * Call from a form's render method.
	 */
	public static function enqueue() {
		wp_enqueue_script( 'epif-forms' );
		wp_enqueue_style( 'epif-forms' );
	}

	/**
	 * Block templates expand patterns after do_shortcode() has run, so a Shortcode block
	 * inside a template pattern would otherwise print the raw [epif_…] text.
	 */
	public static function expand_in_block( $content ) {
		if ( false === strpos( $content, '[epif_' ) ) {
			return $content;
		}
		return do_shortcode( shortcode_unautop( trim( $content ) ) );
	}
}
