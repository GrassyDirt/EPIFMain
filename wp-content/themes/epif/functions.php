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
