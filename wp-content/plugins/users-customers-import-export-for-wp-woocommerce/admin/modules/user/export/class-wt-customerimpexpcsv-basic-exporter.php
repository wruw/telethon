<?php

if (!defined('ABSPATH')) {
	exit;
}

class Wt_Import_Export_For_Woo_Basic_User_Bulk_Export {

	/**
	 * Customer Exporter
	 * @param array $user_IDS [optional]<p>Array of User Id.</p>
	 */
	public static function do_export($user_IDS = array(), $ftp = null) {
		global $wpdb;

		$csv_columns = include_once( __DIR__ . '/../data/data-user-columns.php' );

		$user_columns_name = !empty($_POST['columns_name']) ? $_POST['columns_name'] : $csv_columns;
		$export_columns = !empty($_POST['columns']) ? $_POST['columns'] : array();
		$delimiter = !empty($_POST['delimiter']) ? $_POST['delimiter'] : ',';

		$wpdb->hide_errors();
		@set_time_limit(0);
		if (function_exists('apache_setenv'))
			@apache_setenv('no-gzip', 1);
		@ini_set('zlib.output_compression', 0);
		@ob_end_clean();

		$file_name = apply_filters('wt_iew_product_bulk_export_user_filename', 'user_export_' . date('Y-m-d-h-i-s') . '.csv');

		header('Content-Type: text/csv; charset=UTF-8');
		header('Content-Disposition: attachment; filename=' . $file_name);
		header('Pragma: no-cache');
		header('Expires: 0');

		$fp = fopen('php://output', 'w');

		$args = array(
			'fields' => 'ID'
		);

		if (!empty($user_IDS)) {
			$args['include'] = $user_IDS; // An array of user IDs to include. Default empty array.
		}

		$users = get_users($args);

		// Variable to hold the CSV data we're exporting
		$row = array();

		// Export header rows
		foreach ($csv_columns as $column => $value) {

			if (!$export_columns || in_array($column, $export_columns)) {
				$temp_head = esc_attr($user_columns_name[$column]);
				$row[] = $temp_head;
			}
		}

		$row = array_map('Wt_Import_Export_For_Woo_Basic_User_Bulk_Export::wrap_column', apply_filters('wt_user_alter_csv_header', $row));
		fwrite($fp, implode($delimiter, $row) . "\n");
		$header_row = $row;
		unset($row);

		// Loop users
		foreach ($users as $user) {
			$data = self::get_customers_csv_row($user, $export_columns, $csv_columns, $header_row);
			$data = apply_filters('hf_customer_csv_exclude_admin', $data);
			$row = array_map('Wt_Import_Export_For_Woo_Basic_User_Bulk_Export::wrap_column', $data);
			fwrite($fp, implode($delimiter, $row) . "\n");
			unset($row);
			unset($data);
		}



		fclose($fp);
		exit;
	}

	public static function format_data($data) {
		//if (!is_array($data));
		//$data = (string) urldecode($data);
		$enc = mb_detect_encoding($data, 'UTF-8, ISO-8859-1', true);
		$data = ( $enc == 'UTF-8' ) ? $data : utf8_encode($data);
		return $data;
	}

	/**
	 * Wrap a column in quotes for the CSV
	 * @param  string data to wrap
	 * @return string wrapped data
	 */
	public static function wrap_column($data) {
		return '"' . str_replace('"', '""', $data) . '"';
	}

	/**
	 * Get the customer data for a single CSV row
	 * @since 3.0
	 * @param int $customer_id
	 * @param array $export_columns - user selected columns / all
	 * @return array $meta_keys customer/user meta data
	 */
	public static function get_customers_csv_row($id, $export_columns, $csv_columns, $header_row) {
		$user = get_user_by('id', $id);

		$customer_data = array();
		foreach ($csv_columns as $key) {
			if (!$export_columns || in_array($key, $export_columns)) {
				$key = trim(str_replace('meta:', '', $key));
				if ($key == 'roles') {
					$user_roles = (!empty($user->roles)) ? $user->roles : array();
					$customer_data['roles'] = implode(', ', $user_roles);
					continue;
				}
				if ( 'session_tokens' == $key ) {
					$customer_data[$key] = !empty($user->{$key}) ? base64_encode(json_encode(maybe_unserialize($user->{$key}))) : '';
					continue;
				}  
				if ($key != 'customer_id') {
					$customer_data[$key] = !empty($user->{$key}) ? maybe_serialize($user->{$key}) : '';
				} else {
					$customer_data[$key] = !empty($user->ID) ? maybe_serialize($user->ID) : '';
				}
                                
                                if ( 'orders' === $key ) {
                                    $customer_data[$key] = !empty($user->ID) ? wc_get_customer_order_count($user->ID) : 0;
                                    continue;
                                }            
                                if ( 'total_spent' === $key ) {
                                    $customer_data[$key] = !empty($user->ID) ? wc_get_customer_total_spent($user->ID) : 0.00;
                                    continue;
                                }
                                if ( 'aov' === $key ) {
                                    $total_spent = !empty($user->ID) ? wc_get_customer_total_spent($user->ID) : 0.00;
                                    $order_count = !empty($user->ID) ? wc_get_customer_order_count($user->ID) : 0;
                                    $customer_data[$key] = ( $order_count ) ? round( ( (float)$total_spent / (float)$order_count ), 2 ) : 0.00;
                                    continue;
                                }	
                                
			} else {
				continue;
			}
		}

		/*
		 * CSV Customer Export Row.
		 * Filter the individual row data for the customer export
		 * @since 3.0
		 * @param array $customer_data 
		 */
		return apply_filters('hf_customer_csv_export_data', $customer_data, $header_row);
	}

}
