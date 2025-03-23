<?php

function acf_load_program_choices($field){
	$field['choices'] = array();
	$json =  file_get_contents('https://api.wruw.org/schedule');
	$shows = json_decode($json, true);
	usort($shows, function($item1, $item2){
		if (strtoslug($item1['title']) == strtoslug($item2['title'])) return 0;
		return strtoslug($item1['title']) < strtoslug($item2['title']) ? -1 : 1;
		});
	$field['choices'][''] = 'None';
	$field['choices']['none']= 'Disable';
	foreach ($shows as $show){
		$field['choices'][strtoslug($show['title'])] = $show['title'];
	}
	return $field;
}

add_filter('acf/load_field/name=program', 'acf_load_program_choices');

function strtoslug($str)
{
    if($str){
        $str = preg_replace('/^\s+|\s+$/', '', $str); // trim
        $str = strtolower($str);

        // remove accents, swap ñ for n, etc
        $from = "àáäâèéëêìíïîòóöôùúüûñç·/_,:;";
        $to   = "aaaaeeeeiiiioooouuuunc------";

        for ($i=0; $i<strlen($from) ; $i++)
        {
            $str = str_replace(substr($from,$i,1),substr($to,$i,1),$str);
        }

        $str = preg_replace('/[^a-z0-9 -]/', '', $str); // remove invalid chars
        $str = preg_replace('/\s+/', '-', $str); // collapse whitespace and replace by -
        $str = preg_replace('/-+/', '-',$str); // collapse dashes
    }
	return $str;
}

add_filter( 'woocommerce_checkout_fields' , 'custom_checkout_fields', 30, 1 );
function custom_checkout_fields ( $fields ) {
	$items = WC()->cart->get_cart();
	$ask_show = false;
	foreach($items as $item){
		$overrideshow = get_field("program",$item['product_id']);
		if(!$overrideshow){
			$ask_show = true;
		}
	}
	if($ask_show){
		$domain = 'woocommerce';
		$shownames = array('none' => 'None');
		$json =  file_get_contents('https://api.wruw.org/schedule');
		$shows = json_decode($json, true);
		usort($shows, function($item1, $item2){
			if (strtoslug($item1['title']) == strtoslug($item2['title'])) return 0;
			return strtoslug($item1['title']) < strtoslug($item2['title']) ? -1 : 1;
			});
		foreach ($shows as $show){
			$shownames += array(strtoslug($show['title'])=>__($show['title'],$domain));
		}
	    $fields['billing']['multi_show'] = array(
	        'label'        => __('Show (use ctrl to select more than one)', 'woocommerce'),
	        'required'     => false,
	        'class'        => array('form-row-wide'),
	        'clear'        => true,
	        'autocomplete' => false,
	        'type'         => 'select',
	        'options'      => $shownames
	    );
	}
	$fields['billing']['can_we_say_your_name'] = array(
		'label'        => __('Can we say your name on the air?', 'woocommerce'),
		'required'     => true,
		'class'        => array('form-row-wide'),
		'clear'        => true,
		'autocomplete' => false,
		'type'         => 'select',
		'options'      => array(
			'yes'  => __('Yes', $domain),
			'no'  => __('No', $domain),
		),
	);
	if($ask_show){
	    ?>

	    <input type="hidden"  name="show" id="show" value="0" multiple>
	    <script type='text/javascript'>
	        jQuery(function($){
	            var a = 'select[name="multi_show"]',
	                b = 'input[name="show"]';
	            $(a).attr('multiple', 'multiple');
				$(a).change( function(){
				   $(b).val($(this).val());
			   });
			   //Enable this to automatically get the current show. Only for week of telethon.
			   saved_show = document.cookie.split("; ").find((row) => row.startsWith("program="))?.split("=")[1]
			   if(saved_show){
					showinput = document.querySelector('select[name="multi_show"]');
					for(o in showinput.options){
						if(showinput.options[o].value == saved_show){
							showinput.value = showinput.options[o].value;
							document.querySelector('input[name="show"]').value = showinput.options[o].value;
						}
					}
			   }else{
					$.ajax({
						cache: false,
						url:"https://api.wruw.org/currentshow",
						dataType: 'json',
						success: function(result) {
						if (result) {
							showinput = document.querySelector('select[name="multi_show"]');
							for(o in showinput.options){
								if(showinput.options[o].text == result['title']){
									showinput.value = showinput.options[o].value;
									document.querySelector('input[name="show"]').value = showinput.options[o].value;
								}
							}
						}
					}
				});
				}
	        });

	    </script>
	    <?php
	}
    return $fields;
}

