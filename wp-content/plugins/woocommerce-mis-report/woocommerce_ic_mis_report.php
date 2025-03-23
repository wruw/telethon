<?php
/**
Plugin Name: WooCommerce Sales MIS Report 
Plugin URI: http://plugins.infosofttech.com
Description: Woocommerce Sales Reporter shows you all key sales information in one main Dashboard in very intuitive, easy to understand format which gives a quick overview of your business and helps make smart decisions
Version: 4.0.3
Author: Infosoft Consultant 
Author URI: http://plugins.infosofttech.com
Plugin URI: https://wordpress.org/plugins/woocommerce-mis-report/
License: A  "Slug" license name e.g. GPL2

Tested Wordpress Version: 6.1.x
WC requires at least: 3.5.x
WC tested up to: 7.4.x
Requires at least: 5.7
Requires PHP: 5.6

Text Domain: icwoocommercemis
Domain Path: /languages/

Last Update Date: March 15, 2019
Last Update Date: May 12, 2022
Last Update Date: February 02, 2023
**/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
if ( ! class_exists( 'WC_IS_MIS_Report' ) ) {
	require_once("ic-woocommerce-mis-report-core.php");
	class WC_IS_MIS_Report extends WC_IS_MIS_Report_Core{
		
		public $plugin_name = "";
		
		public $constants = array();
		
		public function __construct() {
			global $options;
			$this->plugin_name = __("WooCommerce Advance Sales Report Plugin",'icwoocommercemis');
			
			$this->constants['post_order_status_found']	= 1;//1 mean woocommerce status replaced with post status
			$this->constants['plugin_key']	= "icwoocommercemis";			
			 
			if(is_admin()){				
				add_action('admin_menu', array(&$this, 'wcismis_add_page'));
				
				add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));	
				add_action('wp_ajax_wcismis_action_comman', array($this, 'wcismis_action_comman'));
				
				add_filter( 'plugin_action_links_woocommerce-mis-report/woocommerce_ic_mis_report.php', array( $this, 'plugin_action_links' ), 9, 2 );
				
				if(isset($_GET['page']) && $_GET['page'] == "wcismis_page"){
					add_action('admin_footer',  array( &$this, 'admin_footer'));
					$this->per_page = get_option('wcismis_per_page',5);				
					$this->define_constant();
					
					$action_type = $this->get_request('action_type');					
					if($action_type == "export"){
						$this->export();
					}
				}
				
				if(isset($_GET['page']) && $_GET['page'] == "wcismis_add_ons_page"){
					add_action('admin_footer',  array( &$this, 'admin_footer'));	
					$this->define_constant();
				}
				
			}
			
			add_action('init', array($this, 'init'));
		}
		
		function init(){
			load_plugin_textdomain( 'icwoocommercemis', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' ); 
		}
		
		
		public function get_request($name,$default = NULL,$set = false){
			if(isset($_REQUEST[$name])){
				$newRequest = $_REQUEST[$name];
				
				if(is_array($newRequest)){
					$newRequest = implode(",", $newRequest);
				}else{
					$newRequest = trim($newRequest);
				}
				
				if($set) $_REQUEST[$name] = $newRequest;
				
				return $newRequest;
			}else{
				if($set) 	$_REQUEST[$name] = $default;
				return $default;
			}
		}
		
		function add_page(){
			global $setting_intence, $activate_golden_intence;
			$current_page	= $this->get_request('page',NULL,false);
			$this->constants['plugin_key'] = 'wcismis';
			$c				= $this->constants;
			$title			= NULL;
			$intence		= NULL;
			
			if ( ! current_user_can('manage_options') ) return;
			
			switch($current_page){
				case $this->constants['plugin_key'].'_add_ons_page':
					$title = NULL;
					include_once('woocommerce_ic_mis_report_addon.php');
					$intence = new IC_Commerce_Add_Ons($c);
					break;				
				default:
					//include_once('ic_commerce_golden_dashboard.php');
					//$intence = new IC_Commerce_Golden_Dashboard($c);
					break;
				break;			
			}
			//add_action('admin_footer',  array( &$this, 'admin_footer'),9);
			//$this->print_array($this->constants);
			?>
				<div class="wrap <?php echo $this->constants['plugin_key']?>_wrap iccommercepluginwrap">
					<div class="icon32" id="icon-options-general"><br /></div>
					<?php  if($title):?>
						<h2><?php _e($title,'icwoocommercemis');?></h2>
					<?php endif; ?>
					<?php if($intence) $intence->init(); else echo "Class not found."?>			
				</div>
			<?php   
			//add_action( 'admin_footer', array( $this, 'admin_footer_css'),100);
		}
		
		
		function wcismis_add_page(){
			$main_page = add_menu_page($this->plugin_name, __('MIS Report','icwoocommercemis'), 'manage_options', 'wcismis_page', array($this, 'wcismis_page'), plugins_url( 'woocommerce-mis-report/assets/images/menu_icons.png' ), '56.01' );		
			
			add_submenu_page('wcismis_page',__( 'Other Plug-ins', 	'icwoocommercemis'),	__( 'Other Plug-ins', 	'icwoocommercemis' ),'manage_options','wcismis_add_ons_page',array($this, 'add_page'));
			
		}
		function define_constant(){
			if(!defined('WC_IS_MIS_FILE_PATH')) define( 'WC_IS_MIS_FILE_PATH', dirname( __FILE__ ) );
			if(!defined('WC_IS_MIS_DIR_NAME')) 	define( 'WC_IS_MIS_DIR_NAME', basename( WC_IS_MIS_FILE_PATH ) );
			if(!defined('WC_IS_MIS_FOLDER')) 	define( 'WC_IS_MIS_FOLDER', dirname( plugin_basename( __FILE__ ) ) );
			if(!defined('WC_IS_MIS_NAME')) 		define(	'WC_IS_MIS_NAME', plugin_basename(__FILE__) );
			if(!defined('WC_IS_MIS_URL')) 		define( 'WC_IS_MIS_URL', WP_CONTENT_URL . '/plugins/' . WC_IS_MIS_FOLDER );
			$this->constants['plugin_url'] 		= WC_IS_MIS_URL;
		}
		
		
		function export(){
			$action_type = $this->get_request('action_type');					
			if($action_type == "export"){
				require_once("woocommerce_ic_mis_export.php");
				$obj = new wc_ic_export();
				$obj->export();
			}
		}
		
		function admin_enqueue_scripts($hook) {
				if( 'toplevel_page_wcismis_page' != $hook ) {
					//return;		
				}
				
				$page = isset($_GET['page']) ? $_GET['page'] : '';
				
				if($page == "wcismis_page"){
					global $wp_scripts;
					
					/*AMCHART*/
					wp_enqueue_script('wcismis_ajax_script_amcharts', plugins_url( '/assets/js/amcharts/amcharts.js', __FILE__), array('jquery'));
					wp_enqueue_script('wcismis_ajax_script_amcharts_pie', plugins_url( '/assets/js/amcharts/pie.js', __FILE__ ));
					wp_enqueue_script('wcismis_ajax_script_amcharts_serial', plugins_url( '/assets/js/amcharts/serial.js', __FILE__));
					wp_enqueue_script('wcismis_ajax_script_amcharts_light', plugins_url( '/assets/js/amcharts/light.js', __FILE__ ));
					/*END AMCHART*/
					
					wp_enqueue_script('wcismis_ajax_script', plugins_url( '/assets/js/graph.js', __FILE__ ));
					//wp_enqueue_script('wcismis_ajax_script', plugins_url( '/assets/js/amcharts.scripts.js', __FILE__ ), array('jquery'));
					wp_localize_script('wcismis_ajax_script', 'ajax_object', array('ajaxurl' => admin_url('admin-ajax.php' ))); // setting ajaxurl	
					
					
					wp_enqueue_script('jquery-ui-datepicker');
					
					// get registered script object for jquery-ui
					$ui = $wp_scripts->query('jquery-ui-core');
				 
					// tell WordPress to load the Smoothness theme from Google CDN
					$protocol = is_ssl() ? 'https' : 'http';
					$url = "$protocol://ajax.googleapis.com/ajax/libs/jqueryui/{$ui->ver}/themes/smoothness/jquery-ui.min.css";
					wp_register_style('jquery-ui-smoothness', $url, false, null);
					wp_enqueue_style( 'jquery-ui-smoothness'); 
				}
				
				if($page == "wcismis_add_ons_page" || $page == "wcismis_page"){
					wp_enqueue_style( 'wcismis_admin_styles', WC_IS_MIS_URL . '/assets/css/admin.css' );
				}		
		}
		
		function wcismis_action_comman() {
			if(isset($_POST['action']) && $_POST['action'] == "wcismis_action_comman"){
				
				if(isset($_POST['graph_by_type']) && $_POST['graph_by_type'] == "top_product"){
					$this->wcismis_pie_chart_top_product();					
				}
				//
				if(isset($_POST['graph_by_type']) && $_POST['graph_by_type'] == "today_order_count"){
					$this->wcismis_today_order_count();					
				}
				if(isset($_POST['graph_by_type']) && $_POST['graph_by_type'] == "Last_7_days_sales_order_amount"){
					$this->wcismis_Last_7_days_sales_order_amount();					
				}
			}
			die(); // this is required to return a proper result
			exit;
		}
		
		
		
		function wcismis_page(){
			global $options;
			
			$this->constants['date_format'] = isset($this->constants['date_format']) ? $this->constants['date_format'] : get_option( 'date_format', "Y-m-d" );//New Change ID 20150209
			$options				= array();
			$date_format			= $this->constants['date_format'];
			$this->today			= date_i18n("Y-m-d");
			$this->yesterday 		= date("Y-m-d",strtotime("-1 day",strtotime($this->today)));
			$this->per_page_default	= 5;
			
			//print_r($_REQUEST);
			
			$shop_order_status		= array('wc-on-hold','wc-processing','wc-completed');	
			$hide_order_status 		= array();
			$start_date 			= $this->first_order_date();
			$end_date 				= $this->today;
			$summary_start_date 	= isset($_REQUEST['start_date']) 	? $_REQUEST['start_date'] 	: $start_date;
			$summary_end_date 		= isset($_REQUEST['end_date']) 		? $_REQUEST['end_date'] 	: $end_date;
			
			$total_part_refund_amt	= $this->get_part_order_refund_amount('total',$shop_order_status,$hide_order_status,$summary_start_date,$summary_end_date);
			$today_part_refund_amt	= $this->get_part_order_refund_amount('today',$shop_order_status,$hide_order_status,$summary_start_date,$summary_end_date);
			
			$_total_orders 			= $this->get_total_order('total',$shop_order_status,$hide_order_status,$summary_start_date,$summary_end_date);
			$total_orders 			= $this->get_value($_total_orders,'total_count',0);
			$total_sales 			= $this->get_value($_total_orders,'total_amount',0);
			$total_sales			= $total_sales - $total_part_refund_amt;
			$total_sales_avg		= $this->get_average($total_sales,$total_orders);
			
			//$users_of_blog 			= count_users();
			//$total_customer 		= isset($users_of_blog['avail_roles']['customer']) ? $users_of_blog['avail_roles']['customer'] : 0;
			
			
			$total_customer 		= $this->get_total_today_order_customer('custom',false,$summary_start_date,$summary_end_date);
			$total_guest_customer 	= $this->get_total_today_order_customer('custom',true,$summary_start_date,$summary_end_date);
			
			$total_categories  	=	$this->wcismis_get_total_categories_count();
			$total_products  	=	$this->wcismis_get_total_products_count();
			
			/*Refund*/
			$total_refund 			= $this->get_total_by_status("total","refunded",$hide_order_status,$summary_start_date,$summary_end_date);
			
			
			$total_refund_amount 	= $this->get_value($total_refund,'total_amount',0);
			$total_refund_count 	= $this->get_value($total_refund,'total_count',0);
			
			$total_refund_amount	= $total_refund_amount + $total_part_refund_amt;
			
			/*Tax*/
			$total_order_tax 		= $this->get_total_of_order("total","_order_tax","tax",$shop_order_status,$hide_order_status,$summary_start_date,$summary_end_date);
			
			$total_ord_tax_amount	= $this->get_value($total_order_tax,'total_amount',0);
			$total_ord_tax_count 	= $this->get_value($total_order_tax,'total_count',0);
			
			$total_ord_shipping_tax	= $this->get_total_of_order("total","_order_shipping_tax","tax",$shop_order_status,$hide_order_status,$summary_start_date,$summary_end_date);
			
			$total_ordshp_tax_amount= $this->get_value($total_ord_shipping_tax,'total_amount',0);
			$total_ordshp_tax_count = $this->get_value($total_ord_shipping_tax,'total_count',0);
			
			$ytday_order_tax		= $this->get_total_of_order("yesterday","_order_tax","tax",$shop_order_status,$hide_order_status,$summary_start_date,$summary_end_date);
			$ytday_ord_shipping_tax	= $this->get_total_of_order("yesterday","_order_shipping_tax","tax",$shop_order_status,$hide_order_status,$summary_start_date,$summary_end_date);
			
			$ytday_tax_amount		= $this->get_value($ytday_order_tax,'total_amount',0);
			$ytday_ordshp_tax_amount= $this->get_value($ytday_ord_shipping_tax,'total_amount',0);
			$ytday_total_tax_amount = $ytday_tax_amount + $ytday_ordshp_tax_amount;
			
			$total_tax_amount		= $total_ordshp_tax_amount + $total_ord_tax_amount;
			$total_tax_count 		= '';			
			
			/*Coupon*/
			$total_coupon 			= $this->get_total_of_coupon("total",$shop_order_status,$hide_order_status,$summary_start_date,$summary_end_date);
			
			$total_coupon_amount 	= $this->get_value($total_coupon,'total_amount',0);
			$total_coupon_count 	= $this->get_value($total_coupon,'total_count',0);
			
			add_filter( 'admin_footer_text', array( $this, 'admin_footer_text' ), 100);
			
			$page = isset($_GET['page']) ? $_GET['page'] : '';
			$admin_url = admin_url("admin.php")."?page=$page&start_date=$summary_start_date&end_date=$summary_end_date";
			
			
			
			?>
            	 <div class="wrap iccommercepluginwrap">                 	
                    <h2><?php _e('Dashboard - WooCommerce Advance Salse Report (FREE Version)','icwoocommercemis') ?></h2> 
                    
                     <?php $this->premium_gold();?>
                     
                     <div id="poststuff" class="woo_cr-reports-wrap">
                        <div class="woo_cr-reports-top">
                            <div class="row">
                                <div class="icpostbox">
                                    <h3><span><?php _e( 'Summary', 'icwoocommercemis'); ?></span></h3>
                                    
                                    <div class="clearfix"></div>
                                    <div class="SubTitle"><span><?php echo sprintf(__('Summary From %1$s To %2$s', 'icwoocommercemis'), date($date_format, strtotime($summary_start_date)),date($date_format, strtotime($summary_end_date))); ?></span></div>
                                    <div class="clearfix"></div>
                                    
                                    <div>
                                    	<form method="post">
                                          <div class="ic_box">
                                           
                                                <div class="ic_box_body">
                                                  <div class="">
                                                    <div class="ic_box_space">
														<div class="ic_box_form_fields">
														  <label for="start_date"><?php _e('Start Date:','icwoocommercemis')?></label>
														  <div class="form_control">
															<input type="text" name="start_date" id="start_date"  class="_proc_date" value="<?php  echo $summary_start_date;?>"  readonly="readonly"/>
														  </div>
														</div>
                                
														<div class="ic_box_form_fields">
														  <label for="end_date"><?php _e('End Date:','icwoocommercemis')?></label>
														  <div class="form_control">
															<input type="text" name="end_date" id="end_date" class="_proc_date" value="<?php  echo $summary_end_date;?>"  readonly="readonly"/>
														  </div>
														</div>
														
														<div class="clear_product"></div>
													   <div class="submit_loader">
														  <input type="submit" name="btnSearch" class="ic_button" id="btnSearch" value="<?php esc_html_e('Search','icwoocommercemis')?>" />                                                      
													  </div>
                                                      
                                                    </div>
                                                  </div>
                                                </div>
                                            </div>
                                            
                                        </form>
                                    </div>
                                    
                                    <div class="ic_dashboard_summary_box">
                                    
                                        <div class="ic_block ic_block-orange">
                                            <div class="ic_block-content">
                                                <h2><span><?php _e( 'Total Sales', 'icwoocommercemis'); ?></span></h2>
                                                <div class="ic_stat_content">
                                                    <p class="ic_stat">
                                                        <?php if ( $total_sales > 0 ) echo $this->price($total_sales); else _e( '0', 'icwoocommercemis'); ?>
                                                        <span class="ic_count">#<?php if ( $total_orders > 0 ) echo $total_orders; else _e( '0', 'icwoocommercemis'); ?></span>
                                                    </p>
                                                    <img src="<?php echo $this->constants['plugin_url']?>/assets/images/icons/sales-icon.png" alt="" />
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="ic_block ic_block-green">
                                            <div class="ic_block-content">
                                                <h2><span><?php _e( 'Average Sales Per Order', 'icwoocommercemis'); ?></span></h2>
                                                <div class="ic_stat_content">
                                                    <p class="ic_stat"><?php if ( $total_sales_avg > 0 ) echo $this->price($total_sales_avg); else _e( '0', 'icwoocommercemis'); ?></p>
                                                    <img src="<?php echo $this->constants['plugin_url']?>/assets/images/icons/average-icon.png" alt="" />
                                                </div>
                                            </div>
                                        </div>
										
										<div class="ic_block ic_block-green3">
											<div class="ic_block-content">
												<h2><span><?php _e( 'Total Refund', 'icwoocommercemis'); ?></span></h2>
												
												<div class="ic_stat_content">
													<p class="ic_stat">
														<?php if ( $total_refund_amount > 0 ) echo $this->price($total_refund_amount); else _e( '0', 'icwoocommercemis'); ?>
														<span class="ic_count">#<?php if ( $total_refund_count > 0 ) echo $total_refund_count; else _e( '0', 'icwoocommercemis'); ?></span>
													</p>
													<img src="<?php echo $this->constants['plugin_url']?>/assets/images/icons/refund-icon1.png" alt="" />
												</div>
											</div>
										</div>
										
										<div class="ic_block ic_block-maroon">
											<div class="ic_block-content">
												<h2><span><?php _e( 'Total Tax', 'icwoocommercemis'); ?></span></h2>
												<div class="ic_stat_content">
													<p class="ic_stat">
														<?php if ( $total_tax_amount > 0 ) echo $this->price($total_tax_amount); else _e( '0', 'icwoocommercemis'); ?>
														<span class="ic_count"><?php if ( $total_tax_count > 0 ) echo $total_tax_count; else _e( '', 'icwoocommercemis'); ?></span>
													</p>
													<img src="<?php echo $this->constants['plugin_url']?>/assets/images/icons/tax-icon2.png" alt="" />
												</div>
											</div>
										</div>
                                        
                                        <div class="ic_block ic_block-light-green">
                                            <div class="ic_block-content">
                                                <h2 class="small-size"><?php _e( 'Total Registered Customers', 'icwoocommercemis'); ?></span></h2>
                                                <div class="ic_stat_content">
                                                    <p class="ic_stat">#<?php if ( $total_customer > 0 ) echo $total_customer; else _e( '0', 'icwoocommercemis'); ?></p>
                                                    <img src="<?php echo $this->constants['plugin_url']?>/assets/images/icons/customers-icon.png" alt="" />
                                                </div>
                                            </div>
                                        </div>
										<div class="ic_block ic_block-blue-light">
											<div class="ic_block-content">
												<h2><span><?php _e( 'Total Coupons', 'icwoocommercemis'); ?></span></h2>
												<div class="ic_stat_content">
													<p class="ic_stat">
														<?php if ( $total_coupon_amount > 0 ) echo $this->price($total_coupon_amount); else _e( '0', 'icwoocommercemis'); ?>
														<span class="ic_count">#<?php if ( $total_coupon_count > 0 ) echo $total_coupon_count; else _e( '0', 'icwoocommercemis'); ?></span>
													</p>
													<img src="<?php echo $this->constants['plugin_url']?>/assets/images/icons/coupon-icon1.png" alt="" />
												</div>
											</div>
										</div>
                                        
                                        <div class="ic_block ic_block-brown">
                                            <div class="ic_block-content">
                                                <h2><?php _e( 'Total Guest Customers', 'icwoocommercemis'); ?></span></h2>
                                                <div class="ic_stat_content">
                                                    <p class="ic_stat">#<?php if ( $total_guest_customer > 0 ) echo $total_guest_customer; else _e( '0', 'icwoocommercemis'); ?></p>
                                                    <img src="<?php echo $this->constants['plugin_url']?>/assets/images/icons/customers-icon.png" alt="" />
                                                </div>
                                            </div>
                                        </div>
                                        <!--<div class="clearfix"></div>-->
                                         <div class="ic_block ic_block-blue">
                                            <div class="ic_block-content">
                                                <h2><?php _e( 'Total Products', 'icwoocommercemis'); ?></span></h2>
                                                <div class="ic_stat_content">
                                                    <p class="ic_stat">#<?php if ( $total_products > 0 ) echo $total_products; else _e( '0', 'icwoocommercemis'); ?></p>
                                                    <img src="<?php echo $this->constants['plugin_url']?>/assets/images/icons/product-icon.png" alt="" />
                                                </div>
                                            </div>
                                        </div>
                                        
                                         <div class="ic_block ic_block-purple">
                                            <div class="ic_block-content">
                                                <h2><?php _e( 'Total Category', 'icwoocommercemis'); ?></span></h2>
                                                <div class="ic_stat_content">
                                                    <p class="ic_stat">#<?php if ( $total_guest_customer > 0 ) echo $total_guest_customer; else _e( '0', 'icwoocommercemis'); ?></p>
                                                    <img src="<?php echo $this->constants['plugin_url']?>/assets/images/icons/category-icon.png" alt="" />
                                                </div>
                                            </div>
                                        </div>
                                        
                                    <div class="clearfix"></div>
                                </div>
                            </div>
                        </div>
                    	</div>
                     </div>
                     
                     <div class="row">
                        <div class="col-md-6">
                        	<div class="icpostbox">
                                <h3>
                                    <span class="title"><?php _e( 'Last 7 days Sales', 'icwoocommercemis'); ?></span>           	
                                </h3>
                                <div class="ic_inside Overflow">                            
                                    <div id="last_7_days_sales_order_amount" class="example-chart" style="width:98%; margin-left:3px;"></div>
                                </div>
                            </div>
                        </div>
                       
                        <div class="col-md-6">
                        	<div class="icpostbox">
                                <h3>
                                    <span class="title"><?php _e( 'Top 5 Products', 'icwoocommercemis'); ?></span>           	
                                </h3>
                                <div class="ic_inside Overflow">                            
                                    <div id="top_product_pie_chart" class="example-chart"></div>
                                </div>
                            </div>
                        </div>
                        
                    </div><!--End Row--> 
                    
                    <div class="row ThreeCol_Boxes">
                        <div class="col-md-6">
                            <div class="icpostbox">
                                <h3>
                                    <span class="title"><?php _e( 'Order Summary', 'icwoocommercemis'); ?></span>
                                    <span class="progress_status"></span>
                                </h3>
                                <div class="ic_inside Overflow" id="sales_order_count_value">
                                    <div class="grid"><?php $this->sales_order_count_value($shop_order_status,$hide_order_status,$start_date,$end_date);?></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="icpostbox">
                                <h3>
									<span class="title"><?php echo sprintf(__( 'Top %s Category','icwoocommercemis' ),$this->get_number_only('top_product_per_page',$this->per_page_default)); ?></span>
                                </h3>                                
                               
                                <div class="ic_inside Overflow" id="top_category_status">
                                	<div class="chart_parent">
                                    	<div class="chart" id="top_category_status_chart"></div>
                                    </div>
                                    <div class="grid"><?php $this->get_category_list($shop_order_status,$hide_order_status,$start_date,$end_date);?></div>
                                </div>
                            </div>                    	
                        </div>
                    </div>
                     
                  
                    
                    <div class="row ThreeCol_Boxes">
                        <div class="col-md-6">
                            <div class="icpostbox">
                                <h3>
									<span class="title"><?php echo sprintf(__( 'Top %s Billing Country','icwoocommercemis' ),$this->get_number_only('top_billing_country_per_page',$this->per_page_default)); ?></span>
                                </h3>
                                <div class="ic_inside Overflow" id="top_billing_country">
                                	<div class="chart_parent">
                                    	<div class="chart" id="top_billing_country_chart"></div>
                                    </div>
                                    <div class="grid"><?php $this->top_billing_country($shop_order_status,$hide_order_status,$start_date,$end_date);//New Change ID 20140918?></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="icpostbox">
                                <h3>
									<span class="title"><?php echo sprintf(__( 'Top %s Customers','icwoocommercemis' ),$this->get_number_only('top_customer_per_page',$this->per_page_default)); ?></span>
                                </h3>
                                <div class="ic_inside Overflow" id="top_customer_list">
                                	<div class="chart_parent">
                                    	<div class="chart" id="top_customer_list_chart"></div>
                                    </div>
                                    <div class="grid"><?php $this->top_customer_list($shop_order_status,$hide_order_status,$start_date,$end_date);?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                     <div class="row">
                        <div class="icpostbox">
                            <h3>
								<span class="title"><?php echo sprintf(__( 'Recent %s Orders','icwoocommercemis' ),$this->get_number_only('recent_order_per_page',$this->per_page_default)); ?></span>
                                <span class="text-right export"><a href="<?php echo "$admin_url&report_name=recent_order&action_type=export"?>"><?php _e( 'Export','icwoocommercemis' ); ?></a></span>
                            </h3>
                            <div class="ic_inside Overflow">                            
                                <div class="grid"><?php $this->recent_orders($shop_order_status,$hide_order_status,$start_date,$end_date);?></div>
                            </div>
                        </div>
                    </div>
                    
                    
                 </div><!--End Wrap-->
            <?php
		}
		
		function premium_gold(){
			?>
				<div class="row">
                    <div class="icpostbox">
                        <h3>
                            <span class="title"><?php echo sprintf(__( 'Upgrade to Advance Sales Report (Premium Gold Version)' ,'icwoocommercemis'),$this->get_number_only('recent_order_per_page',$this->per_page_default)); ?></span>                        	
                        </h3>
                        <div class="ic_inside Overflow">                            
                            <div>                                
								<div class="Notes">
									<div class="offer-plugin">
										<h4>Upgrade to Premium Gold Plugin for $199</h4>
                                        <div class="clearfix"></div>
									</div>
									<ul style="margin-right:15px;">
										<li>Improvised Dashboard, More summaries</li>
										<li>Projected Vs Actual Sales</li>
										<li>Top n States, Category wise sales summary</li>
										<li>8 Different Crosstab Reports</li>
										<li>Cost of Goods/Profit Report/Analysis</li>
                                        
										<li>Tax Reporting </li>
										<li>Coupon based Reporting</li>
										<li>Customize Columns</li>
                                        <li>Stock Analysis Reports</li>
										<li>Export to Excel, CSV</li>
										
										<li>Auto Email Reports</li>
										<li>Advance Variation Filters</li>
										<li>Monthly Sales Reports (Tax, Coupon, Total  Orders, Customer)</li>
										<li>Online PDF Generation</li>
                                        <li>New Customer/Repeat Customer Analysis</li>
										
										<li> <a href="https://plugins.infosofttech.com/woocommerce-advance-sales-report-premium-gold/">Almost 60+ Reports</a></li>
									</ul>
									
									<div class="clearfix"></div>
									
									<div class="footer_buttons"> 
										<div class="footer_left">
											<div class="footer_note">
												<p>Enquiries, Suggestions mail us at - <a href="mailto:sales@infosofttech.com">sales@infosofttech.com</a></p>
												<p>Website: <a href="http://plugins.infosofttech.com" target="_blank">plugins.infosofttech.com</a></p>
											</div>
										</div> 
										
										<div class="footer_left footer_left1">
											<a href="https://demos.infosofttech.com/wp-admin/admin.php?page=icwoocommercepremiumgold_page" target="_blank" class="ViewDemo">View Demo</a>                                            
										</div>
										
										<div class="clearfix"></div>                                               
									</div>
									<div class="clearfix"></div>
								</div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php  	  		
		}
		 
		function wcismis_Last_7_days_sales_order_amount()
		{
			global $wpdb,$sql,$Limit;

			$weekarray = array();
			$timestamp = time();
			for ($i = 0 ; $i < 7 ; $i++) {
				$weekarray[] =  date('Y-m-d', $timestamp);
				$timestamp -= 24 * 3600;
			}
			
			$sql = " SELECT    
				DATE(posts.post_date) AS 'Date' ,
				sum(meta_value) AS 'TotalAmount'
				
				FROM {$wpdb->prefix}posts as posts 
				
				LEFT JOIN  {$wpdb->prefix}postmeta as postmeta ON posts.ID=postmeta.post_id
				
				
				WHERE  post_type='shop_order' AND meta_key='_order_total' AND (posts.post_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY))
				GROUP BY  DATE(posts.post_date)
				";
				$order_items = $wpdb->get_results($sql);
				
				$item_dates = array();
				$item_data = array();
				
				foreach($order_items as $item)
				{
					$item_dates[] = trim($item->Date);
					$item_data[$item->Date]	= $item->TotalAmount;
				}
				$new_data = array();
				foreach($weekarray as $date)
				{	if(in_array($date, $item_dates))
					{
						
						$new_data[$date] = $item_data[$date];
					}
					else
					{
						$new_data[$date] = 0;
					}
				}
				
				$new_data2 = array();
				$i = 0;
				foreach($new_data as $key => $value)
				{
					$new_data2[$i]["Date"]	= $key;
					$new_data2[$i]["TotalAmount"]	= $value;
					
					$i++;
					
				}				
				if(isset($_POST['graph_by_type']) && $_POST['graph_by_type'] == "Last_7_days_sales_order_amount"){
					echo	json_encode(array_reverse($new_data2));
				}
				else
				{
					return $order_items;
				}		
				
		}
		
		/*To 5 Products*/
		function wcismis_pie_chart_top_product()
		{
			global $wpdb,$sql,$Limit;
			$Limit = 5;
			
			/*Order ID, Order Product Name */
				$sql = "SELECT  
						woocommerce_order_items.order_item_name AS 'ItemName'
						,woocommerce_order_items.order_item_id
						,SUM(woocommerce_order_itemmeta.meta_value) AS 'Qty'
						,SUM(woocommerce_order_itemmeta6.meta_value) AS 'Total'
						
						FROM {$wpdb->prefix}woocommerce_order_items as woocommerce_order_items
						LEFT JOIN    {$wpdb->prefix}posts                        as posts                         ON posts.ID                                        =    woocommerce_order_items.order_id
						LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta ON woocommerce_order_itemmeta.order_item_id=woocommerce_order_items.order_item_id						
						LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta6 ON woocommerce_order_itemmeta6.order_item_id=woocommerce_order_items.order_item_id						
						LEFT JOIN    {$wpdb->prefix}woocommerce_order_itemmeta     as woocommerce_order_itemmeta3    ON woocommerce_order_itemmeta3.order_item_id    =    woocommerce_order_items.order_item_id
					
						WHERE woocommerce_order_itemmeta.meta_key='_qty' AND woocommerce_order_itemmeta6.meta_key='_line_total'
						AND posts.post_type          =    'shop_order'
						AND woocommerce_order_itemmeta3.meta_key        =    '_product_id'
						GROUP BY  woocommerce_order_itemmeta3.meta_value
						Order By Total DESC
						LIMIT 5
			";
			$order_items = $wpdb->get_results($sql);
			if(isset($_POST['graph_by_type']) && $_POST['graph_by_type'] == "top_product"){
				echo	json_encode($order_items);
				//echo "anzar";
			}
			else
			{
				return $order_items;
			}		
			
		}
		
		function get_total_by_status($type = 'today',$status = 'refunded',$hide_order_status,$start_date,$end_date)	{
			global $wpdb;
			$today_date 			= $this->today;
			$yesterday_date 		= $this->yesterday;
			$sql = "SELECT";
			
			$sql .= " SUM( postmeta.meta_value) As 'total_amount', count( postmeta.post_id) AS 'total_count'";
			$sql .= "  FROM {$wpdb->prefix}posts as posts";
			
			if($this->constants['post_order_status_found'] == 0 ){
				$sql .= "
				LEFT JOIN  {$wpdb->prefix}term_relationships as term_relationships ON term_relationships.object_id=posts.ID
				LEFT JOIN  {$wpdb->prefix}term_taxonomy as term_taxonomy ON term_taxonomy.term_taxonomy_id=term_relationships.term_taxonomy_id
				LEFT JOIN  {$wpdb->prefix}terms as terms ON terms.term_id=term_taxonomy.term_id";
				
				$date_field = ($status == 'refunded') ? "post_modified" : "post_date";
			}else{
				$status = "wc-".$status;
				$date_field = ($status == 'wc-refunded') ? "post_modified" : "post_date";
			}
			
			$sql .= "
			LEFT JOIN  {$wpdb->prefix}postmeta as postmeta ON postmeta.post_id=posts.ID
			WHERE postmeta.meta_key = '_order_total' AND posts.post_type='shop_order'";
			
			
						
			if($type == "today" || $type == "today") $sql .= " AND DATE(posts.{$date_field}) = '".$today_date."'";
			if($type == "yesterday") 	$sql .=" AND DATE(posts.{$date_field}) = '".$yesterday_date."'";
			
			
			if ($start_date != NULL &&  $end_date != NULL && $type != "today"){
				$sql .= " AND DATE(posts.{$date_field}) BETWEEN '{$start_date}' AND '{$end_date}'";
			}
			
			if($this->constants['post_order_status_found'] == 0 ){
				$sql .= " AND  terms.name IN ('{$status}')";
				if(strlen($status)>0){
					$sql .= " AND  terms.slug IN ('{$status}')";
				}
			}else{
				if(strlen($status)>0){
					$sql .= " AND  posts.post_status IN ('{$status}')";
				}
			}
			
			if(count($hide_order_status)>0){
				$in_hide_order_status		= implode("', '",$hide_order_status);
				$sql .= " AND  posts.post_status NOT IN ('{$in_hide_order_status}')";
			}
			
			if($this->constants['post_order_status_found'] == 0 ){
				$sql .= " Group BY terms.term_id ORDER BY total_amount DESC";
			}else{
				$sql .= " Group BY posts.post_status ORDER BY total_amount DESC";
			}
			
			return $wpdb->get_row($sql);
		
		}
		
		function get_total_of_order($type = "today", $meta_key="_order_tax",$order_item_type="tax",$shop_order_status,$hide_order_status,$start_date,$end_date){
			global $wpdb;
			$today_date 			= $this->today;
			$yesterday_date 		= $this->yesterday;
			
			$sql = "  SELECT";
			$sql .= " SUM(postmeta1.meta_value) 	AS 'total_amount'";
			$sql .= " ,count(posts.ID) 				AS 'total_count'";
			$sql .= " FROM {$wpdb->prefix}posts as posts";			
			$sql .= " LEFT JOIN  {$wpdb->prefix}postmeta as postmeta1 ON postmeta1.post_id=posts.ID";			
						
			if($this->constants['post_order_status_found'] == 0 ){
				if(count($shop_order_status)>0){
					$sql .= " 
					LEFT JOIN  {$wpdb->prefix}term_relationships 	as term_relationships 	ON term_relationships.object_id		=	posts.ID
					LEFT JOIN  {$wpdb->prefix}term_taxonomy 		as term_taxonomy 		ON term_taxonomy.term_taxonomy_id	=	term_relationships.term_taxonomy_id";
				}
			}
			
			$sql .= " WHERE postmeta1.meta_key = '{$meta_key}' AND posts.post_type = 'shop_order' AND postmeta1.meta_value > 0";
			
			$sql .= " AND posts.post_type='shop_order' ";
			
			if($type == "today") $sql .= " AND DATE(posts.post_date) = '{$today_date}'";
			if($type == "yesterday") 	$sql .= " AND DATE(posts.post_date) = '{$yesterday_date}'";
			
			if($this->constants['post_order_status_found'] == 0 ){
				if(count($shop_order_status)>0){
					$in_shop_order_status = implode(",",$shop_order_status);
					$sql .= " AND  term_taxonomy.term_id IN ({$in_shop_order_status})";
				}
			}else{
				if(count($shop_order_status)>0){
					$in_shop_order_status		= implode("', '",$shop_order_status);
					$sql .= " AND  posts.post_status IN ('{$in_shop_order_status}')";
				}
			}
			
			if ($start_date != NULL &&  $end_date != NULL && $type != "today"){
				$sql .= " AND DATE(posts.post_date) BETWEEN '{$start_date}' AND '{$end_date}'";
			}
			
			if(count($hide_order_status)>0){
				$in_hide_order_status		= implode("', '",$hide_order_status);
				$sql .= " AND  posts.post_status NOT IN ('{$in_hide_order_status}')";
			}
			
			return $order_items = $wpdb->get_row($sql);
		}
		
		function get_part_order_refund_amount($type = "today",$shop_order_status,$hide_order_status,$start_date,$end_date){
				global $wpdb;
				
				$today_date 			= $this->today;
				$yesterday_date 		= $this->yesterday;
				
				$sql = " SELECT SUM(postmeta.meta_value) 		as total_amount
						
				FROM {$wpdb->prefix}posts as posts
								
				LEFT JOIN  {$wpdb->prefix}postmeta as postmeta ON postmeta.post_id	=	posts.ID";
				
				$sql .= " LEFT JOIN  {$wpdb->prefix}posts as shop_order ON shop_order.ID	=	posts.post_parent";
				
				if($this->constants['post_order_status_found'] == 0 ){
					if(count($shop_order_status)>0){
						$sql .= " 
						LEFT JOIN  {$wpdb->prefix}term_relationships 	as term_relationships 	ON term_relationships.object_id		=	posts.ID
						LEFT JOIN  {$wpdb->prefix}term_taxonomy 		as term_taxonomy 		ON term_taxonomy.term_taxonomy_id	=	term_relationships.term_taxonomy_id";
					}
				}
				
				$sql .= " WHERE posts.post_type = 'shop_order_refund' AND  postmeta.meta_key='_refund_amount'";
				
				$sql .= " AND shop_order.post_type = 'shop_order'";
						
				if($this->constants['post_order_status_found'] == 0 ){
					$refunded_id 	= $this->get_old_order_status(array('refunded'), array('wc-refunded'));
					$refunded_id    = implode(",".$refunded_id);
					$sql .= " AND terms2.term_id NOT IN (".$refunded_id .")";
					
					if(count($shop_order_status)>0){
						$in_shop_order_status = implode(",",$shop_order_status);
						$sql .= " AND  term_taxonomy.term_id IN ({$in_shop_order_status})";
					}
				}else{
					$sql .= " AND shop_order.post_status NOT IN ('wc-refunded')";
					
					if(count($shop_order_status)>0){
						$in_shop_order_status		= implode("', '",$shop_order_status);
						$sql .= " AND  shop_order.post_status IN ('{$in_shop_order_status}')";
					}
				}
				
				if ($start_date != NULL &&  $end_date != NULL && $type == "total"){
					$sql .= " AND DATE(posts.post_date) BETWEEN '{$start_date}' AND '{$end_date}'";
				}
				
				if($type == "today") $sql .= " AND DATE(posts.post_date) = '{$today_date}'";
				
				if($type == "yesterday") 	$sql .= " AND DATE(posts.post_date) = '{$yesterday_date}'";
				
				if(count($hide_order_status)>0){
					$in_hide_order_status		= implode("', '",$hide_order_status);
					$sql .= " AND  shop_order.post_status NOT IN ('{$in_hide_order_status}')";
				}
				
				$sql .= " LIMIT 1";
				
				//$this->print_sql($sql);
			
				$wpdb->query("SET SQL_BIG_SELECTS=1");
				
				$order_items = $wpdb->get_var($sql);
				
				return $order_items;
				
			}
		
		function get_total_of_coupon($type = "today",$shop_order_status,$hide_order_status,$start_date,$end_date){
				global $wpdb,$options;
				$today_date 			= $this->today;
				$yesterday_date 		= $this->yesterday;
				$sql = "
				SELECT				
				SUM(woocommerce_order_itemmeta.meta_value) As 'total_amount', 
				Count(*) AS 'total_count' 
				FROM {$wpdb->prefix}woocommerce_order_items as woocommerce_order_items 
				LEFT JOIN  {$wpdb->prefix}woocommerce_order_itemmeta as woocommerce_order_itemmeta ON woocommerce_order_itemmeta.order_item_id=woocommerce_order_items.order_item_id
				LEFT JOIN  {$wpdb->prefix}posts as posts ON posts.ID=woocommerce_order_items.order_id";
				
				if($this->constants['post_order_status_found'] == 0 ){
					if(count($shop_order_status)>0){
						$sql .= " 
						LEFT JOIN  {$wpdb->prefix}term_relationships 	as term_relationships 	ON term_relationships.object_id		=	posts.ID
						LEFT JOIN  {$wpdb->prefix}term_taxonomy 		as term_taxonomy 		ON term_taxonomy.term_taxonomy_id	=	term_relationships.term_taxonomy_id";
					}
				}
				
				$sql .= "
				WHERE 
				woocommerce_order_items.order_item_type='coupon' 
				AND woocommerce_order_itemmeta.meta_key='discount_amount'
				AND posts.post_type='shop_order'
				";
				
				if($type == "today") $sql .= " AND DATE(posts.post_date) = '{$today_date}'";
				if($type == "yesterday") 	$sql .= " AND DATE(posts.post_date) = '{$yesterday_date}'";
				
				if($this->constants['post_order_status_found'] == 0 ){
					if(count($shop_order_status)>0){
						$in_shop_order_status = implode(",",$shop_order_status);
						$sql .= " AND  term_taxonomy.term_id IN ({$in_shop_order_status})";
					}
				}else{
					if(count($shop_order_status)>0){
						$in_shop_order_status		= implode("', '",$shop_order_status);
						$sql .= " AND  posts.post_status IN ('{$in_shop_order_status}')";
					}
				}
				
				if ($start_date != NULL &&  $end_date != NULL && $type != "today"){
					$sql .= " AND DATE(posts.post_date) BETWEEN '{$start_date}' AND '{$end_date}'";
				}
				
				if(count($hide_order_status)>0){
					$in_hide_order_status		= implode("', '",$hide_order_status);
					$sql .= " AND  posts.post_status NOT IN ('{$in_hide_order_status}')";
				}
				
				//$this->print_sql($sql);
				return $order_items = $wpdb->get_row($sql); 
				
				///$this->print_array($order_items);
		}
		
		function wcismis_get_total_categories_count(){
			global $wpdb;
			$sql = "SELECT COUNT(*) As 'category_count' FROM {$wpdb->prefix}term_taxonomy as term_taxonomy  
					LEFT JOIN  {$wpdb->prefix}terms as terms ON terms.term_id=term_taxonomy.term_id
			WHERE taxonomy ='product_cat'";
			return $wpdb->get_var($sql);
		}
		
		function wcismis_get_total_products_count(){
			global $wpdb;
			$sql = "SELECT COUNT(*) AS 'product_count'  FROM {$wpdb->prefix}posts as posts WHERE  post_type='product' AND post_status = 'publish'";
			return $wpdb->get_var($sql);		
		}
		
		function admin_footer_text($footer_text = ""){
			//$footer_text = __( 'Thank You for using our WooCommerce Sales Report Plug-in.', 'icwoocommercemis' );
			
			$footer_text = __( 'Website: <a href="http://plugins.infosofttech.com" target="_blank">plugins.infosofttech.com</a><br /> Email: <a href="mailto:sales@infosofttech.com">sales@infosofttech.com</a>', 'icwoocommercemis' );
			
			return $footer_text;
		}
		function admin_footer(){
		}
		function plugin_action_links($plugin_links, $file){
			if ( $file == "woocommerce-mis-report/woocommerce_ic_mis_report.php") {
				$settings_link = array();
				$plugin_links[]		= '<a href="'.admin_url('admin.php?page=wcismis_page').'" title="'.__('Report', 	'icwoocommercemis').'">'.__('Report', 'icwoocommercemis').'</a>';
				$plugin_links[] = '<a target="_blank" style="color:#3db634;" href="https://plugins.infosofttech.com/woocommerce-advance-sales-report-premium-gold/?utm_source=plugin-list&utm_medium=upgrade-link&utm_campaign=plugin-list&utm_content=action-link">' . esc_html__( 'Upgrade', 'icwoocommercemis' ) . '</a>';
			}		
			return $plugin_links;
		}
	}
}

if(!function_exists("plugins_loaded_ic_woocommerce_mis_report")){
	function plugins_loaded_ic_woocommerce_mis_report(){
		if(!in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {	
			
			if(!defined('WC_IS_MIS_WC_ACITVE')) define( 'WC_IS_MIS_WC_ACITVE', FALSE );
			
			function wcismis_admin_notices(){
				$message = "";
				$message .= '<div class="error">';
				$message .= '<p>' . sprintf( __('WooCommerce MIS Report depends on <a href="%s">WooCommerce</a> to work!' , 'icwoocommercemis' ), 'http://wordpress.org/extend/plugins/woocommerce/' ) . '</p>';
				$message .= '</div>';
				echo  $message;
			}
			
			add_action( 'admin_notices', 'wcismis_admin_notices');
			
			$WC_IS_MIS_Report = new WC_IS_MIS_Report();
			
		}else{
			
			if(!defined('WC_IS_MIS_WC_ACITVE')) define( 'WC_IS_MIS_WC_ACITVE', TRUE );
			
			$WC_IS_MIS_Report = new WC_IS_MIS_Report();
		}
	}
}

add_action("plugins_loaded","plugins_loaded_ic_woocommerce_mis_report", 20);