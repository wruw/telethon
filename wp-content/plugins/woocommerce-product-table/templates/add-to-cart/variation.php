<?php
global $product;

if ( ! $product->is_purchasable() ) {
	return;
}

echo wc_get_stock_html( $product );

if ( ! $product->is_in_stock() ) {
	return;
}

do_action( 'woocommerce_before_add_to_cart_form' );
?>
	<form class="cart" method="post" enctype='multipart/form-data'>
		<?php
		do_action( 'woocommerce_before_add_to_cart_button' );
		do_action( 'woocommerce_before_add_to_cart_quantity' );

		woocommerce_quantity_input(
			[
				'min_value'   => apply_filters( 'woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product ),
				'max_value'   => apply_filters( 'woocommerce_quantity_input_max', $product->get_max_purchase_quantity(), $product ),
				'input_value' => isset( $_POST['quantity'] ) ? wc_stock_amount( $_POST['quantity'] ) : $product->get_min_purchase_quantity()
			]
		);

		do_action( 'woocommerce_after_add_to_cart_quantity' );
		?>

		<button type="submit" name="add-to-cart" value="<?php echo absint( $product->get_parent_id() ); ?>"
				class="single_add_to_cart_button button alt"><?php echo esc_html( $product->single_add_to_cart_text() ); ?></button>

		<?php do_action( 'woocommerce_after_add_to_cart_button' ); ?>

		<input type="hidden" name="variation_id" value="<?php echo absint( $product->get_id() ); ?>"/>

		<div class="variations hidden">
			<?php foreach ( $product->get_variation_attributes() as $attribute => $value ) : ?>
				<input type="hidden" name="<?php echo esc_attr( sanitize_title( $attribute ) ); ?>" value="<?php echo esc_attr( $value ); ?>"/>
			<?php endforeach; ?>
		</div>
	</form>

<?php
do_action( 'woocommerce_after_add_to_cart_form' );