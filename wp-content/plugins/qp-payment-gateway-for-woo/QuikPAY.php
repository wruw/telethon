<?php

/**
 * QP Payment Gateway for Woo is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * QP Payment Gateway for Woo is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with WC Quikpay Direct.  If not, see <http://www.gnu.org/licenses/>.
 *
 * Copyright 2015 Case Western Reserve University College of Arts and Sciences, Jesse Cavendish, Sarah Bailey
 */
class QuikPAY extends WC_Payment_Gateway
{

    /**
     * Constructs the QuikPAY payment gateway
     */
    function __construct()
    {

        // The global ID for this Payment method
        $this->id = "quikpay";

        // The title shown on the top of the Payment Gateways Page next to all the other Payment Gateways
        $this->method_title = __("QuikPAY", 'quikpay');

        // The description for this Payment Gateway, shown on the actual Payment options page on the backend
        $this->method_description = __("<i>WC Quikpay Direct is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or any later version.

						WC Quikpay Direct is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

						You should have received a copy of the GNU General Public License along with WC Quikpay Direct.  If not, see <a href=\'http://www.gnu.org/licenses/\'>http://www.gnu.org/licenses/</a>.

						CWRU and its affiliates and subsidiaries shall not be liable for any loss or damage of whatever nature (direct, indirect, consequential, or other) which may arise as a result of your use of (or inability to use) this plugin. By using this plugin, you signify that you have read these Terms and agree to be bound by and comply with them. If you do not agree to be bound by these Terms, please promptly uninstall this plugin.

						You agree to indemnify and hold CWRU harmless from any claims, losses or damages, including legal fees, resulting from your violation of these terms or your use of this plugin, and to fully cooperate in CWRU\'s defense against any such claims.</i>", 'quikpay');

        // The title to be used for the vertical tabs that can be ordered top to bottom
        $this->title = __("QuikPAY", 'quikpay');

        // If you want to show an image next to the gateway's name on the frontend, enter a URL to an image.
        $this->icon = null;

        $this->order_button_text = __('Proceed to Quikpay', 'woocommerce');

        // Set to true to have payment fields shown on the checkout
        $this->has_fields = false;

        // This defines the settings which are loaded with init_settings()
        $this->init_form_fields();

        // Get the settings and load them into variables
        $this->init_settings();

        // Turn these settings into variables we can use
        foreach ($this->settings as $setting_key => $value)
        {
            $this->$setting_key = $value;
        }

        // Save settings
        if (is_admin())
        {
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        }
    }
    /**
     * Creates the URL that the customer is redirected to
     *
     * @param array $payload An array of the redirect parameters
     * @return String The URL that includes the the redirect paramaters
     */

    private function get_request_url($payload)
    {

        if (strlen($payload["redirectUrl"]) > 0)
        {
            $hash = md5($payload["orderNumber"] . $payload["orderType"] . $payload["amountDue"] . $payload["redirectUrl"] . $payload["redirectUrlParameters"] . $payload["timestamp"] . $payload["secret"]);
            return $payload["quikpayurl"] . '?orderNumber=' . $payload["orderNumber"] . '&orderType=' . $payload["orderType"] . '&amountDue=' . $payload["amountDue"] . '&redirectUrl=' . $payload["redirectUrl"] . '&redirectUrlParameters=' . $payload["redirectUrlParameters"] . '&timestamp=' . $payload["timestamp"] . '&hash=' . $hash;
        }
        else
        {
            $hash = md5($payload["orderNumber"] . $payload["orderType"] . $payload["amountDue"] . $payload["timestamp"] . $payload["secret"]);
            return $payload["quikpayurl"] . '?orderNumber=' . $payload["orderNumber"] . '&orderType=' . $payload["orderType"] . '&amountDue=' . $payload["amountDue"] . '&timestamp=' . $payload["timestamp"] . '&hash=' . $hash;
        }
    }

    /**
     * Creates the form fields that appear in the backend
     */
    public function init_form_fields()
    {
        $this->form_fields = array(
            'enabled' => array(
                'title' => __('Enable / Disable', 'quikpay'),
                'label' => __('Enable this payment gateway', 'quikpay'),
                'type' => 'checkbox',
                'default' => 'no',
            ),
            'title' => array(
                'title' => __('Title', 'quikpay'),
                'type' => 'text',
                'desc_tip' => __('This is the text that customers will see when they go to the checkout page.', 'quikpay'),
                'default' => __('QuikPAY', 'quikpay'),
            ),
            'description' => array(
                'title' => __('Description', 'quikpay'),
                'type' => 'textarea',
                'desc_tip' => __('This is the text that customers will see when they select QuikPAY as their payment option.', 'quikpay'),
                'default' => __('Pay securely with QuikPAY.', 'quikpay'),
                'css' => 'max-width:350px;'
            ),
            'ordertype' => array(
                'title' => __('Order Type', 'quikpay'),
                'type' => 'text',
                'desc_tip' => __('The order type for processing QuikPAY purchases for your account. Your QuikPAY administrator should provide this
						for you. Example: Test_Department', 'quikpay'),
            ),
            'sharedsecret' => array(
                'title' => __('Shared Secret', 'quikpay'),
                'type' => 'password',
                'desc_tip' => __('This key is used to authenticate with QuikPAY. Obtain it from your QuikPAY administrator.', 'quikpay'),
            ),
            'sharedsecrettest' => array(
                'title' => __('Test Shared Secret', 'quikpay'),
                'type' => 'password',
                'desc_tip' => __('This key is used to authenticate when in test mode. Obtain it from your QuikPAY administrator.', 'quikpay'),
            ),
            'testmode' => array(
                'title' => __('QuikPAY Test Mode', 'quikpay'),
                'label' => __('Enable Test Mode', 'quikpay'),
                'type' => 'checkbox',
                'description' => __('Place the payment gateway in test mode.', 'quikpay'),
                'default' => 'no',
            ),
            'quikpayurl' => array(
                'title' => __('QuikPAY URL', 'quikpay'),
                'type' => 'text',
                'desc_tip' => __('This should be the live URL for QuikPAY payment processing provided to you by your QuikPAY administrator.', 'quikpay'),
            ),
            'quikpaytesturl' => array(
                'title' => __('QuikPAY Test URL', 'quikpay'),
                'type' => 'text',
                'desc_tip' => __('This should be the test URL for QuikPAY payment processing provided to you by your QuikPAY administrator.', 'quikpay'),
            ),
            'redirecturl' => array(
                'title' => __('Redirect URL', 'quikpay'),
                'type' => 'text',
                'desc_tip' => __('The URL QuikPAY should redirect the customer to upon completion of payment. Default is
						http://www.yoursite.com/</br>checkout/order-received/. This URL must be registered with your QuikPAY administrator.', 'quikpay'),
            ),
            'redirecttesturl' => array(
                'title' => __('Redirect Test URL', 'quikpay'),
                'type' => 'text',
                'desc_tip' => __('The URL QuikPAY should redirect the customer to upon completion of payment when your are in test mode. Default is
						http://www.yoursite.com/</br>checkout/order-received/. This URL must be registered with your QuikPAY administrator.', 'quikpay'),
            )
        );
    }

    /**
     * Processes the QuikPAY payment
     *
     * @param int $order_id The order number that is going to be processed with QuikPAY
     * @return array Redirects the customer to the QuikPAY website to finish payment
     */
    public function process_payment($order_id)
    {

        // Who is charged and how much
        $customer_order = new WC_Order($order_id);

        // Test or not
        $testmode = ($this->testmode == "yes") ? true : false;

		// Get timestamp as integer
		$times = gettimeofday();
		$seconds = strval($times["sec"]);
		$milliseconds = strval(floor($times["usec"]/1000));
		$missingleadingzeros = 3-strlen($milliseconds);
		if($missingleadingzeros >0){
			for($i = 0; $i < $missingleadingzeros; $i++){
				$milliseconds = '0'.$milliseconds;
			}
		}

        $payload = array(
            "orderNumber" => $order_id,
            "orderType" => $this->ordertype,
            "amountDue" => str_replace(".", "", $customer_order->get_total()),
            "quikpayurl" => str_replace("?", "", ($testmode) ? $this->quikpaytesturl : $this->quikpayurl),
            "redirectUrl" => ($testmode) ? $this->redirecttesturl : $this->redirecturl,
            "redirectUrlParameters" => "transactionStatus, orderNumber, transactionId, transactionResultCode",
            "timestamp" => intval($seconds.$milliseconds),
            "secret" => ($testmode) ? $this->sharedsecrettest : $this->sharedsecret
        );

        return ['result' => 'success',
            'redirect' => $this->get_request_url($payload),
            ];
    }

}
