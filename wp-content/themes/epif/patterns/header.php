<?php
/**
 * Title: EPIF header
 * Slug: epif/header
 * Categories: epif, header
 * Block Types: core/template-part/header
 * Description: Location strip, logo, main menu, call/text and the Shop button.
 *
 * @package EPIF
 */

?>
<!-- wp:html -->
<a class="epif-skip" href="#quote"><?php esc_html_e( 'Skip to the quote form', 'epif' ); ?></a>
<div class="epif-strip" data-epif-strip role="region" aria-label="<?php esc_attr_e( 'Your location', 'epif' ); ?>">
	<div class="epif-wrap epif-strip__inner" aria-live="polite">
		<p class="epif-strip__text" data-epif-strip-text><?php esc_html_e( 'Serving Carroll County (home base), Frederick, Baltimore & Howard Co., MD · Hanover & McSherrystown, PA · Washington, DC · Virginia coming soon', 'epif' ); ?></p>
		<button type="button" class="epif-strip__btn" data-epif-zip-toggle aria-expanded="false"><?php esc_html_e( 'Enter your ZIP for local prices', 'epif' ); ?></button>
		<button type="button" class="epif-strip__close" data-epif-banner-close hidden aria-label="<?php esc_attr_e( 'Dismiss this banner', 'epif' ); ?>">×</button>
	</div>
	<form class="epif-zip-pop" data-epif-zip-form hidden>
		<label><?php esc_html_e( 'Your ZIP code', 'epif' ); ?>
			<input type="text" name="zip" inputmode="numeric" maxlength="5" autocomplete="postal-code" placeholder="e.g. 21157">
		</label>
		<p class="epif-zip-pop__error" role="alert" data-epif-zip-error></p>
		<button type="submit" class="wp-element-button"><?php esc_html_e( 'Update prices', 'epif' ); ?></button>
		<p class="epif-small"><?php esc_html_e( 'Saved in this browser only. We never store your IP address.', 'epif' ); ?></p>
	</form>
</div>
<header class="epif-header">
	<div class="epif-wrap epif-header__inner">
		<a class="epif-header__logo" href="<?php echo esc_url( home_url( '/' ) ); ?>"><img src="<?php echo esc_url( epif_logo_url() ); ?>" alt="<?php esc_attr_e( 'EPIF Services home', 'epif' ); ?>" width="72" height="72"></a>
		<nav aria-label="<?php esc_attr_e( 'Main', 'epif' ); ?>" class="epif-nav">
			<ul>
				<li><a href="<?php echo esc_url( home_url( '/#services' ) ); ?>"><?php esc_html_e( 'Services', 'epif' ); ?></a></li>
				<li><a href="<?php echo esc_url( home_url( '/#pricing' ) ); ?>"><?php esc_html_e( 'Pricing', 'epif' ); ?></a></li>
				<li><a href="<?php echo esc_url( home_url( '/#partners' ) ); ?>"><?php esc_html_e( 'Partners', 'epif' ); ?></a></li>
				<li><a href="<?php echo esc_url( home_url( '/#listing-tool' ) ); ?>"><?php esc_html_e( 'Listing tool', 'epif' ); ?></a></li>
				<li><a href="<?php echo esc_url( home_url( '/blog/' ) ); ?>"><?php esc_html_e( 'Blog', 'epif' ); ?></a></li>
				<li><a href="<?php echo esc_url( home_url( '/about/' ) ); ?>"><?php esc_html_e( 'About us', 'epif' ); ?></a></li>
			</ul>
		</nav>
		<div class="epif-header__actions">
			<a class="epif-header__call" href="<?php echo esc_attr( epif_phone_href() ); ?>"><?php esc_html_e( 'Call or text', 'epif' ); ?></a>
			<a class="epif-shop" href="https://epifservices.shop" aria-label="<?php esc_attr_e( 'Shop at epifservices.shop', 'epif' ); ?>"><?php esc_html_e( 'Shop', 'epif' ); ?> ↗</a>
		</div>
	</div>
</header>
<div class="epif-sticky" data-epif-sticky>
	<a class="wp-element-button" href="<?php echo esc_url( home_url( '/#quote' ) ); ?>"><?php esc_html_e( 'Get a quote', 'epif' ); ?></a>
	<a class="epif-sticky__icon" href="<?php echo esc_attr( epif_phone_href() ); ?>" aria-label="<?php esc_attr_e( 'Call EPIF', 'epif' ); ?>">Call</a>
	<a class="epif-sticky__icon" href="<?php echo esc_attr( epif_phone_href( 'sms' ) ); ?>" aria-label="<?php esc_attr_e( 'Text EPIF', 'epif' ); ?>">Text</a>
</div>
<!-- /wp:html -->
