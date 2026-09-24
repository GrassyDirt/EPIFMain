<?php
/**
 * Title: EPIF coming soon
 * Slug: epif/coming-soon
 * Categories: epif, banner
 * Description: Under-construction hero with newsletter signup. Shown to visitors while EPIF_COMING_SOON is on.
 *
 * @package EPIF
 */

$epif_brand = class_exists( 'EPIF_Business' ) ? EPIF_Business::get( 'brand' ) : get_bloginfo( 'name' );
?>
<!-- wp:group {"tagName":"main","align":"full","className":"epif-coming-soon","style":{"dimensions":{"minHeight":"85vh"},"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60","left":"var:preset|spacing|50","right":"var:preset|spacing|50"},"blockGap":"var:preset|spacing|40"}},"backgroundColor":"accent-1","layout":{"type":"constrained","contentSize":"40rem"}} -->
<main class="wp-block-group alignfull epif-coming-soon has-accent-1-background-color has-background" style="min-height:85vh;padding-top:var(--wp--preset--spacing--60);padding-right:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--60);padding-left:var(--wp--preset--spacing--50)">
	<!-- wp:site-logo {"width":96,"align":"center"} /-->

	<!-- wp:paragraph {"align":"center","className":"epif-eyebrow","fontSize":"small"} -->
	<p class="has-text-align-center epif-eyebrow has-small-font-size"><?php esc_html_e( 'Under construction', 'epif' ); ?></p>
	<!-- /wp:paragraph -->

	<!-- wp:heading {"textAlign":"center","level":1,"style":{"typography":{"textWrap":"balance"}},"fontSize":"xx-large"} -->
	<h1 class="wp-block-heading has-text-align-center has-xx-large-font-size" style="text-wrap:balance"><?php echo esc_html( sprintf( /* translators: %s: brand name. */ __( '%s is coming soon.', 'epif' ), $epif_brand ) ); ?></h1>
	<!-- /wp:heading -->

	<!-- wp:paragraph {"align":"center","textColor":"accent-4","fontSize":"large"} -->
	<p class="has-text-align-center has-accent-4-color has-text-color has-large-font-size"><?php esc_html_e( "We're building something new. Join the list and we'll email you the moment we launch.", 'epif' ); ?></p>
	<!-- /wp:paragraph -->

	<!-- wp:group {"anchor":"newsletter","layout":{"type":"default"}} -->
	<div id="newsletter" class="wp-block-group">
		<!-- wp:shortcode -->
		[epif_newsletter_form button="Notify me"]
		<!-- /wp:shortcode -->
	</div>
	<!-- /wp:group -->

	<!-- wp:paragraph {"align":"center","textColor":"accent-4","fontSize":"small"} -->
	<p class="has-text-align-center has-accent-4-color has-text-color has-small-font-size"><?php esc_html_e( 'No spam. Unsubscribe anytime.', 'epif' ); ?></p>
	<!-- /wp:paragraph -->
</main>
<!-- /wp:group -->
