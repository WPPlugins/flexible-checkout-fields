<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'WPDesk_Flexible_Checkout_Fields_Tracker' ) ) {
	class WPDesk_Flexible_Checkout_Fields_Tracker {

		public static $script_version = '11';

		public function __construct() {
			$this->hooks();
		}

		public function hooks() {
			add_filter( 'wpdesk_tracker_data', array( $this, 'wpdesk_tracker_data' ), 11 );
			add_filter( 'wpdesk_tracker_notice_screens', array( $this, 'wpdesk_tracker_notice_screens' ) );
			add_filter( 'wpdesk_track_plugin_deactivation', array( $this, 'wpdesk_track_plugin_deactivation' ) );

			add_filter( 'plugin_action_links_flexible-checkout-fields/flexible-checkout-fields.php', array( $this, 'plugin_action_links' ) );
			add_action( 'activated_plugin', array( $this, 'activated_plugin' ), 10, 2 );
		}

		public function wpdesk_track_plugin_deactivation( $plugins ) {
			$plugins['flexible-checkout-fields/flexible-checkout-fields.php'] = 'flexible-checkout-fields/flexible-checkout-fields.php';
			return $plugins;
		}

		public function wpdesk_tracker_data( $data ) {
			$plugin_data = array(
				'fields'            => array(),
				'custom_sections'   => array(),
				'conditional_logic_for_fields'                  => 0,
				'conditional_logic_fields_action'               => array(),
				'conditional_logic_fields_operator'             => array(),
				'conditional_logic_for_fields_rules'            => 0,
				'conditional_logic'                             => 0,
				'conditional_logic_what'                        => array(),
				'conditional_logic_action'                      => array(),
				'conditional_logic_operator'                    => array(),
				'conditional_logic_rules'                       => 0,
			);
			
			if ( is_flexible_checkout_fields_pro_active() ) {
				$plugin_data['pro'] = 'yes';
			}
			else {
				$plugin_data['pro'] = 'no';
			}

			$settings = get_option('inspire_checkout_fields_settings', array() );
			if ( !is_array( $settings )) {
				$settings = array();
			}
			foreach ( $settings as $section => $fields ) {
				foreach ( $fields as $field ) {
					if ( isset( $field['conditional_logic_fields'] ) ) {
						$plugin_data['conditional_logic_for_fields']++;
						if ( empty( $plugin_data['conditional_logic_fields_action'][$field['conditional_logic_fields_action']] ) ) {
							$plugin_data['conditional_logic_fields_action'][$field['conditional_logic_fields_action']] = 0;
						}
						$plugin_data['conditional_logic_fields_action'][$field['conditional_logic_fields_action']]++;
						if ( empty( $plugin_data['conditional_logic_fields_operator'][$field['conditional_logic_fields_operator']] ) ) {
							$plugin_data['conditional_logic_fields_operator'][$field['conditional_logic_fields_operator']] = 0;
						}
						$plugin_data['conditional_logic_fields_operator'][$field['conditional_logic_fields_operator']]++;
						if ( isset( $field['conditional_logic_fields_rules'] ) ) {
							$plugin_data['conditional_logic_for_fields_rules'] = $plugin_data['conditional_logic_for_fields_rules'] + count( $field['conditional_logic_fields_rules'] );
						}
					}
					if ( isset( $field['conditional_logic'] ) ) {
						$plugin_data['conditional_logic']++;
						if ( empty( $plugin_data['conditional_logic_action'][$field['conditional_logic_action']] ) ) {
							$plugin_data['conditional_logic_action'][$field['conditional_logic_action']] = 0;
						}
						$plugin_data['conditional_logic_action'][$field['conditional_logic_action']]++;
						if ( empty( $plugin_data['conditional_logic_operator'][$field['conditional_logic_operator']] ) ) {
							$plugin_data['conditional_logic_operator'][$field['conditional_logic_operator']] = 0;
						}
						$plugin_data['conditional_logic_operator'][$field['conditional_logic_operator']]++;
						if ( isset( $field['conditional_logic_rules'] ) ) {
							$plugin_data['conditional_logic_rules'] = $plugin_data['conditional_logic_rules'] + count( $field['conditional_logic_rules'] );
							foreach ( $field['conditional_logic_rules'] as $rule ) {
								if ( !isset( $plugin_data['conditional_logic_what'][$rule['what']] ) ) {
									$plugin_data['conditional_logic_what'][$rule['what']] = 0;
								}
								$plugin_data['conditional_logic_what'][$rule['what']]++;
							}
						}
					}
					if ( isset( $field['custom_field'] ) && $field['custom_field'] == '1' ) {
						if ( isset( $field['type'] ) ) {
							if ( empty( $plugin_data['fields'][$field['type']] ) ) {
								$plugin_data['fields'][$field['type']] = 0;
							}
							$plugin_data['fields'][$field['type']]++;
						}
					}
				}
			}

			$plugin_data['inspire_checkout_fields_css_disable'] = get_option( 'inspire_checkout_fields_css_disable', '0' );

			$plugin_data['custom_sections']['before_customer_details'] = get_option( 'inspire_checkout_fields_before_customer_details', '0' );
			$plugin_data['custom_sections']['after_customer_details'] = get_option( 'inspire_checkout_fields_after_customer_details', '0' );
			$plugin_data['custom_sections']['before_checkout_billing_form'] = get_option( 'inspire_checkout_fields_before_checkout_billing_form', '0' );
			$plugin_data['custom_sections']['after_checkout_billing_form'] = get_option( 'inspire_checkout_fields_after_checkout_billing_form', '0' );
			$plugin_data['custom_sections']['before_checkout_shipping_form'] = get_option( 'inspire_checkout_fields_before_checkout_shipping_form', '0' );
			$plugin_data['custom_sections']['after_checkout_shipping_form'] = get_option( 'inspire_checkout_fields_after_checkout_shipping_form', '0' );
			$plugin_data['custom_sections']['before_checkout_registration_form'] = get_option( 'inspire_checkout_fields_before_checkout_registration_form', '0' );
			$plugin_data['custom_sections']['after_checkout_registration_form'] = get_option( 'inspire_checkout_fields_after_checkout_registration_form', '0' );
			$plugin_data['custom_sections']['before_order_notes'] = get_option( 'inspire_checkout_fields_before_order_notes', '0' );
			$plugin_data['custom_sections']['after_order_notes'] = get_option( 'inspire_checkout_fields_after_order_notes', '0' );
			$plugin_data['custom_sections']['review_order_before_submit'] = get_option( 'inspire_checkout_fields_review_order_before_submit', '0' );
			$plugin_data['custom_sections']['review_order_after_submit'] = get_option( 'inspire_checkout_fields_review_order_after_submit', '0' );

			$data['flexible_checkout_fields'] = $plugin_data;

			return $data;
		}

		public function wpdesk_tracker_notice_screens( $screens ) {
			$current_screen = get_current_screen();
			if ( $current_screen->id == 'woocommerce_page_inspire_checkout_fields_settings' ) {
				$screens[] = $current_screen->id;
			}
			return $screens;
		}

		public function plugin_action_links( $links ) {
			if ( !wpdesk_tracker_enabled() ) {
				return $links;
			}
			$options = get_option('wpdesk_helper_options', array() );
			if ( empty( $options['wpdesk_tracker_agree'] ) ) {
				$options['wpdesk_tracker_agree'] = '0';
			}
			$plugin_links = array();
			if ( $options['wpdesk_tracker_agree'] == '0' ) {
				$opt_in_link = admin_url( 'admin.php?page=wpdesk_tracker&plugin=flexible-checkout-fields/flexible-checkout-fields.php' );
				$plugin_links[] = '<a href="' . $opt_in_link . '">' . __( 'Opt-in', 'flexible-checkout-fields' ) . '</a>';
			}
			else {
				$opt_in_link = admin_url( 'plugins.php?wpdesk_tracker_opt_out=1&plugin=flexible-checkout-fields/flexible-checkout-fields.php' );
				$plugin_links[] = '<a href="' . $opt_in_link . '">' . __( 'Opt-out', 'flexible-checkout-fields' ) . '</a>';
			}
			return array_merge( $plugin_links, $links );
		}

		public function activated_plugin( $plugin, $network_wide ) {
			if ( !wpdesk_tracker_enabled() ) {
				return;
			}
			if ( $plugin == 'flexible-checkout-fields/flexible-checkout-fields.php' ) {
				$options = get_option('wpdesk_helper_options', array() );

				if ( empty( $options ) ) {
					$options = array();
				}
				if ( empty( $options['wpdesk_tracker_agree'] ) ) {
					$options['wpdesk_tracker_agree'] = '0';
				}
				$wpdesk_tracker_skip_plugin = get_option( 'wpdesk_tracker_skip_flexible_checkout_fields', '0' );
				if ( $options['wpdesk_tracker_agree'] == '0' && $wpdesk_tracker_skip_plugin == '0' ) {
					update_option( 'wpdesk_tracker_notice', '1' );
					update_option( 'wpdesk_tracker_skip_flexible_checkout_fields', '1' );
					wp_redirect( admin_url( 'admin.php?page=wpdesk_tracker&plugin=flexible-checkout-fields/flexible-checkout-fields.php' ) );
					exit;
				}
			}
		}

	}

	new WPDesk_Flexible_Checkout_Fields_Tracker();

}

if ( !function_exists( 'wpdesk_activated_plugin_activation_date' ) ) {
	function wpdesk_activated_plugin_activation_date( $plugin, $network_wide ) {
		$option_name = 'plugin_activation_' . $plugin;
		$activation_date = get_option( $option_name, '' );
		if ( $activation_date == '' ) {
			$activation_date = current_time( 'mysql' );
			update_option( $option_name, $activation_date );
		}
	}
	add_action( 'activated_plugin', 'wpdesk_activated_plugin_activation_date', 10, 2 );
}
