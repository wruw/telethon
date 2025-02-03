<?php
if(!defined('ABSPATH')) exit;

add_filter( 'woocommerce_product_data_tabs', 'wpas_schedule_sale_data_tab' ); 
function wpas_schedule_sale_data_tab( $product_data_tabs ) 
{ 
	$product_data_tabs['wpas-schedule-sale-tab'] = array( 
				'label' => __( 'Availability Scheduler', 'woocommerce' ), 
				'target' => 'wpas_product_data', 
				'class' => array( 'show_if_simple' ), );
				return $product_data_tabs; 
} 

add_action('woocommerce_product_data_panels', 'wpas_schedule_sale_data_tab_content');

function wpas_schedule_sale_data_tab_content() {
	
    global $post;
	$now=time();
	$post_id=$post->ID;
	
	$status=get_post_meta($post_id,'wpas_schedule_sale_status',true);   
	$start_time=get_post_meta($post_id,'wpas_schedule_sale_st_time',true);   
	$end_time=get_post_meta($post_id,'wpas_schedule_sale_end_time',true);   
	$mode=get_post_meta($post_id,'wpas_schedule_sale_mode',true);   
	$countdown=get_post_meta($post_id,'wpas_schedule_sale_countdown',true);   
	if(!empty($start_time))
	{
		$start_date=date('Y-m-d', $start_time);
		$st_mm=date('m', $start_time);
		$st_dd=date('d', $start_time);
		$st_hh=date('H', $start_time);
		$st_mn=date('i', $start_time);
	}
	if(isset($end_time) &!empty($end_time))
	{
		$end_date=date('Y-m-d', $end_time);
		$end_mm=date('m', $end_time);
		$end_dd=date('d', $end_time);
		$end_hh=date('H', $end_time);
		$end_mn=date('i', $end_time);
	}
	?>
   <div id ='wpas_product_data' class ='panel woocommerce_options_panel' > 
    <div class = 'options_group' >
	<p class='form-field wpas_select_status'>
		<label for='wpas_select_status'><?php _e( 'Status', 'woocommerce' ); ?></label>
		<select name='wpas_select_status' class='wc-enhanced-select' id="wpas_select_status">
			<option  <?php if($status==0){ echo "selected"; } ?> value='0'>Disable</option>
			<option  <?php if($status==1){ echo "selected"; } ?> value='1'>Enable</option>
		</select>	
    </p>
	<p class="form-field wpas_select_start_time">
		<label for='_select_start_time'><?php _e( 'Start Time', 'woocommerce' ); ?></label> 
		<input type="text" id="wpas_st_date" class="wpas_st_date" name="wpas_st_date" value="<?php echo $start_date; ?>" placeholder="From… YYYY-MM-DD" maxlength="10" autocomplete="off">
			<span class="wpas_at"> @</span>
			<span class="screen-reader-text">Hour</span>
			<input type="text" id="wpas_st_hh" class="wpas_st_time" name="wpas_st_hh" placeholder="HH" value="<?php echo $st_hh; ?>" size="2" maxlength="2" autocomplete="off"><span class="wpas_colon">:</span>
			<span class="screen-reader-text">Minute</span>
			<input type="text" id="wpas_st_mn" class="wpas_st_time" name="wpas_st_mn" placeholder="MM" value="<?php echo $st_mn; ?>" size="2" maxlength="2" autocomplete="off"> <span>GMT</span>
			
	</p>
	<p class="form-field wpas_select_end_time">
		<label for='_select_end_time'><?php _e( 'End Time', 'woocommerce' ); ?></label>
		</span><span class="screen-reader-text">Year</span>
				<input type="text" id="wpas_end_date" class="wpas_end_date" name="wpas_end_date" value="<?php echo $end_date; ?>" placeholder="From… YYYY-MM-DD" maxlength="10" autocomplete="off"><span class="wpas_at"> @</span><span class="screen-reader-text">Hour</span>
				<input type="text" id="wpas_end_hh" name="wpas_end_hh" class="wpas_st_time" placeholder="HH" value="<?php echo $end_hh; ?>" size="2" maxlength="2" autocomplete="off"><span class="wpas_colon">:</span><span class="screen-reader-text">Minute</span>
				<input type="text" id="wpas_end_mn" name="wpas_end_mn" class="wpas_st_time" placeholder="MM" value="<?php echo $end_mn; ?>" size="2" maxlength="2" autocomplete="off"> GMT
			
	</p>
	<p class="form-field wpas_countdown">
		<label for='_select_end_time'><?php _e( 'CountDown', 'woocommerce' ); ?></label>
		<select name='countdown' class='wc-enhanced-select' id="countdown">
			<option  <?php if($countdown==0){ echo "selected"; } ?> value='0'>Disable</option>
			<option  <?php if($countdown==1){ echo "selected"; } ?> value='1'>Enable</option>
		</select>	
			
	</p>
	<p class="form-field wpas_note">Note: Start time and End time will be on GMT, Current GMT time is: <?php echo date("Y-m-d @ H:i",$now); ?></p>
  </div>

    </div><?php
}

