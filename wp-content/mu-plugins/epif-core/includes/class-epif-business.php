<?php
/**
 * Business details (Settings → EPIF Business Info).
 *
 * One place for the company name, contact details, and legal facts. The footer,
 * legal pages, and emails read from here, so updating a value updates the whole site.
 *
 * In content: [epif_info field="legal_name"]
 *
 * @package EPIF_Core
 */

defined( 'ABSPATH' ) || exit;

class EPIF_Business {

	const OPTION = 'epif_business';

	public static function init() {
		add_action( 'admin_init', array( __CLASS__, 'register_setting' ) );
		add_action( 'admin_menu', array( __CLASS__, 'add_page' ) );
		add_shortcode( 'epif_info', array( __CLASS__, 'shortcode' ) );
	}

	/**
	 * Field key => array( label, placeholder shown until filled in, input type ).
	 */
	public static function fields() {
		return array(
			'brand'          => array( __( 'Brand name', 'epif' ), 'EPIF Services', 'text' ),
			'legal_name'     => array( __( 'Legal business name', 'epif' ), '[Legal business name, e.g. EPIF Services LLC]', 'text' ),
			'tagline'        => array( __( 'Tagline', 'epif' ), '', 'text' ),
			'email'          => array( __( 'Contact email', 'epif' ), '[contact email]', 'email' ),
			'privacy_email'  => array( __( 'Privacy requests email', 'epif' ), '', 'email' ),
			'phone'          => array( __( 'Phone', 'epif' ), '', 'text' ),
			'address'        => array( __( 'Mailing address', 'epif' ), '[Business mailing address]', 'textarea' ),
			'governing_law'  => array( __( 'Governing law (state/country)', 'epif' ), '[State], United States', 'text' ),
			'effective_date' => array( __( 'Legal pages effective date', 'epif' ), '[Effective date]', 'text' ),
		);
	}

	/**
	 * Saved value, falling back to the placeholder (or another field) when empty.
	 */
	public static function get( $field ) {
		$saved = get_option( self::OPTION, array() );
		$value = isset( $saved[ $field ] ) ? trim( (string) $saved[ $field ] ) : '';
		if ( '' !== $value ) {
			return $value;
		}
		if ( 'privacy_email' === $field ) {
			return self::get( 'email' );
		}
		if ( 'brand' === $field ) {
			return get_bloginfo( 'name' ) ? get_bloginfo( 'name' ) : 'EPIF Services';
		}
		$fields = self::fields();
		return isset( $fields[ $field ] ) ? $fields[ $field ][1] : '';
	}

	/**
	 * Whether a field still shows its [placeholder].
	 */
	public static function is_placeholder( $field ) {
		return 0 === strpos( self::get( $field ), '[' );
	}

	public static function shortcode( $atts ) {
		$atts  = shortcode_atts( array( 'field' => '' ), $atts, 'epif_info' );
		$field = sanitize_key( $atts['field'] );
		if ( ! array_key_exists( $field, self::fields() ) ) {
			return '';
		}
		$value = self::get( $field );

		// Unfilled values are highlighted so they are easy to spot when reviewing drafts.
		if ( self::is_placeholder( $field ) ) {
			return '<mark>' . esc_html( $value ) . '</mark>';
		}
		if ( in_array( $field, array( 'email', 'privacy_email' ), true ) ) {
			return '<a href="mailto:' . esc_attr( antispambot( $value ) ) . '">' . esc_html( antispambot( $value ) ) . '</a>';
		}
		return nl2br( esc_html( $value ) );
	}

	public static function register_setting() {
		register_setting(
			'epif_business',
			self::OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( __CLASS__, 'sanitize' ),
				'default'           => array(),
			)
		);
	}

	public static function sanitize( $input ) {
		$clean = array();
		foreach ( self::fields() as $key => $field ) {
			$value = isset( $input[ $key ] ) ? wp_unslash( $input[ $key ] ) : '';
			switch ( $field[2] ) {
				case 'email':
					$clean[ $key ] = sanitize_email( $value );
					break;
				case 'textarea':
					$clean[ $key ] = sanitize_textarea_field( $value );
					break;
				default:
					$clean[ $key ] = sanitize_text_field( $value );
			}
		}
		return $clean;
	}

	public static function add_page() {
		add_options_page( __( 'EPIF Business Info', 'epif' ), __( 'EPIF Business Info', 'epif' ), 'manage_options', 'epif-business', array( __CLASS__, 'render_page' ) );
	}

	public static function render_page() {
		$saved = get_option( self::OPTION, array() );
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'EPIF Business Info', 'epif' ); ?></h1>
			<p><?php esc_html_e( 'Used in the site footer, the Privacy Policy / Terms / Cookie Policy pages, and newsletter emails. Empty fields show a highlighted [placeholder] on the site.', 'epif' ); ?></p>
			<form method="post" action="options.php">
				<?php settings_fields( 'epif_business' ); ?>
				<table class="form-table" role="presentation"><tbody>
				<?php foreach ( self::fields() as $key => $field ) : ?>
					<?php
					$id    = 'epif-' . $key;
					$name  = self::OPTION . '[' . $key . ']';
					$value = isset( $saved[ $key ] ) ? $saved[ $key ] : '';
					?>
					<tr>
						<th scope="row"><label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $field[0] ); ?></label></th>
						<td>
							<?php if ( 'textarea' === $field[2] ) : ?>
								<textarea id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" rows="3" class="large-text" placeholder="<?php echo esc_attr( $field[1] ); ?>"><?php echo esc_textarea( $value ); ?></textarea>
							<?php else : ?>
								<input id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" type="<?php echo esc_attr( $field[2] ); ?>" value="<?php echo esc_attr( $value ); ?>" class="regular-text" placeholder="<?php echo esc_attr( $field[1] ); ?>">
							<?php endif; ?>
							<?php if ( 'privacy_email' === $key ) : ?>
								<p class="description"><?php esc_html_e( 'Optional. Defaults to the contact email.', 'epif' ); ?></p>
							<?php elseif ( 'address' === $key ) : ?>
								<p class="description"><?php esc_html_e( 'US law (CAN-SPAM) requires a valid postal address in marketing emails. A P.O. box or registered mailbox is fine.', 'epif' ); ?></p>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody></table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
}
