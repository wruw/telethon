<template>
    <tr>
        <td colspan="2">
            <table style="width: 100%">
                <tbody>
                <tr>
                    <td>
                        {{ hideCouponWarningLabel }}
                    </td>
                    <td>
                        <input type="checkbox" class="option" v-model="elHideCouponWarning" name="hide_coupon_warning">
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
            hideCouponWarningLabel: {
                default: function () {
                    return "Hide warning about disabled coupons";
                },
            },
            hideCouponWarning: {
                default: function () {
                    return false;
                },
            },
        },
        data() {
            return {
                elHideCouponWarning: this.hideCouponWarning,

            };
        },
        methods: {
            getSettings() {
                return {
                    hide_coupon_warning: this.elHideCouponWarning,
                };
            },
        },
    }
</script>