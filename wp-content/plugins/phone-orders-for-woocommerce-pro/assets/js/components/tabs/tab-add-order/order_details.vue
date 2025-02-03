<template>
    <div id="postbox-container-2" class="postbox-container">
        <div class="postbox disable-on-order" id="woocommerce-order-items">
            <div>
                <div class="order-details__title" v-html="title" v-show="showHeader"></div>

                <slot name="before-search-items-field"></slot>

                <span class="handlediv button-link" v-if="allowAddProducts">
                    <a href="#" class="link-add-custom-item" @click.prevent="cartEnabled ? addCustomProductItem() : null" :class="{disabled: !cartEnabled}">
                        {{ addProductButtonTitle }}
                    </a>
                </span>
            </div>
            <div style="clear: both"></div>
            <div class="inside">
                <div class="order-content">
                    <div id="search-items-box">
                        <input type="hidden" id="additional_parameters_for_select_items" value="{}">
                        <multiselect
                            ref="productSelectSearch"
                            style="width: 100%;"
                            label="title"
                            v-model="product"
                            :options="productsList"
                            track-by="value"
                            id="ajax"
                            :placeholder="findProductsSelectPlaceholder"
                            :loading="isLoading"
                            :internal-search="false"
                            :show-no-results="true"
                            @search-change="asyncFind"
                            :hide-selected="false"
                            :searchable="true"
                            open-direction="bottom"
                            @input="addProductItemToCart"
                            @open="openSelectSearchProduct"
                            :disabled="!cartEnabled"
                            :options-limit="+productSelectOptionsLimit"
                            :close-on-select="productSelectCloseOnSelected"
                            :clear-on-select="productSelectCloseOnSelected"
                            :allow-empty="false"
                            :show-labels="false"
                        >
                            <span slot="noResult">{{ noResultLabel }}</span>
                            <template slot="singleLabel" slot-scope="props">
                                <span v-html="props.option.title"></span>
                            </template>
                            <template slot="option" slot-scope="props">
                                <img class="option__image" :src="props.option.img" alt="" v-show="!!props.option.img" width="100">
                                <span class="option__desc">
                                    <span class="option__title" v-html="props.option.title"></span>
                                </span>
                            </template>
                        </multiselect>
                    </div>
                    <div class="woocommerce_order_items_wrapper wc-order-items-editable">
                        <table cellpadding="0" cellspacing="0" class="woocommerce_order_items">
                            <thead>
                            <tr>
                                <th class="name sortable" colspan="2" data-sort="string-ins">
                                    {{ productsTableItemColumnTitle }}
                                </th>
                                <th class="item_cost sortable" data-sort="float">
                                    {{ productsTableCostColumnTitle }}
                                </th>
                                <th class="quantity sortable" data-sort="int">
                                    {{ productsTableQtyColumnTitle }}
                                </th>
                                <th class="line_cost sortable" data-sort="float">
                                    {{ productsTableTotalColumnTitle }}
                                </th>
                                <th class="wc-order-edit-line-item">&nbsp;</th>
                            </tr>
                            </thead>
                            <tbody>
                                <product-item
                                    v-bind="productItemLabels"
                                    v-for="product in productList"
                                    :item="Object.assign({}, product)"
                                    :calculated="getProductItemObject(product)"
                                    :key="getProductKey(product)"
                                    :ref="getProductRef(product)"
                                ></product-item>
                            </tbody>
                        </table>
                    </div>
                    <div class="order-details-cart-buttons">
                        <div class="order-details-cart-buttons__block">
                            <copy-cart-button
                                :default-button-label="copyCartButtonLabel"
                                :copied-button-label="copyCopiedCartButtonLabel"
                            ></copy-cart-button>
                            <slot name="wpo-after-order-items"></slot>
                        </div>
                    </div>
                </div>
                <div class="order-footer">
                    <div style="float: left; width: 50%">
                        <slot name="order-footer-left-side"></slot>
                        <div class="order-footer__note">
                            <p>
                                {{ customerProvidedNoteLabel }}
                                <textarea :placeholder="customerProvidedNotePlaceholder" v-model.lazy="customerProvidedNote" v-bind:disabled="!cartEnabled"></textarea>
                            </p>
                            <p>
                                {{ customerPrivateNoteLabel }}
                                <textarea :placeholder="customerPrivateNotePlaceholder" v-model.lazy="customerPrivateNote" v-bind:disabled="!cartEnabled"></textarea>
                            </p>
                        </div>
                    </div>
                    <div style="float: right; width: 50%">
                        <table class="wc-order-totals">
                            <tbody>
                                <tr>
                                    <td class="label-total">{{ subtotalLabel }}:</td>
                                    <td width="1%"></td>
                                    <td class="subtotal">
                                        <strong>{{ currencySymbol }}{{ subtotal | formatPrice }}</strong>
                                    </td>
                                    <td class="total-value subtotal">
                                        <strong>{{ currencySymbol }}{{ subtotalWithTax | formatPrice }}</strong>
                                    </td>
                                </tr>
                                <tr v-if="couponsEnabled" class="coupons-list-item" v-for="(coupon, index) in couponList">
                                    <td class="label-total">Coupon : {{ coupon.title }}</td>
                                    <td width="1%"></td>
                                    <td>
                                        <span class="woocommerce-Price-amount coupon-value">
                                            <span v-if="coupon.amount">
                                                <span class="woocommerce-Price-amount amount">
                                                    <span class="woocommerce-Price-currencySymbol">{{ currencySymbol }}</span>{{ coupon.amount | formatPrice }}
                                                </span>
                                            </span>
                                            <a class="remove-coupon" href="#" @click.prevent.stop="cartEnabled ? removeCoupon(coupon, index) : null" :class="{disabled: !cartEnabled}">[Remove ]</a>
                                        </span>
                                    </td>
                                </tr>
                                <tr v-if="couponsEnabled" class="coupons-list-add">
                                    <td class="label-total">
                                        <a href="#" @click.prevent.stop="cartEnabled ? addCoupon() : null" :class="{disabled: !cartEnabled}">
                                            {{ addCouponLabel }}
                                        </a>
                                    </td>
                                    <td width="1%"></td>
                                    <td><span class="woocommerce-Price-amount coupon-value"></span></td>
                                </tr>
                                <tr v-if="!hideAddDiscount && couponsEnabled">
                                    <td class="label-total">
                                        <a href="#" @click.prevent.stop="cartEnabled ? addDiscount() : null" :class="{disabled: !cartEnabled}">
                                            {{ discount ? manualDiscountLabel : addDiscountLabel }}
                                        </a>
                                    </td>
                                    <td width="1%"></td>
                                    <td>
                                        <strong>
                                            {{ currencySymbol }}{{ discountAmount | formatPrice }}
                                        </strong>
                                    </td>
                                </tr>
                                <tr v-if="!couponsEnabled && !hideCouponWarning" class="coupons-apply-warning">
                                    <td colspan="4" v-html="activateCouponsLabel"></td>
                                </tr>
                                <tr class="fee-list-item" v-for="(fee, index) in feeList">
                                    <td class="label-total">Fee : {{ fee.name }}</td>
                                    <td width="1%"></td>
                                    <td>
                                        <span class="woocommerce-Price-amount fee-value">
                                            <a class="remove-fee" href="#" @click.prevent.stop="cartEnabled ? removeFee(fee, index) : null" :class="{disabled: !cartEnabled}">
                                                [Remove ]
                                            </a>
                                            <span class="woocommerce-Price-amount amount">
                                                <span class="woocommerce-Price-currencySymbol">{{ currencySymbol }}</span>{{ fee.amount | formatPrice }}
                                            </span>
                                        </span>
                                    </td>
                                    <td class="total-value">
                                        <span class="woocommerce-Price-amount fee-value-no-action">
                                            <span class="woocommerce-Price-amount amount">
                                                <span class="woocommerce-Price-currencySymbol">{{ currencySymbol }}</span>{{ (fee.amount_with_tax || fee.amount) | formatPrice }}
                                            </span>
                                        </span>
                                    </td>
                                </tr>
                                <slot name="add-fee"></slot>
                                <tr>
                                    <td colspan="4">
                                        <button id="recalculate" class="btn btn-primary" data-action="recalculate" v-show="!autoRecalculate" :disabled="!cartEnabled || !productList.length" @click="recalculate">
                                            {{ recalculateButtonLabel }}
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="!hideAddShipping">
                                    <td class="label-total">
                                        <a href="#" @click.prevent.stop="cartEnabled ? addShipping() : null" :class="{disabled: !cartEnabled}">
                                            {{ shipping ? shippingLabel : addShippingLabel }}
                                        </a>
                                        <span class="total-shipping-label">
                                            {{ shipping ? shipping.label : '' }}
                                        </span>
                                    </td>
                                    <td width="1%"></td>
                                    <td>
                                        <span class="shipping-value amount" v-if="shipping">
                                            <span class="woocommerce-Price-amount amount">
                                                <span class="woocommerce-Price-currencySymbol">{{ currencySymbol }}</span>{{ shipping.cost | formatPrice }}
                                            </span>
                                        </span>
                                    </td>
                                    <td class="total-value">
                                        <span class="shipping-value amount">
                                            <span class="woocommerce-Price-amount amount" v-if="shipping">
                                                <span class="woocommerce-Price-currencySymbol">{{ currencySymbol }}</span>{{ shipping.full_cost | formatPrice }}
                                            </span>
                                        </span>
                                    </td>
                                </tr>
                                <tr class="order-discount-line order-total-line--updated">
                                    <td class="label-total">{{ discountLabel }}:</td>
                                    <td width="1%"></td>
                                    <td class="total" style="border-top: 1px solid grey;">
                                        <span class="woocommerce-Price-amount amount">
                                            <span class="woocommerce-Price-currencySymbol">{{ currencySymbol }}</span><span class="order-total-value">{{ totalDiscount | formatPrice }}</span>
                                        </span>
                                    </td>
                                </tr>
                                <tr class="order-taxes-line order-total-line--updated">
                                    <td class="label-total">{{ taxLabel }}:</td>
                                    <td width="1%"></td>
                                    <td class=""></td>
                                    <td class="total total-value">
                                        <span class="woocommerce-Price-amount amount">
                                            <span class="woocommerce-Price-currencySymbol">{{ currencySymbol }}</span><span class="order-total-value">{{ totalTax | formatPrice }}</span>
                                        </span>
                                    </td>
                                </tr>
                                <tr class="order-total-line order-total-line--updated">
                                    <td class="label-total">{{ orderTotalLabel }}:</td>
                                    <td width="1%"></td>
                                    <td class="total">
                                        <span class="woocommerce-Price-amount amount">
                                            <span class="woocommerce-Price-currencySymbol">{{ currencySymbol }}</span><!--
                                            --><span class="order-total-value">{{ orderTotal | formatPrice }}</span>
                                        </span>
                                    </td>
                                    <td class="total total-value">
                                        <span class="woocommerce-Price-amount amount">
                                            <span class="woocommerce-Price-currencySymbol">{{ currencySymbol }}</span><!--
                                            --><span class="order-total-value">{{ orderTotalWithTax | formatPrice }}</span>
                                        </span>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="clear"></div>
                    </div>
                    <div class="order-actions" v-show="showOrderActions">
                        <table class="wc-order__actions">
                            <tr>
                                <td>
                                    <span class="description">
                                        <span class="description-content">
                                            <b-alert :show="!!buttonsMessage"
                                                    fade
                                                    variant="success"
                                            >
                                                {{ buttonsMessage }}
                                           </b-alert>
                                        </span>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <button class="btn btn-primary" @click="onCreateOrder" v-show="showCreateOrderButton && showCreateOrderButtonOption">
                                        {{ createOrderButtonLabel }}
                                    </button>

                                    <slot name="pro-version-buttons-1"></slot>

                                    <button class="btn btn-primary" @click="viewOrder" v-show="showViewOrderButton">
                                        {{ viewOrderButtonLabel }}
                                    </button>

                                    <slot name="pro-version-buttons-2"></slot>

                                    <button class="btn btn-primary" @click="sendOrder" v-show="showSendOrderButton">
                                        {{ sendOrderButtonLabel }}
                                    </button>

                                    <button class="btn btn-primary" @click="duplicateOrder" v-show="showDuplicateOrder">
                                        {{ duplicateOrderLabel }}
                                    </button>

                                    <button class="btn btn-primary" @click="createNewOrder" v-show="showCreateNewOrderButton">
                                        {{ createNewOrderLabel }}
                                    </button>

                                    <div data-action="pay-order" v-show="!isProVersion">
                                        <br>
                                        <b>{{ payOrderNeedProVersionMessage }}</b>
                                        <a href="https://algolplus.com/plugins/downloads/phone-orders-woocommerce-pro/" target=_blank>
                                            {{ buyProVersionMessage }}
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style>
    #woocommerce-order-items .button-link {
        text-decoration: none;
    }
    .wc-order-totals .coupons-apply-warning a {
        color: red;
        border: 1px solid red;
        padding: 5px;
    }

    .woocommerce_order_items tr td:first-child {
        width: 10%;
    }

    .woocommerce_order_items tr td.name {
        width: 50%;
    }

    .woocommerce_order_items tr td.item_cost div,
    .woocommerce_order_items tr td.quantity div,
    .woocommerce_order_items tr td.line_total div,
    .woocommerce_order_items tr th.item_cost,
    .woocommerce_order_items tr th.quantity,
    .woocommerce_order_items tr th.line_total {
        text-align: center !important;
    }


    .order-details-cart-buttons {
        text-align: right;
        margin: 10px;
    }

    .order-details-cart-buttons .order-details-cart-buttons__block {
        display: inline-block;
        text-align: left;
    }

    .order-details-cart-buttons .order-details-cart-buttons__block .btn + .btn {
        margin-left: 10px;
    }

