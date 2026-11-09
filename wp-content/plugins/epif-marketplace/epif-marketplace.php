<?php
/**
 * Plugin Name: EPIF Marketplace
 * Description: Landing page design system for EPIF — Enjoy the Past, Improve the Future. The EPIF storefront: design system, landing page, item browsing with filters, product page, header and footer, listing details on products, and an EPIF skin for WooCommerce.
 * Version:     1.3.0
 * Author:      EPIF
 * License:     GPL-2.0-or-later
 * Text Domain: epif
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'EPIF_VERSION', '1.3.0' );
define( 'EPIF_DIR', plugin_dir_path( __FILE__ ) );
define( 'EPIF_URL', plugin_dir_url( __FILE__ ) );

require_once EPIF_DIR . 'includes/class-epif-listing.php';
require_once EPIF_DIR . 'includes/class-epif-store.php';

EPIF_Listing::init();
EPIF_Store::init();

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
 * WooCommerce skin. Loaded only while WooCommerce is active and after
 * WooCommerce's own stylesheets, so the brand wins on equal specificity
 * whichever theme is active. Priority 20 runs after WooCommerce registers its
 * styles at the default priority.
 */
function epif_enqueue_woocommerce_assets() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}

	$deps = array( 'epif' );
	foreach ( array( 'woocommerce-general', 'woocommerce-layout', 'wc-blocks-style' ) as $handle ) {
		if ( wp_style_is( $handle, 'registered' ) ) {
			$deps[] = $handle;
		}
	}

	wp_enqueue_style( 'epif-woocommerce', EPIF_URL . 'assets/epif-woocommerce.css', $deps, EPIF_VERSION );
}
add_action( 'wp_enqueue_scripts', 'epif_enqueue_woocommerce_assets', 20 );

function epif_enqueue_woocommerce_editor_assets() {
	if ( class_exists( 'WooCommerce' ) ) {
		wp_enqueue_style( 'epif-woocommerce-editor', EPIF_URL . 'assets/epif-woocommerce.css', array( 'epif-editor' ), EPIF_VERSION );
	}
}
add_action( 'enqueue_block_editor_assets', 'epif_enqueue_woocommerce_editor_assets', 20 );

/**
 * Products without a photo get the EPIF striped placeholder instead of
 * WooCommerce's grey default. A placeholder chosen under WooCommerce →
 * Settings → Products is left alone; WooCommerce's own default image (which it
 * saves as an attachment on install) is replaced.
 */
function epif_woocommerce_uses_default_placeholder() {
	$id = (int) get_option( 'woocommerce_placeholder_image', 0 );
	if ( $id <= 0 ) {
		return true;
	}

	return false !== strpos( (string) get_attached_file( $id ), 'woocommerce-placeholder' );
}

function epif_woocommerce_placeholder_src( $src ) {
	return epif_woocommerce_uses_default_placeholder() ? EPIF_URL . 'assets/item-placeholder.svg' : $src;
}
add_filter( 'woocommerce_placeholder_img_src', 'epif_woocommerce_placeholder_src' );

function epif_woocommerce_placeholder_img( $html, $size, $dimensions ) {
	if ( ! epif_woocommerce_uses_default_placeholder() ) {
		return $html;
	}

	$width  = isset( $dimensions['width'] ) ? (int) $dimensions['width'] : 600;
	$height = isset( $dimensions['height'] ) ? (int) $dimensions['height'] : 450;

	return sprintf(
		'<img src="%s" alt="%s" width="%d" height="%d" class="woocommerce-placeholder wp-post-image" decoding="async" />',
		esc_url( EPIF_URL . 'assets/item-placeholder.svg' ),
		esc_attr__( 'Photo coming soon', 'epif' ),
		$width,
		$height
	);
}
add_filter( 'woocommerce_placeholder_img', 'epif_woocommerce_placeholder_img', 10, 3 );

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
