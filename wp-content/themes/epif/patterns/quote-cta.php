<?php
/**
 * Title: EPIF quote call-to-action
 * Slug: epif/quote-cta
 * Categories: epif, call-to-action
 * Description: "Send photos, get a price" band. Used at the end of every blog post; insert it anywhere else too.
 *
 * @package EPIF
 */

?>
<!-- wp:group {"className":"epif-cta","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between"}} -->
<div class="wp-block-group epif-cta">
	<!-- wp:paragraph {"className":"epif-cta__text"} -->
	<p class="epif-cta__text"><?php esc_html_e( 'Have a clear-out like this? Send photos, get a price.', 'epif' ); ?></p>
	<!-- /wp:paragraph -->
	<!-- wp:buttons -->
	<div class="wp-block-buttons"><!-- wp:button -->
	<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="/#quote"><?php esc_html_e( 'Get a quote', 'epif' ); ?></a></div>
	<!-- /wp:button --></div>
	<!-- /wp:buttons -->
</div>
<!-- /wp:group -->
