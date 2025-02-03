<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
if ( ! class_exists( 'wc_ic_export' ) ) {
	require_once("ic-woocommerce-mis-report-core.php");
	class wc_ic_export extends WC_IS_MIS_Report_Core{
		
		public $constants = "";
		
		public function __construct() {
			global $options;
		}
		
		function export(){
			$export_format = $this->get_request('export_format','csv');			
			$this->export_csv($export_format);
			die;
		}
		
		function export_csv($export_file_format = 'csv'){
			$report_name 	= $this->get_request('report_name','');
			$columns		= $this->get_column($report_name);			
			$num_decimals   = get_option( 'woocommerce_price_num_decimals'	,	0		);
			$decimal_sep    = get_option( 'woocommerce_price_decimal_sep'	,	'.'		);
			$thousand_sep   = get_option( 'woocommerce_price_thousand_sep'	,	','		);			
			$zero			= number_format(0, $num_decimals,$decimal_sep,$thousand_sep);
			$export_rows	= array();
			$i				= 0;
			$order_items	= $this->get_items();
			
			foreach ( $order_items as $rkey => $rvalue ):	
				$order_item = $rvalue;			
				foreach($columns as $key => $value):					
					switch ($key) {
							case "item_amount":
							case "item_count":
								$td_value 	=  isset($rvalue->$key) ? $rvalue->$key : 0;
								$td_value 	=  strlen($td_value) != 0 ? $td_value : 0;
								$export_rows[$i][$key]	=  $td_value != 0 ? number_format($td_value, $num_decimals,$decimal_sep,$thousand_sep) : $zero;
								break;							
							default:								
								$export_rows[$i][$key] = isset($rvalue->$key) ? $rvalue->$key : '';
								break;
						}
				endforeach;
				$i++;
			endforeach;
			
			$today_date 	 	= date_i18n("Y-m-d-H-i-s");
			$export_file_name	= "ic_export_";
			$export_filename 	= $export_file_name."-".$report_name."-".$today_date.".".$export_file_format;
			$export_filename 	= apply_filters('ic_commerce_export_csv_excel_format_file_name',$export_filename,$report_name,$today_date,$export_file_name,$export_file_format);
			
			$out = $this->ExportToCsv($export_filename,$export_rows,$columns,$export_file_format,$report_name);
			
			$format		= $export_file_format;
			$filename	= $export_filename;
			if($format=="csv"){
				header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
				header("Content-Length: " . strlen($out));	
				header("Content-type: text/x-csv");
				header("Content-type: text/csv");
				header("Content-type: application/csv");
				header("Content-Disposition: attachment; filename=$filename");
			}elseif($format=="xls"){
				
				header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
				header("Content-Length: " . strlen($out));
				header("Content-type: application/octet-stream");
				header("Content-Disposition: attachment; filename=$filename");
				header("Pragma: no-cache");
				header("Expires: 0");
			}
			//echo $report_title;
			//echo "\n";
			echo $out;
			exit;
		}
		
		
		
		function get_column($report_name = ''){
			$grid_column 	= array(
				"order_id" 						=> __("Order ID", 'icwoocommercemis')
				,"billing_first_name" 			=> __("Billing First Name", 'icwoocommercemis')
				,"billing_email" 				=> __("Billing Email", 'icwoocommercemis')
				,"order_date" 					=> __("Order Date", 'icwoocommercemis')
				,"item_count" 					=> __("Item Count", 'icwoocommercemis')
				,"item_amount" 					=> __("Order Total", 'icwoocommercemis')
			);
			return $grid_column;
		}
		
		function get_items(){
			
			$shop_order_status 	= "";
			$hide_order_status 	= "";
			$start_date			= "";
			$end_date			= "";
			$report_name 		= $this->get_request('report_name');
			
			return $this->get_recent_orders($shop_order_status,$hide_order_status,$start_date,$end_date);
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
		
		function get_recent_orders($shop_order_status,$hide_order_status,$start_date,$end_date){
			global $wpdb;
			$sql = "SELECT
			woocommerce_order_items.order_id 	AS 'order_id' 
			,COUNT( *) 							AS 'item_count'
			,postmeta3.meta_value 				AS 'item_amount'
			,posts.post_date 					AS 'order_date'
			,postmeta2.meta_value 				AS 'billing_email'
			,postmeta4.meta_value 				AS 'billing_first_name'
			FROM 
			{$wpdb->prefix}woocommerce_order_items as woocommerce_order_items
			LEFT JOIN  {$wpdb->prefix}postmeta as postmeta4 ON postmeta4.post_id=woocommerce_order_items.order_id
			LEFT JOIN  {$wpdb->prefix}postmeta as postmeta3 ON postmeta3.post_id=woocommerce_order_items.order_id
			LEFT JOIN  {$wpdb->prefix}postmeta as postmeta2 ON postmeta2.post_id=woocommerce_order_items.order_id
			LEFT JOIN  {$wpdb->prefix}posts as posts ON posts.ID=woocommerce_order_items.order_id
			
			WHERE 
			postmeta2.meta_key		=	'_billing_email'
			AND postmeta3.meta_key	=	'_order_total'
			AND posts.post_type		=	'shop_order'
			AND postmeta4.meta_key	=	'_billing_first_name'
			AND woocommerce_order_items.order_item_type ='line_item'
			
			
			GROUP BY woocommerce_order_items.order_id
			
			Order By posts.post_date DESC 
			LIMIT 50";					
			$order_items = $wpdb->get_results($sql );
			
			return $order_items;
		}
		
		function ExportToCsv($filename = 'export.csv',$rows,$columns,$format="csv"){				
			global $wpdb;
			$csv_terminated = "\n";
			$csv_separator = ",";
			$csv_enclosed = '"';
			$csv_escaped = "\\";
			$fields_cnt = count($columns); 
			$schema_insert = '';
			
			if($format=="xls"){
				$csv_terminated = "\r\n";
				$csv_separator = "\t";
			}
				
			foreach($columns as $key => $value):
				$l = $csv_enclosed . str_replace($csv_enclosed, $csv_escaped . $csv_enclosed, $value) . $csv_enclosed;
				$schema_insert .= $l;
				$schema_insert .= $csv_separator;
			endforeach;// end for
		 
		   $out = trim(substr($schema_insert, 0, -1));
		   $out .= $csv_terminated;
			
			//printArray($rows);
			
			for($i =0;$i<count($rows);$i++){
				
				//printArray($rows[$i]);
				$j = 0;
				$schema_insert = '';
				foreach($columns as $key => $value){
						
						
						 if ($rows[$i][$key] == '0' || $rows[$i][$key] != ''){
							if ($csv_enclosed == '')
							{
								$schema_insert .= $rows[$i][$key];
							} else
							{
								$schema_insert .= $csv_enclosed . str_replace($csv_enclosed, $csv_escaped . $csv_enclosed, $rows[$i][$key]) . $csv_enclosed;
							}
						 }else{
							$schema_insert .= '';
						 }
						
						
						
						if ($j < $fields_cnt - 1)
						{
							$schema_insert .= $csv_separator;
						}
						$j++;
				}
				$out .= $schema_insert;
				$out .= $csv_terminated;
			}
			
			return $out;			
		 
		}
		
		
		
    }//End Class
	
}//End Class Exists