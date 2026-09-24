<?php
/**
 * Title: EPIF landing hero
 * Slug: epif/landing-hero
 * Categories: epif, banner
 * Description: Headline, supporting copy, and call-to-action buttons.
 *
 * @package EPIF
 */

?>
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60","left":"var:preset|spacing|50","right":"var:preset|spacing|50"}}},"backgroundColor":"accent-1","layout":{"type":"constrained","contentSize":"820px"}} -->
<div class="wp-block-group alignfull has-accent-1-background-color has-background" style="padding-top:var(--wp--preset--spacing--60);padding-right:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--60);padding-left:var(--wp--preset--spacing--50)">
	<!-- wp:heading {"textAlign":"center","level":1,"fontSize":"xx-large"} -->
	<h1 class="wp-block-heading has-text-align-center has-xx-large-font-size"><?php esc_html_e( 'Reliable services, done right the first time.', 'epif' ); ?></h1>
	<!-- /wp:heading -->

	<!-- wp:paragraph {"align":"center","textColor":"accent-4","fontSize":"large"} -->
	<p class="has-text-align-center has-accent-4-color has-text-color has-large-font-size"><?php esc_html_e( 'EPIF Services — replace this with a one-sentence promise that tells visitors exactly what you do and who it is for.', 'epif' ); ?></p>
	<!-- /wp:paragraph -->

	<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
	<div class="wp-block-buttons">
		<!-- wp:button -->
		<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="#contact"><?php esc_html_e( 'Get a free quote', 'epif' ); ?></a></div>
		<!-- /wp:button -->
		<!-- wp:button {"className":"is-style-outline"} -->
		<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="#services"><?php esc_html_e( 'See our services', 'epif' ); ?></a></div>
		<!-- /wp:button -->
	</div>
	<!-- /wp:buttons -->
</div>
<!-- /wp:group -->
