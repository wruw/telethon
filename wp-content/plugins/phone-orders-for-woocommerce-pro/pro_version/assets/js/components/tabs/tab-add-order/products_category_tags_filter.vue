<template>
    <div class="search_options" style="float: left;" v-if="isShow">
        <div class="search_option">
            <label for="product_cat">
                {{ categoryLabel }}
            </label>
            <multiselect
                :allow-empty="true"
                :hide-selected="true"
                :searchable="false"
                style="width: 200px;"
                label="title"
                v-model="productCategory"
                :options="categoriesList"
                track-by="value"
                :placeholder="selectProductsCategoryPlaceholder"
                @input="onSelectProductCategory"
                v-bind:disabled="!cartEnabled"
                :show-labels="false"
            >
                <template slot="clear" slot-scope="props">
                    <div class="multiselect__clear" v-show="productCategory" @mousedown.prevent.stop="clearProductCategory"></div>
                </template>
                <template slot="singleLabel" slot-scope="props">
                    <span v-html="props.option.title"></span>
                </template>
                <template slot="option" slot-scope="props">
                    <span v-html="props.option.title"></span>
                </template>
            </multiselect>
        </div>

        <div class="search_option">
            <label for="tag_search_option">
                {{ tagLabel }}
            </label>
            <multiselect
                :allow-empty="true"
                :hide-selected="true"
                :searchable="false"
                style="width: 200px;"
                label="title"
                v-model="productTag"
                :options="tagsList"
                track-by="value"
                :placeholder="selectProductsTagPlaceholder"
                @input="onSelectProductTag"
                v-bind:disabled="!cartEnabled"
                :show-labels="false"
            >
                <template slot="clear" slot-scope="props">
                    <div class="multiselect__clear" v-show="productTag" @mousedown.prevent.stop="clearProductTag"></div>
                </template>
                <template slot="singleLabel" slot-scope="props">
                    <span v-html="props.option.title"></span>
                </template>
                <template slot="option" slot-scope="props">
                    <span v-html="props.option.title"></span>
                </template>
            </multiselect>
        </div>
    </div>
</template>

<style>
    .multiselect__clear {
        position: absolute;
        right: 41px;
        height: 40px;
        width: 40px;
        display: block;
        cursor: pointer;
        z-index: 2;
    }

    .multiselect__clear:before {
        transform: rotate(45deg);
    }

    .multiselect__clear:after {
        transform: rotate(-45deg);
    }

    .multiselect__clear:after, .multiselect__clear:before {
        content: "";
        display: block;
        position: absolute;
        width: 3px;
        height: 16px;
        background: #aaa;
        top: 12px;
        right: 4px;
    }
</style>

<script>

    import Multiselect from 'vue-multiselect';

    export default {
        created () {
            this.$root.bus.$on(['settings-loaded', 'settings-saved'], () => {
                this.loadCategoriesList();
                this.loadTagsList();
            });
        },
        props: {
            categoryLabel: {
                default: function() {
                    return 'Category';
                }
            },
            selectProductsCategoryPlaceholder: {
                default: function() {
                    return 'Select a category';
                }
            },
            tagLabel: {
                default: function() {
                    return 'Tag';
                }
            },
            selectProductsTagPlaceholder: {
                default: function() {
                    return 'Select a tag';
                }
            },
            tabName: {
                default: function() {
                    return 'add-order';
                }
            },
        },
        data: function () {
            return {
                productCategory: null,
                productTag: null,
                categoriesList: [],
                tagsList: [],

            };
        },
        computed: {
            isShow: function () {
                return this.getSettingsOption('search_by_cat_and_tag');
            },
        },
        methods: {
            clearProductCategory: function () {

                this.productCategory = null;

                var params = this.getAdditionalParams();

                if (params['category_slug']) {
                    delete params['category_slug'];
                }

                this.setAdditionalParams(params);
            },
            clearProductTag: function () {

                this.productTag = null;

                var params = this.getAdditionalParams();

                if (params['tag_slug']) {
                    delete params['tag_slug'];
                }

                this.setAdditionalParams(params);
            },
            onSelectProductCategory: function () {

                var params              = this.getAdditionalParams();
                params['category_slug'] = this.productCategory.value;

                this.setAdditionalParams(params);
            },
            onSelectProductTag: function () {

                var params         = this.getAdditionalParams();
                params['tag_slug'] = this.productTag.value;

                this.setAdditionalParams(params);
            },
            getAdditionalParams: function () {
                return this.$store.state.add_order.additional_params_product_search || {};
            },
            setAdditionalParams: function (params) {
                this.$store.commit('add_order/setAdditionalParamsProductSearch', params);
            },
            loadCategoriesList () {
                this.axios.get(this.url, {params: {
                    action: 'phone-orders-for-woocommerce',
                    method: 'get_products_categories_list',
                    tab: this.tabName,
                    wpo_cache_references_key: this.getSettingsOption('cache_references_session_key'),
                }}).then( ( response ) => {
                    this.categoriesList = response.data.data.categories_list;
                });
            },
            loadTagsList () {
                this.axios.get(this.url, {params: {
                    action: 'phone-orders-for-woocommerce',
                    method: 'get_products_tags_list',
                    tab: this.tabName,
                    wpo_cache_references_key: this.getSettingsOption('cache_references_session_key'),
                }}).then( ( response ) => {
                    this.tagsList = response.data.data.tags_list;
                });
            },
        },
        components: {
            Multiselect,
        },
    }
</script>