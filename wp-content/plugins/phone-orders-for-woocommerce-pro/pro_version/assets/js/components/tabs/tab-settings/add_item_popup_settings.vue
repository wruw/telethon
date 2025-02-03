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
                    {{ productsDisableCreatingProductsLabel }}
                </td>
                <td>
                    <input type="checkbox" class="option" v-model="elDisableAddingProducts" name="disable_adding_products">
                </td>
            </tr>
            
            <tr>
                <td>
                    {{ productsNewProductAskSKULabel }}
                </td>
                <td>
                    <input type="checkbox" class="option" v-model="elNewProductAskSKU" name="new_product_ask_sku">
                </td>
            </tr>

            <tr>
                <td>
                    {{ productsNewProductVisibilityLabel }}
                </td>
                <td>

                    <i>
                        {{ productsThisSettingDeterminesWhichShopPagesProductsWillBeListedOn }}
                    </i>
                    <br>
                    <multiselect
                        :allow-empty="false"
                        :hide-selected="true"
                        :searchable="false"
                        style="width: 100%;max-width: 800px;"
                        label="label"
                        v-model="elProductVisibility"
                        :options="elProductVisibilityOptions"
                        track-by="name"
                        :show-labels="false"
                    >
                    </multiselect>
                </td>

            </tr>

            <tr>
                <td>
                    {{ productsNewProductAskTaxClassLabel }}
                </td>
                <td>
                    <input type="checkbox" class="option" v-model="elNewProductAskTaxClass" name="new_product_ask_tax_class">
                </td>
            </tr>

            <tr>
                <td>{{ addItemTaxClassLabel }}</td>
                <td>
                    <multiselect
                            :allow-empty="false"
                            :hide-selected="true"
                            :searchable="false"
                            style="width: 100%;max-width: 800px;"
                            label="title"
                            v-model="elItemTaxClass"
                            :options="elItemTaxClasses"
                            track-by="slug"
                            :show-labels="false"
                    ></multiselect>
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
        props: {
            title: {
                default: function() {
                    return 'New Product';
                },
            },
            productsDisableCreatingProductsLabel: {
                default: function() {
                    return 'Disable creating products';
                },
            },
            productsNewProductAskSKULabel: {
                default: function() {
                    return 'Show SKU while adding product';
                },
            },
            productsNewProductVisibilityLabel: {
                default: function() {
                    return 'New product visibility';
                },
            },
	        productsThisSettingDeterminesWhichShopPagesProductsWillBeListedOn: {
		        default: function() {
			        return 'This setting determines which shop pages products will be listed on.';
		        },
	        },
            newProductAskSKU: {
                default: function() {
                    return 0;
                },
            },
            productVisibility: {
                default: function() {
                    return '';
                },
            },
            productVisibilityOptions: {
                default: function() {
                    return [];
                },
            },
	        disableAddingProducts: {
		        default: function() {
			        return 0;
		        },
	        },
	        productsNewProductAskTaxClassLabel: {
		        default: function() {
			        return 'Show tax class selector';
		        },
	        },
	        newProductAskTaxClass: {
		        default: function() {
			        return false;
		        },
	        },
	        addItemTaxClassLabel: {
		        default: function() {
			        return 'Default tax class';
		        },
	        },
	        itemTaxClass: {
		        default: function() {
			        return '';
		        },
	        },
	        itemTaxClasses: {
		        default: function() {
			        return [];
		        },
	        },
        },
        data () {
            return {
	            elProductVisibilityOptions: this.productVisibilityOptions,
	            elDisableAddingProducts: this.disableAddingProducts,
                elNewProductAskSKU: this.newProductAskSKU,
                elProductVisibility: this.getObjectByKeyValue(this.productVisibilityOptions, 'name', this.productVisibility),
	            elNewProductAskTaxClass: this.newProductAskTaxClass,
	            elItemTaxClasses: this.itemTaxClasses,
	            elItemTaxClass: this.getObjectByKeyValue(this.itemTaxClasses, 'slug', this.itemTaxClass),
            };
        },
        methods: {
            getSettings () {
                return {
                    disable_adding_products: this.elDisableAddingProducts,
                    new_product_ask_sku: this.elNewProductAskSKU,
                    new_product_ask_tax_class: this.elNewProductAskTaxClass,
                    product_visibility: this.getKeyValueOfObject(this.elProductVisibility, 'name'),
	                item_tax_class: this.getKeyValueOfObject(this.elItemTaxClass, 'slug'),
                };
            },
        },
        components: {
            Multiselect,
        },
    }
</script>