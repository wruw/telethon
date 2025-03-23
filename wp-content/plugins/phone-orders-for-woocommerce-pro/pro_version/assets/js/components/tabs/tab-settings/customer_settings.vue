<template>
    <tr>
        <td colspan=2>
            <table class="form-table">
                <tbody>
                    <tr>
                        <td colspan=2>
                            <b>{{ title }}</b>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            {{ cacheCustomerTimeoutLabel }}
                        </td>
                        <td>
                            <input type="hidden" name="cache_customers_session_key" v-model="sessionKey">
                            <input type="hidden" name="cache_customers_reset" id="cache_customers_reset" v-model.number="cacheCustomersReset">
                            <input type="number" class="option_hours" v-model.number="timeout" id="cache_customers_timeout" name="cache_customers_timeout" min=0>
                            {{ hoursLabel }}
                            <span v-if="timeout">
                                <button id="cache_customers_disable_button" @click="disableCache" class="btn btn-primary">
                                    {{ cacheCustomersDisableButton }}
                                </button>
                                <button id="cache_customers_reset_button" @click="resetCache" class="btn btn-danger">
                                    {{ cacheCustomersResetButton }}
                                </button>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            {{ searchCustomerInOrdersLabel }}
                        </td>
                        <td>
                            <input type="checkbox" class="option" v-model="searchInOrders" name="search_customer_in_orders">
                        </td>
                    </tr>
                    <tr>
                        <td>
                            {{ searchAllCustomerLabel }}
                        </td>
                        <td>
                            <input type="checkbox" class="option" v-model="searchAllFields" name="search_all_customer_fields">
                        </td>
                    </tr>
                    <tr>
                        <td>
                            {{ numberOfCustomersToShowLabel }}
                        </td>
                        <td>
                            <input type="number" class="option_number" v-model.number="numberOfCustomers" name="number_of_customers_to_show" min=0>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            {{ defaultCustomerLabel }}
                        </td>
                        <td>
                            <multiselect
                                style="width: 100%;max-width: 800px;"
                                label="title"
                                v-model="selectedCustomer"
                                :options="defaultCustomersList"
                                track-by="value"
                                id="ajax"
                                :placeholder="selectDefaultCustomerPlaceholder"
                                :loading="isLoading"
                                :internal-search="false"
                                :show-no-results="true"
                                @search-change="asyncFind"
                                :hide-selected="false"
                                :searchable="true"
                                open-direction="bottom"
                                :show-labels="false"
                            >
                                <template slot="clear" slot-scope="props">
                                    <div class="multiselect__clear" v-show="defaultCustomerID && !props.isOpen" @mousedown.prevent.stop="clearAll(props.search)"></div>
                                </template>
                                <span slot="noResult">{{ noResultLabel }}</span>
                                <template slot="singleLabel" slot-scope="props">
                                    <span v-html="props.option.title"></span>
                                </template>
                                <template slot="option" slot-scope="props">
                                    <span v-html="props.option.title"></span>
                                </template>
                          </multiselect>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            {{ updateCustomersProfileAfterCreateOrderLabel }}
                        </td>
                        <td>
                            <input type="checkbox" class="option" v-model="updateCustomersProfile" name="update_customers_profile_after_create_order">
                        </td>
                    </tr>
                    <tr>
                        <td>
                            {{ hideShippingSectionLabel }}
                        </td>
                        <td>
                            <input type="checkbox" class="option" v-model="hideShipping" name="hide_shipping_section">
                        </td>
                    </tr>
                    <tr>
                        <td>
                            {{ doNotSubmitOnEnterLastFieldLabel }}
                        </td>
                        <td>
                            <input type="checkbox" class="option" v-model="elDoNotSubmitOnEnterLastField" name="do_not_submit_on_enter_last_field">
                        </td>
                    </tr>
                </tbody>
            </table>
        </td>
    </tr>
</template>