add_action( 'woocommerce_checkout_update_order_meta', 'hidden_checkout_field_update_order_meta', 30, 1 );
function hidden_checkout_field_update_order_meta ( $order_id ) {
	global $wpdb;
    if( isset( $_POST['show'] ) ){
		$items = WC()->cart->get_cart();
		$total_to_split = 0;
		$showslugs = [];
		$shows = explode(',',$_POST['show']);
		foreach($shows as $show){
			if($show != 'none'){
				array_push($showslugs,$show);
			}
		}
		if (count($showslugs) > 0) {
			update_post_meta($order_id, '_show', esc_attr( implode(',',$showslugs )));
		}
	}
	update_post_meta($order_id, '_can_we_say_your_name', esc_attr( $_POST['can_we_say_your_name']));
}

add_action( 'woocommerce_admin_order_data_after_billing_address', 'my_custom_checkout_field_display_admin_order_meta', 10, 1 );

function my_custom_checkout_field_display_admin_order_meta($order){
	echo '<p><strong>'.__('Show').':</strong> ' . get_post_meta( $order->id, '_show', true ) . '</p>';
    echo '<p><strong>'.__('Can we say your name on the air?').':</strong> ' . get_post_meta( $order->id, '_can_we_say_your_name', true ) . '</p>';
}

// register a custom post status 'awaiting-delivery' for Orders
add_action( 'init', 'register_custom_post_status', 20 );
function register_custom_post_status() {
    register_post_status( 'wc-awaiting-delivery', array(
        'label'                     => _x( 'Shipped', 'Order status', 'woocommerce' ),
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop( 'Shipped <span class="count">(%s)</span>', 'Shipped <span class="count">(%s)</span>', 'woocommerce' )
    ) );
}

add_action('add_meta_boxes', 'add_custom_order_meta_box');
function add_custom_order_meta_box() {
    add_meta_box(
        'custom_order_fields',
        __('Custom Order Fields', 'woocommerce'),
        'display_custom_order_fields',
        'shop_order',
        'normal',
        'high'
    );
}

// Display custom fields in the admin order edit screen
function display_custom_order_fields($post) {
    // Security check
    wp_nonce_field('custom_order_fields', '_custom_order_nonce');

    // Get the value of your custom field if it is already set
    $multi_show = get_post_meta($post->ID, '_show', true);
    $can_we_say_your_name = get_post_meta($post->ID, '_can_we_say_your_name', true);

    echo '<div class="options_group">';

    // Display multi-show field
	$domain = 'woocommerce';
	$shownames = array('none' => 'None');
	$json = file_get_contents('https://api.wruw.org/schedule');
	$shows = json_decode($json, true);
	usort($shows, function ($item1, $item2) {
		if (strtoslug($item1['title']) == strtoslug($item2['title'])) return 0;
		return strtoslug($item1['title']) < strtoslug($item2['title']) ? -1 : 1;
	});
	foreach ($shows as $show) {
		$shownames += array(strtoslug($show['title']) => __($show['title'], $domain));
	}

	woocommerce_wp_select(array(
		'id'          => '_multi_show',
		'label'       => __('Show', 'woocommerce'),
		'name'        => '_multi_show[]',
		'description' => '',
		'desc_tip'    => true,
		'class'       => 'wc-enhanced-select',
		'options'     => $shownames,
		'value'       => explode(',', $multi_show),
		'custom_attributes' => array('multiple' => 'multiple')
	));

    // Display can we say your name field
    woocommerce_wp_select(array(
        'id'          => '_can_we_say_your_name',
        'label'       => __('Can we say your name on the air?', 'woocommerce'),
        'description' => '',
        'desc_tip'    => true,
        'class'       => 'wc-enhanced-select',
        'options'     => array(
            'yes'  => __('Yes', $domain),
            'no'   => __('No', $domain),
        ),
        'value'       => $can_we_say_your_name
    ));

    echo '</div>';
}

// Save custom fields in the admin order edit screen
add_action('woocommerce_process_shop_order_meta', 'save_custom_order_fields');
function save_custom_order_fields($post_id) {
    // Check if our nonce is set.
    if (!isset($_POST['_custom_order_nonce'])) {
        return $post_id;
    }
    $nonce = $_POST['_custom_order_nonce'];
    // Verify that the nonce is valid.
    if (!wp_verify_nonce($nonce, 'custom_order_fields')) {
        return $post_id;
    }
    // Check the user's permissions.
    if (!current_user_can('edit_shop_orders', $post_id)) {
        return $post_id;
    }

    // Save custom fields
    if (isset($_POST['_multi_show'])) {
		update_post_meta($post_id, '_show', implode(',', array_map('esc_attr', explode(',', $_POST['_multi_show']))));
    }
    if (isset($_POST['_can_we_say_your_name'])) {
        update_post_meta($post_id, '_can_we_say_your_name', esc_attr($_POST['_can_we_say_your_name']));
    }
}

