<?php
/**
 * Title: EPIF diversion receipt
 * Slug: epif/diversion-receipt
 * Categories: epif
 * Description: For blog job stories: what share of the job was recirculated, donated, recycled and landfilled. Edit the numbers.
 *
 * @package EPIF
 */

?>
<!-- wp:group {"className":"epif-receipt","layout":{"type":"constrained"}} -->
<div class="wp-block-group epif-receipt">
	<!-- wp:paragraph {"className":"epif-eyebrow"} -->
	<p class="epif-eyebrow"><?php esc_html_e( 'Diversion receipt for this job', 'epif' ); ?></p>
	<!-- /wp:paragraph -->
	<!-- wp:columns -->
	<div class="wp-block-columns">
		<?php foreach ( array( __( 'recirculated', 'epif' ), __( 'donated', 'epif' ), __( 'recycled', 'epif' ), __( 'landfilled', 'epif' ) ) as $epif_label ) : ?>
		<!-- wp:column -->
		<div class="wp-block-column"><!-- wp:paragraph {"className":"epif-receipt__num"} -->
		<p class="epif-receipt__num">0%</p>
		<!-- /wp:paragraph --><!-- wp:paragraph -->
		<p><?php echo esc_html( $epif_label ); ?></p>
		<!-- /wp:paragraph --></div>
		<!-- /wp:column -->
		<?php endforeach; ?>
	</div>
	<!-- /wp:columns -->
</div>
<!-- /wp:group -->
