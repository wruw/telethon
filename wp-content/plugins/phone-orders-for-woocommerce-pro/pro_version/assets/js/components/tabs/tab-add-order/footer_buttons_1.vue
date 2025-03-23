<template>
    <span>
        <button class="btn btn-primary" @click="putOnDraft" v-show="showPutOnHoldButton">
            {{ putOnHoldButtonLabel }}
        </button>
        <button class="btn btn-primary redirect" @click="goToCartPage" v-show="showGoToCartPageButton">
            {{ goToCartPageLabel }}
        </button>
        <button class="btn btn-primary redirect" @click="goToCheckoutPage" v-show="showGoToCheckoutPageButton">
            {{ goToCheckoutPageLabel }}
        </button>
        <button class="btn btn-primary" @click="updateOrder" v-show="showUpdateOrderButton">
            {{ updateOrderButtonLabel }}
        </button>
        <button class="btn btn-primary" @click="cancelUpdateOrder" v-show="showCancelUpdateOrderButton">
            {{ cancelUpdateOrderButtonLabel }}
        </button>
        <button class="btn btn-danger" @click="clearAll" v-show="showClearAllButton">
            {{ clearAllButtonLabel }}
        </button>
        <button class="btn btn-primary" @click="payOrderAsCustomer" v-show="showPayOrderButton && paymentUrl && !hideButtonPayAsCustomerOption" :title="payOrderAsCustomerTitle" :disabled="payOrderAsCustomerDisabled">
            {{ payOrderButtonLabel }}
        </button>
    </span>
</template>

