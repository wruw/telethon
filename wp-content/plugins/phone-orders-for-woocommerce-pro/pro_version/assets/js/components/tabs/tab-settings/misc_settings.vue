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
                    {{ customFieldsLabel }}
                    <br>
                    <i>{{ oneFieldPerLineLabel }}</i>
                </td>
                <td>
                    <textarea name="order_custom_fields" class="option" rows=3 v-model.lazy="elOrderCustomFields" ></textarea>
                </td>
            </tr>
            <tr>
                <td>
                    {{ customerCustomFieldsLabel }}
                    <br>
                    <i>{{ oneFieldPerLineLabel }}</i>
                </td>
                <td>
                    <textarea name="customer_custom_fields" class="option" rows=3 v-model.lazy="elCustomerCustomFields" ></textarea>
                </td>
            </tr>
            <tr>
                <td>
                    {{ itemMetaFieldsLabel }}
                    <br>
                    <i>{{ itemMetaFieldsOneFieldPerLineLabel }}</i>
                </td>
                <td>
                    <textarea name="item_custom_meta_fields" class="option" rows=3 v-model.lazy="elItemCustomMetaFields" ></textarea>
                </td>
            </tr>
            <tr>
                <td>
                    {{ defaultListItemMetaFieldsLabel }}
                    <br>
                    <i>{{ defaultListItemMetaFieldsOneFieldPerLineLabel }}</i>
                </td>
                <td>
                    <textarea name="default_list_item_custom_meta_fields" class="option" rows=3 v-model.lazy="elDefaultListItemCustomMetaFields" ></textarea>
                </td>
            </tr>
        </tbody>
    </table>
</template>

<style>
</style>

<script>
    export default {
        props: {
            title: {
                default: function() {
                    return 'Custom fields';
                },
            },
            customFieldsLabel: {
                default: function() {
                    return 'Order fields';
                },
            },
            oneFieldPerLineLabel: {
                default: function() {
                    return 'One field per line, use format: Label Text|custom_fieldname';
                },
            },
            orderCustomFields: {
                default: function() {
                    return '';
                },
            },
            itemMetaFieldsLabel: {
                default: function() {
                    return 'Available fields for product';
                },
            },
            itemMetaFieldsOneFieldPerLineLabel: {
                default: function() {
                    return 'One field per line';
                },
            },
            itemCustomMetaFields: {
                default: function() {
                    return '';
                },
            },
            defaultListItemMetaFieldsLabel: {
                default: function() {
                    return 'Default fields for product';
                },
            },
            defaultListItemMetaFieldsOneFieldPerLineLabel: {
                default: function() {
                    return 'One field and value per line, separated by |, e.g meta_key|meta_value';
                },
            },
            defaultListItemCustomMetaFields: {
                default: function() {
                    return '';
                },
            },
	        customerCustomFieldsLabel: {
                default: function() {
                    return 'Customer fields';
                },
            },
	        customerCustomFields: {
		        default: function() {
			        return '';
		        },
	        },
        },
        data () {
            return {
                elOrderCustomFields: this.orderCustomFields,
                elCustomerCustomFields: this.customerCustomFields,
                elItemCustomMetaFields: this.itemCustomMetaFields,
                elDefaultListItemCustomMetaFields: this.defaultListItemCustomMetaFields,
            };
        },
        methods: {
            getSettings () {
                return {
                    order_custom_fields: this.elOrderCustomFields,
	                customer_custom_fields: this.elCustomerCustomFields,
                    item_custom_meta_fields: this.elItemCustomMetaFields,
                    default_list_item_custom_meta_fields: this.elDefaultListItemCustomMetaFields,
                };
            },
        },
    }
</script>