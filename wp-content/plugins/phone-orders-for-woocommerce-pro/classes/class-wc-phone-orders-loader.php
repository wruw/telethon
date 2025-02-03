<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


class WC_Phone_Orders_Loader {

	private static $slug = 'phone-orders-for-woocommerce';
	public static $log_table_name;
	var $activation_notice_option = 'woocommerce-phone-orders-activation-notice-shown';
	public $version = '3.1.0';
	private $wpo_version_option = 'woocommerce_phone_orders_version';

	public static $meta_key_private_note = '_wpo_private_note';
	public static $meta_key_order_creator = '_wpo_order_creator';

	public static $cap_manage_phone_orders = "manage_woocommerce_phone_orders";

	public function __construct() {

		if ( is_admin() ){

                    //for shipping method
                    add_action( 'woocommerce_shipping_init', function ( $methods ) {
                        include_once 'class-wc-phone-shipping-method.php';
                    } );

                    add_filter( 'woocommerce_shipping_methods', function ( $methods ) {
                        $methods['phone_orders'] = 'WC_Phone_Shipping_Method';
                        return $methods;
                    } );

					add_filter( 'user_row_actions', function ( $actions, $user_object ) {
						$actions['new_phone_order'] = "<a  href='" . admin_url( "admin.php?page=phone-orders-for-woocommerce&user_id=" . $user_object->ID ) . "'>" . __( 'Create Order', 'phone-orders-for-woocommerce' ) . "</a>";
						return $actions;
					}, 10, 2 );
		}

                add_action( 'wp_loaded', array( $this, 'check_url' ), 5);

		add_action( 'wp_loaded', array($this, 'show_icon_in_orders_list'), 5);

                add_action( 'plugins_loaded', array( $this, 'init_plugin' ) );

                add_action( 'admin_init', function () {
                    if ( ! get_option( $this->activation_notice_option, false ) ) {
                            add_action( 'admin_notices', array( $this, 'display_plugin_activated_message' ) );
                    }
                    if ( ! defined( 'IFRAME_REQUEST' ) && version_compare( get_option( $this->wpo_version_option ), $this->version, '<' ) ) {
                        self::create_tables();
                        $this->update_wpo_version();
                    }
		} );

		global $wpdb;
		self::$log_table_name = "{$wpdb->prefix}phone_orders_log";
	}

    private function update_wpo_version() {
        delete_option( $this->wpo_version_option );
        add_option( $this->wpo_version_option, $this->version );
    }

	public static function load_core() {
		do_action( 'wpo_include_core_classes' );
	}

	public static function is_pro_version() {
		return defined( 'WC_PHONE_ORDERS_PRO_VERSION_PATH' );
	}

	public function init_plugin() {
		load_plugin_textdomain( self::$slug, false, basename( dirname( dirname( __FILE__ ) ) ) . '/languages/' );
		include_once 'class-wc-phone-orders-settings.php';

		if ( is_admin() ) {
		    self::load_main();
		}
	}

	public static function load_main() {
	    include_once 'class-wc-phone-orders-main.php';
	    new WC_Phone_Orders_Main();
	}

	public function activate() {
		global $wp_roles;
		if($wp_roles) {
			$wp_roles->add_cap( 'shop_manager', self::$cap_manage_phone_orders );
			$wp_roles->add_cap( 'administrator', self::$cap_manage_phone_orders );
		}

		self::create_tables();
	    //self::add_shipping_methods_for_all_zones();
    }

    private static function create_tables() {
	    global $wpdb;
	    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

	    $table_name = self::$log_table_name;

        $charset_collate = $wpdb->get_charset_collate();

	    $sql = "CREATE TABLE $table_name
    (
    ID VARCHAR(20) NOT NULL,
    time_updated DATETIME DEFAULT '0000-00-00 00:00:00' NOT NULL,
    user_id INT,
    user_name VARCHAR(20) DEFAULT '' NOT NULL,
    order_id INT,
    order_number VARCHAR(20) DEFAULT '' NOT NULL,
    customer TEXT,
    customer_id INT,
    items TEXT,
    discount TEXT,
    fees TEXT,
    shipping TEXT,
    total TEXT,
    PRIMARY KEY (ID)
    ) $charset_collate;";

        dbDelta( $sql );

    }

