<template>
    <div class="postbox disable-on-order">
        <span class="handlediv button-link custom-button-link">
            <a href="" class="clear-customer" v-show="customer" @click.prevent.stop="cartEnabled ? clearCustomer() : null" :class="{disabled: !cartEnabled}">&times;</a>
        </span>
        <h2>
            <span v-if="allowAddCustomers">{{ title }}</span>
            <span v-else>{{ titleOnlyFind }}</span>
        </h2>
        <div class="inside">
            <div id="search-customer-box">
                <a v-if="allowAddCustomers" href="#" @click.prevent.stop="cartEnabled ? createNewCustomer() : null" :class="{disabled: !cartEnabled}">
                    {{ createNewCustomerLabel }}
                </a>
                <multiselect
                    style="width: 100%;"
                    label="title"
                    v-model="customer"
                    :options="customerList"
                    track-by="value"
                    id="ajax"
                    :placeholder="selectCustomerPlaceholder"
                    :loading="isLoading"
                    :internal-search="false"
                    :show-no-results="true"
                    @search-change="asyncFind"
                    :hide-selected="false"
                    :searchable="true"
                    open-direction="bottom"
                    :custom-label="customLabel"
                    @input="onChangeCustomer"
                    :disabled="!cartEnabled"
                    :options-limit="+customerSelectOptionsLimit"
                    :show-labels="false"
                >
                    <span slot="noResult">{{ noResultLabel }}</span>
                    <template slot="option" slot-scope="props">
                        <span v-html="props.option.title"></span>
                    </template>
                </multiselect>
            </div>
            <div v-show="showCustomersUrl" id="customer_urls">
                <a v-show="profileUrl" :href="profileUrl" target="_blank">{{profileUrlTitle}}</a>
                <a v-show="otherOrderUrl" :href="otherOrderUrl" target="_blank">{{otherOrderUrlTitle}}</a>
            </div>
            <div>
                <div class="order_data_column phone-orders-customer-data-details">
                    <div class="billing-details" data-edit-address="billing" @click.prevent.stop="cartEnabled ? onClick('billing') : null">
                        <h4>
                            {{ billingDetailsLabel }}
                            <a href="#" class="edit_address" v-show="customer" @click.prevent.stop="cartEnabled ? onClick('billing') : null">Edit</a>
                        </h4>
                        <p>
                            <span v-if="billingAddress" v-html="billingAddress"></span>
                            <span v-else>
                                {{ shippingAddress ? billingAddressAsShippingMessage : emptyBillingAddressMessage }}
                            </span>
                        </p>
                    </div>
                    <span v-show="! customerIsEmpty">
                        <slot name="tax-exempt"></slot>
                        <p v-show="!hideShippingSection">
                            <label>
                                <input type="checkbox" v-model="shipDifferent" v-bind:disabled="!cartEnabled">
                                {{ shipDifferentLabel }}
                            </label>
                        </p>
                        <div v-show="shipDifferent" @click.prevent.stop="cartEnabled ? onClick('shipping') : null">
                            <h4>
                                {{ shipDetailsLabel }}
                                <a href="#" class="edit_address" v-if="customer"
                                   @click.prevent.stop="cartEnabled ? onClick('shipping') : null">Edit</a>
                            </h4>
                            <p>
                                <span v-if="shippingAddress" v-html="shippingAddress"></span>
                                <span v-else>
                                    {{ emptyShippingAddressMessage }}
                                </span>
                            </p>
                        </div>
                    </span>
                </div>
            </div>
            <pro-features v-if="!isProVersion" v-bind="proFeaturesSettings"></pro-features>
        </div>
        <slot name="save-to-customer"></slot>
    </div>
</template>

<style>
    .wp-core-ui .button-link.custom-button-link {
        text-decoration: none;
    }

    #search-customer-box .multiselect .multiselect__option {
        white-space: normal;
    }

    #customer_urls {
        text-align: right;
    }

    .phone-orders-customer-data-details {
	margin-top: 12px;
    }

