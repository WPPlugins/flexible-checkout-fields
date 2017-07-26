<?php
	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 
	
	if (!class_exists('inspire_pluginDependant3'))
	{
		/**
		 * 
		 * 
		 *
		 */
	    abstract class inspire_pluginDependant3
	    {  
	    	protected $_plugin;
	    	
	    	public function __construct(inspire_Plugin3 $plugin)
	    	{
	    		$this->_plugin = $plugin;
	    		$this->_initBaseVariables();
	    	}
	    	
	    	/**
	    	 * 
	    	 * @return inspire_Plugin3
	    	 */
	    	public function getPlugin()
	    	{
	    		return $this->_plugin;
	    	}
	    	
	    	public function getTextDomain()
	    	{
	    		return $this->_plugin->getTextDomain();
	    	}
	    	
	    	protected function _initBaseVariables()
	    	{
	
	    	}
	    	
	    	public function createHelperClass($name)
	    	{
	    		return $this->_plugin->createHelperClass($name);
	    	}
	    	
	        /**
	         * 
	         * @return string
	         */
	        public function getPluginUrl()
	        {
	        	return $this->_plugin->getPluginUrl();
	        }
	        
	        /**
	         * @return string
	         */
	        public function getTemplatePath()
	        {
	        	return $this->_plugin->getTemplatePath();
	        }
	        
	        public function getPluginFileName()
	        {
	        	return $this->_plugin->getPluginFileName();
	        }
	        
	        public function getNamespace()
	        {
	        	return $this->_plugin->getNamespace();
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
	        	return $this->_plugin->loadTemplate($name, $path, $args);
	        }
	        
	        public function getSettingValue($name, $default = null)
	        {
	        	return $this->_plugin->getSettingValue($name, $default);
	        }
	        
	        public function setSettingValue($name, $value)
	        {
	        	return $this->_plugin->setSettingValue($name, $value);
	        }
	        
	        public function isSettingValue($name)
	        {
	        	return $this->_plugin->isSettingValue($name);
	        }
	    }  
	}
