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
                    {{ productsCacheProductsTimeoutLabel }}
                </td>
                <td>
                    <input type="hidden" name="cache_products_session_key" v-model="productsSessionKey">
                    <input type="hidden" name="cache_products_reset" id="cache_products_reset" v-model.number="cacheReset">
                    <input type="number" class="option_hours" v-model.number="productsTimeout" id="cache_products_timeout"
                           name="cache_products_timeout" min="0">
                    {{ hoursLabel }}
                    <span v-if="productsTimeout">
                        <button id="cache_products_disable_button" @click="disableCache" class="btn btn-primary">
                            {{ disableCacheButtonLabel }}
                        </button>
                        <button id="cache_products_reset_button" @click="resetCache" class="btn btn-danger">
                            {{ resetCacheButtonLabel }}
                        </button>
                    </span>
                </td>
            </tr>

            <tr>
                <td>
                    {{ productsSearchBySkuLabel }}
                </td>
                <td>
                    <input type="hidden" name="search_by_sku" value="">
                    <input type="checkbox" class="option" v-model="elSearchBySku" name="search_by_sku">
                </td>
            </tr>

            <tr>
                <td>
                    {{ productsSearchByCatAndTagLabel }}
                </td>
                <td>
                    <input type="hidden" name="search_by_cat_and_tag" value="">
                    <input type="checkbox" class="option" v-model="elSearchByCatAndTag" name="search_by_cat_and_tag">
                </td>
            </tr>

            <tr>
                <td>
                    {{ productsNumberOfProductsToShowLabel }}
                </td>
                <td>
                    <input type="number" class="option_number" v-model.number="elNumberOfProductsToShow" name="number_of_products_to_show" min=0>
                </td>
            </tr>

            <tr>
                        <td>
                            {{ hideProductFieldsLabel }}
                        </td>
                        <td>
                            <input type="checkbox" class="option_checkbox" v-model="elHideImage" name="autocomplete_product_hide_image">{{ hideImageLabel }}
                            <input type="checkbox" class="option_checkbox" v-model="elHideStatus" name="autocomplete_product_hide_status">{{ hideStatusLabel }} &nbsp;
                            <input type="checkbox" class="option_checkbox" v-model="elHideQty" name="autocomplete_product_hide_qty">{{ hideQtyLabel }} &nbsp;
                            <input type="checkbox" class="option_checkbox" v-model="elHidePrice" name="autocomplete_product_hide_price">{{ hidePriceLabel }} &nbsp;
                            <input type="checkbox" class="option_checkbox" v-model="elHideSku" name="autocomplete_product_hide_sku">{{ hideSkuLabel }} &nbsp;
                            <input type="checkbox" class="option_checkbox" v-model="elHideName" name="autocomplete_product_hide_name">{{ hideNameLabel }}
                        </td>
                    </tr>
                    
            <tr>
                <td>
                    {{ productsShowLongAttributeNamesLabel }}
                </td>
                <td>
                    <input type="hidden" name="show_long_attribute_names" value="">
                    <input type="checkbox" class="option" v-model="elShowLongAttributeNames" name="show_long_attribute_names">
                </td>
            </tr>

            <tr>
                <td>
                    {{ productsRepeatSearchLabel }}
                </td>
                <td>
                    <input type="checkbox" class="option" v-model="elRepeatSearch" name="repeat_search">
                </td>
            </tr>

            <tr>
                <td>
                    {{ allowDuplicateProductsLabel }}
                </td>
                <td>
                    <input type="checkbox" class="option" v-model="elAllowDuplicateProducts" name="allow_duplicate_products">
                </td>
            </tr>

            <tr>
                <td>
                    {{ productsHideProductsWithNoPriceLabel }}
                </td>
                <td>
                    <input type="hidden" name="hide_products_with_no_price" value="">
                    <input type="checkbox" class="option" v-model="elHideProductsWithNoPrice" name="hide_products_with_no_price">
                </td>
            </tr>

            <tr>
                <td>
                    {{ productsSellBackorderProductLabel }}
                </td>
                <td>
                    <input type="checkbox" class="option" v-model="elSaleBackorderProducts" name="sale_backorder_product">
                </td>
            </tr>

            <tr>
                <td>
                    {{ productsAddProductToTopOfTheCartLabel }}
                </td>
                <td>
                    <input type="checkbox" class="option" v-model="elAddProductToTopOfTheCart" name="add_product_to_top_of_the_cart">
                </td>
            </tr>
            <tr>
                <td>
                    {{ isReadonlyPriceLabel }}
                </td>
                <td>
                    <input type="checkbox" class="option" v-model="elIsReadonlyPrice" name="is_readonly_price">
                </td>
            </tr>
            <tr>
                <td>
                    {{ productsItemPricePrecisionLabel }}
                </td>
                <td>
                    <input type="number" class="option_number" v-model.number="elItemPricePrecision" name="item_price_precision" min=0>
                </td>
            </tr>

            <tr>
                <td>
                    {{ productsDefaultSelectedLabel }}
                </td>
                <td>
                    <multiselect
                        style="width: 100%;max-width: 800px;"
                        label="title"
                        v-model="elItemDefaultSelected"
                        :options="defaultItemList"
                        track-by="value"
                        id="ajax"
                        :placeholder="itemDefaultSelectedPlaceholder"
                        :loading="isLoading"
                        :internal-search="false"
                        :show-no-results="true"
                        @search-change="asyncFind"
                        :hide-selected="false"
                        :searchable="true"
                        open-direction="bottom"
                        :show-labels="false"
                        :multiple="true"
                        ref="defaultItemsSelected"
                    >
                        <template slot="tag" slot-scope="props">
                            <span class="multiselect__tag">
                              <span v-html="props.option.title"></span>
                              <i aria-hidden="true" tabindex="1" @keydown.enter.prevent="removeElement(props.option)" @mousedown.prevent="removeElement(props.option)" class="multiselect__tag-icon"></i>
                            </span>
                        </template>
                        <span slot="noResult">{{ noResultLabel }}</span>
                        <template slot="option" slot-scope="props">
                            <span v-html="props.option.title"></span>
                        </template>
                  </multiselect>
                </td>
            </tr>
            <tr>
                <td>
                    {{ disableEditMetaLabel }}
                </td>
                <td>
                    <input type="checkbox" class="option" v-model="elDisableEditMeta" name="disable_edit_meta">
                </td>
            </tr>

        </tbody>
    </table>

