<?php
/**
 * EPIF child theme.
 *
 * Presentation only. Site logic (lead capture, hardening) lives in
 * wp-content/mu-plugins/epif-core so it survives theme changes.
 *
 * @package EPIF
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'wp_enqueue_scripts',
	static function () {
		wp_enqueue_style( 'epif-style', get_stylesheet_uri(), array(), wp_get_theme()->get( 'Version' ) );
	}
);

// Pattern category for the landing page sections.
add_action(
	'init',
	static function () {
		register_block_pattern_category( 'epif', array( 'label' => __( 'EPIF', 'epif' ) ) );
	}
);

/**
 * Early-access page markup (inc/early-access.php), rendered fresh each request.
 */
function epif_early_access_html() {
	ob_start();
	include __DIR__ . '/inc/early-access.php';
	return ob_get_clean();
}

// [epif_early_access] puts the whole early-access page into any page's content.
add_action(
	'init',
	static function () {
		add_shortcode( 'epif_early_access', 'epif_early_access_html' );
	}
);

// Show the theme's styles inside the block editor too.
add_action(
	'after_setup_theme',
	static function () {
		add_editor_style( 'style.css' );
	}
);