/** Hook callback function to save custom fields information */
function wpas_schedule_sale_save_data_tab($post_id) {
		global $post;
		$wpas_error=false;
		$wpas_st_hh=00;
		$wpas_st_mn=00;
		$wpas_end_hh=00;
		$wpas_end_mn=00;
		$wpas_status =sanitize_text_field($_POST['wpas_select_status']);
		$countdown =sanitize_text_field($_POST['countdown']);
		$wpas_st_date=sanitize_text_field($_POST['wpas_st_date']);
		if(!empty($_POST['wpas_st_hh'])) $wpas_st_hh=sanitize_text_field($_POST['wpas_st_hh']);
		if(!empty($_POST['wpas_st_mn'])) $wpas_st_mn=sanitize_text_field($_POST['wpas_st_mn']);
		$wpas_end_date=sanitize_text_field($_POST['wpas_end_date']);
		if(!empty($_POST['wpas_end_hh'])) $wpas_end_hh=sanitize_text_field($_POST['wpas_end_hh']);
		if(!empty($_POST['wpas_end_mn'])) $wpas_end_mn=sanitize_text_field($_POST['wpas_end_mn']);
		$wpas_start_schedule_hook="wpas_start_shedule_sale";
		$wpas_end_schedule_hook="wpas_end_shedule_sale";	
		//Y-m-d H:i:s
		$wpas_st_time=strtotime($wpas_st_date." ".$wpas_st_hh.":".$wpas_st_mn.":00"); 
		//echo "start time".$wpas_st_time;
		$wpas_end_time=strtotime($wpas_end_date." ".$wpas_end_hh.":".$wpas_end_mn.":00");
		if($wpas_status==1)
		{		wp_clear_scheduled_hook( $wpas_start_schedule_hook, array($post->ID));
				wp_clear_scheduled_hook( $wpas_end_schedule_hook, array($post->ID) );	
				wp_schedule_single_event($wpas_st_time, $wpas_start_schedule_hook,array($post->ID));
				wp_schedule_single_event($wpas_end_time, $wpas_end_schedule_hook,array($post->ID));
		}
		// Save Data
		
		if (!empty($wpas_st_date) && !empty($wpas_end_date)) {
			
			update_post_meta($post_id,'wpas_schedule_sale_status',$wpas_status);   
			update_post_meta($post_id,'wpas_schedule_sale_st_time',$wpas_st_time);   
			update_post_meta($post_id,'wpas_schedule_sale_end_time',$wpas_end_time);   
			update_post_meta($post_id,'wpas_schedule_sale_countdown',$countdown);   
			
			if($wpas_st_time > time())
			{
			update_post_meta($post_id,'wpas_schedule_sale_mode',0);   	
			}
		}
	
} 

add_action( 'woocommerce_process_product_meta', 'wpas_schedule_sale_save_data_tab');