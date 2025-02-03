<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


class WC_Phone_Orders_Helper_Pro {

	public function __construct() {
		if ( class_exists( 'WC_Phone_Orders_Settings' ) ) {
			$option_handler = WC_Phone_Orders_Settings::getInstance();
			$option_handler->add_default_settings( $this->get_default_settings() );
			$option_handler->add_definitions( $this->set_definitions() );
		}
	}

	public function get_default_settings() {
		$default_settings_pro = array(

			'hide_tabs'				      => false,

			'frontend_page'				      => false,
			'frontend_page_url'			      => get_home_url(null, 'phone-orders-frontend-page'),
			'frontend_hide_theme_header'		      => false,
			'frontend_hide_theme_footer'		      => false,

			'cache_customers_timeout'                     => 0,
			'cache_customers_session_key'                 => 'no-cache',
			'search_customer_in_orders'                   => false,
			'search_all_customer_fields'                  => false,
			'number_of_customers_to_show'                 => 20,
			'default_customer_id'                         => 0,
			'update_customers_profile_after_create_order' => false,
			'hide_shipping_section'                       => false,
			'do_not_submit_on_enter_last_field'           => false,

			'disable_creating_customers'                  => false,
			'newcustomer_show_password_field'             => false,
			'newcustomer_show_username_field'             => false,
			'newcustomer_show_role_field'                 => false,
			'newcustomer_email_is_optional'               => true,
			'newcustomer_hide_email'                      => false,
			'newcustomer_hide_company'                    => false,
			'newcustomer_hide_address_1'                  => false,
			'newcustomer_hide_address_2'                  => false,
			'newcustomer_hide_city'                       => false,
			'newcustomer_hide_postcode'                   => false,
			'newcustomer_hide_country'                    => false,
			'newcustomer_hide_state'                      => false,
			'default_city'                                => '',
			'default_postcode'                            => '',
			'default_country'                             => '',
			'default_state'                               => '',
			'default_role'                                => 'customer',
			'disable_new_user_email'                      => false,
			'dont_fill_shipping_address_for_new_customer' => false,

			'cache_products_timeout'		              => 0,
			'cache_products_session_key'		          => 'no-cache',
			'search_by_sku'                               => false,
			'search_by_cat_and_tag'                       => false,
			'number_of_products_to_show'                  => 25,
			'autocomplete_product_hide_image'             => false,
			'autocomplete_product_hide_status'            => false,
			'autocomplete_product_hide_qty'               => false,
			'autocomplete_product_hide_price'             => false,
			'autocomplete_product_hide_sku'               => false,
			'autocomplete_product_hide_name'              => false,
			'show_long_attribute_names'                   => true,
			'repeat_search'                               => false,
			'allow_duplicate_products'                    => false,
			'hide_products_with_no_price'                 => true,
			'sale_backorder_product'                      => false,
			'add_product_to_top_of_the_cart'              => false,
			'is_readonly_price'                           => false,
			'item_price_precision'                        => 2,
			'item_default_selected'                       => array(),
			'disable_edit_meta'                           => false,

			'is_featured'                                 => 'no',

			'disable_adding_products'                     => false,
			'new_product_ask_sku'                         => false,
			'product_visibility'                          => 'hidden',
			'new_product_ask_tax_class'                   => false,
			'item_tax_class'                              => '',

			'hide_add_fee'                                => false,
			'default_fee_name'                            => 'Fee',
			'default_fee_amount'                          => 0,
			'fee_tax_class'                               => '',

			'hide_add_discount'                           => false,

			'cache_orders_timeout'		                  => 0,
			'cache_orders_session_key'		              => 'no-cache',
			'copy_only_paid_orders'                       => false,
			'button_for_find_orders'                      => false,
			'set_current_price_in_copied_order'           => true,
			'hide_find_orders'                            => false,

			'show_duplicate_order_button'                 => false,
			'show_edit_order_in_wc'                       => true,
			'hide_button_pay_as_customer'                 => false,
			'hide_button_create_order'                    => false,
			'hide_button_put_on_hold'                     => false,
			'hide_coupon_warning'			      => false,
			'hide_add_shipping'			      => false,

			'customer_custom_fields'                      => "",
			'order_custom_fields'                         => "",

			'show_go_to_cart_button'                      => false,
			'show_go_to_checkout_button'                  => false,
			'override_customer_payment_link_in_order_page'=> true,
			'override_product_price_in_cart'              => false,
		);

		return $default_settings_pro;
	}

