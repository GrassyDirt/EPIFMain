<?php
/**
 * Plugin Name: EPIF Marketplace
 * Description: Landing page design system for EPIF — Enjoy the Past, Improve the Future. Registers the EPIF stylesheet, button block styles, auction countdown timers and the landing page block pattern.
 * Version:     1.1.0
 * Author:      EPIF
 * License:     GPL-2.0-or-later
 * Text Domain: epif
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'EPIF_VERSION', '1.1.0' );
define( 'EPIF_DIR', plugin_dir_path( __FILE__ ) );
define( 'EPIF_URL', plugin_dir_url( __FILE__ ) );

/**
 * Front-end assets. Fraunces is self-hosted (assets/fonts, SIL OFL) and declared
 * in epif.css, so pages make no requests to Google Fonts.
 */
function epif_enqueue_assets() {
	wp_enqueue_style(
		'epif',
		EPIF_URL . 'assets/epif.css',
		array(),
		EPIF_VERSION
	);

	wp_enqueue_script(
		'epif-timers',
		EPIF_URL . 'assets/epif-timers.js',
		array(),
		EPIF_VERSION,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'epif_enqueue_assets' );

/**
 * Editor assets, so the canvas matches the front end.
 */
function epif_enqueue_editor_assets() {
	wp_enqueue_style( 'epif-editor', EPIF_URL . 'assets/epif.css', array(), EPIF_VERSION );
}
add_action( 'enqueue_block_editor_assets', 'epif_enqueue_editor_assets' );

/**
 * Classic themes don't handle .alignfull, so the CSS fallback is opt-in.
 */
function epif_body_class( $classes ) {
	if ( ! wp_is_block_theme() ) {
		$classes[] = 'epif-classic-align';
	}

	return $classes;
}
add_filter( 'body_class', 'epif_body_class' );

/**
 * Button variations — colour carries meaning here: orange is an auction,
 * blue is a purchase, green is tokens. Registering them as block styles keeps
 * that vocabulary available in the editor instead of living in pasted markup.
 */
function epif_register_block_styles() {
	$styles = array(
		'epif-bid'     => __( 'EPIF Bid (orange)', 'epif' ),
		'epif-primary' => __( 'EPIF Buy (blue)', 'epif' ),
		'epif-token'   => __( 'EPIF Token (green)', 'epif' ),
		'epif-ghost'   => __( 'EPIF Outline', 'epif' ),
	);

	foreach ( $styles as $name => $label ) {
		register_block_style( 'core/button', array(
			'name'  => $name,
			'label' => $label,
		) );
	}
}
add_action( 'init', 'epif_register_block_styles' );

/**
 * Pattern category + the landing page pattern itself.
 */
function epif_register_patterns() {
	if ( ! function_exists( 'register_block_pattern_category' ) ) {
		return;
	}

	register_block_pattern_category( 'epif', array(
		'label' => __( 'EPIF', 'epif' ),
	) );

	register_block_pattern(
		'epif/landing-page',
		array(
			'title'         => __( 'EPIF marketplace landing page', 'epif' ),
			'description'   => __( 'The full landing page: hero with search, ending-soon auction band with live countdowns, buy-it-now grid, how it works, tokens and growth tiers, the four ways to work with EPIF, FAQ, and a closing pickup CTA.', 'epif' ),
			'categories'    => array( 'epif' ),
			'keywords'      => array( 'landing', 'home', 'auction', 'marketplace' ),
			'viewportWidth' => 1440,
			'content'       => epif_get_pattern_content( 'landing-page' ),
		)
	);

	/*
	 * Header and footer are registered against the template-part block types so
	 * they appear in the header/footer inserters rather than only in the
	 * general pattern list.
	 */
	register_block_pattern(
		'epif/site-header',
		array(
			'title'         => __( 'EPIF site header', 'epif' ),
			'description'   => __( 'Sticky header: EPIF mark and wordmark, primary nav, marketplace search, token balance chip and the clear-out CTA.', 'epif' ),
			'categories'    => array( 'epif' ),
			'keywords'      => array( 'header', 'nav', 'menu' ),
			'blockTypes'    => array( 'core/template-part/header' ),
			'viewportWidth' => 1440,
			'content'       => epif_get_pattern_content( 'site-header' ),
		)
	);

	register_block_pattern(
		'epif/site-footer',
		array(
			'title'         => __( 'EPIF site footer', 'epif' ),
			'description'   => __( 'Footer: brand blurb with the diversion figure, marketplace / work-with-us / company link columns, contact line and legal links.', 'epif' ),
			'categories'    => array( 'epif' ),
			'keywords'      => array( 'footer', 'links', 'contact' ),
			'blockTypes'    => array( 'core/template-part/footer' ),
			'viewportWidth' => 1440,
			'content'       => epif_get_pattern_content( 'site-footer' ),
		)
	);
}
add_action( 'init', 'epif_register_patterns' );

/**
 * Pattern files are PHP so they can build URLs against the plugin directory.
 */
function epif_get_pattern_content( $slug ) {
	$file = EPIF_DIR . 'patterns/' . $slug . '.php';

	if ( ! file_exists( $file ) ) {
		return '';
	}

	ob_start();
	include $file;

	return ob_get_clean();
}
