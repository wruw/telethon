<?php

namespace Barn2\Plugin\WC_Product_Table;

use Barn2\Plugin\WC_Product_Table\Admin\Admin_Controller;
use Barn2\Plugin\WC_Product_Table\Admin\Wizard\Setup_Wizard;
use Barn2\Plugin\WC_Product_Table\Util\Settings;
use Barn2\Plugin\WC_Product_Table\Widgets\Active_Filters_Widget;
use Barn2\Plugin\WC_Product_Table\Widgets\Attribute_Filter_Widget;
use Barn2\Plugin\WC_Product_Table\Widgets\Price_Filter_Widget;
use Barn2\Plugin\WC_Product_Table\Widgets\Rating_Filter_Widget;
use Barn2\Plugin\WC_Product_Table\Dependencies\Lib\Plugin\Licensed_Plugin;
use Barn2\Plugin\WC_Product_Table\Dependencies\Lib\Plugin\Premium_Plugin;
use Barn2\Plugin\WC_Product_Table\Dependencies\Lib\Registerable;
use Barn2\Plugin\WC_Product_Table\Dependencies\Lib\Service;
use Barn2\Plugin\WC_Product_Table\Dependencies\Lib\Service_Container;
use Barn2\Plugin\WC_Product_Table\Dependencies\Lib\Service_Provider;
use Barn2\Plugin\WC_Product_Table\Dependencies\Lib\Translatable;
use Barn2\Plugin\WC_Product_Table\Dependencies\Lib\Util;

/**
 * The main plugin class. Responsible for setting up the core plugin services.
 *
 * @package   Barn2\woocommerce-product-table
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Plugin extends Premium_Plugin implements Licensed_Plugin, Registerable, Translatable, Service_Provider {

	const NAME    = 'WooCommerce Product Table';
	const ITEM_ID = 12913;

	/**
	 * Constructor.
	 *
	 * @param string $file    The main plugin file (__FILE__). This is the file WordPress loads in the plugin root folder.
	 * @param string $version The plugin version string, e.g. '1.2.1'
	 */
	public function __construct( $file, $version = '1.0' ) {
		parent::__construct(
			[
				'id'                 => self::ITEM_ID,
				'name'               => self::NAME,
				'version'            => $version,
				'file'               => $file,
				'is_woocommerce'     => true,
				'is_hpos_compatible' => true,
				'documentation_path' => 'kb-categories/woocommerce-product-table-kb',
				'settings_path'      => 'admin.php?page=wc-settings&tab=products&section=' . Settings::SECTION_SLUG,
				'legacy_db_prefix'   => 'wcpt',
			]
		);

		$this->add_service( 'plugin_setup', new Plugin_Setup( $this ), true );
	}

	/**
	 * Registers the plugin hooks (add_action/add_filter).
	 *
	 * @return void
	 */
	public function register() {
		parent::register();

		add_action( 'plugins_loaded', [ $this, 'add_services' ] );

		add_action( 'init', [ $this, 'register_services' ] );
		add_action( 'init', [ $this, 'load_textdomain' ], 5 );
		add_action( 'init', [ $this, 'load_template_functions' ] );
	}

	/**
	 * Get the list of services that the plugin requires.
	 *
	 * @return Service[] The list of services.
	 */
	public function add_services() {

		if ( ! Util::is_woocommerce_active() ) {
			return;
		}

		add_action( 'widgets_init', [ $this, 'register_widgets' ] );

		$this->add_service( 'wizard', new Setup_Wizard( $this ) );
		$this->add_service( 'admin', new Admin\Admin_Controller( $this ) );

		if ( $this->has_valid_license() ) {
			$this->add_service( 'shortcode', new Table_Shortcode() );
			$this->add_service( 'scripts', new Frontend_Scripts( $this->get_version() ) );
			$this->add_service( 'cart_handler', new Cart_Handler() );
			$this->add_service( 'ajax_handler', new Ajax_Handler() );
			$this->add_service( 'template_handler', new Template_Handler() );
			$this->add_service( 'theme_compat', new Integration\Theme_Integration() );
			$this->add_service( 'searchwp', new Integration\SearchWP() );
			$this->add_service( 'product_addons', new Integration\Product_Addons() );
			$this->add_service( 'quick_view_pro', new Integration\Quick_View_Pro() );
			$this->add_service( 'variation_swatches', new Integration\Variation_Swatches() );
			$this->add_service( 'yith_request_quote', new Integration\YITH_Request_Quote() );
		}
	}

	/**
	 * Load the plugin template functions file.
	 *
	 * @return void
	 */
	public function load_template_functions() {
		require_once $this->get_dir_path() . 'src/template-functions.php';
	}

	/**
	 * Load the plugin's language files by calling load_plugin_textdomain.
	 *
	 * @return void
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'woocommerce-product-table', false, $this->get_slug() . '/languages' );
	}

	/**
	 * Register the plugin's widgets.
	 *
	 * @return void
	 */
	public function register_widgets() {
		if ( ! $this->get_license()->is_valid() ) {
			return;
		}

		$widget_classes = [
			Active_Filters_Widget::class,
			Attribute_Filter_Widget::class,
			Price_Filter_Widget::class,
			Rating_Filter_Widget::class
		];

		// Register the product table widgets
		array_map( 'register_widget', array_filter( $widget_classes, 'class_exists' ) );
	}

}
