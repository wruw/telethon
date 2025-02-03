<template>
    <div>
        <b-modal id="shippingModal"
                 ref="modal"
                 :title="shippingMethodLabel"
                 @shown="shown"
        >
            <div id="shipping_method">
                <div v-if="! shippingMethods.length">{{ noShippingMethodsAvailableLabel }}</div>
                <ul>
                    <li v-for="shippingMethod in shippingMethods">
                        <label>
                            <input type="radio"
                                :ref="'radio_button_' + shippingMethod.id"
                                :value="shippingMethod.id"
                                class="shipping_method"
                                v-model="elSelectedShippingMethodID"
                                :key="shippingMethod.id"
                                @keyup.enter="select(shippingMethod)"
                                name="shipping_method"
                            >
                            {{ shippingMethod.label }}
                            <span v-if="isCustomPriceShipping(shippingMethod)">: <span class="woocommerce-Price-amount amount"><span class="woocommerce-Price-currencySymbol">{{ currencySymbol }}</span> <input :ref="'cost_input_' + shippingMethod.id" @keyup.enter="save" type="number" min="0" step='0.01' v-model.number="shippingMethod.cost" @focus="focusCustomPriceShipping(shippingMethod)"></span></span>
                            <span v-else-if="+shippingMethod.cost">: <span class="woocommerce-Price-amount amount"><span class="woocommerce-Price-currencySymbol">{{ currencySymbol }}</span>{{ shippingMethod.cost | formatPrice }}</span></span>
                        </label>
                    </li>
                </ul>

            </div>
            <div slot="modal-footer">
                <b-button @click="close">{{ cancelLabel }}</b-button>
                <b-button @click="remove" :disabled="!shipping" variant="danger">{{ removeLabel }}</b-button>
                <b-button @click="save" variant="primary" :disabled="!elSelectedShippingMethodID">{{ saveLabel }}</b-button>
            </div>
        </b-modal>
    </div>
</template>

<script>

	export default {
                created () {
//                    this.$root.bus.$on('recalculate-cart', () => {
//                        this.loadShippingMethods();
//                    });
                },
		props: {
			shippingMethodLabel: {
				default: function () {
					return 'Shipping method';
				}
			},
			noShippingMethodsAvailableLabel: {
				default: function () {
					return 'No shipping methods available';
				}
			},
			cancelLabel: {
				default: function () {
					return 'Cancel';
				}
			},
			removeLabel: {
				default: function () {
					return 'Remove';
				}
			},
			saveLabel: {
				default: function () {
					return 'Save';
				}
			},
			currencySymbol: {
				default: function () {
					return '$';
				}
			},
			tabName: {
				default: function () {
					return 'add-order';
				}
			},
		},
		data: function () {
                    return {
                        elSelectedShippingMethodID: null,
                        shippingMethods: [],
                    };
		},
		computed: {
            shipping: function () {
                return this.$store.state.add_order.cart.shipping;
            },
			storedShippingMethods: function () {
				return this.$store.state.add_order.shipping_methods;
			},
			autorecalculate: function () {
				return this.getSettingsOption('auto_recalculate');
			},
		},
        watch: {
	        storedShippingMethods(newVal) {
		        this.shippingMethods = newVal;
            },
        },
		methods: {
                    shown() {

                        this.elSelectedShippingMethodID = this.shipping ? this.shipping.id : this.shipping;

                        if (this.shippingMethods.length) {
                            if (this.shipping) {
                                this.$refs['radio_button_' + this.shipping.id][0].focus();
                            } else {
                                this.$refs['radio_button_' + this.shippingMethods[0].id][0].focus();
                            }
                        }
                    },
                    select(shipping) {

                        this.elSelectedShippingMethodID = shipping.id;

                        if (!this.isCustomPriceShipping(shipping)) {
                            this.save();
                            return;
                        }

                        this.$refs['cost_input_' + shipping.id][0].focus();
                    },
                    save() {
                        this.$store.commit(
                            'add_order/setShipping',
                            this.getObjectByKeyValue(this.shippingMethods, 'id', this.elSelectedShippingMethodID)
                        );
                        this.close();
                    },
                    remove() {
                        this.$store.commit('add_order/setShipping', null);
                        this.close();
                    },
                    close() {
                        this.$refs.modal.hide();
                    },
                    isCustomPriceShipping(shipping) {
                        return shipping && shipping.id.startsWith('phone_orders_custom_price');
                    },
                    focusCustomPriceShipping(shipping) {
                        this.elSelectedShippingMethodID = shipping.id;
                    },
                    loadShippingMethods() {
                    	if ( this.autorecalculate ) {
		                    this.axios.post(this.url, this.qs.stringify({
			                    action: 'phone-orders-for-woocommerce',
			                    method: 'get_shipping_rates',
			                    cart: JSON.stringify(this.$store.state.add_order.cart),
			                    tab: this.tabName,
			                    log_row_id: this.$store.state.add_order.log_row_id,
		                    })).then( ( response ) => {
			                    this.shippingMethods = response.data.data;
		                    });
                        }
                    },
		},
	}
</script>
