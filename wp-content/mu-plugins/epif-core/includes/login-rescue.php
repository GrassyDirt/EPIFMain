<?php
/**
 * Lockout protection for Loginizer Security's "rename wp-admin" and "rename login" features.
 *
 * Both depend on rules in the root .htaccess: Loginizer's own "# BEGIN Loginizer" block for
 * the custom admin URL, and WordPress's "# BEGIN WordPress" rewrite block for the custom login
 * URL. If a deploy replaces or deletes .htaccess, those URLs return 404 while Loginizer keeps
 * pointing every admin link at them, and nobody can log in.
 *
 * When the rules are missing, this pauses the custom URLs so the standard /wp-admin/ and
 * /wp-login.php work again. Brute-force protection and 2FA stay on. To restore the custom
 * URLs, fix .htaccess (Settings → Permalinks → Save, then re-save Loginizer's admin slug).
 *
 * Force the fallback manually with define( 'EPIF_LOGIN_RESCUE', true ); in wp-config-local.php.
 *
 * @package EPIF_Core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether the root .htaccess contains a "# BEGIN {$marker}" block.
 */
function epif_htaccess_has_block( $marker ) {
	static $contents = null;
	if ( null === $contents ) {
		$file     = ABSPATH . '.htaccess';
		$contents = is_readable( $file ) ? (string) file_get_contents( $file ) : ''; // phpcs:ignore WordPress.WP.AlternativeFunctions -- local file.
	}
	return false !== strpos( $contents, '# BEGIN ' . $marker );
}

/**
 * Records which custom URL was paused, for the admin notice.
 */
function epif_login_rescue_paused( $what = null ) {
	static $paused = array();
	if ( null !== $what ) {
		$paused[ $what ] = true;
	}
	return array_keys( $paused );
}

add_filter(
	'option_loginizer_wp_admin',
	static function ( $options ) {
		if ( is_array( $options ) && ! empty( $options['admin_slug'] )
			&& ( EPIF_LOGIN_RESCUE || ! epif_htaccess_has_block( 'Loginizer' ) ) ) {
			$options['admin_slug']        = '';
			$options['restrict_wp_admin'] = '';
			epif_login_rescue_paused( 'admin' );
		}
		return $options;
	}
);

add_filter(
	'option_loginizer_security',
	static function ( $options ) {
		if ( is_array( $options ) && ! empty( $options['login_slug'] )
			&& ( EPIF_LOGIN_RESCUE || ! epif_htaccess_has_block( 'WordPress' ) ) ) {
			$options['login_slug']    = '';
			$options['hide_wp_admin'] = '';
			epif_login_rescue_paused( 'login' );
		}
		return $options;
	}
);

add_action(
	'admin_notices',
	static function () {
		$paused = epif_login_rescue_paused();
		if ( ! $paused || ! current_user_can( 'manage_options' ) ) {
			return;
		}
		echo '<div class="notice notice-warning"><p><strong>' . esc_html__( 'Loginizer custom admin/login URL is paused.', 'epif' ) . '</strong> '
			. esc_html__( 'The rules it needs are missing from .htaccess, so the standard /wp-admin/ and /wp-login.php addresses are active to prevent a lockout. To restore it: Settings → Permalinks → Save Changes, then re-save the custom URL in Loginizer.', 'epif' )
			. '</p></div>';
	}
);