</style>

<script>

    import Multiselect from 'vue-multiselect';
    import ProFeatures from './pro_features.vue';

    export default {
        props: {
            title: {
                default: function() {
                    return 'Find or create a customer';
                }
            },
	        titleOnlyFind: {
                default: function() {
                    return 'Find a customer';
                }
            },
            createNewCustomerLabel: {
                default: function() {
                    return 'New customer';
                }
            },
            billingDetailsLabel: {
                default: function() {
                    return 'Billing Details';
                }
            },
            shipDifferentLabel: {
                default: function() {
                    return 'Ship to a different address?';
                }
            },
            shipDetailsLabel: {
                default: function() {
                    return 'Shipping Details';
                }
            },
            emptyBillingAddressMessage: {
                default: function() {
                    return 'No billing address was provided.';
                }
            },
            emptyShippingAddressMessage: {
                default: function() {
                    return 'No shipping address was provided.';
                }
            },
            billingAddressAsShippingMessage: {
                default: function() {
                    return 'Same as shipping address.';
                }
            },
            tabName: {
                default: function() {
                    return 'add-order';
                }
            },
            proFeaturesSettings: {
                default: function() {
                    return {};
                }
            },
            isProVersion: {
                default: function() {
                    return false;
                }
            },
            requiredFieldsForPopUp: {
                default: function() {
                    return {};
                }
            },
            selectCustomerPlaceholder: {
                default: function() {
                    return 'Guest';
                }
            },
	        profileUrlTitle: {
		        default: function() {
			        return 'Profile &rarr;';
		        }
            },
	        otherOrderUrlTitle: {
		        default: function() {
			        return 'View other orders &rarr;';
		        }
	        },
        },
        data: function () {
            return {
                isLoading: false,
                customerList: [],
	            lastRequest: null,
            };
        },
        computed: {
            customer: {
                get () {
                    return this.storedCustomer;
                },
                set () {},
            },
            storedCustomer: {
                get: function () {
                    return this.$store.state.add_order.cart.customer;
                },
                set: function (newVal) {

		    this.$root.bus.$emit('update-customer-request', {
			customer: newVal,
			callback: (response) => {
			    this.$store.commit('add_order/updateCustomer', response.data.data.customer);
			},
		    });

                },
            },
            customerIsEmpty: function() {
            	return this.customer === "" || this.customer === null;
            },
            billingAddress: function () {
                return this.customer ? this.customer.formatted_billing_address : '';
            },
            shippingAddress: function () {
                return this.customer ? this.customer.formatted_shipping_address : '';
            },
	    profileUrl: function () {
		    return this.customer ? this.customer.profile_url : '';
	    },
	    otherOrderUrl: function () {
		    return this.customer ? this.customer.other_order_url : '';
	    },
            shipDifferent: {
                get: function () {
                    return this.customer ? this.customer['ship_different_address'] : false;
                },
                set: function (newVal) {
                    if ( ! this.customer ) {

			var customer = {
			    ship_different_address: newVal,
			    billing_city: this.getSettingsOption('default_city'),
			    billing_country: this.getSettingsOption('default_country'),
			    billing_state: this.getSettingsOption('default_state'),
			    billing_postcode: this.getSettingsOption('default_postcode'),
			};

			if (newVal) {
			    customer = Object.assign(customer, {
				shipping_city: this.getSettingsOption('default_city'),
				shipping_country: this.getSettingsOption('default_country'),
				shipping_state: this.getSettingsOption('default_state'),
				shipping_postcode: this.getSettingsOption('default_postcode'),
			    });
			}

			this.storedCustomer = customer;

                    } else {

			var customer = JSON.parse(JSON.stringify(this.storedCustomer));

			customer['ship_different_address'] = newVal;

			if (customer['ship_different_address']) {

			    var shipping_fields = ['address_1', 'address_2', 'city', 'company', 'country', 'first_name', 'last_name', 'postcode', 'state'];

			    shipping_fields.forEach((field) => {
				if (typeof this.storedCustomer['billing_' + field] !== 'undefined') {
				    customer['shipping_' + field] = this.storedCustomer['billing_' + field];
				}
			    });
			}

			this.storedCustomer = customer;
                    }
                },
            },
            customersSessionKey () {
                return this.getSettingsOption('cache_customers_session_key');
            },
            customerSelectOptionsLimit: function () {
                return this.getSettingsOption('number_of_customers_to_show');
            },
            hideShippingSection: function () {
                return this.getSettingsOption('hide_shipping_section');
            },
	    allowAddCustomers () {
		    return ! this.getSettingsOption('disable_creating_customers');
	    },
	    showCustomersUrl() {
		return typeof window.wpo_frontend === 'undefined' && (this.profileUrl || this.otherOrderUrl);
	    },
        },
        created: function () {
            this.$root.bus.$on('update-customer', (newId) => {
                    this.getCustomerByCustomerType(newId);
            });
        },
        methods: {
        	onClick ($address_type) {
		        let data = {};
		        data.customer = this.customer;
                data.addressType = $address_type;
                data.fields = {};

                for ( let $field_name in this.requiredFieldsForPopUp) {
                    if ( this.requiredFieldsForPopUp.hasOwnProperty( $field_name ) ) {
                        let $field = this.requiredFieldsForPopUp[$field_name];
	                    if ( this.customer ) {
		                    $field['value'] = this.customer[$address_type + '_' + $field_name];
	                    } else {
		                    $field['value'] = '';
                        }
                        data.fields[$field_name] = $field;
                    }
                }

                this.$root.bus.$emit('edit-customer-address', data);
            },
            createNewCustomer () {
                this.openModal('addCustomer');
            },
            clearCustomer () {
                this.storedCustomer = null;
            },
            customLabel (customer) {
                return ( customer.id && customer.id !== '0' ) ?
                            `${customer.billing_first_name} ${customer.billing_last_name} (#${customer.id} - ${customer.billing_email})`
                        :
                            this.selectCustomerPlaceholder;
            },
            onChangeCustomer (customer) {
                this.getCustomer(customer.value, customer.type);
            },
            asyncFind (query) {

	            const CancelToken = this.axios.CancelToken;
	            const source      = CancelToken.source();

	            this.lastRequest && this.lastRequest.cancel();

	            if ( ! query && query !== null ) {
		            this.isLoading    = false;
		            this.lastRequest  = null;
		            this.customerList = [];
		            return;
	            }

                this.isLoading = true;
	            this.lastRequest  = source;

                this.axios.get(this.url, { params: {
                    action: 'woocommerce_json_search_customers',
                    wpo_find_customer : 1,
                    wpo_cache_customers_key: this.customersSessionKey,
                    security: this.search_customers_nonce,
                    term: query,
                },
	                cancelToken: source.token,
                }).then( ( response ) => {

                    var customers = [];

                    response.data.forEach(function (item) {
                        customers.push({title: item.title, value: item.id, type: item.type});
                    });

                    this.customerList = customers;

                    this.isLoading = false;

                }, (thrown) => {
	                if (!this.axios.isCancel(thrown)) {
		                this.isLoading = false;
                    }
                });
            },
            getCustomerByCustomerType (id, callback) {
                this.getCustomer(id, 'customer', callback);
            },
            getCustomer (id, type, callback) {

                this.axios.get(this.url, { params: {
                    action: 'phone-orders-for-woocommerce',
                    method: 'get_customer',
                    tab: this.tabName,
                    id: id,
                    type: type,
                }}).then( ( response ) => {

                    if (typeof callback === 'function') {
                        callback(response);
                    } else {
                        this.storedCustomer = response.data.data;
                    }

                    this.isLoading  = false;
                }, () => {
                    this.isLoading = false;
                });
            },
        },
        components: {
            Multiselect,
            ProFeatures,
        },
    }
</script>