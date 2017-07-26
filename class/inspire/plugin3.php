<?php
	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 
	
	if (!class_exists('inspire_Plugin3'))
	{
	
		/**
		 * Base plugin class for Inspire Labs plugins
		 * 
		 * @author Krzysiek
		 *
		 */
	    abstract class inspire_Plugin3
	    {
			const VERSION = '3.0';
	    	
	    	protected $_pluginNamespace = "";
	    	protected $_textDomain = "";

	    	protected $_pluginPath;
	    	protected $_templatePath;
	    	protected $_pluginFileName;
	    	protected $_pluginUrl;
	    	
	    	protected $_wpdeskUpdateUrl = "http://wooinvoice.stage.inspirelabs.pl/wordpress/wp-content/plugins/woocommerce-wpdesk-updater";
	    	
	    	protected $_defaultViewArgs; // default args given to template
	    	
	    	public function __construct()
	    	{
	    		$this->_initBaseVariables();
	    	}
	    	
	    	/**
	    	 *
	    	 * @return inspire_Plugin3
	    	 */
	    	public function getPlugin()
	    	{
	    		return $this;
	    	}
	    	
	    	/**
	    	 * 
	    	 * @param string $name
	    	 */
	    	protected function _convertCamelCaseToPath($name)
	    	{
	    		return strtolower(preg_replace('/([a-z0-9])([A-Z])/', '$1_$2', $name));
	    	}
	    	
	    	public function createHelperClass($name)
	    	{
	    		require_once('pluginHelper3.php');
	    		$file = $this->_convertCamelCaseToPath($name); ;
	    		require_once( plugin_dir_path($this->getPluginFileName()) . '/classes/' . $file . '.php' );
	    		
	    		return new $name($this);
	    	}
	    	
	    	public function createDependant($name)
	    	{
	    		require_once('pluginDependant3.php');
	    		$file = $this->_convertCamelCaseToPath($name); ;
	    		require_once( plugin_dir_path($this->getPluginFileName()) . '/classes/' . $file . '.php' );
	    		
	    		return new $name($this);
	    	}
	    	
	    	/**
	    	 * 
	    	 */
	    	protected function _initBaseVariables()
	    	{
	    		$reflection = new ReflectionClass($this);
	    		
	    		// Set Plugin Path
	    		$this->_pluginPath = dirname($reflection->getFileName());
	    		 
	    		// Set Plugin URL
	    		$this->_pluginUrl = plugin_dir_url($reflection->getFileName());
	    		
	    		$this->_pluginFileName = $reflection->getFileName();
	    		
	    		$this->_templatePath = '/' . $this->_pluginNamespace . '_templates';
	    		
	    		$this->_defaultViewArgs = array(
	    			'pluginUrl' => $this->getPluginUrl()
	    		);
	    		
	    		// load locales
	    		load_plugin_textdomain($this->getTextDomain(), FALSE, dirname(plugin_basename(__FILE__)) . '/lang/');
	    		
	   			add_filter( 'plugin_action_links_' . plugin_basename( $this->getPluginFileName() ), array( $this, 'linksFilter' ) );
	    	}
	    	
	    	public function getTextDomain()
	    	{
	    		return $this->_textDomain;
	    	}
	    	
	        /**
	         * 
	         * @return string
	         */
	        public function getPluginUrl()
	        {
	        	return $this->_pluginUrl;
	        }
	        
	        /**
	         * @return string
	         */
	        public function getTemplatePath()
	        {
	        	return $this->_templatePath;
	        }
	        
	        public function getPluginFileName()
	        {
	        	 return $this->_pluginFileName;
	        }
	        
	        public function getNamespace()
	        {
	        	return $this->_pluginNamespace;
	        }
	        
	        public function initPluginUpdates()
	        {
	        	//add_filter('pre_set_site_transient_update_plugins', array($this, 'checkForPluginUpdate'));
	        	//add_filter('plugins_api', array($this, 'pluginApiCall'), 10, 3);
	        }
	         
	        protected function getPluginUpdateName()
	        {
	        	return $this->getNamespace() . '/' . str_replace('woocommerce-', '', $this->getNamespace()) .'.php';
	        }
	         
	        public function checkForPluginUpdate($checked_data)
	        {
	        	global $wp_version;
	        
	        	if (empty($checked_data->checked))
	        		return $checked_data;
	        
	        	var_dump($checked_data);
	        
	        	$args = array(
	        			'slug' => $this->getNamespace(),
	        			'version' => $checked_data->checked[$this->getPluginUpdateName()],
	        	);
	        	$request_string = array(
	        			'body' => array(
	        					'action' => 'basic_check',
	        					'request' => serialize($args),
	        					'site' => get_bloginfo('url')
	        			),
	        			'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo('url')
	        	);
	        
	        	var_dump($request_string); die();
	        
	        	// Start checking for an update
	        	$raw_response = wp_remote_post($this->wpdeskUpdateUrl, $request_string);
	        
	        	if (!is_wp_error($raw_response) && ($raw_response['response']['code'] == 200))
	        		$response = unserialize($raw_response['body']);
	        
	        	if (is_object($response) && !empty($response)) // Feed the update data into WP updater
	        		$checked_data->response[$this->getNamespace() .'/'. $this->getNamespace() .'.php'] = $response;
	        
	        	return $checked_data;
	        }
	         
	        function plugin_api_call($def, $action, $args) {
	        	global $wp_version;
	        	 
	        	if (!isset($args->slug) || ($args->slug != $plugin_slug))
	        		return false;
	        	 
	        	// Get the current version
	        	$plugin_info = get_site_transient('update_plugins');
	        	$current_version = $plugin_info->checked[$this->getPluginUpdateName()];
	        	$args->version = $current_version;
	        	 
	        	$request_string = array(
	        			'body' => array(
	        					'action' => $action,
	        					'request' => serialize($args),
	        					'api-key' => md5(get_bloginfo('url'))
	        			),
	        			'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo('url')
	        	);
	        	 
	        	$request = wp_remote_post($this->wpdeskServer, $request_string);
	        	 
	        	if (is_wp_error($request)) {
	        		$res = new WP_Error('plugins_api_failed', __('An Unexpected HTTP Error occurred during the API request.</p> <p><a href="?" onclick="document.location.reload(); return false;">Try again</a>', $this->getTextDomain()), $request->get_error_message());
	        	} else {
	        		$res = unserialize($request['body']);
	        		 
	        		if ($res === false)
	        			$res = new WP_Error('plugins_api_failed', __('An unknown error occurred', $this->getTextDomain()), $request['body']);
	        	}
	        	 
	        	return $res;
	        }
	        
	        public function addMessage($message)
	        {
	        	$messages = $this->_getMessages();
	        	if (!is_array($messages))
	        	{
	        		$messages = array();
	        	}
	        	$messages[] = $message;
	        	$this->_setMessages($messages);
	        }
	        
	        protected function _getMessages()
	        {
	        	return get_option($this->getNamespace() . '_messages');
	        }
	        
	        protected function _setMessages($messages)
	        {
	        	update_option($this->getNamespace() . '_messages', $messages);
	        }
	        
	        public function getMessagesDiv($clean = true)
	        {
	        	$messages = $this->_getMessages();
	        	$str = '';
	        	
	        	if (is_array($messages) && !empty($messages))
	        	{
	        		$str .= '<div class="updated" id="message">';
	        		foreach ($messages as $message)
	        		{
	        			$str .= '<p>' . $message . '</p>';
	        		}
	        		$str .= '</div>';
	        	}
	        	
	        	if ($clean)
	        	{
	        		$this->_setMessages('');
	        	}
	        	return $str;
	        }
	        
	        /**
			 * Renders end returns selected template
			 *
			 * @param string $name name of the template
			 * @param string $path additional inner path to the template
			 * @param array $args args accesible from template
			 * @return string
			 */
			public function loadTemplate($name, $path = '', $args = array())
			{
				$args = array_merge($this->_defaultViewArgs, array('textDomain', $this->_textDomain), $args);
				$path = trim($path, '/');
			
				if (file_exists($templateName = implode('/', array(get_template_directory(), $this->getTemplatePath(), $path, $name . '.php'))))
				{
				} else {
					$templateName = implode('/', array($this->_pluginPath, $this->getTemplatePath(), $path, $name . '.php'));
				}
			
				ob_start();
				include($templateName);
				return ob_get_clean();
			}
	        
	        /**
	         * Gets setting value
	         * 
	         * @param string $name
	         * @param string $default
	         * @return Ambigous <mixed, boolean>
	         */
	        public function getSettingValue($name, $default = null)
	        {
	        	return get_option($this->getNamespace() . '_' . $name, $default);
	        }
	        
	        public function setSettingValue($name, $value)
	        {
	        	return update_option($this->getNamespace() . '_' . $name, $value);
	        }
	        
	        public function isSettingValue($name)
	        {
	        	$option = get_option($this->getNamespace() . '_' . $name);
	        	return !empty($option);
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
	        			'<a href="' . admin_url( 'admin.php?page=' . $this->getNamespace() ) . '">' . __( 'Ustawienia', $this->getTextDomain() ) . '</a>',
	        			'<a href="http://www.wpdesk.pl/docs/' . str_replace('_', '-', $this->getNamespace()) . '_docs/">' . __( 'Dokumentacja', $this->getTextDomain() ) . '</a>',
	        			'<a href="http://www.wpdesk.pl/support/">' . __( 'Wsparcie', $this->getTextDomain() ) . '</a>',
	        	);
	        
	        	return array_merge( $plugin_links, $links );
	        }
	    }  
	}
