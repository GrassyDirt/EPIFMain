<?php
/**
 * Lead form markup via the [epif_lead_form] shortcode.
 *
 * Works in any theme or block (use a Shortcode block in the editor). Optional attributes:
 *   [epif_lead_form services="Service A|Service B" button="Get a free quote"]
 *
 * @package EPIF_Core
 */

defined( 'ABSPATH' ) || exit;

class EPIF_Lead_Form {

	public static function init() {
		add_shortcode( 'epif_lead_form', array( __CLASS__, 'render' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'register_assets' ) );
		add_filter( 'render_block_core/shortcode', array( __CLASS__, 'expand_in_block' ) );
	}

	/**
	 * Block templates expand patterns after do_shortcode() has run, so a Shortcode block
	 * inside a template pattern would otherwise print the raw [epif_lead_form] text.
	 */
	public static function expand_in_block( $content ) {
		if ( false === strpos( $content, '[epif_lead_form' ) ) {
			return $content;
		}
		return do_shortcode( shortcode_unautop( trim( $content ) ) );
	}

	public static function register_assets() {
		$base = content_url( 'mu-plugins/epif-core/assets/' );
		wp_register_script( 'epif-lead-form', $base . 'lead-form.js', array(), EPIF_CORE_VERSION, array( 'strategy' => 'defer', 'in_footer' => true ) );
		wp_localize_script(
			'epif-lead-form',
			'EPIF_LEAD',
			array(
				'endpoint' => esc_url_raw( rest_url( EPIF_Lead_API::NAMESPACE_V1 . '/leads' ) ),
				'tokenUrl' => esc_url_raw( rest_url( EPIF_Lead_API::NAMESPACE_V1 . '/token' ) ),
			)
		);
		wp_register_style( 'epif-lead-form', $base . 'lead-form.css', array(), EPIF_CORE_VERSION );
	}

	public static function render( $atts ) {
		$atts = shortcode_atts(
			array(
				'services' => '',
				'button'   => __( 'Request a quote', 'epif' ),
			),
			$atts,
			'epif_lead_form'
		);

		wp_enqueue_script( 'epif-lead-form' );
		wp_enqueue_style( 'epif-lead-form' );

		$services = array_filter( array_map( 'trim', explode( '|', $atts['services'] ) ) );
		$uid      = wp_unique_id( 'epif-lead-' );

		ob_start();
		?>
		<form class="epif-lead-form" id="<?php echo esc_attr( $uid ); ?>" novalidate>
			<div class="epif-field">
				<label for="<?php echo esc_attr( $uid ); ?>-name"><?php esc_html_e( 'Name', 'epif' ); ?> <span aria-hidden="true">*</span></label>
				<input id="<?php echo esc_attr( $uid ); ?>-name" name="name" type="text" autocomplete="name" required maxlength="100">
			</div>
			<div class="epif-field">
				<label for="<?php echo esc_attr( $uid ); ?>-email"><?php esc_html_e( 'Email', 'epif' ); ?> <span aria-hidden="true">*</span></label>
				<input id="<?php echo esc_attr( $uid ); ?>-email" name="email" type="email" autocomplete="email" required maxlength="254">
			</div>
			<div class="epif-field">
				<label for="<?php echo esc_attr( $uid ); ?>-phone"><?php esc_html_e( 'Phone', 'epif' ); ?></label>
				<input id="<?php echo esc_attr( $uid ); ?>-phone" name="phone" type="tel" autocomplete="tel" maxlength="25">
			</div>
			<div class="epif-field">
				<label for="<?php echo esc_attr( $uid ); ?>-company"><?php esc_html_e( 'Company', 'epif' ); ?></label>
				<input id="<?php echo esc_attr( $uid ); ?>-company" name="company" type="text" autocomplete="organization" maxlength="200">
			</div>
			<?php if ( $services ) : ?>
				<div class="epif-field">
					<label for="<?php echo esc_attr( $uid ); ?>-service"><?php esc_html_e( 'Service needed', 'epif' ); ?></label>
					<select id="<?php echo esc_attr( $uid ); ?>-service" name="service">
						<option value=""><?php esc_html_e( 'Select one…', 'epif' ); ?></option>
						<?php foreach ( $services as $service ) : ?>
							<option value="<?php echo esc_attr( $service ); ?>"><?php echo esc_html( $service ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			<?php endif; ?>
			<div class="epif-field epif-field--full">
				<label for="<?php echo esc_attr( $uid ); ?>-message"><?php esc_html_e( 'How can we help?', 'epif' ); ?></label>
				<textarea id="<?php echo esc_attr( $uid ); ?>-message" name="message" rows="4" maxlength="5000"></textarea>
			</div>
			<div class="epif-hp" aria-hidden="true">
				<label for="<?php echo esc_attr( $uid ); ?>-website">Website</label>
				<input id="<?php echo esc_attr( $uid ); ?>-website" name="website" type="text" tabindex="-1" autocomplete="off">
			</div>
			<div class="epif-field epif-field--full">
				<button type="submit" class="wp-element-button"><?php echo esc_html( $atts['button'] ); ?></button>
				<p class="epif-status" role="status" aria-live="polite"></p>
			</div>
		</form>
		<?php
		return ob_get_clean();
	}
}
