=== Phone Orders for WooCommerce ===
Contributors: algolplus
Donate link: http://algolplus.com/plugins/
Tags: woocommerce, backend, phone, phone orders, manual, manual orders, call center, call center software
Requires PHP: 5.4.0
Requires at least: 4.8
Tested up to: 5.1
Stable tag: 3.2.5
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Easy way to take a manual/phone order in WooCommerce

== Description ==

The plugin speeds up adding manual/phone orders in WooCommerce backend.

Visit "WooCommerce" > "Phone Orders". 

After creating the order, you can "View order", "Send invoice" and "Pay order as customer" ( [Pro version](https://algolplus.com/plugins/downloads/phone-orders-woocommerce-pro/) only ).

= Features =
* UI was adapted for keyboard input
* Search through existing customers or add new customers quickly and efficiently
* Search through existing products or add new products on the fly
* Use default pricing or adjust pricing within the order
* Places autocomplete for address (you must generate Google Maps API key)
* Support free shipping (method works in admin area only)
* Ability to add coupons with auto find feature
* Copy url to populate cart
* Log created orders

= Pro features =
* Caching search results (autocomplete for Products/Customers/Orders)
* Create new order based on existing order
* Pause and resume the order
* Customer search by shipping/billing fields
* Predefine city, postal code, country and state for new customers
* Configure fields to show while adding new customers
* Save address details to the customer’s profile
* Clear all items in cart with a push of a button
* Add any additional fees
* Set own shipping price
* Add custom fields to the order
* Sell products that are out of stock
* Stop selling products without price
* Hide new products after creation
* and much more ...

Have an idea or feature request?
Please create a topic in the "Support" section with any ideas or suggestions for new features.


== Installation ==

= Automatic Installation =
Go to Wordpress dashboard, click  Plugins / Add New  , type 'woocommerce phone orders' and hit Enter.
Install and activate plugin, visit WooCommerce > Export Orders.

= Manual Installation =
[Please, visit the link and follow the instructions](http://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation)


== Frequently Asked Questions ==

= How to set default country/state for new customers =
[Pro version](https://algolplus.com/plugins/downloads/phone-orders-woocommerce-pro/) has more settings  and we keep adding them.

= I can't add new customer, I see popup with message "Please enter an account password"  =
Please, visit >Woocommerce>Settings, select tab "Accounts & Privacy" and mark checkbox "When creating an account, automatically generate an account password".

= I don't see Free Shipping [Phone Orders] in popup  =
Please, visit >WooCommerce>Settings>Shipping  and add shipping method for necessary zones

= How to pay order?  =
[Pro version](https://algolplus.com/plugins/downloads/phone-orders-woocommerce-pro/) allows you to pay as customer, via checkout page.
You can pay directly from admin area too - use [this free plugin](https://wordpress.org/plugins/woo-mp/). They support Stripe and Authorize.Net.

= Button "Create Order" does nothing  =
Probably, there is a conflict with another plugin. [Please, check javascript errors at first](https://codex.wordpress.org/Using_Your_Browser_to_Diagnose_JavaScript_Errors#Step_3:_Diagnosis) 

== Screenshots ==

1. Filled order 
2. Order was created
3. Edit customer details
4. Apply coupon, autocomplete
5. Adjust discount type and amount
6. Select shipping method 
7. Common settings
8. Interface settings

== Changelog ==

= 3.2.5 2019-02-27 =
* New settings tab "Interface"
* New option "Show order status" (off by default)
* New option "Show icon for phone orders in orders list" (off by default)
* Fixed bug - selected attribute was ignored for variable product

= 3.2.4 2019-02-18 =
* Fixed bug - option "Ship to a different address" didn't copy address for guests
* Fixed some UI bugs
* Added a lot of hooks (for compatibility with other plugins)

= 3.2.3 2019-01-23 =
* Added - support [Advanced Dynamic Pricing for WooCommerce](https://wordpress.org/plugins/advanced-dynamic-pricing-for-woocommerce/advanced/) for bulk/roles/others discounts
* Added - option "Switch customer during cart calculations" to >Settings>Common (off by default)
* Added - own capability "manage_woocommerce_phone_orders" ("manage_woocommerce" still works!)
* Added - "Default customer location"(state/country) applied to address if country was not selected
* Fixed bug - incompatiblity with caching plugins
* Speeded up cart calculations

= 3.2.2 2018-12-04 =
* Show images in product autocomplete
* Show customer links  - profile and orders
* Allow to edit meta for cart items
* Places autocomplete for address (you must generate Google Maps API key)
* Added button "Copy url to populate cart" (off by default)
* Added link "Create order" to users list
* French translation was added

= 3.2.1 2018-10-30 =
* Compatible with Woocommerce 3.5
* Support any number of items in the cart (tested for 100+ items)
* Fixed bug - shipping calculated  automatically even if user turned off "Autocalculation" at tab "Settings"
* Fixed bug - user was able to submit empty attribute for variation 

= 3.2.0 2018-10-11 =
* The plugin requires at least Woocommerce 3.3.0 !
* Show attribute dropdowns for item, if variation uses "Any" value
* Added option "Show order date/time"
* Added nested tabs to "Settings"
* Show warning message if coupons are disabled
* Fixed bug - settings were applied to admin only
* Fixed bug - slow customer autocomplete 
* Fixed bug - empty default shipping method (it didn't work for some shipping plugins)
* Work correctly with "sold individually" products
* German translation was added 

= 3.1.0 2018-08-20 =
* Increased  page loading speed
* Optimized product search
* Added section "References" to tab "Settings"
* Fixed minor UI bugs

= 3.0.0 2018-08-08 =
* UI migrated to modern JavaScript framework (Vue.js)

= 2.6.8 2018-06-18 =
* Added section "Coupons" to tab "Settings"
* Cache search results (only for coupons)
* Fixed bug in product search (for variable products)
* Added Spanish language

= 2.6.7 2018-06-06 =
* Fixed some incompatibility issues with WooCommerce 3.4 
* Removed unnecessary ajax method which updates shipping rates after modifying cart items

= 2.6.6 2018-05-11 =
* Support **subscription products**
* Prompting to save changes if user adds items and doesn't create the order
* Added tab "Log"
* Only admin has access to tab "Settings"
* Default payment method was added to tab "Settings"
* Bug fixed, we clear the cart for current user after order creation 

= 2.6.5 2018-04-23 =
* Added column to show full amounts
* Added tab "Help"
* Show shipping address for selected customer (if it doesn't match with billing address)
* Call necessary Woocommerce hooks to support discount plugins
* Bug fixed in popup "edit address", autocomplete didn't work for state/county

= 2.6.4 2018-04-02 =
* Increased speed of UI (reduced number of ajax requests)
* Supports multicurrency plugins
* Coupon search is not case sensitive
* Show extra information for  product in autocomplete (instock status, price, sku)
* Bug fixed, new customer didn't see password in welcome email
* Bug fixed, we show all applied coupons now (including automatic ones)

= 2.6.3 2018-03-13 =
* Show "Discount" in totals
* Coupon shows deducted amount
* Conflict was resolved if two versions (free and pro) are active

= 2.6.2 2018-03-03 =
* Fixed critial bug, products autocomplete doesn't work

= 2.6.1 2018-03-02 =
* Supports products with zero price
* Automatically adds shipping if cart has real products
* Input validation was added to all popups

= 2.6.0 2018-02-16 =
* Settings were moved to separate tab
* Fixed wrong item link (for variable products)
* Fixed error in custom prices for the items
* A lot of minor UI tweaks

= 2.5 2018-02-06 =
* Added "Free Shipping" method (in admin area only). Don't forget to assign it to necessary shipping zones!

= 2.4 2017-12-13 =
* Bug fixed - "create customer" fills address and phone

= 2.3 2017-11-17 =
* Bug fixed - localization works now

= 2.2 2017-09-07 =
* Added field "Private Note"
* Bug fixed - fill billing email for registered user

= 2.1 2017-08-04 =
* Create new products on fly
* Add new customer from same page
* Apply coupons to the order

= 2.0 2017-07-04 =
* Rebuild UI (show buttons after order creation )
* Skip out of stock products

= 1.0 2017-06-10 =
* Initial release