<?php
/*
Plugin Name: Product Open Pricing (Name Your Price) for WooCommerce Pro
Plugin URI: https://wpwham.com/products/product-open-pricing-name-your-price-for-woocommerce/
Description: Open price (i.e. Name your price) products for WooCommerce.
Version: 1.7.0
Author: WP Wham
Author URI: https://wpwham.com/
Text Domain: product-open-pricing-for-woocommerce
Domain Path: /langs
WC tested up to: 5.7
Copyright: © 2018-2021 WP Wham. All rights reserved.
This software may not be resold, redistributed or otherwise conveyed to a third party.
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Check if WooCommerce is active
$plugin = 'woocommerce/woocommerce.php';
if (
	! in_array( $plugin, apply_filters( 'active_plugins', get_option( 'active_plugins', array() ) ) ) &&
	! ( is_multisite() && array_key_exists( $plugin, get_site_option( 'active_sitewide_plugins', array() ) ) )
) {
	return;
}

if ( 'product-open-pricing-for-woocommerce.php' === basename( __FILE__ ) ) {
	// Check if Pro is active, if so then return
	$plugin = 'product-open-pricing-for-woocommerce-pro/product-open-pricing-for-woocommerce-pro.php';
	if (
		in_array( $plugin, apply_filters( 'active_plugins', get_option( 'active_plugins', array() ) ) ) ||
		( is_multisite() && array_key_exists( $plugin, get_site_option( 'active_sitewide_plugins', array() ) ) )
	) {
		return;
	}
}

define( 'WPWHAM_PRODUCT_OPEN_PRICING_PRO_VERSION', '1.7.0' );
define( 'WPWHAM_PRODUCT_OPEN_PRICING_PRO_FILE', __FILE__ );

include( plugin_dir_path( __FILE__ ) . '/updater/plugin-updater.php' ); 

register_deactivation_hook( __FILE__, array( 'Alg_WC_Product_Open_Pricing', 'deactivate' ) );



if ( ! class_exists( 'Alg_WC_Product_Open_Pricing' ) ) :

/**
 * Main Alg_WC_Product_Open_Pricing Class
 *
 * @class   Alg_WC_Product_Open_Pricing
 * @version 1.7.0
 * @since   1.0.0
 */
final class Alg_WC_Product_Open_Pricing {

	/**
	 * Plugin version.
	 *
	 * @var   string
	 * @since 1.0.0
	 */
	public $version = '1.7.0';

	/**
	 * @var   Alg_WC_Product_Open_Pricing The single instance of the class
	 * @since 1.0.0
	 */
	protected static $_instance = null;

	/**
	 * Main Alg_WC_Product_Open_Pricing Instance
	 *
	 * Ensures only one instance of Alg_WC_Product_Open_Pricing is loaded or can be loaded.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 * @static
	 * @return  Alg_WC_Product_Open_Pricing - Main instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Alg_WC_Product_Open_Pricing Constructor.
	 *
	 * @version 1.3.0
	 * @since   1.0.0
	 * @access  public
	 */
	function __construct() {

		// Set up localisation
		load_plugin_textdomain( 'product-open-pricing-for-woocommerce', false, dirname( plugin_basename( __FILE__ ) ) . '/langs/' );

		// The filter
		add_filter( 'alg_wc_product_open_pricing', array( $this, 'alg_wc_product_open_pricing' ), PHP_INT_MAX, 2 );

		// Include required files
		$this->includes();

		// Admin
		if ( is_admin() ) {
			$this->admin();
		}
	}
	
	/**
	 * @since   1.4.0
	 */
	public static function deactivate() {
	
		wp_clear_scheduled_hook( 'wpwham_product_open_pricing_updater' );
		$updater = Wpwham_Product_Open_Pricing_Updater::get_instance();
		$updater->updater_notices_clear();
		
	}
	
	/**
	 * @since   1.7.0
	 */
	public function enqueue_scripts() {
		global $pagenow;
		
		// check we are on the settings page
		if (
			$pagenow === 'admin.php'
			&& isset( $_REQUEST['tab'] ) && $_REQUEST['tab'] === 'alg_wc_product_open_pricing'
		) {
			wp_enqueue_script(
				'wpwham-product-open-pricing-admin',
				$this->plugin_url() . '/includes/js/admin.js',
				array( 'jquery' ),
				WPWHAM_PRODUCT_OPEN_PRICING_PRO_VERSION,
				false
			);
		}
	}
	
	/**
	 * @version 1.7.0
	 * @since   1.4.0
	 */
	public function enqueue_styles() {
		global $pagenow;
		
		// check we are on the settings page
		if (
			$pagenow === 'admin.php'
			&& isset( $_REQUEST['tab'] ) && $_REQUEST['tab'] === 'alg_wc_product_open_pricing'
		) {
			wp_enqueue_style(
				'wpwham-product-open-pricing-admin',
				$this->plugin_url() . '/includes/css/admin.css',
				array(),
				WPWHAM_PRODUCT_OPEN_PRICING_PRO_VERSION,
				'all'
			);
		}
	}

