<?php
/**
 * Title: EPIF about us
 * Slug: epif/about
 * Categories: epif
 * Description: About Us hero and "how we work". Page content below it holds the founding story and team, editable in the page editor.
 *
 * @package EPIF
 */

?>
<!-- wp:html -->
<section class="epif-hero epif-wrap" aria-labelledby="epif-about-title">
	<div class="epif-hero__copy">
		<p class="epif-eyebrow"><?php esc_html_e( 'About us', 'epif' ); ?></p>
		<h1 id="epif-about-title"><?php esc_html_e( 'Enjoy the past. Improve the future.', 'epif' ); ?></h1>
		<p class="epif-lead"><?php esc_html_e( 'EPIF Services clears out homes, barns and businesses from our home base in Carroll County, Maryland, then puts what we haul back into circulation.', 'epif' ); ?></p>
	</div>
	<figure class="epif-hero__media"><div class="epif-photo-slot"><?php esc_html_e( 'Add a photo of the team', 'epif' ); ?></div></figure>
</section>
<section class="epif-band epif-band--light" aria-labelledby="epif-how-title">
	<div class="epif-wrap">
		<h2 id="epif-how-title"><?php esc_html_e( 'How we work', 'epif' ); ?></h2>
		<div class="epif-cards">
			<article class="epif-card"><h3><?php esc_html_e( 'Recirculate first', 'epif' ); ?></h3><p><?php esc_html_e( 'Every load is sorted: recirculated, donated, recycled, and the landfill last.', 'epif' ); ?></p></article>
			<article class="epif-card"><h3><?php esc_html_e( 'A receipt for every job', 'epif' ); ?></h3><p><?php esc_html_e( 'You get diversion data showing where your items went.', 'epif' ); ?></p></article>
			<article class="epif-card"><h3><?php esc_html_e( 'On your time', 'epif' ); ?></h3><p><?php esc_html_e( "You don't need to be home. Share the scope and access, and we stay in touch throughout.", 'epif' ); ?></p></article>
		</div>
	</div>
</section>
<!-- /wp:html -->
