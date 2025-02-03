<?php
/*
Plugin Name: Woocommerce Product Availability Scheduler
Description: This Plugin you can manage the availability of all your woocommerce products, scheduling it throughout Any Date and Time.
Author: Geek Web Solution
Version: 1.4
WC tested up to: 4.2.2
Author URI: http://geekwebsolution.com/
*/

if(!defined('ABSPATH')) exit;
if(!defined("WPAS_PLUGIN_DIR_PATH"))
	define("WPAS_PLUGIN_DIR_PATH",plugin_dir_path(__FILE__));	
if(!defined("WPAS_PLUGIN_URL"))
	define("WPAS_PLUGIN_URL",plugins_url().'/'.basename(dirname(__FILE__)));	
require_once( WPAS_PLUGIN_DIR_PATH .'functions.php');
require_once( WPAS_PLUGIN_DIR_PATH .'shortcodes.php');
require_once( WPAS_PLUGIN_DIR_PATH .'widgets.php');

add_action('admin_enqueue_scripts','wpas_include_admin_style');
function wpas_include_admin_style(){
	wp_enqueue_style("wpas-admin-style.css",WPAS_PLUGIN_URL."/assets/css/wpas-style.css",''); 
}

add_action( 'wp_enqueue_scripts', 'wpas_include_front_style' );
function wpas_include_front_style() {
   wp_enqueue_style("wpas-front-style.css",WPAS_PLUGIN_URL."/assets/css/wpas-front-style.css",'');  
}
add_action('admin_init', 'wpas_manage_scheduler');
 
register_activation_hook( __FILE__, 'wpas_plugin_active' );
function wpas_plugin_active(){	
	$error='required <b>woocommerce</b> plugin.';	
	if ( !class_exists( 'WooCommerce' ) ) {
	   die('Plugin NOT activated: ' . $error);
	}
}
function wpas_manage_scheduler() {
    if ( is_admin() ) {
		$current_role=wp_get_current_user()->roles[0];
		if($current_role=="administrator")
		{
			include( WPAS_PLUGIN_DIR_PATH .'options.php');
		}       
    }
}
add_action('wpas_start_shedule_sale','wpas_start_schedule_event');
add_action('wpas_end_shedule_sale','wpas_end_schedule_event');

function wpas_start_schedule_event($post_id)
{	
	update_post_meta($post_id,'for_test',1);
	$status=get_post_meta($post_id,'wpas_schedule_sale_status',true);
	if($status){
		update_post_meta($post_id,'wpas_schedule_sale_mode',1);
	}
}
function wpas_end_schedule_event($post_id)
{	
	update_post_meta($post_id,'for_test',0);
	$status=get_post_meta($post_id,'wpas_schedule_sale_status',true);
	if($status){
		update_post_meta($post_id,'wpas_schedule_sale_mode',0);
	}
}

add_filter('woocommerce_is_purchasable', 'wpas_woocommerce_is_purchasable', 10, 2);
function wpas_woocommerce_is_purchasable($is_purchasable, $product) {
	$product_ids= wpas_get_schedule_product_list(0);
	if (in_array($product->id, $product_ids))
	{
		return false;
	}
	else
	{
		return $is_purchasable;
	}
}
add_action( 'woocommerce_after_shop_loop_item', 'wpas_shop_loop_item', 5 );
function wpas_shop_loop_item() {
	global $product;	
	$product_ids= wpas_get_schedule_product_list(0);
	$product_id=$product->id;	
	if (in_array($product_id, $product_ids))
	{ 
		$start_time=get_post_meta($product_id,'wpas_schedule_sale_st_time',true);   
		$countdown=get_post_meta($product_id,'wpas_schedule_sale_countdown',true);   
		$time_diffrent=$start_time-time();
		$s = $time_diffrent;
		$m = floor($s / 60);
		$s = $s % 60;
		$h = floor($m / 60);
		$m = $m % 60;
		$d = floor($h / 24);
		$h = $h % 24;
		if ($time_diffrent > 0 && !empty($countdown))
		{
			echo '<div id="wpas_countdown_'.$product_id.'" data-product="'.$product_id.'" data-start="'.$start_time.'" class="wpas_countdown wpas_coundown_shop">
			<span>Product will be available in</span>
			<ul><li><div><span class="wpas_count_digit">'.$d.'</span><span class="wpas_count_lable">Days</span></div></li><li><div><span class="wpas_count_digit">'.$h.'</span><span class="wpas_count_lable">Hours</span></div></li><li><div><span class="wpas_count_digit">'.$m.'</span><span class="wpas_count_lable">Min</span></div></li><li><div><span class="wpas_count_digit">'.$s.'</span><span class="wpas_count_lable">Sec</span></div></li></ul></div>';
		}
	}
}
add_action( 'woocommerce_single_product_summary', 'wpas_single_page_summary', 30 );
 
