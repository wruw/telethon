=== QuikPAY Payment Gateway for WooCommerce ===
Contributors: CWRUCAS
Tags: woocommerce, quikpay, payment gateways, payment gateway
Requires at least: 4.7 
Tested up to: 4.8.2
Stable tag: 1.1.1 
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

The QuikPAY Payment Gateway for WooCommerce allows you to accept payments via QuikPAY's credit processing.

== Description ==

This is a QuikPAY payment gateway for WooCommerce.

QuikPAY is credit processing solution offered by NelNet Business Solutions (NBS). With QuikPAY, credit card information
is entered directly on the QuikPAY's website rather than your hosted web page. This solution relieves you of
some of the burdens associated with PCI compliance.

With this QuikPAY Payment Gateway, you can pass the information required to complete a QuikPAY transaction (amount due, order number),
allow the customer to fill out their information on QuikPAY's website, and then have customer redirected back to a receipt page
that includes the transaction number of the purchase.

Currently processing refunds directly from WooCommerce using this plugin is not possible. This feature is planned for a future release. Refunds should be processed directly from the QuikPAY website.

QuikPAY can accept the following payment options on its website:


* __Visa__
* __MasterCard__
* __American Express__
* __Discover__

= Plugin Features =

* __Accept payment__ via Visa, MasterCard, American Express, Discover.
* __Offsite solution__ by using QuikPAY's websites instead of your own to process payments.
* __Automatic redirection__ back to your website.

= Disclaimer =

QuikPAY Payment Gateway for WooCommerce is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or any later version.
QuikPAY Payment Gateway for WooCommerce is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
You should have received a copy of the GNU General Public License along with QuikPAY Payment Gateway for WooCommerce.  If not, see http://www.gnu.org/licenses/.
CWRU and its affiliates and subsidiaries shall not be liable for any loss or damage of whatever nature (direct, indirect, consequential, or other) which may arise as a result of your use of (or inability to use) this plugin. By using this plugin, you signify that you have read these Terms and agree to be bound by and comply with them. If you do not agree to be bound by these Terms, please promptly uninstall this plugin.
You agree to indemnify and hold CWRU harmless from any claims, losses or damages, including legal fees, resulting from your violation of these terms or your use of this plugin, and to fully cooperate in CWRU's defense against any such claims.

Other Contributors: Jesse Cavendish, Sarah Bailey, Case Western Reserve University College of Arts and Sciences IT Support Group

== Installation ==

= Automatic Installation =

1. Login as the WordPress administrator
2. Click on "Plugins > Add New" using the left hand menu
3. Search for "QuikPAY WooCommerce Payment Gateway"
4. In the search results, click on "QuikPAY WooCommerce Payment Gateway"
5. Confirm the installation
6. Activate the plugin

= Manual Installation =

1. Download the plugin's zip file
2. Click on "Plugins > Add New" using the left hand menu
3. Click on "Upload" option and then click "Choose File"
4. Select the zip file
5. Activate the plugin

= Configure =

To configure the plugin:

1. Click on "WooCommerce > Settings" using the left hand menu
2. Go to the "Checkout" tab and select QuikPAY from the "Checkout Options"

* __Enable / Disable__ - check the box to enable QuikPAY Payment Gateway.
* __Title__ - This is the text that customers will see when they go to the checkout page.
* __Description__ - This is the text that customers will see when they select QuikPAY as their payment option.
* __Order Type__ - The order type for processing QuikPAY purchases for your account. Your QuikPAY administrator should provide this for you. Example: Test_Department
* __Shared Secret__ - This key is used to authenticate with QuikPAY. Obtain it from your QuikPAY administrator.
* __Test Shared Secret__ - This key is used to authenticate when in test mode. Obtain it from your QuikPAY administrator.
* __QuikPAY Test Mode__ - Place the payment gateway in test mode.
* __QuikPAY URL__ - This should be the live URL for QuikPAY payment processing provided to you by your QuikPAY administrator.
* __QuikPAY Test URL__ - This should be the test URL for QuikPAY payment processing provided to you by your QuikPAY administrator.
* __Redirect URL__ - The URL QuikPAY should redirect the customer to upon completion of payment. Default is http://www.yoursite.com/checkout/order-received/. This URL must be registered with your QuikPAY administrator.
* __Redirect Test URL__ - The URL QuikPAY should redirect the customer to upon completion of payment when your are in test mode. Default is http://www.yoursite.com/checkout/order-received/. This URL must be registered with your QuikPAY administrator.

== Frequently Asked Questions ==

= What are the requirements to use this payment gateway? =

* WordPress 4.7+
* WooCommerce 3.0+
* A QuikPAY account

= Where can I get the shared key, QuikPAY URL, etc? =

That information is provided to you by your QuikPAY administrator. Contact your QuikPAY administrator to obtain keys.

= Who uses QuikPAY =

QuikPAY is typically used by colleges and universities to provide consolidated billing services to the students and faculty. Students and families can then view their entire payment history in one, easy-to-read statement.

= Can I issue refunds? =

Currently processing refunds directly from WooCommerce using this plugin is not possible. This feature is planned for a future release. Refunds should be processed directly from the QuikPAY website.

== Screenshots ==

1. Payment gateway settings page with defaults

== Changelog ==

= 1.1.1 =
* Update stable version
* Changed timestamp method to no longer require PHP precision of 13
* Changed order_total to get_total per WC 3.0+

= 1.1.0 =
* Update stable version
* Added inventory control

= 1.0.1 =
* Update stable version
* Bugfix: Fixed a problem where hash could be returned mismatched due to INT validation on transactionID stripping characters.

= 1.0 =
* Initial public release.