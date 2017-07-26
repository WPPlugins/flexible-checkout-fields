<?php
	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

    class inspireCheckoutFieldsSettings extends inspire_pluginDependant3 {
        public function __construct($plugin) {
            parent::__construct($plugin);

            $this->plugin = $plugin;

            add_action( 'admin_init', array($this, 'updateSettingsAction') );                        
            add_action( 'admin_menu', array($this, 'initAdminMenuAction'), 80);
            
            add_action( 'init', array($this, 'init_polylang') );
            add_action( 'admin_init', array($this, 'init_wpml') );
        }
        
        function init_polylang() {
        	if ( function_exists( 'pll_register_string' ) ) {
        		$settings = get_option('inspire_checkout_fields_settings', array() );
        		$checkout_field_type = $this->plugin->get_fields();
        		foreach ( $settings as $section ) {
        			if ( is_array( $section ) ) {
        				foreach ( $section as $field ) {
        					if ( isset( $field['label'] ) && $field['label'] != '' ) {
        						pll_register_string( $field['label'], $field['label'], __('Flexible Checkout Fields', 'flexible-checkout-fields' ) );
        					}
        					if ( isset( $field['placeholder'] ) && $field['placeholder'] != '' ) {
        						pll_register_string( $field['placeholder'], $field['placeholder'], __('Flexible Checkout Fields', 'flexible-checkout-fields' ) );
        					}
        					if ( isset( $field['type'] ) && isset( $checkout_field_type[$field['type']]['has_options'] ) && $checkout_field_type[$field['type']]['has_options'] ) {
        						$array_options = explode("\n", $field['option']);
        						if ( !empty( $array_options ) ){
        							foreach ( $array_options as $option ) {
        								$tmp = explode(':', $option, 2);
        								$option_label = trim( $tmp[1] );
        								pll_register_string( $option_label, $option_label, __('Flexible Checkout Fields', 'flexible-checkout-fields' ) );
        								unset($tmp);
        							}
        						}        						
        					}
        				}
        			}
        		}
        	}
        }
        
        function init_wpml() {
        	if ( function_exists( 'icl_register_string' ) ) {
        		$icl_language_code = defined('ICL_LANGUAGE_CODE') ? ICL_LANGUAGE_CODE : get_bloginfo('language');
        		$settings = get_option('inspire_checkout_fields_settings', array() );
        		$checkout_field_type = $this->plugin->get_fields();
        		foreach ( $settings as $section ) {
        			if ( is_array( $section ) ) {
        				foreach ( $section as $field ) {
        					if ( isset( $field['label'] ) && $field['label'] != '' ) {        						
        						icl_register_string( 'flexible-checkout-fields', $field['label'], $field['label'], false, $icl_language_code );
        					}
        					if ( isset( $field['placeholder'] ) && $field['placeholder'] != '' ) {
        						icl_register_string( 'flexible-checkout-fields', $field['placeholder'], $field['placeholder'], false, $icl_language_code );
        					}
        					if ( isset( $field['type'] ) && isset( $checkout_field_type[$field['type']]['has_options'] ) && $checkout_field_type[$field['type']]['has_options'] ) {
        						$array_options = explode("\n", $field['option']);
        						if ( !empty( $array_options ) ){
        							foreach ( $array_options as $option ) {
        								$tmp = explode(':', $option, 2);
        								$option_label = trim( $tmp[1] );
        								icl_register_string( 'flexible-checkout-fields', $option_label, $option_label, false, $icl_language_code );
        								unset($tmp);
        							}
        						}        						
        					}
        				}
        			}
        		}        		
        	}
        }
        
        /**
         * add new menu to woocommerce function.
         *
         * @access public
         * @param none
         * @return void
         */

        public function initAdminMenuAction() {
            add_submenu_page( 'woocommerce', __( 'Checkout Fields Settings', 'flexible-checkout-fields' ),  __( 'Checkout Fields', 'flexible-checkout-fields' ) , 'manage_woocommerce', 'inspire_checkout_fields_settings', array( $this, 'renderInspireCheckoutFieldsSettingsPage') );
        }

        /**
         * wordpress action
         *
         * renders plugin submenu page
         */
        public function renderInspireCheckoutFieldsSettingsPage() {
            global $woocommerce;

            $settings = get_option('inspire_checkout_fields_settings');

            $countries = new WC_Countries();
            $billing = $countries->get_address_fields($countries->get_base_country(), 'billing_');
            $shipping = $countries->get_address_fields($countries->get_base_country(), 'shipping_');
            
            if( empty( $settings ) || empty( $settings['order'] ) ) {        	
                $order = array(
                	'order_comments' => array(
                    'type'           => 'textarea',
                	'class'          => array('notes'),
                	'label'          => __( 'Order Notes', 'flexible-checkout-fields' ),
                	'placeholder'    => _x( 'Notes about your order, e.g. special notes for delivery.', 'placeholder', 'flexible-checkout-fields')
                	)
                );                
            }
            else {
           		$order = $settings['order'];
            }

            $checkout_fields = array_merge( array('billing' => $billing), array('shipping' => $shipping), array('order' => $order) );

        	foreach ( $this->plugin->sections as $custom_section => $custom_section_data ) {
        		if ( $custom_section_data['section'] == 'billing' || $custom_section_data['section'] == 'shipping' || $custom_section_data['section'] == 'order' ) continue;
        		if ( empty( $settings[$custom_section_data['section']] ) ) {
        			$checkout_fields[$custom_section_data['section']] = array();
        		}
        		else {
        			$checkout_fields[$custom_section_data['section']] = $settings[$custom_section_data['section']];
        		}

        	}

            $current_tab = ( empty( $_GET['tab'] ) ) ? 'fields_billing' : sanitize_text_field( urldecode( $_GET['tab'] ) );

            $args = array(
                    'current_tab' => $current_tab,
                    'tabs' => array(
                    		'settings'			=>	__( 'Settings', 'flexible-checkout-fields' ),
                    )
            );

            foreach ( $this->plugin->sections as $section => $section_data ) {
            	$args['tabs'][$section_data['tab']] = $section_data['tab_title'];
            }

            if ( !is_flexible_checkout_fields_pro_active() ) {
                $args['tabs']['pro'] = __( 'Custom Sections', 'flexible-checkout-fields' );
            }

            include( 'views/settings-tabs.php' );

            switch ($current_tab) {
                case 'settings':

                	$args = array(
                		'plugin' => $this->getPlugin()
                    );

                	include( 'views/settings-settings.php' );

                break;

                case 'checkboxes':
                    echo $this->loadTemplate('submenu_checkboxes', 'settings', array(
                            'plugin' => $this->getPlugin()
                        )
                    );
                break;

                case 'pro':

                    include( 'views/settings-pro.php' );

                    break;

                default:

                	$args = array(
                            'plugin' 			=> $this->getPlugin(),
                            'checkout_fields' 	=> $checkout_fields
                    );

                	include( 'views/settings-fields.php' );

                break;
            }

        }

         /**
          * save settings function.
          *
          * @access public
          * @param none
          * @return void
          */

        public function updateSettingsAction(){
            if ( !empty( $_POST ) ) {
                if ( !empty($_POST['option_page']) && in_array( $_POST['option_page'], array('inspire_checkout_fields_settings', 'inspire_checkout_fields_checkboxes') ) ) {
                    if ( !empty( $_POST[$this->getNamespace()] ) ) {
                        foreach ( $_POST[$this->getNamespace()] as $name => $value ) {                        	
                        	$settings = get_option( 'inspire_checkout_fields_' . $name, array() );
                        	if ( is_array( $value )) {
                        		foreach ( $value as $key => $val ) {
                        			$settings[$key] = $val;
                        			if ( isset( $_POST['reset_settings'] ) ) {
                        				unset( $settings[$key] );
                        			}
                        		}
                        	}
                        	else {
                        		$settings = $value;
                        	}
                            update_option( 'inspire_checkout_fields_' . $name, $settings );
                            $settings = get_option( 'inspire_checkout_fields_' . $name, array() );
                            $this->plugin->get_sections();
                        }
                    }
                    elseif ( empty( $_POST[$this->getNamespace()] ) && $_POST['option_page'] == 'inspire_checkout_fields_checkboxes' ) {
                        update_option('inspire_checkout_fields_checkboxes', '');
                    }
                }
            }
        }

    }
