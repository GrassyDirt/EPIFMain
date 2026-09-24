<?php
/**
 * Title: EPIF footer
 * Slug: epif/footer
 * Categories: epif, footer
 * Block Types: core/template-part/footer
 * Description: Brand, contact details, legal links, and copyright. Details come from Settings → EPIF Business Info; legal links appear once each page is published.
 *
 * @package EPIF
 */

$epif_has_core = class_exists( 'EPIF_Business' );
$epif_brand    = $epif_has_core ? EPIF_Business::get( 'brand' ) : get_bloginfo( 'name' );
$epif_tagline  = $epif_has_core ? EPIF_Business::get( 'tagline' ) : get_bloginfo( 'description' );
$epif_owner    = $epif_has_core && ! EPIF_Business::is_placeholder( 'legal_name' ) ? EPIF_Business::get( 'legal_name' ) : $epif_brand;
$epif_email    = $epif_has_core && ! EPIF_Business::is_placeholder( 'email' ) ? EPIF_Business::get( 'email' ) : '';
$epif_phone    = $epif_has_core ? EPIF_Business::get( 'phone' ) : '';
$epif_links    = class_exists( 'EPIF_Legal' ) ? EPIF_Legal::links() : array();
?>
<!-- wp:group {"tagName":"footer","align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|40","left":"var:preset|spacing|50","right":"var:preset|spacing|50"},"blockGap":"var:preset|spacing|30"},"border":{"top":{"color":"var:preset|color|accent-2","width":"1px"}}},"fontSize":"small","layout":{"type":"constrained","contentSize":"1200px"}} -->
<footer class="wp-block-group alignfull has-small-font-size" style="border-top-color:var(--wp--preset--color--accent-2);border-top-width:1px;padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--40);padding-left:var(--wp--preset--spacing--50)">
	<!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|30"}},"layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between","verticalAlignment":"top"}} -->
	<div class="wp-block-group">
		<!-- wp:group {"style":{"spacing":{"blockGap":"0.25rem"}},"layout":{"type":"flex","orientation":"vertical"}} -->
		<div class="wp-block-group">
			<!-- wp:paragraph {"style":{"typography":{"fontWeight":"600"}},"fontSize":"medium"} -->
			<p class="has-medium-font-size" style="font-weight:600"><?php echo esc_html( $epif_brand ); ?></p>
			<!-- /wp:paragraph -->
			<?php if ( $epif_tagline ) : ?>
			<!-- wp:paragraph {"textColor":"accent-4"} -->
			<p class="has-accent-4-color has-text-color"><?php echo esc_html( $epif_tagline ); ?></p>
			<!-- /wp:paragraph -->
			<?php endif; ?>
		</div>
		<!-- /wp:group -->

		<?php if ( $epif_email || $epif_phone ) : ?>
		<!-- wp:group {"style":{"spacing":{"blockGap":"0.25rem"}},"layout":{"type":"flex","orientation":"vertical"}} -->
		<div class="wp-block-group">
			<?php if ( $epif_email ) : ?>
			<!-- wp:paragraph -->
			<p><a href="mailto:<?php echo esc_attr( antispambot( $epif_email ) ); ?>"><?php echo esc_html( antispambot( $epif_email ) ); ?></a></p>
			<!-- /wp:paragraph -->
			<?php endif; ?>
			<?php if ( $epif_phone ) : ?>
			<!-- wp:paragraph -->
			<p><a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $epif_phone ) ); ?>"><?php echo esc_html( $epif_phone ); ?></a></p>
			<!-- /wp:paragraph -->
			<?php endif; ?>
		</div>
		<!-- /wp:group -->
		<?php endif; ?>
	</div>
	<!-- /wp:group -->

	<!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|20"}},"layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between"}} -->
	<div class="wp-block-group">
		<!-- wp:paragraph {"textColor":"accent-4"} -->
		<p class="has-accent-4-color has-text-color">&copy; <?php echo esc_html( gmdate( 'Y' ) . ' ' . $epif_owner ); ?>. <?php esc_html_e( 'All rights reserved.', 'epif' ); ?></p>
		<!-- /wp:paragraph -->
		<?php if ( $epif_links ) : ?>
		<!-- wp:paragraph {"className":"epif-legal-links"} -->
		<p class="epif-legal-links">
			<?php
			$epif_items = array();
			foreach ( $epif_links as $epif_title => $epif_url ) {
				$epif_items[] = '<a href="' . esc_url( $epif_url ) . '">' . esc_html( $epif_title ) . '</a>';
			}
			echo implode( '<span aria-hidden="true"> · </span>', $epif_items ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped above.
			?>
		</p>
		<!-- /wp:paragraph -->
		<?php endif; ?>
	</div>
	<!-- /wp:group -->
</footer>
<!-- /wp:group -->
