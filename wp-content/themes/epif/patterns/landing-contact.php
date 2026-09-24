<?php
/**
 * Title: EPIF contact / lead form
 * Slug: epif/landing-contact
 * Categories: epif, call-to-action
 * Description: Lead capture section. Submissions are stored under Leads in the dashboard and emailed to the site admin.
 *
 * @package EPIF
 */

?>
<!-- wp:group {"anchor":"contact","align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60","left":"var:preset|spacing|50","right":"var:preset|spacing|50"}}},"backgroundColor":"accent-1","layout":{"type":"constrained","contentSize":"760px"}} -->
<div id="contact" class="wp-block-group alignfull has-accent-1-background-color has-background" style="padding-top:var(--wp--preset--spacing--60);padding-right:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--60);padding-left:var(--wp--preset--spacing--50)">
	<!-- wp:heading {"textAlign":"center"} -->
	<h2 class="wp-block-heading has-text-align-center"><?php esc_html_e( 'Get a free quote', 'epif' ); ?></h2>
	<!-- /wp:heading -->

	<!-- wp:paragraph {"align":"center","textColor":"accent-4"} -->
	<p class="has-text-align-center has-accent-4-color has-text-color"><?php esc_html_e( 'Tell us about your project. We reply within one business day.', 'epif' ); ?></p>
	<!-- /wp:paragraph -->

	<!-- wp:shortcode -->
	[epif_lead_form services="Service one|Service two|Service three|Other" button="Request my quote"]
	<!-- /wp:shortcode -->
</div>
<!-- /wp:group -->
