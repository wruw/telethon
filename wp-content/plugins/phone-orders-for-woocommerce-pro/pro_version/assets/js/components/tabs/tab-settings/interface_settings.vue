<template>
    <tr>
        <td colspan="2">
            <table style="width: 100%">
                <tbody>
                <tr>
                    <td>
                        {{ hideTabsLabel }}
                    </td>
                    <td>
                        <input type="checkbox" class="option" v-model="elHideTabs" name="hide_tabs">
                    </td>
                </tr>

                <tr>
                    <td>
                        {{ showDuplicateOrderLabel }}
                    </td>
                    <td>
                        <input type="checkbox" class="option" v-model="elShowDuplicateOrder"
                               name="show_duplicate_order_button">
                    </td>
                </tr>

                <tr>
                    <td>
                        {{ showEditOrderInWCLabel }}
                    </td>
                    <td>
                        <input type="checkbox" class="option" v-model="elShowEditOrderInWC" name="show_edit_order_in_wc">
                    </td>
                </tr>

                <tr>
                    <td>
                        {{ hideButtonPayAsCustomerLabel }}
                    </td>
                    <td>
                        <input type="checkbox" class="option" v-model="elHideButtonPayAsCustomer" name="hide_button_pay_as_customer">
                    </td>
                </tr>

                <tr>
                    <td>
                        {{ hideButtonCreateOrderLabel }}
                    </td>
                    <td>
                        <input type="checkbox" class="option" v-model="elHideButtonCreateOrder" name="hide_button_create_order">
                    </td>
                </tr>

                <tr>
                    <td>
                        {{ hideButtonPutOnHoldLabel }}
                    </td>
                    <td>
                        <input type="checkbox" class="option" v-model="elHideButtonPutOnHold" name="hide_button_put_on_hold">
                    </td>
                </tr>
                </tbody>
            </table>
        </td>
    </tr>
</template>

<script>

    export default {
        props: {
            hideTabsLabel: {
                default: function () {
                    return 'Hide tabs for non-admins';
                },
            },
            hideTabs: {
                default: function () {
                    return false;
                },
            },
            showDuplicateOrderLabel: {
                default: function () {
                    return 'Show "duplicate order" button after order creation';
                },
            },
            showDuplicateOrder: {
                default: function () {
                    return false;
                },
            },
            showEditOrderInWCLabel: {
                default: function () {
                    return "Show button \"Edit\" in orders list ";
                },
            },
            showEditOrderInWC: {
                default: function () {
                    return true;
                },
            },
            hideButtonPayAsCustomerLabel: {
                default: function () {
                    return "Hide button \"Pay as customer\"";
                },
            },
            hideButtonPayAsCustomer: {
                default: function () {
                    return false;
                },
            },
            hideButtonCreateOrderLabel: {
                default: function () {
                    return "Hide button \"Create order\"";
                },
            },
            hideButtonCreateOrder: {
                default: function () {
                    return false;
                },
            },
            hideButtonPutOnHoldLabel: {
                default: function () {
                    return "Hide button \"Put on hold\"";
                },
            },
            hideButtonPutOnHold: {
                default: function () {
                    return false;
                },
            },
        },
        data() {
            return {
                elHideTabs: this.hideTabs,
                elShowDuplicateOrder: this.showDuplicateOrder,
                elShowEditOrderInWC: this.showEditOrderInWC,
                elHideButtonPayAsCustomer: this.hideButtonPayAsCustomer,
                elHideButtonCreateOrder: this.hideButtonCreateOrder,
                elHideButtonPutOnHold: this.hideButtonPutOnHold,
            };
        },
        methods: {
            getSettings() {
                return {
                    hide_tabs: this.elHideTabs,
                    show_duplicate_order_button: this.elShowDuplicateOrder,
                    show_edit_order_in_wc: this.elShowEditOrderInWC,
                    hide_button_pay_as_customer: this.elHideButtonPayAsCustomer,
                    hide_button_create_order: this.elHideButtonCreateOrder,
                    hide_button_put_on_hold: this.elHideButtonPutOnHold,
                };
            },
        },
    }
</script>