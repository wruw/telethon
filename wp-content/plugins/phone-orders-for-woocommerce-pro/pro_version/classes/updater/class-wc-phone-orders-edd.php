<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


class WC_Phone_Orders_EDD {

	private static $instance;

	/**
	 * WC_Order_Export_EDD constructor.
	 */
	private function __construct() {
		// EDD license actions
		add_action( 'admin_init', array( $this, 'edd_wpo_plugin_updater' ), 0 );
		add_action( 'admin_init', array( $this, 'edd_wpo_register_option' ) );
		add_action( 'admin_init', array( $this, 'edd_wpo_activate_license' ) );
		add_action( 'admin_init', array( $this, 'edd_wpo_deactivate_license' ) );
	}

	//***********  EDD LICENSE FUNCTIONS BEGIN  *************************************************************************************************************************************************************************************************************************************************

	public static function getInstance() {
		if ( empty( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public static function wpo_get_main_url() {
		$home_url       = home_url();
		$url_components = explode( '.', basename( $home_url ) );
		if ( count( $url_components ) > 2 ) {
			array_shift( $url_components );
		}
		$main_url = implode( '.', $url_components );
		if ( strpos( $home_url, 'https://' ) !== 0 ) {
			$main_url = "https://{$main_url}";
		} else {
			$main_url = "http://{$main_url}";
		}

		return $main_url;
	}

	function edd_wpo_plugin_updater() {

		// retrieve our license key from the DB
		$license_key = trim( get_option( 'edd_wpo_license_key' ) );

		// setup the updater
		$edd_updater = new WC_Phone_Orders_Updater( WC_PHONE_ORDERS_STORE_URL,
			'phone-orders-for-woocommerce-pro/phone-orders-for-woocommerce-pro.php', array(
				'version'   => WC_PHONE_ORDERS_VERSION,   // current version number
				'license'   => $license_key,  // license key (used get_option above to retrieve from DB)
				'item_name' => WC_PHONE_ORDERS_ITEM_NAME, // name of this plugin
				'author'    => WC_PHONE_ORDERS_AUTHOR     // author of this plugin
			)
		);

	}

	function edd_wpo_license_page() {
		$this->error_messages = array(
			'missing'               => __( 'not found', 'phone-orders-for-woocommerce' ),
			'license_not_activable' => __( 'is not activable', 'phone-orders-for-woocommerce' ),
			'revoked'               => __( 'revoked', 'phone-orders-for-woocommerce' ),
			'no_activations_left'   => __( 'no activations left', 'phone-orders-for-woocommerce' ),
			'expired'               => __( 'expired', 'phone-orders-for-woocommerce' ),
			'key_mismatch'          => __( 'key mismatch', 'phone-orders-for-woocommerce' ),
			'invalid_item_id'       => __( 'invalid item ID', 'phone-orders-for-woocommerce' ),
			'item_name_mismatch'    => __( 'item name mismatch', 'phone-orders-for-woocommerce' ),
		);

		$license = get_option( 'edd_wpo_license_key' );
		$status  = get_option( 'edd_wpo_license_status' );
		$error   = get_option( 'edd_wpo_license_error' );
		if ( isset( $this->error_messages[ $error ] ) ) {
			$error = $this->error_messages[ $error ];
		}
		?>
        <div class="wrap">
        <h2><?php _e( 'Plugin License', 'phone-orders-for-woocommerce' ); ?></h2>
        <form method="post" action="options.php">

			<?php settings_fields( 'edd_wpo_license' ); ?>

            <table class="form-table">
                <tbody>
                <tr valign="top">
                    <th scope="row" valign="top">
						<?php _e( 'License Key', 'phone-orders-for-woocommerce' ); ?>
                    </th>
                    <td>
                        <input id="edd_wpo_license_key" name="edd_wpo_license_key" type="text" class="regular-text"
                               value="<?php esc_attr_e( $license ); ?>"/><br>
                        <label class="description"
                               for="edd_wpo_license_key"><?php _e( 'look for it inside purchase receipt (email)',
								'phone-orders-for-woocommerce' ); ?></label>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row" valign="top">
                    </th>
                    <td>
						<?php if ( $status !== false && $status == 'valid' ) { ?>
                            <span style="color:green;"><?php _e( 'License is active',
									'phone-orders-for-woocommerce' ); ?></span><br><br>
							<?php wp_nonce_field( 'edd_wpo_nonce', 'edd_wpo_nonce' ); ?>
                            <input type="submit" class="button-secondary" name="edd_wpo_license_deactivate"
                                   value="<?php _e( 'Deactivate License', 'phone-orders-for-woocommerce' ); ?>"/>
						<?php } else {
							if ( ! empty( $error ) ) { ?>
								<?php echo __( 'License is inactive:', 'phone-orders-for-woocommerce' ); ?>&nbsp;<span
                                        style="color:red;"><?php echo $error; ?></span><br><br>
							<?php }
							wp_nonce_field( 'edd_wpo_nonce', 'edd_wpo_nonce' ); ?>
                            <input type="submit" class="button-secondary" name="edd_wpo_license_activate"
                                   value="<?php _e( 'Activate License', 'phone-orders-for-woocommerce' ); ?>"/>
						<?php } ?>
                    </td>
                </tr>
                </tbody>
            </table>

        </form>
		<?php
	}

	function edd_wpo_register_option() {
		// creates our settings in the options table
		register_setting( 'edd_wpo_license', 'edd_wpo_license_key', array( $this, 'edd_sanitize_license' ) );
	}

	function edd_sanitize_license( $new ) {
		$old = get_option( 'edd_wpo_license_key' );
		if ( $old && $old != $new ) {
			delete_option( 'edd_wpo_license_status' ); // new license has been entered, so must reactivate
		}

		return $new;
	}

	/************************************
	 * this illustrates how to activate
	 * a license key
	 *************************************/

	function edd_wpo_activate_license() {

                    //var_dump($_POST);die;
		// listen for our activate button to be clicked
		if ( isset( $_POST['edd_wpo_license_activate'] ) ) {


			// run a quick security check
			if ( ! check_admin_referer( 'edd_wpo_nonce', 'edd_wpo_nonce' ) ) {
				return;
			} // get out if we didn't click the Activate button

			// retrieve the license from the database
			$license = trim( $_POST['edd_wpo_license_key'] );
			update_option( 'edd_wpo_license_key', $license );


			// data to send in our API request
			$api_params = array(
				'edd_action' => 'activate_license',
				'license'    => $license,
				'item_name'  => urlencode( WC_PHONE_ORDERS_ITEM_NAME ), // the name of our product in EDD
				'url'        => WC_PHONE_ORDERS_MAIN_URL,
			);

			// Call the custom API.
			$response = wp_remote_post( WC_PHONE_ORDERS_STORE_URL,
				array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

			// make sure the response came back okay
			if ( is_wp_error( $response ) ) {
				return false;
			}

			// decode the license data
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			// $license_data->license will be either "valid" or "invalid"

			update_option( 'edd_wpo_license_status', $license_data->license );
			update_option( 'edd_wpo_license_error', @$license_data->error );

		}
	}

	function edd_wpo_force_deactivate_license() {
		$this->_edd_wpo_deactivate_license();
	}

	private function _edd_wpo_deactivate_license() {
		// retrieve the license from the database
		$license = trim( get_option( 'edd_wpo_license_key' ) );


		// data to send in our API request
		$api_params = array(
			'edd_action' => 'deactivate_license',
			'license'    => $license,
			'item_name'  => urlencode( WC_PHONE_ORDERS_ITEM_NAME ), // the name of our product in EDD
			'url'        => WC_PHONE_ORDERS_MAIN_URL,
		);

		// Call the custom API.
		$response = wp_remote_post( WC_PHONE_ORDERS_STORE_URL,
			array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

		// make sure the response came back okay
		if ( is_wp_error( $response ) ) {
			return false;
		}

		// decode the license data
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		// $license_data->license will be either "deactivated" or "failed"
		if ( $license_data->license == 'deactivated' ) {
			delete_option( 'edd_wpo_license_status' );
		}
		delete_option( 'edd_wpo_license_error' );
	}

	function edd_wpo_deactivate_license() {

		// listen for our activate button to be clicked
		if ( isset( $_POST['edd_wpo_license_deactivate'] ) ) {

			// run a quick security check
			if ( ! check_admin_referer( 'edd_wpo_nonce', 'edd_wpo_nonce' ) ) {
				return;
			} // get out if we didn't click the Activate button

			$this->_edd_wpo_deactivate_license();
		}
	}

//***********  EDD LICENSE FUNCTIONS END  *************************************************************************************************************************************************************************************************************************************************

	function edd_wpo_check_license() {

		global $wp_version;

		$license = trim( get_option( 'edd_wpo_license_key' ) );

		$api_params = array(
			'edd_action' => 'check_license',
			'license'    => $license,
			'item_name'  => urlencode( WC_PHONE_ORDERS_ITEM_NAME ),
			'url'        => WC_PHONE_ORDERS_MAIN_URL,
		);

		// Call the custom API.
		$response = wp_remote_post( WC_PHONE_ORDERS_STORE_URL,
			array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		update_option( 'edd_wpo_license_status', $license_data->license );
		update_option( 'edd_wpo_license_error', @$license_data->error );
	}
}

WC_Phone_Orders_EDD::getInstance();