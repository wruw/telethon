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
                        {{ logShowRecordsDaysLabel }}
                    </td>
                    <td>
                        <input type="number" class="option_number" v-model.number="logShowDays"
                               name="log_show_records_days" min=0>
                    </td>
                </tr>

                <tr>
                    <td>
                        {{ showOrderDateTimeLabel }}
                    </td>
                    <td>
                        <input type="checkbox" class="option" v-model="tmpShowOrderDateTime"
                               name="show_order_date_time">
                    </td>
                </tr>

                <tr>
                    <td>
                        {{ showCartLinkLabel }}
                    </td>
                    <td>
                        <input type="checkbox" class="option" v-model="tmpShowCartLink" name="show_cart_link">
                    </td>
                </tr>

                <tr>
                    <td>
                        {{ showIconInOrdersListLabel }}
                    </td>
                    <td>
                        <input type="checkbox" class="option" v-model="tmpShowIconInOrdersList" name="show_icon_in_orders_list">
                    </td>
                </tr>

                <tr>
                    <td>
                        {{ showOrderStatusLabel }}
                    </td>
                    <td>
                        <input type="checkbox" class="option" v-model="tmpShowOrderStatus" name="show_order_status">
                    </td>
                </tr>

                <slot name="pro-interface-settings"></slot>

                </tbody>
            </table>
        </td>
    </tr>
</template>

<style>


</style>

<script>

    export default {
        props: {
            title: {
                default: function () {
                    return 'Interface';
                },
            },
            logShowRecordsDaysLabel: {
                default: function () {
                    return 'Show records for last X days in log';
                },
            },
            logShowRecordsDays: {
                default: function () {
                    return 0;
                },
            },
            showOrderDateTimeLabel: {
                default: function () {
                    return 'Show order date/time';
                },
            },
            showOrderDateTime: {
                default: function () {
                    return false;
                },
            },
            showCartLinkLabel: {
                default: function () {
                    return 'Show button "Copy url to populate cart"';
                },
            },
            showCartLink: {
                default: function () {
                    return false;
                },
            },
            showIconInOrdersListLabel: {
                default: function () {
                    return 'Show icon for phone orders in orders list';
                },
            },
            showIconInOrdersList: {
                default: function () {
                    return false;
                },
            },
            showOrderStatusLabel: {
                default: function () {
                    return 'Show order status';
                },
            },
            showOrderStatus: {
                default: function () {
                    return false;
                },
            },
        },
        data() {
            return {
                logShowDays: +this.logShowRecordsDays,
                tmpShowOrderDateTime: this.showOrderDateTime,
                tmpShowCartLink: this.showCartLink,
                tmpShowIconInOrdersList: this.showIconInOrdersList,
                tmpShowOrderStatus: this.showOrderStatus,

                shown: false,
            };
        },
        methods: {
            getSettings() {

                var settings = {
                    log_show_records_days: this.logShowDays,
                    show_order_date_time: this.tmpShowOrderDateTime,
                    show_cart_link: this.tmpShowCartLink,
                    show_icon_in_orders_list: this.tmpShowIconInOrdersList,
                    show_order_status: this.tmpShowOrderStatus,
                };

                var childsSettings = {};

                this.$children.forEach(function (child) {
                    if (typeof child.getSettings === 'function') {
                        childsSettings = Object.assign(childsSettings, child.getSettings());
                    }
                });

                return Object.assign(settings, childsSettings);
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
    }
</script>