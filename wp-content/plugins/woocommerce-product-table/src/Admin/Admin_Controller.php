<?php

namespace Barn2\Plugin\WC_Product_Table\Admin;

use Barn2\Plugin\WC_Product_Table\Util\Util;
use Barn2\Plugin\WC_Product_Table\Dependencies\Lib\Conditional;
use Barn2\Plugin\WC_Product_Table\Dependencies\Lib\Plugin\Admin\Admin_Links;
use Barn2\Plugin\WC_Product_Table\Dependencies\Lib\Plugin\Licensed_Plugin;
use Barn2\Plugin\WC_Product_Table\Dependencies\Lib\Registerable;
use Barn2\Plugin\WC_Product_Table\Dependencies\Lib\Service;
use Barn2\Plugin\WC_Product_Table\Dependencies\Lib\Service_Container;
use Barn2\Plugin\WC_Product_Table\Dependencies\Lib\Util as Lib_Util;
use Barn2\Plugin\WC_Product_Table\Dependencies\Lib\WooCommerce\Admin\Navigation;

/**
 * Handles general admin functions, such as adding links to our settings page in the Plugins menu.
 *
 * @package   Barn2\woocommerce-product-table
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Admin_Controller implements Service, Registerable, Conditional {

	use Service_Container;

	private $plugin;

	public function __construct( Licensed_Plugin $plugin ) {
		$this->plugin = $plugin;

		$this->add_services();
	}

	public function is_required() {
		return Lib_Util::is_admin();
	}

	public function register() {
		$this->register_services();
		add_action( 'admin_enqueue_scripts', [ $this, 'register_admin_scripts' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'setup_wizard_inline_styles' ] );
	}

	public function add_services() {
		$this->add_service( 'admin_links', new Admin_Links( $this->plugin ) );
		$this->add_service( 'settings_page', new Settings_Page( $this->plugin ) );
		$this->add_service( 'tiny_mce', new TinyMCE() );
		$this->add_service( 'navigation', new Navigation( $this->plugin, 'product-table', __( 'Product Table', 'woocommerce-product-table' ) ) );
	}

	public function register_admin_scripts( $hook_suffix ) {
		if ( 'woocommerce_page_wc-settings' !== $hook_suffix ) {
			return;
		}

		$suffix = Lib_Util::get_script_suffix();

		wp_enqueue_style( 'wcpt-admin', Util::get_asset_url( 'css/admin/wc-product-table-admin.css' ), [], $this->plugin->get_version() );
		wp_enqueue_script( 'wcpt-admin', Util::get_asset_url( "js/admin/wc-product-table-admin.js" ), [ 'jquery' ], $this->plugin->get_version(), true );
	}

	/**
	 * Enqueue inline styling needed for the setup wizard.
	 *
	 * @param string $hook
	 * @return void
	 */
	public function setup_wizard_inline_styles( $hook ) {

		if ( $hook !== 'toplevel_page_woocommerce-product-table-setup-wizard' ) {
			return;
		}

		wp_register_style( 'wpt-wizard-dummy-handle', false );
		wp_enqueue_style( 'wpt-wizard-dummy-handle' );

		wp_add_inline_style( 'wpt-wizard-dummy-handle', '.no-top-pad {margin-top:-1rem}' );

	}

}
