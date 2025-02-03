<?php
/**
 * The Template for displaying radio button field.
 *
 * @version 3.0.0
 * @package woocommerce-product-addons
 *
 * phpcs:disable WordPress.Security.NonceVerification.Missing
 */

$loop           = 0;
$field_name     = ! empty( $addon['field_name'] ) ? $addon['field_name'] : '';
$addon_key      = 'addon-' . sanitize_title( $field_name );
$required       = ! empty( $addon['required'] ) ? $addon['required'] : '';
$current_value  = isset( $_POST[ $addon_key ], $_POST[ $addon_key ][0] ) ? wc_clean( wp_unslash( $_POST[ $addon_key ][0] ) ) : '';
$option_id_none = $field_name . '-none';
?>

<?php if ( empty( $required ) ) { ?>
	<p class="form-row form-row-wide wc-pao-addon-wrap wc-pao-addon-<?php echo esc_attr( sanitize_title( $field_name ) ); ?>">
		<input type="radio" id="<?php echo esc_attr( $option_id_none ); ?>" class="wc-pao-addon-field wc-pao-addon-radio" value="" name="addon-<?php echo esc_attr( sanitize_title( $field_name ) ); ?>[]"/>
		<label for="<?php echo esc_attr( $option_id_none ); ?>">
			<?php esc_html_e( 'None', 'woocommerce-product-table' ); ?>
		</label>
	</p>
<?php } ?>

<?php
foreach ( $addon['options'] as $i => $option ) {
	$loop++;

	$price        = ! empty( $option['price'] ) ? $option['price'] : '';
	$price_prefix = 0 < $price ? '+' : '';
	$price_type   = ! empty( $option['price_type'] ) ? $option['price_type'] : '';
	$price_raw    = apply_filters( 'woocommerce_product_addons_option_price_raw', $price, $option );
	$label        = ( '0' === $option['label'] ) || ! empty( $option['label'] ) ? $option['label'] : '';

	if ( 'percentage_based' === $price_type ) {
		$price_for_display = apply_filters( 'woocommerce_addons_add_price_to_name', true ) ? apply_filters(
			'woocommerce_product_addons_option_price',
			$price_raw ? '(' . $price_prefix . $price_raw . '%)' : '',
			$option,
			$i,
			'radiobutton'
		) : '';
	} else {
		$price_for_display = apply_filters( 'woocommerce_addons_add_price_to_name', true ) ? apply_filters(
			'woocommerce_product_addons_option_price',
			$price_raw ? '(' . $price_prefix . wc_price( WC_Product_Addons_Helper::get_product_addon_price_for_display( $price_raw ) ) . ')' : '',
			$option,
			$i,
			'radiobutton'
		) : '';
	}

	$price_display = WC_Product_Addons_Helper::get_product_addon_price_for_display( $price_raw );

	if ( 'percentage_based' === $price_type ) {
		$price_display = $price_raw;
	}

	$option_id = sanitize_title( $field_name ) . '-' . $i;
	?>
	<p class="form-row form-row-wide wc-pao-addon-wrap wc-pao-addon-<?php echo esc_attr( sanitize_title( $field_name ) ); ?>">
		<input
				type="radio"
				id="<?php echo esc_attr( $option_id ); ?>"
				class="wc-pao-addon-field wc-pao-addon-radio"
				name="addon-<?php echo esc_attr( sanitize_title( $field_name ) ); ?>[]"
				data-raw-price="<?php echo esc_attr( $price_raw ); ?>"
				data-price="<?php echo esc_attr( $price_display ); ?>"
				data-price-type="<?php echo esc_attr( $price_type ); ?>"
				value="<?php echo esc_attr( sanitize_title( $label ) ); ?>"
			<?php checked( $current_value, 1 ); ?>
			<?php echo WC_Product_Addons_Helper::is_addon_required( $addon ) ? 'required' : ''; ?>
				data-label="<?php echo esc_attr( wptexturize( $label ) ); ?>"
		/>
		<label for="<?php echo esc_attr( $option_id ); ?>">
			<?php echo wp_kses_post( wptexturize( $label . ' ' . $price_for_display ) ); ?>
		</label>
	</p>
	<?php
}