	public function set_definitions() {
		$definitions_pro = array(

			'hide_tabs'				      => FILTER_VALIDATE_BOOLEAN,

			'frontend_page'				      => FILTER_VALIDATE_BOOLEAN,
			'frontend_page_url'			      => FILTER_SANITIZE_STRING,
			'frontend_hide_theme_header'		      => FILTER_VALIDATE_BOOLEAN,
			'frontend_hide_theme_footer'		      => FILTER_VALIDATE_BOOLEAN,

			'cache_customers_timeout'                     => FILTER_SANITIZE_NUMBER_INT,
			'cache_customers_session_key'                 => FILTER_SANITIZE_STRING,
			'search_customer_in_orders'                   => FILTER_VALIDATE_BOOLEAN,
			'search_all_customer_fields'                  => FILTER_VALIDATE_BOOLEAN,
			'number_of_customers_to_show'                 => FILTER_SANITIZE_NUMBER_INT,
			'default_customer_id'                         => FILTER_SANITIZE_STRING,
			'update_customers_profile_after_create_order' => FILTER_VALIDATE_BOOLEAN,
			'hide_shipping_section'                       => FILTER_VALIDATE_BOOLEAN,
			'do_not_submit_on_enter_last_field'           => FILTER_VALIDATE_BOOLEAN,

			'disable_creating_customers'                  => FILTER_VALIDATE_BOOLEAN,
			'newcustomer_show_password_field'             => FILTER_VALIDATE_BOOLEAN,
			'newcustomer_show_username_field'             => FILTER_VALIDATE_BOOLEAN,
			'newcustomer_show_role_field'                 => FILTER_VALIDATE_BOOLEAN,
			'newcustomer_email_is_optional'               => FILTER_VALIDATE_BOOLEAN,
			'newcustomer_hide_email'                      => FILTER_VALIDATE_BOOLEAN,
			'newcustomer_hide_company'                    => FILTER_VALIDATE_BOOLEAN,
			'newcustomer_hide_address_1'                  => FILTER_VALIDATE_BOOLEAN,
			'newcustomer_hide_address_2'                  => FILTER_VALIDATE_BOOLEAN,
			'newcustomer_hide_city'                       => FILTER_VALIDATE_BOOLEAN,
			'newcustomer_hide_postcode'                   => FILTER_VALIDATE_BOOLEAN,
			'newcustomer_hide_country'                    => FILTER_VALIDATE_BOOLEAN,
			'newcustomer_hide_state'                      => FILTER_VALIDATE_BOOLEAN,
			'default_city'                                => FILTER_SANITIZE_STRING,
			'default_postcode'                            => FILTER_SANITIZE_STRING,
			'default_country'                             => FILTER_SANITIZE_STRING,
			'default_state'                               => FILTER_SANITIZE_STRING,
			'default_role'                                => FILTER_SANITIZE_STRING,
			'disable_new_user_email'                      => FILTER_VALIDATE_BOOLEAN,
			'dont_fill_shipping_address_for_new_customer' => FILTER_VALIDATE_BOOLEAN,

			'cache_products_timeout'                      => FILTER_SANITIZE_NUMBER_INT,
			'cache_products_session_key'                  => FILTER_SANITIZE_STRING,
			'search_by_sku'                               => FILTER_VALIDATE_BOOLEAN,
			'search_by_cat_and_tag'                       => FILTER_VALIDATE_BOOLEAN,
			'number_of_products_to_show'                  => array(
				'filter'  => FILTER_VALIDATE_INT,
				'options' => array(
					'min_range' => 1,
					'default'   => $this->get_default_settings()['number_of_products_to_show'],
				)
				,
			),
			'autocomplete_product_hide_image'             => FILTER_VALIDATE_BOOLEAN,
			'autocomplete_product_hide_status'            => FILTER_VALIDATE_BOOLEAN,
			'autocomplete_product_hide_qty'               => FILTER_VALIDATE_BOOLEAN,
			'autocomplete_product_hide_price'             => FILTER_VALIDATE_BOOLEAN,
			'autocomplete_product_hide_sku'               => FILTER_VALIDATE_BOOLEAN,
			'autocomplete_product_hide_name'              => FILTER_VALIDATE_BOOLEAN,
			'show_long_attribute_names'                   => FILTER_VALIDATE_BOOLEAN,
			'repeat_search'                               => FILTER_VALIDATE_BOOLEAN,
			'allow_duplicate_products'                    => FILTER_VALIDATE_BOOLEAN,
			'hide_products_with_no_price'                 => FILTER_VALIDATE_BOOLEAN,
			'sale_backorder_product'                      => FILTER_VALIDATE_BOOLEAN,
			'add_product_to_top_of_the_cart'              => FILTER_VALIDATE_BOOLEAN,
			'is_readonly_price'                           => FILTER_VALIDATE_BOOLEAN,
			'item_price_precision'                        => array(
				'filter'  => FILTER_VALIDATE_INT,
				'options' => array(
					'min_range' => 0,
					'default'   => $this->get_default_settings()['item_price_precision'],
				)
				,
			),
			'item_default_selected' => array(
				'filter'  => FILTER_VALIDATE_INT,
				'flags'   => FILTER_REQUIRE_ARRAY,
				'default' => array(),
			),
			'is_featured'                                 => FILTER_SANITIZE_STRING,
			'disable_edit_meta'                           => FILTER_VALIDATE_BOOLEAN,

			'disable_adding_products'                     => FILTER_VALIDATE_BOOLEAN,
			'new_product_ask_sku'                         => FILTER_VALIDATE_BOOLEAN,
			'product_visibility'                          => FILTER_SANITIZE_STRING,
			'new_product_ask_tax_class'                   => FILTER_VALIDATE_BOOLEAN,
			'item_tax_class'                              => FILTER_SANITIZE_STRING,

			'hide_add_fee'                                => FILTER_VALIDATE_BOOLEAN,
			'default_fee_name'                            => FILTER_SANITIZE_STRING,
			'default_fee_amount'                          => FILTER_SANITIZE_NUMBER_FLOAT,
			'fee_tax_class'                               => FILTER_SANITIZE_STRING,

			'hide_add_discount'                           => FILTER_VALIDATE_BOOLEAN,

			'cache_orders_timeout'                        => FILTER_SANITIZE_NUMBER_INT,
			'cache_orders_session_key'                    => FILTER_SANITIZE_STRING,
			'copy_only_paid_orders'                       => FILTER_VALIDATE_BOOLEAN,
			'button_for_find_orders'                      => FILTER_VALIDATE_BOOLEAN,
			'set_current_price_in_copied_order'           => FILTER_VALIDATE_BOOLEAN,
			'hide_find_orders'                            => FILTER_VALIDATE_BOOLEAN,

			'show_duplicate_order_button'                 => FILTER_VALIDATE_BOOLEAN,
			'show_edit_order_in_wc'                       => FILTER_VALIDATE_BOOLEAN,
			'hide_button_pay_as_customer'                 => FILTER_VALIDATE_BOOLEAN,
			'hide_button_create_order'                    => FILTER_VALIDATE_BOOLEAN,
			'hide_button_put_on_hold'                     => FILTER_VALIDATE_BOOLEAN,
			'hide_coupon_warning'			      => FILTER_VALIDATE_BOOLEAN,
			'hide_add_shipping'			      => FILTER_VALIDATE_BOOLEAN,

			'customer_custom_fields'                      => FILTER_SANITIZE_STRING,
			'order_custom_fields'                         => FILTER_SANITIZE_STRING,

			'show_go_to_cart_button'                      => FILTER_VALIDATE_BOOLEAN,
			'show_go_to_checkout_button'                  => FILTER_VALIDATE_BOOLEAN,
			'override_customer_payment_link_in_order_page'=> FILTER_VALIDATE_BOOLEAN,
			'override_product_price_in_cart'              => FILTER_VALIDATE_BOOLEAN,
		);

		return $definitions_pro;
	}
}

new WC_Phone_Orders_Helper_Pro();