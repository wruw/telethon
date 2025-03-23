<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WC_Phone_Orders_Add_Order_Page_Pro extends WC_Phone_Orders_Add_Order_Page {

	protected $meta_key_tax_exempt = 'is_vat_exempt';

	public function __construct() {

            parent::__construct();

            $render_hooks = array(
                'wpo_after_order_items',
                'wpo_after_customer_details',
                'wpo_find_order',
                'wpo_order_footer_left_side',
                'wpo_before_search_items_field',
                'wpo_add_fee',
                'wpo_footer_buttons',
            );

            array_map( function ( $hook_name ) {
                add_action( $hook_name, function () {
                    $method = sprintf('%s_action_hook_render', current_action());
                    if (method_exists($this, $method)) {
                        call_user_func_array(array($this, $method), array());
                    }
                } );
            }, $render_hooks );


            add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'woocommerce_checkout_update_order_meta' ),
                    10, 2 );

            if ( $this->option_handler->get_option( 'update_customers_profile_after_create_order' ) ) {
                    add_action( 'wpo_order_created', function( $order, $data ) {
                            $this->save_customer_data( $data['customer'] );
                    }, 10, 2 );
            }

        // apply custom fields
		add_action( 'wpo_after_create_customer', function ( $user_id, $data ) {
			if ( ! empty( $data['custom_fields'] ) ) {
				foreach ( $data['custom_fields'] as $key => $value ) {
					update_user_meta( $user_id, $key, $value );
				}
			}
		}, 10, 2 );

		add_action( "wpo_set_cart_customer", function ( $cart_customer, $id, $customer_data ) {
			if ( ! empty( $customer_data['custom_fields'] ) ) {
				foreach ( $customer_data['custom_fields'] as $key => $value ) {
					WC()->customer->update_meta_data( $key, $value );
				}
			}
		}, 10, 3 );

		add_filter( "wpo_after_update_customer", function ( $customer ) {
			$custom_fields                  = array();
			$customer_custom_fields_options = $this->extract_field_from_option( $this->option_handler->get_option( 'customer_custom_fields' ) );
			foreach ( WC()->customer->get_meta_data() as $meta ) {
				if ( in_array( $meta->key, array_keys( $customer_custom_fields_options ) ) ) {
					$custom_fields[ $meta->key ] = $meta->value;
				};
			}
			$customer['custom_fields'] = $custom_fields;

			return $customer;
		}, 10, 1 );

		add_filter( "wpo_after_get_customer_by_id", function ( $customer_data, $customer_obj ) {
			$custom_fields                  = array();
			$customer_custom_fields_options = $this->extract_field_from_option( $this->option_handler->get_option( 'customer_custom_fields' ) );
			foreach ( $customer_obj->get_meta_data() as $meta ) {
				if ( in_array( $meta->key, array_keys( $customer_custom_fields_options ) ) ) {
					$custom_fields[ $meta->key ] = $meta->value;
				};
			}
			$customer_data['custom_fields'] = $custom_fields;

			return $customer_data;
		}, 10, 2 );
		// end apply custom fields

	}

	protected function make_customer_fields_to_show() {
	    $fields = parent::make_customer_fields_to_show();
	    $additional_fields = array(
		    'password' => array(
			    'label' => __( 'Password', 'phone-orders-for-woocommerce' ),
			    'value' => '',
		    ),
		    'username' => array(
			    'label' => __( 'Username', 'phone-orders-for-woocommerce' ),
			    'value' => '',
		    ),
		    'role' => array(
			    'label' => __( 'Role', 'phone-orders-for-woocommerce' ),
			    'value' => $this->option_handler->get_option( 'default_role' ),
		    ),
        );
	    $fields['common']['fields'] = array_merge($fields['common']['fields'], $additional_fields);

	    return $fields;
    }

        protected function wpo_after_order_items_action_hook_render() {
            ?>

            <clear-cart slot="wpo-after-order-items" v-bind="<?php
                echo esc_attr(json_encode(array(
                    'buttonLabel' => __('Clear cart', 'phone-orders-for-woocommerce'),
                )))
            ?>"></clear-cart>

            <?php
        }

        protected function wpo_after_customer_details_action_hook_render() {
            ?>

            <save-to-customer slot="save-to-customer" v-bind="<?php
                echo esc_attr(json_encode(array(
                    'buttonTitle' => __('Save to customer', 'phone-orders-for-woocommerce'),
                    'tabName'     => 'add-order',
                )))
            ?>"></save-to-customer>

            <tax-exempt slot="tax-exempt" v-bind="<?php
            echo esc_attr( json_encode( array(
	            'title'        => __( 'Tax exempt', 'phone-orders-for-woocommerce' ),
	            'tabName'      => 'add-order',
	            'isTaxEnabled' => wc_tax_enabled(),
            ) ) )
	        ?>"></tax-exempt>

            <?php
        }

        protected function wpo_find_order_action_hook_render() {
            ?>

            <find-existing-order slot="find-order" v-bind="<?php
                echo esc_attr(json_encode(array(
                    'title'                           => __('Find existing order', 'phone-orders-for-woocommerce'),
                    'copyButtonForFindOrdersLabel'        => __('Copy order', 'phone-orders-for-woocommerce'),
                    'editButtonForFindOrdersLabel'        => __('Edit order', 'phone-orders-for-woocommerce'),
                    'noticeLoadedLabel'               => __('Current order was copied from order', 'phone-orders-for-woocommerce'),
                    'noticeEditedLabel'               => __('You edit order', 'phone-orders-for-woocommerce'),
                    'noticeDraftedLabel'              => __('You edit unfinished order', 'phone-orders-for-woocommerce'),
                    'selectExistingOrdersPlaceholder' => __('Type to search', 'phone-orders-for-woocommerce'),
                    'noResultLabel'                   => __('Oops! No elements found. Consider changing the search query.', 'phone-orders-for-woocommerce'),
                    'tabName'                         => 'add-order',
                )))
            ?>"></find-existing-order>

            <customer-custom-fields slot="edit-customer-address" v-bind="<?php
	        echo esc_attr( json_encode( array(
		        'dateFormat' => $this->convertPHPToMomentFormat( wc_date_format() ),
		        'empty' => false,
		        'customFieldsLabel' => __('Custom fields', 'phone-orders-for-woocommerce'),
	        ) ) )
	        ?>"></customer-custom-fields>

            <customer-custom-fields slot="add-customer-address" v-bind="<?php
            echo esc_attr( json_encode( array(
	            'dateFormat' => $this->convertPHPToMomentFormat( wc_date_format() ),
	            'empty' => true,
	            'customFieldsLabel' => __('Custom fields', 'phone-orders-for-woocommerce'),
            ) ) )
            ?>"></customer-custom-fields>

	        <?php
        }

	private function convertPHPToMomentFormat( $format ) {
		$replacements = [
			'd' => 'DD',
			'D' => 'ddd',
			'j' => 'D',
			'l' => 'dddd',
			'N' => 'E',
			'S' => 'o',
			'w' => 'e',
			'z' => 'DDD',
			'W' => 'W',
			'F' => 'MMMM',
			'm' => 'MM',
			'M' => 'MMM',
			'n' => 'M',
			't' => '', // no equivalent
			'L' => '', // no equivalent
			'o' => 'YYYY',
			'Y' => 'YYYY',
			'y' => 'YY',
			'a' => 'a',
			'A' => 'A',
			'B' => '', // no equivalent
			'g' => 'h',
			'G' => 'H',
			'h' => 'hh',
			'H' => 'HH',
			'i' => 'mm',
			's' => 'ss',
			'u' => 'SSS',
			'e' => 'zz', // deprecated since version 1.6.0 of moment.js
			'I' => '', // no equivalent
			'O' => '', // no equivalent
			'P' => '', // no equivalent
			'T' => '', // no equivalent
			'Z' => '', // no equivalent
			'c' => '', // no equivalent
			'r' => '', // no equivalent
			'U' => 'X',
		];
		$momentFormat = strtr( $format, $replacements );

		return $momentFormat;
	}

	protected function wpo_order_footer_left_side_action_hook_render() {
		?>

        <order-custom-fields slot="order-footer-left-side" v-bind="<?php
		echo esc_attr( json_encode( array(
			'dateFormat' => $this->convertPHPToMomentFormat( wc_date_format() ),
		) ) )
		?>"></order-custom-fields>

		<?php
	}

        protected function wpo_before_search_items_field_action_hook_render() {

        ?>

            <products-category-tags-filter slot="before-search-items-field" v-bind="<?php
                echo esc_attr(json_encode(array(
                    'categoryLabel'                     => __('Category', 'phone-orders-for-woocommerce'),
                    'selectProductsCategoryPlaceholder' => __('Select a category', 'phone-orders-for-woocommerce'),
                    'tagLabel'                          => __('Tag', 'phone-orders-for-woocommerce'),
                    'selectProductsTagPlaceholder'      => __('Select a tag', 'phone-orders-for-woocommerce'),
                    'tabName'                           => 'add-order',
                )))
            ?>"></products-category-tags-filter>

            <?php
        }

        protected function get_terms_hierarchical($terms, array $output = array(), $parent_id = 0, $level = 0) {

            foreach ($terms as $term) {
                if ($parent_id == $term->parent) {

                    $output[] = array(
                        'value' => $term->slug,
                        'title' => str_pad('', $level * 12, '&nbsp;&nbsp;') . $term->name . '(' . $term->count . ')',
                    );

                    $output = $this->get_terms_hierarchical($terms, $output, $term->term_id, $level + 1);
                }
            }

            return $output;
        }

        protected function ajax_get_products_tags_list( $data ) {

            $all_tags  = get_terms(array('taxomony' => 'product_tag'));
            $tags_list = array();

            foreach ($all_tags as $tag) {
                $tags_list[] = array(
                    'value' => $tag->slug,
                    'title' => $tag->name . ' (' . $tag->count . ')',
                );
            }

            array_walk_recursive($tags_list, function (&$item, $key) {
                $item = mb_convert_encoding($item, 'UTF-8', mb_detect_encoding($item));
            });

            return $this->wpo_send_json_success(array(
                'tags_list' => $tags_list,
            ));
        }

        protected function ajax_get_products_categories_list( $data ) {

            $categories = get_terms(array(
                'hierarchical' => 1,
                'orderby'      => 'name',
                'taxonomy'     => 'product_cat',
            ));

            $categories_list = $this->get_terms_hierarchical($categories);

            array_walk_recursive($categories_list, function (&$item, $key) {
                $item = mb_convert_encoding($item, 'UTF-8', mb_detect_encoding($item));
            });

            return $this->wpo_send_json_success(array(
                'categories_list' => $categories_list,
            ));
        }

        protected function wpo_add_fee_action_hook_render() {
            ?>

            <add-fee slot="add-fee" v-bind="<?php
                echo esc_attr(json_encode(array(
                    'addFeeButtonLabel' => __( 'Add fee', 'phone-orders-for-woocommerce' ),
                )))
            ?>"></add-fee>

            <?php
        }

        protected function wpo_footer_buttons_action_hook_render() {
            ?>

            <footer-buttons-1 slot="pro-version-buttons-1" v-bind="<?php
                echo esc_attr(json_encode(array(
                    'putOnHoldButtonLabel'         => __('Put on hold', 'phone-orders-for-woocommerce'),
                    'goToCartPage'                 => __('Go to Cart', 'phone-orders-for-woocommerce'),
                    'goToCheckoutPage'             => __('Go to Checkout', 'phone-orders-for-woocommerce'),
                    'updateOrderButtonLabel'       => __('Update order', 'phone-orders-for-woocommerce'),
                    'cancelUpdateOrderButtonLabel' => __('Cancel', 'phone-orders-for-woocommerce'),
                    'clearAllButtonLabel'          => __('Clear all', 'phone-orders-for-woocommerce'),
                    'payOrderButtonLabel'          => __('Pay order as the customer', 'phone-orders-for-woocommerce'),
                    'orderIsCompletedTitle'        => __('Order completed', 'phone-orders-for-woocommerce'),
                    'tabName'                      => 'add-order',
                )))
            ?>"></footer-buttons-1>

            <footer-buttons-2 slot="pro-version-buttons-2" v-bind="<?php
                echo esc_attr(json_encode(array(
                    'editCreatedOrderButtonLabel' => __( 'Edit created order', 'phone-orders-for-woocommerce' ),
                    'orderIsCompletedTitle'       => __('Order completed', 'phone-orders-for-woocommerce'),
                )))
            ?>"></footer-buttons-2>

            <?php
        }

	public function woocommerce_checkout_update_order_meta( $order_id, $data ) {
		if ( isset( $_REQUEST['cart']['custom_fields'] ) ) {
			$custom_fields = $_REQUEST['cart']['custom_fields'];
			$order         = wc_get_order( $order_id );
			foreach ( $custom_fields as $key => $value ) {
				$order->update_meta_data( $key, $value );
			}
			$order->save();
		}

		if ( isset( $_REQUEST['cart']['customer']['custom_fields'] ) ) {
			$customer_custom_fields_options = $this->extract_field_from_option( $this->option_handler->get_option( 'customer_custom_fields' ) );
			$custom_fields = $_REQUEST['cart']['customer']['custom_fields'];
			$order         = wc_get_order( $order_id );

            foreach ( $custom_fields as $key => $value ) {
	            if ( in_array( $key, array_keys( $customer_custom_fields_options ) ) ) {
		            $order->update_meta_data( '_wpo_customer_meta_' . $key, $value );
	            };
            }
			$order->save();
		}
	}

	public function enqueue_scripts() {
            parent::enqueue_scripts();
	}

	protected function ajax_save_customer_data( $data ) {
		$this->save_customer_data( $data['customer_data'] );
		return $this->wpo_send_json_success(  );
	}

	protected function save_customer_data( $customer_data ) {
		if ( empty( $customer_data['id'] ) ) {
			return false;
		}
		$customer      = new WC_Customer( $customer_data['id'] );

		array_walk(
			$customer_data,
			function ( $value, $key ) {
				if ( stripos( $key, 'billing_' ) OR stripos( $key, 'shipping_' ) ) {
					return $value;
				}
			}
		);
		$errors = $customer->set_props( $customer_data );
		if ( ! empty( $customer_data['is_vat_exempt'] ) ) {
			$tax_exempt = wc_string_to_bool($customer_data['is_vat_exempt']) ? 'yes' : 'no';
			$customer->update_meta_data( 'is_vat_exempt', $tax_exempt );
			$customer->save_meta_data();
		}
		if ( ! empty( $customer_data['custom_fields'] ) ) {
			foreach ( $customer_data['custom_fields'] as $key => $value ) {
				$customer->update_meta_data( $key, $value );
			}
			$customer->save_meta_data();
		}

		$customer->save();

		return true;
	}

	private function search_orders( $term ) {
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
		$order_ids     = array();

		if ( is_numeric( $term ) ) {
			$order_ids[] = absint( $term );
		}

		if ( ! empty( $search_fields ) ) {
			$date_depth = apply_filters( 'wpo_search_orders_date_depth', '-5 years');
			$order_ids = array_unique(
				array_merge(
					$order_ids,
					$wpdb->get_col(
						$wpdb->prepare(
							"SELECT DISTINCT p1.post_id FROM {$wpdb->postmeta} p1 
INNER JOIN {$wpdb->posts} as p2 on p1.post_id = p2.ID
WHERE p1.meta_value LIKE %s AND p1.meta_key IN ('" . implode( "','", array_map( 'esc_sql', $search_fields ) ) . "')" . " AND p2.post_modified > %s LIMIT 100", // @codingStandardsIgnoreLine
							'%' . $wpdb->esc_like( wc_clean( $term ) ) . '%',
                            gmdate( 'Y-m-d H:i:s', ( strtotime( $date_depth ) + ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ) ) )
						)
					),
					$wpdb->get_col(
						$wpdb->prepare(
							"SELECT order_id
							FROM {$wpdb->prefix}woocommerce_order_items as order_items
							INNER JOIN {$wpdb->posts} as p2 on order_items.order_id = p2.ID
							WHERE order_item_name LIKE %s" . " AND p2.post_modified > %s LIMIT 100",
							'%' . $wpdb->esc_like( wc_clean( $term ) ) . '%',
							gmdate( 'Y-m-d H:i:s', ( strtotime( $date_depth ) + ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ) ) )
						)
					)
				)
			);
		}

		return apply_filters( 'woocommerce_shop_order_search_results', $order_ids, $term, $search_fields );
	}

	protected function ajax_find_orders( $data ) {

                $term               = $data['term'];
                $option_handler     = $this->option_handler;
                $paid_statuses      = array('processing', 'completed', 'draft');
                $allowed_post_types = array('shop_order');

		$orders_ids = $this->search_orders( str_replace( 'Order #', '', wc_clean( $term ) ));
                rsort( $orders_ids );

		$limit  = (int)apply_filters('wpo_find_orders_limit', 20);
                $result = array();
		foreach ( $orders_ids as $order_id ) {

                        $order = wc_get_order( $order_id );

                        if ( ! $order || ! in_array($order->get_type(), $allowed_post_types) ) {
				continue;
			}

                        //allow only  paid ?
			if ( $option_handler->get_option( 'copy_only_paid_orders' ) AND ! in_array( $order->get_status(),
					$paid_statuses ) ) {
				continue;
			}

			if ( ! wc_is_order_status('wc-' . $order->get_status()) AND 'draft' != $order->get_status()){
				continue;
			}

			$formated_output_array = array(
				__( 'order # ', 'phone-orders-for-woocommerce' ) . $order->get_order_number(),
				$order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
				$this->render_order_date_column( $order->get_date_created() ),
				get_woocommerce_currency_symbol() . ' ' . $order->get_total(),
				$order->get_status(),
			);

			$formated_output = implode( " | ", $formated_output_array );

			if ( 'draft' == $order->get_status() ) {
				$copy_button_value = __( 'Resume order', 'phone-orders-for-woocommerce' );
				$edit_button_value = '';
			} else {
				$copy_button_value = __( 'Copy order', 'phone-orders-for-woocommerce' );
				$edit_button_value = __( 'Edit order', 'phone-orders-for-woocommerce' );
			}

			$result[] = array(
				'formated_output'  => $formated_output,
				'loaded_order_url' => $order->get_edit_order_url(),
				'loaded_order_id'  => $order->get_id(),
				'copy_button_value'     => $copy_button_value,
				'edit_button_value'     => $edit_button_value,
			);

			if( count($result) > $limit )
				break;
		}

		wp_send_json( $result );
	}

	protected function ajax_load_order( $data ) {
		$order_id = isset( $data['order_id'] ) ? $data['order_id'] : '';
		$is_edit = ( isset( $data['is_edit'] ) AND $data['is_edit'] == 'true' )? true : false;
		if ( $order_id ) {
			$response = $this->load_order( $order_id, $is_edit );
			$response['log_row_id'] = uniqid();
			$result = $this->update_cart( $response['cart'] );
			if ( $result instanceof WC_Data_Exception ) {
				return $this->wpo_send_json_error( $result->getMessage() );
			}
			WC_Phone_Orders_Tabs_Helper::add_log( $response['log_row_id'], $result, $order_id );
			return $this->wpo_send_json_success( $response );
		} else {
			return $this->wpo_send_json_error();
		}
	}

	private function load_order( $order_id, $is_edit = false ) {

		$option_handler = $this->option_handler;

		$order = wc_get_order( $order_id );

		if ( ! $order_id ){
			return false;
		}

		$cart                 = array();
		$cart['loaded_order'] = true;

		$hidden_order_itemmeta = apply_filters(
			'woocommerce_hidden_order_itemmeta', array(
				'_qty',
				'_tax_class',
				'_product_id',
				'_variation_id',
				'_line_subtotal',
				'_line_subtotal_tax',
				'_line_total',
				'_line_tax',
				'method_id',
				'cost',
				'_reduced_stock',
			)
		);

		//order id
		$deleted_items = $out_of_stock_items = array();
		$post_statuses = current_user_can( 'edit_private_products' ) ? array( 'private', 'publish' ) : array( 'publish' );
                // items
		foreach ( $order->get_items() as $key => $order_item ) {

                        $item_data  = $order_item->get_data();

                        $product_id = ( $item_data['variation_id'] ) ? $item_data['variation_id'] : $item_data['product_id'];

                        $_product = wc_get_product( $product_id );
                        
                        if( !$_product ) {
							$deleted_items[] = array(
											'id'   => $product_id,
											'name' => $item_data['name'],
                            );
							continue;
						}

                        $item_custom_meta_fields = array();

                        $product_attrs = $_product->get_attributes();

                        if (isset($item_data['meta_data']) && is_array($item_data['meta_data'])) {
                            foreach ($item_data['meta_data'] as $meta) {
								if ( in_array( $meta->key, $hidden_order_itemmeta, true ) ) {
									continue;
								}

								$d = $meta->get_data();
								if (isset($product_attrs[$d['key']])) {
									continue;
								}
                                $item_custom_meta_fields[] = array(
                                    'id'         => $d['id'],
                                    'meta_key'   => $d['key'],
                                    'meta_value' => $d['value'],
                                );
                            }
                        }

			if ( ! in_array($_product->get_status(), $post_statuses) ) {
				$deleted_items[] = array(
                                    'id'   => $product_id,
                                    'name' => $item_data['name'],
                                );
				continue;
			}
			if ( ! $_product->is_in_stock() AND ! $option_handler->get_option( 'sale_backorder_product' ) ) {
				$out_of_stock_items[] = array(
                                    'id'   => $product_id,
                                    'name' => $item_data['name'],
                                );
				continue;
			};

			if ( $option_handler->get_option( 'set_current_price_in_copied_order' ) ) {
				$item_cost     = $_product->get_price();
				$line_subtotal = $item_cost * $item_data['quantity'];
			} else {
				$item_cost     = $item_data['subtotal'] / $item_data['quantity'];
				$line_subtotal = $item_data['subtotal'];
			}

                    $cart['items'][] = $this->get_item_by_product($_product, array_merge($item_data, array(
                        'item_cost'          => $item_cost,
                        'line_subtotal'      => $line_subtotal,
                        'custom_meta_fields' => $item_custom_meta_fields,
                    )));
                };

		if ( ! isset( $cart['items'] ) ) {
			$cart['items'] = array();
		}


                // customer
		$cart['customer'] = $this->get_customer_by_order( $order );

		// fee
		$cart['fee'] = array();
		foreach ( $order->get_fees() as $key => $fee_data ) {
			$cart['fee'][] = array(
				'name'   => $fee_data->get_name(),
				'amount' => $fee_data->get_amount(),
			);
		}

		// discount in coupons
		$cart['discount'] = null;
		$discount         = get_post_meta( $order_id, $option_handler->get_option( 'manual_coupon_title' ), true );
		if ( $discount ) {
			$cart['discount'] = array(
				'type'   => isset( $discount['type'] ) ? $discount['type'] : $discount['discount_type'],
				'amount' => $discount['amount'],
			);
		}

		// coupons
		$cart['coupons'] = array();
		foreach ( $order->get_used_coupons() as $index => $value ) {
			if ( isset( $discount['code'] ) ) {
				$code = $discount['code'];
			} elseif ( isset( $discount['discount_code'] ) ) {
				$code = $discount['discount_code'];
			} else {
				$code = '';
			}
			if ( $value === $code ) {
				continue;
			}

			$cart['coupons'][] = array(
                'title' => $value,
            );
		}

		// shipping
		$cart['shipping'] = null;
		$item_shipping    = current( $order->get_shipping_methods() );

		//var_dump($item_shipping);die;

		if ( $item_shipping ) {
			if( method_exists($item_shipping, "get_instance_id") )   //since WC 3.4
				$shipping_id = $item_shipping->get_method_id() . ':' . $item_shipping->get_instance_id();
			else
				$shipping_id = $item_shipping->get_method_id();
			$cart['shipping'] = array(
				'id'        => $shipping_id,
				'label'     => $item_shipping->get_method_title(),
				'cost'      => $item_shipping->get_total(),
				'full_cost' => $item_shipping->get_total() + $item_shipping->get_total_tax(),
			);
		}

		// customer_note
		$cart['customer_note'] = $order->get_customer_note();

		// private note
		$cart['private_note'] = get_post_meta( $order_id, $this->meta_key_private_note, true );

		$message = '';
		if ( 'draft' == $order->get_status() ) {
			$cart['drafted_order_id'] = $order->get_id();
			$message                  =
				'<h2>
					<span class="">' . __( 'You edit unfinished order', 'phone-orders-for-woocommerce' ) . '</span>
				</h2>';
		} elseif ( $is_edit ) {
			$cart['edit_order_id'] = $order->get_id();
			$message               =
				'<h2>
					<span class="">' . __( 'You edit order', 'phone-orders-for-woocommerce' ) . '</span>
					<a id="loaded_order_url" href="' . $order->get_edit_order_url() . '" target="_blank">#' . $order->get_order_number() . '</a>
				</h2>';
		} else {
			$cart['loaded_order_id'] = $order->get_id();
			$message                 =
				'<h2>
					<span class="">' . __( 'Current order was copied from order', 'phone-orders-for-woocommerce' ) . '</span>
					<a id="loaded_order_url" href="' . $order->get_edit_order_url() . '" target="_blank">#' . $order->get_order_number() . '</a>
				</h2>';
		}

		// custom fields
		$custom_fields_options          = $this->extract_field_from_option( $option_handler->get_option( 'order_custom_fields' ) );
		$customer_custom_fields_options = $this->extract_field_from_option( $option_handler->get_option( 'customer_custom_fields' ) );

		foreach ( $order->get_meta_data() as $meta ) {
			if ( in_array( $meta->key, array_keys( $custom_fields_options ) ) ) {
				$cart['custom_fields'][ $meta->key ] = $meta->value;
			};
			if ( in_array( str_replace( '_wpo_customer_meta_', '', $meta->key ), array_keys( $customer_custom_fields_options ) ) ) {
				$cart['customer']['custom_fields'][ str_replace( '_wpo_customer_meta_', '', $meta->key ) ] = $meta->value;
			};
		}

                if (!isset($cart['custom_fields'])) {
                    $cart['custom_fields'] = (object) array();
                }

                $cart['custom_fields_values'] = array();

                $current_time = current_time('timestamp', true);
                if ($option_handler->get_option('show_order_date_time')) {
                    $order_date_timestamp = $order->get_date_created()->getTimestamp();
                    $order_date_timestamp = $order_date_timestamp > $current_time ? $order_date_timestamp : $current_time;
                } else {
                    $order_date_timestamp = $current_time;
                }

		if ( $order->get_status() !== self::ORDER_STATUS_COMPLETED ) {
			$cart['order_payment_url'] = $order->get_checkout_payment_url();
		}

		$order_status = $order->get_status();
		if ( wc_is_order_status( 'wc-' . $order_status ) ) {
			$order_status = 'wc-' . $order_status;
        }

                $result = array(
			'message'            => $message,
			'loaded_order_id'    => $order->get_id(),
			'cart'               => $cart,
			'deleted_items'      => $deleted_items,
			'out_of_stock_items' => $out_of_stock_items,
			'order_date_timestamp' => $order_date_timestamp,
			'order_status'       => $order_status,
		);

		return $result;
	}

	protected function render_order_date_column( $date ) {
		$order_timestamp = $date->getTimestamp();

		if ( $order_timestamp > strtotime( '-1 day', current_time( 'timestamp', true ) ) ) {
			$show_date = sprintf(
			/* translators: %s: human-readable time difference */
				_x( '%s ago', '%s = human-readable time difference', 'woocommerce' ),
				human_time_diff( $date->getTimestamp(), current_time( 'timestamp', true ) )
			);
		} else {
			$show_date = $date->date_i18n( apply_filters( 'woocommerce_admin_order_date_format',
				__( 'M j, Y', 'woocommerce' ) ) );
		}

		return $show_date;
	}

	protected function ajax_put_on_draft( $data ) {
                $result_update = $this->update_cart( $data['cart'] );
		if ( $result_update instanceof WC_Data_Exception ) {
			return $this->wpo_send_json_error( $result_update->getMessage() );
		}
		if ( isset( $data['cart']['drafted_order_id'] ) && $data['cart']['drafted_order_id'] ) {
			$order_id = $data['cart']['drafted_order_id'];
			$created_date_time = ! empty( $data['created_date_time'] ) ? $data['created_date_time'] : '';
			$message = $this->update_order( $order_id, $data['cart'], 'draft', $created_date_time );
			if ( ! $message ) {
                                die;
				return $this->wpo_send_json_error();
			}
		} else {
			$order_id = $this->create_order( $data, $set_status = false );
			$message  = sprintf( __( 'Order #%s created and put on hold', 'phone-orders-for-woocommerce' ),
				$order_id );
			wp_update_post( array(
				'ID'          => $order_id,
				'post_status' => 'draft',
				)
			);
		}
		$loaded_order = $this->load_order($order_id);

		$result = array(
			'drafted_order_id' => $order_id,
			'message'          => $message,
			'cart'             => $loaded_order['cart'],
			'order_message'    => $loaded_order['message'],
		);

		return $this->wpo_send_json_success( $result );
	}

	protected function update_order( $order_id, $cart, $new_status = '', $created_date_time = '' ) {
//		$cart = $data['cart'];

//		$order_id = $cart['drafted_order_id'];
		$order    = wc_get_order( $order_id );
		if ( !$order ) {
			return false;
		}

		$checkout = WC()->checkout();

		$cart_hash = md5( json_encode( wc_clean( WC()->cart->get_cart_for_session() ) ) . WC()->cart->total );

		$billing_address  = array();
		$shipping_address = array();
		$use_shipping_address = ( isset( $cart['customer']['ship_different_address'] ) AND 'true' == $cart['customer']['ship_different_address'] );
		foreach ( $cart['customer'] as $key => $value ) {
			if ( stripos( $key, 'billing_' ) !== false ) {
				$billing_address[ str_replace( 'billing_', '', $key ) ] = $value;
				if ( ! $use_shipping_address )
				{
					$shipping_address[ str_replace( 'billing_', '', $key ) ] = $value;
				}
			} elseif ( $use_shipping_address AND stripos( $key, 'shipping_' ) !== false ) {
				$shipping_address[ str_replace( 'shipping_', '', $key ) ] = $value;
			}
		}
		$order->set_customer_id( $cart['customer']['id'] );
		$order->set_address( $billing_address, 'billing' );
		$order->set_address( $shipping_address, 'shipping' );
		$this->maybe_update_tax_exempt( $order, $cart );

		$order->set_cart_hash( $cart_hash );
		$order->set_currency( get_woocommerce_currency() );

		$order->set_customer_note( isset( $cart['customer_note'] ) ? $cart['customer_note'] : '' );
		$private_note = get_post_meta( $order_id, $this->meta_key_private_note, true );
		if ( isset( $cart['private_note'] ) AND $cart['private_note'] != $private_note ) {
			$order->add_order_note( $cart['private_note'] );
			update_post_meta( $order_id, $this->meta_key_private_note, $cart['private_note'] );
		}

		$order->set_shipping_total( WC()->cart->get_shipping_total() );
		$order->set_discount_total( WC()->cart->get_discount_total() );
		$order->set_discount_tax( WC()->cart->get_discount_tax() );
		$order->set_cart_tax( WC()->cart->get_cart_contents_tax() + WC()->cart->get_fee_tax() );
		$order->set_shipping_tax( WC()->cart->get_shipping_tax() );
		$order->set_total( WC()->cart->get_total( 'edit' ) );


		$order->remove_order_items();

                add_action('woocommerce_checkout_create_order_line_item', array($this, 'action_woocommerce_checkout_create_order_line_item'), 10, 4);

		$checkout->create_order_line_items( $order, WC()->cart );

                remove_action('woocommerce_checkout_create_order_line_item', array($this, 'action_woocommerce_checkout_create_order_line_item'));

                $checkout->create_order_fee_lines( $order, WC()->cart );
		$checkout->create_order_shipping_lines( $order, WC()->session->get( 'chosen_shipping_methods' ),
			WC()->shipping->get_packages() );
		$checkout->create_order_tax_lines( $order, WC()->cart );
		$checkout->create_order_coupon_lines( $order, WC()->cart );

		foreach ( WC()->cart->get_coupons() as $code => $coupon ) {
			if ( $code == $this->option_handler->get_option( 'manual_coupon_title' ) ) {
				$result                    = array(
					'code'   => $this->option_handler->get_option( 'manual_coupon_title' ),
					'type'   => $coupon->get_discount_type(),
					'amount' => $coupon->get_amount(),
					'id'     => - 1,
				);
				if ( ! add_post_meta( $order->get_id(), $this->option_handler->get_option( 'manual_coupon_title' ), $result, true ) ) {
					update_post_meta( $order->get_id(), $this->option_handler->get_option( 'manual_coupon_title' ), $result );
				}
				break;
			}
		}
		$created_date_time = (int)$created_date_time;
		if ( ! empty( $created_date_time ) && is_integer( $created_date_time ) ) {
			$order->set_date_created( $created_date_time );
		}
		$order->save();

		do_action( 'woocommerce_checkout_update_order_meta', $order_id, array() );

		if ( $new_status AND $new_status != get_post_status($order_id) ) {

			$_new_status = wc_is_order_status( 'wc-' . get_post_status( $order_id ) ) ? 'wc-' . $new_status : $new_status;

			$order->set_status($_new_status);

			$order->save();
		}

		$message = sprintf( __( 'Order #%s updated', 'phone-orders-for-woocommerce' ),
			$order_id );

		return $message;
	}

	/**
	 * @param $order WC_order
	 * @param $cart array
	 */
	protected function maybe_update_tax_exempt( $order, $cart ) {
		if ( isset( $cart['customer']['is_vat_exempt'] ) ) {
			$tax_exempt = $cart['customer']['is_vat_exempt'] ? 'yes' : 'no';
			$order->set_meta_data( $this->meta_key_tax_exempt, $tax_exempt );
			$order->save_meta_data();
		}
	}

	protected function create_order( $data, $set_status = true ) {
		$order_id = parent::create_order( $data, $set_status );
		if ( ! is_integer($order_id) ) {
		    return $order_id;
        }

		$order    = wc_get_order( $order_id );
		if ( ! $order ) {
		    return false;
        }

		if ( ! empty( $data['cart'] ) ) {
			$this->maybe_update_tax_exempt( $order, $data['cart'] );
		}

		do_action( 'wpo_order_created_pro', $order, $data['cart'] );

		return $order_id;
	}

	protected function ajax_update_order( $data ) {
		$result_cart_update = $this->update_cart( $data['cart'] );
		if ( $result_cart_update instanceof WC_Data_Exception ) {
			return $this->wpo_send_json_error( $result_cart_update->getMessage() );
		}
		if ( isset( $data['order_id'] ) ) {
			$order_id = $data['order_id'];
			$created_date_time = ! empty( $data['created_date_time'] ) ? $data['created_date_time'] : '';
			$order_status = ! empty( $data['order_status'] ) ? $data['order_status'] : '';
			$message = $this->update_order( $order_id, $data['cart'], $order_status, $created_date_time );
			if ( ! $message ) {
				return $this->wpo_send_json_error();
			}
			$loaded_order = $this->load_order($order_id);
			unset($loaded_order['cart']['loaded_order']);
			unset($loaded_order['cart']['loaded_order_id']);
			$loaded_order['cart']['edit_order_id'] = (int)$order_id;
			$result = array(
				'order_id'      => $order_id,
				'cart'          => $loaded_order['cart'],
				'order_message' => $loaded_order['message'],
				'message'       => $message,
			);

			return $this->wpo_send_json_success( $result );
		}
		return $this->wpo_send_json_error();
	}

	protected function ajax_move_from_draft( $data ) {
		$order_id = isset($data['drafted_order_id']) ? $data['drafted_order_id'] : 0;
		if ( ! $order_id ) {
			return $this->wpo_send_json_error();
		}

		$order       = wc_get_order( $order_id );
		$payment_url = $order->get_checkout_payment_url();

		$created_date_time = ! empty( $data['created_date_time'] ) ? (int) $data['created_date_time'] : '';
		if ( $created_date_time && is_integer( $created_date_time ) ) {
			$order->set_date_created( $created_date_time );
		}
		$order->save();

		$new_order_status = ! empty( $data['order_status'] ) ? $data['order_status'] : 'wc-pending';
		$wc_order_statuses = wc_get_order_statuses();
		$new_order_status_title = isset( $wc_order_statuses[ $new_order_status ] ) ? $wc_order_statuses[ $new_order_status ] : $new_order_status;

		$message = sprintf( __( 'Order #%s has status "%s"', 'phone-orders-for-woocommerce' ), $order_id, $new_order_status_title );

		$order->set_status($new_order_status);
		$order->save();

		$result = array(
			'order_id'    => $order_id,
			'message'     => $message,
			'payment_url' => $payment_url,
		);

		return $this->wpo_send_json_success( $result );
	}

	protected function ajax_set_payment_cookie( $data ) {
		$order_id = isset( $data['order_id'] ) ? $data['order_id'] : 0;
		if ( ! $order_id ) {
			return $this->wpo_send_json_error( new WP_Error( 'incorrect_parameter', __('Incorrect order ID', 'phone-orders-for-woocommerce') ) );
		}

		$order = wc_get_order($order_id);
		if ( ! $order ) {
			return $this->wpo_send_json_error( new WP_Error( 'error_get_order', __('Error when getting order', 'phone-orders-for-woocommerce') ) );
        }

		$referrer = array(
		    'is_frontend' => $data['is_frontend'],
		    'url'	  => $data['referrer'],
		);

		$result = self::set_payment_cookie( $order->get_customer_id(), $referrer );
        if ( $result === true ) {
		    return $this->wpo_send_json_success();
        } elseif ( is_wp_error($result) ) {
		    return $this->wpo_send_json_error( $result );
        }
	}

	/**
	 * @param $customer_id int
	 *
	 * @return boolean|WP_Error
	 */
	public static function set_payment_cookie( $customer_id, array $referrer = array() ) {
		$expiration = time() + 172800; // 48 hours

		$cookie          = wp_generate_auth_cookie( $customer_id, $expiration, 'original_user' );
		$customer_result = setcookie( WC_PHONE_CUSTOMER_COOKIE, json_encode( $cookie ), $expiration, COOKIEPATH );
		if ( ! $customer_result ) {
			return new WP_Error( 'cookie_is_not_set', __('Customer cookie set error', 'phone-orders-for-woocommerce') );
		}

		$current_user_id     = get_current_user_id();
		$cookie              = wp_generate_auth_cookie( $current_user_id, $expiration, 'original_user' );
		$current_user_result = setcookie( WC_PHONE_ADMIN_COOKIE, json_encode( $cookie ), $expiration, COOKIEPATH );
		if ( ! $current_user_result ) {
			setcookie( WC_PHONE_CUSTOMER_COOKIE, json_encode( $cookie ), time() - 3600, COOKIEPATH );

			return new WP_Error( 'cookie_is_not_set', __('Current user cookie set error', 'phone-orders-for-woocommerce') );
		}

		if ($referrer) {

		    $current_user_result = setcookie( WC_PHONE_ADMIN_REFERRER_COOKIE, base64_encode(json_encode( $referrer )), $expiration, COOKIEPATH );

		    if ( ! $current_user_result ) {
			return new WP_Error( 'cookie_is_not_set', __('Current user referrer cookie set error', 'phone-orders-for-woocommerce') );
		    }
		}


		return true;
	}

	protected function create_additional_query_args( $data ) {
		$additional_query_args = array();
		$params = isset( $data['additional_parameters'] ) ? $data['additional_parameters'] : array();
		if ( isset( $params ) ) {
			if ( isset( $params['category_slug'] ) ) {
				$additional_query_args['category'] = array( $params['category_slug'] );
			}
			if ( isset( $params['tag_slug'] ) ) {
				$additional_query_args['tag'] = array( $params['tag_slug'] );
			}
		}

		return $additional_query_args;
	}

	protected function ajax_prepare_to_redirect( $data ) {
		WC_Phone_Orders_Loader_Pro::disable_object_cache();
		$where = isset($data['where']) ? $data['where'] : false;
		if ( ! $where ) {
			return $this->wpo_send_json_error( new WP_Error( 'incorrect_parameter', __('Incorrect redirect destination', 'phone-orders-for-woocommerce') ) );
		}

		$cart = isset($data['cart']) ? $data['cart'] : false;
		if ( ! $cart ) {
			return $this->wpo_send_json_error( new WP_Error( 'incorrect_parameter', __('Incorrect cart data', 'phone-orders-for-woocommerce') ) );
		}

		//refresh cart
		$result = $this->update_cart( $cart );
		if ( $result instanceof WC_Data_Exception ) {
			return $this->wpo_send_json_error( $result->getMessage() );
		}
		if ( count( $result['deleted_items'] ) ) {
			return false;
		}

		$customer_id = (integer)$cart['customer']['id'];

		$referrer = array(
		    'is_frontend' => $data['is_frontend'],
		    'url'	  => $data['referrer'],
		);

		$result = self::set_payment_cookie( $customer_id, $referrer );
		if ( $result !== true ) {
			return $this->wpo_send_json_error();
		} elseif ( is_wp_error($result) || $result !== true) {
			return $this->wpo_send_json_error( $result );
		}

		$current_user = get_current_user_id();
		$result       = set_transient( $current_user . '_temp_cart', $cart );

		if ( $result ) {
		    $data = array();
		    if ( $where == 'cart' ) {
		        $data['url'] = wc_get_cart_url();
            } elseif ( $where == 'checkout' ) {
			    $data['url'] = wc_get_checkout_url();
            }
			return $this->wpo_send_json_success($data);
        } else {
		    return $this->wpo_send_json_error();
        }

    }

	protected function create_item( $data ) {
		$product = parent::create_item( $data );

		if ( isset( $data['tax_class']['slug'] ) && $this->option_handler->get_option( 'new_product_ask_tax_class' ) ) {
			$tax_class = $data['tax_class']['slug'];
		} else {
            $tax_class = $this->option_handler->get_option( 'item_tax_class' );
		}

        if ( ! $tax_class ) {
            $product->set_tax_status( 'none' );
        } else {
            $product->set_tax_class( $tax_class );
        }

        $product->save();

		return $product;
	}

	protected function create_customer( $request ) {
		if ( $this->option_handler->get_option( 'disable_creating_customers' ) ) {
			return new WP_Error( 'creating_customers_is_disabled',
				__( 'Creating customers is disabled', 'phone-orders-for-woocommerce' ) );
		}

		if ( $this->option_handler->get_option( 'disable_new_user_email' ) ) {
			remove_action( 'woocommerce_created_customer', array( 'WC_Emails', 'send_transactional_email' ), 10 );
		}

		parse_str( $request['data'], $data );
		$user_id = parent::create_customer( $request );
		if ( is_wp_error( $user_id ) ) {
			return $user_id;
		}

		if ( ! empty( $data['role'] ) ) {
			$user = new WP_User( $user_id );
			$user->set_role( $data['role'] );
		}

		if ( $this->option_handler->get_option( 'dont_fill_shipping_address_for_new_customer' ) ) {
			$shipping_fields = array(
				'first_name',
				'last_name',
				'company',
				'address_1',
				'address_2',
				'city',
				'postcode',
				'country',
				'state',
			);

			foreach ( $shipping_fields as $field ) {
				update_user_meta( $user_id, 'shipping_' . $field, '' );
			}

		}

		return $user_id;
	}

	protected function extract_field_from_option( $option_value ) {
		$custom_fields_options = array();

		if ( $option_value ) {
			foreach ( preg_split( "/((\r?\n)|(\r\n?))/", $option_value ) as $line ) {
				$line = explode( '|', $line );
				if ( count( $line ) > 2 ) {
					$custom_fields_options[ $line[1] ] = $line[2];
				} elseif ( count( $line ) == 2 ) {
					$custom_fields_options[ $line[1] ] = 'text';
				}
			}
		}


		return $custom_fields_options;
	}

}