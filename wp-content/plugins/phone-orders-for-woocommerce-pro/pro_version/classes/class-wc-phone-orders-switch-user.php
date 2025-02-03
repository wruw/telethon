<?php

class WC_Phone_Orders_Switch_User {

	private $clear_cookie_get_parameter_key = 'wpo_back_to_admin';

	public function __construct() {
		add_action( 'init', array( $this, 'login_gate' ) );
		add_filter( 'woocommerce_thankyou_order_received_text', function ( $text ) {
			if ( isset( $_COOKIE[ WC_PHONE_ADMIN_COOKIE ] ) ) {
				$text = $this->get_html_back_to_admin_area() . $text;
			}

			return $text;
		} );

		add_action( 'woocommerce_before_cart', function () {
			if ( isset( $_COOKIE[ WC_PHONE_ADMIN_COOKIE ] ) ) {
				echo $this->get_html_back_to_admin_area();
			}
		} );

		add_action( 'woocommerce_before_checkout_form', function ( $checkout ) {
			if ( isset( $_COOKIE[ WC_PHONE_ADMIN_COOKIE ] ) ) {
				echo $this->get_html_back_to_admin_area();
			}
		} );

		add_action( 'wp_logout', function () {
			$this->clear_admin_cookie();
		} );
		add_filter( 'before_woocommerce_pay', function () {
			if ( isset( $_COOKIE[ WC_PHONE_ADMIN_COOKIE ] ) ) {
				echo $this->get_html_edit_order();
				echo $this->get_html_back_to_admin_area();
			}
		} );

		add_action('load-woocommerce_page_phone-orders-for-woocommerce', function() {
			$this->clear_admin_cookie();
		});

		add_action( 'before_woocommerce_init', function () {
			if ( isset( $_COOKIE[ WC_PHONE_ADMIN_COOKIE ] ) && ! is_admin() ) {
				add_filter( 'woocommerce_session_handler', function () {
					return 'WC_Phone_Orders_Session_Handler';
				} );
				add_filter( 'woocommerce_persistent_cart_enabled', '__return_false', 1 );
			}
		});
	}

	private function get_html_back_to_admin_area() {
		$admin_url = admin_url( 'admin.php' );
		$admin_url = esc_url( add_query_arg( array(
			'page'          => 'phone-orders-for-woocommerce',
			$this->clear_cookie_get_parameter_key => 'yes',
		), $admin_url ) );

		$is_frontend	= 0;
		$referrer_data  = self::get_data_from_cookie_name(WC_PHONE_ADMIN_REFERRER_COOKIE);

		if (is_array($referrer_data) && isset($referrer_data['is_frontend'])) {
		    $is_frontend = $referrer_data['is_frontend'];
		}

		$title =  __( 'Back to admin area.', 'phone-orders-for-woocommerce' );

		if ($is_frontend) {
		    $title =  __( 'Back to frontend page.', 'phone-orders-for-woocommerce' );
		}

		return "<a href='$admin_url'>" . $title . "</a><hr><br>";
	}

	private function get_html_edit_order() {
		global $wp;
		$order_id = $wp->query_vars['order-pay'];

		$edit_order_page_url = admin_url( 'admin.php' );
		$edit_order_page_url = esc_url( add_query_arg( array(
			'page'				      => 'phone-orders-for-woocommerce',
			$this->clear_cookie_get_parameter_key => 'yes',
			'wpo_edit_order_id'		      => $order_id,
		), $edit_order_page_url ) );

		return "<a href='$edit_order_page_url'>" . __( 'Edit order', 'phone-orders-for-woocommerce' ) . "</a><hr><br>";
	}

	private function clear_admin_cookie() {
		$admin_id = self::get_id_from_cookie_name( WC_PHONE_ADMIN_COOKIE );
		delete_transient( $admin_id . '_temp_cart' );

		setcookie( WC_PHONE_CUSTOMER_COOKIE, '', time() - 31536000, COOKIEPATH );
		setcookie( WC_PHONE_ADMIN_COOKIE, '', time() - 31536000, COOKIEPATH );
		setcookie( WC_PHONE_ADMIN_REFERRER_COOKIE, '', time() - 31536000, COOKIEPATH );
	}

