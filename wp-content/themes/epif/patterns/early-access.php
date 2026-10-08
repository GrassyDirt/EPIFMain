<?php
/**
 * Title: EPIF early access page (template use)
 * Slug: epif/early-access
 * Categories: epif
 * Inserter: false
 * Description: Used by the coming-soon and Early Access templates. In the editor, insert "EPIF early access page" instead.
 *
 * Templates run shortcodes before patterns expand, so this renders the markup directly.
 *
 * @package EPIF
 */

?>
<!-- wp:html -->
<?php echo epif_early_access_html(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
<!-- /wp:html -->
