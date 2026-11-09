<?php
/**
 * EPIF storefront: settings, browse filters, item cards and the dynamic blocks
 * the theme templates and landing page are built from.
 *
 * Every piece renders on the server from real WooCommerce data, so the pages
 * stay accurate as stock changes. Each block is also a shortcode of the same
 * name (epif/product-loop → [epif_product_loop]) for classic themes and page
 * builders.
 *
 * Browse filters are plain GET parameters, so filtered views are linkable
 * and work without JavaScript:
 *   listing=auction|buy  condition=good,fair  fulfillment=ship|pickup
 *   instock=1  min_price / max_price / orderby / product_cat (WooCommerce's own)
 *
 * @package epif
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EPIF_Store {

	const OPTION = 'epif_store';

	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_blocks' ) );
		add_action( 'admin_init', array( __CLASS__, 'register_setting' ) );
		add_action( 'admin_menu', array( __CLASS__, 'add_settings_page' ) );

		add_action( 'pre_get_posts', array( __CLASS__, 'filter_main_query' ), 20 );
		add_filter( 'woocommerce_get_catalog_ordering_args', array( __CLASS__, 'ordering_args' ), 10, 2 );
		add_filter( 'woocommerce_catalog_orderby', array( __CLASS__, 'orderby_options' ) );
		add_filter( 'woocommerce_add_to_cart_fragments', array( __CLASS__, 'cart_fragment' ) );

		foreach ( array( 'save_post_product', 'woocommerce_product_set_stock_status', 'woocommerce_update_product', 'deleted_post' ) as $hook ) {
			add_action( $hook, array( __CLASS__, 'flush_stats' ) );
		}
	}

	public static function wc() {
		return class_exists( 'WooCommerce' ) && function_exists( 'wc_get_product' );
	}

	/* =====================================================================
	 * Settings (Settings → EPIF Store)
	 * =================================================================== */

	public static function defaults() {
		return array(
			'pickup_location' => 'Westminster, MD',
			'service_area'    => 'Carroll County and the Baltimore area',
			'diverted'        => '',
		);
	}

	public static function setting( $key ) {
		$saved    = get_option( self::OPTION, array() );
		$defaults = self::defaults();
		$value    = isset( $saved[ $key ] ) ? trim( (string) $saved[ $key ] ) : '';
		if ( '' === $value && 'diverted' !== $key ) {
			$value = isset( $defaults[ $key ] ) ? $defaults[ $key ] : '';
		}
		return $value;
	}

	public static function register_setting() {
		register_setting(
			'epif_store',
			self::OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => static function ( $input ) {
					$out = array();
					foreach ( array_keys( self::defaults() ) as $key ) {
						$out[ $key ] = isset( $input[ $key ] ) ? sanitize_text_field( $input[ $key ] ) : '';
					}
					return $out;
				},
			)
		);
	}

	public static function add_settings_page() {
		add_options_page( __( 'EPIF Store', 'epif' ), __( 'EPIF Store', 'epif' ), 'manage_options', 'epif-store', array( __CLASS__, 'settings_page' ) );
	}

	public static function settings_page() {
		$fields = array(
			'pickup_location' => array( __( 'Pickup location', 'epif' ), __( 'Shown on items marked for pickup, e.g. "Pickup in Westminster, MD".', 'epif' ) ),
			'service_area'    => array( __( 'Service area', 'epif' ), __( 'Where you do clear-outs. Used on the landing page and footer.', 'epif' ) ),
			'diverted'        => array( __( 'Kept out of landfill', 'epif' ), __( 'A real, current figure such as "12 tons". Leave empty to hide the stat on the landing page and footer.', 'epif' ) ),
		);
		$saved = (array) get_option( self::OPTION, array() );
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'EPIF Store', 'epif' ); ?></h1>
			<form method="post" action="options.php">
				<?php settings_fields( 'epif_store' ); ?>
				<table class="form-table" role="presentation">
					<?php foreach ( $fields as $key => $field ) : ?>
						<tr>
							<th scope="row"><label for="epif-store-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $field[0] ); ?></label></th>
							<td>
								<input class="regular-text" id="epif-store-<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( self::OPTION . '[' . $key . ']' ); ?>" value="<?php echo esc_attr( $saved[ $key ] ?? '' ); ?>" placeholder="<?php echo esc_attr( self::defaults()[ $key ] ); ?>">
								<p class="description"><?php echo esc_html( $field[1] ); ?></p>
							</td>
						</tr>
					<?php endforeach; ?>
				</table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}

	/* =====================================================================
	 * URLs and request state
	 * =================================================================== */

	public static function shop_url() {
		if ( function_exists( 'wc_get_page_permalink' ) ) {
			$url = wc_get_page_permalink( 'shop' );
			if ( $url && home_url( '/' ) !== $url ) {
				return $url;
			}
			return add_query_arg( 'post_type', 'product', home_url( '/' ) );
		}
		return home_url( '/' );
	}

	public static function cart_url() {
		return function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : '';
	}

	/**
	 * Is this request a product listing (shop, category, tag, product search)?
	 */
	public static function is_browse( $query = null ) {
		$query = $query ? $query : $GLOBALS['wp_query'];
		if ( ! $query instanceof WP_Query ) {
			return false;
		}
		$product_tax = array( 'product_cat', 'product_tag' );
		if ( taxonomy_exists( 'product_brand' ) ) {
			$product_tax[] = 'product_brand';
		}
		$is_attribute = false;
		$term         = $query->get_queried_object();
		if ( $term instanceof WP_Term && 0 === strpos( $term->taxonomy, 'pa_' ) ) {
			$is_attribute = true;
		}
		return $query->is_post_type_archive( 'product' )
			|| $query->is_tax( $product_tax )
			|| $is_attribute
			|| ( $query->is_search() && 'product' === $query->get( 'post_type' ) );
	}

	/**
	 * Current browse filters, sanitized.
	 */
	public static function filters() {
		// Read-only filtering of a public listing; no nonce needed.
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		$get = wp_unslash( $_GET );
		// phpcs:enable

		$listing = isset( $get['listing'] ) ? sanitize_key( $get['listing'] ) : '';
		$listing = in_array( $listing, array( 'auction', 'buy' ), true ) ? $listing : '';

		$conditions = array();
		if ( isset( $get['condition'] ) ) {
			$raw        = is_array( $get['condition'] ) ? $get['condition'] : explode( ',', (string) $get['condition'] );
			$conditions = array_values( array_intersect( array_map( 'sanitize_key', $raw ), array_keys( EPIF_Listing::conditions() ) ) );
		}

		$fulfillment = isset( $get['fulfillment'] ) ? sanitize_key( $get['fulfillment'] ) : '';
		$fulfillment = in_array( $fulfillment, array( 'ship', 'pickup' ), true ) ? $fulfillment : '';

		$price = static function ( $key ) use ( $get ) {
			if ( ! isset( $get[ $key ] ) || '' === $get[ $key ] || is_array( $get[ $key ] ) ) {
				return '';
			}
			$value = (float) $get[ $key ];
			return $value >= 0 ? $value : '';
		};

		return array(
			'listing'     => $listing,
			'condition'   => $conditions,
			'fulfillment' => $fulfillment,
			'instock'     => ! empty( $get['instock'] ),
			'min_price'   => $price( 'min_price' ),
			'max_price'   => $price( 'max_price' ),
			'orderby'     => isset( $get['orderby'] ) && ! is_array( $get['orderby'] ) ? sanitize_key( $get['orderby'] ) : '',
			's'           => get_search_query( false ),
		);
	}

	/**
	 * Base URL of the listing being viewed, without page number or filters.
	 */
	public static function browse_base_url() {
		if ( is_search() ) {
			return add_query_arg(
				array(
					's'         => rawurlencode( get_search_query( false ) ),
					'post_type' => 'product',
				),
				home_url( '/' )
			);
		}
		$term = get_queried_object();
		if ( $term instanceof WP_Term ) {
			$link = get_term_link( $term );
			if ( ! is_wp_error( $link ) ) {
				return $link;
			}
		}
		return self::shop_url();
	}

	/**
	 * Browse URL with the current filters, changed by $changes (null removes).
	 */
	public static function browse_url( $changes = array(), $base = null ) {
		$f    = self::filters();
		$args = array(
			'listing'     => $f['listing'],
			'condition'   => implode( ',', $f['condition'] ),
			'fulfillment' => $f['fulfillment'],
			'instock'     => $f['instock'] ? '1' : '',
			'min_price'   => '' === $f['min_price'] ? '' : $f['min_price'],
			'max_price'   => '' === $f['max_price'] ? '' : $f['max_price'],
			'orderby'     => $f['orderby'],
		);
		foreach ( $changes as $key => $value ) {
			$args[ $key ] = null === $value ? '' : $value;
		}
		$args = array_filter(
			$args,
			static function ( $v ) {
				return '' !== $v && null !== $v;
			}
		);
		return add_query_arg( $args, $base ? $base : self::browse_base_url() );
	}

	/* =====================================================================
	 * Query changes for the browse pages
	 * =================================================================== */

	public static function filter_main_query( $q ) {
		if ( is_admin() || ! $q->is_main_query() || ! self::is_browse( $q ) ) {
			return;
		}

		$f    = self::filters();
		$meta = (array) $q->get( 'meta_query' );
		$tax  = (array) $q->get( 'tax_query' );

		if ( 'auction' === $f['listing'] ) {
			$meta[] = array(
				'key'     => EPIF_Listing::AUCTION_END,
				'value'   => time(),
				'compare' => '>',
				'type'    => 'NUMERIC',
			);
		} elseif ( 'buy' === $f['listing'] ) {
			$meta[] = array(
				'relation' => 'OR',
				array(
					'key'     => EPIF_Listing::AUCTION_END,
					'compare' => 'NOT EXISTS',
				),
				array(
					'key'   => EPIF_Listing::AUCTION_END,
					'value' => '',
				),
			);
		}

		if ( $f['condition'] ) {
			$meta[] = array(
				'key'     => EPIF_Listing::CONDITION,
				'value'   => $f['condition'],
				'compare' => 'IN',
			);
		}

		if ( $f['fulfillment'] ) {
			$meta[] = array(
				'key'     => EPIF_Listing::FULFILLMENT,
				'value'   => array( $f['fulfillment'], 'both' ),
				'compare' => 'IN',
			);
		}

		if ( $f['instock'] ) {
			$tax[] = array(
				'taxonomy' => 'product_visibility',
				'field'    => 'name',
				'terms'    => array( 'outofstock' ),
				'operator' => 'NOT IN',
			);
		}

		$q->set( 'meta_query', $meta );
		$q->set( 'tax_query', $tax );
	}

	/**
	 * "Ending soonest" sort for auctions.
	 */
	public static function ordering_args( $args, $orderby ) {
		if ( 'ending' === $orderby ) {
			$args['orderby']  = 'meta_value_num';
			$args['order']    = 'ASC';
			$args['meta_key'] = EPIF_Listing::AUCTION_END; // phpcs:ignore WordPress.DB.SlowDBQuery
		}
		return $args;
	}

	public static function orderby_options( $options ) {
		$f = self::filters();
		if ( 'auction' === $f['listing'] ) {
			$options['ending'] = __( 'Ending soonest', 'epif' );
		}
		return $options;
	}

	/* =====================================================================
	 * Product queries for the landing page sections
	 * =================================================================== */

	/**
	 * @return array{0: WC_Product[], 1: int} Products and total matches.
	 */
	public static function query_products( $type, $limit, $category = '' ) {
		if ( ! self::wc() ) {
			return array( array(), 0 );
		}

		$args = array(
			'post_type'           => 'product',
			'post_status'         => 'publish',
			'posts_per_page'      => max( 1, min( 24, (int) $limit ) ),
			'fields'              => 'ids',
			'ignore_sticky_posts' => true,
			'orderby'             => 'date',
			'order'               => 'DESC',
			'tax_query'           => array( // phpcs:ignore WordPress.DB.SlowDBQuery
				array(
					'taxonomy' => 'product_visibility',
					'field'    => 'name',
					'terms'    => array( 'exclude-from-catalog', 'outofstock' ),
					'operator' => 'NOT IN',
				),
			),
		);

		if ( $category ) {
			$args['tax_query'][] = array(
				'taxonomy' => 'product_cat',
				'field'    => 'slug',
				'terms'    => array_map( 'sanitize_title', explode( ',', $category ) ),
			);
		}

		if ( 'auction' === $type ) {
			$args['meta_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery
				array(
					'key'     => EPIF_Listing::AUCTION_END,
					'value'   => time(),
					'compare' => '>',
					'type'    => 'NUMERIC',
				),
			);
			$args['meta_key'] = EPIF_Listing::AUCTION_END; // phpcs:ignore WordPress.DB.SlowDBQuery
			$args['orderby']  = 'meta_value_num';
			$args['order']    = 'ASC';
		} elseif ( 'buy' === $type ) {
			$args['meta_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery
				'relation' => 'OR',
				array(
					'key'     => EPIF_Listing::AUCTION_END,
					'compare' => 'NOT EXISTS',
				),
				array(
					'key'   => EPIF_Listing::AUCTION_END,
					'value' => '',
				),
			);
		} elseif ( 'sale' === $type ) {
			$args['post__in'] = array_merge( array( 0 ), wc_get_product_ids_on_sale() );
		}

		$query    = new WP_Query( apply_filters( 'epif_products_query_args', $args, $type ) );
		$products = array_filter( array_map( 'wc_get_product', $query->posts ) );

		return array( array_values( $products ), (int) $query->found_posts );
	}

	/* =====================================================================
	 * Item card
	 * =================================================================== */

	/**
	 * Listing tags: condition, fulfillment, tokens (and Sale/Sold on cards).
	 */
	public static function card_tags( $product, $detail = false ) {
		$tags = array();
		if ( $detail ) {
			$condition = EPIF_Listing::condition( $product );
			if ( $condition ) {
				/* translators: %s: condition, e.g. Good */
				$tags[] = '<span class="epif-tag">' . esc_html( sprintf( __( 'Condition: %s', 'epif' ), $condition ) ) . '</span>';
			}
			$fulfillment = EPIF_Listing::fulfillment( $product );
			if ( $fulfillment ) {
				$tags[] = '<span class="epif-tag">' . esc_html( $fulfillment ) . '</span>';
			}
		}
		$tokens = EPIF_Listing::tokens( $product );
		if ( $tokens ) {
			/* translators: %s: number of tokens */
			$tags[] = '<span class="epif-tag epif-tag--token">' . esc_html( sprintf( __( '+%s tokens', 'epif' ), number_format_i18n( $tokens ) ) ) . '</span>';
		}
		return implode( '', $tags );
	}

	public static function card( $product ) {
		$id       = $product->get_id();
		$url      = $product->get_permalink();
		$title    = $product->get_name();
		$auction  = EPIF_Listing::is_auction( $product );
		$live     = EPIF_Listing::is_live_auction( $product );
		$in_stock = $product->is_in_stock();

		$badges = array();
		if ( $live ) {
			$badges[] = '<span class="epif-tag epif-tag--live">' . esc_html__( 'Live', 'epif' ) . '</span>';
		} elseif ( $auction ) {
			$badges[] = '<span class="epif-tag">' . esc_html__( 'Auction ended', 'epif' ) . '</span>';
		} elseif ( ! $in_stock ) {
			$badges[] = '<span class="epif-tag epif-tag--sold">' . esc_html__( 'Sold', 'epif' ) . '</span>';
		} elseif ( $product->is_on_sale() ) {
			$badges[] = '<span class="epif-tag epif-tag--sale">' . esc_html__( 'Sale', 'epif' ) . '</span>';
		}
		$badges = implode( '', $badges ) . self::card_tags( $product );

		$notes = array_filter( array( EPIF_Listing::condition( $product ), EPIF_Listing::fulfillment( $product ) ) );

		$price = $product->get_price_html();
		if ( $auction && $price ) {
			$price = '<span class="epif-price__label">' . esc_html__( 'Starting bid', 'epif' ) . '</span> ' . $price;
		}

		ob_start();
		?>
		<article class="epif-card<?php echo $in_stock ? '' : ' epif-card--sold'; ?>">
			<a class="epif-card__media" href="<?php echo esc_url( $url ); ?>" tabindex="-1" aria-hidden="true">
				<?php echo $product->get_image( 'woocommerce_thumbnail', array( 'alt' => '', 'loading' => 'lazy' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<?php if ( $badges ) : ?>
					<span class="epif-card__badges"><?php echo $badges; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped above. ?></span>
				<?php endif; ?>
			</a>
			<div class="epif-card__body">
				<?php echo $auction ? EPIF_Listing::timer_html( $product ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<h3 class="epif-card__title"><a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $title ); ?></a></h3>
				<p class="epif-card__meta">
					<span class="epif-price<?php echo $live ? ' epif-price--live' : ''; ?>"><?php echo wp_kses_post( $price ); ?></span>
				</p>
				<?php if ( $notes ) : ?>
					<p class="epif-card__note"><?php echo esc_html( implode( ' · ', $notes ) ); ?></p>
				<?php endif; ?>
				<div class="epif-card__foot"><?php echo self::card_button( $product, $live ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
			</div>
		</article>
		<?php
		return ob_get_clean();
	}

	private static function card_button( $product, $live ) {
		$url   = $product->get_permalink();
		$label = $product->get_name();

		if ( $live ) {
			/* translators: %s: item name */
			return sprintf( '<a class="epif-btn epif-btn--bid" href="%s" aria-label="%s">%s</a>', esc_url( $url ), esc_attr( sprintf( __( 'View auction: %s', 'epif' ), $label ) ), esc_html__( 'View auction', 'epif' ) );
		}
		if ( ! $product->is_in_stock() ) {
			return '<span class="epif-btn epif-btn--ghost is-disabled" aria-disabled="true">' . esc_html__( 'Sold', 'epif' ) . '</span>';
		}
		if ( $product->is_purchasable() && $product->supports( 'ajax_add_to_cart' ) && ! EPIF_Listing::is_auction( $product ) ) {
			wp_enqueue_script( 'wc-add-to-cart' );
			return sprintf(
				'<a href="%1$s" data-quantity="1" data-product_id="%2$d" data-product_sku="%3$s" class="epif-btn epif-btn--primary add_to_cart_button ajax_add_to_cart" aria-label="%4$s" rel="nofollow">%5$s</a>',
				esc_url( $product->add_to_cart_url() ),
				$product->get_id(),
				esc_attr( $product->get_sku() ),
				esc_attr( $product->add_to_cart_description() ),
				esc_html( $product->add_to_cart_text() )
			);
		}
		/* translators: %s: item name */
		return sprintf( '<a class="epif-btn epif-btn--ghost" href="%s" aria-label="%s">%s</a>', esc_url( $url ), esc_attr( sprintf( __( 'View item: %s', 'epif' ), $label ) ), esc_html__( 'View item', 'epif' ) );
	}

	private static function grid( $cards, $columns = 4, $extra = '' ) {
		$columns = (int) $columns;
		$class   = 'epif-grid epif-grid--cards' . ( 3 === $columns ? ' epif-grid--3' : '' );
		return '<div class="' . esc_attr( $class ) . '">' . implode( '', $cards ) . $extra . '</div>';
	}

	private static function empty_state( $title, $text, $link = '', $link_text = '' ) {
		$out  = '<div class="epif-empty">';
		$out .= '<p class="epif-empty__title">' . esc_html( $title ) . '</p>';
		$out .= '<p>' . esc_html( $text ) . '</p>';
		if ( $link ) {
			$out .= '<a class="epif-btn epif-btn--ghost" href="' . esc_url( $link ) . '">' . esc_html( $link_text ) . '</a>';
		}
		return $out . '</div>';
	}

	/* =====================================================================
	 * Blocks
	 * =================================================================== */

	public static function blocks() {
		return array(
			'products'        => array(
				'title'       => __( 'EPIF item grid', 'epif' ),
				'description' => __( 'Item cards from the store: live auctions, buy-it-now, on sale or newest.', 'epif' ),
				'render'      => array( __CLASS__, 'render_products' ),
				'attributes'  => array(
					'type'     => array( 'type' => 'string', 'default' => 'all', 'enum' => array( 'all', 'auction', 'buy', 'sale' ) ),
					'limit'    => array( 'type' => 'integer', 'default' => 8 ),
					'category' => array( 'type' => 'string', 'default' => '' ),
					'columns'  => array( 'type' => 'integer', 'default' => 4 ),
					'more'     => array( 'type' => 'boolean', 'default' => false ),
				),
			),
			'category-chips'  => array(
				'title'       => __( 'EPIF category chips', 'epif' ),
				'description' => __( 'Top-level product categories as a row of chips.', 'epif' ),
				'render'      => array( __CLASS__, 'render_category_chips' ),
			),
			'store-stats'     => array(
				'title'       => __( 'EPIF store stats', 'epif' ),
				'description' => __( 'Live counts for the hero: items listed, auctions closing today, landfill diversion and location.', 'epif' ),
				'render'      => array( __CLASS__, 'render_store_stats' ),
			),
			'auction-summary' => array(
				'title'       => __( 'EPIF live auction count', 'epif' ),
				'description' => __( 'Live-auction count and a link to every auction.', 'epif' ),
				'render'      => array( __CLASS__, 'render_auction_summary' ),
			),
			'shop-header'     => array(
				'title'       => __( 'EPIF shop header', 'epif' ),
				'description' => __( 'Breadcrumb, title, search, listing tabs and categories for the browse pages.', 'epif' ),
				'render'      => array( __CLASS__, 'render_shop_header' ),
			),
			'shop-filters'    => array(
				'title'       => __( 'EPIF shop filters', 'epif' ),
				'description' => __( 'Filter sidebar for the browse pages.', 'epif' ),
				'render'      => array( __CLASS__, 'render_shop_filters' ),
			),
			'shop-toolbar'    => array(
				'title'       => __( 'EPIF shop toolbar', 'epif' ),
				'description' => __( 'Result count, active filters and sort order.', 'epif' ),
				'render'      => array( __CLASS__, 'render_shop_toolbar' ),
			),
			'product-loop'    => array(
				'title'       => __( 'EPIF browse grid', 'epif' ),
				'description' => __( 'The items on the current shop, category or search page, with pagination.', 'epif' ),
				'render'      => array( __CLASS__, 'render_product_loop' ),
			),
			'single-product'  => array(
				'title'       => __( 'EPIF product page', 'epif' ),
				'description' => __( 'The item being viewed: gallery, price, add to cart or auction countdown, details and related items.', 'epif' ),
				'render'      => array( __CLASS__, 'render_single_product' ),
			),
			'header-cart'     => array(
				'title'       => __( 'EPIF cart link', 'epif' ),
				'description' => __( 'Cart link with item count, for the header.', 'epif' ),
				'render'      => array( __CLASS__, 'render_header_cart' ),
			),
			'booking-form'    => array(
				'title'       => __( 'EPIF clear-out request form', 'epif' ),
				'description' => __( 'Lead form for clear-outs and the other ways to work with EPIF. Submissions go to Leads.', 'epif' ),
				'render'      => array( __CLASS__, 'render_booking_form' ),
			),
		);
	}

	public static function register_blocks() {
		foreach ( self::blocks() as $slug => $block ) {
			$attributes = isset( $block['attributes'] ) ? $block['attributes'] : array();
			register_block_type(
				'epif/' . $slug,
				array(
					'api_version'     => 3,
					'title'           => $block['title'],
					'description'     => $block['description'],
					'category'        => 'widgets',
					'icon'            => 'store',
					'attributes'      => $attributes,
					'supports'        => array(
						'html'         => false,
						'autoRegister' => true,
					),
					'render_callback' => static function ( $atts ) use ( $block ) {
						return call_user_func( $block['render'], (array) $atts );
					},
				)
			);

			add_shortcode(
				'epif_' . str_replace( '-', '_', $slug ),
				static function ( $atts ) use ( $block, $attributes ) {
					$atts = (array) $atts;
					foreach ( $attributes as $key => $def ) {
						if ( ! isset( $atts[ $key ] ) ) {
							$atts[ $key ] = $def['default'];
						} elseif ( 'integer' === $def['type'] ) {
							$atts[ $key ] = (int) $atts[ $key ];
						} elseif ( 'boolean' === $def['type'] ) {
							$atts[ $key ] = in_array( strtolower( (string) $atts[ $key ] ), array( '1', 'true', 'yes' ), true );
						}
					}
					return call_user_func( $block['render'], $atts );
				}
			);
		}
	}

	/* ---------------------------------------------------------------------
	 * Landing page blocks
	 * ------------------------------------------------------------------- */

	public static function render_products( $atts ) {
		$atts = wp_parse_args(
			$atts,
			array(
				'type'     => 'all',
				'limit'    => 8,
				'category' => '',
				'columns'  => 4,
				'more'     => false,
			)
		);

		if ( ! self::wc() ) {
			return self::empty_state( __( 'The shop isn\'t open yet', 'epif' ), __( 'Items will appear here once WooCommerce is active and products are published.', 'epif' ) );
		}

		list( $products, $total ) = self::query_products( $atts['type'], $atts['limit'], $atts['category'] );

		if ( ! $products ) {
			if ( 'auction' === $atts['type'] ) {
				return self::empty_state( __( 'No auctions running right now', 'epif' ), __( 'New auctions open as we finish sorting each clear-out. Everything else is up for sale now.', 'epif' ), add_query_arg( 'listing', 'buy', self::shop_url() ), __( 'Shop buy-it-now items', 'epif' ) );
			}
			return self::empty_state( __( 'New items are on the way', 'epif' ), __( 'We\'re photographing and listing the latest clear-outs. Check back soon.', 'epif' ) );
		}

		$cards = array_map( array( __CLASS__, 'card' ), $products );
		$extra = '';
		$left  = $total - count( $products );
		if ( $atts['more'] && $left > 0 ) {
			$link  = add_query_arg( 'listing', in_array( $atts['type'], array( 'auction', 'buy' ), true ) ? $atts['type'] : false, self::shop_url() );
			$extra = sprintf(
				'<a class="epif-card epif-card--more" href="%s"><b>%s</b><span>%s</span></a>',
				esc_url( $link ),
				/* translators: %s: number of items */
				esc_html( sprintf( _n( '%s more item', '%s more items', $left, 'epif' ), number_format_i18n( $left ) ) ),
				esc_html__( 'Browse the full marketplace →', 'epif' )
			);
		}

		return '<div class="epif">' . self::grid( $cards, $atts['columns'], $extra ) . '</div>';
	}

	public static function render_category_chips() {
		if ( ! taxonomy_exists( 'product_cat' ) ) {
			return '';
		}
		$terms = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'hide_empty' => true,
				'parent'     => 0,
				'exclude'    => array( (int) get_option( 'default_product_cat', 0 ) ),
				'orderby'    => 'name',
			)
		);
		if ( is_wp_error( $terms ) || ! $terms ) {
			return '';
		}

		$current = get_queried_object();
		$current = $current instanceof WP_Term && 'product_cat' === $current->taxonomy ? $current : null;
		$active  = array();
		if ( $current ) {
			$active   = get_ancestors( $current->term_id, 'product_cat', 'taxonomy' );
			$active[] = $current->term_id;
		}

		$keep = self::is_browse() ? array( 'listing' => self::filters()['listing'] ) : array();
		$all  = add_query_arg( array_filter( $keep ), self::shop_url() );

		$out = '<nav class="epif epif-tags epif-chips" aria-label="' . esc_attr__( 'Categories', 'epif' ) . '">';
		$out .= sprintf( '<a class="epif-tag%s" href="%s"%s>%s</a>', $active ? '' : ' epif-tag--on', esc_url( $all ), $active ? '' : ' aria-current="page"', esc_html__( 'All', 'epif' ) );
		foreach ( $terms as $term ) {
			$on   = in_array( $term->term_id, $active, true );
			$link = get_term_link( $term );
			if ( is_wp_error( $link ) ) {
				continue;
			}
			$out .= sprintf(
				'<a class="epif-tag%s" href="%s"%s>%s</a>',
				$on ? ' epif-tag--on' : '',
				esc_url( add_query_arg( array_filter( $keep ), $link ) ),
				$on ? ' aria-current="page"' : '',
				esc_html( $term->name )
			);
		}
		return $out . '</nav>';
	}

	private static function stats() {
		$stats = get_transient( 'epif_store_stats' );
		if ( is_array( $stats ) ) {
			return $stats;
		}
		$stats = array(
			'items'         => 0,
			'live_auctions' => 0,
			'closing_today' => 0,
		);
		if ( self::wc() ) {
			list( , $stats['items'] )         = self::query_products( 'all', 1 );
			list( , $stats['live_auctions'] ) = self::query_products( 'auction', 1 );

			$tomorrow = ( new DateTimeImmutable( 'tomorrow', wp_timezone() ) )->getTimestamp();
			$closing  = new WP_Query(
				array(
					'post_type'      => 'product',
					'post_status'    => 'publish',
					'posts_per_page' => 1,
					'fields'         => 'ids',
					'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery
						array(
							'key'     => EPIF_Listing::AUCTION_END,
							'value'   => array( time(), $tomorrow ),
							'compare' => 'BETWEEN',
							'type'    => 'NUMERIC',
						),
					),
				)
			);
			$stats['closing_today'] = (int) $closing->found_posts;
		}
		set_transient( 'epif_store_stats', $stats, 10 * MINUTE_IN_SECONDS );
		return $stats;
	}

	public static function flush_stats() {
		delete_transient( 'epif_store_stats' );
	}

	public static function render_store_stats() {
		$stats = self::stats();
		$items = array();
		if ( self::wc() ) {
			$items[] = array( number_format_i18n( $stats['items'] ), _n( 'item listed right now', 'items listed right now', $stats['items'], 'epif' ), '' );
			if ( $stats['live_auctions'] ) {
				$items[] = array( number_format_i18n( $stats['closing_today'] ), _n( 'auction closing today', 'auctions closing today', $stats['closing_today'], 'epif' ), '' );
			}
		}
		$diverted = self::setting( 'diverted' );
		if ( $diverted ) {
			$items[] = array( $diverted, __( 'kept out of landfill', 'epif' ), ' epif-stat--green' );
		}
		$items[] = array( self::setting( 'pickup_location' ), __( 'pickup across the Baltimore area & nationwide shipping', 'epif' ), '' );

		$out = '<div class="epif epif-stats">';
		foreach ( $items as $item ) {
			$out .= sprintf( '<div class="epif-stat%s"><b>%s</b><span>%s</span></div>', esc_attr( $item[2] ), esc_html( $item[0] ), esc_html( $item[1] ) );
		}
		return $out . '</div>';
	}

	public static function render_auction_summary() {
		$stats = self::stats();
		$out   = '<div class="epif epif-sectionhead__aside">';
		if ( $stats['live_auctions'] ) {
			/* translators: %s: number of auctions */
			$out .= '<span class="epif-tag epif-tag--live">' . esc_html( sprintf( _n( '%s live auction', '%s live auctions', $stats['live_auctions'], 'epif' ), number_format_i18n( $stats['live_auctions'] ) ) ) . '</span>';
			$out .= '<a class="epif-link" href="' . esc_url( add_query_arg( array( 'listing' => 'auction', 'orderby' => 'ending' ), self::shop_url() ) ) . '">' . esc_html__( 'See every auction →', 'epif' ) . '</a>';
		} else {
			$out .= '<a class="epif-link" href="' . esc_url( self::shop_url() ) . '">' . esc_html__( 'Shop everything →', 'epif' ) . '</a>';
		}
		return $out . '</div>';
	}

	/* ---------------------------------------------------------------------
	 * Browse page blocks
	 * ------------------------------------------------------------------- */

	private static function breadcrumb() {
		$crumbs = array( array( __( 'Home', 'epif' ), home_url( '/' ) ) );
		$shop   = array( __( 'Shop', 'epif' ), self::shop_url() );

		if ( is_singular( 'product' ) ) {
			$crumbs[] = $shop;
			$terms    = wc_get_product_terms( get_the_ID(), 'product_cat', array( 'orderby' => 'parent', 'order' => 'DESC' ) );
			if ( $terms ) {
				$term = $terms[0];
				foreach ( array_reverse( get_ancestors( $term->term_id, 'product_cat', 'taxonomy' ) ) as $ancestor ) {
					$a        = get_term( $ancestor, 'product_cat' );
					$crumbs[] = array( $a->name, get_term_link( $a ) );
				}
				$crumbs[] = array( $term->name, get_term_link( $term ) );
			}
			$crumbs[] = array( get_the_title(), '' );
		} elseif ( is_search() ) {
			$crumbs[] = $shop;
			$crumbs[] = array( __( 'Search', 'epif' ), '' );
		} elseif ( is_tax() ) {
			$term     = get_queried_object();
			$crumbs[] = $shop;
			if ( $term instanceof WP_Term ) {
				foreach ( array_reverse( get_ancestors( $term->term_id, $term->taxonomy, 'taxonomy' ) ) as $ancestor ) {
					$a        = get_term( $ancestor, $term->taxonomy );
					$crumbs[] = array( $a->name, get_term_link( $a ) );
				}
				$crumbs[] = array( $term->name, '' );
			}
		} else {
			$crumbs[] = array( $shop[0], '' );
		}

		$out = '<nav class="epif-breadcrumb" aria-label="' . esc_attr__( 'Breadcrumb', 'epif' ) . '"><ol>';
		foreach ( $crumbs as $crumb ) {
			$link = is_wp_error( $crumb[1] ) ? '' : $crumb[1];
			$out .= '<li>' . ( $link ? '<a href="' . esc_url( $link ) . '">' . esc_html( $crumb[0] ) . '</a>' : '<span aria-current="page">' . esc_html( $crumb[0] ) . '</span>' ) . '</li>';
		}
		return $out . '</ol></nav>';
	}

	private static function browse_title() {
		$f = self::filters();
		if ( is_search() ) {
			/* translators: %s: search terms */
			return sprintf( __( 'Results for “%s”', 'epif' ), get_search_query( false ) );
		}
		if ( is_tax() ) {
			return single_term_title( '', false );
		}
		if ( 'auction' === $f['listing'] ) {
			return __( 'Live auctions', 'epif' );
		}
		if ( 'buy' === $f['listing'] ) {
			return __( 'Buy it now', 'epif' );
		}
		$shop_id = function_exists( 'wc_get_page_id' ) ? wc_get_page_id( 'shop' ) : 0;
		return $shop_id > 0 ? get_the_title( $shop_id ) : __( 'Shop', 'epif' );
	}

	public static function render_shop_header() {
		$f    = self::filters();
		$term = get_queried_object();
		$lede = $term instanceof WP_Term && $term->description
			? wp_strip_all_tags( $term->description )
			: sprintf(
				/* translators: %s: pickup location */
				__( 'Every item came through our %s warehouse, was checked by a person, and is photographed and described honestly — flaws included.', 'epif' ),
				self::setting( 'pickup_location' )
			);

		$tabs = array(
			''        => __( 'Everything', 'epif' ),
			'auction' => __( 'Live auctions', 'epif' ),
			'buy'     => __( 'Buy it now', 'epif' ),
		);

		ob_start();
		?>
		<div class="epif epif-shophead">
			<?php echo self::breadcrumb(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<h1 class="epif-shophead__title"><?php echo esc_html( self::browse_title() ); ?></h1>
			<p class="epif-lede"><?php echo esc_html( $lede ); ?></p>

			<form class="epif-searchform" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
				<label class="screen-reader-text" for="epif-shop-search"><?php esc_html_e( 'Search items', 'epif' ); ?></label>
				<input id="epif-shop-search" type="search" name="s" value="<?php echo esc_attr( $f['s'] ); ?>" placeholder="<?php esc_attr_e( 'Search recovered parts, clothing, tools, home goods…', 'epif' ); ?>">
				<input type="hidden" name="post_type" value="product">
				<button type="submit"><?php esc_html_e( 'Search', 'epif' ); ?></button>
			</form>

			<nav class="epif-tabs" aria-label="<?php esc_attr_e( 'Listing type', 'epif' ); ?>">
				<?php
				foreach ( $tabs as $key => $label ) :
					$on   = $f['listing'] === $key;
					$args = array(
						'listing' => $key,
						'orderby' => 'auction' === $key ? 'ending' : ( 'ending' === $f['orderby'] ? null : $f['orderby'] ),
					);
					?>
					<a class="epif-tabs__tab<?php echo $on ? ' is-active' : ''; ?><?php echo 'auction' === $key ? ' epif-tabs__tab--auction' : ''; ?>" href="<?php echo esc_url( self::browse_url( $args ) ); ?>"<?php echo $on ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $label ); ?></a>
				<?php endforeach; ?>
			</nav>

			<?php echo self::render_category_chips(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</div>
		<?php
		return ob_get_clean();
	}

	public static function render_shop_filters() {
		$f        = self::filters();
		$currency = function_exists( 'get_woocommerce_currency_symbol' ) ? get_woocommerce_currency_symbol() : '$';
		$count    = count( $f['condition'] ) + ( $f['fulfillment'] ? 1 : 0 ) + ( $f['instock'] ? 1 : 0 ) + ( '' !== $f['min_price'] || '' !== $f['max_price'] ? 1 : 0 );

		ob_start();
		?>
		<details class="epif epif-filters" data-epif-filters open>
			<summary class="epif-filters__toggle">
				<?php esc_html_e( 'Filters', 'epif' ); ?>
				<?php if ( $count ) : ?>
					<span class="epif-filters__count"><?php echo esc_html( number_format_i18n( $count ) ); ?></span>
				<?php endif; ?>
			</summary>
			<form class="epif-filters__form" method="get" action="<?php echo esc_url( self::browse_base_url() ); ?>">
				<?php if ( is_search() ) : ?>
					<input type="hidden" name="s" value="<?php echo esc_attr( $f['s'] ); ?>">
					<input type="hidden" name="post_type" value="product">
				<?php endif; ?>
				<?php if ( $f['listing'] ) : ?>
					<input type="hidden" name="listing" value="<?php echo esc_attr( $f['listing'] ); ?>">
				<?php endif; ?>
				<?php if ( $f['orderby'] ) : ?>
					<input type="hidden" name="orderby" value="<?php echo esc_attr( $f['orderby'] ); ?>">
				<?php endif; ?>

				<fieldset class="epif-filters__group">
					<legend><?php esc_html_e( 'Price', 'epif' ); ?></legend>
					<div class="epif-filters__price">
						<label>
							<span class="screen-reader-text"><?php esc_html_e( 'Minimum price', 'epif' ); ?></span>
							<span class="epif-filters__currency" aria-hidden="true"><?php echo esc_html( html_entity_decode( $currency ) ); ?></span>
							<input type="number" name="min_price" min="0" step="1" inputmode="numeric" placeholder="<?php esc_attr_e( 'Min', 'epif' ); ?>" value="<?php echo esc_attr( $f['min_price'] ); ?>">
						</label>
						<span aria-hidden="true">–</span>
						<label>
							<span class="screen-reader-text"><?php esc_html_e( 'Maximum price', 'epif' ); ?></span>
							<span class="epif-filters__currency" aria-hidden="true"><?php echo esc_html( html_entity_decode( $currency ) ); ?></span>
							<input type="number" name="max_price" min="0" step="1" inputmode="numeric" placeholder="<?php esc_attr_e( 'Max', 'epif' ); ?>" value="<?php echo esc_attr( $f['max_price'] ); ?>">
						</label>
					</div>
				</fieldset>

				<fieldset class="epif-filters__group">
					<legend><?php esc_html_e( 'Condition', 'epif' ); ?></legend>
					<?php foreach ( EPIF_Listing::conditions() as $slug => $label ) : ?>
						<label class="epif-check">
							<input type="checkbox" name="condition[]" value="<?php echo esc_attr( $slug ); ?>" <?php checked( in_array( $slug, $f['condition'], true ) ); ?>>
							<span><?php echo esc_html( $label ); ?></span>
						</label>
					<?php endforeach; ?>
				</fieldset>

				<fieldset class="epif-filters__group">
					<legend><?php esc_html_e( 'Getting it', 'epif' ); ?></legend>
					<?php
					$options = array(
						''       => __( 'Any', 'epif' ),
						'ship'   => __( 'Can be shipped', 'epif' ),
						/* translators: %s: pickup location */
						'pickup' => sprintf( __( 'Pickup in %s', 'epif' ), self::setting( 'pickup_location' ) ),
					);
					foreach ( $options as $slug => $label ) :
						?>
						<label class="epif-check">
							<input type="radio" name="fulfillment" value="<?php echo esc_attr( $slug ); ?>" <?php checked( $f['fulfillment'], $slug ); ?>>
							<span><?php echo esc_html( $label ); ?></span>
						</label>
					<?php endforeach; ?>
				</fieldset>

				<fieldset class="epif-filters__group">
					<legend><?php esc_html_e( 'Availability', 'epif' ); ?></legend>
					<label class="epif-check">
						<input type="checkbox" name="instock" value="1" <?php checked( $f['instock'] ); ?>>
						<span><?php esc_html_e( 'Hide sold items', 'epif' ); ?></span>
					</label>
				</fieldset>

				<div class="epif-filters__actions">
					<button type="submit" class="epif-btn epif-btn--primary"><?php esc_html_e( 'Show results', 'epif' ); ?></button>
					<?php if ( $count ) : ?>
						<a class="epif-link" href="<?php echo esc_url( self::browse_url( array( 'condition' => null, 'fulfillment' => null, 'instock' => null, 'min_price' => null, 'max_price' => null ) ) ); ?>"><?php esc_html_e( 'Clear filters', 'epif' ); ?></a>
					<?php endif; ?>
				</div>
			</form>
		</details>
		<script>
		( function () {
			var d = document.currentScript && document.currentScript.previousElementSibling;
			if ( d && d.matches( '[data-epif-filters]' ) && window.matchMedia( '(max-width: 900px)' ).matches ) {
				d.removeAttribute( 'open' );
			}
		}() );
		</script>
		<?php
		return ob_get_clean();
	}

	public static function render_shop_toolbar() {
		global $wp_query;
		$f     = self::filters();
		$total = (int) $wp_query->found_posts;
		$per   = max( 1, (int) $wp_query->get( 'posts_per_page' ) );
		$paged = max( 1, (int) get_query_var( 'paged' ) );
		$first = ( $paged - 1 ) * $per + 1;
		$last  = min( $total, $paged * $per );

		if ( ! $total ) {
			$count = '';
		} elseif ( $total <= $per ) {
			/* translators: %s: number of items */
			$count = sprintf( _n( '%s item', '%s items', $total, 'epif' ), number_format_i18n( $total ) );
		} else {
			/* translators: 1: first item, 2: last item, 3: total items */
			$count = sprintf( __( '%1$s–%2$s of %3$s items', 'epif' ), number_format_i18n( $first ), number_format_i18n( $last ), number_format_i18n( $total ) );
		}

		$sorts = array(
			'menu_order' => __( 'Featured', 'epif' ),
			'date'       => __( 'Newest first', 'epif' ),
			'price'      => __( 'Price: low to high', 'epif' ),
			'price-desc' => __( 'Price: high to low', 'epif' ),
		);
		if ( 'auction' === $f['listing'] ) {
			$sorts = array( 'ending' => __( 'Ending soonest', 'epif' ) ) + $sorts;
		}
		$current = $f['orderby'] ? $f['orderby'] : get_option( 'woocommerce_default_catalog_orderby', 'menu_order' );

		// Active filter pills, each with its own remove link.
		$pills = array();
		foreach ( $f['condition'] as $slug ) {
			$rest    = array_values( array_diff( $f['condition'], array( $slug ) ) );
			$pills[] = array( EPIF_Listing::conditions()[ $slug ], self::browse_url( array( 'condition' => implode( ',', $rest ) ) ) );
		}
		if ( $f['fulfillment'] ) {
			$pills[] = array( 'ship' === $f['fulfillment'] ? __( 'Can be shipped', 'epif' ) : __( 'Pickup', 'epif' ), self::browse_url( array( 'fulfillment' => null ) ) );
		}
		if ( $f['instock'] ) {
			$pills[] = array( __( 'Hide sold', 'epif' ), self::browse_url( array( 'instock' => null ) ) );
		}
		if ( '' !== $f['min_price'] || '' !== $f['max_price'] ) {
			$price   = function_exists( 'wc_price' ) ? 'wc_price' : static function ( $v ) {
				return '$' . $v;
			};
			$label   = '' !== $f['min_price'] && '' !== $f['max_price']
				? wp_strip_all_tags( $price( $f['min_price'] ) ) . '–' . wp_strip_all_tags( $price( $f['max_price'] ) )
				: ( '' !== $f['min_price']
					/* translators: %s: price */
					? sprintf( __( 'From %s', 'epif' ), wp_strip_all_tags( $price( $f['min_price'] ) ) )
					/* translators: %s: price */
					: sprintf( __( 'Up to %s', 'epif' ), wp_strip_all_tags( $price( $f['max_price'] ) ) ) );
			$pills[] = array( $label, self::browse_url( array( 'min_price' => null, 'max_price' => null ) ) );
		}

		ob_start();
		?>
		<div class="epif epif-toolbar">
			<p class="epif-toolbar__count" role="status"><?php echo esc_html( $count ); ?></p>
			<?php if ( $pills ) : ?>
				<ul class="epif-toolbar__pills" aria-label="<?php esc_attr_e( 'Active filters', 'epif' ); ?>">
					<?php foreach ( $pills as $pill ) : ?>
						<?php /* translators: %s: filter name */ ?>
						<li><a class="epif-tag epif-tag--removable" href="<?php echo esc_url( $pill[1] ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Remove filter: %s', 'epif' ), $pill[0] ) ); ?>"><?php echo esc_html( $pill[0] ); ?> <span aria-hidden="true">×</span></a></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
			<form class="epif-toolbar__sort" method="get" action="<?php echo esc_url( self::browse_base_url() ); ?>">
				<?php
				foreach ( array( 'listing', 'fulfillment', 'min_price', 'max_price' ) as $key ) {
					if ( '' !== $f[ $key ] ) {
						printf( '<input type="hidden" name="%s" value="%s">', esc_attr( $key ), esc_attr( $f[ $key ] ) );
					}
				}
				if ( $f['condition'] ) {
					printf( '<input type="hidden" name="condition" value="%s">', esc_attr( implode( ',', $f['condition'] ) ) );
				}
				if ( $f['instock'] ) {
					echo '<input type="hidden" name="instock" value="1">';
				}
				if ( is_search() ) {
					printf( '<input type="hidden" name="s" value="%s"><input type="hidden" name="post_type" value="product">', esc_attr( $f['s'] ) );
				}
				?>
				<label for="epif-sort"><?php esc_html_e( 'Sort', 'epif' ); ?></label>
				<select id="epif-sort" name="orderby" onchange="this.form.submit()">
					<?php foreach ( $sorts as $value => $label ) : ?>
						<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $current, $value ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
				<noscript><button type="submit" class="epif-btn epif-btn--ghost"><?php esc_html_e( 'Sort', 'epif' ); ?></button></noscript>
			</form>
		</div>
		<?php
		return ob_get_clean();
	}

	public static function render_product_loop() {
		global $wp_query;

		if ( ! self::wc() ) {
			return self::empty_state( __( 'The shop isn\'t open yet', 'epif' ), __( 'Items will appear here once WooCommerce is active and products are published.', 'epif' ) );
		}

		$cards = array();
		if ( $wp_query instanceof WP_Query ) {
			foreach ( $wp_query->posts as $post ) {
				$product = wc_get_product( $post );
				if ( $product && $product->is_visible() ) {
					$cards[] = self::card( $product );
				}
			}
		}

		if ( ! $cards ) {
			$f        = self::filters();
			$filtered = $f['condition'] || $f['fulfillment'] || $f['instock'] || '' !== $f['min_price'] || '' !== $f['max_price'] || $f['listing'];
			if ( $filtered ) {
				return '<div class="epif">' . self::empty_state( __( 'Nothing matches those filters', 'epif' ), __( 'Try a wider price range or fewer filters — new items go up every week.', 'epif' ), self::browse_base_url(), __( 'Clear all filters', 'epif' ) ) . '</div>';
			}
			if ( is_search() ) {
				return '<div class="epif">' . self::empty_state( __( 'No items match your search', 'epif' ), __( 'Check the spelling or try a broader word, like "drill" instead of the model number.', 'epif' ), self::shop_url(), __( 'Browse everything', 'epif' ) ) . '</div>';
			}
			return '<div class="epif">' . self::empty_state( __( 'New items are on the way', 'epif' ), __( 'We\'re photographing and listing the latest clear-outs. Check back soon.', 'epif' ) ) . '</div>';
		}

		$pagination = paginate_links(
			array(
				'total'     => (int) $wp_query->max_num_pages,
				'current'   => max( 1, (int) get_query_var( 'paged' ) ),
				'type'      => 'list',
				'prev_text' => '<span aria-hidden="true">←</span><span class="screen-reader-text">' . esc_html__( 'Previous page', 'epif' ) . '</span>',
				'next_text' => '<span aria-hidden="true">→</span><span class="screen-reader-text">' . esc_html__( 'Next page', 'epif' ) . '</span>',
			)
		);

		$out = '<div class="epif">' . self::grid( $cards, 3 );
		if ( $pagination ) {
			$out .= '<nav class="epif-pagination" aria-label="' . esc_attr__( 'Pages', 'epif' ) . '">' . $pagination . '</nav>';
		}
		return $out . '</div>';
	}

	/* ---------------------------------------------------------------------
	 * Product page, header cart, booking form
	 * ------------------------------------------------------------------- */

	public static function render_single_product() {
		if ( ! self::wc() || ! is_singular( 'product' ) ) {
			return '';
		}

		ob_start();
		echo '<div class="epif epif-product">';
		echo self::breadcrumb(); // phpcs:ignore WordPress.Security.EscapeOutput
		echo '<div class="woocommerce">';
		// Block templates already run the_post() for singular views, so render
		// the queried product directly rather than looping again.
		$GLOBALS['post'] = get_post( get_queried_object_id() ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride
		setup_postdata( $GLOBALS['post'] );
		wc_get_template_part( 'content', 'single-product' );
		echo '</div></div>';
		return ob_get_clean();
	}

	public static function render_header_cart() {
		if ( ! self::wc() || ! function_exists( 'WC' ) ) {
			return '';
		}
		$count = WC()->cart ? (int) WC()->cart->get_cart_contents_count() : 0;
		return sprintf(
			'<a class="epif epif-cartlink" href="%1$s"><span>%2$s</span> <span class="epif-cartlink__count" data-epif-cart-count>%3$s</span><span class="screen-reader-text"> %4$s</span></a>',
			esc_url( self::cart_url() ),
			esc_html__( 'Cart', 'epif' ),
			esc_html( number_format_i18n( $count ) ),
			esc_html( _n( 'item', 'items', $count, 'epif' ) )
		);
	}

	/**
	 * Keeps the header cart count current after an AJAX add to cart.
	 */
	public static function cart_fragment( $fragments ) {
		$fragments['a.epif-cartlink'] = self::render_header_cart();
		return $fragments;
	}

	public static function render_booking_form() {
		if ( ! shortcode_exists( 'epif_lead_form' ) ) {
			return '';
		}
		$services = array(
			__( 'Clear-out pickup', 'epif' ),
			__( 'List my inventory', 'epif' ),
			__( 'Source inventory', 'epif' ),
			__( 'Fulfillment partner', 'epif' ),
		);
		$form = do_shortcode( '[epif_lead_form services="' . esc_attr( implode( '|', $services ) ) . '" button="' . esc_attr__( 'Request a free pickup', 'epif' ) . '"]' );

		// "Ask about this item" on an auction links here with ?item=…; start the message for them.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- prefill only.
		$item = isset( $_GET['item'] ) ? sanitize_text_field( wp_unslash( $_GET['item'] ) ) : '';
		if ( $item ) {
			/* translators: %s: item name */
			$text = esc_textarea( sprintf( __( 'I\'d like to bid on: %s. My bid: ', 'epif' ), mb_substr( $item, 0, 200 ) ) );
			$form = preg_replace( '#(<textarea[^>]*name="message"[^>]*>)(</textarea>)#', '${1}' . $text . '${2}', $form, 1 );
		}

		return '<div class="epif epif-booking">' . $form . '</div>';
	}
}
