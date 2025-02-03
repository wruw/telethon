<template>
    <tr v-show="shown">
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
                            {{ autoRecalculateLabel }}
                        </td>
                        <td>
                            <input type="checkbox" class="option" v-model="recalculate" name="auto_recalculate">
                        </td>
                    </tr>
                    <tr>
                        <td>
                            {{ orderPaymentMethodLabel }}
                        </td>
                        <td>
                            <multiselect
                                :allow-empty="false"
                                :hide-selected="true"
                                :searchable="false"
                                style="width: 100%;max-width: 800px;"
                                label="title"
                                v-model="paymentMethod"
                                :options="orderPaymentMethodsList"
                                track-by="value"
                                :show-labels="false"
                            ></multiselect>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            {{ orderStatusLabel }}
                        </td>
                        <td>
                            <multiselect
                                :allow-empty="false"
                                :hide-selected="true"
                                :searchable="false"
                                style="width: 100%;max-width: 800px;"
                                label="title"
                                v-model="statusOrder"
                                :options="orderStatusesList"
                                track-by="value"
                                :show-labels="false"
                            ></multiselect>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            {{ switchCustomerInCartLabel }}<br>
                            <i>{{ switchCustomerInCartLabelTip }}</i>
                        </td>
                        <td>
                            <input type="checkbox" class="option" v-model="tmpSwitchCustomerInCart" name="switch_customer_while_calc_cart">
                        </td>
                    </tr>
                    <tr>
                        <td>
                            {{ googleMapAPIKeyLabel }}

                            <div class="link-note">
                                <a href="https://algolplus.freshdesk.com/solution/articles/25000018340-how-to-get-api-key-for-address-autocomplete" target="_blank">{{ googleMapAPIKeyLinkLabel }}</a>
                            </div>
                        </td>
                        <td>
                            <span class="block-input-check-map-api">
                                <input type="text" class="option" v-model.trim="mapAPIKey" name="google_map_api_key">
                                <span class="fa-icon green-color" v-show="mapAPIKeyIsValid === true" :title="validatedMapAPIKeySuccessTitle">
                                    <fa-icon icon="check-circle"/>
                                </span>
                                <span class="fa-icon red-color" v-show="mapAPIKeyIsValid === false" :title="validatedMapAPIKeyErrorTitle">
                                    <fa-icon icon="exclamation-circle"/>
                                </span>
                            </span>
                            <button class="btn btn-secondary btn-sm btn-check-api-key" @click="validateMapAPIKey" v-show="!!mapAPIKey">
                                {{ validateMapAPIKeyLabel }}
                                <loader v-show="isChecking"></loader>
                            </button>
                        </td>
                    </tr>
		    <slot name="pro-common-settings"></slot>
                </tbody>
            </table>
        </td>
    </tr>
</template>

<style>

    #phone-orders-app .btn.btn-check-api-key {
        padding: 3px 12px;
    }

    .btn-check-api-key .v-spinner {
        display: inline-block;
        vertical-align: middle;
        height: 15px;
    }

    .btn-check-api-key .v-spinner .v-pulse {
        animation-duration: 1s !important;
        height: 10px !important;
        width: 10px !important;
    }

    .block-input-check-map-api .fa-icon.green-color {
        color: green;
    }

    .block-input-check-map-api .fa-icon.red-color {
        color: red;
    }

    .block-input-check-map-api .fa-icon {
        position: absolute;
        top: 0;
        right: 15px;
    }

    .block-input-check-map-api {
        position: relative;
    }

    .block-input-check-map-api .option {
        padding-right: 30px;
    }

    .link-note {
        margin-top: 5px;
        font-size: 13px;
    }

</style>

