<?php
/**
 * Thankyou page
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.2.0
 */
$url = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

if ($order || false !== strpos($url, 'orderNumber=')) :
    ?>

    <?php
    global $woocommerce;

    if ($order)
    {
        $total = $order->get_formatted_order_total();
    }

    if ($order && $total == 0.00  && $order->get_payment_method() == 'quikpay')
    {
        $order->update_status('completed', __('Payment complete', 'woocommerce'));
    }
    else
    {
        //Grab & Sanitize the Get variables
        $transactionStatus = filter_input(INPUT_GET, "transactionStatus", FILTER_SANITIZE_NUMBER_INT);
        $order_id = filter_input(INPUT_GET, "orderNumber", FILTER_SANITIZE_NUMBER_INT);
        
        // Turns out this can contain characters
        //$confirmationNumber = filter_input(INPUT_GET, "transactionId", FILTER_SANITIZE_NUMBER_INT);
        $confirmationNumber = filter_input(INPUT_GET,"transactionId", FILTER_SANITIZE_STRING);
	// Apparently this can be contain characters too
        $transactionResultCode = filter_input(INPUT_GET, "transactionResultCode", FILTER_SANITIZE_STRING);
        $timestamp = filter_input(INPUT_GET, "timestamp", FILTER_SANITIZE_NUMBER_INT);
        $hash = filter_input(INPUT_GET, "hash", FILTER_SANITIZE_STRING);

        //Get Quikpay Settings
        //TODO: Need a better way to do this
        //TODO: This entire page should really be refactored into the Quikpay class itself
        $settings = get_option("woocommerce_quikpay_settings");
        $testmode = $settings['testmode'] == "yes" ? true : false;
        $sharedsecret = $testmode ? $settings['sharedsecrettest'] : $settings['sharedsecret'];

        //Calculate the expected hash
        $calcHash = md5($transactionStatus . $order_id . $confirmationNumber . $transactionResultCode . $timestamp . $sharedsecret);

        // Load Order
        $order = new WC_Order($order_id);

        // Verify the transaction status
        if ($transactionStatus == 1)
        {
            // CC Approved.
            // Quikpay Hash Verification
            // This verifies that Quikpay was the actual source of the data
            if ($calcHash === $hash)
            {
                // Hash Verification Success.
                $order->add_order_note('The confirmation number is: ' . $confirmationNumber);
                $order->update_status('completed', __('Payment complete', 'woocommerce'));
                $order->reduce_order_stock();
                $woocommerce->cart->empty_cart();
            }
            else
            {
                // Hash Verification Failure.
                $order->add_order_note("Quikpay Hash Verification Failed. Returned Hash: " . $hash . " Expected Hash: " . $calcHash);
                $order->update_status('failed', __('Quikpay Hash Verification Failed', "woocommerce"));
            }
        }
        elseif ($transactionStatus == 2)
        {
            // CC Declined

            $order->add_order_note("Quikpay Return Status: CC Declined");
            $order->update_status('failed', __('Quikpay CC Declined', "woocommerce"));
        }
        elseif ($transactionStatus == 3 || $transactionStatus==4)
        {
            // Error

            $order->add_order_note("Quikpay Return Status: Error Processing CC");
            $order->update_status('pending', __('Quikpay CC Processing Error', "woocommerce"));
        }
    }
    ?>

    <?php if ($order->has_status('failed')) : ?>

        <p><?php _e('Unfortunately your order cannot be processed as the originating bank/merchant has declined your transaction.', 'woocommerce'); ?></p>

        <p><?php
            if (is_user_logged_in())
                _e('Please attempt your purchase again or go to your account page.', 'woocommerce');
            else
                _e('Please attempt your purchase again.', 'woocommerce');
            ?></p>

        <p>
            <a href="<?php echo esc_url($order->get_checkout_payment_url()); ?>" class="button pay"><?php _e('Pay', 'woocommerce') ?></a>
            <?php if (is_user_logged_in()) : ?>
                <a href="<?php echo esc_url(get_permalink(wc_get_page_id('myaccount'))); ?>" class="button pay"><?php _e('My Account', 'woocommerce'); ?></a>
            <?php endif; ?>
        </p>

    <?php else : ?>

        <p><?php echo apply_filters('woocommerce_thankyou_order_received_text', __('Thank you, your order has been received.', 'woocommerce'), $order); ?></p>

        <ul class="order_details">
            <li class="order">
                <?php _e('Order:', 'woocommerce'); ?>
                <strong><?php echo $order->get_order_number(); ?></strong>
            </li>
            <li class="date">
                <?php _e('Date:', 'woocommerce'); ?>
                <strong><?php echo date_i18n(get_option('date_format'), strtotime($order->order_date)); ?></strong>
            </li>
            <li class="total">
                <?php _e('Total:', 'woocommerce'); ?>
                <strong><?php echo $order->get_formatted_order_total(); ?></strong>
            </li>
            <?php if ($order->payment_method_title) : ?>
                <li class="method">
                    <?php _e('Payment method:', 'woocommerce'); ?>
                    <strong><?php echo $order->payment_method_title; ?></strong>
                </li>
                <li class="order">
                    <?php _e('Confirmation Number:', 'woocommerce'); ?>
                    <strong><?php echo $confirmationNumber; ?></strong>
                </li>
            <?php endif; ?>
        </ul>
        <div class="clear"></div>

    <?php endif; ?>

    <?php do_action('woocommerce_thankyou_' . $order->payment_method, $order->id); ?>
    <?php do_action('woocommerce_thankyou', $order->id); ?>

<?php else : ?>

    <p><?php echo apply_filters('woocommerce_thankyou_order_received_text', __("Thank you, your order has been recieved.", 'woocommerce'), null); ?></p>

<?php endif; ?>
