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
                {{ hideAddShippingLabel }}
            </td>
            <td>
                <input type="checkbox" class="option" v-model="elHideAddShipping" name="hide_add_shipping">
            </td>
        </tr>

        </tbody>
    </table>
</template>

<script>
    export default {
        props: {
            title: {
                default: function () {
                    return 'Shipping';
                },
            },
            hideAddShippingLabel: {
                default: function () {
                    return "Hide shipping section";
                },
            },
            hideAddShipping: {
                default: function () {
                    return false;
                },
            },
        },
        data() {
            return {
                elHideAddShipping: this.hideAddShipping,
            };
        },
        methods: {
            getSettings() {
                return {
                    hide_add_shipping: this.elHideAddShipping,
                };
            },
        },
    }
</script>