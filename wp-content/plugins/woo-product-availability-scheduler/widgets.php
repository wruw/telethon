<?php

add_filter('body_class','wpas_widget_add_class');
function wpas_widget_add_class($wpas_classes){

		if ( is_active_widget(false, false, 'Woocommerce_Schedule_Products', true) ) {
			$wpas_classes[] = 'woocommerce woocommerce-page';
		}
		return $wpas_classes;
    }  
	
	
// register Widget Woocommerce_Schedule_Products

add_action( 'widgets_init', function(){
	register_widget( 'Woocommerce_Schedule_Products' );
});


class Woocommerce_Schedule_Products extends WP_Widget {

	public function __construct() {
		
		$widget_ops = array( 
		'classname' => 'woocommerce_schedule_products',
		'description' => 'A Widget to display woocommerce scheduled products list.',
		);
		parent::__construct( 'Woocommerce_Schedule_Products', 'Woocommerce Schedule Products', $widget_ops );
		
	}
	         

	public function widget( $args, $instance ) {
			extract( $args );
			$wpas_title = apply_filters('widget_title', $instance['wpas_title']);
			$wpas_limit = $instance['wpas_limit'];
			?>
			<?php echo $before_widget; ?>
			<?php if ( $wpas_title ){
					echo $before_title . $wpas_title . $after_title;
						$args = array(
							'post_type'      => 'product',
							'posts_per_page'=> $wpas_limit,
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
												'value'   =>  current_time( 'timestamp', 1 ),
												'compare' => '>',		
											),
											
									),
									
								
						);
							$loop = new WP_Query( $args );
							if ( $loop->have_posts() ) {
								?>
							 <ul class ="products columns-1"><?php
								while ( $loop->have_posts() ) : $loop->the_post();
									
									woocommerce_get_template_part( 'content', 'product' );
									
								endwhile;
								?> </ul><?php
							} else {
								echo __( 'No products found' );
							}
							wp_reset_postdata();
							echo $after_widget; 
			}
	}
	
	public function form( $instance ) {
		$wpas_title = esc_attr($instance['wpas_title']);
        $wpas_limit = esc_attr($instance['wpas_limit']);
        ?>
            <p>
				<label for="<?php echo $this->get_field_id('wpas_title'); ?>"><?php _e('Title:'); ?> 
					<input class="wpas_widget_title" id="<?php echo $this->get_field_id('wpas_title'); ?>" name="<?php echo $this->get_field_name('wpas_title'); ?>" type="text" value="<?php echo $wpas_title; ?>" />
				</label>
			</p>
     		<p>
				<label for="<?php echo $this->get_field_id('wpas_limit'); ?>"><?php _e('Limit:'); ?> 
					<input class="wpas_widget_limit" id="<?php echo $this->get_field_id('wpas_limit'); ?>" name="<?php echo $this->get_field_name('wpas_limit'); ?>" type="number" value="<?php echo $wpas_limit; ?>" size="2" />
				</label>
			</p>
		
        
		<?php
	}
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['wpas_title'] = ( ! empty( $new_instance['wpas_title'] ) ) ? strip_tags( $new_instance['wpas_title'] ) : '';
		$instance['wpas_limit'] = ( ! empty ( $new_instance['wpas_limit'] ) ) ? strip_tags($new_instance['wpas_limit'] ) : '';
		return $instance;
	}
}