    private static function add_shipping_methods_for_all_zones() {

        $delivery_zones       = WC_Shipping_Zones::get_zones();
        $new_shipping_methods = array(
            'phone_orders',
        );

        $new_shipping_methods = apply_filters('wpo_get_shipping_methods', $new_shipping_methods);

        foreach ((array)$delivery_zones as $the_zone ) {
            $zone             = new WC_Shipping_Zone($the_zone['id']);
            $shipping_methods = array();

            foreach ($zone->get_shipping_methods() as $method) {
                $shipping_methods[] = $method->id;
            }

            foreach ($new_shipping_methods as $method) {
                if (!in_array($method, $shipping_methods)) {
                    $zone->add_shipping_method($method);
                }
            }
        }
    }

    public function deactivate() {
		global $wp_roles;
		if( $wp_roles ) {
			$wp_roles->remove_cap( 'shop_manager', self::$cap_manage_phone_orders );
			$wp_roles->remove_cap( 'administrator', self::$cap_manage_phone_orders );
		}

		delete_option( $this->activation_notice_option );
	}

	public function display_plugin_activated_message() {
		?>
        <div class="notice notice-success is-dismissible">
            <p><?php echo sprintf( __( 'Phone Orders For WooCommerce is available <a href="%s">on this page</a>.',
					'phone-orders-for-woocommerce' ), 'admin.php?page=' . self::$slug ); ?></p>
        </div>
		<?php
		update_option( $this->activation_notice_option, true );
	}

        public function check_url() {

            $key = 'wpo_fill_cart';

            if ( ! isset( $_GET[$key] ) ) {
                return;
            }

            include_once 'class-wc-phone-orders-fill-cart.php';

            WC_Phone_Orders_Fill_Cart::fill_cart($_GET[$key]);

            wp_redirect( remove_query_arg( array($key), get_home_url(null, $_SERVER['REQUEST_URI'])) );
            exit;
        }

	public static function check_user_capability() {

	    //detect active capability
	    $capability = false;

	    if( current_user_can( WC_Phone_Orders_Loader::$cap_manage_phone_orders ) )
		    $capability = WC_Phone_Orders_Loader::$cap_manage_phone_orders;
	    elseif( current_user_can( 'manage_woocommerce' ) )
		    $capability = 'manage_woocommerce';
	    elseif( current_user_can( 'edit_shop_orders' ) )
		    $capability = 'edit_shop_orders';
	    return $capability;
	}

        public function show_icon_in_orders_list() {

	    $settings_option_handler = WC_Phone_Orders_Settings::getInstance();

	    $show_icon_in_orders_list = $settings_option_handler->get_option('show_icon_in_orders_list');

	    if ( ! $show_icon_in_orders_list ) {
		return;
	    }

	    add_action( 'manage_shop_order_posts_custom_column', function ( $column, $post_id ) {

		if ($column === 'order_number' && get_post_meta( $post_id, '_wpo_order_creator', true ) ) {
		    echo '<span title="' . __("Phone order","phone-orders-for-woocommerce") . '" class="wc-orders-list__wpo-order-number-icon">&nbsp;</span>';
		}

	    }, 999, 2 );

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
			add_action( 'wp_print_scripts', function () {

			    ?>
			    <style>

				.wc-orders-list__wpo-order-number-icon {
				    margin-left: 3px;
				    vertical-align: middle;
				}

				.wc-orders-list__wpo-order-number-icon::after {
				    font-family: 'Woocommerce';
				    content: "\e03b";
				}

			    </style>
			    <?php

			} );
		}
	    });
        }
}