<script>
    export default {
        created: function () {
            this.$root.bus.$on('create-order', () => {
                this.draftToPending();
            });
        },
        props: {
            putOnHoldButtonLabel: {
                default: function() {
                    return 'Put on hold';
                }
            },
	        goToCartPageLabel: {
		        default: function() {
			        return 'Go to Cart';
		        }
	        },
	        goToCheckoutPageLabel: {
		        default: function() {
			        return 'Go to Checkout';
		        }
	        },
            updateOrderButtonLabel: {
                default: function() {
                    return 'Update order';
                }
            },
            cancelUpdateOrderButtonLabel: {
                default: function() {
                    return 'Cancel';
                }
            },
            clearAllButtonLabel: {
                default: function() {
                    return 'Clear all';
                }
            },
            payOrderButtonLabel: {
                default: function() {
                    return 'Pay order as the customer';
                }
            },
            orderIsCompletedTitle: {
                default: function() {
                    return 'Order completed';
                }
            },
            tabName: {
                default: function() {
                    return 'add-order';
                }
            },
        },
        computed: {
            customer: function () {
                return this.$store.state.add_order.cart.customer;
            },
            showCreateOrderButton () {
                return !!! this.$store.state.add_order.cart.order_id
                    && !! this.$store.state.add_order.cart.items.length
                    && !! this.customer
                    && ! this.showUpdateOrderButton;
            },
            showPutOnHoldButton () {
                return this.showCreateOrderButton && !!!this.getSettingsOption('hide_button_put_on_hold');
            },
	        showGoToCartPageButton () {
		        return this.showCreateOrderButton && this.getSettingsOption('show_go_to_cart_button');
	        },
	        showGoToCheckoutPageButton () {
		        return this.showCreateOrderButton && this.getSettingsOption('show_go_to_checkout_button');
	        },
	        hideButtonPayAsCustomerOption () {
		        return this.getSettingsOption('hide_button_pay_as_customer');
	        },
            showUpdateOrderButton () {
                return !! this.$store.state.add_order.cart.edit_order_id;
            },
            showCancelUpdateOrderButton () {
                return !! this.$store.state.add_order.cart.edit_order_id;
            },
            showClearAllButton () {
                return this.showCreateOrderButton
                    && ! this.$store.state.add_order.cart.edit_order_id;
            },
            showPayOrderButton () {
                return !! this.$store.state.add_order.cart.order_id;
            },
            payOrderAsCustomerTitle: function () {
                return this.payOrderAsCustomerDisabled ? this.orderIsCompletedTitle : '';
            },
            payOrderAsCustomerDisabled: function () {
                return !! this.$store.state.add_order.cart.order_is_completed;
            },
            paymentUrl: function () {
                return this.$store.state.add_order.cart.order_payment_url;
            },
        },
        methods: {
            putOnDraft () {

                this.$store.commit('add_order/setIsLoading', true);

                this.axios.post(this.url, this.qs.stringify({
                    action: 'phone-orders-for-woocommerce',
                    method: 'put_on_draft',
                    cart: JSON.stringify(this.$store.state.add_order.cart),
                    tab: this.tabName,
                    log_row_id: this.$store.state.add_order.log_row_id,
	                created_date_time: this.$store.state.add_order.order_date_timestamp,
                })).then( ( response ) => {
                    this.$store.commit('add_order/setCartDraftedOrderID', response.data.data.drafted_order_id);
                    this.$store.commit('add_order/setButtonsMessage', response.data.data.message);
                    this.$store.commit('add_order/setIsLoading', false);
                });
            },
            prepareToGo (where) {
	            this.$store.commit('add_order/setIsLoading', true);

	            this.axios.post(this.url, this.qs.stringify({
		            action: 'phone-orders-for-woocommerce',
		            method: 'prepare_to_redirect',
		            cart: JSON.stringify(this.$store.state.add_order.cart),
			    referrer: window.location.href,
			    is_frontend: typeof window.wpo_frontend === 'undefined' ? 0 : 1,
			    where: where,
		            tab: this.tabName,
	            })).then( ( response ) => {
		            if ( response.data.success ) {
			            this.$store.commit('add_order/enableUnconditionalRedirect', true);
			            window.open( response.data.data.url, "_self" );
		            } else {
			            this.$store.commit('add_order/setIsLoading', false);
		            }
	            });
            },
	        goToCartPage () {
		        this.prepareToGo('cart');
            },
	        goToCheckoutPage () {
		        this.prepareToGo('checkout');
            },
            updateOrder () {

                this.$store.commit('add_order/setIsLoading', true);

                this.axios.post(this.url, this.qs.stringify({
                    action: 'phone-orders-for-woocommerce',
                    method: 'update_order',
                    order_id: this.$store.state.add_order.cart.edit_order_id || this.$store.state.add_order.cart.order_id,
                    cart: JSON.stringify(this.$store.state.add_order.cart),
                    tab: this.tabName,
	                created_date_time: this.$store.state.add_order.order_date_timestamp,
                    order_status: this.$store.state.add_order.order_status,
                })).then( ( response ) => {
                    this.$store.commit('add_order/setCartOrderID', this.$store.state.add_order.cart.edit_order_id);
                    this.$store.commit('add_order/setCartEditOrderID', null);
                    this.$store.commit('add_order/setCartEnabled', false);
                    this.$store.commit('add_order/setButtonsMessage', response.data.data.message);
                    this.$store.commit('add_order/setIsLoading', false);
                    this.updateStoredCartHash();
                });

                this.$root.bus.$emit('update-order');
            },
            cancelUpdateOrder () {
                this.$store.commit('add_order/setCartOrderID', this.$store.state.add_order.cart.edit_order_id);
                this.$store.commit('add_order/setCartEditOrderID', null);
                this.$store.commit('add_order/setCartEnabled', false);
                this.$store.commit('add_order/setButtonsMessage', '');
                this.updateStoredCartHash();

                this.$root.bus.$emit('cancel-update-order');
            },
            clearAll () {
                this.$root.bus.$emit('clear-all');
            },
            payOrderAsCustomer () {
	            this.$store.commit('add_order/setIsLoading', true);

	            this.axios.post(this.url, this.qs.stringify({
		            action: 'phone-orders-for-woocommerce',
		            method: 'set_payment_cookie',
		            order_id: this.$store.state.add_order.cart.order_id,
			    referrer: window.location.href,
			    is_frontend: typeof window.wpo_frontend === 'undefined' ? 0 : 1,
		            tab: this.tabName,
	            })).then( ( response ) => {
	            	if ( response.data.success ) {
			            window.open( this.paymentUrl, "_self" );
                    } else {
			            this.$store.commit('add_order/setIsLoading', false);
		            }
	            });
            },
            draftToPending () {

                if ( ! this.$store.state.add_order.cart.drafted_order_id ) {
                    return;
                }

                this.$store.commit('add_order/setIsLoading', true);

                this.axios.post(this.url, this.qs.stringify({
                    action: 'phone-orders-for-woocommerce',
                    method: 'move_from_draft',
                    drafted_order_id: this.$store.state.add_order.cart.drafted_order_id,
                    tab: this.tabName,
	                created_date_time: this.$store.state.add_order.order_date_timestamp,
	                order_status: this.$store.state.add_order.order_status,
                })).then( ( response ) => {
                    this.$store.commit('add_order/setCartOrderID', response.data.data.order_id);
                    this.$store.commit('add_order/setCartDraftedOrderID', null);
                    this.$store.commit('add_order/setCartOrderPaymentUrl', response.data.data.payment_url);
                    this.$store.commit('add_order/setCartEnabled', false);
                    this.buttonsMessage = response.data.data.message;
                    this.$store.commit('add_order/setIsLoading', false);
                });

            },
        },
    }
</script>