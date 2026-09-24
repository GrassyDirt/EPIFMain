<?php
/**
 * Lead storage: a private custom post type that shows up in the admin under "Leads".
 *
 * @package EPIF_Core
 */

defined( 'ABSPATH' ) || exit;

class EPIF_Leads {

	const POST_TYPE = 'epif_lead';

	/**
	 * Meta fields stored on each lead, keyed by meta key => admin label.
	 */
	const FIELDS = array(
		'_epif_name'       => 'Name',
		'_epif_email'      => 'Email',
		'_epif_phone'      => 'Phone',
		'_epif_company'    => 'Company',
		'_epif_service'    => 'Service',
		'_epif_message'    => 'Message',
		'_epif_source_url' => 'Source page',
		'_epif_utm'        => 'UTM / campaign',
		'_epif_ip_hash'    => 'IP hash',
	);

	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_post_type' ) );
		add_filter( 'manage_' . self::POST_TYPE . '_posts_columns', array( __CLASS__, 'columns' ) );
		add_action( 'manage_' . self::POST_TYPE . '_posts_custom_column', array( __CLASS__, 'column_content' ), 10, 2 );
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_box' ) );
	}

	public static function register_post_type() {
		register_post_type(
			self::POST_TYPE,
			array(
				'labels'              => array(
					'name'          => __( 'Leads', 'epif' ),
					'singular_name' => __( 'Lead', 'epif' ),
					'all_items'     => __( 'All Leads', 'epif' ),
					'edit_item'     => __( 'View Lead', 'epif' ),
					'search_items'  => __( 'Search Leads', 'epif' ),
					'not_found'     => __( 'No leads yet.', 'epif' ),
				),
				'public'              => false,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'show_in_rest'        => false,
				'exclude_from_search' => true,
				'publicly_queryable'  => false,
				'menu_position'       => 25,
				'menu_icon'           => 'dashicons-email-alt',
				'supports'            => array( 'title' ),
				'capability_type'     => 'post',
				// Leads are only created by the form, never by hand.
				'capabilities'        => array( 'create_posts' => 'do_not_allow' ),
				'map_meta_cap'        => true,
			)
		);
	}

	/**
	 * Store a sanitized lead. Returns the new post ID or WP_Error.
	 *
	 * @param array $data Sanitized lead data (keys match FIELDS without the "_epif_" prefix).
	 * @return int|WP_Error
	 */
	public static function create( array $data ) {
		$meta = array();
		foreach ( array_keys( self::FIELDS ) as $key ) {
			$short = substr( $key, 6 );
			if ( isset( $data[ $short ] ) && '' !== $data[ $short ] ) {
				$meta[ $key ] = $data[ $short ];
			}
		}

		return wp_insert_post(
			array(
				'post_type'   => self::POST_TYPE,
				'post_status' => 'private',
				'post_title'  => sprintf( '%s — %s', $data['name'], $data['email'] ),
				'meta_input'  => $meta,
			),
			true
		);
	}

	public static function columns( $columns ) {
		return array(
			'cb'            => $columns['cb'],
			'title'         => __( 'Lead', 'epif' ),
			'epif_phone'    => __( 'Phone', 'epif' ),
			'epif_service'  => __( 'Service', 'epif' ),
			'epif_source'   => __( 'Source', 'epif' ),
			'date'          => __( 'Received', 'epif' ),
		);
	}

	public static function column_content( $column, $post_id ) {
		$map = array(
			'epif_phone'   => '_epif_phone',
			'epif_service' => '_epif_service',
			'epif_source'  => '_epif_utm',
		);
		if ( isset( $map[ $column ] ) ) {
			echo esc_html( (string) get_post_meta( $post_id, $map[ $column ], true ) );
		}
	}

	public static function add_meta_box() {
		add_meta_box( 'epif_lead_details', __( 'Lead details', 'epif' ), array( __CLASS__, 'render_meta_box' ), self::POST_TYPE, 'normal', 'high' );
	}

	public static function render_meta_box( $post ) {
		echo '<table class="form-table" role="presentation"><tbody>';
		foreach ( self::FIELDS as $key => $label ) {
			$value = (string) get_post_meta( $post->ID, $key, true );
			if ( '_epif_email' === $key && $value ) {
				$value_html = '<a href="mailto:' . esc_attr( $value ) . '">' . esc_html( $value ) . '</a>';
			} elseif ( '_epif_phone' === $key && $value ) {
				$value_html = '<a href="tel:' . esc_attr( preg_replace( '/[^0-9+]/', '', $value ) ) . '">' . esc_html( $value ) . '</a>';
			} else {
				$value_html = nl2br( esc_html( $value ) );
			}
			printf( '<tr><th scope="row">%s</th><td>%s</td></tr>', esc_html( $label ), $value_html ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped above.
		}
		echo '</tbody></table>';
	}
}
