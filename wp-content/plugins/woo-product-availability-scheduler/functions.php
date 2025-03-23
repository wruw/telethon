<?php 
if(!defined('ABSPATH')) exit;

if(!defined("WPAS_PLUGIN_DIR_PATH"))
	
	define("WPAS_PLUGIN_DIR_PATH",plugin_dir_path(__FILE__));
	
if(!defined("WPAS_PLUGIN_URL"))
	
	define("WPAS_PLUGIN_URL",plugins_url().'/'.basename(dirname(__FILE__)));
	

function wpas_get_post_meta_options()
{
	global $post_id;
	return get_post_meta( $post_id, 'wpas_schedule_sale_meta',true);
}
	
// Success message
function  success_option_msg_wpas($msg)
{
	
	return ' <div class="notice notice-success wpas-success-msg is-dismissible"><p>'. $msg . '</p></div>';		
	
}

// Error message
function  failure_option_msg_wpas($msg)
{

	return '<div class="notice notice-error wpas-error-msg is-dismissible"><p>' . $msg . '</p></div>';		
	
}

function wpas_get_schedule_product_list($status){
	$product_ids=array();
	$args = array(
	'posts_per_page' => -1,
    'post_type'  => 'product',
    'meta_query' => array(
        'relation' => 'AND',
        array(
            'key'     => 'wpas_schedule_sale_status',
            'value'   => 1,
            'compare' => '=',
        ),
        array(
            'key'     => 'wpas_schedule_sale_mode',
            'value'   => $status,
			'compare' => '=',
		),
		)
	);
	$wpas_products= get_posts( $args );	
	foreach ($wpas_products as $wpas_product)
	{ 	
		array_push($product_ids,$wpas_product->ID);		
	}	
	return $product_ids;
}

?>