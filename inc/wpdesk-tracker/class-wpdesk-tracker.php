<?php
/**
 * WP Desk Tracker
 *
 * @class 		WPDESK_Tracker
 * @version		2.3.0
 * @package		WPDESK/Helper
 * @category	Class
 * @author 		WP Desk
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( !class_exists( 'WPDesk_Tracker' ) ) {

	class WPDesk_Tracker {

		public static $script_version = '11';

		public static $request_coupon = false;

		public static $plugin_basename = '';

		/**
		 * URL to the WP Desk Tracker API endpoint.
		 * @var string
		 */
		private static $api_url = 'https://data.wpdesk.org/?track=1';
		//private static $api_url = 'http://woo271.grola.pl/?track=1';

		/**
		 * Hook into cron event.
		 */
		public static function init( $plugin_basename ) {
			self::$plugin_basename = $plugin_basename;
			add_action( 'plugins_loaded', array( __CLASS__, 'load_plugin_text_domain') );
			add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_enqueue_scripts' ), 100 );
			add_action( 'wpdesk_tracker_send_event', array( __CLASS__, 'send_tracking_data' ) );
			add_action( 'admin_menu', array( __CLASS__, 'admin_menu' )  );
			add_action( 'admin_init', array( __CLASS__, 'admin_init' )  );
			add_action( 'admin_notices', array( __CLASS__, 'admin_notices' )  );
			add_action( 'wp_ajax_wpdesk_tracker_notice_handler', array( __CLASS__, 'wp_ajax_wpdesk_tracker_notice_handler' ) );
			add_action( 'wp_ajax_wpdesk_tracker_deactivation_handler', array( __CLASS__, 'wp_ajax_wpdesk_tracker_deactivation_handler' ) );
			add_action( 'update_option_wpdesk_helper_options', array( __CLASS__, 'update_option_wpdesk_helper_options' ), 10, 3 );

			add_filter( 'wpdesk_tracker_data', array( __CLASS__, 'wpdesk_tracker_data_license_emails' ) );
			add_filter( 'wpdesk_tracker_data', array( __CLASS__, 'wpdesk_tracker_data_shipping_country_per_order' ) );
			add_filter( 'wpdesk_tracker_data', array( __CLASS__, 'wpdesk_tracker_data_shipping_classes' ) );
			add_filter( 'wpdesk_tracker_data', array( __CLASS__, 'wpdesk_tracker_data_product_variations' ) );
			add_filter( 'wpdesk_tracker_data', array( __CLASS__, 'wpdesk_tracker_data_orders_per_month' ) );

			global $pagenow;
			if ( 'plugins.php' === $pagenow ) {
				add_action( 'admin_footer', array( __CLASS__, 'admin_footer' ) );
			}

			$options = get_option('wpdesk_helper_options', array() );
			if ( !is_array( $options ) ) {
				$options = array();
			}
			if ( empty( $options['wpdesk_tracker_agree'] ) ) {
				$options['wpdesk_tracker_agree'] = '0';
			}
			$wpdesk_tracker_agree = $options['wpdesk_tracker_agree'];
			$wp_next_scheduled = wp_next_scheduled( 'wpdesk_tracker_send_event' );
			if ( $wpdesk_tracker_agree == '1' && !$wp_next_scheduled ) {
				wp_schedule_event( time(), 'daily', 'wpdesk_tracker_send_event' );
			}
			if ( $wpdesk_tracker_agree == '0' && $wp_next_scheduled ) {
				wp_clear_scheduled_hook( 'wpdesk_tracker_send_event' );
			}
		}

		public static function load_plugin_text_domain() {
			$wpdesk_translation = load_plugin_textdomain( 'wpdesk-tracker', FALSE, self::$plugin_basename . '/inc/wpdesk-tracker/lang' );
		}

		public static function admin_footer() {
			if ( wpdesk_tracker_enabled() ) {
				$plugins = array(
					'wpdesk-helper/wpdesk-helper.php' => 'wpdesk-helper/wpdesk-helper.php'
				);
				$plugins = apply_filters( 'wpdesk_track_plugin_deactivation', $plugins );
				include( 'views/tracker-plugins-footer.php' );
			}
		}

		public static function admin_enqueue_scripts() {
			$screen = get_current_screen();
			if ( $screen->id == 'admin_page_wpdesk_tracker' || $screen->id == 'admin_page_wpdesk_tracker_deactivate' ) {
				wp_register_style( 'wpdesk-helper-tracker', plugin_dir_url( __FILE__ ) . 'assets/css/tracker.css', array(), self::$script_version, 'all' );
				wp_enqueue_style( 'wpdesk-helper-tracker' );
			}
		}

		public static function admin_menu() {
			add_submenu_page(
				null,
				'WP Desk Tracker',
				'WP Desk Tracker',
				'manage_options',
				'wpdesk_tracker',
				array( __CLASS__, 'wpdesk_tracker_page' )
			);
			add_submenu_page(
				null,
				'Deactivate plugin',
				'Deactivate plugin',
				'manage_options',
				'wpdesk_tracker_deactivate',
				array( __CLASS__, 'wpdesk_tracker_deactivate' )
			);
		}

		public static function wp_ajax_wpdesk_tracker_deactivation_handler() {
			self::send_deactivation_data();
		}


		public static function wp_ajax_wpdesk_tracker_notice_handler() {
			$type = '';
			if ( isset( $_REQUEST['type'] ) ) {
				$type = $_REQUEST['type'];
			}
			if ( $type == 'allow' ) {
				$options = get_option('wpdesk_helper_options', array() );
				if ( !is_array( $options ) ) {
					$options = array();
				}
				update_option( 'wpdesk_helper_options', $options );
				delete_option( 'wpdesk_tracker_notice' );
				$options['wpdesk_tracker_agree'] = '1';
				update_option( 'wpdesk_helper_options', $options );
			}
			if ( $type == 'dismiss' ) {
				$options = get_option('wpdesk_helper_options', array() );
				if ( !is_array( $options ) ) {
					$options = array();
				}
				delete_option( 'wpdesk_tracker_notice' );
				$options['wpdesk_tracker_agree'] = '0';
				update_option( 'wpdesk_helper_options', $options );
				update_option( 'wpdesk_tracker_notice', '1' );
			}
			if ( $type == 'allow_coupon' ) {
				self::$request_coupon = true;
				$options = get_option('wpdesk_helper_options', array() );
				if ( !is_array( $options ) ) {
					$options = array();
				}
				update_option( 'wpdesk_helper_options', $options );
				delete_option( 'wpdesk_tracker_notice' );
				$options['wpdesk_tracker_agree'] = '1';
				update_option( 'wpdesk_helper_options', $options );
			}
			if ( $type == 'dismiss_coupon' ) {
				$options = get_option('wpdesk_helper_options', array() );
				if ( !is_array( $options ) ) {
					$options = array();
				}
				delete_option( 'wpdesk_tracker_notice' );
				$options['wpdesk_tracker_agree'] = '0';
				update_option( 'wpdesk_helper_options', $options );
				update_option( 'wpdesk_tracker_notice', 'dismiss_all' );
			}
		}

		public static function update_option_wpdesk_helper_options( $old_value, $value, $option ) {
			if ( empty( $old_value ) ) {
				$old_value = array( 'wpdesk_tracker_agree' => '0' );
			}
			if ( empty( $old_value['wpdesk_tracker_agree'] ) ) {
				$old_value['wpdesk_tracker_agree'] = '0';
			}
			if ( empty( $value ) ) {
				$value = array( 'wpdesk_tracker_agree' => '0' );
			}
			if ( empty( $value['wpdesk_tracker_agree'] ) ) {
				$value['wpdesk_tracker_agree'] = '0';
			}
			if ( $old_value['wpdesk_tracker_agree'] == '0' ) {
				if ( $value['wpdesk_tracker_agree'] == '1' ) {
					self::send_tracking_data( true, 'agree' );
				}
			}
			if ( $old_value['wpdesk_tracker_agree'] == '1' ) {
				if ( $value['wpdesk_tracker_agree'] == '0' ) {
					self::send_tracking_data( true, 'no' );
					update_option( 'wpdesk_tracker_notice', 'dismiss_all' );
				}
			}
		}

		public static function admin_notices() {
			if ( !wpdesk_tracker_enabled() ) {
				return;
			}
			$screen = get_current_screen();
			$options = get_option('wpdesk_helper_options', array() );
			if ( !is_array( $options ) ) {
				$options = array();
			}
			if ( get_option( 'wpdesk_tracker_notice', '0' ) != 'dismiss_all' ) {
				if ( empty( $options['wpdesk_tracker_agree'] ) || $options['wpdesk_tracker_agree'] == '0' ) {
					$coupon_avaliable = false;
					if ( get_option( 'wpdesk_tracker_notice', '0' ) == '1' ) {
						$coupon_avaliable = true;
					}
					if ( in_array( $screen->id, apply_filters( 'wpdesk_tracker_notice_screens', array() ) ) ) {
						$user     = wp_get_current_user();
						$username = $user->first_name ? $user->first_name : $user->user_login;
						$terms_url = get_locale() == 'pl_PL' ? 'https://www.wpdesk.pl/dane-uzytkowania/' : 'https://www.wpdesk.net/usage-tracking/';
						include( 'views/tracker-notice.php' );
					}
				}
			}
			if ( $screen->id == 'plugins' ) {
				if ( isset( $_GET['wpdesk_tracker_opt_out'] ) ) {
					$options = get_option('wpdesk_helper_options', array() );
					if ( !is_array( $options ) ) {
						$options = array();
					}
					delete_option( 'wpdesk_tracker_notice' );
					$options['wpdesk_tracker_agree'] = '0';
					update_option( 'wpdesk_helper_options', $options );
					include( 'views/tracker-opt-out-notice.php' );
				}
			}
		}

		public static function wpdesk_tracker_page() {
			$user = wp_get_current_user();
			$username = $user->first_name ? $user->first_name : $user->user_login;
			$allow_url = admin_url( 'admin.php?page=wpdesk_tracker' );
			$allow_url = add_query_arg( 'plugin', $_GET['plugin'], $allow_url );
			$skip_url = $allow_url;
			$allow_url = add_query_arg( 'allow', '1', $allow_url );
			$skip_url = add_query_arg( 'allow', '0', $skip_url );
			$terms_url = get_locale() == 'pl_PL' ? 'https://www.wpdesk.pl/dane-uzytkowania/' : 'https://www.wpdesk.net/usage-tracking/';
			include( 'views/tracker-connect.php' );
		}

		public static function wpdesk_tracker_deactivate() {
			$user = wp_get_current_user();
			$username = $user->first_name;
			$plugin = $_GET['plugin'];
			$active_plugins = get_plugins();
			$plugin_name = $active_plugins[$plugin]['Name'];
			include( 'views/tracker-deactivate.php' );
		}

		public static function admin_init() {
			if ( isset( $_GET['page'] ) && $_GET['page'] == 'wpdesk_tracker' ) {
				if ( isset( $_GET['plugin'] ) && isset( $_GET['allow'] ) ) {
					$options = get_option('wpdesk_helper_options', array() );
					if ( !is_array( $options ) ) {
						$options = array();
					}
					if ( $_GET['allow'] == '0' ) {
						$options['wpdesk_tracker_agree'] = '0';
						update_option( 'wpdesk_helper_options', $options );
					}
					else {
						delete_option( 'wpdesk_tracker_notice' );
						update_option( 'wpdesk_tracker_agree', '1' );
						$options['wpdesk_tracker_agree'] = '1';
						update_option( 'wpdesk_helper_options', $options );
					}
					wp_redirect(admin_url( 'plugins.php' ) );
					exit();
				}
			}
		}

		public static function wpdesk_tracker_data_license_emails( $data ) {
			global $wpdesk_helper_plugins;
			$license_emails_email = array();
			$license_emails = array();
			if ( ! isset( $wpdesk_helper_plugins ) ) $wpdesk_helper_plugins = array();
			foreach ( $wpdesk_helper_plugins as $key => $plugin ) {
				if ( isset( $plugin['api_manager'] ) ) {
					$api_manager = $plugin['api_manager'];
					if ( isset( $api_manager->options[$api_manager->activation_email] ) ) {
						$license_emails_email[ $api_manager->options[ $api_manager->activation_email ] ] = $api_manager->options[ $api_manager->activation_email ];
					}
				}
			}
			foreach ( $license_emails_email as $email ) {
				$license_emails[] = $email;
			}
			$data['license_emails'] = $license_emails;
			return $data;
		}

		public static function wpdesk_tracker_data_shipping_country_per_order( $data ) {
			global $wpdb;
			$query = $wpdb->get_results("
            	SELECT m.meta_value AS shipping_country, p.post_status AS post_status , COUNT(p.ID) AS orders
            	FROM {$wpdb->postmeta} m, {$wpdb->posts} p
            	WHERE p.ID = m.post_id
            	AND m.meta_key = '_shipping_country'
            	GROUP BY shipping_country, post_status ORDER BY orders DESC"
			);
			$data['shipping_country_per_order'] = array();
			if ( $query ) {
				foreach ( $query as $row ) {
					if ( !isset( $data['shipping_country_per_order'][$row->shipping_country] ) ) {
						$data['shipping_country_per_order'][$row->shipping_country] = array();
					}
					$data['shipping_country_per_order'][$row->shipping_country][$row->post_status] = $row->orders;
				}
			}
			return $data;
		}

		public static function wpdesk_tracker_data_shipping_classes( $data ) {
			$data['number_of_shipping_classes'] = 0;
			$shipping_classes = WC()->shipping()->get_shipping_classes();
			if ( is_array( $shipping_classes ) ) {
				$data['number_of_shipping_classes'] = count( $shipping_classes );
			}
			return $data;
		}

		public static function wpdesk_tracker_data_product_variations( $data ) {
			$data['number_of_variations'] = 0;
			$number_of_variations = wp_count_posts( 'product_variation' );
			$data['number_of_variations'] = $number_of_variations;
			return $data;
		}

		public static function wpdesk_tracker_data_orders_per_month( $data ) {
			global $wpdb;
			$query = $wpdb->get_results("
            	SELECT min(post_date) min, max(post_date) max, TIMESTAMPDIFF(MONTH, min(post_date), max(post_date) )+1 months
            	FROM {$wpdb->posts} p
            	WHERE p.post_type = 'shop_order'
            	AND p.post_status = 'wc-completed'
            	"
			);
			$data['orders_per_month'] = array();
			if ( $query ) {
				foreach ( $query as $row ) {
					$data['orders_per_month']['first'] = $row->min;
					$data['orders_per_month']['last'] = $row->max;
					$data['orders_per_month']['months'] = $row->months;
					if ( $row->months != 0 ) {
						if ( isset( $data['orders'] ) && isset( $data['orders']['wc-completed'] ) ) {
							$data['orders_per_month']['per_month'] = floatval($data['orders']['wc-completed'])/floatval($row->months);
						}
					}
				}
			}
			return $data;
		}


		public static function send_deactivation_data() {

			$params = array();

			$params['click_action'] = 'plugin_deactivation';

			$params['plugin'] = $_REQUEST['plugin'];

			$params['plugin_name'] = $_REQUEST['plugin_name'];

			$params['reason'] = $_REQUEST['reason'];

			if ( !empty( $_REQUEST['additional_info'] ) ) {
				$params['additional_info'] = $_REQUEST['additional_info'];
			}
			$response = wp_remote_post( self::$api_url, array(
					'method'      => 'POST',
					'timeout'     => 5,
					'redirection' => 5,
					'httpversion' => '1.0',
					'blocking'    => false,
					'headers'     => array( 'user-agent' => 'WPDeskTracker' ),
					'body'        => json_encode( $params ),
					'cookies'     => array(),
				)
			);
		}


		/**
		 * Decide whether to send tracking data or not.
		 *
		 * @param boolean $override
		 */
		public static function send_tracking_data( $override = false, $click_action = null ) {
			$options = get_option('wpdesk_helper_options', array() );
			if ( empty( $options ) ) {
				$options = array();
			}
			if ( empty( $options['wpdesk_tracker_agree'] ) ) {
				$options['wpdesk_tracker_agree'] = '0';
			}
			if ( empty( $click_action ) && $options['wpdesk_tracker_agree'] == '0' ) {
				return;
			}
			if ( ! wpdesk_tracker_enabled() ) {
				return;
			}
			// Dont trigger this on AJAX Requests
			if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
				//return;
			}
			if ( ! apply_filters( 'wpdesk_tracker_send_override', $override ) ) {
				// Send a maximum of once per week by default.
				$last_send = self::get_last_send_time();
				if ( $last_send && $last_send > apply_filters( 'wpdesk_tracker_last_send_interval', strtotime( '-1 week' ) ) ) {
					return;
				}
			} else {
				// Make sure there is at least a 1 hour delay between override sends, we dont want duplicate calls due to double clicking links.
				$last_send = self::get_last_send_time();
				if ( empty( $click_action ) && $last_send && $last_send > strtotime( '-1 hours' ) ) {
					return;
				}
			}

			// Update time first before sending to ensure it is set
			update_option( 'wpdesk_tracker_last_send', time() );

			if ( empty( $click_action ) || $click_action == 'agree' ) {
				$params = self::get_tracking_data();

				if ( isset( $params['active_plugins'] ) ) {
					foreach ( $params['active_plugins'] as $plugin=>$plugin_data ) {
						$option_name = 'plugin_activation_' . $plugin;
						$activation_date = get_option( $option_name, '' );
						if ( $activation_date != '' ) {
							$params['active_plugins'][$plugin]['activation_date'] = $activation_date;
						}
					}
				}

				if ( !empty( $click_action ) ) {
					$params['click_action'] = 'agree';
				}
				if ( self::$request_coupon ) {
					$params['get_coupon'] = 1;
					$params['click_action'] = 'agree_coupon';
				}
			}
			else {
				$params = array( 'click_action' => 'no' );
			}

			$params['localhost'] = 'no';
			if ( !empty( $_SERVER['SERVER_ADDR'] ) && $_SERVER['SERVER_ADDR'] == '127.0.0.1' ) {
				$params['localhost'] = 'yes';
			}

			$response = wp_remote_post( self::$api_url, array(
					'method'      => 'POST',
					'timeout'     => 5,
					'redirection' => 5,
					'httpversion' => '1.0',
					'blocking'    => false,
					'headers'     => array( 'user-agent' => 'WPDeskTracker' ),
					'body'        => json_encode( $params ),
					'cookies'     => array(),
				)
			);
		}

		/**
		 * Get the last time tracking data was sent.
		 * @return int|bool
		 */
		private static function get_last_send_time() {
			return apply_filters( 'wpdesk_tracker_last_send_time', get_option( 'wpdesk_tracker_last_send', false ) );
		}

		/**
		 * Get all the tracking data.
		 * @return array
		 */
		private static function get_tracking_data() {
			$data = array();

			// General site info
			$data['url']   = home_url();
			$data['email'] = apply_filters( 'wpdesk_tracker_admin_email', get_option( 'admin_email' ) );
			$data['theme'] = self::get_theme_info();

			// WordPress Info
			$data['wp'] = self::get_wordpress_info();

			// Server Info
			$data['server'] = self::get_server_info();

			// Plugin info
			$all_plugins              = self::get_all_plugins();
			$data['active_plugins']   = $all_plugins['active_plugins'];
			$data['inactive_plugins'] = $all_plugins['inactive_plugins'];

			// Jetpack & WooCommerce Connect
			$data['jetpack_version']    = defined( 'JETPACK__VERSION' ) ? JETPACK__VERSION : 'none';
			$data['jetpack_connected']  = ( class_exists( 'Jetpack' ) && is_callable( 'Jetpack::is_active' ) && Jetpack::is_active() ) ? 'yes' : 'no';
			$data['jetpack_is_staging'] = ( class_exists( 'Jetpack' ) && is_callable( 'Jetpack::is_staging_site' ) && Jetpack::is_staging_site() ) ? 'yes' : 'no';
			$data['connect_installed']  = class_exists( 'WC_Connect_Loader' ) ? 'yes' : 'no';
			$data['connect_active']     = ( class_exists( 'WC_Connect_Loader' ) && wp_next_scheduled( 'wc_connect_fetch_service_schemas' ) ) ? 'yes' : 'no';

			// Store count info
			$data['users']    = self::get_user_counts();
			$data['products'] = self::get_product_counts();
			$data['orders']   = self::get_order_counts();

			// Payment gateway info
			$data['gateways'] = self::get_active_payment_gateways();

			// Shipping method info
			$data['shipping_methods'] = self::get_active_shipping_methods();

			// Get all WooCommerce options info
			$data['settings'] = self::get_all_woocommerce_options_values();

			// Template overrides
			$data['template_overrides'] = self::get_all_template_overrides();

			// Template overrides
			$data['admin_user_agents'] = self::get_admin_user_agents();

			return apply_filters( 'wpdesk_tracker_data', $data );
		}

		/**
		 * Get the current theme info, theme name and version.
		 * @return array
		 */
		public static function get_theme_info() {
			$theme_data        = wp_get_theme();
			$theme_child_theme = is_child_theme() ? 'Yes' : 'No';
			$theme_wc_support  = ( ! current_theme_supports( 'woocommerce' ) && ! in_array( $theme_data->template, wc_get_core_supported_themes() ) ) ? 'No' : 'Yes';

			return array( 'name'        => $theme_data->Name,
			              'version'     => $theme_data->Version,
			              'child_theme' => $theme_child_theme,
			              'wc_support'  => $theme_wc_support
			);
		}

		/**
		 * Get WordPress related data.
		 * @return array
		 */
		private static function get_wordpress_info() {
			$wp_data = array();

			$memory = wc_let_to_num( WP_MEMORY_LIMIT );

			if ( function_exists( 'memory_get_usage' ) ) {
				$system_memory = wc_let_to_num( @ini_get( 'memory_limit' ) );
				$memory        = max( $memory, $system_memory );
			}

			$wp_data['memory_limit'] = size_format( $memory );
			$wp_data['debug_mode']   = ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? 'Yes' : 'No';
			$wp_data['locale']       = get_locale();
			$wp_data['version']      = get_bloginfo( 'version' );
			$wp_data['multisite']    = is_multisite() ? 'Yes' : 'No';

			return $wp_data;
		}

		/**
		 * Get server related info.
		 * @return array
		 */
		private static function get_server_info() {
			$server_data = array();

			if ( isset( $_SERVER['SERVER_SOFTWARE'] ) && ! empty( $_SERVER['SERVER_SOFTWARE'] ) ) {
				$server_data['software'] = $_SERVER['SERVER_SOFTWARE'];
			}

			if ( function_exists( 'phpversion' ) ) {
				$server_data['php_version'] = phpversion();
			}

			if ( function_exists( 'ini_get' ) ) {
				$server_data['php_post_max_size']  = size_format( wc_let_to_num( ini_get( 'post_max_size' ) ) );
				$server_data['php_time_limt']      = ini_get( 'max_execution_time' );
				$server_data['php_max_input_vars'] = ini_get( 'max_input_vars' );
				$server_data['php_suhosin']        = extension_loaded( 'suhosin' ) ? 'Yes' : 'No';
			}

			global $wpdb;
			$server_data['mysql_version'] = $wpdb->db_version();

			$server_data['php_max_upload_size']  = size_format( wp_max_upload_size() );
			$server_data['php_default_timezone'] = date_default_timezone_get();
			$server_data['php_soap']             = class_exists( 'SoapClient' ) ? 'Yes' : 'No';
			$server_data['php_fsockopen']        = function_exists( 'fsockopen' ) ? 'Yes' : 'No';
			$server_data['php_curl']             = function_exists( 'curl_init' ) ? 'Yes' : 'No';

			return $server_data;
		}

		/**
		 * Get all plugins grouped into activated or not.
		 * @return array
		 */
		private static function get_all_plugins() {
			// Ensure get_plugins function is loaded
			if ( ! function_exists( 'get_plugins' ) ) {
				include ABSPATH . '/wp-admin/includes/plugin.php';
			}

			$plugins             = get_plugins();
			$active_plugins_keys = get_option( 'active_plugins', array() );
			$active_plugins      = array();

			foreach ( $plugins as $k => $v ) {
				// Take care of formatting the data how we want it.
				$formatted         = array();
				$formatted['name'] = strip_tags( $v['Name'] );
				if ( isset( $v['Version'] ) ) {
					$formatted['version'] = strip_tags( $v['Version'] );
				}
				if ( isset( $v['Author'] ) ) {
					$formatted['author'] = strip_tags( $v['Author'] );
				}
				if ( isset( $v['Network'] ) ) {
					$formatted['network'] = strip_tags( $v['Network'] );
				}
				if ( isset( $v['PluginURI'] ) ) {
					$formatted['plugin_uri'] = strip_tags( $v['PluginURI'] );
				}
				if ( in_array( $k, $active_plugins_keys ) ) {
					// Remove active plugins from list so we can show active and inactive separately
					unset( $plugins[ $k ] );
					$active_plugins[ $k ] = $formatted;
				} else {
					$plugins[ $k ] = $formatted;
				}
			}

			return array( 'active_plugins' => $active_plugins, 'inactive_plugins' => $plugins );
		}

		/**
		 * Get user totals based on user role.
		 * @return array
		 */
		private static function get_user_counts() {
			$user_count          = array();
			$user_count_data     = count_users();
			$user_count['total'] = $user_count_data['total_users'];

			// Get user count based on user role
			foreach ( $user_count_data['avail_roles'] as $role => $count ) {
				$user_count[ $role ] = $count;
			}

			return $user_count;
		}

		/**
		 * Get product totals based on product type.
		 * @return array
		 */
		private static function get_product_counts() {
			$product_count          = array();
			$product_count_data     = wp_count_posts( 'product' );
			$product_count['total'] = $product_count_data->publish;

			$product_statuses = get_terms( 'product_type', array( 'hide_empty' => 0 ) );
			foreach ( $product_statuses as $product_status ) {
				$product_count[ $product_status->name ] = $product_status->count;
			}

			return $product_count;
		}

		/**
		 * Get order counts based on order status.
		 * @return array
		 */
		private static function get_order_counts() {
			$order_count      = array();
			$order_count_data = wp_count_posts( 'shop_order' );

			foreach ( wc_get_order_statuses() as $status_slug => $status_name ) {
				$order_count[ $status_slug ] = $order_count_data->{$status_slug};
			}

			return $order_count;
		}

		/**
		 * Get a list of all active payment gateways.
		 * @return array
		 */
		private static function get_active_payment_gateways() {
			$active_gateways = array();
			$gateways        = WC()->payment_gateways->payment_gateways();
			foreach ( $gateways as $id => $gateway ) {
				if ( isset( $gateway->enabled ) && 'yes' === $gateway->enabled ) {
					$active_gateways[ $id ] = array( 'title' => $gateway->title, 'supports' => $gateway->supports );
				}
			}

			return $active_gateways;
		}

		/**
		 * Get a list of all active shipping methods.
		 * @return array
		 */
		private static function get_active_shipping_methods() {
			$active_methods   = array();
			$shipping_methods = WC()->shipping->get_shipping_methods();
			foreach ( $shipping_methods as $id => $shipping_method ) {
				if ( isset( $shipping_method->enabled ) && 'yes' === $shipping_method->enabled ) {
					$active_methods[ $id ] = array( 'title'      => $shipping_method->title,
					                                'tax_status' => $shipping_method->tax_status
					);
				}
			}

			return $active_methods;
		}

		/**
		 * Get all options starting with woocommerce_ prefix.
		 * @return array
		 */
		private static function get_all_woocommerce_options_values() {
			return array(
				'version'                               => WC()->version,
				'currency'                              => get_woocommerce_currency(),
				'base_location'                         => WC()->countries->get_base_country(),
				'selling_locations'                     => WC()->countries->get_allowed_countries(),
				'api_enabled'                           => get_option( 'woocommerce_api_enabled' ),
				'weight_unit'                           => get_option( 'woocommerce_weight_unit' ),
				'dimension_unit'                        => get_option( 'woocommerce_dimension_unit' ),
				'download_method'                       => get_option( 'woocommerce_file_download_method' ),
				'download_require_login'                => get_option( 'woocommerce_downloads_require_login' ),
				'calc_taxes'                            => get_option( 'woocommerce_calc_taxes' ),
				'coupons_enabled'                       => get_option( 'woocommerce_enable_coupons' ),
				'guest_checkout'                        => get_option( 'woocommerce_enable_guest_checkout' ),
				'secure_checkout'                       => get_option( 'woocommerce_force_ssl_checkout' ),
				'enable_signup_and_login_from_checkout' => get_option( 'woocommerce_enable_signup_and_login_from_checkout' ),
				'enable_myaccount_registration'         => get_option( 'woocommerce_enable_myaccount_registration' ),
				'registration_generate_username'        => get_option( 'woocommerce_registration_generate_username' ),
				'registration_generate_password'        => get_option( 'woocommerce_registration_generate_password' ),
			);
		}

		/**
		 * Look for any template override and return filenames.
		 * @return array
		 */
		private static function get_all_template_overrides() {
			$override_data  = array();
			$template_paths = apply_filters( 'woocommerce_template_overrides_scan_paths', array( 'WooCommerce' => WC()->plugin_path() . '/templates/' ) );
			$scanned_files  = array();

			require_once( WC()->plugin_path() . '/includes/admin/class-wc-admin-status.php' );

			foreach ( $template_paths as $plugin_name => $template_path ) {
				$scanned_files[ $plugin_name ] = WC_Admin_Status::scan_template_files( $template_path );
			}

			foreach ( $scanned_files as $plugin_name => $files ) {
				foreach ( $files as $file ) {
					if ( file_exists( get_stylesheet_directory() . '/' . $file ) ) {
						$theme_file = get_stylesheet_directory() . '/' . $file;
					} elseif ( file_exists( get_stylesheet_directory() . '/woocommerce/' . $file ) ) {
						$theme_file = get_stylesheet_directory() . '/woocommerce/' . $file;
					} elseif ( file_exists( get_template_directory() . '/' . $file ) ) {
						$theme_file = get_template_directory() . '/' . $file;
					} elseif ( file_exists( get_template_directory() . '/woocommerce/' . $file ) ) {
						$theme_file = get_template_directory() . '/woocommerce/' . $file;
					} else {
						$theme_file = false;
					}

					if ( false !== $theme_file ) {
						$override_data[] = basename( $theme_file );
					}
				}
			}

			return $override_data;
		}

		/**
		 * When an admin user logs in, there user agent is tracked in user meta and collected here.
		 * @return array
		 */
		private static function get_admin_user_agents() {
			return array_filter( (array) get_option( 'woocommerce_tracker_ua', array() ) );
		}
	}

}
