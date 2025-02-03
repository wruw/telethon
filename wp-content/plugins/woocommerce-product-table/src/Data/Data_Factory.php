<?php

namespace Barn2\Plugin\WC_Product_Table\Data;

use Barn2\Plugin\WC_Product_Table\Table_Args;
use Barn2\Plugin\WC_Product_Table\Util\Columns;
use Barn2\Plugin\WC_Product_Table\Util\Settings;
use Barn2\Plugin\WC_Product_Table\Dependencies\Lib\Table\Table_Data_Interface;

/**
 * Factory class to get the product table data object for a given column.
 *
 * @package   Barn2\woocommerce-product-table
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Data_Factory {

	/**
	 * @var Table_Args $args The table args.
	 */
	private $args;

	public function __construct( Table_Args $args ) {
		$this->args = $args;
	}

	public function create( $column, $product ) {
		/**
		 * Custom columns method 1: return an object implementing the Table_Data_Interface interface.
		 *
		 * @see Table_Data_Interface
		 */
		$data_object = apply_filters( 'wc_product_table_custom_table_data_' . $column, false, $product, $this->args );

		if ( $data_object instanceof Table_Data_Interface ) {
			return $data_object;
		}

		// Create the data object for the column.
		switch ( $column ) {
			case 'id':
			case 'sku':
			case 'categories':
			case 'tags':
			case 'weight':
			case 'dimensions':
			case 'stock':
			case 'price':
			case 'reviews':
				$data_class = __NAMESPACE__ . '\\Product_' . ucfirst( $column );

				if ( class_exists( $data_class ) ) {
					$data_object = new $data_class( $product, $this->args->links );
				}
				break;
			case 'name':
				$data_object = new Product_Name( $product, $this->args->links, Settings::get_setting_misc()['variation_name_format'] );
				break;
			case 'image':
				$data_object = new Product_Image( $product, $this->args->links, $this->args->image_size, $this->args->lightbox );
				break;
			case 'date':
				$data_object = new Product_Date( $product, $this->args->date_format );
				break;
			case 'date_modified':
				$data_object = new Product_Date_Modified( $product, $this->args->date_format );
				break;
			case 'summary':
				$data_object = new Product_Summary( $product, $this->args->description_length, $this->args->shortcodes );
				break;
			case 'description':
				$data_object = new Product_Description( $product, $this->args->description_length, $this->args->shortcodes );
				break;
			case 'buy':
				$data_object = new Product_Cart( $product, $this->args->variations, $this->args->quantities, $this->args->cart_button, $this->args->is_multi_add_to_cart() );
				break;
			case 'button':
				$data_object = new Product_Button( $product, $this->args->button_text );
				break;
			default:
				if ( $attribute = Columns::get_product_attribute( $column ) ) {
					// Attribute column.
					$data_object = new Product_Attribute( $product, $attribute, $this->args->links, $this->args->lazy_load );
				} elseif ( $taxonomy = Columns::get_custom_taxonomy( $column ) ) {
					// Custom taxonomy column.
					$data_object = new Product_Custom_Taxonomy( $product, $taxonomy, $this->args->links, $this->args->date_format, $this->args->date_columns );
				} elseif ( $field = Columns::get_custom_field( $column ) ) {
					// Custom field column.
					$data_object = new Product_Custom_Field( $product, $field, $this->args->links, $this->args->image_size, $this->args->date_format, $this->args->date_columns );
				} elseif ( $filter = Columns::get_hidden_filter_column( $column ) ) {
					// Hidden filter column.
					$data_object = new Product_Hidden_Filter( $product, $filter, $this->args->lazy_load );
				}
				break;
		}

		return $data_object;
	}

}
