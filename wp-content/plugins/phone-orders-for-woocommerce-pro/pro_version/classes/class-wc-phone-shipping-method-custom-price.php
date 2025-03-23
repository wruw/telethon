<?php
// class loaded in ADMIN areay only 

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly


class WC_Phone_Shipping_Method_Custom_Price extends WC_Shipping_Method {
	public function __construct( $instance_id = 0 ) {
		$this->id                 = 'phone_orders_custom_price';
		$this->instance_id        = absint( $instance_id );
		$this->method_title       = __( 'Custom Price [Phone Orders]', 'phone-orders-for-woocommerce' );
		$this->method_description = __( 'Custom Price in admin area only, for Phone Orders ',
			'phone-orders-for-woocommerce' );
		$this->supports           = array(
			'shipping-zones',
			'instance-settings',
			'instance-settings-modal',
		);
		add_filter( 'woocommerce_package_rates', array( $this, 'change_price' ), 10, 2 );
		$this->init();
	}

	function init() {
		// Load the settings API
		$this->init_form_fields();
		$this->init_settings();

		$this->title = $this->get_option( 'title' );
		$this->tax_status = $this->get_option( 'tax_status' );
		$this->cost  = $this->get_option( 'cost' );

		// Actions
		add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
	}

	function init_form_fields() {
		$this->instance_form_fields = array(
			'title' => array(
				'title'       => __( 'Title', 'phone-orders-for-woocommerce' ),
				'type'        => 'text',
				'description' => __( 'Title to be displayed', 'phone-orders-for-woocommerce' ),
				'default'     => __( 'Custom Price [Phone Orders]', 'phone-orders-for-woocommerce' ),
			),
			'tax_status' => array(
				'title' 		=> __( 'Tax status', 'woocommerce' ),
				'type' 			=> 'select',
				'class'         => 'wc-enhanced-select',
				'default' 		=> 'taxable',
				'options'		=> array(
					'taxable' 	=> __( 'Taxable', 'woocommerce' ),
					'none' 		=> _x( 'None', 'Tax status', 'woocommerce' ),
				),
			),
			'cost' => array(
				'title' 		=> __( 'Cost', 'woocommerce' ),
				'type' 			=> 'text',
				'placeholder'	=> '0',
				'description'	=> __( 'Default cost', 'phone-orders-for-woocommerce' ),
				'default'		=> '',
			),
		);
	}

	public function change_price( $rates, $package ) {

		if ( isset($_REQUEST['cart']['shipping']) AND is_array($_REQUEST['cart']['shipping'])) {
			$new_shipping_id   = isset( $_REQUEST['cart']['shipping']['id'] ) ? $_REQUEST['cart']['shipping']['id'] : '';
			$new_shipping_cost = isset( $_REQUEST['cart']['shipping']['cost'] ) ? $_REQUEST['cart']['shipping']['cost'] : '';

			if ( isset( $rates[ $this->id . ":" . $this->instance_id ] ) AND ( $this->id . ":" . $this->instance_id ) == $new_shipping_id ) {
				$rates[ $this->id . ":" . $this->instance_id ]->cost = (float) $new_shipping_cost;
			}
		}

		return $rates;
	}

	public function calculate_shipping( $package = array() ) {
	
		if ( isset( $_REQUEST['cart']['shipping'] ) AND is_array( $_REQUEST['cart']['shipping'] ) ) {
			$new_shipping_id   = isset( $_REQUEST['cart']['shipping']['id'] ) ? $_REQUEST['cart']['shipping']['id'] : '';
			$new_shipping_cost = isset( $_REQUEST['cart']['shipping']['cost'] ) ? $_REQUEST['cart']['shipping']['cost'] : '';

			if ( $this->id . ":" . $this->instance_id == $new_shipping_id ) {
				$this->cost = (float) $new_shipping_cost;
			}
		}
		$this->add_rate( array(
			'label'   => $this->title,
			'package' => $package,
			'cost'    => $this->cost,
		) );
	}
}
 