</style>

<script>

    import Multiselect from 'vue-multiselect';
    import ProductItem from './product_item.vue';
    import CopyCartButton from './copy_cart_button.vue';
    import _ from 'lodash';

    export default {
        created: function () {

            this.$root.bus.$on('open-search-product', () => {
                this.openProductSearchSelect();
            });

            this.$root.bus.$on('clear-cart', () => {
                !this.autoRecalculate && this.recalculate();
            });

            this.$root.bus.$on('clear-calculated-item', (key) => {
                this.calculatedItems = this.calculatedItems.filter((item) => {
                    return item.key !== key;
                });
            });

            this.$root.bus.$on('clear-selected-item', (id) => {

		var deleted = false;

                this.selectedItems = this.selectedItems.filter((item) => {

		    if (item !== id || deleted) {
			return true;
		    }

		    deleted = true;

		    return false;
                });
            });

            this.$root.bus.$on('recalculate-cart', () => {
                this.autoRecalculate && this.recalculate();
            });

            this.$root.bus.$on('set-manual-discount', (discount) => {
                this.discountAmount = ! discount ? 0 : (discount.type === 'percent' ? this.subtotal * discount.amount / 100 : discount.amount);
            });

            this.$store.commit('add_order/setLogRowID', this.logRowID);

            this.$root.bus.$on(['app-loaded', 'create-new-order', 'clear-all'], (params) => {
                this.initEmptyCart(params && params.callback ? params.callback : null);
            });
        },
        mounted: function () {
            this.openProductSearchSelect();
        },
        props: {
            title: {
                default: function() {
                    return '<h2 style="float: left; margin: 12px 12px 0 12px;"><span>Order details</span></h2>';
                }
            },
            addProductButtonTitle: {
                default: function() {
                    return 'Add custom item';
                }
            },
            findProductsSelectPlaceholder: {
                default: function() {
                    return 'Find products...';
                }
            },
            productsTableItemColumnTitle: {
                default: function() {
                    return 'Item';
                }
            },
            productsTableCostColumnTitle: {
                default: function() {
                    return 'Cost';
                }
            },
            productsTableQtyColumnTitle: {
                default: function() {
                    return 'Qty';
                }
            },
            productsTableTotalColumnTitle: {
                default: function() {
                    return 'Total';
                }
            },
            customerProvidedNoteLabel: {
                default: function() {
                    return 'Customer provided note';
                }
            },
            customerProvidedNotePlaceholder: {
                default: function() {
                    return 'Add a note';
                }
            },
            customerPrivateNoteLabel: {
                default: function() {
                    return 'Private note';
                }
            },
            customerPrivateNotePlaceholder: {
                default: function() {
                    return 'Add a note';
                }
            },
            subtotalLabel: {
                default: function() {
                    return 'Subtotal';
                }
            },
            addCouponLabel: {
                default: function() {
                    return 'Add coupon';
                }
            },
            addDiscountLabel: {
                default: function() {
                    return 'Add discount';
                }
            },
            manualDiscountLabel: {
                default: function() {
                    return 'Manual Discount :';
                }
            },
            discountLabel: {
                default: function() {
                    return 'Discount';
                }
            },
            addShippingLabel: {
                default: function() {
                    return 'Add shipping';
                }
            },
            shippingLabel: {
                default: function() {
                    return 'Shipping';
                }
            },
            recalculateButtonLabel: {
                default: function() {
                    return 'Recalculate';
                }
            },
            taxLabel: {
                default: function() {
                    return 'Taxes';
                }
            },
            currencySymbol: {
                default: function() {
                    return '';
                }
            },
            orderTotalLabel: {
                default: function() {
                    return 'Order Total';
                }
            },
            createOrderButtonLabel: {
                default: function() {
                    return 'Create order';
                }
            },
            viewOrderButtonLabel: {
                default: function() {
                    return 'View order';
                }
            },
            sendOrderButtonLabel: {
                default: function() {
                    return 'Send invoice';
                }
            },
            createNewOrderLabel: {
                default: function() {
                    return 'Create new order';
                }
            },
            payOrderNeedProVersionMessage: {
                default: function() {
                    return 'Want to pay order as customer?';
                }
            },
            buyProVersionMessage: {
                default: function() {
                    return 'Buy Pro version';
                }
            },
            tabName: {
                default: function() {
                    return 'add-order';
                }
            },
            isProVersion: {
                default: function() {
                    return false;
                }
            },
            logRowID: {
                default: function() {
                    return '';
                }
            },
            productItemLabels: {
                default: function() {
                    return {};
                }
            },
            noResultLabel: {
                default: function() {
                    return 'Oops! No elements found. Consider changing the search query.';
                }
            },
            couponsEnabled: {
                default: function() {
                    return false;
                }
            },
            activateCouponsLabel: {
                default: function() {
                    return "Please, enable coupons to use discounts.";
                }
            },
            chooseMissingAttributeLabel: {
                default: function() {
                    return "Please, choose all attributes.";
                }
            },
	        duplicateOrderLabel: {
		        default: function() {
			        return 'Duplicate order';
		        }
	        },
            copyCartButtonLabel: {
                default: function() {
                    return null;
                }
            },
            copyCopiedCartButtonLabel: {
                default: function() {
                    return null;
                }
            },
        },
        data: function () {
            return {
                product: null,
                isLoading: false,
                selectedItems: [],
                productsList: [],
                subtotal: 0,
                subtotalWithTax: 0,
                totalDiscount: 0,
                totalTax: 0,
                orderTotal: 0,
                orderTotalWithTax: 0,
                discountAmount: 0,
                calculatedItems: [],
                lastRequest: null,
            };
        },
        watch: {
            additionalProductSearchParams (newVal) {

                if (JSON.stringify(newVal) === JSON.stringify({})) {
                    this.autoDeactivateSelectSearchProduct();
                    return;
                }

                this.autoSelectSearchProduct();
            },
            customer (newVal, oldVal) {
                !this.autoRecalculate && JSON.stringify(newVal) !== JSON.stringify(oldVal) && this.recalculate();
            },
	        shipping (newVal, oldVal) {
		        !this.autoRecalculate && null === newVal && this.recalculate();
	        },
        },
        computed: {
            showHeader () {
                return ! this.getSettingsOption('search_by_cat_and_tag');
            },
            buttonsMessage: {
                get () {
                    return this.$store.state.add_order.buttons_message;
                },
                set (newVal) {
                    this.$store.commit('add_order/setButtonsMessage', newVal)
                },
            },
            cart () {
                return this.$store.state.add_order.cart;
            },
            productSelectOptionsLimit: function () {
                return this.getSettingsOption('number_of_products_to_show');
            },
            productSelectCloseOnSelected: function () {
                return ! this.getSettingsOption('repeat_search');
            },
            productList: function () {
                return this.$store.state.add_order.cart.items;
            },
            feeList: function () {
                return this.$store.state.add_order.cart.fee;
            },
            couponList: function () {
                return this.$store.state.add_order.cart.coupons;
            },
            discount: function () {
                return this.$store.state.add_order.cart.discount;
            },
            customer: function () {
                return this.$store.state.add_order.cart.customer;
            },
            additionalProductSearchParams: function () {
                return this.$store.state.add_order.additional_params_product_search;
            },
            customerProvidedNote: {
                get () {
                    return this.$store.state.add_order.cart.customer_note;
                },
                set (newVal) {
                    this.$store.commit('add_order/setCustomerNote', newVal);
                },
            },
            customerPrivateNote: {
                get () {
                    return this.$store.state.add_order.cart.private_note;
                },
                set (newVal) {
                    this.$store.commit('add_order/setPrivateNote', newVal);
                },
            },
            showCreateOrderButton () {
                return !!! this.$store.state.add_order.cart.order_id
                    && !! this.$store.state.add_order.cart.items.length
                    && !! this.customer
                    && !!! this.$store.state.add_order.cart.edit_order_id;
            },
            showViewOrderButton () {
                return !! this.$store.state.add_order.cart.order_id;
            },
            showSendOrderButton () {
                return !! this.$store.state.add_order.cart.order_id
                    && !! this.customer
                    && !! this.customer.billing_email;
            },
            showCreateNewOrderButton () {
                return !! this.$store.state.add_order.cart.order_id;
            },
            showOrderActions () {
                return this.showCreateOrderButton
                    || !! this.$store.state.add_order.cart.edit_order_id
                    || this.showCreateOrderButton && ! this.$store.state.add_order.cart.edit_order_id
                    || !! this.$store.state.add_order.cart.order_id
                    || this.showViewOrderButton
                    || !! this.$store.state.add_order.cart.order_id
                    || this.showSendOrderButton
                    || this.showCreateNewOrderButton;
            },
            shipping () {
                return this.$store.state.add_order.cart.shipping;
            },
            hideAddDiscount () {
                return this.getSettingsOption('hide_add_discount');
            },
            cacheProductsSessionKey () {
                return this.getSettingsOption('cache_products_session_key');
            },
            autoRecalculate () {
                return this.getSettingsOption('auto_recalculate');
            },
            allowAddProducts () {
                return ! this.getSettingsOption('disable_adding_products');
            },
            allowDuplicateProducts() {
	            return this.getSettingsOption('allow_duplicate_products');
            },
            showCreateOrderButtonOption() {
	            return !!!this.getSettingsOption('hide_button_create_order');
            },
            excludeIDs () {
                return this.allowDuplicateProducts ? [] : [...this.$store.state.add_order.cart.items.filter(function (product) {
		    return typeof product.wpo_readonly_child_item === 'undefined' || ! product.wpo_readonly_child_item;
		}).map(function (product) {
                    return product.variation_id || product.product_id;
                }), ...this.selectedItems];
            },
	    showDuplicateOrder() {
		    return !! this.$store.state.add_order.cart.order_id && this.getSettingsOption('show_duplicate_order_button');
	    },
	    hideCouponWarning() {
		return this.getSettingsOption('hide_coupon_warning');
	    },
	    hideAddShipping() {
		return this.getSettingsOption('hide_add_shipping');
	    },
        },
        methods: {
            asyncFind (query) {

                const CancelToken = this.axios.CancelToken;
                const source      = CancelToken.source();

                this.lastRequest && this.lastRequest.cancel();

            	if ( ! query && query !== null ) {
                    this.isLoading    = false;
                    this.lastRequest  = null;
                    this.productsList = [];
                    return;
                }

                this.isLoading   = true;
                this.lastRequest = source;

                this.axios.get(this.url, {
                    params: {
                        action: 'phone-orders-for-woocommerce',
                        method: 'search_products_and_variations',
                        tab: this.tabName,
                        term: query,
                        exclude: JSON.stringify(this.excludeIDs),
                        additional_parameters: this.additionalProductSearchParams,
                        wpo_cache_products_key: this.cacheProductsSessionKey,
                    },
                    cancelToken: source.token,
                    paramsSerializer: (params) => {
                        return this.qs.stringify(params)
                    }}).then( ( response ) => {

                    var products = [];

                    for(var id in response.data) {
                        var product_id = response.data[id].product_id;
                        if ( this.excludeIDs.indexOf(+product_id) === -1) {
                            products.push({title: response.data[id].title, value: product_id, img: response.data[id].img});
                        }
                    }

                    this.productsList = products;

                    this.isLoading = false;
                }, (thrown) => {
                    if (!this.axios.isCancel(thrown)) {
                        this.isLoading = false;
                    }
                });
            },
            addCoupon () {
                this.openModal('addCoupon');
            },
            addDiscount () {
                this.openModal('addDiscountModal');
            },
            addShipping () {
                this.openModal('shippingModal');
            },
            addCustomProductItem () {
                this.openModal('addCustomItemModal');
            },
	        isAllAttributesSelected() {
            	var all_selected = true;

		        this.$store.state.add_order.cart.items.forEach(function(item){
		        	if ( typeof item.missing_variation_attributes === 'object' ) {
				        item.missing_variation_attributes.forEach(function(attribute){
					        if ( typeof attribute.value !== 'undefined' && ! attribute.value ) {
						        all_selected = false;
					        }
				        });
                    }
                });

                return all_selected;
            },
            onCreateOrder () {
	            if ( ! this.isAllAttributesSelected() ) {
		            alert( this.chooseMissingAttributeLabel );
		            return;
	            }

                if ( ! this.$store.state.add_order.cart.drafted_order_id ) {
                    this.createOrder();
                }

                this.$root.bus.$emit('create-order');
            },
            createOrder () {

                var cart = this.$store.state.add_order.cart;

                if (cart.customer && typeof cart.customer.set_by_default !== 'undefined') {
                    delete cart.customer.set_by_default;
                }

                this.$store.commit('add_order/setIsLoading', true);

                this.axios.post(this.url, this.qs.stringify({
                    action: 'phone-orders-for-woocommerce',
                    method: 'create_order',
                    cart: JSON.stringify(this.$store.state.add_order.cart),
                    created_date_time: this.$store.state.add_order.order_date_timestamp,
                    order_status: this.$store.state.add_order.order_status,
                    tab: this.tabName,
                    log_row_id: this.$store.state.add_order.log_row_id,
                })).then( ( response ) => {
                    this.$store.commit('add_order/setCartOrderID', response.data.data.order_id);
                    this.$store.commit('add_order/setCartOrderIsCompleted', response.data.data.is_completed);
                    this.$store.commit('add_order/setCartOrderPaymentUrl', response.data.data.payment_url);
                    this.$store.commit('add_order/setCartEnabled', false);
                    this.buttonsMessage = response.data.data.message;
                    this.$store.commit('add_order/setIsLoading', false);
                    this.updateStoredCartHash();
                });

            },
            viewOrder () {
                window.open( this.base_admin_url + "post.php?post=" + this.$store.state.add_order.cart.order_id + "&action=edit" );
            },
            sendOrder () {
                this.$store.commit('add_order/setIsLoading', true);
                this.axios.post(this.url, this.qs.stringify({
                    action: 'phone-orders-for-woocommerce',
                    method: 'create_order_email_invoice',
                    order_id: this.$store.state.add_order.cart.order_id,
                    tab: this.tabName,
                })).then( ( response ) => {
                    this.buttonsMessage = response.data.data.message;
                    this.$store.commit('add_order/setIsLoading', false);
                });
            },
            createNewOrder () {

                this.$root.bus.$emit('create-new-order', {callback: () => {
                    this.axios.get(this.url, { params: {
                            action: 'phone-orders-for-woocommerce',
                            method: 'generate_log_row_id',
                            tab: this.tabName,
                    }}).then( ( response ) => {
                            this.$store.commit('add_order/setLogRowID', response.data.data.log_row_id);
                    }, () => {});
                }});
            },
            addProductItemToCart (product) {

	            if ( this.selectedItems.indexOf( + product.value ) === -1 ) {
		            this.selectedItems.push( + product.value );
	            } else if(!this.allowDuplicateProducts)  {
		            return false;
	            }



                this.isLoading = true;

                this.productsList = this.productsList.filter((item) => {
                    return this.excludeIDs.indexOf(+item.value) === -1;
                });

                this.axios.get(this.url, { params: {
                    action: 'phone-orders-for-woocommerce',
                    method: 'load_items',
                    ids: [product.value],
                    tab: this.tabName,
                    qty: 1,
                    customer_id: this.customer.id,
                }}).then( ( response ) => {
                    this.addProductItemsToStore(response.data.data.items);
//                    this.selectedItems = this.selectedItems.filter((item) => {
//                        return item !== +product.value;
//                    });
                    this.asyncFind(this.$refs.productSelectSearch.search);
                    this.isLoading = false;
                    this.product   = null;
                    this.productSelectCloseOnSelected && response.data.data.items.length && this.setFocusToItemQty(response.data.data.items[0]);
                }, () => {
                    this.isLoading = false;
                });
            },
	        setFocusToItemQty( item ) {
		        if ( this.isQtyChangeAvailible( item ) ) {
			        this.$nextTick( () => {
				        this.$refs[this.getProductRef( item )][0].$refs['qty'].focus();
			        } );
		        }
	        },
            isQtyChangeAvailible(item) {
            	return ! item.sold_individually;
            },
            openSelectSearchProduct () {
                this.productsList = [];
            },
            removeFee (fee, index) {
                this.$store.commit('add_order/removeFeeItem', index);
            },
            removeCoupon (coupon, index) {
                this.$store.commit('add_order/removeCouponItem', index);
            },
            autoSelectSearchProduct () {
                this.asyncFind(null);
                this.openProductSearchSelect();
                this.$refs.productSelectSearch.updateSearch(' ');
            },
            autoDeactivateSelectSearchProduct () {
                this.$refs.productSelectSearch.deactivate();
                this.$refs.productSelectSearch.updateSearch('');
            },
            openProductSearchSelect () {
                this.$refs.productSelectSearch.activate();
            },
            recalculate () {

                this.$store.commit('add_order/setIsLoading', true);

                this.axios.post(this.url, this.qs.stringify({
                    action: 'phone-orders-for-woocommerce',
                    method: 'recalculate',
                    cart: JSON.stringify(this.$store.state.add_order.cart),
                    tab: this.tabName,
                    log_row_id: this.$store.state.add_order.log_row_id,
                })).then( ( response ) => {

                    if (response.data.data) {

                        this.subtotal           = response.data.data.subtotal;
                        this.subtotalWithTax    = response.data.data.subtotal_with_tax;
                        this.totalDiscount      = response.data.data.discount;
                        this.totalTax           = response.data.data.taxes;
                        this.orderTotal         = response.data.data.total_ex_tax;
                        this.orderTotalWithTax  = response.data.data.total;
                        this.discountAmount     = response.data.data.discount_amount;
                        this.calculatedItems    = response.data.data.items;

                        this.$store.commit('add_order/setCartParamsChangedByBackend', 1);
                        this.$store.commit('add_order/setShipping', response.data.data.chosen_shipping_method);
                        this.$store.commit('add_order/setShippingMethods', response.data.data.shipping);
                        this.$store.commit('add_order/setCartCoupons', response.data.data.applied_coupons);
                        this.$store.commit('add_order/setCartFees', response.data.data.applied_fees);
                        this.$store.commit('add_order/setCalculatedDeletedItems', response.data.data.deleted_items);

                        let excludeIDs = response.data.data.deleted_items.map(function (item) {
                            return +item.key;
                        });
                        let calculatedKeys = this.calculatedItems.map(function (item) {
                            return +item.key;
                        });

                        let productList = this.productList.filter(function (item) {
                            return excludeIDs.indexOf(+item.key) === -1 && calculatedKeys.indexOf(+item.key) !== -1;
                        });

                        let productKeys = productList.map(function (item) {
	                        return +item.key;
                        });
	                    productList = [];
	                    this.calculatedItems.forEach( function ( item, index ) {
		                    productList.push( item.loaded_product );
	                    } );

	                    this.$store.commit('add_order/setForceCartSet', 1);
	                    this.$store.commit('add_order/setCartItems', productList);

                    } else {
                        this.subtotal           = 0;
                        this.subtotalWithTax    = 0;
                        this.totalDiscount      = 0;
                        this.totalTax           = 0;
                        this.orderTotal         = 0;
                        this.orderTotalWithTax  = 0;
                        this.discountAmount     = 0;
                        this.calculatedItems    = [];
                        this.$store.commit('add_order/setCalculatedDeletedItems', []);
                    }

                    this.$store.commit('add_order/setIsLoading', false);
                    this.$store.commit('add_order/setIsLoadingWithoutBackground', false);
                }, () => {
                    this.$store.commit('add_order/setIsLoading', false);
                    this.$store.commit('add_order/setIsLoadingWithoutBackground', false);
                });

            },
            getProductKey (item) {
            	return item.key? item.key : (item.variation_id ? item.variation_id : item.product_id);
            },
            getProductRef (item) {
                return 'item_' + this.getProductKey(item);
            },
            getProductItemObject (item) {
	            let key = item.key ? 'key' : (item.variation_id ? 'variation_id' : 'product_id');
	            let value = item.key ? item.key : (item.variation_id ? item.variation_id : item.product_id);

	            return this.getObjectByKeyValue(this.calculatedItems, key,value, {});
            },
            loadDefaultSelectedItems () {
                this.loadItems(this.getSettingsOption('item_default_selected'));
            },
            initEmptyCart (callback) {

                this.$store.commit('add_order/setIsLoading', true);

                this.axios.get(this.url, { params: {
                    action: 'phone-orders-for-woocommerce',
                    method: 'init_order',
                    tab: this.tabName,
                }}).then( ( response ) => {

                    var cart = {
                        items: response.data.default_items,
                        custom_fields_values: response.data.default_order_custom_field_values,
                    };

	                let user_id = this.removeGetParameter( "user_id" );
	                if ( user_id ) {
		                this.$root.bus.$emit( 'update-customer', user_id );
	                } else {
		                cart.customer = response.data.default_customer;
	                }

                    this.$store.commit(
                        'add_order/setStateToDefault',
                        {cart: cart}
                    );

                    this.setDefaultCustomFieldsValuesEx();

                    this.updateStoredCartHash();

                    this.$store.commit('add_order/updateOrderStatus', response.data.default_order_status);

	                this.$store.commit('add_order/setLogRowID', response.data.log_row_id);

                    if (typeof callback === 'function') {
                        callback();
                    }

                    this.$store.commit('add_order/setIsLoading', false);
                }, () => {
                    this.$store.commit('add_order/setIsLoading', false);
                });
            },
	        duplicateOrder() {
		        this.$store.commit('add_order/purgeCartOrder');
		        this.$store.commit('add_order/setCartEnabled', true);
		        this.$store.commit('add_order/setButtonsMessage', "")
	        },
        },
        components: {
            Multiselect,
            ProductItem,
            CopyCartButton,
        },
    }
</script>