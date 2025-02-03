<template>
    <table class="form-table">
        <tbody>
	    <tr>
		<td colspan=2>
		    <b>{{ title }}</b>
		</td>
	    </tr>

		<tr>
			<td>
				{{ hideFindOrdersLabel }}
			</td>
			<td>
				<input type="checkbox" class="option" v-model="elHideFindOrders" name="hide_find_orders">
			</td>
		</tr>


		<tr>
		<td>
		    {{ cacheTimeoutLabel }}
		</td>
		<td>
		    <input type="hidden" name="cache_orders_session_key" v-model="orderSessionKey">
		    <input type="hidden" name="cache_orders_reset" id="cache_orders_reset" v-model.number="cacheReset">
		    <input type="number" class="option_hours" v-model.number="orderTimeout" id="cache_orders_timeout"
			   name="cache_orders_timeout" min="0">
		    {{ hoursLabel }}
		    <span v-if="orderTimeout">
			    <button id="cache_orders_disable_button" @click="disableCache" class="btn btn-primary">
				{{ disableCacheButtonLabel }}
			    </button>
			    <button id="cache_orders_reset_button" @click="resetCache" class="btn btn-danger">
				{{ resetCacheButtonLabel }}
			    </button>
			</span>
		</td>
	    </tr>

	    <tr>
		<td>
		    {{ copyOnlyProcOrCompOrdersLabel }}
		</td>
		<td>
		    <input type="checkbox" class="option" v-model="elCopyOnlyPaidOrders" name="copy_only_paid_orders">
		</td>
	    </tr>

	    <tr>
		<td>
		    {{ showButtonCopyOrderLabel }}
		</td>
		<td>
		    <input type="checkbox" class="option" v-model="elButtonForFindOrder" name="button_for_find_orders">
		</td>
	    </tr>

	    <tr>
		<td>
		    {{ setCurrentPriceForItemsInCopiedOrderLabel }}
		</td>
		<td>
		    <input type="hidden" name="set_current_price_in_copied_order" value="">
		    <input type="checkbox" class="option" v-model="elSetCurrentPriceInCopiedOrder" name="set_current_price_in_copied_order">
		</td>
	    </tr>

        </tbody>
    </table>
</template>

<style>
</style>

<script>
    export default {
        created () {
            this.$root.bus.$on('settings-saved', this.onSettingsSaved);
        },
        props: {
            title: {
                default: function () {
                    return 'Find orders';
                },
            },
            hoursLabel: {
                default: function() {
                    return 'hours';
                },
            },
            cacheTimeoutLabel: {
                default: function () {
                    return 'Caching search results';
                },
            },
            disableCacheButtonLabel: {
                default: function () {
                    return 'Disable cache';
                },
            },
            resetCacheButtonLabel: {
                default: function () {
                    return 'Reset cache';
                },
            },
            copyOnlyProcOrCompOrdersLabel: {
                default: function () {
                    return 'Seek in processing/completed orders only';
                },
            },
            showButtonCopyOrderLabel: {
                default: function () {
                    return 'Show buttons';
                },
            },
            setCurrentPriceForItemsInCopiedOrderLabel: {
                default: function () {
                    return 'Set current price for items in copied order';
                },
            },
			hideFindOrdersLabel: {
				default: function () {
					return "Hide \"Find orders\"";
				},
			},
            sessionKey: {
                default: function () {
                    return '';
                },
            },
            cacheTimeout: {
                    default: function () {
                            return 0;
                    },
            },
            copyOnlyPaidOrders: {
                default: function () {
                    return false;
                },
            },
            buttonForFindOrder: {
                default: function () {
                    return false;
                },
            },
            setCurrentPriceInCopiedOrder: {
                default: function () {
                    return false;
                },
            },
			hideFindOrders: {
                default: function () {
                    return false;
                },
            },
        },
        data () {
            return {
                orderSessionKey: this.sessionKey,
                cacheReset: 0,
                orderTimeout: this.cacheTimeout,
                elCopyOnlyPaidOrders: this.copyOnlyPaidOrders,
                elButtonForFindOrder: this.buttonForFindOrder,
                elSetCurrentPriceInCopiedOrder: this.setCurrentPriceInCopiedOrder,
                elHideFindOrders: this.hideFindOrders,
            };
        },
        methods: {
            disableCache () {
                this.orderTimeout = 0;
                this.saveSettingsByEvent();
            },
            resetCache () {
                this.cacheReset = 1;
                this.saveSettingsByEvent();
            },
            getSettings () {
                return {
                    cache_orders_session_key: this.orderSessionKey,
                    cache_orders_reset: this.cacheReset,
                    cache_orders_timeout: this.orderTimeout,
                    copy_only_paid_orders: this.elCopyOnlyPaidOrders,
                    button_for_find_orders: this.elButtonForFindOrder,
                    set_current_price_in_copied_order: this.elSetCurrentPriceInCopiedOrder,
					hide_find_orders: this.elHideFindOrders,
                };
            },
            onSettingsSaved (settings) {
                this.orderSessionKey = settings.cache_orders_session_key;
                this.cacheReset      = settings.cache_orders_reset;
            },

        },
    }
</script>