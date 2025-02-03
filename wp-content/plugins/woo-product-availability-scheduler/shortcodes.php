<?php 
add_filter( 'body_class', 'wpas_shortcode_add_class' );
function wpas_shortcode_add_class( $wpas_classes ) {

    global $post;

    if( isset($post->post_content) && has_shortcode( $post->post_content, 'wpas_schedule_product' ) ) {
        $wpas_classes[] = 'woocommerce woocommerce-page';
    }
	
    return $wpas_classes;
}


function wpas_schedule_product_availability_shortcode($atts)
{
	global $post; 
	$time=time();
	extract(shortcode_atts(array(
      'limit' => '',
	  'columns'=>''), $atts));
	  $columns=$atts['columns'];
	  if($columns=='')
	  {
		  $columns=3;
	  }
			$args = array(
					'post_type'      => 'product',
					'posts_per_page'=> $atts['limit'],
					'meta_query' => 
							array(
									'relation' => 'AND',
									array(
										'key'     => 'wpas_schedule_sale_status',
										'value'   => 1,
										'compare' => '=',
									),
									array(
										'key'     => 'wpas_schedule_sale_st_time',
										'value'   => current_time( 'timestamp', 1 ),
										'compare' => '>',		
									),
									
							),
							
						
				);
		
		$loop = new WP_Query( $args );
		if ( $loop->have_posts() ) {
			?>
		 <ul class ="products columns-<?php echo $columns; ?>"><?php
			while ( $loop->have_posts() ) : $loop->the_post();
				
				woocommerce_get_template_part( 'content', 'product' );
				
			endwhile;
			?> </ul><?php
		} else {
			echo __( 'No products found' );
		}
		wp_reset_postdata();
	
}
add_shortcode('wpas_schedule_product','wpas_schedule_product_availability_shortcode');
?>