<script>

    import Multiselect from 'vue-multiselect';

    export default {
        created () {
            this.$root.bus.$on('settings-saved', this.onSettingsSaved);
        },
        props: {
            title: {
                default: function() {
                    return 'Customers';
                },
            },
            hoursLabel: {
                default: function() {
                    return 'hours';
                },
            },
            cacheCustomerTimeoutLabel: {
                default: function() {
                    return 'Caching search results';
                },
            },
            cacheCustomersSessionKey: {
                default: function() {
                    return '';
                },
            },
            cacheCustomersTimeout: {
                default: function() {
                    return 0;
                },
            },
            cacheCustomersDisableButton: {
                default: function() {
                    return 'Disable cache';
                },
            },
            cacheCustomersResetButton: {
                default: function() {
                    return 'Reset cache';
                },
            },
            cacheCustomersResetButton: {
                default: function() {
                    return 'Reset cache';
                },
            },
            searchAllCustomerFields: {
                default: function() {
                    return false;
                },
            },
	        searchCustomerInOrders: {
		        default: function() {
			        return false;
		        },
	        },
            searchAllCustomerLabel: {
                default: function() {
                    return 'Customer search by shipping/billing fields';
                },
            },
	        searchCustomerInOrdersLabel: {
                default: function() {
                    return 'Search for customer in orders';
                },
            },
            numberOfCustomersToShowLabel: {
                default: function() {
                    return 'Number of customers to show in autocomplete';
                },
            },
            numberOfCustomersToShow: {
                default: function() {
                    return 0;
                },
            },
            defaultCustomerLabel: {
                default: function() {
                    return 'Default customer';
                },
            },
            defaultCustomerObject: {
                default: function() {
                    return {};
                },
            },
            updateCustomersProfileAfterCreateOrderLabel: {
                default: function() {
                    return "Automatically update customer's profile on order creation";
                },
            },
            updateCustomersProfileAfterCreateOrder: {
                default: function() {
                    return false;
                },
            },
            selectDefaultCustomerPlaceholder: {
                default: function() {
                    return 'Type to search';
                },
            },
	        hideShippingSectionLabel: {
		        default: function() {
			        return 'Hide shipping section';
		        },
	        },
	        hideShippingSection: {
		        default: function() {
			        return false;
		        },
	        },
	        doNotSubmitOnEnterLastFieldLabel: {
		        default: function() {
			        return "Don't close customer/address form automatically";
		        },
	        },
	        doNotSubmitOnEnterLastField: {
		        default: function() {
			        return false;
		        },
	        },
            noResultLabel: {
                default: function() {
                    return 'Oops! No elements found. Consider changing the search query.';
                },
            },
        },
        watch: {
            selectedCustomer (newVal, oldVal) {
                this.defaultCustomerID = this.getKeyValueOfObject(newVal, 'value');
            },
        },
        data () {
            return {
                isLoading: false,
                defaultCustomersList: [],
                selectedCustomer: this.defaultCustomerObject,
                sessionKey: this.cacheCustomersSessionKey,
                cacheCustomersReset: 0,
                timeout: this.cacheCustomersTimeout,
	            searchInOrders: this.searchCustomerInOrders,
                searchAllFields: this.searchAllCustomerFields,
                numberOfCustomers: this.numberOfCustomersToShow,
                defaultCustomerID: this.getKeyValueOfObject(this.defaultCustomerObject, 'value'),
                updateCustomersProfile: this.updateCustomersProfileAfterCreateOrder,
	            hideShipping: this.hideShippingSection,
	            elDoNotSubmitOnEnterLastField: this.doNotSubmitOnEnterLastField,
	            lastRequest: null,
            };
        },
        methods: {
            disableCache () {
                this.timeout = 0;
                this.saveSettingsByEvent();
            },
            resetCache () {
                this.cacheCustomersReset = 1;
                this.saveSettingsByEvent();
            },
            getSettings () {
                return {
                    cache_customers_session_key: this.sessionKey,
                    cache_customers_reset: this.cacheCustomersReset,
                    cache_customers_timeout: this.timeout,
	                search_customer_in_orders: this.searchInOrders,
                    search_all_customer_fields: this.searchAllFields,
                    number_of_customers_to_show: this.numberOfCustomers,
                    default_customer_id: this.defaultCustomerID,
                    update_customers_profile_after_create_order: this.updateCustomersProfile,
	                hide_shipping_section: this.hideShipping,
	                do_not_submit_on_enter_last_field: this.elDoNotSubmitOnEnterLastField,
                };
            },
            asyncFind (query) {

	            const CancelToken = this.axios.CancelToken;
	            const source      = CancelToken.source();

	            this.lastRequest && this.lastRequest.cancel();

	            if ( ! query && query !== null ) {
		            this.isLoading    = false;
		            this.lastRequest  = null;
		            return;
	            }

                this.isLoading = true;
	            this.lastRequest  = source;

                this.axios.get(this.url, { params: {
                    action: 'woocommerce_json_search_customers',
                    security: this.search_customers_nonce,
                    term: query,
                }, cancelToken: source.token,
                }).then( ( response ) => {

                    var customers = [];

                    for(var id in response.data) {
                        if (response.data.hasOwnProperty(id)) {
                            customers.push({title: response.data[id], value: id});
                        }
                    }

                    this.defaultCustomersList = customers;

                    this.isLoading = false;
                }, (thrown) => {
	                if (!this.axios.isCancel(thrown)) {
		                this.isLoading = false;
	                }
                });
            },
            clearAll () {
                this.selectedCustomer = null;
            },
            onSettingsSaved (settings) {
                this.sessionKey          = settings.cache_customers_session_key;
                this.cacheCustomersReset = settings.cache_customers_reset;
            },
        },
        components: {
            Multiselect,
        },
    }
</script>