	public function login_gate() {
		$current_user_id = get_current_user_id();
		if ( is_admin() AND ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			if ( isset( $_COOKIE[ WC_PHONE_ADMIN_COOKIE ] ) ) {
				$admin_id = self::get_id_from_cookie_name( WC_PHONE_ADMIN_COOKIE );
				if ( $admin_id AND $current_user_id != $admin_id ) {

					$referrer = null;

					if ( isset( $_GET[ $this->clear_cookie_get_parameter_key ] ) AND 'yes' === $_GET[ $this->clear_cookie_get_parameter_key ] ) {
						$referrer_data = self::get_data_from_cookie_name(WC_PHONE_ADMIN_REFERRER_COOKIE);

						if (is_array($referrer_data) && isset($referrer_data['url'])) {

						    $referrer = $referrer_data['url'];

						    if ( isset( $_GET['wpo_edit_order_id'] ) ) {

							$referrer = add_query_arg( array(
							    'edit_order_id' => $_GET['wpo_edit_order_id'],
							), $referrer );
						    }
						}

						$this->clear_admin_cookie();
						$_SERVER['REQUEST_URI'] = remove_query_arg( $this->clear_cookie_get_parameter_key );
					}

					$current_url = $referrer ? $referrer : "//" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
					do_action("wpo_before_switch_to_customer", $admin_id, $current_url);
					if ( $this->switch_user( $admin_id ) ) { // redirect admin after relogin!
						wp_redirect( $current_url );
						die();
					}
				}
			}
		} elseif ( ! is_admin() ) {
			$current_url = "//" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
			//must set user cookies?
			if ( isset( $_COOKIE[ WC_PHONE_CUSTOMER_COOKIE ] ) ) {
				$customer_id = self::get_id_from_cookie_name( WC_PHONE_CUSTOMER_COOKIE );
				if ( $current_user_id != $customer_id ) {
					do_action("wpo_before_switch_to_customer", $customer_id, $current_url);
					$this->switch_user( $customer_id );
					//do redirect
					wp_redirect( $current_url );
					die();
				}
			}
		}

		if ( isset( $_COOKIE[ WC_PHONE_ADMIN_COOKIE ] ) && ! is_admin() ) {
			$this->apply_cart_data();
		}
	}

	public static function get_customer_id() {
		WC_Phone_Orders_Loader_Pro::disable_object_cache();
		$admin_id = self::get_id_from_cookie_name( WC_PHONE_ADMIN_COOKIE );
		$trans_name       = $admin_id . '_temp_customer_id';
		$temp_customer_id = get_transient($trans_name);

		return $temp_customer_id;
	}

	private static function get_id_from_cookie_name( $cookie_name ) {
		$cookie = isset( $_COOKIE[ $cookie_name ] ) ? json_decode( stripslashes( $_COOKIE[ $cookie_name ] ) ) : false;
		if ( $cookie ) {
			return wp_validate_auth_cookie( $cookie, 'original_user' );
		} else {
			return false;
		}
	}

	private static function get_data_from_cookie_name( $cookie_name ) {
	    return isset( $_COOKIE[ $cookie_name ] ) ? json_decode( stripslashes( base64_decode($_COOKIE[ $cookie_name ]) ), true ) : false;
	}

	private function switch_user( $user_id ) {
		wp_clear_auth_cookie();
		// known user?
		if ( $user_id AND ( $user = get_userdata( $user_id ) ) ) {
			wp_set_auth_cookie( $user_id );
			wp_set_current_user( $user_id, $user->user_login );
			do_action( 'wp_login', $user->user_login, $user );

			return true;
		}

		return false;
	}