// Adding custom status 'awaiting-delivery' to order edit pages dropdown
add_filter( 'wc_order_statuses', 'custom_wc_order_statuses' );
function custom_wc_order_statuses( $order_statuses ) {
    $order_statuses['wc-awaiting-delivery'] = _x( 'Shipped', 'Order status', 'woocommerce' );
    return $order_statuses;
}

// Adding custom status 'awaiting-delivery' to admin order list bulk dropdown
add_filter( 'bulk_actions-edit-shop_order', 'custom_dropdown_bulk_actions_shop_order', 20, 1 );
function custom_dropdown_bulk_actions_shop_order( $actions ) {
    $actions['mark_awaiting-delivery'] = __( 'Shipped', 'woocommerce' );
    return $actions;
}

// Adding action for 'awaiting-delivery'
add_filter( 'woocommerce_email_actions', 'custom_email_actions', 20, 1 );
function custom_email_actions( $actions ) {
    $actions[] = 'woocommerce_order_status_wc-awaiting-delivery';
    return $actions;
}

add_action( 'woocommerce_order_status_wc-awaiting-delivery', array( WC(), 'send_transactional_email' ), 10, 1 );

// Sending an email notification when order get 'awaiting-delivery' status
add_action('woocommerce_order_status_awaiting-delivery', 'awaiting_delivery_order_status_email_notification', 20, 2);
function awaiting_delivery_order_status_email_notification( $order_id, $order ) {
    // HERE below your settings
    $heading   = __('Shipped','woocommerce');
    $subject   = '[{site_title}] Your Order Has Shipped ({order_number}) - {order_date}';

        // The email notification type
        $email_key   = 'WC_Email_Customer_Processing_Order';

        // Get specific WC_emails object
        $email_obj = WC()->mailer()->get_emails()[$email_key];

        // Sending the customized email
        $email_obj->trigger( $order_id );
}

// Customize email heading for this custom status email notification
add_filter( 'woocommerce_email_heading_customer_processing_order', 'email_heading_customer_awaiting_delivery_order', 10, 2 );
function email_heading_customer_awaiting_delivery_order( $heading, $order ){
    if( $order->has_status( 'awaiting-delivery' ) ) {
        $email_key   = 'WC_Email_Customer_Processing_Order'; // The email notification type
        $email_obj   = WC()->mailer()->get_emails()[$email_key]; // Get specific WC_emails object
        $heading_txt = __('Your order has Shipped','woocommerce'); // New heading text

        return $email_obj->format_string( $heading_txt );
    }
    return $heading;
}

// Customize email subject for this custom status email notification
add_filter( 'woocommerce_email_subject_customer_processing_order', 'email_subject_customer_awaiting_delivery_order', 10, 2 );
function email_subject_customer_awaiting_delivery_order( $subject, $order ){
    if( $order->has_status( 'awaiting-delivery' ) ) {
        $email_key   = 'WC_Email_Customer_Processing_Order'; // The email notification type
        $email_obj   = WC()->mailer()->get_emails()[$email_key]; // Get specific WC_emails object
        $subject_txt = sprintf( __('[%s] Your Order Has Shipped (%s) - %s', 'woocommerce'), '{site_title}', '{order_number}', '{order_date}' ); // New subject text

        return $email_obj->format_string( $subject_txt );
    }
    return $subject;
}

// register a custom post status 'awaiting-delivery' for Orders
add_action( 'init', 'register_custom_post_status_2', 20 );
function register_custom_post_status_2() {
    register_post_status( 'wc-fufilled', array(
        'label'                     => _x( 'Fufilled', 'Order status', 'woocommerce' ),
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop( 'Fufilled <span class="count">(%s)</span>', 'Fufilled <span class="count">(%s)</span>', 'woocommerce' )
    ) );
}

// Adding custom status 'awaiting-delivery' to order edit pages dropdown
add_filter( 'wc_order_statuses', 'custom_wc_order_statuses_2' );
function custom_wc_order_statuses_2( $order_statuses ) {
    $order_statuses['wc-fufilled'] = _x( 'Fufilled', 'Order status', 'woocommerce' );
    return $order_statuses;
}

// Adding custom status 'awaiting-delivery' to admin order list bulk dropdown
add_filter( 'bulk_actions-edit-shop_order', 'custom_dropdown_bulk_actions_shop_order_2', 20, 1 );
function custom_dropdown_bulk_actions_shop_order_2( $actions ) {
    $actions['mark_fufilled'] = __( 'Fufilled', 'woocommerce' );
    return $actions;
}

function wpb_hook_javascript() {
    ?>
        <script>
			jQuery(document).ready(function(){
			jQuery('.menu-toggle').click(function(){
				if(jQuery('.handheld-navigation').css('max-height') == '0px'){
					jQuery('.handheld-navigation').css('max-height', 'inherit');
				}else{
					jQuery('.handheld-navigation').css('max-height', '0px');
				}
			});
		});
        </script>
    <?php
}
add_action('wp_head', 'wpb_hook_javascript');
?>
