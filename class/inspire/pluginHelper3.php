<?php
	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 
	
	if (!class_exists('inspire_pluginHelper3'))
	{
	    abstract class inspire_pluginHelper3
	    {  
	    	protected $_plugin;
	    	
	    	public function __construct(inspire_Plugin3 $plugin)
	    	{
	    		$this->_plugin = $plugin;
	    	}
	    }
  
	}