function wpas_single_page_summary() {
	global $product;	
	$product_ids= wpas_get_schedule_product_list(0);
	$product_id=$product->id;	
	if (in_array($product_id, $product_ids))
	{ 
		  $start_time=get_post_meta($product_id,'wpas_schedule_sale_st_time',true);   
		  $countdown=get_post_meta($product_id,'wpas_schedule_sale_countdown',true);   
		  $time_diffrent=$start_time-time();
		  $s = $time_diffrent;
		  $m = floor($s / 60);
		  $s = $s % 60;
		  $h = floor($m / 60);
		  $m = $m % 60;
		  $d = floor($h / 24);
		  $h = $h % 24;
	if ($time_diffrent > 0 && !empty($countdown))
		{
			 echo '
			<div id="wpas_countdown_'.$product_id.'" data-product="'.$product_id.'" data-start="'.$start_time.'" class="wpas_countdown wpas_coundown_single">
				<span>Product will be available in</span>
				<ul>
					<li>
						<div>
							<span class="wpas_count_digit">'.$d.'</span>
							<span class="wpas_count_lable">Days</span>
							<div class="border-over"></div>
							<div class="slice">
								<div class="bar"></div>
							</div>
						</div>
					</li>
					<li>
						<div>
							<span class="wpas_count_digit">'.$h.'</span>
							<span class="wpas_count_lable">Hours</span>
							<div class="border-over"></div>
						</div>
					</li>
					<li>
						<div>
							<span class="wpas_count_digit">'.$m.'</span>
							<span class="wpas_count_lable">Min</span>
							<div class="border-over"></div>
						</div>
					</li>
					<li>
						<div>
							<span class="wpas_count_digit">'.$s.'</span>
							<span class="wpas_count_lable">Sec</span>
							<div class="border-over"></div>
						</div>
					</li>
				</ul>
			</div>';
		}
	}
}
add_action('admin_footer', 'wpas_schedule_sale_admin_footer_function');
function wpas_schedule_sale_admin_footer_function() {
	if(get_post_type($_REQUEST['post_type'])=='product'){ ?>
	<script>
		jQuery("#wpas_st_date").datepicker({
			dateFormat: 'yy-m-d'
		});
		jQuery("#wpas_end_date").datepicker({
			dateFormat: 'yy-m-d'
		});
	</script>
	<?php
	}
}
add_action('wp_footer', 'wpas_schedule_sale_front_footer_function');
function wpas_schedule_sale_front_footer_function() {
	?>
		<script>
			jQuery(".wpas_countdown").each(function() {
				var start_time = jQuery(this).attr('data-start');
				var product_id = jQuery(this).attr('data-product');
				var inter = setInterval(function() {
					var today = new Date();
					var str = today.toGMTString();
					var now_timestamp = Date.parse(str) / 1000;
					var remain_time = start_time - now_timestamp;
					if (remain_time >= 0) {
						jQuery('#wpas_countdown_' + product_id + ' ul').html(convertMS(remain_time + '000'));
					}else{
						clearInterval(inter);
						document.location.reload(true);
					}
				}, 1000);
			});
			function convertMS(ms) {
				var d, h, m, s;
				s = Math.floor(ms / 1000);
				m = Math.floor(s / 60);
				s = s % 60;
				h = Math.floor(m / 60);
				m = m % 60;
				d = Math.floor(h / 24);
				h = h % 24;
				var html = '<li><div><span class="wpas_count_digit">' + d + '</span><span class="wpas_count_lable">Days</span></div></li><li><div><span class="wpas_count_digit">' + h + '</span><span class="wpas_count_lable">Hours</span></div></li><li><div><span class="wpas_count_digit">' + m + '</span><span class="wpas_count_lable">Min</span></div></li><li><div><span class="wpas_count_digit">' + s + '</span><span class="wpas_count_lable">Sec</span></div></li>'
				return html;
			};
		</script>
		<?php }