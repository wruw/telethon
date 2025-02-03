<?php

namespace Barn2\Plugin\WC_Product_Table\Widgets;

use Barn2\Plugin\WC_Product_Table\Util\Util;
use WC_Query;

/**
 * Product Table implementation of WooCommerce Layered Navigation Filters Widget.
 *
 * Based on version 2.3.0 of the WC_Widget_Layered_Nav_Filters class.
 *
 * @package   Barn2\woocommerce-product-table
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
class Active_Filters_Widget extends Product_Table_Widget {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->widget_cssclass    = 'woocommerce widget_layered_nav_filters';
		$this->widget_description = __( 'Display a list of active filters for your product table.', 'woocommerce-product-table' );
		$this->widget_id          = 'woocommerce_pt_layered_nav_filters';
		$this->widget_name        = __( 'Product Table: Active Filters', 'woocommerce-product-table' );
		$this->settings           = [
			'title' => [
				'type'  => 'text',
				'std'   => __( 'Active filters', 'woocommerce-product-table' ),
				'label' => __( 'Title', 'woocommerce-product-table' ),
			],
		];

		parent::__construct();
	}

	/**
	 * Output widget.
	 *
	 * @param array $args
	 * @param array $instance
	 * @see WP_Widget
	 */
	public function widget( $args, $instance ) {
		if ( ! Util::is_table_on_page() || ! is_singular() ) {
			return;
		}

		$_chosen_attributes = WC_Query::get_layered_nav_chosen_attributes();
		$min_price          = isset( $_GET['min_price'] ) ? wc_clean( $_GET['min_price'] ) : 0;
		$max_price          = isset( $_GET['max_price'] ) ? wc_clean( $_GET['max_price'] ) : 0;
		$rating_filter      = isset( $_GET['rating_filter'] ) ? array_filter( array_map( 'absint', explode( ',', $_GET['rating_filter'] ) ) ) : [];
		$base_link          = remove_query_arg( 'add-to-cart' );

		if ( 0 < count( $_chosen_attributes ) || 0 < $min_price || 0 < $max_price || ! empty( $rating_filter ) ) {

			$this->widget_start( $args, $instance );

			echo '<ul>';

			// Attributes
			if ( ! empty( $_chosen_attributes ) ) {
				foreach ( $_chosen_attributes as $taxonomy => $data ) {
					foreach ( $data['terms'] as $term_slug ) {
						if ( ! $term = get_term_by( 'slug', $term_slug, $taxonomy ) ) {
							continue;
						}

						$filter_name    = 'filter_' . sanitize_title( str_replace( 'pa_', '', $taxonomy ) );
						$current_filter = isset( $_GET[ $filter_name ] ) ? explode( ',', wc_clean( $_GET[ $filter_name ] ) ) : [];
						$current_filter = array_map( 'sanitize_title', $current_filter );
						$new_filter     = array_diff( $current_filter, [ $term_slug ] );

						$link = remove_query_arg( $filter_name, $base_link );

						if ( count( $new_filter ) > 0 ) {
							$link = add_query_arg( $filter_name, implode( ',', $new_filter ), $link );
						}

						$filter_classes = [
							'chosen',
							'chosen-' . sanitize_html_class( str_replace( 'pa_', '', $taxonomy ) ),
							'chosen-' . sanitize_html_class( str_replace( 'pa_', '', $taxonomy ) . '-' . $term_slug )
						];

						echo '<li class="' . esc_attr( implode( ' ', $filter_classes ) ) . '"><a rel="nofollow" aria-label="' . esc_attr__( 'Remove filter', 'woocommerce-product-table' ) . '" href="' . esc_url( $link ) . '">' . esc_html( $term->name ) . '</a></li>';
					}
				}
			}

			if ( $min_price ) {
				$link = remove_query_arg( 'min_price', $base_link );
				/* translators: %s: minimum price */
				echo '<li class="chosen"><a rel="nofollow" aria-label="' . esc_attr__( 'Remove filter', 'woocommerce-product-table' ) . '" href="' . esc_url( $link ) . '">' . sprintf( __( 'Min %s', 'woocommerce-product-table' ), wc_price( $min_price ) ) . '</a></li>';
			}

			if ( $max_price ) {
				$link = remove_query_arg( 'max_price', $base_link );
				/* translators: %s: maximum price */
				echo '<li class="chosen"><a rel="nofollow" aria-label="' . esc_attr__( 'Remove filter', 'woocommerce-product-table' ) . '" href="' . esc_url( $link ) . '">' . sprintf( __( 'Max %s', 'woocommerce-product-table' ), wc_price( $max_price ) ) . '</a></li>';
			}

			if ( ! empty( $rating_filter ) ) {
				foreach ( $rating_filter as $rating ) {
					$link_ratings = implode( ',', array_diff( $rating_filter, [ $rating ] ) );
					$link         = $link_ratings ? add_query_arg( 'rating_filter', $link_ratings ) : remove_query_arg( 'rating_filter', $base_link );

					/* translators: %s: rating */
					echo '<li class="chosen"><a rel="nofollow" aria-label="' . esc_attr__( 'Remove filter', 'woocommerce-product-table' ) . '" href="' . esc_url( $link ) . '">' . sprintf( esc_html__( 'Rated %s out of 5', 'woocommerce-product-table' ), esc_html( $rating ) ) . '</a></li>';
				}
			}

			echo '</ul>';

			$this->widget_end( $args );
		}
	}

}