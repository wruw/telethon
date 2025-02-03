<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// WC_Phone_Orders_Helper
class WC_Phone_Orders_Settings {

	private static $instance;

	private $meta_key = 'phone-orders-for-woocommerce';

	private $default_settings = array(
		'auto_recalculate'            => false,
		'order_payment_method'        => '',
		'order_status'                => 'wc-pending',
		'show_order_date_time'        => false,
		'google_map_api_key'          => '',

		'show_cart_link'               => false,
		'show_icon_in_orders_list'     => false,
		'show_order_status'            => false,
		'switch_customer_while_calc_cart' => false,
		'manual_coupon_title'          => '_wpo_cart_discount',
		'item_price_precision'         => 2,
		'log_show_records_days'        => 7,
		'cache_customers_session_key'  => 'no-cache',
		'cache_products_session_key'   => 'no-cache',
		'cache_coupons_timeout'        => 1,
		'cache_coupons_session_key'    => 'default',
		'cache_references_timeout'     => 1,
		'cache_references_session_key' => 'default',
		'number_of_products_to_show'   => 25,
		'number_of_customers_to_show'  => 20,

		'item_default_selected'        => array(),

		'item_custom_meta_fields'              => '',
		'default_list_item_custom_meta_fields' => '',
	);
	private $definitions = array(
		'auto_recalculate'            => FILTER_VALIDATE_BOOLEAN,
		'order_payment_method'        => FILTER_SANITIZE_STRING,
		'order_status'                => FILTER_SANITIZE_STRING,
		'show_order_date_time'        => FILTER_VALIDATE_BOOLEAN,
		'google_map_api_key'          => FILTER_SANITIZE_STRING,

		'show_cart_link'               => FILTER_VALIDATE_BOOLEAN,
		'show_icon_in_orders_list'     => FILTER_VALIDATE_BOOLEAN,
		'show_order_status'            => FILTER_VALIDATE_BOOLEAN,
		'switch_customer_while_calc_cart' => FILTER_VALIDATE_BOOLEAN,
		'manual_coupon_title'          => FILTER_SANITIZE_STRING,
		'log_show_records_days'        => FILTER_VALIDATE_INT,
		'cache_customers_session_key'  => FILTER_SANITIZE_STRING,
		'cache_products_session_key'   => FILTER_SANITIZE_STRING,
		'cache_coupons_timeout'        => FILTER_SANITIZE_NUMBER_INT,
		'cache_coupons_session_key'    => FILTER_SANITIZE_STRING,
		'cache_references_timeout'     => FILTER_SANITIZE_NUMBER_INT,
		'cache_references_session_key' => FILTER_SANITIZE_STRING,
		'number_of_products_to_show'   => FILTER_SANITIZE_NUMBER_INT,
		'number_of_customers_to_show'  => FILTER_SANITIZE_NUMBER_INT,

		'item_default_selected' => array(
			'filter'  => FILTER_VALIDATE_INT,
			'flags'   => FILTER_REQUIRE_ARRAY,
			'default' => array(),
		),

		'item_custom_meta_fields'              => FILTER_SANITIZE_STRING,
		'default_list_item_custom_meta_fields' => FILTER_SANITIZE_STRING,
	);

	private $options = array();

	public static function getInstance() {
		if ( empty( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function __construct() {
		$this->get_options();
	}

	public function add_default_settings( $default_settings_new = array() ) {
		$this->default_settings = array_merge( $this->default_settings, $default_settings_new );
		$this->get_options();
		$this->set_options();
	}

	public function add_definitions( $definitions_new = array() ) {
		$this->definitions = array_merge( $this->definitions, $definitions_new );
	}

	public function set_options( $key_value_array = array() ) {
		if ( ! empty( $key_value_array ) ) {
			$options       = filter_var_array( $key_value_array, $this->get_definitions() );
			$options       = array_merge( $this->get_default_settings(), $options );

                        //var_dump($options);die;

			$this->options = array_merge( $this->options, $options );
		} else {
			$this->options = array_merge( $this->get_default_settings(), $this->options );
		}

		update_option( $this->get_metakey(), $this->options );
	}

	public function get_option( $key ) {
		if ( in_array( $key, array_keys( $this->options ) ) ) {
			$value = $this->options[ $key ];
		} elseif ( in_array( $key, array_keys( $this->default_settings ) ) ) {
			$value = $this->default_settings[ $key ];
		} else {
			$value = null;
		}
		return apply_filters( "wpo_get_option", $value, $key );
	}

	public function get_metakey() {
		return $this->meta_key;
	}

	public function get_all_options() {
		return apply_filters( "wpo_get_all_options", $this->options );
	}

	private function get_options() {
		$options = get_option( $this->get_metakey() );
		if ( ! is_array( $options ) ) {
			$options = $this->get_default_settings();
		} else {
			$result = array();
			foreach ( $this->get_default_settings() as $key => $option ) {
				$result[ $key ] = isset( $options[ $key ] ) ? $options[ $key ] : $option;
			}
			$options = $result;
		}
		$this->options = $options;
	}

	private function get_default_settings() {
		return $this->default_settings;
	}


	private function get_definitions() {
		return $this->definitions;
	}

}