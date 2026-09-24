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

		EPIF_Forms::enqueue();

		$services = array_filter( array_map( 'trim', explode( '|', $atts['services'] ) ) );
		$uid      = wp_unique_id( 'epif-lead-' );

		ob_start();
		?>
		<form class="epif-form epif-lead-form" id="<?php echo esc_attr( $uid ); ?>" data-epif-endpoint="leads" data-epif-event="generate_lead" novalidate>
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