<script>

    import Multiselect from 'vue-multiselect';

    var loader = require('vue-spinner/dist/vue-spinner.min').PulseLoader;

    import {library} from '@fortawesome/fontawesome-svg-core';
    import {faCheckCircle, faExclamationCircle} from '@fortawesome/free-solid-svg-icons';
    import {FontAwesomeIcon as FaIcon} from '@fortawesome/vue-fontawesome';

    library.add(faCheckCircle, faExclamationCircle)

    export default {
        props: {
            title: {
                default: function() {
                    return 'Common';
                },
            },
            tabKey: {
                default: function() {
                    return 'commonSettings';
                },
            },
            autoRecalculateLabel: {
                default: function() {
                    return 'Automatically update Shipping/Taxes/Totals';
                },
            },
            autoRecalculate: {
                default: function() {
                    return false;
                },
            },
            orderPaymentMethodLabel: {
                default: function() {
                    return 'Set payment method for created order';
                },
            },
            orderPaymentMethod: {
                default: function() {
                    return '';
                },
            },
            orderPaymentMethodsList: {
                default: function() {
                    return [];
                },
            },
            orderStatusLabel: {
                default: function() {
                    return 'Set status for created order';
                },
            },
            orderStatus: {
                default: function() {
                    return '';
                },
            },
            orderStatusesList: {
                default: function() {
                    return [];
                },
            },
            googleMapAPIKeyLabel: {
                default: function() {
                    return 'Google Map API Key';
                },
            },
            validateMapAPIKeyLabel: {
                default: function() {
                    return 'Check';
                },
            },
            validatedMapAPIKeySuccessTitle: {
                default: function() {
                    return 'Check';
                },
            },
            validatedMapAPIKeyErrorTitle: {
                default: function() {
                    return 'Check';
                },
            },
            googleMapAPIKey: {
                default: function() {
                    return '';
                },
            },
            googleMapAPIKeyLinkLabel: {
                default: function() {
                    return 'How to get api key';
                },
            },
            switchCustomerInCartLabel: {
                default: function() {
                    return 'Switch customer during cart calculations';
                },
            },
            switchCustomerInCartLabelTip: {
                default: function() {
                    return 'required by some pricing plugins';
                },
            },
            switchCustomerInCart: {
                default: function() {
                    return false;
                },
            },
        },
        data () {
            return {
                recalculate: this.autoRecalculate,
                paymentMethod: this.getObjectByKeyValue(this.orderPaymentMethodsList, 'value', this.orderPaymentMethod),
                statusOrder: this.getObjectByKeyValue(this.orderStatusesList, 'value', this.orderStatus),
                mapAPIKey: this.googleMapAPIKey,
                mapAPIKeyIsValid: null,
                isChecking: false,
                tmpSwitchCustomerInCart: this.switchCustomerInCart,
		shown: false,
            };
        },
        watch: {
            mapAPIKey() {
                this.mapAPIKeyIsValid = null;
            },
        },
        methods: {
            getSettings () {

		var settings = {
                    auto_recalculate: this.recalculate,
                    order_payment_method: this.getKeyValueOfObject(this.paymentMethod, 'value'),
                    order_status: this.getKeyValueOfObject(this.statusOrder, 'value'),
                    google_map_api_key: this.mapAPIKey,
                    switch_customer_while_calc_cart: this.tmpSwitchCustomerInCart,
                };

		var childsSettings = {};

                this.$children.forEach(function (child) {
		    if (typeof child.getSettings === 'function') {
			childsSettings = Object.assign(childsSettings, child.getSettings());
		    }
                });

                return Object.assign(settings, childsSettings);
            },
            validateMapAPIKey() {

                this.isChecking = true;
                this.mapAPIKeyIsValid = null;

                var self = this;

                var successCallback = function () {

                    var addressGeo = 'Stephansplatz 1 Vienna 1010 Austria';
                    var geocoder   = new google.maps.Geocoder();

                    geocoder.geocode( { 'address': addressGeo}, function(results, status) {

                        if (status === google.maps.GeocoderStatus.OK) {
                            self.mapAPIKeyIsValid = true;
                        } else if (status === google.maps.GeocoderStatus.REQUEST_DENIED) {
                            //alert( 'ERROR: Access denied' );
                            self.mapAPIKeyIsValid = false;
                        } else {
                            //alert( 'ERROR: Error occured accessing the API.' );
                            self.mapAPIKeyIsValid = false;
                        }

                        self.isChecking = false;
                    });
                }

                var errorCallback = function () {
                    self.mapAPIKeyIsValid = false;
                    self.isChecking = false;
                }

                this.registerGoogleMapJs(this.mapAPIKey, successCallback, errorCallback);
            },
            getTabsHeaders() {
                return {
		    key: this.tabKey,
		    title: this.title,
		};
            },
            showOption(key) {
                this.shown = this.tabKey === key;
            },
        },
        components: {
            Multiselect,
            loader,
            FaIcon
        },
    }
</script>