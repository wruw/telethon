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
                    {{ showGoToCartPageLabel }}
                </td>
                <td>
                    <input type="hidden" name="show_go_to_cart_button" value="">
                    <input type="checkbox" class="option" v-model="elShowGoToCartButton" name="show_go_to_cart_button">
                </td>
            </tr>

            <tr>
                <td>
                    {{ showGoToCheckoutPageLabel }}
                </td>
                <td>
                    <input type="hidden" name="show_go_to_checkout_button" value="">
                    <input type="checkbox" class="option" v-model="elShowGoToCheckoutButton" name="show_go_to_checkout_button">
                </td>
            </tr>

            <tr>
                <td>
                    {{ overrideCustomerPaymentLinkInOrderPageLabel }}
                </td>
                <td>
                    <input type="hidden" name="show_go_to_checkout_button" value="">
                    <input type="checkbox" class="option" v-model="elOverrideCustomerPaymentLinkInOrderPage" name="override_customer_payment_link_in_order_page">
                </td>
            </tr>

            <tr>
                <td>
                    {{ overrideProductPriceInCartLabel }}
                </td>
                <td>
                    <input type="checkbox" class="option" v-model="elOverrideProductPriceInCart" name="override_product_price_in_cart">
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
                    return 'Checkout at frontend';
                },
            },
            showGoToCartPageLabel: {
                default: function() {
                    return 'Show \'Go to Cart\' button';
                }
            },
            showGoToCheckoutPageLabel: {
                default: function() {
                    return 'Show \'Go to Checkout\' button';
                }
            },
            overrideCustomerPaymentLinkInOrderPageLabel: {
                default: function() {
                    return 'Override "Customer payment page" in the order';
                }
            },
            overrideProductPriceInCartLabel: {
                default: function() {
                    return 'Pass modified product prices to frontend cart';
                }
            },
            showGoToCartButton: {
                default: function() {
                    return false;
                }
            },
            showGoToCheckoutButton: {
                default: function() {
                    return false;
                }
            },
            overrideCustomerPaymentLinkInOrderPage: {
                default: function() {
                    return true;
                }
            },
            overrideProductPriceInCart: {
                default: function() {
                    return false;
                }
            },
        },
        data () {
            return {
                elShowGoToCartButton: this.showGoToCartButton,
                elShowGoToCheckoutButton: this.showGoToCheckoutButton,
                elOverrideCustomerPaymentLinkInOrderPage: this.overrideCustomerPaymentLinkInOrderPage,
                elOverrideProductPriceInCart: this.overrideProductPriceInCart,
            };
        },
        methods: {
            getSettings () {
                return {
                    show_go_to_cart_button: this.elShowGoToCartButton,
                    show_go_to_checkout_button: this.elShowGoToCheckoutButton,
                    override_customer_payment_link_in_order_page: this.elOverrideCustomerPaymentLinkInOrderPage,
                    override_product_price_in_cart: this.elOverrideProductPriceInCart,
                };
            },
        },
    }
</script>