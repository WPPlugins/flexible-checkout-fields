<?php
/*
    Plugin Name: Flexible Checkout Fields
    Plugin URI: https://www.wpdesk.net/products/flexible-checkout-fields-pro-woocommerce/
    Description: Manage your WooCommerce checkout fields. Change order, labels, placeholders and add new fields.
    Version: 1.5.1
    Author: WP Desk
    Author URI: https://www.wpdesk.net/
    Text Domain: flexible-checkout-fields
    Domain Path: /lang/

    Copyright 2017 WP Desk Ltd.

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

*/

    if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

	if ( !function_exists( 'wpdesk_is_plugin_active' ) ) {
		function wpdesk_is_plugin_active( $plugin_file ) {

			$active_plugins = (array) get_option( 'active_plugins', array() );

			if ( is_multisite() ) {
				$active_plugins = array_merge( $active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
			}

			return in_array( $plugin_file, $active_plugins ) || array_key_exists( $plugin_file, $active_plugins );
		}
	}

    require_once('class/inspire/plugin3.php');
    require_once('class/inspire/pluginDependant3.php');
    require_once('class/inspire/pluginPostTypeFactory3.php');
    require_once('class/inspire/pluginPostType3.php');

    require_once('class/inspireCheckoutFieldsSettings.php');

	require_once('inc/wpdesk-woo27-functions.php');

	require_once('class/tracker.php');

    class inspireCheckoutFields extends inspire_Plugin3 {
        private static $_oInstance = false;

        protected $_pluginNamespace = 'inspire_checkout_fields';
        protected $_textDomain = 'flexible-checkout-fields';
        protected $_templatePath = 'inspire_checkout_fields_templates';

        protected $fields = array();

        public $sections = array();

        public $all_sections = array();

        public $page_size = array();

        public function __construct() {
            $this->_initBaseVariables();

            // load locales
            load_plugin_textdomain('flexible-checkout-fields', FALSE, dirname(plugin_basename(__FILE__)) . '/lang/');

            $this->init_fields();

            $this->inspireCheckoutFieldsSettings = new inspireCheckoutFieldsSettings($this);

            if ( is_admin() ) {
                add_action( 'admin_enqueue_scripts', array($this, 'initAdminCssAction'), 75 );
                add_action( 'admin_enqueue_scripts', array($this, 'initAdminJsAction'), 75 );
            }

            add_action( 'wp_enqueue_scripts', array($this, 'initPublicCssAction'), 75 );
            add_action( 'wp_enqueue_scripts', array($this, 'initPublicJsAction'), 75 );

            add_action( 'woocommerce_checkout_fields', array( $this, 'changeCheckoutFields' ), 9999 );
            add_action( 'woocommerce_checkout_update_order_meta', array($this, 'updateCheckoutFields'), 9 );

            add_action( 'woocommerce_admin_billing_fields', array($this, 'changeAdminBillingFields'), 9999 );
            add_action( 'woocommerce_admin_shipping_fields', array($this, 'changeAdminShippingFields'), 9999 );
            add_action( 'woocommerce_admin_order_fields', array($this, 'changeAdminOrderFields'), 9999 );

            add_action( 'woocommerce_admin_order_data_after_billing_address', array($this, 'addCustomBillingFieldsToAdmin') );
            add_action( 'woocommerce_admin_order_data_after_shipping_address', array($this, 'addCustomShippingFieldsToAdmin') );
            add_action( 'woocommerce_admin_order_data_after_shipping_address', array($this, 'addCustomOrderFieldsToAdmin') );

            add_action( 'woocommerce_thankyou', array($this, 'addCustomFieldsToReview'), 75);
            add_action( 'woocommerce_email_order_meta', array($this, 'addCustomFieldsToEmail'), 195);
            add_action( 'woocommerce_view_order', array($this, 'addCustomFieldsToReview'), 195);

            add_action( 'show_user_profile', array( $this, 'addCustomUserFieldsAdmin'), 75 );
            add_action( 'edit_user_profile', array( $this, 'addCustomUserFieldsAdmin'), 75 );

            add_action( 'personal_options_update', array( $this, 'saveCustomUserFieldsAdmin') );
            add_action( 'edit_user_profile_update',  array( $this, 'saveCustomUserFieldsAdmin') );

            add_action( 'woocommerce_edit_address_slugs', array($this, 'changeEditAddressSlugToEnglish'), 95);

            add_action( 'woocommerce_billing_fields', array($this, 'addCustomFieldsBillingFields'), 9999 );
            add_action( 'woocommerce_shipping_fields', array($this, 'addCustomFieldsShippingFields'), 9999 );
            add_action( 'woocommerce_order_fields', array($this, 'addCustomFieldsOrderFields'), 9999 );


            add_action( 'woocommerce_before_checkout_form', array( $this, 'woocommerce_before_checkout_form' ), 10 );
            add_action( 'woocommerce_before_edit_address_form_shipping', array( $this, 'woocommerce_before_checkout_form' ), 10 );
            add_action( 'woocommerce_before_edit_address_form_billing', array( $this, 'woocommerce_before_checkout_form' ), 10 );

            add_filter( 'flexible_chekout_fields_fields', array( $this, 'getCheckoutFields'), 10, 2 );

            add_filter( 'flexible_checkout_fields_field_tabs', array( 'inspireCheckoutFields', 'flexible_checkout_fields_field_tabs' ), 10 );

            add_action( 'flexible_checkout_fields_field_tabs_content', array( 'inspireCheckoutFields', 'flexible_checkout_fields_field_tabs_content'), 10, 4 );

            //add_action( 'woocommerce_get_country_locale_default', array( $this, 'woocommerce_get_country_locale_default' ), 11 );
			//do użycia dla pola miasto, kod pocztowy i stan
            $this->get_sections();
        }

        public function get_sections() {
        	$sections = array(
        			'billing' => array(
        					'section'			=> 'billing',
        					'tab'				=> 'fields_billing',
        					'tab_title'			=> __( 'Billing', 'flexible-checkout-fields' ),
        					'custom_section' 	=> false
        			),
        			'shipping' => array(
        					'section'			=> 'shipping',
        					'tab'				=> 'fields_shipping',
        					'tab_title'			=> __( 'Shipping', 'flexible-checkout-fields' ),
        					'custom_section' 	=> false
        			),
        			'order' => array(
        					'section'			=> 'order',
        					'tab'				=> 'fields_order',
        					'tab_title'			=> __( 'Order', 'flexible-checkout-fields' ),
        					'custom_section' 	=> false
        			)
        	);

            $all_sections = unserialize( serialize( $sections ) );

        	$this->sections = apply_filters( 'flexible_checkout_fields_sections', $sections );

        	$this->all_sections = apply_filters( 'flexible_checkout_fields_all_sections', $all_sections );

        }

        function init_fields() {

        	$this->fields['text'] = array(
        			'name'	=> __( 'Single Line Text', 'flexible-checkout-fields' )
        	);

        	$this->fields['textarea'] = array(
        			'name'	=> __( 'Paragraph Text', 'flexible-checkout-fields' )
        	);
        }

        function pro_fields( $fields ) {
            $add_fields = array();

            $add_fields['inspirecheckbox'] = array(
                'name' 				=> __( 'Checkbox', 'flexible-checkout-fields-pro' ),
                'pro'               => true
            );

            $add_fields['inspireradio'] = array(
                'name' 					=> __( 'Radio button', 'flexible-checkout-fields-pro' ),
                'pro'                   => true
            );

            $add_fields['select'] = array(
                'name' 					=> __( 'Select (Drop Down)', 'flexible-checkout-fields-pro' ),
                'pro'                   => true
            );

            $add_fields['datepicker'] = array(
                'name' 					=> __( 'Date', 'flexible-checkout-fields-pro' ),
                'pro'                   => true
            );

            $add_fields['timepicker'] = array(
                'name' 					=> __( 'Time', 'flexible-checkout-fields-pro'),
                'pro'                   => true
            );

            $add_fields['colorpicker'] = array(
                'name' 					=> __( 'Color Picker', 'flexible-checkout-fields-pro' ),
                'pro'                   => true
            );

            $add_fields['heading'] = array(
                'name' 					=> __( 'Headline', 'flexible-checkout-fields-pro' ),
                'pro'                   => true
            );

            $add_fields['info'] = array(
                'name' 					=> __( 'HTML', 'flexible-checkout-fields-pro' ),
                'pro'                   => true
            );

            $add_fields['file'] = array(
                'name' 					=> __( 'File Upload', 'flexible-checkout-fields-pro' ),
                'pro'                   => true
            );

            foreach ( $add_fields as $key => $field ) {
                $fields[$key] = $field;
            }

            return $fields;

        }

        public function get_fields() {
            $this->fields = $this->pro_fields( $this->fields );
        	return apply_filters( 'flexible_checkout_fields_fields' , $this->fields );
        }

        function get_settings() {
            $default = array(
            );
            $settings = get_option('inspire_checkout_fields_settings', $default );
            return $settings;
        }

        function woocommerce_before_checkout_form() {
        	WC()->session->set( 'checkout-fields', array() );
            $settings = $this->get_settings();
            $args = array( 'settings' => $settings );
            include( 'views/before-checkout-form.php' );
        }


        public function changeEditAddressSlugToEnglish($slug) {
            $slug['billing'] = 'billing';
            $slug['shipping'] = 'shipping';

            return $slug;
        }


        /**
         * wordpress action
         *
         * inits css
         */
        public function initAdminCssAction() {
            wp_enqueue_style( 'jquery-ui-style', '//ajax.googleapis.com/ajax/libs/jqueryui/' . '1.9.2' . '/themes/smoothness/jquery-ui.css' );
            wp_enqueue_style( 'inspire_checkout_fields_admin_style', $this->getPluginUrl() . '/assets/css/admin.css' );
        }

        public function initPublicCssAction() {

            if(is_checkout() || is_account_page()){
                if( $this->getSettingValue('css_disable') != 1 ){
                    wp_enqueue_style( 'jquery-ui-style', '//ajax.googleapis.com/ajax/libs/jqueryui/' . '1.9.2' . '/themes/smoothness/jquery-ui.css' );
                }

                wp_enqueue_style( 'inspire_checkout_fields_public_style', $this->getPluginUrl() . '/assets/css/front.css' );
            }
        }

        /**
         * wordpress action
         *
         * inits js
         */
        public function initAdminJsAction() {
            wp_enqueue_script( 'jquery' );
            wp_enqueue_script( 'jquery-ui' );
            wp_enqueue_script( 'jquery-ui-sortable' );
            wp_enqueue_script( 'jquery-ui-tooltip' );
            wp_enqueue_script( 'inspire_checkout_fields_admin_js', $this->getPluginUrl() . '/assets/js/admin.js', array(), '1.1' );
            wp_enqueue_script( 'jquery-ui-datepicker' );

            $labels_and_packing_list_params = array(
                'plugin_url' => $this->getPluginUrl()
            );
        }

        public function initPublicJsAction() {
    		if ( is_checkout() || is_account_page() ) {
                wp_enqueue_script( 'jquery' );
                wp_enqueue_script( 'jquery-ui' );
                wp_enqueue_script( 'jquery-ui-datepicker' );
                add_action( 'wp_enqueue_scripts', array( $this, 'wp_localize_jquery_ui_datepicker' ), 1000 );

                wp_register_script( 'inspire_checkout_fields_checkout_js', $this->getPluginUrl() . '/assets/js/checkout.js', array(), '1.1.1' );
                $translation_array = array(
                		'uploading' => __( 'Uploading file...', 'flexible-checkout-fields' ),
                );
                wp_localize_script( 'inspire_checkout_fields_checkout_js', 'words', $translation_array );
                wp_enqueue_script( 'inspire_checkout_fields_checkout_js' );
            }
        }

        function wp_localize_jquery_ui_datepicker() {
        	global $wp_locale;
        	global $wp_version;

        	if ( ! wp_script_is( 'jquery-ui-datepicker', 'enqueued' ) || version_compare( $wp_version, '4.6' ) != -1 ) {
        		return;
        	}

        	// Convert the PHP date format into jQuery UI's format.
        	$datepicker_date_format = str_replace(
        			array(
        					'd', 'j', 'l', 'z', // Day.
        					'F', 'M', 'n', 'm', // Month.
        					'Y', 'y'            // Year.
        			),
        			array(
        					'dd', 'd', 'DD', 'o',
        					'MM', 'M', 'm', 'mm',
        					'yy', 'y'
        			),
        			get_option( 'date_format' )
        			);

        	$datepicker_defaults = wp_json_encode( array(
        			'closeText'       => __( 'Close' ),
        			'currentText'     => __( 'Today' ),
        			'monthNames'      => array_values( $wp_locale->month ),
        			'monthNamesShort' => array_values( $wp_locale->month_abbrev ),
        			'nextText'        => __( 'Next' ),
        			'prevText'        => __( 'Previous' ),
        			'dayNames'        => array_values( $wp_locale->weekday ),
        			'dayNamesShort'   => array_values( $wp_locale->weekday_abbrev ),
        			'dayNamesMin'     => array_values( $wp_locale->weekday_initial ),
        			'dateFormat'      => $datepicker_date_format,
        			'firstDay'        => absint( get_option( 'start_of_week' ) ),
        			'isRTL'           => $wp_locale->is_rtl(),
        	) );

        	wp_add_inline_script( 'jquery-ui-datepicker', "jQuery(document).ready(function(jQuery){jQuery.datepicker.setDefaults({$datepicker_defaults});});" );
        }

        public function getCheckoutFields( $fields, $request_type = null ) {
            $settings = $this->get_settings();

            $checkout_field_type = $this->get_fields();
			if ( !empty( $settings ) ) {
				$new = array();
				$priority = 0;
                foreach ( $settings as $key => $type ) {
                	if ( $key != 'billing' && $key != 'shipping' && $key != 'order' ) {
                		if ( get_option('inspire_checkout_fields_' . $key, '0' ) == '0' ) {
                			continue;
                		}
                	}
                	if ( !is_array( $type ) ) {
                		continue;
                	}
                    if ( $request_type == null || $request_type == $key ) {
                    	if ( !isset( $new[$key] ) ) {
                    		$new[$key] = array();
                    	}
                    	$fields_found = true;
                    	foreach ( $type as $field_name => $field ) {
                    		if ( apply_filters( 'flexible_checkout_fields_condition', true, $field ) ) {
	                            if ( $field['visible'] == 0 or
	                            	( ( isset( $_GET['page'] ) && $_GET['page'] == 'inspire_checkout_fields_settings' ) and $field['visible'] == 1) or $field['name'] == 'billing_country' or $field['name'] == 'shipping_country')
	                            {
	                            	if ( isset( $fields[$key][$field['name']] ) ) {
	                            		$new[$key][$field['name']] = $fields[$key][$field['name']];
	                            	}
	                            	else {
	                            		$new[$key][$field['name']] = $type[$field['name']];
	                            	}
	                                if( $field['required'] == 1 ){
	                                    $new[$key][$field['name']]['required'] = true;
	                                }
	                                else{
	                                    $new[$key][$field['name']]['required'] = false;
	                                }
	                                if ( isset( $field['label'] ) ) {
		                                $new[ $key ][ $field['name'] ]['label'] = stripcslashes( wpdesk__( $field['label'], 'flexible-checkout-fields' ) );
	                                }
	                                if ( isset( $field['placeholder'] ) ) {
	                                    $new[$key][$field['name']]['placeholder'] = wpdesk__( $field['placeholder'], 'flexible-checkout-fields' );
	                                }
	                                else {
	                                	$new[$key][$field['name']]['placeholder'] = '';
	                                }
	                                if( is_array($field['class'])){
	                                    $new[$key][$field['name']]['class'] = $field['class'];
	                                }
	                                else {
	                                    $new[$key][$field['name']]['class'] = explode( ' ', $field['class'] );
	                                }
	                                if ( ($field['name'] == 'billing_country' or $field['name'] == 'shipping_country') and $field['visible'] == 1 ){
	                                    $new[$key][$field['name']]['class'][1] = "inspire_checkout_fields_hide";
	                                }

	                                if( isset( $field['custom_field'] ) && $field['custom_field'] == 1 ){
	                                    $new[$key][$field['name']]['type'] = $field['type'];

	                                    if ( isset( $checkout_field_type[$field['type']]['has_options'] ) ){
	                                        $array_options = explode("\n", $field['option']);
	                                        if(!empty($array_options)){
	                                            foreach ($array_options as $option) {
	                                                $tmp = explode( ':', $option, 2 );
                                                    $tmp[1] =  strip_tags( $tmp[1], '<img><a><strong><em><br>' );
                                                    $tmp[1] = wp_unslash( $tmp[1] );
	                                                $options[trim($tmp[0])] = wpdesk__( trim($tmp[1]), 'flexible-checkout-fields' );
	                                                unset($tmp);
	                                            }
	                                            $new[$key][$field['name']]['options'] = $options;
	                                            unset($options);
	                                        }
	                                    }
	                                }

	                                $new[$key][$field['name']]['custom_attributes'] = apply_filters( 'flexible_checkout_fields_custom_attributes', array(), $field );
		                            $priority = $priority + 10;
		                            $new[$key][$field['name']]['priority'] = $priority;

	                            }
                    		}
                        }
                    }
                }
                if ( !empty( $fields ) && is_array( $fields ) ) {
	                foreach ( $fields as $key => $value ) {
	                	if ( $request_type == null || $request_type == $key ) {
	                		if ( empty( $settings[$key] ) ) {
	                			$new[$key] = $value;
	                		}
	                	}
	                }
                }

                if ( $request_type == null ) {
                    if ( !empty($fields['account'] ) ) {
                        $new['account'] = $fields['account'];
                    }
                    return $new;
                }
                else{
                	if ( isset( $new[$request_type] ) ) {
		                return $new[$request_type];
	                }
	                else {
                		return array();
	                }
                }

            }
            else {
                return $fields;
            }
        }

        public function getCheckoutUserFields($fields, $request_type = null) {
            $settings = $this->get_settings();

            $checkout_field_type = $this->get_fields();

            if ( !empty($settings[$request_type] ) ) {
                foreach ( $settings[$request_type] as $key => $field ) {
                    if($field['visible'] == 0 or $field['name'] == 'billing_country' or $field['name'] == 'shipping_country' or ( isset($_GET['page']) && $_GET['page'] == 'inspire_checkout_fields_settings' and $field['visible'] == 1)){

                        if(!empty($fields[$key])){
                            $new[$key] = $fields[$key];
                        }

                        if($field['required'] == 1){
                            $new[$key]['required'] = true;
                        }
                        else{
                            $new[$key]['required'] = false;
                        }

                        //if(!empty($field['label'])){
	                    if ( isset( $field['label'] ) ) {
		                    $new[ $key ]['label'] = wpdesk__( $field['label'], 'flexible-checkout-fields' );
	                    }
                        //}
                        //if(!empty($field['placeholder'])){
                        if ( isset( $field['placeholder'] ) ) {
                            $new[$key]['placeholder'] = wpdesk__( $field['placeholder'], 'flexible-checkout-fields' );
                        }
                        else {
                        	$new[$key]['placeholder'] = '';
                        }
                        //}
                        //if(!empty($field['class'])){
                            if(is_array($field['class'])){
                                $new[$key]['class'][0] = implode(' ', $field['class']);
                            }
                            else {
                                $new[$key]['class'][0] = $field['class'];
                            }
                        //}
                        if(($field['name'] == 'billing_country' or $field['name'] == 'shipping_country') and $field['visible'] == 1){
                            $new[$key]['class'][1] = "inspire_checkout_fields_hide";
                        }

                        if(!empty($field['type'])){
                            $new[$key]['type'] = $field['type'];
                        }

                        if( isset( $field['type'] ) && ( !empty( $checkout_field_type[$field['type']]['has_options'] ) ) ) {
                            $array_options = explode( "\n", $field['option'] );
                            if ( !empty( $array_options ) ) {
                                foreach ( $array_options as $option ) {
                                    $tmp = explode( ':', $option, 2 );
                                    $options[trim($tmp[0])] = wpdesk__( trim($tmp[1]), 'flexible-checkout-fields' );
                                    unset($tmp);
                                }
                                $new[$key]['options'] = $options;
                                unset($options);
                            }
                        }
                    }
                }
                return $new;
            }
            else {
                return $fields;
            }
        }

        public function printCheckoutFields( $order, $request_type = null ) {

        	$settings = $this->get_settings();

            $checkout_field_type = $this->get_fields();

            if( !empty( $settings ) ){
                foreach ($settings as $key => $type) {
                    if ( $request_type == null || $request_type == $key ) {
                        foreach ($type as $field) {
                            if ( $field['visible'] == 0
                            	&& ( ( isset( $field['custom_field'] ) && $field['custom_field'] == 1 ) || in_array( $field['name'], array('billing_phone', 'billing_email' ) ) )
                            	&& ( empty( $field['type'] ) || ( !empty( $checkout_field_type[$field['type']] ) && empty( $checkout_field_type[$field['type']]['exclude_in_admin'] ) ) )
                            	) {
                                if ( $value = wpdesk_get_order_meta( $order, '_'.$field['name'] , true ) ) {
                                    if ( isset( $field['type'] ) ) {
                                    	$value = apply_filters( 'flexible_checkout_fields_print_value', $value, $field );
                                        $return[] = '<b>'.stripslashes( wpdesk__( $field['label'], 'flexible-checkout-fields' ) ).'</b>: '.$value;
                                    }
                                	else{
                                        $return[] = '<b>'.stripslashes( wpdesk__( $field['label'], 'flexible-checkout-fields' ) ).'</b>: '.$value;
                                    }
                                }
                            }
                        }
                    }
                }

                if( !empty( $return ) ) {
                    echo '<div class="address_flexible_checkout_fields"><p class="form-field form-field-wide">' . implode( '<br />', $return ) . '</p></div>';
                }
            }
        }

        public function changeAdminLabelsCheckoutFields( $labels, $request_type ) {
            $settings = $this->get_settings();
            if( !empty( $settings ) && ( $request_type == null || !empty( $settings[$request_type] ) ) ) {
            	$new = array();
                foreach ($settings as $key => $type) {
                    if ( $request_type == null || $request_type == $key ) {
                        foreach ($type as $field) {
                            if ( $field['visible'] == 0 && ($request_type == null || strpos($field['name'], $request_type) === 0 )
                            	&& ( ( empty( $field['type'] ) || ( $field['type'] != 'heading' && $field['type'] != 'info' && $field['type'] != 'file' ) ) )
                            	) {
	                            $field_name = str_replace($request_type.'_', '', $field['name']);

	                            if ( isset( $labels[$field_name] ) ) {

	                                $new[$field_name] = $labels[$field_name];

	                                if(!empty($field['label'])){
	                                    $new[$field_name]['label'] = stripslashes($field['label']);

	                                }

	                                if(empty($new[$field_name]['label'])){
	                                    $new[$field_name]['label'] = $field['name'];
	                                }

	                                $new[$field_name]['type'] = 'text';
	                                if ( isset( $field['type'] ) ) {
	                                	$new[$field_name]['type'] = $field['type'];
	                            	}

	                            	$new[$field_name] = apply_filters( 'flexible_checkout_fields_admin_labels', $new[$field_name], $field, $field_name );

	                                if($field_name == 'country'){
	                                    $new[$field_name]['type'] = 'select';
	                                }

	                                $new[$field_name]['show'] = false;
                            	}
                            }
                        }
                    }
                }

                foreach ( $labels as $key=>$value ) {
                	if ( $request_type == null || $request_type == $key ) {
                		if ( empty( $new[$key] ) ) {
                			$new[$key] = $value;
                		}
                	}
                }

                return $new;
            }
            else{
                return $labels;
            }

        }

        public function addCustomFieldsToReview($order_id) {
            $settings = $this->get_settings();

            $checkout_field_type = $this->get_fields();

            if( !empty( $settings ) && is_array( $settings ) ) {
            	$return = array();
                foreach ( $settings as $key => $type ) {
                	if ( isset( $type ) && is_array( $type ) ) {
	                    foreach ( $type as $field ) {
	                        if ( isset( $field['visible'] ) && $field['visible'] == 0 && isset($field['custom_field']) && $field['custom_field'] == 1 ){
	                            if($value = wpdesk_get_order_meta( $order_id, '_'.$field['name'] , true )){
	                                if ( !empty( $checkout_field_type[$field['type']]['has_options'] ) ) {
	                                    $array_options = explode("\n", $field['option']);
	                                    if(!empty($array_options)){
	                                        foreach ($array_options as $option) {
	                                            $tmp = explode(':', $option , 2 );
	                                            $options[trim($tmp[0])] = wpdesk__( trim($tmp[1]), 'flexible-checkout-fields' );
	                                            unset($tmp);
	                                        }
	                                    }
	                                    $return[] = '<strong>'.stripslashes( wpdesk__( $field['label'], 'flexible-checkout-fields' ) ).'</strong>: '.$options[$value];
	                                    unset($options);
	                                }
	                                else{
	                                	if ( !isset( $field['type'] ) || $field['type'] != 'file' ) {
	                                		$return[] = '<strong>'.stripslashes( wpdesk__( $field['label'], 'flexible-checkout-fields' ) ).'</strong>: '.$value;
	                                	}
	                                }
	                            }
	                        }
	                    }
                	}
                }
                if( count($return) > 0 ) {
                    echo '<div class="inspire_checkout_fields_additional_information">';
                    echo '<header class="title"><h3>'. __( 'Additional Information', 'flexible-checkout-fields' ) .'</h3></header>';
                    echo '<p>'.implode('<br />', $return).'</p>';
                    echo '</div>';
                }
            }
        }

        public function changeCheckoutFields( $fields ) {
            return $this->getCheckoutFields($fields);
        }

        public function changeShippingFields($fields) {

            return $this -> getCheckoutFields($fields, 'shipping');
        }

        public function changeBillingFields($fields) {
            return $this -> getCheckoutFields($fields, 'billing');
        }

        public function changeOrderFields($fields) {
            return $this -> getCheckoutFields($fields, 'order');
        }

        public function changeAdminBillingFields($labels) {
            return $this -> changeAdminLabelsCheckoutFields($labels, 'billing');
        }

        public function changeAdminShippingFields($labels) {
            return $this -> changeAdminLabelsCheckoutFields($labels, 'shipping');
        }

        public function changeAdminOrderFields($labels) {
            return $this -> changeAdminLabelsCheckoutFields($labels, 'order');
        }

        public function addCustomBillingFieldsToAdmin($order){
            $this->printCheckoutFields( $order, 'billing' );
        }

        public function addCustomShippingFieldsToAdmin($order){
            $this->printCheckoutFields( $order, 'shipping' );
        }

        public function addCustomOrderFieldsToAdmin($order){
            $this->printCheckoutFields( $order, 'order' );
        }

        public function addCustomFieldsToEmail($order) {
            $this -> addCustomFieldsToReview( wpdesk_get_order_id( $order ) );
        }

        public function addCustomFieldsBillingFields($fields) {
            return $this -> getCheckoutUserFields($fields, 'billing');
        }

        public function addCustomFieldsShippingFields($fields) {
            return $this -> getCheckoutUserFields($fields, 'shipping');
        }

        public function addCustomFieldsOrderFields($fields) {
            return $this -> getCheckoutUserFields($fields, 'order');
        }

        function updateCheckoutFields( $order_id ) {
            $shippingNotOverwrite = array(
                'shipping_address_1',
                'shipping_address_2',
                'shipping_address_2',
                'shipping_city',
                'shipping_company',
                'shipping_country',
                'shipping_first_name',
                'shipping_last_name',
                'shipping_postcode',
                'shipping_state',
            );

            $settings = $this->get_settings();
            if ( !empty( $settings ) ) {
                $keys = array_flip(
                				array_merge(
                						array_keys(	isset( $settings['billing'] ) ? $settings['billing'] : array() ),
                						array_keys( isset( $settings['shipping'] ) ? $settings['shipping'] : array() ),
                						array_keys( isset( $settings['order'] ) ? $settings['order'] : array() )
                				)
                		);
                foreach ($_POST as $key => $value) {
                    $save = true;
                    if (empty($_POST['ship_to_different_address']))
                    {
                        $save = !in_array( $key, $shippingNotOverwrite );
                    }
                    if ($save)
                    {
                        if(array_key_exists($key, $keys)){
                            update_post_meta( $order_id, '_'.$key, esc_attr( $value ) );
                        }
                    }
                }
            }

            do_action( 'flexible_checkout_fields_checkout_update_order_meta', $order_id );

        }
        /**
         * add custom fields to edit user admin /wp-admin/profile.php
         *
         * @access public
         * @param mixed $user
         * @return void
         */
        public function addCustomUserFieldsAdmin( $user ) {
            $settings = $this->get_settings();
            if ( !empty($settings ) ) {

                foreach ( $settings as $key => $type ) {
                    foreach ( $type as $field ) {
                        if( $field['visible'] == 0 && ( isset( $field['custom_field'] ) && $field['custom_field'] == 1 ) ) {

                        	$return = false;

                        	$return = apply_filters( 'flexible_checkout_fields_user_fields', $return, $field, $user );

                        	if ( $return === false ) {

	                        	switch ( $field['type'] ) {
	                                case 'textarea':
	                                    $fields[] = '
	                                        <tr>
	                                            <th><label for="'.$field['name'].'">'.$field['label'].'</label></th>
	                                            <td>
	                                                <textarea name="'.$field['name'].'" id="'.$field['name'].'" class="regular-text" rows="5" cols="30">'.esc_attr( get_the_author_meta( $field['name'], $user->ID ) ).'</textarea><br /><span class="description"></span>
	                                            </td>
	                                        </tr>
	                                    ';
	                                break;

	                                default:
	                                    $fields[] = '
	                                        <tr>
	                                            <th><label for="'.$field['name'].'">'.$field['label'].'</label></th>
	                                            <td>
	                                                <input type="text" name="'.$field['name'].'" id="'.$field['name'].'" value="'.esc_attr( get_the_author_meta( $field['name'], $user->ID ) ).'" class="regular-text" /><br /><span class="description"></span>
	                                            </td>
	                                        </tr>
	                                    ';
	                                break;
	                            }
	                        }
	                        else {
	                        	if ( $return != '' ) {
	                        		$fields[] = $return;
	                        	}
	                        }
                        }
                    }
                }
                echo '<h3>'.  __( 'Additional Information', 'flexible-checkout-fields' ).'</h3>';
                echo '<table class="form-table">';
                echo implode('', $fields);
                echo '</table>';
            }
        }

        public function saveCustomUserFieldsAdmin($user_id) {
            if ( !current_user_can( 'edit_user', $user_id ) )
                return false;

            $settings = $this->get_settings();
            if(!empty($settings)){

                foreach ($settings as $key => $type) {
                    foreach ($type as $field) {
                        if($field['visible'] == 0 and $field['custom_field'] == 1){
                            update_user_meta( $user_id, $field['name'], $_POST[$field['name']] );
                        }
                    }
                }
            }

        }

        /**
         * action_links function.
         *
         * @access public
         * @param mixed $links
         * @return void
         */
         public function linksFilter( $links ) {

            $plugin_links = array(
                '<a href="' . admin_url( 'admin.php?page=inspire_checkout_fields_settings') . '">' . __( 'Settings', 'flexible-checkout-fields' ) . '</a>',
                '<a href="' . __('https://www.wpdesk.net/docs/flexible-checkout-fields-docs/', 'flexible-checkout-fields' ) . '">' . __( 'Docs', 'flexible-checkout-fields' ) . '</a>',
                '<a href="https://wordpress.org/support/plugin/flexible-checkout-fields/">' . __( 'Support', 'flexible-checkout-fields' ) . '</a>',
            );

            $pro_link = get_locale() === 'pl_PL' ? 'https://www.wpdesk.pl/sklep/woocommerce-checkout-fields/' : 'https://www.wpdesk.net/products/flexible-checkout-fields-pro-woocommerce/';
            $utm = '?utm_source=wp-admin-plugins&utm_medium=link&utm_campaign=flexible-checkout-fields-plugins-upgrade-link';

            if ( ! wpdesk_is_plugin_active( 'flexible-checkout-fields-pro/flexible-checkout-fields-pro.php' ) )
                $plugin_links[] = '<a href="' . $pro_link . $utm . '" target="_blank" style="color:#d64e07;font-weight:bold;">' . __( 'Upgrade', 'flexible-checkout-fields' ) . '</a>';

             return array_merge( $plugin_links, $links );
         }

         public static function getInstance() {
         	if( self::$_oInstance == false ) {
                self::$_oInstance = new inspireCheckoutFields();
            }
            return self::$_oInstance;
         }

         public static function flexible_checkout_fields_section_settings( $key, $settings ) {
         	echo 1;
         }

         public static function flexible_checkout_fields_field_tabs( $tabs ) {
         	$tabs[] = array(
         			'hash' => 'advanced',
         			'title' => __( 'Advanced', 'flexible-checkout-fields' )
         	);
         	return $tabs;
         }

    	public static function flexible_checkout_fields_field_tabs_content( $key, $name, $field, $settings ) {
    		include( 'views/settings-field-advanced.php' );
    	}

    	public function woocommerce_get_country_locale_default( $address_fields ) {
         	return $address_fields;
	    }

    }

    /**
     * Checks if Flexible Checkout Fields PRO is active
     *
     */
	function is_flexible_checkout_fields_pro_active() {
		return wpdesk_is_plugin_active( 'flexible-checkout-fields-pro/flexible-checkout-fields-pro.php' );
	}

	if ( !function_exists( 'wpdesk__' ) ) {
		function wpdesk__( $text, $domain ) {
			if ( function_exists( 'icl_sw_filters_gettext' ) ) {
				return icl_sw_filters_gettext( $text, $text, $domain, $text );
			}
			if ( function_exists( 'pll__' ) ) {
				return pll__( $text );
			}
			return __( $text, $domain );
		}
	}

	if ( !function_exists( 'wpdesk__e' ) ) {
		function wpdesk__e( $text, $domain ) {
			echo wpdesk__( $text, $domain );
		}
	}

	if ( !function_exists( 'wpdesk_tracker_enabled' ) ) {
		function wpdesk_tracker_enabled() {
			$tracker_enabled = true;
			if ( !empty( $_SERVER['SERVER_ADDR'] ) && $_SERVER['SERVER_ADDR'] == '127.0.0.1' ) {
				$tracker_enabled = false;
			}
			return apply_filters( 'wpdesk_tracker_enabled', $tracker_enabled );
			// add_filter( 'wpdesk_tracker_enabled', '__return_true' );
		}
	}

	add_action( 'plugins_loaded', 'flexible_chekout_fields_plugins_loaded', 9 );
	function flexible_chekout_fields_plugins_loaded() {
		if ( !class_exists( 'WPDesk_Tracker' ) ) {
			include( 'inc/wpdesk-tracker/class-wpdesk-tracker.php' );
			WPDesk_Tracker::init( basename( dirname( __FILE__ ) ) );
		}
	}

    $_GLOBALS['inspire_checkout_fields'] = $inspire_checkout_fields = inspireCheckoutFields::getInstance();
