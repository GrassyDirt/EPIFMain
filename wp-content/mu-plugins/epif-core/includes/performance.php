<?php
/**
 * Front-end performance defaults: trim head output a landing page doesn't need.
 *
 * Page caching itself is handled by LiteSpeed Cache / SpeedyCache.
 *
 * @package EPIF_Core
 */

defined( 'ABSPATH' ) || exit;

// Emoji detection script + styles (modern browsers render emoji natively).
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'admin_print_styles', 'print_emoji_styles' );
remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
add_filter( 'emoji_svg_url', '__return_false' );

// Legacy / unused discovery links.
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'wp_shortlink_wp_head' );
remove_action( 'wp_head', 'wp_oembed_add_host_js' );

// Heartbeat: none on the front end, slower in the admin.
add_action(
	'init',
	static function () {
		if ( ! is_admin() ) {
			wp_deregister_script( 'heartbeat' );
		}
	},
	1
);
add_filter(
	'heartbeat_settings',
	static function ( $settings ) {
		$settings['interval'] = 60;
		return $settings;
	}
);