	private function apply_cart_data() {

	    include_once WC_PHONE_ORDERS_PRO_VERSION_PATH . 'classes/class-wc-phone-orders-settings-pro.php';

	    WC_Phone_Orders_Loader_Pro::disable_object_cache();

	    $admin_id		 = $this->get_id_from_cookie_name(WC_PHONE_ADMIN_COOKIE);
	    $phone_order_cart	 = get_transient($admin_id . '_temp_cart');

	    if (!empty($phone_order_cart)) {

		$settings = WC_Phone_Orders_Settings::getInstance();

		$items			 = isset($phone_order_cart['items']) ? $phone_order_cart['items'] : array();
		$override_items_price	 = $settings->get_option('override_product_price_in_cart');

		if ($override_items_price && $items) {
		    add_filter('woocommerce_get_cart_item_from_session', function($session_data, $values, $key) use ($items) {

			foreach ($items as $item) {
			    if ( ! $item['is_subscribed'] && isset($item['key']) && isset($values['wpo_key']) && $values['wpo_key'] == $item['key'] ) {

				$session_data['data']->set_price($item['item_cost']);

				return $session_data;
			    }
			}

			return $session_data;
		    }, 10, 3);
		}

		    // SET UP SHIPPING
		    // register 'custom price' shipping method only when it has been selected in admin side and shipping was not remove
		    if ( isset( $phone_order_cart['shipping'] ) && is_array( $phone_order_cart['shipping'] ) ) {
			    if ( preg_match( '/^phone_orders_custom_price:\d+$/', $phone_order_cart['shipping']['id'] ) == 1 ) {
				    //for shipping method
				    add_action( 'woocommerce_shipping_init', array(
					    'WC_Phone_Orders_Loader_Pro',
					    'woocommerce_shipping_init',
				    ) );
				    add_filter( 'woocommerce_shipping_methods', array(
					    'WC_Phone_Orders_Loader_Pro',
					    'woocommerce_shipping_methods',
				    ) );
			    }
		    }

			// set selected shipping
		    add_action( 'woocommerce_cart_loaded_from_session', function () use ( $phone_order_cart ) {
			    $this->set_shipping_from_transient_cart( $phone_order_cart );
		    }, 10, 0 );
		    add_action( 'woocommerce_check_cart_items', function () use ( $phone_order_cart ) {
			    $this->set_shipping_from_transient_cart( $phone_order_cart );
		    }, 10, 0 );

			// remove shipping from transient cart after changing in cart page
		    add_action( 'check_ajax_referer', function ( $action, $result ) use ( $admin_id ) {
			    if ( 'update-shipping-method' === $action ) {
				    $phone_order_cart = get_transient( $admin_id . '_temp_cart' );
				    unset( $phone_order_cart['shipping'] );
				    set_transient( $admin_id . '_temp_cart', $phone_order_cart );
			    }
		    }, 10, 2 );

		    // set price for 'custom_price' shipping method if enabled
		    add_filter( 'woocommerce_package_rates', function ( $rates, $package ) use ( $phone_order_cart ) {
			    if ( isset( $phone_order_cart['shipping'] ) && is_array( $phone_order_cart['shipping'] ) ) {
				    if ( isset( $rates[ $phone_order_cart['shipping']['id'] ] ) ) {
					    $rates[ $phone_order_cart['shipping']['id'] ]->cost = (float) $phone_order_cart['shipping']['cost'];
				    }
			    }

			    return $rates;
		    }, 10, 2 );
			// FINISH SET UP SHIPPING


		$fee_tax_class = $settings->get_option('fee_tax_class');

		$fees_data = isset($phone_order_cart['fee']) ? $phone_order_cart['fee'] : false;

		if ($fees_data) {
		    add_action('woocommerce_cart_calculate_fees', function () use ( $fees_data, $fee_tax_class ) {

			foreach ($fees_data as $index => $fee_data) {
			    WC()->cart->add_fee($fee_data['name'], $fee_data['amount'], (boolean) $fee_tax_class, $fee_tax_class);
			}
		    });
		}

		$discount			 = isset($phone_order_cart['discount']) ? $phone_order_cart['discount'] : false;
		$manual_cart_discount_code	 = strtolower($settings->get_option('manual_coupon_title'));

		if ($discount && $manual_cart_discount_code) {
		    add_action('woocommerce_get_shop_coupon_data', function ( $manual, $coupon ) use ( $discount, $manual_cart_discount_code ) {

			if ($coupon != $manual_cart_discount_code) {
			    return $manual;
			}

			// fake coupon here
			return array('amount' => $discount['amount'], 'discount_type' => $discount['type'], 'id' => - 1);
		    }, 10, 2);
		}

		$customer_note	 = isset($phone_order_cart['customer_note']) ? $phone_order_cart['customer_note'] : false;
		$private_note	 = isset($phone_order_cart['private_note']) ? $phone_order_cart['private_note'] : false;
		$custom_fields	 = isset($phone_order_cart['custom_fields']) ? $phone_order_cart['custom_fields'] : array();
		
		if ($customer_note || $custom_fields) {
		    add_filter('woocommerce_checkout_get_value', function ( $value, $input ) use ( $customer_note, $custom_fields ) {
			if ('order_comments' == $input && $customer_note) {
			    $value = $customer_note;
			}

		    if ( isset($custom_fields[$input]) ) {
			    $value = $custom_fields[$input];
		    }

			return $value;
		    }, 10, 2);
		}


			    $meta_key_private_note = WC_Phone_Orders_Loader::$meta_key_private_note;
			    $meta_key_order_creator = WC_Phone_Orders_Loader::$meta_key_order_creator;

			    add_action( 'woocommerce_checkout_order_processed', function ( $order_id, $posted_data, $order ) use ( $private_note, $meta_key_private_note, $admin_id, $meta_key_order_creator ) {
				    if ( $private_note ) {
					    $order->add_order_note( $private_note );
					    update_post_meta( $order_id, $meta_key_private_note, $private_note );
				    }

				    update_post_meta( $order_id, $meta_key_order_creator, $admin_id );
			    }, 10, 3 );
	    }
	}

	public function set_shipping_from_transient_cart($phone_order_cart) {
		WC()->shipping->reset_shipping();

		$chosen_shipping_methods = array();
		if ( isset( $phone_order_cart['shipping'] ) && is_array( $phone_order_cart['shipping'] ) ) {
			$chosen_shipping_methods = array( wc_clean( $phone_order_cart['shipping']['id'] ) );
			foreach ( WC()->shipping->get_packages() as $index => $value ) {
				WC()->session->set( 'shipping_for_package_' . $index, '' );
			}
		}

		WC()->session->set( 'chosen_shipping_methods', $chosen_shipping_methods );
	}

}

new WC_Phone_Orders_Switch_User();