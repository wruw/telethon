<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class WC_Phone_Orders_Main {

	public static $slug = 'phone-orders-for-woocommerce';
	/** @var WC_Phone_Orders_Admin_Abstract_Page[] */
	private $tabs;

	/**
	 * WC_Phone_Orders_Main constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_menu' ) );

		WC_Phone_Orders_Loader::load_core();
		$this->init_tabs_and_helper();

		add_action( 'admin_enqueue_scripts', function ( $hook ) {
                    if ( 'woocommerce_page_phone-orders-for-woocommerce' === $hook ) {
			self::load_scripts();
                    }
		} );

		add_action( 'wp_ajax_' . self::$slug, array( $this, 'ajax_gate' ) );

		$settings = WC_Phone_Orders_Settings::getInstance();
		// enable cache?
		$types = array('customers','products','orders','coupons');
		foreach($types as $type) {
			if( $settings->get_option( 'cache_' . $type . '_timeout' ) )  {
				if( isset($_GET['wpo_cache_' . $type . '_key']) AND $_GET['wpo_cache_' . $type . '_key']!='no-cache' )
					$this->set_ajax_cache( 'cache_' . $type . '_timeout' );
			}
		}
		//cache for references
		$type = 'references';
		if( !empty($_GET['method']) AND in_array($_GET['method'], array( "get_countries_and_states_list", "get_products_categories_list","get_products_tags_list") )
			AND $settings->get_option( 'cache_'.$type.'_timeout' )
			) {
				if( isset($_GET['wpo_cache_' . $type . '_key']) AND $_GET['wpo_cache_' . $type . '_key']!='no-cache' )
					$this->set_ajax_cache( 'cache_' . $type . '_timeout' );
		}

		// tweak customer search for our tab only
		if ( isset( $_GET['wpo_find_customer'] ) AND !WC_Phone_Orders_Loader::is_pro_version() ) {
			add_filter( "woocommerce_json_search_found_customers", array( $this, 'reformat_customers_search_results' ) );
		}

		// exclude none admin customers
		if ( isset( $_GET['wpo_find_customer'] ) && ! is_super_admin() ) {

		    if ( isset( $_GET['term'] ) && is_numeric( $_GET['term'] ) ) {

			$users = (new WP_User_Query(array('include' => array($_GET['term']), 'role' => 'Administrator')))->get_results();

			if ($users) {
			    $_GET['exclude'] = array($users[0]->ID);
			}
		    }

		    add_filter( "woocommerce_customer_search_customers", function ( $filter, $term, $limit, $type ) {

			$filter['role__not_in'] = array('Administrator');

			return $filter;
		    }, 10, 4 );
		}

		do_action('wc_phone_orders_construct_end', $settings);
	}

	public static function load_scripts() {

	    $script_handle = self::$slug . '-app';

	    wp_enqueue_script(
		$script_handle,
		plugin_dir_url( __DIR__ ) . 'assets/js/build-app.js',
		array(),
		WC_PHONE_ORDERS_VERSION,
		true // Load JS in footer so that templates in DOM can be referenced.
	    );

	    $data = array(
		'nonce'                  => wp_create_nonce( self::$slug ),
		'edd_wpo_nonce'          => wp_create_nonce( 'edd_wpo_nonce' ),
		'search_customers_nonce' => wp_create_nonce( 'search-customers' ),
		'ajax_url'               => admin_url( 'admin-ajax.php' ),
		'base_cart_url'          => get_home_url( null, 'cart' ),
		'base_admin_url'         => admin_url(),
	    );

	    wp_localize_script( $script_handle, 'PhoneOrdersData', $data );

	    wp_enqueue_style( WC_Phone_Orders_Main::$slug.'-main-css', WC_PHONE_ORDERS_PLUGIN_URL . 'assets/css/bundle.css', array(), WC_PHONE_ORDERS_VERSION );
	}

	private function init_tabs_and_helper() {
		include_once WC_PHONE_ORDERS_PLUGIN_PATH . 'classes/tabs/class-wc-phone-orders-tabs-helper.php';
		$this->tabs = WC_Phone_Orders_Tabs_Helper::get_tabs();
	}

	public function add_menu() {

		//detect active capability
		$capability = WC_Phone_Orders_Loader::check_user_capability();

		//can do it ?
		if ( $capability ) {
			add_submenu_page(
				'woocommerce',
				__( 'Phone Orders', 'phone-orders-for-woocommerce' ),
				__( 'Phone Orders', 'phone-orders-for-woocommerce' ),
				$capability,
				self::$slug,
				array( $this, 'render_menu' )
			);
		}
	}

	public function render_menu() {
		$tabs     = $this->tabs;
		$settings = WC_Phone_Orders_Settings::getInstance()->get_all_options();
	    ?>

                <div class="wrap woocommerce">
                    <div class="wpo_settings ui-page-theme-a">
                        <div class="wpo_settings_container">
                            <div id="phone-orders-app" data-all-settings="<?php echo esc_attr( json_encode($settings) ) ?>">
				<?php if (count($tabs) > 1): ?>
				    <b-tabs card ref="tabs">
					<?php foreach ( $tabs as $tab_key => $tab_handler ): ?>
					    <b-tab
						title="<?php echo $tab_handler->title; ?>"
						href="#<?php echo $tab_key; ?>"
						:active="'#<?php echo $tab_key; ?>' === getWindowLocationHash()"
					    >
						<?php $tab_handler->render();?>
					    </b-tab>
					<?php endforeach; ?>
				    </b-tabs>
				<?php else: ?>
				    <?php
					$tab_handler = array_shift($tabs);
					$tab_handler && $tab_handler->render();
				    ?>
				<?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

            <?php
	}

	public function ajax_gate() {
		$request = $_REQUEST;

		$method   = isset( $request['method'] ) ? "ajax_{$request['method']}" : '';
		$tab_name = isset( $request['tab'] ) ? $request['tab'] : '';

		if ( $method AND $tab_name ) {
			$this->tabs[ $tab_name ]->ajax( $method, $request );
		}

		die;
	}

	private function set_ajax_cache( $cache_type ) {
		$hours = WC_Phone_Orders_Settings::getInstance()->get_option( $cache_type );
		$seconds_to_cache = $hours * 3600;
		add_filter("nocache_headers" , function ($headers) use ($seconds_to_cache){
			$headers['Expires'] = gmdate("D, d M Y H:i:s", time() + $seconds_to_cache) . " GMT";
			$headers['Pragma'] = "cache";
			$headers['Cache-Control'] = "max-age=" . $seconds_to_cache;
			return $headers;
		});
	}

	public function reformat_customers_search_results( $found_customers ) {
        $result = array();
		//convert
		foreach ( $found_customers as $id => $title ) {
			$result[ $title ] = array(
				'id'    => $id,
				'type'  => 'customer',
				'title' => $title,
			);
		}
		return array_values($result);
	}
}