</template>

<style>
</style>

<script>

    import Multiselect from 'vue-multiselect';

    export default {
        created () {
            this.$root.bus.$on('settings-saved', this.onSettingsSaved);
        },
        props: {
            title: {
                default: function() {
                    return 'Products';
                },
            },
            hoursLabel: {
                default: function() {
                    return 'hours';
                },
            },
            productsCacheProductsTimeoutLabel: {
                default: function() {
                    return 'Caching search results';
                },
            },
            cacheSessionKey: {
                default: function() {
                    return '';
                },
            },
            cacheTimeout: {
                default: function() {
                    return 0;
                },
            },
            disableCacheButtonLabel: {
                default: function() {
                    return 'Disable cache';
                },
            },
            resetCacheButtonLabel: {
                default: function() {
                    return 'Reset cache';
                },
            },
            searchBySku: {
                default: function() {
                    return false;
                },
            },
            productsSearchBySkuLabel: {
                default: function() {
                    return 'Search by SKU';
                },
            },
            searchByCatAndTag: {
                default: function() {
                    return false;
                },
            },
            productsSearchByCatAndTagLabel: {
                default: function() {
                    return 'Filter products by category/tags';
                },
            },
            productsNumberOfProductsToShowLabel: {
                default: function() {
                    return 'Number of products to show in autocomplete';
                },
            },
            hideProductFieldsLabel: {
                default: function() {
                    return 'Hide fields in autocomplete';
                },
            },
            hideImageLabel: {
                default: function() {
                    return 'Image';
                },
            },
            hideStatusLabel: {
                default: function() {
                    return 'Status';
                },
            },
            hideQtyLabel: {
                default: function() {
                    return 'Qty';
                },
            },
            hidePriceLabel: {
                default: function() {
                    return 'Price';
                },
            },
            hideSkuLabel: {
                default: function() {
                    return 'Sku';
                },
            },
            hideNameLabel: {
                default: function() {
                    return 'Name';
                },
            },
            productsShowLongAttributeNamesLabel: {
                default: function() {
                    return 'Show long attribute names';
                },
            },
            productsRepeatSearchLabel: {
                default: function() {
                    return 'Repeat search after select product';
                },
            },
            productsHideProductsWithNoPriceLabel: {
                default: function() {
                    return 'Don\'t sell products with no price defined';
                },
            },
            productsSellBackorderProductLabel: {
                default: function() {
                    return 'Sell "out of stock" products';
                },
            },
            productsAddProductToTopOfTheCartLabel: {
                default: function() {
                    return 'Add product to top of the cart';
                },
            },
            productsItemPricePrecisionLabel: {
                default: function() {
                    return 'Item price precision';
                },
            },
            productsDefaultSelectedLabel: {
                default: function() {
                    return 'Add products by default';
                },
            },
            noResultLabel: {
                default: function() {
                    return 'Oops! No elements found. Consider changing the search query.';
                },
            },
            itemDefaultSelectedPlaceholder: {
                default: function() {
                    return 'Select items';
                },
            },
            numberOfProductsToShow: {
                default: function() {
                    return false;
                },
            },
            hideImage: {
                default: function() {
                    return false;
                },
            },
            hideStatus: {
                default: function() {
                    return false;
                },
            },
            hideQty: {
                default: function() {
                    return false;
                },
            },
            hidePrice: {
                default: function() {
                    return false;
                },
            },
            hideSku: {
                default: function() {
                    return false;
                },
            },
            hideName: {
                default: function() {
                    return false;
                },
            },
            showLongAttributeNames: {
                default: function() {
                    return false;
                },
            },
            repeatSearch: {
                default: function() {
                    return false;
                },
            },
            hideProductsWithNoPrice: {
                default: function() {
                    return false;
                },
            },
            saleBackorderProducts: {
                default: function() {
                    return false;
                },
            },
            addProductToTopOfTheCart: {
                default: function() {
                    return false;
                },
            },
            itemPricePrecision: {
                default: function() {
                    return 0;
                },
            },
            itemDefaultSelected: {
                default: function() {
                    return [];
                },
            },
	        disableEditMetaLabel: {
		        default: function () {
			        return 'Disable edit meta';
		        },
	        },
	        disableEditMeta: {
		        default: function () {
			        return false;
		        },
	        },
	        isReadonlyPriceLabel: {
		        default: function () {
			        return 'Item price is read-only';
		        },
	        },
	        isReadonlyPrice: {
		        default: function () {
			        return false;
		        },
	        },
	        allowDuplicateProductsLabel: {
		        default: function () {
			        return 'Allow to duplicate products';
		        },
	        },
            allowDuplicateProducts: {
		        default: function () {
			        return false;
		        },
	        },
        },
        data () {
            return {
	            productsSessionKey: this.cacheSessionKey,
	            cacheReset: 0,
	            productsTimeout: + this.cacheTimeout,
	            elSearchBySku: this.searchBySku,
	            elSearchByCatAndTag: this.searchByCatAndTag,
	            elNumberOfProductsToShow: this.numberOfProductsToShow,
	            elHideImage: this.hideImage,
	            elHideStatus: this.hideStatus,
	            elHideQty: this.hideQty,
	            elHidePrice: this.hidePrice,
	            elHideSku: this.hideSku,
	            elHideName: this.hideName,
	            elShowLongAttributeNames: this.showLongAttributeNames,
	            elRepeatSearch: this.repeatSearch,
	            elHideProductsWithNoPrice: this.hideProductsWithNoPrice,
	            elSaleBackorderProducts: this.saleBackorderProducts,
	            elAddProductToTopOfTheCart: this.addProductToTopOfTheCart,
	            elIsReadonlyPrice: this.isReadonlyPrice,
	            elItemPricePrecision: this.itemPricePrecision,
	            elItemDefaultSelected: this.itemDefaultSelected,
	            elDisableEditMeta: this.disableEditMeta,
	            elAllowDuplicateProducts: this.allowDuplicateProducts,
	            lastRequest: null,
	            defaultItemList: [],
	            isLoading: false,
            };
        },
        computed: {
            selectedItemIDs () {
                return this.elItemDefaultSelected.map(function (v) { return v.value });
            },
        },
        methods: {
            disableCache () {
                this.productsTimeout = 0;
                this.saveSettingsByEvent();
            },
            resetCache () {
                this.cacheReset = 1;
                this.saveSettingsByEvent();
            },
            getSettings () {
                return {
	                cache_products_session_key: this.productsSessionKey,
	                cache_products_reset: this.cacheReset,
	                cache_products_timeout: this.productsTimeout,
	                search_by_sku: this.elSearchBySku,
	                search_by_cat_and_tag: this.elSearchByCatAndTag,
	                number_of_products_to_show: this.elNumberOfProductsToShow,
	                autocomplete_product_hide_image: this.elHideImage,
	                autocomplete_product_hide_status: this.elHideStatus,
	                autocomplete_product_hide_qty: this.elHideQty,
	                autocomplete_product_hide_price: this.elHidePrice,
	                autocomplete_product_hide_sku: this.elHideSku,
	                autocomplete_product_hide_name: this.elHideName,
	                show_long_attribute_names: this.elShowLongAttributeNames,
	                repeat_search: this.elRepeatSearch,
	                hide_products_with_no_price: this.elHideProductsWithNoPrice,
	                sale_backorder_product: this.elSaleBackorderProducts,
	                add_product_to_top_of_the_cart: this.elAddProductToTopOfTheCart,
	                is_readonly_price: this.elIsReadonlyPrice,
	                item_price_precision: this.elItemPricePrecision,
	                item_default_selected: this.selectedItemIDs,
	                disable_edit_meta: this.elDisableEditMeta,
	                allow_duplicate_products: this.elAllowDuplicateProducts,
                };
            },
            onSettingsSaved (settings) {
                this.productsSessionKey = settings.cache_products_session_key;
                this.cacheReset         = settings.cache_products_reset;
            },
            removeElement (option) {
                this.$refs.defaultItemsSelected.removeElement(option);
            },
            asyncFind (query) {

                const CancelToken = this.axios.CancelToken;
                const source      = CancelToken.source();

                this.lastRequest && this.lastRequest.cancel();

                if ( ! query && query !== null ) {
                    this.isLoading       = false;
                    this.lastRequest     = null;
                    this.defaultItemList = [];
                    return;
                }

                this.isLoading   = true;
                this.lastRequest = source;

                this.axios.get(this.url, {
                    params: {
                        action: 'phone-orders-for-woocommerce',
                        method: 'search_products_and_variations',
                        tab: 'add-order',
                        term: query,
                        exclude: JSON.stringify(this.selectedItemIDs),
                        wpo_cache_products_key: this.productsSessionKey,
                    },
                    cancelToken: source.token,
                    paramsSerializer: (params) => {
                        return this.qs.stringify(params)
                    }}).then( ( response ) => {

                    var products = [];

                    for(var id in response.data) {
                        var product_id = response.data[id].product_id;
                        if ( this.selectedItemIDs.indexOf(+product_id) === -1) {
                            products.push({title: response.data[id].title, value: product_id});
                        }
                    }

                    this.defaultItemList = products;

                    this.isLoading = false;
                }, (thrown) => {
                    if (!this.axios.isCancel(thrown)) {
                        this.isLoading = false;
                    }
                });
            },
        },
        components: {
            Multiselect,
        },
    }
</script>