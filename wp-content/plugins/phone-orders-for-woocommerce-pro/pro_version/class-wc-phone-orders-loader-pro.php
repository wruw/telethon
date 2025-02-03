<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WC_Phone_Orders_Loader_Pro {
    private $list_statuses_available_edit_order = array();

    protected $tabs;

    public function __construct() {
		include_once 'classes/class-wc-phone-orders-switch-user.php';
		add_action('woocommerce_loaded', function() {
			include_once 'classes/class-wc-phone-orders-session-handler.php';
		});

		// EDD
		include 'classes/updater/class-wc-phone-orders-updater.php';
		include 'classes/updater/class-wc-phone-orders-edd.php';

	    include_once 'classes/class-wc-phone-orders-report-register.php';
	    WC_Report_Phone_Order_Report_Register::init();

		// Settings
		add_action( 'wpo_include_core_classes', array( $this, 'wpo_include_core_classes' ) );

		// load Pro tabs
		add_filter( 'wpo_admin_tabs', array( $this, 'wpo_admin_tabs' ) );

		if ( is_admin() ) {

			//for shipping method
			add_action( 'woocommerce_shipping_init', array( __CLASS__, 'woocommerce_shipping_init' ) );
			add_filter( 'woocommerce_shipping_methods', array( __CLASS__, 'woocommerce_shipping_methods' ) );

			// pro actions on admin_init
			add_action( "wc_phone_orders_construct_end", array( $this, 'wc_phone_orders_construct_end' ) );
		}

		$this->define_constants();

		add_action( 'wpo_cart_updated', array( $this, 'store_cart_in_session' ) );
		add_action( 'wpo_cart_updated', array( $this, 'store_customer_in_session' ) );

		add_filter( 'wpo_get_shipping_methods', array( $this, 'get_shipping_methods' ) );

		add_action( 'wp_loaded', function () {
			// these hooks for admin pages only !
			if ( !is_admin() )
				return ;

			$settings = WC_Phone_Orders_Settings::getInstance();
			if ( $settings->get_option( 'override_customer_payment_link_in_order_page' ) ) {
				add_filter( 'woocommerce_get_checkout_payment_url', function ( $pay_url, $order ) {
					/** @var WC_Order $order */
					global $pagenow, $post_type;
					if ( $pagenow == 'post.php' AND $post_type == "shop_order") {
						$pay_url = add_query_arg( array(
							"pay_as_customer" => "true",
							'order_id'        => $order->get_id(),
						), admin_url() );
					}

					return $pay_url;
				}, 10, 2 );

				if ( ! empty( $_REQUEST['pay_as_customer'] ) && "true" === $_REQUEST['pay_as_customer'] && ! empty( $_REQUEST['order_id'] ) && current_user_can( 'manage_woocommerce' ) ) {
					$order = wc_get_order( $_REQUEST['order_id'] );
					if ( ! $order ) {
						return;
					}
					WC_Phone_Orders_Add_Order_Page_Pro::set_payment_cookie( $order->get_customer_id() );
					wp_redirect( $order->get_checkout_payment_url() );
					exit();
				}
			}

			if ( $settings->get_option( 'show_edit_order_in_wc' ) ) {
				$this->list_statuses_available_edit_order = apply_filters( 'wpo_list_statuses_available_edit_order', array( 'pending', 'on-hold' ) );

				// add icons only in orders list
				add_action('current_screen', function(){
					$screen_id = false;
					if ( function_exists( 'get_current_screen' ) ) {
						$screen    = get_current_screen();
						$screen_id = isset( $screen, $screen->id ) ? $screen->id : '';
					}
					if ( ! empty( $_REQUEST['screen'] ) ) { // WPCS: input var ok.
						$screen_id = wc_clean( wp_unslash( $_REQUEST['screen'] ) ); // WPCS: input var ok, sanitization ok.
					}
					if ( 'edit-shop_order' == $screen_id ) {
						add_action( 'wp_print_scripts', array( $this, 'add_icons_for_order_action' ) );
					}
				});

				// print button with edit order url to plugin page
				add_filter( 'woocommerce_admin_order_actions_end', function ( $object ) {
					if ( $object->has_status( $this->list_statuses_available_edit_order ) ) {
						$action = array(
							'action' => 'edit_in_wpo',
							'url'    => add_query_arg( array(
								'page'          => WC_Phone_Orders_Main::$slug,
								'edit_order_id' => $object->get_id(),
							), admin_url( 'admin.php' ) ),
							'name'   => __( 'Edit in Phone Orders', 'woocommerce' ),
						);


						echo sprintf( '<a class="button wc-action-button wc-action-button-%1$s %1$s" href="%2$s" aria-label="%3$s" title="%3$s" target="_blank">%4$s</a>',
							esc_attr( $action['action'] ),
							esc_url( $action['url'] ),
							esc_attr( isset( $action['title'] ) ? $action['title'] : $action['name'] ),
							esc_html( $action['name'] )
						);
					}
				}, 10, 2 );
			}
		} );

		add_action('parse_request', array($this, 'init_frontend_page'));
	}

	public function init_frontend_page() {

	    global $wp;

	    $current_url = home_url(add_query_arg(array(), $wp->request));

	    $this->wpo_include_core_classes();

	    $settings_option_handler = WC_Phone_Orders_Settings::getInstance();

	    $clean_current_url = remove_query_arg(array('edit_order_id'), $current_url);

	    if ( ! $settings_option_handler->get_option('frontend_page')
		|| trim($settings_option_handler->get_option('frontend_page_url'), '/') !== trim($clean_current_url, '/')
	    ) {
		return;
	    }

	    if ( ! is_user_logged_in() ) {
		wp_safe_redirect( wp_login_url( $current_url ) );
		exit();
	    }

	    if ( ! WC_Phone_Orders_Loader::check_user_capability() ) {
		return;
	    }

	    add_action( 'template_redirect', array( 'WC_Phone_Orders_Loader', 'load_main' ) );

	    // Admin Color Schemes
	    add_action( 'template_redirect', 'register_admin_color_schemes', 1);

	    add_action('wc_phone_orders_construct_end', array($this, 'render_frontend_page'));

	    add_action( 'wp_enqueue_scripts', function () {

		//admin styles
		wp_enqueue_style( 'colors' );
		wp_enqueue_style( 'ie' );

		WC_Phone_Orders_Main::load_scripts();
	    });

	    add_filter( 'script_loader_src', array( $this, 'script_loader_src' ), 999, 2 );
	    add_filter( 'style_loader_src', array( $this, 'script_loader_src' ), 999, 2 );
	}

	public function script_loader_src( $src, $handle ) {
	    if (strpos( $src, WC_PHONE_ORDERS_PLUGIN_URL ) === false
		&& !preg_match( '/\/wp-includes\//', $src )
		&& !preg_match( '/\/wp-admin\//', $src )
	    ) {
		return "";
	    }

	    return $src;
	}

	public function render_frontend_page() {

		status_header(200);//suppress 404

		add_filter("document_title_parts", function($title){ // replace "Page Not Found"
			    $title['title'] = __( 'Add order', 'phone-orders-for-woocommerce' );
			    return $title;
		});

		$settings_option_handler = WC_Phone_Orders_Settings::getInstance();

		$hide_header = $settings_option_handler->get_option('frontend_hide_theme_header');
		$hide_footer = $settings_option_handler->get_option('frontend_hide_theme_footer');
	    ?>

	    <!DOCTYPE html>
	    <html>
		<head>
		    <div style="<?php echo $hide_header ? 'display:none;' : ''; ?>">
			<?php
			    do_action( 'wp_head' );
			    do_action( 'admin_print_styles' );
			?>
		    </div>
		</head>
		<body>
		    <?php
			// include get_editable_roles()
			require_once(ABSPATH . 'wp-admin/includes/admin.php');
			$settings = $settings_option_handler->get_all_options();
		    ?>
		    <script>
			window.wpo_frontend = 1;
		    </script>
		    <div class="wrap woocommerce">
			<div class="wpo_settings ui-page-theme-a">
			    <div class="wpo_settings_container">
				<br/>
				<div id="phone-orders-app" data-all-settings="<?php echo esc_attr( json_encode($settings) ) ?>">
				    <?php $this->tabs['add-order']->render(); ?>
				</div>
			    </div>
			</div>
		    </div>
		    <div style="<?php echo $hide_footer ? 'display:none;' : ''; ?>">
			<?php do_action( 'wp_footer' ); ?>
		    </div>
		</body>
	    </html>

            <?php
	    exit;
	}

	/**
	 * @return WC_Customer
	 */
	public function store_customer_in_session(  ) {
		WC_Phone_Orders_Loader_Pro::disable_object_cache();
		$customer = WC()->customer;

		$current_user = get_current_user_id();
		$trans_name       = $current_user . '_temp_customer_id';
		$temp_customer_id = get_transient( $trans_name );

		$temp_session = new WC_Phone_Orders_Session_Handler();
		$temp_session->set_customer_id( $temp_customer_id );
		$temp_session->init();
		if ( ! $temp_customer_id ) {
			set_transient( $trans_name, $temp_session->get_customer_id() );
		}
		$temp_session->set_original_customer( $customer );
		$temp_session->save_data();

		return $customer;
	}

	public function store_cart_in_session() {
		WC_Phone_Orders_Loader_Pro::disable_object_cache();
		$current_user = get_current_user_id();
		$trans_name       = $current_user . '_temp_customer_id';
		$temp_customer_id = get_transient( $trans_name );

		$temp_session = new WC_Phone_Orders_Session_Handler();
		$temp_session->set_customer_id( $temp_customer_id );
		$temp_session->init();

		if ( ! $temp_customer_id ) {
			set_transient( $trans_name, $temp_session->get_customer_id() );
		}

		$cart_keys = array(
			'cart',
			'cart_totals',
			'applied_coupons',
			'coupon_discount_totals',
			'coupon_discount_tax_totals',
			'removed_cart_contents',
			'order_awaiting_payment'
		);

		foreach ( $cart_keys as $key ) {
			$temp_session->set( $key, maybe_unserialize( WC()->session->get( $key ) ) );
		}
		$temp_session->save_data();
	}

	public function wpo_include_core_classes() {
		include_once WC_PHONE_ORDERS_PRO_VERSION_PATH . 'classes/class-wc-phone-orders-settings-pro.php';
	}

	public static function woocommerce_shipping_init() {
		include_once 'classes/class-wc-phone-shipping-method-custom-price.php';
	}

	public static function woocommerce_shipping_methods( $methods ) {
		$methods['phone_orders_custom_price'] = 'WC_Phone_Shipping_Method_Custom_Price';

		return $methods;
	}

	public function wpo_admin_tabs( $tabs ) {
		include_once WC_PHONE_ORDERS_PRO_VERSION_PATH . 'classes/tabs/class-wc-phone-orders-tab-helper-pro.php';

		$this->tabs = WC_Phone_Orders_Tabs_Helper_Pro::init_tabs( $tabs );

		return $this->tabs;
	}

	/**
	 * @param $settings WC_Phone_Orders_Settings
	 */
	public function wc_phone_orders_construct_end( $settings ) {
		// allow search by order fields?
		if ( $settings->get_option( 'search_all_customer_fields' ) ) {
			add_filter( "woocommerce_customer_search_customers", function ( $filter, $term, $limit, $type ) {
				if ( $type != 'meta_query' ) {
					return $filter;
				}

				$fields = array(
					"address_1",
					"address_2",
					"city",
					"company",
					"email",
					"first_name",
					"last_name",
					"phone",
					"postcode",
				);

				$fields = apply_filters('wpo_search_customers_meta_fields', $fields);

				foreach ( $fields as $f ) {
					$filter['meta_query'][] = array( 'key' => 'billing_' . $f, 'value' => $term, 'compare' => 'LIKE' );
				}

				foreach ( $fields as $f ) {
					$filter['meta_query'][] = array( 'key' => 'shipping_' . $f, 'value' => $term, 'compare' => 'LIKE' );
				}

				return $filter;
			}, 10, 4 );
		}

		// limit search ?
		if ( $settings->get_option( 'number_of_customers_to_show' ) ) {
			add_filter( "woocommerce_customer_search_customers", function ( $filter, $term, $limit, $type ) {
				$filter['number'] = WC_Phone_Orders_Settings::getInstance()->get_option( 'number_of_customers_to_show' );

				return $filter;
			}, 10, 4 );
		}

		// tweak customer search for our tab only
		if ( isset( $_GET['wpo_find_customer'] ) ) {
			add_filter( "woocommerce_json_search_found_customers", array( $this, 'do_customers_search_by_orders' ) );
		}
	}

	private function define_constants() {
		define( 'WC_PHONE_ORDERS_PRO_VERSION_PATH', WC_PHONE_ORDERS_PLUGIN_PATH . 'pro_version/' );
		define( 'WC_PHONE_ORDERS_PRO_VERSION_URL', WC_PHONE_ORDERS_PLUGIN_URL . 'pro_version/' );

		// User switcher
		define( 'WC_PHONE_ADMIN_COOKIE', 'wordpress_woocommerce_po_admin_' . COOKIEHASH );
		define( 'WC_PHONE_ADMIN_REFERRER_COOKIE', 'wordpress_woocommerce_po_admin_referrer_' . COOKIEHASH );
		define( 'WC_PHONE_CUSTOMER_COOKIE', 'wordpress_woocommerce_po_customer_' . COOKIEHASH );

		define( 'WC_PHONE_ORDERS_MAIN_URL', WC_Phone_Orders_EDD::wpo_get_main_url() );
		define( 'WC_PHONE_ORDERS_STORE_URL', 'https://algolplus.com/plugins/' );
		define( 'WC_PHONE_ORDERS_ITEM_NAME', 'Phone Orders for WooCommerce (Pro)' );
		define( 'WC_PHONE_ORDERS_AUTHOR', 'AlgolPlus' );
	}

	public function do_customers_search_by_orders( $found_customers ) {

		$result = array();

		//convert
		foreach ( $found_customers as $id => $title ) {
			$result[ $title ] = array(
				'id'    => $id,
				'type'  => 'customer',
				'title' => $title,
			);
		}

                if ( ! WC_Phone_Orders_Settings::getInstance()->get_option( 'search_customer_in_orders' ) ) {
                    return array_values($result);
                }

		$limit   = 0;
		$founded = count( $result );

		$number_of_customers_to_show = WC_Phone_Orders_Settings::getInstance()->get_option( 'number_of_customers_to_show' );

		if ( $number_of_customers_to_show ) {
			$limit = (int) $number_of_customers_to_show - $founded > 0 ? $number_of_customers_to_show - $founded : 0;
		}

		if ( ! $limit ) {
		    return array_values( $result );
		}

		//find ids
		$order_ids = $this->get_woocommerce_json_search_customers_search_by_orders( $_GET['term'], $limit );

		foreach ( $order_ids as $order_id ) {

			$order = wc_get_order( $order_id );

			if ( ! $order ) {
				continue;
			}

			$title = sprintf(
				esc_html__( '%1$s (#%2$s &ndash; %3$s)', 'phone-orders-for-woocommerce' ),
				implode(
					' ',
					array(
						current( array_filter( array(
							$order->get_billing_first_name(),
							$order->get_shipping_first_name(),
						) ) ),
						current( array_filter( array(
							$order->get_billing_last_name(),
							$order->get_shipping_last_name(),
						) ) ),
					)
				),
				$order->get_customer_id(),
				$order->get_billing_email()
			);
			if ( isset( $result[ $title ] ) ) {
				continue;
			}

			$result[ $title ] = array(
				'id'    => $order->get_id(),
				'type'  => 'order',
				'title' => $title,
			);
		}

		//done
		return array_values( $result );
	}


	protected function get_woocommerce_json_search_customers_search_by_orders( $term, $limit ) {
		global $wpdb;

		$search_fields = array_map(
			'wc_clean', apply_filters(
				'woocommerce_shop_order_search_fields', array(
					'_billing_address_index',
					'_shipping_address_index',
					'_billing_last_name',
					'_billing_email',
				)
			)
		);

		$order_ids = array();

		if ( ! empty( $search_fields ) ) {
			$order_ids = $wpdb->get_col(
				$wpdb->prepare(
					"SELECT DISTINCT p1.post_id FROM {$wpdb->postmeta} p1 WHERE p1.meta_value LIKE %s AND p1.meta_key IN ('" . implode( "','",
						array_map( 'esc_sql', $search_fields ) ) . "') ORDER BY p1.post_id DESC LIMIT " . (int)$limit,
					// @codingStandardsIgnoreLine
					'%' . $wpdb->esc_like( wc_clean( $term ) ) . '%'
				)
			);
		}

		return $order_ids;
	}

        public function get_shipping_methods(array $shipping_methods) {

            $shipping_methods[] = 'phone_orders_custom_price';

            return $shipping_methods;
        }

	public function add_icons_for_order_action() {
		?>
        <style>

            .widefat .wc_actions a.wc-action-button-edit_in_wpo::after {
                font-family: 'Woocommerce';
                content: "\e03b";
            }

        </style>
		<?php
	}

	public static function disable_object_cache() {
	    global $_wp_using_ext_object_cache;
		$_wp_using_ext_object_cache = false;
    }

}

new WC_Phone_Orders_Loader_Pro();
