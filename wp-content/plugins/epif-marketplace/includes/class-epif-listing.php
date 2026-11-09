<?php
/**
 * EPIF listing details stored on each WooCommerce product.
 *
 * Adds an "EPIF listing" group to Product data → General: condition, how the
 * item leaves the warehouse, tokens the buyer earns, and an optional auction
 * end time. Cards, filters and the product page read them through the
 * getters here.
 *
 * Bidding itself is not implemented by this plugin. An auction end time makes
 * the item show as a live auction (orange "Live" tag, countdown, "Ending
 * soonest" sort); taking bids needs an auction plugin. If that plugin stores
 * its own end time, return it from the `epif_auction_end` filter.
 *
 * @package epif
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EPIF_Listing {

	const CONDITION   = '_epif_condition';
	const FULFILLMENT = '_epif_fulfillment';
	const TOKENS      = '_epif_tokens';
	const AUCTION_END = '_epif_auction_end'; // Unix timestamp (UTC).

	public static function init() {
		add_action( 'woocommerce_product_options_general_product_data', array( __CLASS__, 'fields' ) );
		add_action( 'woocommerce_admin_process_product_object', array( __CLASS__, 'save' ) );
		add_action( 'woocommerce_single_product_summary', array( __CLASS__, 'single_summary' ), 7 );
		add_filter( 'woocommerce_is_purchasable', array( __CLASS__, 'purchasable' ), 10, 2 );
		add_action( 'woocommerce_single_product_summary', array( __CLASS__, 'auction_notice' ), 30 );
	}

	/**
	 * Condition grades, slug => label.
	 */
	public static function conditions() {
		return apply_filters(
			'epif_conditions',
			array(
				'new'      => __( 'New', 'epif' ),
				'like-new' => __( 'Like new', 'epif' ),
				'good'     => __( 'Good', 'epif' ),
				'fair'     => __( 'Fair', 'epif' ),
				'parts'    => __( 'For parts / repair', 'epif' ),
			)
		);
	}

	/**
	 * Fulfillment options, slug => label.
	 */
	public static function fulfillments() {
		$pickup = EPIF_Store::setting( 'pickup_location' );

		return apply_filters(
			'epif_fulfillments',
			array(
				'ship'   => __( 'Ships nationwide', 'epif' ),
				/* translators: %s: pickup location, e.g. Westminster, MD */
				'pickup' => sprintf( __( 'Pickup in %s', 'epif' ), $pickup ),
				/* translators: %s: pickup location */
				'both'   => sprintf( __( 'Ships or pickup in %s', 'epif' ), $pickup ),
			)
		);
	}

	/* ---------------------------------------------------------------------
	 * Getters
	 * ------------------------------------------------------------------- */

	public static function condition( $product ) {
		$slug = (string) $product->get_meta( self::CONDITION );
		$all  = self::conditions();
		return isset( $all[ $slug ] ) ? $all[ $slug ] : '';
	}

	public static function fulfillment( $product ) {
		$slug = (string) $product->get_meta( self::FULFILLMENT );
		$all  = self::fulfillments();
		return isset( $all[ $slug ] ) ? $all[ $slug ] : '';
	}

	public static function tokens( $product ) {
		return max( 0, (int) $product->get_meta( self::TOKENS ) );
	}

	/**
	 * Auction end as a Unix timestamp, or 0 when the item isn't an auction.
	 */
	public static function auction_end( $product ) {
		return (int) apply_filters( 'epif_auction_end', (int) $product->get_meta( self::AUCTION_END ), $product );
	}

	public static function is_auction( $product ) {
		return self::auction_end( $product ) > 0;
	}

	public static function is_live_auction( $product ) {
		return self::auction_end( $product ) > time();
	}

	/**
	 * Starting text for a countdown; epif-timers.js keeps it ticking.
	 */
	public static function time_left( $end ) {
		$secs = $end - time();
		if ( $secs <= 0 ) {
			return __( 'Ended', 'epif' );
		}
		$d = (int) floor( $secs / DAY_IN_SECONDS );
		$h = (int) floor( ( $secs % DAY_IN_SECONDS ) / HOUR_IN_SECONDS );
		$m = (int) floor( ( $secs % HOUR_IN_SECONDS ) / MINUTE_IN_SECONDS );
		$s = $secs % MINUTE_IN_SECONDS;
		if ( $d > 0 ) {
			return sprintf( '%dd %dh', $d, $h );
		}
		if ( $h > 0 ) {
			return sprintf( '%dh %02dm', $h, $m );
		}
		return sprintf( '%02d:%02d', $m, $s );
	}

	/**
	 * Countdown markup for an auction item.
	 */
	public static function timer_html( $product ) {
		$end = self::auction_end( $product );
		if ( ! $end ) {
			return '';
		}
		$class = 'epif-timer';
		if ( $end <= time() ) {
			$class .= ' epif-timer--ended';
		} elseif ( $end - time() < HOUR_IN_SECONDS ) {
			$class .= ' epif-timer--urgent';
		}
		return sprintf(
			'<p class="%1$s" data-epif-end="%2$s"><span class="screen-reader-text">%3$s </span>%4$s</p>',
			esc_attr( $class ),
			esc_attr( gmdate( 'c', $end ) ),
			esc_html__( 'Time left:', 'epif' ),
			esc_html( self::time_left( $end ) )
		);
	}

	/* ---------------------------------------------------------------------
	 * Admin
	 * ------------------------------------------------------------------- */

	public static function fields() {
		global $product_object;

		$end   = $product_object ? (int) $product_object->get_meta( self::AUCTION_END ) : 0;
		$local = $end ? wp_date( 'Y-m-d\TH:i', $end ) : '';

		echo '<div class="options_group epif-listing-fields">';
		echo '<p class="form-field"><strong>' . esc_html__( 'EPIF listing', 'epif' ) . '</strong></p>';

		woocommerce_wp_select(
			array(
				'id'      => self::CONDITION,
				'label'   => __( 'Condition', 'epif' ),
				'options' => array( '' => __( '— Not set —', 'epif' ) ) + self::conditions(),
			)
		);
		woocommerce_wp_select(
			array(
				'id'          => self::FULFILLMENT,
				'label'       => __( 'Shipping / pickup', 'epif' ),
				'options'     => array( '' => __( '— Not set —', 'epif' ) ) + self::fulfillments(),
				'desc_tip'    => true,
				'description' => __( 'Shown on the item card and used by the shop\'s Shipping filter.', 'epif' ),
			)
		);
		woocommerce_wp_text_input(
			array(
				'id'                => self::TOKENS,
				'label'             => __( 'Tokens earned', 'epif' ),
				'type'              => 'number',
				'custom_attributes' => array( 'min' => '0', 'step' => '1' ),
				'desc_tip'          => true,
				'description'       => __( 'Shown as a green "+N tokens" badge. Leave empty for none.', 'epif' ),
			)
		);
		woocommerce_wp_text_input(
			array(
				'id'          => self::AUCTION_END,
				'value'       => $local,
				'label'       => __( 'Auction ends', 'epif' ),
				'type'        => 'datetime-local',
				'desc_tip'    => true,
				/* translators: %s: site timezone name */
				'description' => sprintf( __( 'Site time (%s). Set this to list the item as a live auction with a countdown. Bidding needs an auction plugin.', 'epif' ), wp_timezone_string() ),
			)
		);
		echo '</div>';
	}

	public static function save( $product ) {
		// WooCommerce verifies the product edit nonce before this action fires.
		// phpcs:disable WordPress.Security.NonceVerification.Missing
		$condition   = isset( $_POST[ self::CONDITION ] ) ? sanitize_key( wp_unslash( $_POST[ self::CONDITION ] ) ) : '';
		$fulfillment = isset( $_POST[ self::FULFILLMENT ] ) ? sanitize_key( wp_unslash( $_POST[ self::FULFILLMENT ] ) ) : '';
		$tokens      = isset( $_POST[ self::TOKENS ] ) ? absint( $_POST[ self::TOKENS ] ) : 0;
		$end_raw     = isset( $_POST[ self::AUCTION_END ] ) ? sanitize_text_field( wp_unslash( $_POST[ self::AUCTION_END ] ) ) : '';
		// phpcs:enable

		self::set( $product, self::CONDITION, isset( self::conditions()[ $condition ] ) ? $condition : '' );
		self::set( $product, self::FULFILLMENT, isset( self::fulfillments()[ $fulfillment ] ) ? $fulfillment : '' );
		self::set( $product, self::TOKENS, $tokens ? $tokens : '' );

		$end = '';
		if ( $end_raw ) {
			$date = date_create_immutable_from_format( 'Y-m-d\TH:i', $end_raw, wp_timezone() );
			$end  = $date ? $date->getTimestamp() : '';
		}
		self::set( $product, self::AUCTION_END, $end );
	}

	private static function set( $product, $key, $value ) {
		if ( '' === $value || null === $value ) {
			$product->delete_meta_data( $key );
		} else {
			$product->update_meta_data( $key, $value );
		}
	}

	/* ---------------------------------------------------------------------
	 * Auctions without a bidding plugin
	 * ------------------------------------------------------------------- */

	/**
	 * A live auction can't be bought outright at its starting price. An auction
	 * plugin that handles its own checkout can turn this off with
	 * add_filter( 'epif_auction_blocks_purchase', '__return_false' ).
	 */
	public static function purchasable( $purchasable, $product ) {
		if ( $purchasable && self::is_live_auction( $product ) && apply_filters( 'epif_auction_blocks_purchase', true, $product ) ) {
			return false;
		}
		return $purchasable;
	}

	public static function auction_notice() {
		global $product;
		if ( ! $product instanceof WC_Product || ! self::is_live_auction( $product ) || ! apply_filters( 'epif_auction_blocks_purchase', true, $product ) ) {
			return;
		}
		printf(
			'<div class="epif epif-auction-note"><p><strong>%1$s</strong> %2$s</p><a class="epif-btn epif-btn--bid" href="%3$s">%4$s</a></div>',
			esc_html__( 'This item is up for auction.', 'epif' ),
			esc_html__( 'Online bidding isn\'t open yet — tell us your bid and we\'ll confirm it before the countdown ends.', 'epif' ),
			esc_url( add_query_arg( 'item', rawurlencode( $product->get_name() ), home_url( '/' ) ) . '#book' ),
			esc_html__( 'Ask about this item', 'epif' )
		);
	}

	/* ---------------------------------------------------------------------
	 * Product page
	 * ------------------------------------------------------------------- */

	/**
	 * Countdown and listing tags under the product title.
	 */
	public static function single_summary() {
		global $product;
		if ( ! $product instanceof WC_Product ) {
			return;
		}

		$tags = EPIF_Store::card_tags( $product, true );
		$out  = self::timer_html( $product );
		if ( $tags ) {
			$out .= '<div class="epif-tags epif-product__tags">' . $tags . '</div>';
		}
		if ( $out ) {
			echo '<div class="epif epif-product__listing">' . $out . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput -- built from escaped parts.
		}
	}
}
