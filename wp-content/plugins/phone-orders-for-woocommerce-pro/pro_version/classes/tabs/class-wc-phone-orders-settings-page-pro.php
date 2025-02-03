<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WC_Phone_Orders_Settings_Page_Pro extends WC_Phone_Orders_Settings_Page {

	private function make_default_customer_object() {

		$customer_id = (int) $this->option_handler->get_option( 'default_customer_id' );

		if( !$customer_id )
			return null;

		$customer    = new WC_Customer( $customer_id );

		$default_customer_object = array(
			'value' => $customer_id,
			'title' => sprintf(
				'%s %s (#%s - %s)',
				$customer->get_first_name(),
				$customer->get_last_name(),
				$customer->get_id(),
				$customer->get_email()
			),
		);

		return $default_customer_object;
	}

	public function __construct() {
		parent::__construct();
		add_action( 'wpo_add_settings', array( $this, 'add_settings' ) );
		add_action( 'wpo_add_interface_settings', array( $this, 'add_interface_settings' ) );
		add_action( 'wpo_add_coupons_settings', array( $this, 'add_coupons_settings' ) );
	}

	public function add_settings() {
		$settings_option_handler = $this->option_handler;

                $item_ids = $settings_option_handler->get_option('item_default_selected');

                $item_default_selected = array();

                if (is_array($item_ids)) {

                    foreach ($item_ids as $iid) {

                        $item = wc_get_product($iid);

                        if ( ! $item ) {
                            continue;
                        }

                        $title = $this->format_row_product($item);

                        $item_default_selected[] = array(
                            'title' => $title,
                            'value' => $iid,
                        );
                    }
                }

		$tab_data_pro = array(
			'pro' => array(
				'runAtFrontendSettings'         => array(
					'title'                                       => __( "Run at frontend", 'phone-orders-for-woocommerce' ),
					'frontendPageInstructions'	      => __('The user must be admin or has capability "edit_shop_orders"', 'phone-orders-for-woocommerce'),
					'frontendPageLabel'			      => __('Accept orders at frontend page', 'phone-orders-for-woocommerce'),
					'frontendPage'				      => $settings_option_handler->get_option('frontend_page'),
					'frontendPageUrlLabel'			      => __('Frontend page url', 'phone-orders-for-woocommerce'),
					'frontendPageUrl'			      => $settings_option_handler->get_option('frontend_page_url'),

					'hideThemeHeaderLabel'			      => __('Hide theme header', 'phone-orders-for-woocommerce'),
					'hideThemeHeader'			      => $settings_option_handler->get_option('frontend_hide_theme_header'),
					'hideThemeFooterLabel'			      => __('Hide theme footer', 'phone-orders-for-woocommerce'),
					'hideThemeFooter'			      => $settings_option_handler->get_option('frontend_hide_theme_footer'),
				),
				'customerSettings'         => array(
					'title'                                       => __( "Customers", 'phone-orders-for-woocommerce' ),
					'cacheCustomerTimeoutLabel'                   => __( 'Caching search results',
						'phone-orders-for-woocommerce' ),
					'hoursLabel'                                  => __( "hours", 'phone-orders-for-woocommerce' ),
					'cacheCustomersSessionKey'                    => $settings_option_handler->get_option( 'cache_customers_session_key' ),
					'cacheCustomersTimeout'                       => (int) $settings_option_handler->get_option( 'cache_customers_timeout' ),
					'cacheCustomersDisableButton'                 => __( "Disable cache",
						'phone-orders-for-woocommerce' ),
					'cacheCustomersResetButton'                   => __( "Reset cache",
						'phone-orders-for-woocommerce' ),
					'searchAllCustomerFields'                     => $settings_option_handler->get_option( 'search_all_customer_fields' ),
					'searchAllCustomerLabel'                      => __( "Customer search by shipping/billing fields",
						'phone-orders-for-woocommerce' ),
					'searchCustomerInOrders'                      => $settings_option_handler->get_option( 'search_customer_in_orders' ),
					'searchCustomerInOrdersLabel'                 => __( "Search for customer in orders",
						'phone-orders-for-woocommerce' ),
					'numberOfCustomersToShowLabel'                => __( 'Number of customers to show in autocomplete',
						'phone-orders-for-woocommerce' ),
					'numberOfCustomersToShow'                     => (int) $settings_option_handler->get_option( 'number_of_customers_to_show' ),
					'defaultCustomerLabel'                        => __( 'Default customer',
						'phone-orders-for-woocommerce' ),
					'defaultCustomerObject'                       => $this->make_default_customer_object(),
					'defaultCustomersList'                        => array(),
					'updateCustomersProfileAfterCreateOrderLabel' => __( "Automatically update customer's profile on order creation",
						'phone-orders-for-woocommerce' ),
					'updateCustomersProfileAfterCreateOrder'      => $settings_option_handler->get_option( 'update_customers_profile_after_create_order' ),
					'selectDefaultCustomerPlaceholder'            => __( "Type to search",
						'phone-orders-for-woocommerce' ),
					'hideShippingSection'                         => $settings_option_handler->get_option( 'hide_shipping_section' ),
					'hideShippingSectionLabel'                    => __( "Hide shipping section",
						'phone-orders-for-woocommerce' ),
					'doNotSubmitOnEnterLastField'                 => $settings_option_handler->get_option( 'do_not_submit_on_enter_last_field' ),
					'doNotSubmitOnEnterLastFieldLabel'            => __( "Don't close customer/address form automatically",
						'phone-orders-for-woocommerce' ),
					'noResultLabel'                               => __( "Oops! No elements found. Consider changing the search query.",
						'phone-orders-for-woocommerce' ),
				),
				'newCustomerPopupSettings' => array(
					'title'                                      => __( "New Customer",
						'phone-orders-for-woocommerce' ),
					'newcustomerShowPasswordFieldLabel'          => __( "Show Password field",
						'phone-orders-for-woocommerce' ),
					'newcustomerShowPasswordField'               => $settings_option_handler->get_option( 'newcustomer_show_password_field' ),
					'newcustomerShowPasswordFieldNote'           => __( "You have to tell them the password",
						'phone-orders-for-woocommerce' ),
					'newcustomerShowUsernameFieldLabel'          => __( "Show Username field",
						'phone-orders-for-woocommerce' ),
					'newcustomerShowUsernameField'               => $settings_option_handler->get_option( 'newcustomer_show_username_field' ),
					'emailIsOptionalLabel'                       => __( "Email is optional",
						'phone-orders-for-woocommerce' ),
					'emailIsOptional'                            => $settings_option_handler->get_option( 'newcustomer_email_is_optional' ),
					'hideFieldsLabel'                            => __( "Hide fields", 'phone-orders-for-woocommerce' ),
					'hideCompanyLabel'                           => __( "Company", 'phone-orders-for-woocommerce' ),
					'hideCompany'                                => $settings_option_handler->get_option( 'newcustomer_hide_company' ),
					'hideEmailLabel'                             => __( "Email", 'phone-orders-for-woocommerce' ),
					'hideEmail'                                  => $settings_option_handler->get_option( 'newcustomer_hide_email' ),
					'hideAddress1Label'                          => __( "Address 1", 'phone-orders-for-woocommerce' ),
					'hideAddress1'                               => $settings_option_handler->get_option( 'newcustomer_hide_address_1' ),
					'hideAddress2Label'                          => __( "Address 2", 'phone-orders-for-woocommerce' ),
					'hideAddress2'                               => $settings_option_handler->get_option( 'newcustomer_hide_address_2' ),
					'hideCityLabel'                              => __( "City", 'phone-orders-for-woocommerce' ),
					'hideCity'                                   => $settings_option_handler->get_option( 'newcustomer_hide_city' ),
					'hidePostcodeLabel'                          => __( "Postcode", 'phone-orders-for-woocommerce' ),
					'hidePostcode'                               => $settings_option_handler->get_option( 'newcustomer_hide_postcode' ),
					'hideCountryLabel'                           => __( "Country", 'phone-orders-for-woocommerce' ),
					'hideCountry'                                => $settings_option_handler->get_option( 'newcustomer_hide_country' ),
					'hideStateLabel'                             => __( "State", 'phone-orders-for-woocommerce' ),
					'hideState'                                  => $settings_option_handler->get_option( 'newcustomer_hide_state' ),
					'defaultCityLabel'                           => __( 'Default city',
						'phone-orders-for-woocommerce' ),
					'defaultCity'                                => $settings_option_handler->get_option( 'default_city' ),
					'defaultPostcodeLabel'                       => __( 'Default postcode',
						'phone-orders-for-woocommerce' ),
					'defaultPostcode'                            => $settings_option_handler->get_option( 'default_postcode' ),
					'defaultCountryLabel'                        => __( 'Default country',
						'phone-orders-for-woocommerce' ),
					'defaultCountry'                             => $settings_option_handler->get_option( 'default_country' ),
					'defaultStateLabel'                          => __( 'Default  state/county',
						'phone-orders-for-woocommerce' ),
					'defaultState'                               => $settings_option_handler->get_option( 'default_state' ),
					'selectPlaceholder'                          => __( 'Select option',
						'phone-orders-for-woocommerce' ),
					'dontFillShippingAddressForNewCustomer'      => $settings_option_handler->get_option( 'dont_fill_shipping_address_for_new_customer' ),
					'dontFillShippingAddressForNewCustomerLabel' => __( 'Don\'t fill shipping address',
						'phone-orders-for-woocommerce' ),
					'disableCreatingCustomersLabel'              => __( 'Disable creating customers',
						'phone-orders-for-woocommerce' ),
					'disableCreatingCustomers'                   => $settings_option_handler->get_option( 'disable_creating_customers' ),
					'newcustomerShowRoleFieldLabel'              => __( 'Show Role field', 'phone-orders-for-woocommerce' ),
					'newcustomerShowRoleField'                   => $settings_option_handler->get_option( 'newcustomer_show_role_field' ),
					'defaultRoleLabel'                           => __( 'Default role', 'phone-orders-for-woocommerce' ),
					'defaultRole'                                => $settings_option_handler->get_option( 'default_role' ),
					'disableNewUserEmailLabel'                   => __( 'Disable user notification email', 'phone-orders-for-woocommerce' ),
					'disableNewUserEmail'                        => $settings_option_handler->get_option( 'disable_new_user_email' ),
					'rolesList'                                  => $this->make_roles_list(),
					'tabName'                                    => 'settings',
				),
				'productsSettings' => array(
					'title'                                 => __( 'Products',
						'phone-orders-for-woocommerce' ),
					'productsCacheProductsTimeoutLabel'     => __( 'Caching search results',
						'phone-orders-for-woocommerce' ),
					'hoursLabel'                            => __( "hours", 'phone-orders-for-woocommerce' ),
					'disableCacheButtonLabel'               => __( 'Disable cache',
						'phone-orders-for-woocommerce' ),
					'resetCacheButtonLabel'                 => __( 'Reset cache',
						'phone-orders-for-woocommerce' ),
					'productsSearchBySkuLabel'              => __( 'Search by SKU',
						'phone-orders-for-woocommerce' ),
					'productsSearchByCatAndTagLabel'        => __( 'Filter products by category/tags',
						'phone-orders-for-woocommerce' ),
					'productsNumberOfProductsToShowLabel'   => __( 'Number of products to show in autocomplete',
						'phone-orders-for-woocommerce' ),
					'hideProductFieldsLabel'                => __( 'Hide fields in autocomplete',
						'phone-orders-for-woocommerce' ),
					'hideStatusLabel'                       => __( "Status", 'phone-orders-for-woocommerce' ),
					'hideQtyLabel'                          => __( "Qty", 'phone-orders-for-woocommerce' ),
					'hidePriceLabel'                        => __( "Price", 'phone-orders-for-woocommerce' ),
					'hideSkuLabel'                          => __( "Sku", 'phone-orders-for-woocommerce' ),
					'hideNameLabel'                         => __( "Name", 'phone-orders-for-woocommerce' ),
					'productsShowLongAttributeNamesLabel'   => __( 'Show long attribute names',
						'phone-orders-for-woocommerce' ),
					'productsRepeatSearchLabel'             => __( 'Repeat search after select product',
						'phone-orders-for-woocommerce' ),
					'allowDuplicateProductsLabel'     => __( 'Allow to duplicate products',
						'phone-orders-for-woocommerce' ),
					'productsHideProductsWithNoPriceLabel'  => __( 'Don\'t sell products with no price defined',
						'phone-orders-for-woocommerce' ),
					'productsSellBackorderProductLabel'     => __( 'Sell "out of stock" products',
						'phone-orders-for-woocommerce' ),
					'productsAddProductToTopOfTheCartLabel' => __( 'Add product to top of the cart',
						'phone-orders-for-woocommerce' ),

					'productsItemPricePrecisionLabel' => __( 'Item price precision', 'phone-orders-for-woocommerce' ),
					'disableEditMetaLabel'            => __( 'Disable edit meta', 'phone-orders-for-woocommerce' ),
					'isReadonlyPriceLabel'            => __( 'Item price is read-only',
						'phone-orders-for-woocommerce' ),

					'cacheSessionKey'          => $settings_option_handler->get_option( 'cache_products_session_key' ),
					'cacheTimeout'             => (int) $settings_option_handler->get_option( 'cache_products_timeout' ),
					'searchBySku'              => $settings_option_handler->get_option( 'search_by_sku' ),
					'searchByCatAndTag'        => $settings_option_handler->get_option( 'search_by_cat_and_tag' ),
					'numberOfProductsToShow'   => (int) $settings_option_handler->get_option( 'number_of_products_to_show' ),
					'hideImage'                => $settings_option_handler->get_option( 'autocomplete_product_hide_image' ),
					'hideStatus'               => $settings_option_handler->get_option( 'autocomplete_product_hide_status' ),
					'hideQty'                  => $settings_option_handler->get_option( 'autocomplete_product_hide_qty' ),
					'hidePrice'                => $settings_option_handler->get_option( 'autocomplete_product_hide_price' ),
					'hideSku'                  => $settings_option_handler->get_option( 'autocomplete_product_hide_sku' ),
					'hideName'                 => $settings_option_handler->get_option( 'autocomplete_product_hide_name' ),
					'showLongAttributeNames'   => $settings_option_handler->get_option( 'show_long_attribute_names' ),
					'repeatSearch'             => $settings_option_handler->get_option( 'repeat_search' ),
					'allowDuplicateProducts'   => $settings_option_handler->get_option( 'allow_duplicate_products' ),
					'hideProductsWithNoPrice'  => $settings_option_handler->get_option( 'hide_products_with_no_price' ),
					'saleBackorderProducts'    => $settings_option_handler->get_option( 'sale_backorder_product' ),
					'addProductToTopOfTheCart' => $settings_option_handler->get_option( 'add_product_to_top_of_the_cart' ),
					'itemPricePrecision'       => (int) $settings_option_handler->get_option( 'item_price_precision' ),
					'disableEditMeta'          => $settings_option_handler->get_option( 'disable_edit_meta' ),
					'isReadonlyPrice'          => $settings_option_handler->get_option( 'is_readonly_price' ),

					'productsDefaultSelectedLabel'   => __( 'Add products by default', 'phone-orders-for-woocommerce' ),
					'itemDefaultSelected'            => $item_default_selected,
					'noResultLabel'                  => __( "Oops! No elements found. Consider changing the search query.", 'phone-orders-for-woocommerce' ),
					'itemDefaultSelectedPlaceholder' => __( "Select items", 'phone-orders-for-woocommerce' ),
					'tabName'                        => 'settings',
				),
				'addItemPopupSettings' => array(
					'title'                                => __( 'New Product', 'phone-orders-for-woocommerce' ),
					'productsDisableCreatingProductsLabel' => __( 'Disable creating products',
						'phone-orders-for-woocommerce' ),
					'productsNewProductAskSKULabel'        => __( 'Show SKU while adding product',
						'phone-orders-for-woocommerce' ),
					'productsNewProductVisibilityLabel'    => __( 'New product visibility',
						'phone-orders-for-woocommerce' ),
					'addItemTaxClassLabel'                 => __( 'Default tax class', 'phone-orders-for-woocommerce' ),
					'productsNewProductAskTaxClassLabel'   => __( 'Show tax class selector',
						'phone-orders-for-woocommerce' ),

					'productsThisSettingDeterminesWhichShopPagesProductsWillBeListedOn' => __( 'This setting determines which shop pages products will be listed on.',
						'woocommerce' ),

					'disableAddingProducts'    => $settings_option_handler->get_option( 'disable_adding_products' ),
					'newProductAskSKU'         => $settings_option_handler->get_option( 'new_product_ask_sku' ),
					'productVisibility'        => $settings_option_handler->get_option( 'product_visibility' ),
					'productVisibilityOptions' => $this->make_product_visibility_options(),

					'newProductAskTaxClass' => $settings_option_handler->get_option( 'new_product_ask_tax_class' ),
					'itemTaxClass'          => $settings_option_handler->get_option( 'item_tax_class' ),
					'itemTaxClasses'        => $this->make_tax_classes(),
				),
				'feeSettings'              => array(
					'title'            => __( 'Fee', 'phone-orders-for-woocommerce' ),
					'hideAddFeeLabel'  => __( 'Hide "Add fee"', 'phone-orders-for-woocommerce' ),
					'feeNameLabel'     => __( 'Fee name', 'phone-orders-for-woocommerce' ),
					'feeAmountLabel'   => __( 'Fee amount', 'phone-orders-for-woocommerce' ),
					'feeTaxClassLabel' => __( 'Fee tax class', 'phone-orders-for-woocommerce' ),
					'hideAddFee'       => $settings_option_handler->get_option( 'hide_add_fee' ),
					'defaultFeeName'   => $settings_option_handler->get_option( 'default_fee_name' ),
					'defaultFeeAmount' => $settings_option_handler->get_option( 'default_fee_amount' ),
					'feeTaxClass'      => $settings_option_handler->get_option( 'fee_tax_class' ),
					'taxClasses'       => $this->make_tax_classes(),
				),
				'discountSettings'         => array(
					'title'                => __( 'Discount', 'phone-orders-for-woocommerce' ),
					'hideAddDiscountLabel' => __( 'Hide "Add discount"', 'phone-orders-for-woocommerce' ),
					'couponNameLabel'      => __( 'Coupon name  (used by manual discount)',
						'phone-orders-for-woocommerce' ),
					'hideAddDiscount'      => $settings_option_handler->get_option( 'hide_add_discount' ),
					'manualCouponTitle'    => $settings_option_handler->get_option( 'manual_coupon_title' ),
				),
				'shippingSettings'         => array(
					'title'                => __( 'Shipping', 'phone-orders-for-woocommerce' ),
					'hideAddShippingLabel' => __( 'Hide shipping section', 'phone-orders-for-woocommerce' ),
					'hideAddShipping'      => $settings_option_handler->get_option( 'hide_add_shipping' ),
				),
				'copyOrdersSettings' => array(
					'title'                                     => __( 'Find orders', 'phone-orders-for-woocommerce' ),
					'cacheTimeoutLabel'                         => __( 'Caching search results',
						'phone-orders-for-woocommerce' ),
                    'hoursLabel'                                => __( "hours", 'phone-orders-for-woocommerce'),
					'disableCacheButtonLabel'                   => __( 'Disable cache',
						'phone-orders-for-woocommerce' ),
					'resetCacheButtonLabel'                     => __( 'Reset cache', 'phone-orders-for-woocommerce' ),
					'copyOnlyProcOrCompOrdersLabel'             => __( 'Seek in processing/completed orders only',
						'phone-orders-for-woocommerce' ),
					'showButtonCopyOrderLabel'                  => __( 'Show buttons', 'phone-orders-for-woocommerce' ),
					'setCurrentPriceForItemsInCopiedOrderLabel' => __( 'Set current price for items in copied order',
						'phone-orders-for-woocommerce' ),
					'hideFindOrdersLabel'                       => __( 'Hide "Find orders"', 'phone-orders-for-woocommerce' ),
					'sessionKey'                                => $settings_option_handler->get_option( 'cache_orders_session_key' ),
					'cacheTimeout'                              => (int) $settings_option_handler->get_option( 'cache_orders_timeout' ),
					'copyOnlyPaidOrders'                        => $settings_option_handler->get_option( 'copy_only_paid_orders' ),
					'buttonForFindOrder'                        => $settings_option_handler->get_option( 'button_for_find_orders' ),
					'setCurrentPriceInCopiedOrder'              => $settings_option_handler->get_option( 'set_current_price_in_copied_order' ),
					'hideFindOrders'                            => $settings_option_handler->get_option( 'hide_find_orders' ),
				),
				'miscSettings' => array(
					'title'                => __( 'Custom fields', 'phone-orders-for-woocommerce' ),
					'customFieldsLabel'    => __( 'Order fields', 'phone-orders-for-woocommerce' ),
					'oneFieldPerLineLabel' => __( 'One field per line, use format: Label Text|custom_fieldname', 'phone-orders-for-woocommerce' ),
					'orderCustomFields'    => $settings_option_handler->get_option( 'order_custom_fields' ),

					'customerCustomFieldsLabel' => __( 'Customer fields', 'phone-orders-for-woocommerce' ),
					'customerCustomFields'    => $settings_option_handler->get_option( 'customer_custom_fields' ),

					'itemMetaFieldsLabel'                => __( 'Available fields for product', 'phone-orders-for-woocommerce' ),
					'itemMetaFieldsOneFieldPerLineLabel' => __( 'One field per line', 'phone-orders-for-woocommerce' ),
					'itemCustomMetaFields'               => $settings_option_handler->get_option( 'item_custom_meta_fields' ),

					'defaultListItemMetaFieldsLabel'                => __( 'Default fields for product', 'phone-orders-for-woocommerce' ),
					'defaultListItemMetaFieldsOneFieldPerLineLabel' => __( 'One field and value per line, separated by |, e.g meta_key|meta_value', 'phone-orders-for-woocommerce' ),
					'defaultListItemCustomMetaFields'               => $settings_option_handler->get_option( 'default_list_item_custom_meta_fields' ),
				),
				'redirectSettings' => array(
					'title'                                       => __( 'Checkout at frontend', 'phone-orders-for-woocommerce' ),
					'showGoToCartPageLabel'                       => __( 'Show \'Go to Cart\' button', 'phone-orders-for-woocommerce' ),
					'showGoToCheckoutPageLabel'                   => __( 'Show \'Go to Checkout\' button', 'phone-orders-for-woocommerce' ),
					'overrideCustomerPaymentLinkInOrderPageLabel' => __( 'Override "Customer payment page" in the order', 'phone-orders-for-woocommerce' ),
					'overrideProductPriceInCartLabel'             => __( 'Pass modified product prices to frontend cart', 'phone-orders-for-woocommerce' ),
					'showGoToCartButton'                          => $settings_option_handler->get_option( 'show_go_to_cart_button' ),
					'showGoToCheckoutButton'                      => $settings_option_handler->get_option( 'show_go_to_checkout_button' ),
					'overrideCustomerPaymentLinkInOrderPage'      => $settings_option_handler->get_option( 'override_customer_payment_link_in_order_page' ),
					'overrideProductPriceInCart'                  => $settings_option_handler->get_option( 'override_product_price_in_cart' ),
				),
			),
		);

		$this->tab_data = array_merge( $this->tab_data, $tab_data_pro );
		?>
        <hr/>
        <pro-settings slot="pro-settings"
                      v-bind="<?php echo esc_attr( json_encode( $this->tab_data['pro'] ) ) ?>"></pro-settings>
		<?php
	}

	public function add_interface_settings() {

		$settings_option_handler = $this->option_handler;

		$pro_interface_settings = array(
			'hideTabsLabel'                => __( 'Hide tabs for non-admins', 'phone-orders-for-woocommerce' ),
			'showDuplicateOrderLabel'      => __( 'Show "duplicate order" button after order creation', 'phone-orders-for-woocommerce' ),
			'showEditOrderInWCLabel'       => __( 'Show button "Edit" in orders list', 'phone-orders-for-woocommerce' ),
			'hideButtonPayAsCustomerLabel' => __( 'Hide button "Pay as customer"', 'phone-orders-for-woocommerce' ),
			'hideButtonCreateOrderLabel'   => __( 'Hide button "Create order"', 'phone-orders-for-woocommerce' ),
			'hideButtonPutOnHoldLabel'     => __( 'Hide button "Put on hold"', 'phone-orders-for-woocommerce' ),


			'hideTabs'                => $settings_option_handler->get_option( 'hide_tabs' ),
			'showDuplicateOrder'      => $settings_option_handler->get_option( 'show_duplicate_order_button' ),
			'showEditOrderInWC'       => $settings_option_handler->get_option( 'show_edit_order_in_wc' ),
			'hideButtonPayAsCustomer' => $settings_option_handler->get_option( 'hide_button_pay_as_customer' ),
			'hideButtonCreateOrder'   => $settings_option_handler->get_option( 'hide_button_create_order' ),
			'hideButtonPutOnHold'     => $settings_option_handler->get_option( 'hide_button_put_on_hold' ),
		);
		?>

        <pro-interface-settings slot="pro-interface-settings"
                                v-bind="<?php echo esc_attr( json_encode( $pro_interface_settings ) ) ?>"></pro-interface-settings>
		<?php
	}

	public function add_coupons_settings() {

		$settings_option_handler = $this->option_handler;

		$pro_coupons_settings = array(
			'hideCouponWarningLabel' => __( 'Hide warning about disabled coupons', 'phone-orders-for-woocommerce' ),
			'hideCouponWarning'      => $settings_option_handler->get_option( 'hide_coupon_warning' ),
		);
		?>

        <pro-coupons-settings slot="pro-coupons-settings"
                             v-bind="<?php echo esc_attr( json_encode( $pro_coupons_settings ) ) ?>"></pro-coupons-settings>
		<?php
	}

	private function make_product_visibility_options() {
		$product_visibility_options = array();
		foreach ( wc_get_product_visibility_options() as $name => $label ) {
			$product_visibility_options[] = array(
				'name'  => $name,
				'label' => $label,
			);
		}

		return $product_visibility_options;
	}

	public function enqueue_scripts() {
		parent::enqueue_scripts();
	}
}