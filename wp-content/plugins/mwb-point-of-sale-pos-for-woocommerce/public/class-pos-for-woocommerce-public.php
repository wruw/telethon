<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @author MakeWebBetter <webmaster@makewebbetter.com>
 * @package    MWB_Point_Of_Sale_Woocommerce
 * @subpackage MWB_Point_Of_Sale_Woocommerce/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 * namespace pos_for_woocommerce_public.
 *
 * @package    MWB_Point_Of_Sale_Woocommerce
 * @subpackage MWB_Point_Of_Sale_Woocommerce/public
 * @author     MakeWebBetter <webmaster@makewebbetter.com>
 */
class Pos_For_Woocommerce_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @author   MakeWebBetter <webmaster@makewebbetter.com>
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @author   MakeWebBetter <webmaster@makewebbetter.com>
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @author MakeWebBetter <webmaster@makewebbetter.com>
	 * @param      string $plugin_name       The name of the plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 * @author MakeWebBetter <webmaster@makewebbetter.com>
	 */
	public function mwb_pos_public_enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, POS_FOR_WOOCOMMERCE_DIR_URL . 'public/src/scss/pos-for-woocommerce-public.css', array(), $this->version, 'all' );
		wp_enqueue_style( 'mwb-pos-notifications', POS_FOR_WOOCOMMERCE_DIR_URL . 'public/src/scss/pos-for-woocommerce-notifications.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 * @author MakeWebBetter <webmaster@makewebbetter.com>
	 */
	public function mwb_pos_public_enqueue_scripts() {
		global $wp_query;
		if ( ! is_admin() ) {
			if ( isset( $wp_query->query['pagename'] ) && 'point-of-sale' === $wp_query->query['pagename'] ) {
				wp_register_script( $this->plugin_name, POS_FOR_WOOCOMMERCE_DIR_URL . 'public/src/js/pos-for-woocommerce-public.js', array( 'jquery' ), $this->version, false );
				wp_localize_script( $this->plugin_name, 'pfw_public_param', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
				wp_enqueue_script( $this->plugin_name );
				wp_enqueue_script( 'mwb-pos-app-js', POS_FOR_WOOCOMMERCE_DIR_URL . 'public/src/js/mwb-pos-app-build.js', array( 'jquery' ), time(), true );
			}
		}
	}

	/**
	 * Remove unwanted styles and scripts for pos page.
	 *
	 * @since    1.0.0
	 * @author MakeWebBetter <webmaster@makewebbetter.com>
	 */
	public function mwb_pos_remove_theme_styles() {
		global $wp_query;
		global $wp_styles, $wp_scripts;
		$stylesheet_uri = get_stylesheet_directory_uri();
		if ( ! is_admin() ) {
			if ( isset( $wp_query->query['pagename'] ) && 'point-of-sale' === $wp_query->query['pagename'] ) {
				foreach ( $wp_styles->queue as $handle ) {
					$obj        = $wp_styles->registered[ $handle ];
					$obj_handle = $obj->handle;
					$obj_uri    = $obj->src;
					if ( strpos( $obj_uri, $stylesheet_uri ) === 0 ) {
						wp_dequeue_style( $obj_handle );
					}
				}

				foreach ( $wp_scripts->queue as $handles ) {
					$objs        = $wp_scripts->registered[ $handles ];
					$obj_handles = $objs->handle;
					$obj_uris    = $objs->src;
					if ( strpos( $obj_uris, $stylesheet_uri ) === 0 ) {
						wp_dequeue_script( $obj_handles );
					}
				}
			}
		}
	}

	/**
	 * Remove inline styles and scripts for pos page.
	 *
	 * @param  array $styles collection of style urls.
	 * @return array $styles Return all style urls.
	 */
	public function mwb_pos_remve_inline_styles( $styles ) {
		global $wp_query;
		if ( ! is_admin() ) {
			if ( isset( $wp_query->query['pagename'] ) && 'point-of-sale' === $wp_query->query['pagename'] ) {
				if ( is_array( $styles ) && count( $styles ) > 0 ) {
					foreach ( $styles as $key => $code ) {
						if ( 'mwb-point-of-sale-woocommerce' === $code || 'mwb-pos-notifications' === $code ) {
							continue;
						} else {
							unset( $styles[ $key ] );
						}
					}
				}
			}
		}
		return $styles;
	}

	/**
	 * Remove inline styles and scripts for pos page.
	 *
	 * @since    1.0.0
	 * @author MakeWebBetter <webmaster@makewebbetter.com>
	 */
	public function mwb_pos_panel_remove_admin_bar() {
		global $wp_query;

		if ( ! is_admin() ) {
			if ( isset( $wp_query->query['pagename'] ) && 'point-of-sale' === $wp_query->query['pagename'] ) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Change the page template for POS panel on frontend.
	 *
	 * @param  string $template url of file selected.
	 * @return string $template return url of selected file.
	 * @since  1.0.0
	 * @author MakeWebBetter <webmaster@makewebbetter.com>
	 */
	public function mwb_pos_panel_template( $template ) {
		$mwb_pos_page = get_option( 'mwb_pos_page_exists', false );
		if ( isset( $mwb_pos_page ) && '' !== $mwb_pos_page ) {
			if ( is_page( $mwb_pos_page ) ) {
				$template = POS_FOR_WOOCOMMERCE_DIR_PATH . 'public/partials/pos-for-woocommerce-public-display.php';
			}
		}
		return $template;
	}

	/**
	 * Process all general settings data.
	 *
	 * @since  1.0.0
	 * @author MakeWebBetter <webmaster@makewebbetter.com>
	 */
	public function mwb_pos_config() {
		global $wp_filesystem;
		WP_Filesystem();
		check_ajax_referer( 'mwb-pos-operarions', 'security' );
		$manager_id                         = get_current_user_id();
		$mwb_pos_general_data               = $wp_filesystem->get_contents( POS_FOR_WOOCOMMERCE_DIR_PATH . 'components/settings-data/general-settings.json' );
		$mwb_pos_general_data               = json_decode( $mwb_pos_general_data, true );
		
		if ( is_user_logged_in() ) {
			$current_user = wp_get_current_user();
			$mwb_pos_get_current_user = get_user_by( 'id', get_current_user_id() );
			if ( ( $current_user instanceof WP_User ) ) {
				if ( current_user_can( 'read_post', $current_user->ID ) ) {

					$first_name = $current_user->first_name;
					
					$last_name = $current_user->last_name;
					$user_name = $first_name . ' ' . $last_name;
					$mwb_pos_general_data['mwb_user_name']   = esc_html__( 'Welcome: ', 'mwb-point-of-sale-woocommerce' ) . esc_html( $user_name );
				}
			}
		}

		$mwb_pos_general_data['profileimg'] = get_avatar_url( $manager_id, 32 );
		$mwb_pos_general_data['crr_symbol'] = get_woocommerce_currency_symbol();		
		echo wp_json_encode( $mwb_pos_general_data );
		wp_die();
	}

	/**
	 * Get woocommerce store currency symbol.
	 *
	 * @since  1.0.0
	 * @author MakeWebBetter <webmaster@makewebbetter.com>
	 */
	public function mwb_pos_get_currency_symb() {
		check_ajax_referer( 'mwb-pos-operarions', 'security' );
		$mwb_pos_currency_symbol = get_woocommerce_currency_symbol();
		echo wp_json_encode( $mwb_pos_currency_symbol );
		wp_die();
	}

	/**
	 * Validate user logged in or not.
	 *
	 * @since  1.0.0
	 * @author MakeWebBetter <webmaster@makewebbetter.com>
	 */
	public function mwb_pos_validate_user() {
		global $wp_filesystem;
		WP_Filesystem();
		check_ajax_referer( 'mwb-pos-operarions', 'security' );
		$mwb_posuser_data           = array();
		$mwb_pos_loging_settings  = $wp_filesystem->get_contents( POS_FOR_WOOCOMMERCE_DIR_PATH . 'components/settings-data/login-settings.json' );
		$mwb_posuser_data['data']   = json_decode( $mwb_pos_loging_settings, true );
		
		$mwb_pos_get_current_user = get_user_by( 'id', get_current_user_id() );
		$mwb_flag_check           = false;
		$mwb_allowed_user_roles   = get_option( 'mwb_pfw_user_roles', array() );
		if ( is_array( $mwb_allowed_user_roles ) && ! empty( $mwb_allowed_user_roles ) ) {
			foreach ( $mwb_pos_get_current_user->roles as $user_role ) {
				if ( in_array( $user_role, $mwb_allowed_user_roles, true ) ) {
					if ( current_user_can( 'read_post', $mwb_pos_get_current_user->ID ) ) {
						$mwb_flag_check = true;
					}
				}
			}
		}
		if ( ! is_wp_error( $mwb_pos_get_current_user ) ) {
			if ( is_array( $mwb_pos_get_current_user->roles ) && $mwb_flag_check ) {
				if ( current_user_can( 'read_post', $mwb_pos_get_current_user->ID ) ) {
					if ( is_user_logged_in() ) {
						$mwb_posuser_data['msg'] = 'success';
					} else {
						$mwb_posuser_data['msg'] = 'failed';
					}
				}
				
			} else {
				$mwb_posuser_data['msg'] = 'failed';
			}
		} else {
			$mwb_posuser_data['msg'] = 'failed';
		}

		echo wp_json_encode( $mwb_posuser_data );
		wp_die();
	}

	/**
	 * Get login page settings data.
	 *
	 * @since  1.0.0
	 * @author MakeWebBetter <webmaster@makewebbetter.com>
	 */
	public function mwb_pos_login_page_settings() {
		global $wp_filesystem;
		WP_Filesystem();
		check_ajax_referer( 'mwb-pos-operarions', 'security' );
		$mwb_pos_loging_settings = $wp_filesystem->get_contents( POS_FOR_WOOCOMMERCE_DIR_PATH . 'components/settings-data/login-settings.json' );
		$mwb_pos_loging_settings = json_decode( $mwb_pos_loging_settings, true );
		echo wp_json_encode( $mwb_pos_loging_settings );
		wp_die();
	}

	/**
	 * Check current user existance.
	 *
	 * @since  1.0.0
	 * @author MakeWebBetter <webmaster@makewebbetter.com>
	 */
	public function mwb_pos_check_user_existence() {
		check_ajax_referer( 'mwb-pos-operarions', 'security' );

		$mwb_flag_check     = false;
		$response_data      = array();
		$mwb_login_username = isset( $_POST['username'] ) ? sanitize_text_field( wp_unslash( $_POST['username'] ) ) : '';
		$mwb_login_userpass = isset( $_POST['userpass'] ) ? sanitize_text_field( wp_unslash( $_POST['userpass'] ) ) : '';
		$mwb_login_status   = isset( $_POST['remember'] ) ? sanitize_text_field( wp_unslash( $_POST['remember'] ) ) : false;
		if ( ( isset( $mwb_login_username ) && isset( $mwb_login_userpass ) ) && ( '' !== $mwb_login_username && '' !== $mwb_login_userpass ) ) {
			$creds                  = array();
			$creds['user_login']    = $mwb_login_username;
			$creds['user_password'] = $mwb_login_userpass;
			$creds['remember']      = $mwb_login_status;
			$user                   = wp_signon( $creds, false );

			if ( is_wp_error( $user ) ) {
				$mwb_pos_page_link = '';
				$mwb_pos_page = get_option( 'mwb_pos_page_exists', false );
				if ( isset( $mwb_pos_page ) && '' !== $mwb_pos_page ) {
					$mwb_pos_page_link = get_permalink( $mwb_pos_page );
				}
				$response_data['msg']  = 'failed';
				$response_data['link'] = $mwb_pos_page_link;
				echo wp_json_encode( $response_data );
			}
			$mwb_allowed_user_roles = get_option( 'mwb_pfw_user_roles', array() );
			if ( is_array( $mwb_allowed_user_roles ) && ! empty( $mwb_allowed_user_roles ) ) {
				foreach ( $user->roles as $user_role ) {
					if ( in_array( $user_role, $mwb_allowed_user_roles, true ) ) {
						if ( current_user_can( 'read_post', $user->ID ) ) {
							$mwb_flag_check = true;
						}
					}
				}
			}
			if ( ! is_wp_error( $user ) ) {
				if ( is_array( $user->roles ) && $mwb_flag_check ) {
					if ( current_user_can( 'read_post', $user->ID ) ){
						$user_id     = $user->ID;
						$user_login = $user->user_email;
						wp_set_current_user( $user_id );
						wp_set_auth_cookie( $user_id, true, false );

						$mwb_pos_page = get_option( 'mwb_pos_page_exists', false );
						if ( isset( $mwb_pos_page ) && '' !== $mwb_pos_page ) {
							$mwb_pos_page_link = get_permalink( $mwb_pos_page );
						}

						$response_data['msg']  = 'success';
						$response_data['link'] = $mwb_pos_page_link;
						echo wp_json_encode( $response_data );
					}
				} else {
					$mwb_pos_page = get_option( 'mwb_pos_page_exists', false );
					if ( isset( $mwb_pos_page ) && '' !== $mwb_pos_page ) {
						$mwb_pos_page_link = get_permalink( $mwb_pos_page );
					}
					$response_data['msg']  = 'failed';
					$response_data['link'] = $mwb_pos_page_link;
					echo wp_json_encode( $response_data );
				}
			}
		} else {
			$mwb_pos_page = get_option( 'mwb_pos_page_exists', false );
			if ( isset( $mwb_pos_page ) && '' !== $mwb_pos_page ) {
				$mwb_pos_page_link = get_permalink( $mwb_pos_page );
			}
			$response_data['msg']  = 'failed';
			$response_data['link'] = $mwb_pos_page_link;
			echo wp_json_encode( $response_data );
		}
		wp_die();
	}

	/**
	 * Current users logout process.
	 *
	 * @since  1.0.0
	 * @author MakeWebBetter <webmaster@makewebbetter.com>
	 */
	public function mwb_pos_logout_user() {
		if ( is_user_logged_in() ) {
			wp_logout();
			$mwb_pos_page = get_option( 'mwb_pos_page_exists', false );
			if ( isset( $mwb_pos_page ) && '' !== $mwb_pos_page ) {
				$mwb_pos_page_link = get_permalink( $mwb_pos_page );
			}
			echo esc_attr( $mwb_pos_page_link );
		}
		wp_die();
	}

	/**
	 * List all woocommerce product categories.
	 *
	 * @since  1.0.0
	 * @author MakeWebBetter <webmaster@makewebbetter.com>
	 */
	public function mwb_pos_prod_category() {
		check_ajax_referer( 'mwb-pos-operarions', 'security' );

		$cat_list       = array();
		$args           = array(
			'taxonomy'     => 'product_cat',
			'orderby'      => 'name',
			'hierarchical' => false,
			'hide_empty'   => true,
		);
		$all_categories = get_categories( $args );
		if ( is_array( $all_categories ) && ! empty( $all_categories ) ) {
			foreach ( $all_categories as $all_category ) {
				if ( isset( $all_category ) && 'uncategorized' !== $all_category->slug ) {
					$is_simple_or_variable = false;
					$all_products = get_posts(
						array(
							'post_type' => 'product',
							'numberposts' => -1,
							'post_status' => 'publish',
							'fields' => 'ids',
							'tax_query' => array(
								array(
									'taxonomy' => 'product_cat',
									'field' => 'slug',
									'terms' => $all_category->name,
									'operator' => 'IN',
								),
							),
						)
					);
					if ( is_array( $all_products ) && ! empty( $all_products ) ) {
						foreach ( $all_products as $product_id ) {
							$product = wc_get_product( $product_id );
							if ( $product->is_type( 'simple' ) || $product->is_type( 'variable' ) ) {

								$is_simple_or_variable = true;
							}
						}
					}
					if ( ! $is_simple_or_variable ) {
						continue;
					}
					$cat_list[] = array(
						'title' => $all_category->name,
						'cat_id' => $all_category->term_id,
						'slug' => $all_category->slug,
					);
				}
			}
			echo wp_json_encode( $cat_list );
		}

		wp_die();
	}

	/**
	 * Get product by category id.
	 *
	 * @since 1.0.0
	 * @author MakeWebBetter <webmaster@makewebbetter.com>
	 */
	public function mwb_pos_get_category_prod() {
		check_ajax_referer( 'mwb-pos-operarions', 'security' );
		$mwb_prod_cat_slug = isset( $_POST['category'] ) ? sanitize_text_field( wp_unslash( $_POST['category'] ) ) : '';
		if ( isset( $mwb_prod_cat_slug ) && 'all' !== $mwb_prod_cat_slug ) {
			$args = array(
				'post_status' => 'publish',
				'category' => array( $mwb_prod_cat_slug ),
			);
		} elseif ( isset( $mwb_prod_cat_slug ) && 'all' === $mwb_prod_cat_slug ) {
			$args = array(
				'post_status' => 'publish',
				'limit'  => -1,
			);
		}
		$posts    = wc_get_products( $args );
		$products = array();
		if ( is_array( $posts ) && ! empty( $posts ) ) {
			foreach ( $posts as $post ) {
				if ( isset( $post ) && 'simple' === $post->get_type() ) {
					$products[] = $this->mwb_pos_prepare_simple_product( $post );
				} elseif ( isset( $post ) && 'variable' === $post->get_type() ) {

					$product_variations = $post->get_available_variations();
					if ( is_array( $product_variations ) && ! empty( $product_variations ) ) {
						foreach ( $product_variations as $product_variation ) {
							$_products[] = $this->mwb_pos_get_variations( $post, $product_variation );
						}
					}
				}
			}
		}
		if ( is_array( $products ) && ! empty( $products ) ) {
			if ( is_array( $_products ) && ! empty( $_products ) ) {
				$products = array_merge( $products, $_products );
			}
			echo wp_json_encode( $products );
		}
		wp_die();
	}

	/**
	 * Get List of searched products.
	 *
	 * @since 1.0.0
	 * @author MakeWebBetter <webmaster@makewebbetter.com>
	 */
	public function mwb_pos_get_search_prod() {
		check_ajax_referer( 'mwb-pos-operarions', 'security' );
		$mwb_prod_search = isset( $_POST['search'] ) ? sanitize_text_field( wp_unslash( $_POST['search'] ) ) : '';

		if ( isset( $mwb_prod_search ) && '' !== $mwb_prod_search ) {
			$mwb_prod_search = strtolower( $mwb_prod_search );
			$_product    = array();
			$args        = array(
				'status' => 'publish',
				'limit'  => -1,
			);
			$allproducts = wc_get_products( $args );
			if ( is_array( $allproducts ) && ! empty( $allproducts ) ) {
				foreach ( $allproducts as $allproduct ) {

					if ( false !== strpos( $allproduct->get_slug(), $mwb_prod_search ) ) {

						if ( 'simple' === $allproduct->get_type() ) {
							$_product[] = $this->mwb_pos_prepare_simple_product( $allproduct );
						} elseif ( 'variable' === $allproduct->get_type() ) {
							$product_variations = $allproduct->get_available_variations();
							if ( is_array( $product_variations ) && ! empty( $product_variations ) ) {
								foreach ( $product_variations as $product_variation ) {
									$_products[] = $this->mwb_pos_get_variations( $allproduct, $product_variation );
								}
							}
						}
					} elseif ( false !== strpos( $allproduct->get_id(), $mwb_prod_search ) ) {

						if ( 'simple' === $allproduct->get_type() ) {
							$_product[] = $this->mwb_pos_prepare_simple_product( $allproduct );
						} elseif ( 'variable' === $allproduct->get_type() ) {
							$product_variations = $allproduct->get_available_variations();
							if ( is_array( $product_variations ) && ! empty( $product_variations ) ) {
								foreach ( $product_variations as $product_variation ) {
									$_products[] = $this->mwb_pos_get_variations( $allproduct, $product_variation );
								}
							}
						}
					}
				}
			}
		} else {
			$_product    = array();
			$args        = array(
				'status' => 'publish',
				'limit'  => -1,
			);
			$allproducts = wc_get_products( $args );
			if ( is_array( $allproducts ) && ! empty( $allproducts ) ) {
				foreach ( $allproducts as $allproduct ) {
					if ( 'simple' === $allproduct->get_type() ) {
						$_product[] = $this->mwb_pos_prepare_simple_product( $allproduct );
					} elseif ( 'variable' === $allproduct->get_type() ) {
						$product_variations = $allproduct->get_available_variations();
						if ( is_array( $product_variations ) && ! empty( $product_variations ) ) {
							foreach ( $product_variations as $product_variation ) {
								$_products[] = $this->mwb_pos_get_variations( $allproduct, $product_variation );
							}
						}
					}
				}
			}
		}
		
		if ( is_array( $_product ) && ! empty( $_product ) ) {
			if ( is_array( $_products ) && ! empty( $_products ) ) {
				$_product = array_merge( $_product, $_products );
			}
			echo wp_json_encode( $_product );
		}
		wp_die();
	}


	/**
	 * Get WooCommerce products.
	 *
	 * @since 1.0.0
	 * @author MakeWebBetter <webmaster@makewebbetter.com>
	 */
	public function mwb_pos_get_products() {
		check_ajax_referer( 'mwb-pos-operarions', 'security' );
		$_product = array();
		$args     = array(
			'status' => 'publish',
			'limit'  => -1,
		);
		$products = wc_get_products( $args );
		if ( is_array( $products ) && ! empty( $products ) ) {
			foreach ( $products as $product ) {
				if ( $product->is_type( 'simple' ) ) {
					$_product[] = $this->mwb_pos_prepare_simple_product( $product );
				} elseif ( $product->is_type( 'variable' ) ) {
					$product_variations = $product->get_available_variations();
					if ( is_array( $product_variations ) && ! empty( $product_variations ) ) {
						foreach ( $product_variations as $product_variation ) {
							$_products[] = $this->mwb_pos_get_variations( $product, $product_variation );
						}
					}
				}
			}
		}

		if ( is_array( $_products ) && ! empty( $_products ) ) {
			$_product = array_merge( $_product, $_products );
		}

		echo wp_json_encode( $_product );
		wp_die();
	}

	/**
	 * Prepare all simple products.
	 *
	 * @param array $prod_data collection of simple data array.
	 * @since    1.0.0
	 * @author MakeWebBetter <webmaster@makewebbetter.com>
	 */
	public function mwb_pos_prepare_simple_product( $prod_data ) {
		$updated_prod       = array();
		$product_categories = array();
		if ( isset( $prod_data ) ) {
			$updated_prod['prod_id']            = $prod_data->get_id();
			$updated_prod['name']               = $prod_data->get_name();
			$updated_prod['slug']               = $prod_data->get_slug();
			$updated_prod['sku']                = $prod_data->get_sku();
			$updated_prod['created_date']       = $prod_data->get_date_created();
			$updated_prod['modified_date']      = $prod_data->get_date_modified();
			$updated_prod['status']             = $prod_data->get_status();
			$updated_prod['featured']           = $prod_data->get_featured();
			$updated_prod['descp']              = $prod_data->get_description();
			$updated_prod['short_descp']        = $prod_data->get_short_description();
			$updated_prod['permalink']          = get_permalink( $prod_data->get_id() );
			$updated_prod['catalog_visibility'] = $prod_data->get_catalog_visibility();
			$updated_prod['prod_menu_order']    = $prod_data->get_menu_order();
			// product prices.
			$updated_prod['crr_symbol']     = get_woocommerce_currency_symbol();
			$updated_prod['price']          = $prod_data->get_price();
			$updated_prod['regular_price']  = $prod_data->get_regular_price();
			$updated_prod['sale_price']     = $prod_data->get_sale_price();
			$updated_prod['on_sale_from']   = $prod_data->get_date_on_sale_from();
			$updated_prod['on_sale_to']     = $prod_data->get_date_on_sale_to();
			$updated_prod['number_of_sale'] = $prod_data->get_total_sales();
			// Get Product Stock.
			$updated_prod['manage_stock']       = $prod_data->get_manage_stock();
			$updated_prod['stock_status']       = $prod_data->get_stock_status();
			$updated_prod['backorders_allowed'] = $prod_data->get_backorders();

			if ( 'instock' === $prod_data->get_stock_status() && '' !== $prod_data->get_stock_quantity() ) {
				$updated_prod['quantity'] = 'in stock (' . $prod_data->get_stock_quantity() . ' )';
			} elseif ( 'instock' === $prod_data->get_stock_status() && '' === $prod_data->get_stock_quantity() ) {
				$updated_prod['quantity'] = 'in stock';
			} elseif ( 'instock' !== $prod_data->get_stock_status() ) {
				$updated_prod['quantity'] = 'out of stock';
			}

			// Get Product image and gallery images.
			$updated_prod['main_image'] = wp_get_attachment_url( $prod_data->get_image_id() );
		}
		return $updated_prod;
	}

	/**
	 * Prepare all variable products and it's variations.
	 *
	 * @param array $prod_data collection of variable data array.
	 * @since    1.0.0
	 * @author MakeWebBetter <webmaster@makewebbetter.com>
	 */
	public function mwb_pos_prepare_variable_product( $prod_data ) {
		$updated_prod       = array();
		$product_categories = array();
		if ( isset( $prod_data ) ) {
			$updated_prod['prod_id']            = $prod_data->get_id();
			$updated_prod['name']               = $prod_data->get_name();
			$updated_prod['slug']               = $prod_data->get_slug();
			$updated_prod['sku']                = $prod_data->get_sku();
			$updated_prod['status']             = $prod_data->get_status();
			$updated_prod['featured']           = $prod_data->get_featured();
			$updated_prod['descp']              = $prod_data->get_description();
			$updated_prod['short_descp']        = $prod_data->get_short_description();
			$updated_prod['permalink']          = get_permalink( $prod_data->get_id() );
			$updated_prod['catalog_visibility'] = $prod_data->get_catalog_visibility();
			$updated_prod['prod_menu_order']    = $prod_data->get_menu_order();
			// product prices.
			$updated_prod['crr_symbol']     = get_woocommerce_currency_symbol();
			$updated_prod['price']          = $prod_data->get_price();
			$updated_prod['regular_price']  = $prod_data->get_regular_price();
			$updated_prod['sale_price']     = $prod_data->get_sale_price();
			$updated_prod['on_sale_from']   = $prod_data->get_date_on_sale_from();
			$updated_prod['on_sale_to']     = $prod_data->get_date_on_sale_to();
			$updated_prod['number_of_sale'] = $prod_data->get_total_sales();
			// Get product Stock.
			$updated_prod['manage_stock']       = $prod_data->get_manage_stock();
			$updated_prod['stock_status']       = $prod_data->get_stock_status();
			$updated_prod['backorders_allowed'] = $prod_data->get_backorders();

			if ( 'instock' === $prod_data->get_stock_status() && '' !== $prod_data->get_stock_quantity() ) {
				$updated_prod['quantity'] = 'in stock (' . $prod_data->get_stock_quantity() . ' )';
			} elseif ( 'instock' === $prod_data->get_stock_status() && '' === $prod_data->get_stock_quantity() ) {
				$updated_prod['quantity'] = 'in stock';
			} elseif ( 'instock' !== $prod_data->get_stock_status() ) {
				$updated_prod['quantity'] = 'out of stock';
			}

			// Get Product image and gallery images.
			$updated_prod['main_image'] = wp_get_attachment_url( $prod_data->get_image_id() );
		}
		return $updated_prod;
	}

	/**
	 * Collect all variations.
	 *
	 * @since 1.0.0
	 * @author MakeWebBetter <webmaster@makewebbetter.com>
	 * @param  object $prod_data    Holds product data.
	 * @param  array  $product_variation    Holds product variation data.
	 */
	public function mwb_pos_get_variations( $prod_data, $product_variation ) {
		// Get all avialable variations data.
		$prepare_variation_data = array();
		if ( is_array( $product_variation ) && ! empty( $product_variation ) ) {
			if ( isset( $product_variation['image_id'] ) && '' !== $product_variation['image_id'] ) {
				$variation_img = wp_get_attachment_image_src( $product_variation['image_id'], 'shop_thumbnail' )[0];
			} else {
				$variation_img = '';
			}

			$prepare_variation_data['prod_id']            = $product_variation['variation_id'];
			$prepare_variation_data['name']               = rtrim( $prod_data->get_name() . '-' . implode( '-', $product_variation['attributes'] ), '-' );
			$prepare_variation_data['attributes']         = $product_variation['attributes'];
			$prepare_variation_data['backorders_allowed'] = $product_variation['backorders_allowed'];
			$prepare_variation_data['price']              = $product_variation['display_price'];
			$prepare_variation_data['regular_price']      = $product_variation['display_regular_price'];
			$prepare_variation_data['stock_status']       = $product_variation['is_in_stock'];
			$prepare_variation_data['min_qty']            = $product_variation['min_qty'];
			$prepare_variation_data['max_qty']            = $product_variation['max_qty'];
			$prepare_variation_data['sku']                = $product_variation['sku'];
			$prepare_variation_data['short_descp']        = wp_strip_all_tags( $product_variation['variation_description'] );
			$prepare_variation_data['main_image']         = $variation_img;
			$prepare_variation_data['crr_symbol']         = get_woocommerce_currency_symbol();
			if ( $product_variation['is_in_stock'] && '' !== $product_variation['max_qty'] ) {
				$prepare_variation_data['quantity'] = 'in stock ( ' . $product_variation['max_qty'] . ' )';
			} elseif ( $product_variation['is_in_stock'] && '' === $product_variation['max_qty'] ) {
				$prepare_variation_data['quantity'] = 'in stock';
			} elseif ( ! $product_variation['is_in_stock'] ) {
				$prepare_variation_data['quantity'] = 'out of stock';
			}
		}
		return $prepare_variation_data;
	}

	/**
	 * Collect manager's data.
	 *
	 * @since 1.0.0
	 * @author MakeWebBetter <webmaster@makewebbetter.com>
	 */
	public function mwb_pos_current_manager_data() {
		check_ajax_referer( 'mwb-pos-operarions', 'security' );
		if ( is_user_logged_in() ) {
			$manager_data     = array();
			$manager_id       = get_current_user_id();
			$mwb_user_manager = get_userdata( $manager_id );
			if ( is_object( $mwb_user_manager ) ) {
				if ( isset( $mwb_user_manager->data ) && is_object( $mwb_user_manager->data ) ) {
					$manager_data['fname']      = get_user_meta( $manager_id, 'first_name', true );
					$manager_data['lname']      = get_user_meta( $manager_id, 'last_name', true );
					$manager_data['nicename']   = isset( $mwb_user_manager->data->user_nicename ) ? $mwb_user_manager->data->user_nicename : '';
					$manager_data['phone']      = get_user_meta( $manager_id, 'billing_phone', true );
					$manager_data['email']      = isset( $mwb_user_manager->data->user_email ) ? $mwb_user_manager->data->user_email : '';
					$manager_data['profileimg'] = get_avatar_url( $manager_id, 32 );
					$manager_data['id']         = isset( $mwb_user_manager->data->ID ) ? $mwb_user_manager->data->ID : '';
				}
			}
			echo wp_json_encode( $manager_data );
		}
		wp_die();
	}

	/**
	 * Update manager profile.
	 *
	 * @since 1.0.0
	 * @author MakeWebBetter <webmaster@makewebbetter.com>
	 */
	public function mwb_pos_update_manager_profile() {
		check_ajax_referer( 'mwb-pos-operarions', 'security' );
		$user_id = isset( $_POST['manager_key'] ) ? explode( '-', map_deep( wp_unslash( $_POST['manager_key'] ), 'sanitize_text_field' ) ) : '';

		if ( is_array( $user_id ) && isset( $user_id[1] ) && '' !== $user_id[1] ) {
			$current_user_id       = $user_id[1];
			$managers_updated_data = isset( $_POST['manager_data'] ) ? map_deep( wp_unslash( $_POST['manager_data'] ), 'sanitize_text_field' ) : array();
			if ( is_array( $managers_updated_data ) && ! empty( $managers_updated_data ) ) {
				$manager_update                  = array();
				$manager_update['ID']            = $current_user_id;
				$manager_update['user_email']    = isset( $managers_updated_data['mwb-pos-manager-email'] ) ? $managers_updated_data['mwb-pos-manager-email'] : '';
				$manager_update['user_nicename'] = isset( $managers_updated_data['mwb-pos-manager-nickName'] ) ? $managers_updated_data['mwb-pos-manager-nickName'] : '';
				$manager_update['first_name']    = isset( $managers_updated_data['mwb-pos-manager-fname'] ) ? $managers_updated_data['mwb-pos-manager-fname'] : '';
				$manager_update['last_name']     = isset( $managers_updated_data['mwb-pos-manager-lname'] ) ? $managers_updated_data['mwb-pos-manager-lname'] : '';

				wp_update_user( $manager_update );

				if ( isset( $managers_updated_data['mwb-pos-manager-phone'] ) && '' !== $managers_updated_data['mwb-pos-manager-phone'] ) {
					update_user_meta( $current_user_id, 'billing_phone', $managers_updated_data['mwb-pos-manager-phone'] );
				}
				echo 'success';
			}
		}
		wp_die();
	}


	/**
	 * Create customer order.
	 *
	 * @since 1.0.0
	 * @author MakeWebBetter <webmaster@makewebbetter.com>
	 */
	public function mwb_pos_order_details() {

		check_ajax_referer( 'mwb-pos-operarions', 'security' );
		$cart_items              = isset( $_POST['cartItems'] ) ? map_deep( wp_unslash( $_POST['cartItems'] ), 'sanitize_text_field' ) : '';
		$cart_additional_details = isset( $_POST['cartData'] ) ? map_deep( wp_unslash( $_POST['cartData'] ), 'sanitize_text_field' ) : '';
		$customer_details        = isset( $_POST['customerData'] ) ? map_deep( wp_unslash( $_POST['customerData'] ), 'sanitize_text_field' ) : '';

		if ( is_array( $cart_items ) && is_array( $cart_additional_details ) && is_array( $customer_details ) ) {
			$payment_method = isset( $cart_additional_details['paymentmode'] ) ? $cart_additional_details['paymentmode'] : '';
			if ( 'wallet' == $payment_method ) {

				$user_email = isset( $customer_details['mwb-pos-customer-email'] ) ? $customer_details['mwb-pos-customer-email'] : '';
				$order_total = isset( $cart_additional_details['ordertotal'] ) ? $cart_additional_details['ordertotal'] : 0;

				$user = get_user_by( 'email', $user_email );
				if ( isset( $user->ID ) && '' !== $user->ID ) {
					$result = $this->mwb_pos_validate_wallet_user( $user->ID, $order_total );
					if ( $result ) {
						$order = $this->mwb_pos_create_order( $cart_items, $cart_additional_details, $customer_details, 'wallet' );
						echo 'success';
					} else {

						echo 'failed';
					}
				} else {
					echo 'failed';
				}
			} else {

				$order = $this->mwb_pos_create_order( $cart_items, $cart_additional_details, $customer_details, 'without-wallet' );
				echo 'success';
			}
		} else {
			echo 'failed';
		}

		wp_die();
	}

	/**
	 * Validate user.
	 *
	 * @since 1.0.0
	 * @author MakeWebBetter <webmaster@makewebbetter.com>
	 * @param int $user_id user id.
	 * @param int $amount amount.
	 */
	public function mwb_pos_validate_wallet_user( $user_id, $amount ) {
		$result = true;
		$wallet_balance = get_user_meta( $user_id, 'mwb_wallet', true );
		if ( empty( $wallet_balance ) ) {
			$result = false;
		} elseif ( $amount > $wallet_balance ) {
			$result = false;
		}
		return $result;
	}

	/**
	 * Process the order data and create the orders for woocommerce.
	 *
	 * @since 1.0.0
	 * @author MakeWebBetter <webmaster@makewebbetter.com>
	 * @param      array $products        Holds product data.
	 * @param      array $cart_extra      Holds cart data.
	 * @param      array $cust_addr       Holds customers info.
	 * @param      array $type            Holds payment type.
	 */
	public function mwb_pos_create_order( $products, $cart_extra, $cust_addr, $type ) {
		global $woocommerce;
		$address = array(
			'first_name' => isset( $cust_addr['mwb-pos-customer-fname'] ) ? $cust_addr['mwb-pos-customer-fname'] : '',
			'last_name'  => isset( $cust_addr['mwb-pos-customer-lname'] ) ? $cust_addr['mwb-pos-customer-lname'] : '',
			'email'      => isset( $cust_addr['mwb-pos-customer-email'] ) ? $cust_addr['mwb-pos-customer-email'] : '',
			'phone'      => isset( $cust_addr['mwb-pos-customer-phone'] ) ? $cust_addr['mwb-pos-customer-phone'] : '',
			'address_1'  => isset( $cust_addr['mwb-pos-customer-address1'] ) ? $cust_addr['mwb-pos-customer-address1'] : '',
			'address_2'  => isset( $cust_addr['mwb-pos-customer-address2'] ) ? $cust_addr['mwb-pos-customer-address2'] : '',
			'city'       => isset( $cust_addr['mwb-pos-customer-city'] ) ? $cust_addr['mwb-pos-customer-city'] : '',
			'state'      => isset( $cust_addr['mwb-pos-customer-state'] ) ? $cust_addr['mwb-pos-customer-state'] : '',
			'postcode'   => isset( $cust_addr['mwb-pos-customer-postcode'] ) ? $cust_addr['mwb-pos-customer-postcode'] : '',
			'country'    => isset( $cust_addr['mwb-pos-customer-country'] ) ? $cust_addr['mwb-pos-customer-country'] : '',
		);
		// Now we create the order.
		$order = wc_create_order( array( 'customer_id' => get_current_user_id() ) );
		if ( is_array( $products ) && ! empty( $products ) ) {
			foreach ( $products as $product ) {
				$_product     = wc_get_product( $product['id'] );
				$product_type = $_product->get_type();
				if ( 'variation' === $product_type ) {
					$variation_args = $_product->get_attributes();
					$order->add_product( $_product, $product['qty'], $variation_args ); // This is an existing VARIATION product.
				} elseif ( 'simple' === $product_type ) {
					$order->add_product( $_product, $product['qty'] ); // This is an existing SIMPLE product.
				}
			}
		}

		$order->set_address( $address, 'billing' );
		$order->set_address( $address, 'shipping' );

		if ( is_array( $cart_extra ) && ! empty( $cart_extra ) ) {
			if ( isset( $cart_extra['shipping'] ) && '' !== $cart_extra['shipping'] ) {
				$shipping_rate = new WC_Shipping_Rate( '', 'Flat Rate', floatval( $cart_extra['shipping'] ), array(), 'custom_shipping_method' );
				$order->add_shipping( $shipping_rate );
			}
			// Get a new instance of the WC_Order_Item_Fee Object.

			if ( isset( $cart_extra['tax'] ) && '' !== $cart_extra['tax'] ) {

				$item_fee_tax = new WC_Order_Item_Fee();
				$item_fee_tax->set_name( 'Vat' );
				$item_fee_tax->set_amount( floatval( $cart_extra['tax'] ) ); // Fee amount.
				$item_fee_tax->set_tax_class( '' );
				$item_fee_tax->set_tax_status( 'taxable' );
				$item_fee_tax->set_total( floatval( $cart_extra['tax'] ) );
				$order->add_item( $item_fee_tax );
				$order->calculate_totals( false );
			}

			if ( ( isset( $cart_extra['couponName'] ) && isset( $cart_extra['couponValue'] ) ) && ( '' !== $cart_extra['couponName'] && '' !== $cart_extra['couponValue'] ) ) {

				$item_fee = new WC_Order_Item_Fee();
				$item_fee->set_name( 'Discount for coupon {' . $cart_extra['couponName'] . '} ' ); // Generic fee name.
				$item_fee->set_amount( -floatval( $cart_extra['couponValue'] ) ); // Fee amount.
				$item_fee->set_total( -floatval( $cart_extra['couponValue'] ) );
				$order->add_item( $item_fee );
				$order->calculate_totals( false );
			}
		}

		$order->update_status( 'completed', 'Imported POS Order', true );
		update_post_meta( $order->get_id(), '_payment_method', $cart_extra['paymentmode'] );
		update_post_meta( $order->get_id(), '_payment_method_title', $cart_extra['paymentmode'] );
		update_post_meta( $order->get_id(), 'mwb_pos_order', 'yes' );
		$order->save();
		if ( isset( $cust_addr['mwb-pos-customer-email'] ) && '' !== $cust_addr['mwb-pos-customer-email'] && 'wallet' === $type ) {
			$user = get_user_by( 'email', $cust_addr['mwb-pos-customer-email'] );
			if ( isset( $user->ID ) && '' !== $user->ID ) {
				if ( function_exists( 'mwb_wsfw_update_user_wallet_balance' ) ) {
					$result = mwb_wsfw_update_user_wallet_balance( $user->ID, $order->get_total(), $order->get_id() );
				}
			}
		}
		return $order;
	}

	/**
	 * Collect all orders related with current logged in user.
	 *
	 * @since 1.0.1
	 * @author MakeWebBetter <webmaster@makewebbetter.com>
	 */
	public function mwb_pos_get_manager_orders() {

		check_ajax_referer( 'mwb-pos-operarions', 'security' );
		global $woocommerce;
		$mwb_pos_orders_list = array();
		$manager_id          = get_current_user_id();
		if ( isset( $manager_id ) && '' !== $manager_id ) {
			$args   = array(
				'customer_id' => $manager_id,
				'meta_key' => 'mwb_pos_order',
				'meta_value' => 'yes',
				'limit' => -1,
			);
			$orders = wc_get_orders( $args );

			if ( is_array( $orders ) && ! empty( $orders ) ) {
				foreach ( $orders as $order ) {
					$mwb_pos_orders_list[] = array(
						'currency_symbol' => get_woocommerce_currency_symbol(),
						'order_status' => $order->get_status(),
						'order_id'     => '#order-' . $order->get_id(),
						'payment_method' => $order->get_payment_method(),
						'order_total'    => $order->get_total(),
						'customer_name'  => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
						'customer_address' => $order->get_billing_address_1() . ' ' . $order->get_billing_address_2() . ' ' . $order->get_billing_city() . ' ' . $order->get_billing_state() . ' ' . $order->get_billing_postcode() . ' ' . $order->get_billing_country(),
					);
				}
			}
		}
		echo wp_json_encode( $mwb_pos_orders_list );
		wp_die();
	}

	/**
	 * Check wallet plugin enable of not.
	 *
	 * @since 1.0.1
	 * @author MakeWebBetter <webmaster@makewebbetter.com>
	 */
	public function mwb_pos_get_wallet_details() {
		check_ajax_referer( 'mwb-pos-operarions', 'security' );
		$mwb_wallet_enable = '';
		if ( in_array( 'wallet-system-for-woocommerce/wallet-system-for-woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ), true ) ) {
			$mwb_wallet_enable = get_option( 'mwb_wsfw_enable', 'no' );
			$mwb_pos_wallet_enable = get_option( 'mwb_pfw_wallet_enable', 'no' );
			$mwb_wallet_enable = ( 'yes' == $mwb_pos_wallet_enable ) ?  $mwb_wallet_enable : $mwb_pos_wallet_enable;
		}
		echo wp_json_encode( $mwb_wallet_enable );
		wp_die();
	}

	/**
	 * Get reports for orders.
	 *
	 * @since 1.0.1
	 * @author MakeWebBetter <webmaster@makewebbetter.com>
	 */
	public function mwb_pos_collect_report() {
		check_ajax_referer( 'mwb-pos-operarions', 'security' );
		global $woocommerce;

		$mwb_posreport_type = isset( $_POST['report_type'] ) ? sanitize_text_field( wp_unslash( $_POST['report_type'] ) ) : '';
		if ( isset( $mwb_posreport_type ) && '' !== $mwb_posreport_type ) {
			$mwb_all_orders = array();
			$args           = array(
				'customer_id' => get_current_user_id(),
			);

			$arguments = array();

			$no_of_months = array(
				'Jan' => '01',
				'Feb' => '02',
				'Mar' => '03',
				'Apr' => '04',
				'May' => '05',
				'Jun' => '06',
				'Jul' => '07',
				'Aug' => '08',
				'Sep' => '09',
				'Oct' => '10',
				'Nov' => '11',
				'Dec' => '12',

			);
			switch ( $mwb_posreport_type ) {
				case 'yearly':
				foreach ( $no_of_months as $key => $month ) {
					$start_date_year = gmdate( 'Y' ) . '-' . $month . '-01';
					$end_date_year   = gmdate( 'Y' ) . '-' . $month . '-31';
					$arguments       = array(
						'date_created' => '' . $start_date_year . '...' . $end_date_year . '',
					);
					$mwb_all_orders[ $key ] = $this->mwb_pos_get_order_count_report( $arguments );
				}
				break;
				case 'monthly':
				$days_in_months = gmdate( 't' );

				for ( $i = 1; $i <= $days_in_months; $i++ ) {
					$start_date_year = gmdate( 'Y' ) . '-' . gmdate( 'm' ) . '-' . $i;
					$end_date_year   = gmdate( 'Y' ) . '-' . gmdate( 'm' ) . '-' . $i;
					$arguments       = array(
						'date_created' => '' . $start_date_year . '...' . $end_date_year . '',
					);
					$mwb_all_orders[ $i ] = $this->mwb_pos_get_order_count_report( $arguments );
				}

				break;
				case 'weekly':
				$days = array( 'Sun', 'Mon', 'Tue', 'Wed', 'Thus', 'Fri', 'Sat' );

				$today_date_year = gmdate( 'Y' ) . '-' . gmdate( 'm' ) . '-' . gmdate( 'd' );
				$current_week    = gmdate( 'W', strtotime( $today_date_year ) );
				$current_year    = gmdate( 'Y' );
				$week_start_date = gmdate( 'Y-m-d', strtotime( "{$current_year}-W{$current_week}-1" ) );
				$week_end_date   = gmdate( 'Y-m-d', strtotime( "{$current_year}-W{$current_week}-7" ) );
				$count = 0;
				$old_days = '';
				$arguments       = array(
					'date_created' => '' . $week_start_date . '...' . $week_end_date . '',
					'limit' => -1,
					'status' => array_keys( wc_get_order_statuses() ),
					'meta_key' => 'mwb_pos_order',
					'meta_value' => 'yes',
				);
				$orders              = wc_get_orders( $arguments );
				if ( is_array( $orders ) && ! empty( $orders ) ) {
					foreach ( $orders as $order ) {
						$active_day = $order->get_date_created()->format( 'D' );
						if ( $old_days != $active_day ) {
							$count = 0;
						}
						if ( is_array( $days ) && in_array( $active_day, $days ) ) {
							$old_days = $active_day;
							$count++;
							$mwb_data[ $order->get_date_created()->format( 'D' ) ] = $count;
							$deleted_days[] = $active_day;
						}
						$day_without_orders = array_diff( $days, $deleted_days );
						if ( is_array( $day_without_orders ) && ! empty( $day_without_orders ) ) {
							foreach ( $day_without_orders as $day_without_order ) {
								$mwb_data[ $day_without_order ] = 0;
							}
						}
					}
				}

				$mwb_all_orders = array();
				foreach ( $days as $k => $v ) {
					$mwb_all_orders[ $v ] = $mwb_data[ $v ];
				}
				break;
				case 'daily':
				$order_date_created = gmdate( 'Y' ) . '-' . gmdate( 'm' ) . '-' . gmdate( 'd' );
				$arguments          = array(
					'date_created' => '' . $order_date_created . '',
				);
				$mwb_all_orders[ $order_date_created ] = $this->mwb_pos_get_order_count_report( $arguments );
				break;
			}
		}

		echo wp_json_encode( $mwb_all_orders );
		wp_die();
	}

	/**
	 * Get reports for orders.
	 *
	 * @since 1.1.0
	 * @param array $arguments arguments.
	 * @author MakeWebBetter <webmaster@makewebbetter.com>
	 */
	public function mwb_pos_get_order_count_report( $arguments ) {
		$arguments['status'] = array_keys( wc_get_order_statuses() );
		$arguments['meta_key'] = 'mwb_pos_order';
		$arguments['meta_value'] = 'yes';
		$arguments['limit'] = -1;
		$orders = wc_get_orders( $arguments );
		$total_order = count( $orders );
		return $total_order;
	}

	/**
	 * Hide admin bar.
	 *
	 * @since 1.0.1
	 * @param bool $valid valid.
	 * @param bool $show_admin_bar show_admin_bar.
	 * @author MakeWebBetter <webmaster@makewebbetter.com>
	 */
	public function mwb_pos_remove_admin_bar_query_moniter( $valid, $show_admin_bar ) {
		global $wp_query;
		if ( ! is_admin() ) {
			if ( isset( $wp_query->query['pagename'] ) && 'point-of-sale' === $wp_query->query['pagename'] ) {
				$valid = false;
			}
		}
		return $valid;
	}

	/**
	 * Hide mobile menu for flatsome theme.
	 *
	 * @since 1.1.0
	 * @author MakeWebBetter <webmaster@makewebbetter.com>
	 */
	public function mwb_pos_remove_mobile_menu_flatsome() {
		global $wp_query;
		if ( ! is_admin() ) {
			if ( isset( $wp_query->query['pagename'] ) && 'point-of-sale' === $wp_query->query['pagename'] ) {
				remove_action('wp_footer', 'flatsome_mobile_menu', 7);
			}
		}
	}

}