	/**
	 * alg_wc_product_open_pricing.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function alg_wc_product_open_pricing( $value, $type ) {
		switch ( $type ) {
			case 'per_product_settings':
				return true;
		}
		return $value;
	}

	/**
	 * Include required core files used in admin and on the frontend.
	 *
	 * @version 1.4.6
	 * @since   1.0.0
	 */
	function includes() {
	
		// Core
		require_once( 'includes/class-alg-wc-product-open-pricing-core.php' );
		
		// Extensions
  		if ( ! class_exists( 'Wpwham_Extensions', false ) ) {
			include( 'includes/class-wpwham-extensions.php' );
		}
		$this->extensions = Wpwham_Extensions::get_instance();
		$this->extensions->register( 'product-open-pricing-name-your-price-for-woocommerce' );
		
	}

	/**
	 * add settings to WC status report
	 *
	 * @version 1.5.0
	 * @since   1.4.6
	 * @author  WP Wham
	 */
	public static function add_settings_to_status_report() {
		#region add_settings_to_status_report
		$protected_settings = array( 'wpwham_product_open_pricing_license' );
		$settings           = Alg_WC_Product_Open_Pricing_Settings_General::get_settings();
		?>
		<table class="wc_status_table widefat" cellspacing="0">
			<thead>
				<tr>
					<th colspan="3" data-export-label="Product Open Pricing Settings"><h2><?php esc_html_e( 'Product Open Pricing Settings', 'product-open-pricing-for-woocommerce' ); ?></h2></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $settings as $setting ): ?>
				<?php 
				if ( in_array( $setting['type'], array( 'title', 'sectionend' ) ) ) { 
					continue;
				}
				if ( isset( $setting['title'] ) ) {
					$title = $setting['title'];
				} elseif ( isset( $setting['desc'] ) ) {
					$title = $setting['desc'];
				} else {
					$title = $setting['id'];
				}
				$value = get_option( $setting['id'] ); 
				if ( in_array( $setting['id'], $protected_settings ) ) {
					$value = $value > '' ? '(set)' : 'not set';
				}
				?>
				<tr>
					<td data-export-label="<?php echo esc_attr( $title ); ?>"><?php esc_html_e( $title, 'product-open-pricing-for-woocommerce' ); ?>:</td>
					<td class="help">&nbsp;</td>
					<td><?php echo is_array( $value ) ? print_r( $value, true ) : $value; ?></td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php
		#endregion add_settings_to_status_report
	}

	/**
	 * admin.
	 *
	 * @version 1.7.0
	 * @since   1.3.0
	 */
	function admin() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		// Action links
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'action_links' ) );
		// Settings
		require_once( 'includes/settings/class-alg-wc-product-open-pricing-settings-section.php' );
		$this->settings = array();
		$this->settings['general'] = require_once( 'includes/settings/class-alg-wc-product-open-pricing-settings-general.php' );
		add_filter( 'woocommerce_get_settings_pages', array( $this, 'add_woocommerce_settings_tab' ) );
		add_action( 'woocommerce_system_status_report', array( $this, 'add_settings_to_status_report' ) );
		// Metaboxes (per Product Settings)
		require_once( 'includes/settings/class-alg-wc-product-open-pricing-settings-per-product.php' );
		// Version updated
		if ( get_option( 'alg_wc_product_open_pricing_version', '' ) !== $this->version ) {
			add_action( 'admin_init', array( $this, 'version_updated' ) );
		}
	}

	/**
	 * Show action links on the plugin screen.
	 *
	 * @version 1.7.0
	 * @since   1.0.0
	 * @param   mixed $links
	 * @return  array
	 */
	function action_links( $links ) {
		$custom_links = array();
		$custom_links[] = '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=alg_wc_product_open_pricing' ) . '">' . __( 'Settings', 'woocommerce' ) . '</a>';
		if ( 'product-open-pricing-for-woocommerce.php' === basename( __FILE__ ) ) {
			$custom_links[] = '<a href="https://wpwham.com/products/product-open-pricing-name-your-price-for-woocommerce/?utm_source=plugins_page&utm_campaign=free&utm_medium=product_open_pricing">' . __( 'Unlock All', 'product-open-pricing-for-woocommerce' ) . '</a>';
		}
		return array_merge( $custom_links, $links );
	}

	/**
	 * Add Product Open Pricing settings tab to WooCommerce settings.
	 *
	 * @version 1.3.0
	 * @since   1.0.0
	 */
	function add_woocommerce_settings_tab( $settings ) {
		$settings[] = require_once( 'includes/settings/class-alg-wc-settings-product-open-pricing.php' );
		return $settings;
	}

	/**
	 * version_updated.
	 *
	 * @version 1.3.0
	 * @since   1.3.0
	 */
	function version_updated() {
		update_option( 'alg_wc_product_open_pricing_version', $this->version );
	}

	/**
	 * Get the plugin url.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 * @return  string
	 */
	function plugin_url() {
		return untrailingslashit( plugin_dir_url( __FILE__ ) );
	}

	/**
	 * Get the plugin path.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 * @return  string
	 */
	function plugin_path() {
		return untrailingslashit( plugin_dir_path( __FILE__ ) );
	}

}

endif;

if ( ! function_exists( 'alg_wc_product_open_pricing' ) ) {
	/**
	 * Returns the main instance of Alg_WC_Product_Open_Pricing to prevent the need to use globals.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 * @return  Alg_WC_Product_Open_Pricing
	 */
	function alg_wc_product_open_pricing() {
		return Alg_WC_Product_Open_Pricing::instance();
	}
}

alg_wc_product_open_pricing();
