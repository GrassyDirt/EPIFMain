<?php
/**
 * Legal pages: Privacy Policy, Terms of Use, Cookie Policy, Accessibility Statement.
 *
 * The first time an administrator opens the dashboard, any of these pages that don't
 * exist yet are created as DRAFTS from epif-core/legal/*.html. They use [epif_info]
 * shortcodes, so company details come from Settings → EPIF Business Info. A dashboard
 * notice lists the drafts until they are reviewed and published. Existing pages are
 * never overwritten (except WordPress's own unpublished sample privacy policy).
 *
 * @package EPIF_Core
 */

defined( 'ABSPATH' ) || exit;

class EPIF_Legal {

	const VERSION = '1';
	const OPTION  = 'epif_legal_seeded';

	public static function pages() {
		return array(
			'privacy-policy'          => __( 'Privacy Policy', 'epif' ),
			'terms-of-use'            => __( 'Terms of Use', 'epif' ),
			'cookie-policy'           => __( 'Cookie Policy', 'epif' ),
			'accessibility-statement' => __( 'Accessibility Statement', 'epif' ),
		);
	}

	public static function init() {
		add_action( 'admin_init', array( __CLASS__, 'seed' ) );
		add_action( 'admin_notices', array( __CLASS__, 'review_notice' ) );
	}

	/**
	 * The page for a slug in any status, or null.
	 */
	public static function page( $slug ) {
		if ( 'privacy-policy' === $slug ) {
			$id = (int) get_option( 'wp_page_for_privacy_policy' );
			if ( $id && 'page' === get_post_type( $id ) && 'trash' !== get_post_status( $id ) ) {
				return get_post( $id );
			}
		}
		return get_page_by_path( $slug, OBJECT, 'page' );
	}

	/**
	 * Permalink of a published legal page, or '' while it is still a draft.
	 */
	public static function url( $slug ) {
		$page = self::page( $slug );
		return ( $page && 'publish' === $page->post_status ) ? get_permalink( $page ) : '';
	}

	/**
	 * Published legal pages as title => URL, for the footer.
	 */
	public static function links() {
		$links = array();
		foreach ( self::pages() as $slug => $title ) {
			$url = self::url( $slug );
			if ( $url ) {
				$links[ $title ] = $url;
			}
		}
		return $links;
	}

	/**
	 * IDs of all legal pages (any status), e.g. to keep them reachable in coming-soon mode.
	 */
	public static function page_ids() {
		$ids = array();
		foreach ( array_keys( self::pages() ) as $slug ) {
			$page = self::page( $slug );
			if ( $page ) {
				$ids[] = (int) $page->ID;
			}
		}
		return $ids;
	}

	public static function seed() {
		if ( self::VERSION === get_option( self::OPTION ) || ! current_user_can( 'publish_pages' ) || wp_doing_ajax() ) {
			return;
		}

		foreach ( self::pages() as $slug => $title ) {
			$file = EPIF_CORE_DIR . 'legal/' . $slug . '.html';
			if ( ! is_readable( $file ) ) {
				continue;
			}
			$content = file_get_contents( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions -- local file.
			$page    = self::page( $slug );

			if ( ! $page ) {
				$id = wp_insert_post(
					array(
						'post_type'    => 'page',
						'post_status'  => 'draft',
						'post_title'   => $title,
						'post_name'    => $slug,
						'post_content' => $content,
					),
					true
				);
			} elseif ( 'privacy-policy' === $slug && 'publish' !== $page->post_status && false !== strpos( $page->post_content, 'privacy-policy-tutorial' ) ) {
				// WordPress's untouched sample privacy page: replace its generic text with ours.
				$id = wp_update_post(
					array(
						'ID'           => $page->ID,
						'post_title'   => $title,
						'post_name'    => $slug,
						'post_content' => $content,
					),
					true
				);
			} else {
				$id = $page->ID;
			}

			if ( 'privacy-policy' === $slug && $id && ! is_wp_error( $id ) ) {
				update_option( 'wp_page_for_privacy_policy', (int) $id );
			}
		}

		update_option( self::OPTION, self::VERSION );
	}

	public static function review_notice() {
		$screen = get_current_screen();
		if ( ! $screen || ! current_user_can( 'publish_pages' ) || ! in_array( $screen->id, array( 'dashboard', 'edit-page', 'settings_page_epif-business' ), true ) ) {
			return;
		}

		$drafts = array();
		foreach ( self::pages() as $slug => $title ) {
			$page = self::page( $slug );
			if ( $page && 'publish' !== $page->post_status ) {
				$drafts[] = '<a href="' . esc_url( get_edit_post_link( $page->ID ) ) . '">' . esc_html( $title ) . '</a>';
			}
		}
		if ( ! $drafts ) {
			return;
		}

		echo '<div class="notice notice-warning"><p><strong>' . esc_html__( 'Legal pages are waiting for review:', 'epif' ) . '</strong> ' . implode( ', ', $drafts ) . '.</p><p>' // phpcs:ignore WordPress.Security.EscapeOutput -- built from escaped parts.
			. sprintf(
				/* translators: %s: settings page link. */
				esc_html__( 'First fill in %s (the highlighted [placeholders]), then have the drafts reviewed by a lawyer and publish them. They show up in the site footer once published. These drafts are a starting point, not legal advice.', 'epif' ),
				'<a href="' . esc_url( admin_url( 'options-general.php?page=epif-business' ) ) . '">' . esc_html__( 'EPIF Business Info', 'epif' ) . '</a>'
			)
			. '</p></div>';
	}
}
