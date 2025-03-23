<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WC_Phone_Orders_Add_Order_Page extends WC_Phone_Orders_Admin_Abstract_Page {
	public $title;
	public $priority = 10;
	protected $tab_name = 'add-order';

	protected $meta_key_private_note;
	protected $meta_key_order_creator;

	private $subscription_plugin_enabled;

	const ORDER_STATUS_COMPLETED = 'completed';

	public function __construct() {
		parent::__construct();
		$this->title = __( 'Add order', 'phone-orders-for-woocommerce' );
		$this->meta_key_private_note = WC_Phone_Orders_Loader::$meta_key_private_note;
		$this->meta_key_order_creator = WC_Phone_Orders_Loader::$meta_key_order_creator;
		add_action( 'wp_loaded', function () {
			$this->subscription_plugin_enabled = class_exists( 'WC_Subscriptions' ) && class_exists( 'WC_Subscriptions_Product' );
		} );

	}

	public function enqueue_scripts() {
            parent::enqueue_scripts();
	}

	public function action() {
	}

	public function render() {

		$buttons_labels = array(
			'cancelLabel' => __( 'Cancel', 'phone-orders-for-woocommerce' ),
			'applyLabel'  => __( 'Apply', 'phone-orders-for-woocommerce' ),
			'removeLabel' => __( 'Remove', 'phone-orders-for-woocommerce' ),
			'saveLabel'   => __( 'Save', 'phone-orders-for-woocommerce' ),
		);

		$this->tab_data = array(
                    'addCouponModalSettings'     => array(
                        'addCouponLabel'    => __('Add Coupon', 'phone-orders-for-woocommerce'),
                        'typeToSearchLabel' => __('Type to search', 'phone-orders-for-woocommerce'),
                        'noResultLabel'     => __('Oops! No elements found. Consider changing the search query.', 'phone-orders-for-woocommerce'),
                    ),
                    'addCustomItemModalSettings' => array(
	                    'addCustomItemLabel' => __( 'Add custom item', 'phone-orders-for-woocommerce' ),
	                    'skuNameLabel'       => __( 'SKU', 'phone-orders-for-woocommerce' ),
	                    'taxClassLabel'      => __( 'Tax class', 'phone-orders-for-woocommerce' ),
	                    'itemTaxClasses'     => $this->make_tax_classes(),
	                    'lineItemNameLabel'  => __( 'Line item name', 'phone-orders-for-woocommerce' ),
	                    'pricePerItemLabel'  => __( 'Price per item', 'phone-orders-for-woocommerce' ),
	                    'quantityLabel'      => __( 'Quantity', 'phone-orders-for-woocommerce' ),
                    ),
                    'addCustomerModalSettings'   => array(
	                    'fieldsToShow'                 => $this->make_customer_fields_to_show_visibility( $this->make_customer_fields_to_show() ),
	                    'saveCustomerLabel'            => __( 'Save customer', 'phone-orders-for-woocommerce' ),
	                    'addCustomerLabel'             => __( 'New customer', 'phone-orders-for-woocommerce' ),
	                    'selectPlaceholder'            => __( 'Select option', 'phone-orders-for-woocommerce' ),
	                    'autocompleteInputPlaceholder' => __( 'Input your address', 'phone-orders-for-woocommerce' ),
	                    'autocompleteInvalidMessage'   => __( 'Please, enter valid Places API key at tab Settings', 'phone-orders-for-woocommerce' ),
	                    'rolesList'                    => $this->make_roles_list(),
                    ),
                    'addDiscountModalSettings'   => array(
                        'addDiscountLabel' => __('Add discount', 'phone-orders-for-woocommerce'),
                        'discountTypes'    => array(
                            array(
                                'text'  => get_woocommerce_currency_symbol(),
                                'value' => 'fixed_cart',
                            ),
                            array(
                                'text'  => '%',
                                'value' => 'percent',
                            )
                        ),
                        'discountType'     => 'fixed_cart', // discount type by default
                        'discountValue'    => 10, // discount value by default
                    ),
                    'addFeeModalSettings'        => array(
                        'addFeeLabel'    => __('Add Fee', 'phone-orders-for-woocommerce'),
                        'feeNameLabel'   => __('Fee name', 'phone-orders-for-woocommerce'),
                        'feeAmountLabel' => __('Fee amount', 'phone-orders-for-woocommerce'),
                    ),
                    'shippingModalSettings'      => array(
                        'shippingMethodLabel'             => __('Shipping method', 'phone-orders-for-woocommerce'),
                        'noShippingMethodsAvailableLabel' => __('No shipping methods available', 'phone-orders-for-woocommerce'),
                        'shippingMethods'                 => $this->get_shipping_rates(),
                        'selectedShippingMethod'          => '',
                        'currencySymbol'                  => get_woocommerce_currency_symbol(),
                    ),
                    'editAddressModalSettings'   => array(
                        'editAddressLabel'             => __('Edit address', 'phone-orders-for-woocommerce'),
                        'saveAddressLabel'             => __('Save address', 'phone-orders-for-woocommerce'),
                        'addressType'                  => '',
                        'customerId'                   => 0,
                        'selectPlaceholder'            => __('Select option', 'phone-orders-for-woocommerce'),
                        'autocompleteInputPlaceholder' => __('Input your address', 'phone-orders-for-woocommerce'),
                        'autocompleteInvalidMessage'   => __('Please, enter valid Places API key at tab Settings', 'phone-orders-for-woocommerce'),
                    ),
                    'deletedItemLabel'           => __('Product does not exist', 'phone-orders-for-woocommerce'),
                    'outOfStockItemLabel'        => __('Product is out of stock', 'phone-orders-for-woocommerce'),
                );

        array_walk( $this->tab_data, function ( &$item, $key ) use ( $buttons_labels ) {
			if ( is_array( $item ) ) {
				$item = array_merge( $item, $buttons_labels, array( 'tabName' => $this->tab_name ) );
			}
		} );

                $option_handler = $this->option_handler;

                //var_dump($this->get_shipping_rates());die;
        $wc_settings_url = admin_url( 'admin.php?page=wc-settings' );
		$wc_settings_url_html = "<a target='_blank' href=\"{$wc_settings_url}\">" . __( 'Please, enable coupons to use discounts.', 'phone-orders-for-woocommerce' ) . '</a>';

		$order_statuses_list = array();
        foreach ( wc_get_order_statuses() as $i => $status ) {
            $order_statuses_list[] = array(
                'value' => $i,
                'title' => $status,
            );
        }
		$order_statuses_list[] = array(
			'value' => 'draft',
			'title' => __( 'Draft', 'phone-orders-for-woocommerce' ),
		);

		$tab_data = array(
			'findOrCreateCustomerSettings' => array(
				'title'                           => __( 'Find or create a customer', 'phone-orders-for-woocommerce' ),
				'titleOnlyFind'                   => __( 'Find a customer', 'phone-orders-for-woocommerce' ),
				'createNewCustomerLabel'          => __( 'New customer', 'phone-orders-for-woocommerce' ),
				'billingDetailsLabel'             => __( 'Billing Details', 'phone-orders-for-woocommerce' ),
				'shipDifferentLabel'              => __( 'Ship to a different address?', 'phone-orders-for-woocommerce' ),
				'shipDetailsLabel'                => __( 'Shipping Details', 'phone-orders-for-woocommerce' ),
				'emptyBillingAddressMessage'      => __( 'No billing address was provided.', 'phone-orders-for-woocommerce' ),
				'emptyShippingAddressMessage'     => __( 'No shipping address was provided.', 'phone-orders-for-woocommerce' ),
				'billingAddressAsShippingMessage' => __( 'Same as shipping address.', 'phone-orders-for-woocommerce' ),
				'tabName'                         => 'add-order',
				'defaultCustomer'                 => $this->get_customer_by_id( $option_handler->get_option( 'default_customer_id' ) ),
				'isProVersion'                    => WC_Phone_Orders_Loader::is_pro_version(),
				'proFeaturesSettings'             => array(
					'needExtraFeaturesTitle' => __( 'Need extra features?', 'phone-orders-for-woocommerce' ),
					'buyProVersionTitle'     => __( 'Buy Pro version', 'phone-orders-for-woocommerce' ),
				),
				'requiredFieldsForPopUp'          => $this->make_edit_address_fields_to_show(),
				'selectCustomerPlaceholder'       => __( 'Guest', 'phone-orders-for-woocommerce' ),
				'noResultLabel'                   => __( 'Oops! No elements found. Consider changing the search query.', 'phone-orders-for-woocommerce' ),
				'profileUrlTitle'                 => __( 'Profile &rarr;', 'phone-orders-for-woocommerce' ),
				'otherOrderUrlTitle'              => __( 'View other orders &rarr;', 'phone-orders-for-woocommerce' ),
			),
			'orderDetailsSettings'         => array(
				'title'                           => apply_filters( 'wpo_order_details_container_header', '<h2><span>' . __( 'Order details', 'phone-orders-for-woocommerce' ) . '</span></h2>' ),
				'addProductButtonTitle'           => __( 'Add custom item', 'phone-orders-for-woocommerce' ),
				'findProductsSelectPlaceholder'   => __( 'Find products...', 'phone-orders-for-woocommerce' ),
				'productsTableItemColumnTitle'    => __( 'Item', 'phone-orders-for-woocommerce' ),
				'productsTableCostColumnTitle'    => __( 'Cost', 'phone-orders-for-woocommerce' ),
				'productsTableQtyColumnTitle'     => __( 'Qty', 'phone-orders-for-woocommerce' ),
				'productsTableTotalColumnTitle'   => __( 'Total', 'phone-orders-for-woocommerce' ),
				'customerProvidedNoteLabel'       => __( 'Customer provided note', 'phone-orders-for-woocommerce' ),
				'customerProvidedNotePlaceholder' => __( 'Add a note', 'phone-orders-for-woocommerce' ),
				'customerPrivateNoteLabel'        => __( 'Private note', 'phone-orders-for-woocommerce' ),
				'customerPrivateNotePlaceholder'  => __( 'Add a note', 'phone-orders-for-woocommerce' ),
				'subtotalLabel'                   => __( 'Subtotal', 'phone-orders-for-woocommerce' ),
				'addCouponLabel'                  => __( 'Add coupon', 'phone-orders-for-woocommerce' ),
				'addDiscountLabel'                => __( 'Add discount', 'phone-orders-for-woocommerce' ),
				'discountLabel'                   => __( 'Discount', 'phone-orders-for-woocommerce' ),
				'addShippingLabel'                => __( 'Add shipping', 'phone-orders-for-woocommerce' ),
				'shippingLabel'                   => __( 'Shipping', 'phone-orders-for-woocommerce' ),
				'recalculateButtonLabel'          => __( 'Recalculate', 'phone-orders-for-woocommerce' ),
				'taxLabel'                        => __( 'Taxes', 'phone-orders-for-woocommerce' ),
				'currencySymbol'                  => get_woocommerce_currency_symbol(),
				'orderTotalLabel'                 => __( 'Order Total', 'phone-orders-for-woocommerce' ),
				'createOrderButtonLabel'          => __( 'Create order', 'phone-orders-for-woocommerce' ),
				'viewOrderButtonLabel'            => __( 'View order', 'phone-orders-for-woocommerce' ),
				'sendOrderButtonLabel'            => __( 'Send invoice', 'phone-orders-for-woocommerce' ),
				'createNewOrderLabel'             => __( 'Create new order', 'phone-orders-for-woocommerce' ),
				'payOrderNeedProVersionMessage'   => __( 'Want to pay order as customer?', 'phone-orders-for-woocommerce' ),
				'buyProVersionMessage'            => __( 'Buy Pro version', 'phone-orders-for-woocommerce' ),
				'tabName'                         => 'add-order',
				'isProVersion'                    => WC_Phone_Orders_Loader::is_pro_version(),
				'logRowID'                        => uniqid(),
				'noResultLabel'                   => __( 'Oops! No elements found. Consider changing the search query.', 'phone-orders-for-woocommerce' ),
				'productItemLabels'               => array(
					'deleteProductItemButtonTooltipText' => __( 'Delete item', 'phone-orders-for-woocommerce' ),
					'skuLabel'                           => __( 'SKU', 'phone-orders-for-woocommerce' ),
					'productStockMessage'                => __( 'Only %s items can be purchased', 'phone-orders-for-woocommerce' ),
					'variationIDLabel'                   => __( 'Variation ID', 'phone-orders-for-woocommerce' ),
					'productCustomMetaFieldsLabels'      => array(
						'editMetaLabel'                => __( 'Edit meta', 'phone-orders-for-woocommerce' ),
						'productCustomMetaFieldLabels' => array(
							'chooseOptionLabel'          => __( 'Choose meta field', 'phone-orders-for-woocommerce' ),
							'addMetaLabel'               => __( 'Add meta', 'phone-orders-for-woocommerce' ),
							'customMetaValuePlaceholder' => __( 'Custom meta field value', 'phone-orders-for-woocommerce' ),
							'customMetaKeyPlaceholder'   => __( 'Custom meta field key', 'phone-orders-for-woocommerce' ),
							'cancelEditMetaLabel'        => __( 'Сollapse edit meta', 'phone-orders-for-woocommerce' ),
						),
					),
					'productMissingAttributeLabels'      => array(
						'chooseOptionLabel' => __( 'Choose an option', 'phone-orders-for-woocommerce' ),
					),
				),
				'couponsEnabled'                  => wc_coupons_enabled(),
				'activateCouponsLabel'            => $wc_settings_url_html,
				'chooseMissingAttributeLabel'     => __( 'Please, choose all attributes.', 'phone-orders-for-woocommerce' ),
				'manualDiscountLabel'             => __( 'Manual Discount :', 'phone-orders-for-woocommerce' ),
				'copyCartButtonLabel'             => __( 'Copy url to populate cart', 'phone-orders-for-woocommerce' ),
				'copyCopiedCartButtonLabel'       => __( 'Url has been copied to clipboard', 'phone-orders-for-woocommerce' ),
				'duplicateOrderLabel'             => __( 'Duplicate order', 'phone-orders-for-woocommerce' ),
			),
			'orderDateSettings'            => array(
				'title'                    => apply_filters( 'wpo_order_date_container_header', __( 'Order date', 'phone-orders-for-woocommerce' ) ),
				'currentDateTimeTimestamp' => current_time( 'timestamp' ),
				'timeZoneOffset'           => (int) get_option( 'gmt_offset' ),
				'hourPlaceholder'          => _x( 'h', 'hour placeholder', 'phone-orders-for-woocommerce' ),
				'minutePlaceholder'        => _x( 'm', 'minute placeholder', 'phone-orders-for-woocommerce' ),
			),
			'orderStatusSettings' => array(
				'title'             => apply_filters( 'wpo_order_status_container_header', __( 'Order status', 'phone-orders-for-woocommerce' ) ),
				'orderStatusesList' => $order_statuses_list,
			),
		);

		?>

                <tab-add-order v-bind="<?php echo esc_attr( json_encode( $this->tab_data ) ) ?>">
                    <?php do_action( 'wpo_find_order' ) ?>
                    <find-or-create-customer slot="find-or-create-customer" v-bind="<?php echo esc_attr(json_encode($tab_data['findOrCreateCustomerSettings'])) ?>">
                        <?php do_action( 'wpo_after_customer_details' ) ?>
                    </find-or-create-customer>
                    <order-details slot="order-details" v-bind="<?php echo esc_attr(json_encode($tab_data['orderDetailsSettings'])) ?>">
                        <?php do_action( 'wpo_before_search_items_field' ) ?>
                        <?php do_action( 'wpo_after_order_items' ) ?>
                        <?php do_action('wpo_order_footer_left_side') ?>
                        <?php do_action( 'wpo_add_fee' ); ?>
                        <?php do_action("wpo_footer_buttons") ?>
                    </order-details>
                    <order-date slot="order-date" v-bind="<?php echo esc_attr(json_encode($tab_data['orderDateSettings'])) ?>" ></order-date>
                    <order-status slot="order-status" v-bind="<?php echo esc_attr(json_encode($tab_data['orderStatusSettings'])) ?>" ></order-status>
                </tab-add-order>
		<?php
	}

	private function make_edit_address_fields_to_show() {
	    $fields = array(
		    'email' => array(
			    'label' => __( 'E-mail', 'phone-orders-for-woocommerce' ),
			    'value' => '',
		    ),
		    'first_name' => array(
			    'label' => __( 'First name', 'phone-orders-for-woocommerce' ),
			    'value' => '',
		    ),
		    'last_name' => array(
			    'label' => __( 'Last name', 'phone-orders-for-woocommerce' ),
			    'value' => '',
		    ),
        );

	    $fields = array_merge( $fields, $this->make_customer_fields_to_show()['billingAddress']['fields'] );

        return $fields;
    }

	private function make_customer_fields_to_show_visibility( $fields ) {
		foreach ( $fields as &$container ) {
			foreach ( $container['fields'] as $field_name => &$field ) {
				$field['visibility'] = true;
			}
		}

		return $fields;
	}

	protected function make_customer_fields_to_show() {
	    $fields = array(
            'common' => array(
                'label' => __( 'Common', 'phone-orders-for-woocommerce' ),
                'fields' => array(
                    'first_name' => array(
                        'label' => __( 'First name', 'phone-orders-for-woocommerce' ),
                        'value' => '',
                    ),
                    'last_name' => array(
	                    'label' => __( 'Last name', 'phone-orders-for-woocommerce' ),
	                    'value' => '',
                    ),
                    'email' => array(
	                    'label' => __( 'E-mail', 'phone-orders-for-woocommerce' ),
	                    'value' => '',
                    ),
                ),
            ),
            'billingAddress' => array(
	            'label' => __( 'Billing address', 'phone-orders-for-woocommerce' ),
	            'fields' => array(
		            'company' => array(
			            'label' => __( 'Company', 'phone-orders-for-woocommerce' ),
			            'value' => '',
		            ),
		            'phone' => array(
			            'label' => __( 'Phone', 'phone-orders-for-woocommerce' ),
			            'value' => '',
		            ),
		            'country' => array(
			            'label' => __( 'Country', 'phone-orders-for-woocommerce' ),
			            'value' => $this->option_handler->get_option( 'default_country' ),
		            ),
		            'address_1' => array(
			            'label' => __( 'Address1', 'phone-orders-for-woocommerce' ),
			            'value' => '',
		            ),
		            'address_2' => array(
			            'label' => __( 'Address2', 'phone-orders-for-woocommerce' ),
			            'value' => '',
		            ),
		            'city' => array(
			            'label' => __( 'City', 'phone-orders-for-woocommerce' ),
			            'value' => $this->option_handler->get_option( 'default_city' ),
		            ),
		            'state' => array(
			            'label' => __( 'State/County', 'phone-orders-for-woocommerce' ),
			            'value' => $this->option_handler->get_option( 'default_state' ),
		            ),
		            'postcode' => array(
			            'label' => __( 'Postcode', 'phone-orders-for-woocommerce' ),
			            'value' => $this->option_handler->get_option( 'default_postcode' ),
		            ),
                )
            ),
        );

	    return $fields;
    }

	protected function ajax_load_items( $request ) {

		if ( ! isset( $request['ids'] ) || ! is_array( $request['ids'] ) ) {
			return $this->wpo_send_json_success( array(
				'items' => array(),
			) );
		}

		$qty = isset( $request['qty'] ) && (int) $request['qty'] > 1 ? (int) $request['qty'] : 1;

		$old_user_id = false;
		if ( ! empty ( $request['customer_id'] ) ) {
			$customer_id = $request['customer_id'];

			if ( $customer_id AND apply_filters( 'wpo_must_switch_cart_user', $this->option_handler->get_option( 'switch_customer_while_calc_cart' ) ) ) {
				$old_user_id = get_current_user_id();
				wp_set_current_user( $customer_id );
			}
			do_action( 'wdp_after_switch_customer_while_calc' );
		}

		$result = array(
			'items' => $this->get_formatted_product_items_by_ids( $request['ids'], $qty ),
		);

		//switch back to admin
		if ( $old_user_id ) {
			wp_set_current_user( $old_user_id );
		}

		return $this->wpo_send_json_success( $result );
	}

	protected function get_formatted_product_items_by_ids( array $ids = array(), $quantity = 1 ) {

            if( $this->option_handler->get_option( 'show_long_attribute_names' ) ) {
                add_filter("woocommerce_product_variation_title_include_attributes", "__return_true");
            }

            $items = array();

            $item_default_custom_meta_fields_option = $this->option_handler->get_option('default_list_item_custom_meta_fields');
            $item_custom_meta_fields                = array();

            if ( $item_default_custom_meta_fields_option ) {
                foreach ( preg_split( "/((\r?\n)|(\r\n?))/", $item_default_custom_meta_fields_option ) as $line ) {
                    $line = explode( '|', $line );
                    if ( count($line) > 1 ) {
                        $item_custom_meta_fields[] = array(
                            'id'         => '',
                            'meta_key'   => $line[0],
                            'meta_value' => $line[1],
                        );
                    }
                }
            }

            foreach ($ids as $item_id) {
                $items[] = $this->get_item_by_product( wc_get_product( $item_id ), array(
                    'quantity'           => (int)$quantity > 1 ? (int)$quantity : 1,
                    'custom_meta_fields' => $item_custom_meta_fields,
                ));
            }

            return $items;
	}

	protected function get_order_item_html( $item ) {
		$option_handler = $this->option_handler;

		$item_id  = $item['variation_id'] ? $item['variation_id'] : $item['product_id'];
		$_product = wc_get_product( $item_id );

		if ( $_product->get_parent_id() ) {
			$product_link = admin_url( 'post.php?post=' . absint( $_product->get_parent_id() ) . '&action=edit' );
		} else {
			$product_link = admin_url( 'post.php?post=' . absint( $item_id ) . '&action=edit' );
		}

		$thumbnail = $_product ? apply_filters( 'woocommerce_admin_order_item_thumbnail',
			$_product->get_image( 'thumbnail', array( 'title' => '' ), false ), $item_id, $item ) : '';
		$tax_data  = empty( $legacy_order ) && $this->is_tax_enabled() ? maybe_unserialize( isset( $item['line_tax_data'] ) ? $item['line_tax_data'] : '' ) : false;
//		$item_total    = ( isset( $item['line_total'] ) ) ? esc_attr( wc_format_localized_price( $item['line_total'] ) ) : '';
//		$item_subtotal = ( isset( $item['line_subtotal'] ) ) ? esc_attr( wc_format_localized_price( $item['line_subtotal'] ) ) : '';

		$item['variation_data'] = $item['variation_id'] ? $_product->get_variation_attributes() : '';
		$item['in_stock']       = $_product->is_on_backorder() ? null : $_product->get_stock_quantity();
		$item['name']           = $_product->get_name();

		if ( $this->is_subscription( $item_id ) ) {
			$item['item_cost']          = wc_format_decimal( $item['item_cost'],
				$option_handler->get_option( 'item_price_precision' ) );
		}

		ob_start();
		include( WC_Phone_Orders_Tabs_Helper::get_views_path() . 'html/html-order-item.php' );
		$html = ob_get_contents();
		ob_end_clean();

		return $html;
	}

	protected function create_item( $data ) {
		$post_id = wp_insert_post( array(
			'post_title'   => $data['name'],
			'post_content' => '',
			'post_status'  => 'publish',
			'post_type'    => 'product',
		));

		wp_set_object_terms( $post_id, 'simple', 'product_type' );

		update_post_meta( $post_id, '_visibility', 'visible' );
		update_post_meta( $post_id, '_stock_status', 'instock' );
		update_post_meta( $post_id, 'total_sales', '0' );
		update_post_meta( $post_id, '_downloadable', 'no' );
		update_post_meta( $post_id, '_virtual', 'no' );
		update_post_meta( $post_id, '_regular_price', '' );
		update_post_meta( $post_id, '_sale_price', '' );
		update_post_meta( $post_id, '_purchase_note', '' );
		update_post_meta( $post_id, '_featured', 'no' );
		update_post_meta( $post_id, '_weight', '' );
		update_post_meta( $post_id, '_length', '' );
		update_post_meta( $post_id, '_width', '' );
		update_post_meta( $post_id, '_height', '' );
		update_post_meta( $post_id, '_sku', $data['sku'] );
		update_post_meta( $post_id, '_product_attributes', array() );
		update_post_meta( $post_id, '_sale_price_dates_from', '' );
		update_post_meta( $post_id, '_sale_price_dates_to', '' );
		update_post_meta( $post_id, '_price', $data['price'] );
		update_post_meta( $post_id, '_sold_individually', '' );
		update_post_meta( $post_id, '_manage_stock', 'no' );
		update_post_meta( $post_id, '_backorders', 'no' );
		update_post_meta( $post_id, '_stock', '' );

		$visibility  = $this->option_handler->get_option( 'product_visibility' ) ? $this->option_handler->get_option( 'product_visibility' ) : 'visible';
		$is_featured = $this->option_handler->get_option( 'is_featured' ) ? $this->option_handler->get_option( 'is_featured' ) : 'no';
		$product = wc_get_product( $post_id );
		if ( ! $product ) {
			die();
		}

		$product->set_catalog_visibility( $visibility );
		$product->set_featured( $is_featured );
		$product->set_regular_price( $data['price'] );
		$product->save();

		return $product;
    }

	protected function ajax_create_item( $request ) {
		if( $this->option_handler->get_option( 'disable_adding_products' ) ) {
			return $this->wpo_send_json_error( __( 'Can not create new product', 'phone-orders-for-woocommerce' ) );
		}

		if ( ! empty( $request['data'] ) ) {
			$product = $this->create_item( $request['data'] );
			$quantity = ! empty( $request['data']['quantity'] ) ? $request['data']['quantity'] : 1;
        } else {
			return $this->wpo_send_json_error( __( 'Missing data', 'phone-orders-for-woocommerce' ) );
		}


		return $this->wpo_send_json_success( array(
			'item' => $this->get_item_by_product( $product, array( 'quantity' => $quantity ) ),
		) );
	}

	/**
	 * @param WC_Product|WC_Product_Variation $product
	 * @param array $item_data
	 *
	 * @return array
	 */
	protected function get_item_by_product( $product, array $item_data = array() ) {

		$item_id = $product->get_id();

		$qty = isset ( $item_data['quantity'] ) && is_numeric( $item_data['quantity'] ) ? $item_data['quantity'] : 1;

		if ( isset( $item_data['item_cost'] ) AND is_numeric( $item_data['item_cost'] ) ) {
			$price_excluding_tax = $item_data['item_cost'];
		} else {
			$price_excluding_tax = (float)$product->get_price();
		}

		if ( isset( $item_data['line_subtotal'] ) AND is_numeric( $item_data['line_subtotal'] ) ) {
			$line_subtotal = $item_data['line_subtotal'];
		} else {
			$line_subtotal = $price_excluding_tax * $qty;
		}

		$item_meta_data = array();
		if ( $product->is_type( 'variation' ) ) {
			$variation_id = $item_id;
			$product_id   = $product->get_parent_id();

			if ( ! empty( $item_data['meta_data'] ) && is_array( $item_data['meta_data'] ) ) {
				foreach ( $item_data['meta_data'] as $meta_datum ) {
					/**
					 * @var WC_Meta_Data $meta_datum
					 */
					$meta                           = $meta_datum->get_data();
					$item_meta_data[ $meta['key'] ] = $meta['value'];
				}
			}
		} else {
			$variation_id = '';
			$product_id   = $item_id;
		}

		$is_subscribed_item = $this->is_subscription( $item_id );

		$item_cost = (float) $price_excluding_tax;

		if ( $is_subscribed_item ) {
			$item_cost = (float) $product->get_price() + WC_Subscriptions_Product::get_sign_up_fee( $product );
		}

		$is_readonly_price = $this->is_readonly_product_price( $item_id );

		$post_id = $product->get_parent_id() ? $product->get_parent_id() : $item_id;

		$missing_variation_attributes = array();
		if ( $variation_id ) {
			$attributes = $product->get_attributes();
			foreach ( $attributes as $attribute => $value ) {
				if ( ! $value ) {
				    $values = array();
					$parent = wc_get_product( $post_id );
					$variable_attributes = $parent->get_attributes();
					if ( ! empty( $variable_attributes[ $attribute ] ) ) {
					    $variable_attribute = $variable_attributes[ $attribute ];

					    if ( $variable_attribute->is_taxonomy() ) {
					        $values =  wc_get_product_terms( $product_id, $attribute );
                        } else {
						    $values = $variable_attribute->get_options();
                        }
					}


					$missing_variation_attributes[] = array(
						'key'    => $attribute,
						'label'  => wc_attribute_label( $attribute, $product ),
						'value' => ! empty( $item_meta_data[ $attribute ] ) ? $item_meta_data[ $attribute ] : "",
						'values' => $values,
					);
				}
			}
		}

		return array(
			'product_id'                   => $product_id,
			'item_cost'                    => $item_cost,
			'product_price_html'           => $product->get_price_html(),
			'variation_id'                 => $variation_id,
			'variation_data'               => $variation_id ? $product->get_variation_attributes() : '',
            'custom_meta_fields'           => isset($item_data['custom_meta_fields']) ? $item_data['custom_meta_fields'] : array(),
			'missing_variation_attributes' => $variation_id ? $missing_variation_attributes : '',
			'name'                         => $product->get_name(),
			'qty'                          => $qty,
			'type'                         => 'line_item',
			'in_stock'                     => $product->is_on_backorder() ? null : $product->get_stock_quantity(),
			'decimals'                     => wc_get_price_decimals(),
			'qty_step'                     => apply_filters( 'woocommerce_quantity_input_step', '1', $product ),
			'is_enabled_tax'               => $this->is_tax_enabled(),
			'is_price_included_tax'        => wc_prices_include_tax(),
			'sku'                          => esc_html( $product->get_sku() ),
			'thumbnail'                    => $this->get_thumbnail_src_by_product($product),
			'product_link'                 => admin_url( 'post.php?post=' . absint( $post_id ) . '&action=edit' ),
			'permalink'		       => $product->get_permalink(),
			'is_subscribed'                => $is_subscribed_item,
			'is_readonly_price'            => $is_readonly_price,
			'line_total_with_tax'          => null,
			'item_cost_with_tax'           => null,
			'sold_individually'            => $product->is_sold_individually(),
			'key'                          => uniqid(),
		);
	}

	protected function ajax_create_customer( $request ) {
		$user_id = $this->create_customer( $request );
		if ( is_wp_error($user_id) ) {
			return $this->wpo_send_json_error( $user_id->get_error_message() );
        }

		return $this->wpo_send_json_success( array(
			'id' => $user_id,
		) );
	}

	protected function create_customer( $request ) {

		parse_str( $request['data'], $data );

		// $user_name = $data['first_name'] . ' ' . $data['last_name'] . rand(1, 1000);

		$fake_email = false;

		if (
			( ! isset( $data['email'] ) || $data['email'] === '' )
			&&
			( $this->option_handler->get_option( 'newcustomer_hide_email' ) || $this->option_handler->get_option( 'newcustomer_email_is_optional' ) ) ) {
			$fake_email = sanitize_email( trim( $data['first_name'] ) . trim( $data['last_name'] ) . '@test.com' );
			if ( ! $fake_email AND ! empty( $data['phone'] ) ) {
				$fake_email = sanitize_email( "Tel." . trim( $data['phone'] ) . '@test.com' );
			}
			if ( ! $fake_email ) { //use date
				$fake_email = sanitize_email( current_time("Ymd") . '@test.com' );
			}
			$fake_email    = apply_filters( "wpo_make_fake_email", $fake_email, $data );
			$data['email'] = $fake_email;
			// don't pass it to Wordpress!
			add_filter( 'pre_user_email', function () {
				return "";
			} );
		}

		$data     = apply_filters( 'wpo_before_create_customer', $data );
		$username = isset( $data['username'] ) ? $data['username'] : "";
		$password = isset( $data['password'] ) ? $data['password'] : "";
		$user_id  = wc_create_new_customer( $data['email'], $username, $password );
		do_action( 'wpo_after_create_customer', $user_id, $data );
		if ( is_wp_error( $user_id ) ) {
		    return $user_id;
		}

		if ( $fake_email ) {
			$data['email'] = '';
		}

		update_user_meta( $user_id, 'first_name', $data['first_name'] );
		update_user_meta( $user_id, 'last_name', $data['last_name'] );

		$billing_fields = array(
			'email',
			'first_name',
			'last_name',
			'company',
			'address_1',
			'address_2',
			'city',
			'postcode',
			'country',
			'state',
			'phone',
		);
		foreach ( $billing_fields as $field ) {
			update_user_meta( $user_id, 'billing_' . $field, $data[ $field ] );
			if ( $field != "phone" AND $field != "email" ) {
				update_user_meta( $user_id, 'shipping_' . $field, $data[ $field ] );
			}
		}

		return $user_id;
	}

	protected function ajax_update_customer( $request ) {

		//parse_str( $request['data'], $data );

		$customer_data = ( isset( $request['customer_data'] ) && is_array( $request['customer_data'] ) ) ? $request['customer_data'] : array();
		$id = isset( $customer_data['id'] ) ? $customer_data['id'] : '0';

		$customer_data = $this->update_customer( $id, $customer_data );
		if ( $customer_data instanceof WC_Data_Exception ) {
			return $this->wpo_send_json_error( $customer_data->getMessage() );
		} else {
			$customer = array();

			foreach ( WC()->customer->get_billing() as $key => $value ) {
				$customer[ 'billing_' . $key ] = $value;
			}

            foreach ( WC()->customer->get_shipping() as $key => $value ) {
                $customer[ 'shipping_' . $key ] = $value;
            }

			$customer['ship_different_address'] = $customer_data['ship_different_address'];

			$customer['formatted_billing_address']  = WC()->countries->get_formatted_address( WC()->customer->get_billing() );
			$customer['formatted_shipping_address'] = WC()->countries->get_formatted_address( WC()->customer->get_shipping() );

			$customer['id'] = $id;
			$customer['is_vat_exempt'] = WC()->customer->is_vat_exempt();

			$customer['other_order_url'] = $this->get_customer_other_order_url( $id );
			$customer['profile_url']     = $this->get_customer_profile_url( $id );

			$customer = apply_filters( 'wpo_after_update_customer', $customer );

			return $this->wpo_send_json_success( array( 'customer' => $customer ) );
		}
	}

	protected function update_customer( $id, $customer_data ) {
		if ( isset( $customer_data['ship_different_address'] ) ) {
			// string 'false' to boolean false, otherwise boolean true
			$customer_data['ship_different_address'] = ! ( $customer_data['ship_different_address'] === 'false' || $customer_data['ship_different_address'] === false );
		} else {
			$customer_data['ship_different_address'] = false;
        }
        // missed state/country ?
		$this->try_set_default_state_country( $customer_data, 'billing' );
		if ( $customer_data['ship_different_address'] )
			$this->try_set_default_state_country( $customer_data, 'shipping' );

		if ( $id ) {
			WC()->customer = new WC_Customer( $id );
		} else {
			WC()->customer = new WC_Customer();
		}
		$cart_customer = WC()->customer;


		try {
                    $exclude = array('ship_different_address');
                    foreach ($customer_data as $field => $value) {
                        if (in_array($field, $exclude)) {
                            continue;
                        }

                        $method = 'set_' . $field;

                        if (!$customer_data['ship_different_address']) { // shipping == billing
                            $field = str_replace('shipping_', 'billing_', $field);
                        }

                        if (method_exists($cart_customer, $method)) {
                            $cart_customer->$method($customer_data[$field]);
                        }
                    }
                } catch (WC_Data_Exception $e) {
                    return $e;
                }

        // fix shipping not applied to totals after WC 3.5 release
		WC()->customer->set_calculated_shipping( true );

		$cart_customer->apply_changes();

		do_action( "wpo_set_cart_customer", $cart_customer, $id, $customer_data );
		return $customer_data;
	}

	protected function try_set_default_state_country( &$customer_data, $type ) {
		if( empty($customer_data[$type . '_country']) ) {
			$location = wc_get_customer_default_location();
			$customer_data[$type . '_state'] = $location['state'];
			$customer_data[$type . '_country'] = $location['country'];
		}
	}


	protected function ajax_get_formatted_address( $request ) {
		$customer_data = isset( $request['data'] ) ? json_decode(stripslashes($request['data']), true) : array();

		$result        = $this->get_formatted_address( $customer_data );

		return $this->wpo_send_json_success( $result );
	}

	protected function get_formatted_address( $customer_data ) {
		$address         = array(
			'first_name' => isset( $customer_data['billing_first_name'] ) ? $customer_data['billing_first_name'] : '',
			'last_name'  => isset( $customer_data['billing_last_name'] ) ? $customer_data['billing_last_name'] : '',
			'company'    => isset( $customer_data['billing_company'] ) ? $customer_data['billing_company'] : '',
			'address_1'  => isset( $customer_data['billing_address_1'] ) ? $customer_data['billing_address_1'] : '',
			'address_2'  => isset( $customer_data['billing_address_2'] ) ? $customer_data['billing_address_2'] : '',
			'city'       => isset( $customer_data['billing_city'] ) ? $customer_data['billing_city'] : '',
			'state'      => isset( $customer_data['billing_state'] ) ? $customer_data['billing_state'] : '',
			'postcode'   => isset( $customer_data['billing_postcode'] ) ? $customer_data['billing_postcode'] : '',
			'country'    => isset( $customer_data['billing_country'] ) ? $customer_data['billing_country'] : '',
		);

		$billing_data = array();
		$shipping_data = array();

		foreach ( $customer_data as $key => $data ) {
			if ( preg_match( '"^(billing_)(.+)"', $key, $matches ) === 1 ) {
				$billing_data[ $matches[2] ] = $data;
			} elseif ( preg_match( '"^(shipping_)(.+)"', $key, $matches ) === 1 ) {
				$shipping_data[ $matches[2] ] = $data;
			}
		}


		$billing_address = WC()->countries->get_formatted_address( $billing_data );
		$shipping_address = WC()->countries->get_formatted_address( $shipping_data );

		return array(
			'formatted_billing_address' => $billing_address,
			'formatted_shipping_address' => $shipping_address,
		);
    }

	protected function ajax_get_shipping_rates( $data ) {
		$result = $this->update_cart( $data['cart'] );
		if ( $result instanceof WC_Data_Exception ) {
			return $this->wpo_send_json_error( $result->getMessage() );
		}
		$order_id = $this->get_frontend_order_id( $data );
		WC_Phone_Orders_Tabs_Helper::add_log( $data['log_row_id'], $result, $order_id );
		if ( isset( $data['cart']['order_id'] ) AND $data['cart']['order_id'] ) {
			WC()->cart->empty_cart();
		}
		return $this->wpo_send_json_success( $result['shipping'] );
	}

	protected function update_cart( $data ) {
		if ( ! defined( 'WOOCOMMERCE_CART' ) ) {
			define( 'WOOCOMMERCE_CART', 1 );
		}
		$cart_data = wp_parse_args( $data, array(
			'customer' => array(),
			'items'    => array(),
			'coupons'  => array(),
			// 'taxes'    => array(),
			'discount' => null,
			'shipping' => null,
		) );

		WC()->cart->empty_cart();

		$old_user_id = false;
		// customer
		if ( ! empty ( $cart_data['customer'] ) ) {
			$customer_data = $cart_data['customer'];

			$id = isset($customer_data['id']) ? $customer_data['id'] : 0;
			$update_customer_result = $this->update_customer( $id, $customer_data );
			if ( $update_customer_result instanceof WC_Data_Exception ) {
				return $update_customer_result;
			}
			if( $id AND apply_filters('wpo_must_switch_cart_user',  $this->option_handler->get_option( 'switch_customer_while_calc_cart' ) ) ) {
				$old_user_id = get_current_user_id();
				wp_set_current_user($id);
			}
			do_action( 'wdp_after_switch_customer_while_calc' );
		} else {
			WC()->customer->set_calculated_shipping( true );//required since 3.5!
		}

		WC()->shipping->reset_shipping();
		wc_clear_notices(); // suppress front-end messages
		// Suppress total recalculation until finished.
		remove_action( 'woocommerce_add_to_cart', array( WC()->cart, 'calculate_totals' ), 20, 0 );

		do_action("wpo_before_update_cart");

		$deleted_cart_items = array();

		//ignore stock status??
		if ( $this->option_handler->get_option( 'sale_backorder_product' ) ) {
			add_filter( 'woocommerce_product_is_in_stock', '__return_true' );
			add_filter( 'woocommerce_product_backorders_allowed', '__return_true' );
		}

		// items
		$cart_item_key___original_item = array();
		foreach ( $cart_data['items'] as $item ) {
		    if ( ! empty($item['wpo_skip_item']) ) {
                continue;
            }
            if ( empty( $item['variation_id'] ) ) { // required field for checkout
				$item['variation_data'] = array();
				$product = wc_get_product( $item['product_id'] );
			} else {
				if ( ! isset($item['variation_data']) OR ! count($item['variation_data']) ) {
					$item['variation_data'] = isset($item['variation']) ? $item['variation'] : array();
				}

				$missing_variation_attributes = isset( $item['missing_variation_attributes'] ) && is_array( $item['missing_variation_attributes'] ) ? $item['missing_variation_attributes'] : array();

				foreach ( $missing_variation_attributes as $attribute ) {
					$slug = $attribute['key'];

					if ( empty( $item['variation_data'][ $slug ] ) ) {
						$item['variation_data'][ 'attribute_' . $slug ] = $attribute['value'];
					}
				}

				$product = wc_get_product( $item['variation_id'] );
			}

            $item_custom_meta_fields = isset($item['custom_meta_fields']) && is_array($item['custom_meta_fields']) ? $item['custom_meta_fields'] : array();

            $item['custom_meta_fields'] = $item_custom_meta_fields;

			$item = apply_filters("wpo_prepare_item", $item, $product);

			if ( ! $product or - 1 == $item['product_id'] ) {
				continue;
			}

			if ( '' === $product->get_regular_price() AND ! $this->option_handler->get_option( 'hide_products_with_no_price' ) ) {
				$product->set_price( '0' );
				$product->set_regular_price( '0' );
				$product->save();
//				$item['item_cost'] = 0;
			}

			if ( $item['qty'] < 1 ) {
				$error                                     = __( 'Incorrect quantity value',
					'phone-orders-for-woocommerce' );
				$deleted_cart_items[] = array(
                                    'id'   => $item['product_id'],
                                    'name' => isset( $item['name'] ) ? $item['name'] : $product->get_name(),
                                );
				WC()->session->set( 'wc_notices', array() );
				continue;
			}

			$cart_item_meta		   = defined('WC_ADP_VERSION') ? array() : array( 'rand' => rand() );
			$cart_item_meta['wpo_key'] = isset($item['key']) ? $item['key'] : '';

			$cart_item_key = WC()->cart->add_to_cart( $item['product_id'], $item['qty'], $item['variation_id'],
				$item['variation_data'],  $cart_item_meta);
			if ( $cart_item_key ) {
				if ( ! ( $this->is_subscription( $product->get_id() ) ) ) {
					if ( $item['item_cost'] != $product->get_price() ) {
						WC()->cart->get_cart()[ $cart_item_key ]['data']->set_price( $item['item_cost'] );
					}
				}
				$cart_item_key___original_item[ $cart_item_key ] = $item;
//				WC()->cart->cart_contents[ $cart_item_key ] = apply_filters( 'wdp_after_cart_item_add', WC()->cart->cart_contents[ $cart_item_key ], $item );;
                WC()->cart->get_cart()[ $cart_item_key ]['data']->custom_meta_fields = $item['custom_meta_fields'];

			} else {

				$error                                     = htmlspecialchars_decode( WC()->session->get( 'wc_notices',
					array() )['error'][0] );

				$deleted_cart_items[] = array(
                                    'id'   => $item['product_id'],
                                    'name' => $item['name'],
                                    'key' => ! empty( $item['key'] ) ? $item['key'] : false,
                                );

				WC()->session->set( 'wc_notices', array() );
			}
		};

		WC()->cart->calculate_totals();
		if ( ! wc_prices_include_tax() ) {
			foreach ( WC()->cart->get_cart() as $cart_item_key => $item ) {
				if ( isset( $cart_item_key___original_item[ $cart_item_key ] ) ) {
					$cart_item_key___original_item[ $cart_item_key ]['line_subtotal_after_add_to_cart']          = wc_format_decimal( $item['line_subtotal'] );
					$cart_item_key___original_item[ $cart_item_key ]['line_subtotal_with_tax_after_add_to_cart'] = wc_format_decimal( $item['line_subtotal'] + $item['line_subtotal_tax'] );
				}
			}
		}

		//fee
		if ( isset( $cart_data['fee'] ) && is_array( $cart_data['fee'] ) ) {
			$fees_data = $cart_data['fee'];
			$tax_class = $this->option_handler->get_option( 'fee_tax_class' );
            add_action( 'woocommerce_cart_calculate_fees', function () use ( $fees_data, $tax_class ) {
				foreach ( $fees_data as $index => $fee_data ) {
                    WC()->cart->add_fee( $fee_data['name'], $fee_data['amount'], (boolean) $tax_class, $tax_class );
				}
			} );
		}


		// shipping
		$chosen_shipping_methods = array();
		if ( isset( $cart_data['shipping'] ) && is_array( $cart_data['shipping'] ) ) {
			$chosen_shipping_methods = array( wc_clean( $cart_data['shipping']['id'] ) );
			foreach ( WC()->shipping->get_packages() as $index => $value ) {
				WC()->session->set( 'shipping_for_package_' . $index, '' );
			}

		}

		WC()->session->set( 'chosen_shipping_methods', $chosen_shipping_methods );

		//new cart ready
		do_action( 'woocommerce_cart_loaded_from_session', WC()->cart );

		// coupons
		WC()->cart->applied_coupons = array();
		foreach ( $cart_data['coupons'] as $item ) {
			WC()->cart->add_discount( $item['title'] );
		}

		// discount as another coupon
		$manual_cart_discount_code = strtolower( $this->option_handler->get_option( 'manual_coupon_title' ) );
		if ( ! empty( $cart_data['discount'] ) ) {
			$discount = $cart_data['discount'];
			if ( empty( $discount['type'] ) ) {
				$discount['type'] = 'fixed_cart';
			}
			//create new coupon via action
			add_action( 'woocommerce_get_shop_coupon_data',
				function ( $manual, $coupon ) use ( $discount, $manual_cart_discount_code ) {
					if ( $coupon != $manual_cart_discount_code ) {
						return $manual;
					}

					// fake coupon here
					return array( 'amount' => $discount['amount'], 'discount_type' => $discount['type'], 'id' => - 1 );
				}, 10, 2 );
			WC()->cart->add_discount( $manual_cart_discount_code );
		}

		$chosen_shipping_methods = WC()->cart->calculate_shipping();
		$chosen_shipping_method  = null;

               // var_dump($chosen_shipping_methods);die;

		if ( count( $chosen_shipping_methods ) ) {
		    $temp_chosen_shipping_method = reset($chosen_shipping_methods);
			$chosen_shipping_method = array(
				'id'             => $temp_chosen_shipping_method->get_id(),
				'label'          => $temp_chosen_shipping_method->get_label(),
				'cost'           => $temp_chosen_shipping_method->get_cost(),
				'full_cost'      => $temp_chosen_shipping_method->get_cost() + $temp_chosen_shipping_method->get_shipping_tax(),
				'total_html'     => WC()->cart->get_cart_shipping_total(), // only for logging
			);
		}

		WC()->cart->calculate_totals();

		$manual_discount_value = 0;
		$applied_coupons = array();
		$coupon_amounts  = WC()->cart->get_coupon_discount_totals();

		foreach ( $coupon_amounts as $coupon_code => $amount ) {
			if ( $coupon_code != $manual_cart_discount_code ) {
				$coupon = new WC_Coupon( $coupon_code );
				$applied_coupons[] = array(
					'title' => get_post( $coupon->get_id())->post_title,
                                        'amount' => $amount,
                                );
			} else {
				$manual_discount_value = $amount;
			}
		}

        $fees         = array();
		$applied_fees = array();

		foreach ( WC()->cart->get_fees() as $fee_id => $fee_data ) {

                    $fees[ $fee_data->name ]['amount'] = wc_price( $fee_data->amount );
                    $fees[ $fee_data->name ]['amount_with_tax'] = wc_price( $fee_data->amount + $fee_data->tax );

                    $applied_fees[] = array(
                        'name'            => $fee_data->name,
                        'amount'          => (float)$fee_data->amount,
                        'amount_with_tax' => (float)($fee_data->amount + $fee_data->tax),
                    );
                }

                //var_dump(WC()->cart->get_totals());die;

		$items    = array();
		$subtotal = 0;
		$subtotal_with_tax = WC()->cart->get_cart_contents_total() + WC()->cart->get_cart_contents_tax();
		foreach ( WC()->cart->get_cart() as $cart_key => $item ) {
            $product_id = $item['variation_id'] ? $item['variation_id'] : $item['product_id'];
            $product = wc_get_product($product_id);
			$item['qty'] = $item['quantity'];
			$item['sold_individually'] = $product->is_sold_individually();
			$item['is_readonly_price'] = $this->is_readonly_product_price( $product_id );
			if ( isset( $cart_item_key___original_item[ $cart_key ] ) ) {
				$item['item_cost'] = wc_format_decimal( $cart_item_key___original_item[ $cart_key ]['item_cost'] );
			} else {
				$item['item_cost'] = wc_format_decimal( $item['line_subtotal'] / $item['qty'] );
			}

			// price before pricing plugin was applied
			// will show price as wc_format_sale_price($item['original_price'], $item['price']) without wc_price()
			$item['original_price'] = $this->get_original_price( $item );

//			if ( ! empty( $cart_item_key___original_item[ $cart_key ]['key'] ) ) {
//				$item['key'] = $cart_item_key___original_item[ $cart_key ]['key'];
//            } else {
				$loaded_products = $this->get_formatted_product_items_by_ids(array($product_id), $item['qty']);
				if ( ! empty($loaded_products) ) {
					$item['loaded_product']                       = reset( $loaded_products );
					$item['loaded_product']['wpo_skip_item']      = apply_filters( 'wpo_skip_add_to_cart_item', ! empty( $item['wpo_skip_item'] ), $item );

					$item['loaded_product']['wpo_readonly_child_item'] = apply_filters( 'wpo_readonly_child_cart_item', false, $item );

					$key                                          = uniqid( $item['item_cost'] );
					$item['key']                                  = $key;
					$item['loaded_product']['key']                = $key;
					$item['loaded_product']['item_cost']          = $item['item_cost'];
					$item['loaded_product']['custom_meta_fields'] = $item['data']->custom_meta_fields;
					$item['loaded_product']['variation_data']     = $item['variation'];
					if ( ! empty( $item['loaded_product']['missing_variation_attributes'] ) ) {
						foreach ( $item['loaded_product']['missing_variation_attributes'] as &$attribute ) {
							if ( isset( $item['variation'][ 'attribute_' . $attribute['key'] ] ) ) {
								$attribute['value'] = $item['variation'][ 'attribute_' . $attribute['key'] ];
							}
						}
					}
				}
//            }

			if ($this->is_tax_enabled()) {
				if ( ! wc_prices_include_tax() ) {
					if ( isset( $cart_item_key___original_item[ $cart_key ] ) ) {
						$item['line_subtotal_after_add_to_cart']          = wc_format_decimal( $cart_item_key___original_item[ $cart_key ]['line_subtotal_after_add_to_cart'] );
						$item['line_subtotal_with_tax_after_add_to_cart'] = wc_format_decimal( $cart_item_key___original_item[ $cart_key ]['line_subtotal_with_tax_after_add_to_cart'] );
					} else {
						$item['line_subtotal_after_add_to_cart'] = wc_format_decimal( $item['line_subtotal']);
						$item['line_subtotal_with_tax_after_add_to_cart'] = wc_format_decimal( $item['line_subtotal'] + $item['line_subtotal_tax']);
					}
				}
				$item['item_cost_with_tax']  = wc_get_price_including_tax(wc_get_product($product_id), array('qty' => 1, 'price' => $item['item_cost']));
				$item['line_total_with_tax'] = $item['line_total'] + $item['line_tax'];
			}

			$items[] = $item;
			if ( $this->is_tax_enabled() AND $item['line_tax'] ) {
				if ( ! wc_prices_include_tax() ) {
					$subtotal += $item['line_subtotal_after_add_to_cart'];
				} else {
					$subtotal += $item['line_total'];
				}
			} else {
				$subtotal += $item['line_total'];
			}
		}

		//switch back to admin
		if( $old_user_id ) {
			wp_set_current_user( $old_user_id );
		}

		do_action( 'wpo_cart_updated' );

		return array(
			'subtotal'               => $subtotal,
			'subtotal_with_tax'      => $subtotal_with_tax,
			'taxes'                  => WC()->cart->get_taxes_total(),
			'total'                  => (float) WC()->cart->get_total( 'edit' ),
			'total_ex_tax'           => max( 0, WC()->cart->get_total( 'edit' ) - WC()->cart->get_total_tax() ),
			'discount'               => WC()->cart->get_discount_total(),
			'discount_amount'        => $manual_discount_value,
			'items'                  => $items,
			'shipping'               => $this->get_shipping_rates(),
			'chosen_shipping_method' => $chosen_shipping_method,
			'deleted_items'          => $deleted_cart_items,
			'applied_coupons'        => $applied_coupons,
			'applied_fees'           => $applied_fees,
			'fees'                   => $fees, // only for logging
		);
    }

	protected function get_cart_total() {
		$value = '<strong>' . WC()->cart->get_total() . '</strong> ';

		// If prices are tax inclusive, show taxes here
//		if ( wc_tax_enabled() && WC()->cart->tax_display_cart == 'incl' ) {
//			$tax_string_array = array();
//
//			if ( get_option( 'woocommerce_tax_total_display' ) == 'itemized' ) {
//				foreach ( WC()->cart->get_tax_totals() as $code => $tax ) {
//					$tax_string_array[] = sprintf( '%s %s', $tax->formatted_amount, $tax->label );
//				}
//			} else {
//				$tax_string_array[] = sprintf( '%s %s', wc_price( WC()->cart->get_taxes_total( true, true ) ),
//					WC()->countries->tax_or_vat() );
//			}
//
//			if ( ! empty( $tax_string_array ) ) {
//				$taxable_address = WC()->customer->get_taxable_address();
//				$estimated_text  = WC()->customer->is_customer_outside_base() && ! WC()->customer->has_calculated_shipping()
//					? sprintf( ' ' . __( 'estimated for %s', 'woocommerce' ),
//						WC()->countries->estimated_for_prefix( $taxable_address[0] ) . WC()->countries->countries[ $taxable_address[0] ] )
//					: '';
//				$value           .= '<small class="includes_tax">' . sprintf( __( '(includes %s)', 'woocommerce' ),
//						implode( ', ', $tax_string_array ) . $estimated_text ) . '</small>';
//			}
//		}

		return apply_filters( 'woocommerce_cart_totals_order_total_html', $value );
	}

	protected function get_shipping_rates() {
		WC()->shipping->load_shipping_methods();
		$packages              = WC()->shipping->get_packages();
		$shipping_rates_result = array();

               // var_dump(WC()->shipping);die;

		foreach ( $packages as $package ) {
			if ( isset( $package['rates'] ) ) {
               //var_dump($package['rates']);die;
				$shipping_rates = array_map( function ( $rate ) {
					$fields = array(
						'id'             => $rate->get_id(),
						'label'          => $rate->get_label(),
						'cost'           => $rate->get_cost(),
						'full_cost'      => $rate->get_cost() + $rate->get_shipping_tax(),
					);

					return $fields;
				}, $package['rates'] );

				$shipping_rates_result = array_merge( $shipping_rates_result, $shipping_rates );
			}
		}

		return array_values( $shipping_rates_result );
	}

	// Sees if the customer has entered enough data to calc the shipping yet.

	protected function ajax_create_order( $data ) {
		$order_id = $this->create_order( $data );
		if ( ! $order_id ) {
//			return $this->wpo_send_json_error( __( 'Recalculate cart changed cart items', 'phone-orders-for-woocommerce' ) );
			return $this->wpo_send_json_error();
		}

		$order       = wc_get_order( $order_id );
		$payment_url = $order->get_checkout_payment_url();

		$message = sprintf( __( 'Order #%s created', 'phone-orders-for-woocommerce' ), $order_id );

		$result = array(
                    'order_id'     => $order_id,
                    'message'      => $message,
                    'payment_url'  => $payment_url,
                    'is_completed' => $order->get_status() === self::ORDER_STATUS_COMPLETED,
                );

                return $this->wpo_send_json_success( $result );
	}

	protected function create_order( $data, $set_status = true ) {
		$option_handler = $this->option_handler;
		$cart = $data['cart'];
		add_filter( 'woocommerce_checkout_customer_id', function ( $user_id ) use ( $cart ) {
			return ! empty( $cart['customer']['id'] ) ? $cart['customer']['id'] : 0;
		} );

		//refresh cart
		$result = $this->update_cart( $cart );
		if ( $result instanceof WC_Data_Exception ) {
			return $this->wpo_send_json_error( $result->getMessage() );
		}
		if ( count( $result['deleted_items'] ) ) {
			return false;
		}
		// checkout needs customer fields!
		$use_shipping_address = (isset( $cart['customer']['ship_different_address'] ) AND 'true' == $cart['customer']['ship_different_address']);
		$checkout_data        = array();
		foreach ( $cart['customer'] as $key => $value ) {
			if ( stripos( $key, 'billing_' ) !== false ) {
				$checkout_data[ $key ] = $value;
				if ( ! $use_shipping_address ) // use billing details as delivery address
				{
					$checkout_data[ str_replace( 'billing_', 'shipping_', $key ) ] = $value;
				}
			} elseif ( $use_shipping_address AND stripos( $key, 'shipping_' ) !== false ) {
				$checkout_data[ $key ] = $value;
			}
		}

        add_action('woocommerce_checkout_create_order_line_item', array($this, 'action_woocommerce_checkout_create_order_line_item'), 10, 4);

		//remap incoming note
		$checkout_data['order_comments'] = ! empty( $cart['customer_note'] ) ? $cart['customer_note'] : "";
		//force "Cash On delivery"
		$checkout_data['payment_method'] = $option_handler->get_option( 'order_payment_method' );
		$checkout                        = new WC_Checkout();
		$order_id                        = $checkout->create_order( $checkout_data );

        remove_action('woocommerce_checkout_create_order_line_item', array($this, 'action_woocommerce_checkout_create_order_line_item'));

        WC_Phone_Orders_Tabs_Helper::add_log( $data['log_row_id'], $result, $order_id );

		$order    = wc_get_order( $order_id );
		if ( is_wp_error( $order_id ) ) {
			return $this->wpo_send_json_error( $order_id->get_error_message() );
		}

		do_action( 'woocommerce_checkout_order_processed', $order_id, $checkout_data, $order );
		$order    = wc_get_order( $order_id );

		if ( isset( $cart['private_note'] ) and ! empty( $cart['private_note'] ) ) {
			$order->add_order_note( $cart['private_note'] );
			if ( ! add_post_meta( $order_id, $this->meta_key_private_note, $cart['private_note'], true ) ) {
				update_post_meta( $order_id, $this->meta_key_private_note, $cart['private_note'] );
			}
		}

		$created_date_time = ! empty( $data['created_date_time'] ) ? (int) $data['created_date_time']: null;
		if ( is_integer( $created_date_time ) ) {
			$order->set_date_created( $data['created_date_time'] );
			$order->save();
        }

        // external plugins can filter orders by creator
		update_post_meta( $order_id, $this->meta_key_order_creator, get_current_user_id() );

		WC()->cart->empty_cart();

		// set status ?
		if ( ! empty( $data['order_status'] ) && $set_status ) {
			$new_status = $data['order_status'];

			if ( $new_status AND $new_status != get_post_status( $order_id ) ) {

				$_new_status = wc_is_order_status( 'wc-' . get_post_status( $order_id ) ) ? 'wc-' . $new_status : $new_status;

				$order->set_status($_new_status);

				$order->save();
			}
		}

		// set status ?
		/*if ( $this->option_handler->get_option( 'order_status' ) ) {
			$order->update_status( $this->option_handler->get_option( 'order_status' ) );
		}*/

		if ($order->get_status() === self::ORDER_STATUS_COMPLETED) {
			do_action( 'woocommerce_payment_complete', $order->get_id() );
		}

		if ( ! empty( $cart['discount'] ) ) {
			$discount = $cart['discount'];
			if ( empty( $discount['type'] ) ) {
				$discount['type'] = 'fixed_cart';
			}
			$manual_cart_discount_code = strtolower( $this->option_handler->get_option( 'manual_coupon_title' ) );
			$result                    = array(
				'code'   => $manual_cart_discount_code,
				'type'   => $discount['type'],
				'amount' => $discount['amount'],
				'id'     => - 1,
			);
			if ( ! add_post_meta( $order_id, $option_handler->get_option( 'manual_coupon_title' ), $result, true ) ) {
				update_post_meta( $order_id, $option_handler->get_option( 'manual_coupon_title' ), $result );
			}
		}

		do_action( 'wpo_order_created', wc_get_order( $order_id ), $data['cart'] );

		return $order_id;
	}

    public function action_woocommerce_checkout_create_order_line_item($item, $cart_item_key, $values, $order) {

            if ( ! isset($values['data']->custom_meta_fields) ) {
                return;
            }

            foreach ($values['data']->custom_meta_fields as $meta) {
                $item->update_meta_data($meta['meta_key'], $meta['meta_value']);
            }

        }

    protected function ajax_create_order_email_invoice( $data ) {
		$order_id = $data['order_id'];

		$order = wc_get_order( $order_id );

		if ( ! $order ) {
			return $this->wpo_send_json_error( __( 'Order not found', 'phone-orders-for-woocommerce' ) );
		}

		$email = $order->get_billing_email();

		if ( empty( $email ) ) {
			$user_info = get_userdata( $data['cart']['customer']['id'] );
			$email     = $user_info->user_email;
		}

		if ( ! is_email( $email ) ) {
			return $this->wpo_send_json_error( __( 'A valid email address is required', 'phone-orders-for-woocommerce' ) );
		}

		try {
			WC()->mailer()->customer_invoice( $order );
		} catch ( phpmailerException $e ) {
			return $this->wpo_send_json_error( __( 'There was an error sending the email', 'phone-orders-for-woocommerce' ) );
		}

		$result = array(
			'order_id' => $order_id,
			'email'    => $email,
			'message'  => sprintf( __( 'Invoice for order #%s has been sent to %s', 'phone-orders-for-woocommerce' ),
				$order_id, $email ),
		);

		return $this->wpo_send_json_success( $result );
	}

	protected function ajax_recalculate( $data ) {

                $result = $this->update_cart( $data['cart'] );

                if ( $result instanceof WC_Data_Exception ) {
                    return $this->wpo_send_json_error( $result->getMessage() );
                }

                $order_id = $this->get_frontend_order_id( $data );

                WC_Phone_Orders_Tabs_Helper::add_log( $data['log_row_id'], $result, $order_id );

                if ($result['chosen_shipping_method'] && isset($result['chosen_shipping_method']['total_html'])) {
                    unset($result['chosen_shipping_method']['total_html']);
                }

		WC()->cart->empty_cart();

		return $this->wpo_send_json_success( $result );
	}

	protected function get_frontend_order_id( $data ) {
		$order_id = 0;
		foreach ( array( 'order_id', 'drafted_order_id', 'edit_order_id' ) as $item ) {
			if ( isset( $data['cart'][ $item ] ) ) {
				$order_id = $data['cart'][ $item ];
				break;
			}
		}
		return $order_id;
	}

	protected function ajax_get_coupons_list( $data ) {
		$exclude        = isset( $_GET['exclude'] ) ? $_GET['exclude'] : array();
		$exclude_titles = array_filter( array_map( function ( $current ) {
			$current = json_decode( stripslashes( $current ), true );

			return ! empty( $current['title'] ) ? $current['title'] : false;
		}, $exclude ) );

		$term = $data['term'];

		$args    = array(
			'posts_per_page' => - 1,
			'orderby'        => 'title',
			'order'          => 'asc',
			'post_type'      => 'shop_coupon',
			'post_status'    => 'publish',
			's'              => $term,
		);
		$coupons = get_posts( $args );

		$result = array();
		foreach ( $coupons as $coupon ) {
			$coupon_name = $coupon->post_title;

			if ( in_array( $coupon_name, $exclude_titles ) ) {
				continue;
			}

			$result[] = array(
				'title' => $coupon_name,
			);
		}

		die( json_encode( $result ) );
	}

	protected function ajax_search_products_and_variations( $data ) {
		if ( ! $data ) {
			wp_send_json( null );
		}
		if ( ! empty( $data['exclude'] ) ) {
			$exclude = json_decode( stripslashes( $data['exclude'] ), true );
		} else {
			$exclude = array();
		}

		$term = isset( $data['term'] ) ? $data['term'] : '';

		$additional_query_args = $this->create_additional_query_args( $data );
		if( $this->option_handler->get_option( 'show_long_attribute_names' ) )
			add_filter("woocommerce_product_variation_title_include_attributes", "__return_true");
		$products = $this->search_products_and_variations( $term, $exclude, $additional_query_args );

		$result = array();
		$delimiter = apply_filters( 'wpo_autocomplete_product_fields_delimiter', '|' );
		$hide_image = $this->option_handler->get_option( 'autocomplete_product_hide_image' );

		foreach ( $products as $product_id => $product) {

			$image_url = "";

			if( ! $hide_image ) {
			    $image_url = $this->get_thumbnail_src_by_product($product);
			}

			$result[] = array( "product_id" =>$product_id, "title"=>$this->format_row_product( $product, $delimiter ), "sort"=>($term == $product_id)? "" : $product->get_name(), 'img'=>$image_url );
		}

        //sort by title
		usort( $result, function( $a, $b ) {
		    return strcmp($a['sort'], $b['sort']);
		});
		wp_send_json( $result );
	}

	protected function get_thumbnail_src_by_product($product) {

	    $src = '';

	    if( preg_match('/src\=["|\'](.*?)["|\']/i', $product->get_image(), $matches) ) {
		$src = $matches[1];
	    }

	    return $src;
	}

	public function OR_search_in_title($where,$query) {
		global $wpdb;
		if( isset( $query->query_vars['OR_title_filter'] ) ) {
			$like = "%" . $wpdb->esc_like( $query->query_vars['OR_title_filter'] ) . "%";
			$find = $wpdb->postmeta . ".meta_key = '_sku' AND";
			$replace = $wpdb->posts . ".post_title LIKE '$like' OR " . $wpdb->postmeta . ".meta_key = '_sku' AND";
			$where = str_replace( $find, $replace, $where );
		}
		return $where;
	}

	protected function search_products_and_variations( $term, $exclude, $additional_query_args = array() ) {
		$limit = $this->option_handler->get_option( 'number_of_products_to_show' );
		$search_by_sku = $this->option_handler->get_option( 'search_by_sku' );

		$query_args = array(
			'type'   => apply_filters( "wpo_search_product_types", array( 'simple', 'variable','variation','subscription' ) ),
			'exclude' => $exclude,
			'return' => 'ids',
			'orderby' => 'title',
			'order' => 'ASC',
			'limit' => $limit,
		);
		// filter by category/tags ?
		$query_args = array_merge( $query_args, $additional_query_args );
		if ( $products_ids = apply_filters( "wpo_custom_product_search", array(),$query_args,$term) ) {
			; // do nothing,  just use  custom results
        }
		elseif ( isset( $term ) AND $term ) { // keyword?
			//default search by title only
			if( !$search_by_sku ) {
				$query_args[ 's'] = $term;
				$products_ids = wc_get_products( $query_args );
			} else {
				$query_args[ 'sku'] = $term;
				$query_args[ 'OR_title_filter'] = $term;
				add_filter( "posts_where", array($this,"OR_search_in_title"), 10, 2);
				$products_ids = wc_get_products( $query_args );
				remove_filter( "posts_where", array($this,"OR_search_in_title"), 10, 2);
			}
		} else { // just category/tag  ?
			$products_ids = wc_get_products( $query_args );
		}

		//exact product by id ? add at top!
		if( preg_match('#^\d+$#',$term) AND ($product = wc_get_product($term)) ) {
			array_unshift( $products_ids, (int)$term );
			$products_ids = array_unique( $products_ids );
		}

		$selected_products = array();
		foreach ( $products_ids as $index => $product_id ) {
			if( in_array( $product_id, $exclude ) )
				continue;
			$product = wc_get_product( $product_id );
			if ( $product ) {
				if( $product->is_type( 'variable' ) ) {
					$variations = $product->get_children();
					// add childrens only IF we don't have exact varition in results already!
					if( !array_intersect($products_ids,$variations ) ) {
						foreach($variations  as $variation_id) {
							if( in_array( $variation_id, $exclude ) )
								continue;
							$variation = wc_get_product( $variation_id );
							if( $this->is_valid_product($variation) ) {
								$selected_products[$variation_id] = $variation;
								if ( count($selected_products) >= $limit )
									break;
							}
						}//end foreach variations
					}
				}
				elseif ( $this->is_valid_product($product) ) // add simple product or exact variation
					$selected_products[$product_id] = $product;
			}
			if ( count($selected_products) >= $limit )
				break;
		}
		return $selected_products;
	}

	protected function is_valid_product( $product ) {
		$zero_prices    = array( false, "", "0", "0.00" );
		$option_handler = $this->option_handler;

		if ( in_array( $product->get_status(), array("trash","draft") ) ) {
			return false;
		}

		if ( in_array( $product->get_regular_price(),
				$zero_prices ) AND $option_handler->get_option( 'hide_products_with_no_price' ) ) {
			return false;
		}

		if ( ! $product->is_in_stock() AND ! $option_handler->get_option( 'sale_backorder_product' ) ) {
			return false;
		}

		return true;
	}

	protected function create_additional_query_args( $params ) {
		return array();
	}

	protected function ajax_generate_log_row_id() {
	    return $this->wpo_send_json_success(array('log_row_id' => uniqid()));
        }

    protected function is_tax_enabled() {
	    return wc_tax_enabled() && ! WC()->customer->get_is_vat_exempt();
    }

    protected function ajax_init_order() {

        $custom_fields_option  = $this->option_handler->get_option('order_custom_fields');
        $custom_fields_options = array();

        if ($custom_fields_option) {
            foreach (preg_split("/((\r?\n)|(\r\n?))/", $custom_fields_option) as $line) {
                $line = explode('|', $line);
                if (count($line) > 2) {
                    $custom_fields_options[$line[1]] = $line[2] === 'text' && isset($line[3]) ? $line[3] : '';
                }
            }
        }

	    $old_user_id = false;
	    if ( $this->option_handler->get_option('default_customer_id') ) {
		    $customer_id = $this->option_handler->get_option('default_customer_id');

		    if ( $customer_id AND apply_filters( 'wpo_must_switch_cart_user', $this->option_handler->get_option( 'switch_customer_while_calc_cart' ) ) ) {
			    $old_user_id = get_current_user_id();
			    wp_set_current_user( $customer_id );
		    }
		    do_action( 'wdp_after_switch_customer_while_calc' );
	    }

        $state = array(
            'default_customer'                  => $this->get_customer_by_id($this->option_handler->get_option('default_customer_id')),
            'default_items'                     => $this->get_formatted_product_items_by_ids($this->option_handler->get_option('item_default_selected')),
            'default_order_custom_field_values' => apply_filters('wpo_init_order_default_custom_fields_values', $custom_fields_options),
            'log_row_id' => uniqid(),
            'default_order_status' => $this->option_handler->get_option('order_status'),
        );

	    //switch back to admin
	    if ( $old_user_id ) {
		    wp_set_current_user( $old_user_id );
	    }

        wp_send_json( $state );
    }

	protected function is_readonly_product_price( $product_id ) {
		return apply_filters( 'wpo_cart_item_is_price_readonly', $this->is_subscription( $product_id ) || $this->option_handler->get_option( 'is_readonly_price' ) );
	}

	protected function is_subscription( $product_id ) {
		return $this->subscription_plugin_enabled && WC_Subscriptions_Product::is_subscription( $product_id );
    }

    protected function get_original_price( $cart_item ) {
	    $price = apply_filters( 'wpo_set_original_price_after_calculation', false, $cart_item );

	    return is_numeric( $price ) && isset( $cart_item['item_cost'] ) && $price !== $cart_item['item_cost'] ? (int) $price : false;
    }

}