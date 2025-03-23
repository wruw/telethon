/**
 * Product Open Pricing for WooCommerce Pro - admin scripts
 *
 * @since   1.7.0
 * @author  WP Wham
 */

(function( $ ){
	
	$( document ).ready( function(){
		
		/*
		 * settings page - don't trigger WC "are you sure?" warning when updating license key
		 */
		$( '#wpwham_product_open_pricing_license' ).on( 'change', function(){
			setTimeout( function(){
				window.onbeforeunload = "";
			}, 1 );
		});
		
	});
	
})( jQuery );
