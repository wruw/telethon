<?php

namespace Barn2\Plugin\WC_Product_Table;

use Automattic\Jetpack\Constants;
use Barn2\Plugin\WC_Product_Table\Data\Product_Hidden_Filter;
use Barn2\Plugin\WC_Product_Table\Integration\Quick_View_Pro;
use Barn2\Plugin\WC_Product_Table\Util\Settings;
use Barn2\Plugin\WC_Product_Table\Util\Util;
use Barn2\Plugin\WC_Product_Table\Dependencies\Lib\Conditional;
use Barn2\Plugin\WC_Product_Table\Dependencies\Lib\CSS_Util;
use Barn2\Plugin\WC_Product_Table\Dependencies\Lib\Registerable;
use Barn2\Plugin\WC_Product_Table\Dependencies\Lib\Service;
use Barn2\Plugin\WC_Product_Table\Dependencies\Lib\Util as Lib_Util;

/**
 * Handles the registering of the front-end scripts and stylesheets. Also creates the inline CSS (if required) for the product tables.
 *
 * @package   Barn2\woocommerce-product-table
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Frontend_Scripts implements Service, Registerable, Conditional {

	const SCRIPT_HANDLE      = 'wc-product-table';
	const DATATABLES_VERSION = '1.13.5';

	private $script_version;

	/**
	 * Constructor.
	 *
	 * @param string $script_version The script version for registering product table assets.
	 */
	public function __construct( $script_version ) {
		$this->script_version = $script_version;
	}

	public function is_required() {
		return Lib_Util::is_front_end();
	}

	public function register() {
		// Register front-end styles and scripts
		add_action( 'wp_enqueue_scripts', [ $this, 'register_styles' ], 15 ); // after WooCommerce load_scripts()
		add_action( 'wp_enqueue_scripts', [ $this, 'register_scripts' ], 15 ); // after WooCommerce load_scripts()
		add_action( 'wp_enqueue_scripts', [ $this, 'load_head_scripts' ], 20 );

		add_action( 'wc_product_table_before_load_table_scripts', [ $this, 'reregister_woocommerce_scripts' ] );
	}

	public function load_scripts() {
		_deprecated_function( __METHOD__, '2.9', 'register' );
		$this->register();
	}

	public function register_styles() {
		$style_options = Settings::get_setting_table_styling();

		wp_register_style( 'jquery-datatables-wpt', Util::get_asset_url( 'js/datatables/datatables.min.css' ), [], self::DATATABLES_VERSION );

		wp_register_style(
			self::SCRIPT_HANDLE,
			Util::get_asset_url( 'css/styles.css' ),
			[ 'jquery-datatables-wpt', 'select2' ],
			$this->script_version
		);

		// Add RTL data - we need suffix to correctly format RTL stylesheet when minified.
		wp_style_add_data( self::SCRIPT_HANDLE, 'rtl', 'replace' );
		wp_style_add_data( self::SCRIPT_HANDLE, 'suffix', '.min' );

		// Add custom styles (if enabled)
		if ( 'custom' === $style_options['use_theme'] ) {
			wp_add_inline_style( self::SCRIPT_HANDLE, self::build_custom_styles( $style_options ) );
		}

		// Header styles - we just a dummy handle as we only need inline styles in <head>.
		wp_register_style( 'wc-product-table-head', false, [], '1.0' );

		// Ensure tables don't 'flicker' on page load - visibility is set by JS when table initialised.
		wp_add_inline_style( 'wc-product-table-head', 'table.wc-product-table { visibility: hidden; }' );
	}

	public function register_scripts() {
		$suffix = Lib_Util::get_script_suffix();

		wp_register_script( 'jquery-datatables-wpt', Util::get_asset_url( "js/datatables/datatables{$suffix}.js" ), [ 'jquery' ], self::DATATABLES_VERSION, true );
		wp_register_script( 'fitvids', Util::get_asset_url( 'js/jquery-fitvids/jquery.fitvids.min.js' ), [ 'jquery' ], '1.1', true );

		// We need to use a unique handle for our serialize object script to distinguish it from the built-in WordPress version.
		wp_register_script(
			'jquery-serialize-object-wpt',
			Util::get_asset_url( 'js/jquery-serialize-object/jquery.serialize-object.min.js' ),
			[ 'jquery' ],
			'2.5',
			true
		);

		wp_register_script(
			self::SCRIPT_HANDLE,
			Util::get_asset_url( "js/wc-product-table.js" ),
			[ 'jquery', 'jquery-datatables-wpt', 'jquery-serialize-object-wpt', 'jquery-blockui', 'selectWoo' ],
			$this->script_version,
			true
		);

		$script_params = [
			'ajax_url'                => admin_url( 'admin-ajax.php' ),
			'ajax_nonce'              => wp_create_nonce( self::SCRIPT_HANDLE ),
			'multi_cart_button_class' => esc_attr( apply_filters( 'wc_product_table_multi_cart_class', Util::get_button_class() ) ),
			'enable_select2'          => apply_filters( 'wc_product_table_enable_select2', true ),
			'filter_term_separator'   => Product_Hidden_Filter::get_term_separator(),
			'language'                => apply_filters(
				'wc_product_table_language_defaults',
				[
					'info'                 => __( 'Showing _TOTAL_ products', 'woocommerce-product-table' ),
					'infoEmpty'            => __( '0 products', 'woocommerce-product-table' ),
					'infoFiltered'         => __( '(_MAX_ in total)', 'woocommerce-product-table' ),
					'lengthMenu'           => __( 'Show _MENU_ per page', 'woocommerce-product-table' ),
					'emptyTable'           => __( 'No matching products', 'woocommerce-product-table' ),
					'zeroRecords'          => __( 'No matching products', 'woocommerce-product-table' ),
					'search'               => apply_filters( 'wc_product_table_search_label', __( 'Search:', 'woocommerce-product-table' ) ),
					'paginate'             => [
						'first'    => __( 'First', 'woocommerce-product-table' ),
						'last'     => __( 'Last', 'woocommerce-product-table' ),
						'next'     => __( 'Next', 'woocommerce-product-table' ),
						'previous' => __( 'Previous', 'woocommerce-product-table' ),
					],
					'thousands'            => _x( ',', 'thousands separator', 'woocommerce-product-table' ),
					'decimal'              => _x( '.', 'decimal mark', 'woocommerce-product-table' ),
					'aria'                 => [
						/* translators: ARIA text for sorting column in ascending order */
						'sortAscending'  => __( ': activate to sort column ascending', 'woocommerce-product-table' ),
						/* translators: ARIA text for sorting column in descending order */
						'sortDescending' => __( ': activate to sort column descending', 'woocommerce-product-table' ),
					],
					'filterBy'             => apply_filters( 'wc_product_table_search_filter_label', '' ),
					'resetButton'          => apply_filters( 'wc_product_table_reset_button', __( 'Reset', 'woocommerce-product-table' ) ),
					'multiCartButton'      => esc_attr( apply_filters( 'wc_product_table_multi_cart_button', Settings::get_setting_misc()['add_selected_text'] ) ),
					'multiCartNoSelection' => __( 'Please select one or more products.', 'woocommerce-product-table' )
				]
			)
		];

		if ( Quick_View_Pro::open_links_in_quick_view() ) {
			$script_params['open_links_in_quick_view'] = true;
		}

		wp_add_inline_script(
			self::SCRIPT_HANDLE,
			sprintf( 'var product_table_params = %s;', wp_json_encode( $script_params ) ),
			'before'
		);
	}

	public function load_head_scripts() {
		wp_enqueue_style( 'wc-product-table-head' );
	}

	/**
	 * Some themes take it upon themselves to remove core WC scripts & styles which we require, so re-register them here.
	 *
	 * @return void
	 */
	public function reregister_woocommerce_scripts() {
		$wc_version = Constants::get_constant( 'WC_VERSION' );

		// Register any scripts that we require that may have been dequeued by theme or other plugins.
		$required_styles = [
			'photoswipe'              => [
				'src'     => Util::get_wc_asset_url( 'css/photoswipe/photoswipe.min.css' ),
				'deps'    => [],
				'version' => $wc_version
			],
			'photoswipe-default-skin' => [
				'src'     => Util::get_wc_asset_url( 'css/photoswipe/default-skin/default-skin.min.css' ),
				'deps'    => [ 'photoswipe' ],
				'version' => $wc_version
			],
			'select2'                 => [
				'src'     => Util::get_wc_asset_url( 'css/select2.css' ),
				'deps'    => [],
				'version' => $wc_version
			]
		];

		foreach ( $required_styles as $style => $script_data ) {
			if ( ! wp_style_is( $style, 'registered' ) ) {
				wp_register_style( $style, $script_data['src'], $script_data['deps'], $script_data['version'] );
			}
		}

		// Register any scripts that we require that may have been dequeued by theme or other plugins.
		$required_scripts = [
			'jquery-blockui'        => [
				'src'     => Util::get_wc_asset_url( 'js/jquery-blockui/jquery.blockUI.min.js' ),
				'deps'    => [ 'jquery' ],
				'version' => '2.7.0-wc.' . $wc_version
			],
			'photoswipe'            => [
				'src'     => Util::get_wc_asset_url( 'js/photoswipe/photoswipe.min.js' ),
				'deps'    => [],
				'version' => '4.1.1-wc.' . $wc_version
			],
			'photoswipe-ui-default' => [
				'src'     => Util::get_wc_asset_url( 'js/photoswipe/photoswipe-ui-default.min.js' ),
				'deps'    => [],
				'version' => '4.1.1-wc.' . $wc_version
			],
			'selectWoo'             => [
				'src'     => Util::get_wc_asset_url( 'js/selectWoo/selectWoo.full.min.js' ),
				'deps'    => [ 'jquery' ],
				'version' => '1.0.9-wc.' . $wc_version
			]
		];

		foreach ( $required_scripts as $script => $script_data ) {
			if ( ! wp_script_is( $script, 'registered' ) ) {
				wp_register_script( $script, $script_data['src'], $script_data['deps'], $script_data['version'], true );
			}
		}
	}

	/**
	 * Register the scripts & styles for an individual product table.
	 *
	 * @param Table_Args $args
	 */
	public static function load_table_scripts( Table_Args $args ) {
		do_action( 'wc_product_table_before_load_table_scripts', $args );

		// Queue the main table styles and scripts.
		wp_enqueue_style( self::SCRIPT_HANDLE );
		wp_enqueue_script( self::SCRIPT_HANDLE );

		// Add fitVids for responsive video if we're displaying shortcodes.
		if ( apply_filters( 'wc_product_table_enable_fitvids', true ) ) {
			wp_enqueue_script( 'fitvids' );
		}

		// Queue media element and playlist scripts/styles.
		wp_enqueue_style( 'wp-mediaelement' );
		wp_enqueue_script( 'wp-mediaelement' );
		wp_enqueue_script( 'wp-playlist' );
		add_action( 'wp_footer', 'wp_underscore_playlist_templates', 0 );

		if ( in_array( 'buy', $args->columns, true ) ) {
			if ( 'dropdown' === $args->variations ) {
				wp_enqueue_script( 'wc-add-to-cart-variation' );
			}

			// Enqueue and localize add to cart script if not queued already.
			if ( $args->ajax_cart ) {
				wp_enqueue_script( 'wc-add-to-cart' );
			}
		}

		// Enqueue Photoswipe for image lightbox.
		if ( in_array( 'image', $args->columns, true ) && $args->lightbox ) {
			wp_enqueue_style( 'photoswipe-default-skin' );
			wp_enqueue_script( 'photoswipe-ui-default' );

			if ( false === has_action( 'wp_footer', 'woocommerce_photoswipe' ) ) {
				add_action( 'wp_footer', [ self::class, 'load_photoswipe_template' ] );
			}
		}

		do_action( 'wc_product_table_load_table_scripts', $args );
	}

	public static function load_photoswipe_template() {
		wc_get_template( 'single-product/photoswipe.php' );
	}

	private static function build_custom_styles( $options ) {
		$styles = [];
		$result = '';

		if ( ! empty( $options['border_outer'] ) ) {
			$styles[] = [
				'selector' => 'table.wc-product-table.no-footer',
				'css'      => 'border-bottom-width: 0;'
			];
			$styles[] = [
				'selector' => 'table.wc-product-table',
				'css'      => CSS_Util::build_border_style( $options['border_outer'], 'all', true )
			];
		}
		if ( ! empty( $options['border_header'] ) ) {
			$styles[] = [
				'selector' => 'table.wc-product-table thead th',
				'css'      => CSS_Util::build_border_style( $options['border_header'], 'bottom', true )
			];
			$styles[] = [
				'selector' => 'table.wc-product-table tfoot th',
				'css'      => CSS_Util::build_border_style( $options['border_header'], 'top', true )
			];
		}
		if ( ! empty( $options['border_cell'] ) ) {
			$cell_left_css = CSS_Util::build_border_style( $options['border_cell'], 'left', true );

			if ( $cell_left_css ) {
				$styles[] = [
					'selector' => 'table.wc-product-table td, table.wc-product-table th',
					'css'      => 'border-width: 0;'
				];
				$styles[] = [
					'selector' => 'table.wc-product-table td, table.wc-product-table th',
					'css'      => $cell_left_css
				];
				$styles[] = [
					'selector' => 'table.wc-product-table td:first-child, table.wc-product-table td.control[style*="none"] + td, table.wc-product-table th:first-child',
					'css'      => 'border-left: none !important;'
				];
			}

			$cell_top_css = CSS_Util::build_border_style( $options['border_cell'], 'top', true );

			if ( $cell_top_css ) {
				$styles[] = [
					'selector' => 'table.wc-product-table td',
					'css'      => $cell_top_css
				];
				$styles[] = [
					'selector' => 'table.wc-product-table tbody tr:first-child td',
					'css'      => 'border-top: none !important;'
				];
			}
		}
		if ( ! empty( $options['header_bg'] ) ) {
			$styles[] = [
				'selector' => 'table.wc-product-table thead, table.wc-product-table tfoot',
				'css'      => 'background-color: transparent;'
			];
			$styles[] = [
				'selector' => 'table.wc-product-table th',
				'css'      => CSS_Util::build_background_style( $options['header_bg'], true )
			];
		}
		if ( ! empty( $options['cell_bg'] ) ) {
			$styles[] = [
				'selector' => 'table.wc-product-table tbody tr',
				'css'      => 'background-color: transparent !important;'
			];
			$styles[] = [
				'selector' => 'table.wc-product-table tbody td',
				'css'      => CSS_Util::build_background_style( $options['cell_bg'], true )
			];
		}
		if ( ! empty( $options['header_font'] ) ) {
			$styles[] = [
				'selector' => 'table.wc-product-table th',
				'css'      => CSS_Util::build_font_style( $options['header_font'], true )
			];
		}
		if ( ! empty( $options['cell_font'] ) ) {
			$styles[] = [
				'selector' => 'table.wc-product-table tbody td',
				'css'      => CSS_Util::build_font_style( $options['cell_font'], true )
			];
		}

		foreach ( $styles as $style ) {
			if ( ! empty( $style['css'] ) ) {
				$result .= sprintf( '%1$s { %2$s } ', $style['selector'], $style['css'] );
			}
		}

		return trim( $result );
	}

}
