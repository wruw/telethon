<?php

namespace Barn2\Plugin\WC_Product_Table;

/**
 * Factory to create/return the shared plugin instance.
 *
 * @package   Barn2\woocommerce-product-table
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Plugin_Factory {

	private static $plugin = null;

	/**
	 * Create/return the shared plugin instance.
	 *
	 * @param string $file
	 * @param string $version
	 * @return Plugin The plugin instance.
	 */
	public static function create( $file, $version ) {
		if ( null === self::$plugin ) {
			self::$plugin = new Plugin( $file, $version );
		}
		return self::$plugin;
	}

}
