<?php
/**
 * Plugin Name: QP Payment Gateway for Woo
 * Plugin URI: http://artsci.case.edu/
 * Description: Extends WooCommerce by adding a Quikpay Gateway
 * Version: 1.1.1
 * Author: CWRU College of Arts and Sciences, Jesse Cavendish, Sarah Bailey
 * Author URI: http://artsci.case.edu/
 *
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
 * along with QP Payment Gateway for Woo.  If not, see <http://www.gnu.org/licenses/>.
 *
 * Copyright 2015 Case Western Reserve University College of Arts and Sciences, Jesse Cavendish, Sarah Bailey
 */

// Include the gateway class and register payment gateway with WooCommerce
add_action( 'plugins_loaded', 'quikpay_init', 0 );

/**
 * Checks if WooCommerce exists and attempts to add QuikPAY to WooCommerce if it does
 * 
 * @return array Adds QuikPAY's methods to WooCommerce
 */
function quikpay_init() {
	// If the WC_Payment_Gateway class doesn't exit, do nothing
	If (!class_exists('WC_Payment_Gateway')) {
            return;
    }

    // Include this Gateway class
	include_once( 'QuikPAY.php' );
	
	// Add to WooCommercce
	add_filter( 'woocommerce_payment_gateways', 'add_quikpay_gateway' );
	function add_quikpay_gateway( $methods ) {
		$methods[] = 'QuikPAY';
		return $methods;
	}
}

// Add custom action links
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'quikpay_action_links' );
function quikpay_action_links( $links ) {
	$plugin_links = array(
		'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout' ) . '">' . __( 'Settings', 'quikpay' ) . '</a>',
	);
	
	return array_merge( $plugin_links, $links );
}

add_filter( 'woocommerce_locate_template', 'myplugin_woocommerce_locate_template', 10, 3 );

function myplugin_plugin_path() {
	return untrailingslashit( plugin_dir_path( __FILE__ ) );
}

function myplugin_woocommerce_locate_template( $template, $template_name, $template_path ) {
	global $woocommerce;
	$_template = $template;
	if ( ! $template_path ) $template_path = $woocommerce->template_url;
	$plugin_path  = myplugin_plugin_path() . '/woocommerce/';
	$template = locate_template(
			array(
					$template_path . $template_name,
					$template_name
			)
	);

	if ( ! $template && file_exists( $plugin_path . $template_name ) )

		$template = $plugin_path . $template_name;
	if ( ! $template )
		$template = $_template;
	return $template;

}
?>