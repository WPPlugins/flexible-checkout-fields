<?php
	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 
	
	if (!class_exists('inspire_pluginWidget3'))
	{
	    abstract class inspire_pluginWidget3 extends WP_Widget
	    { 
	    	protected $_plugin;
	    	
	    	public function __construct(inspire_Plugin3 $plugin)
	    	{
	    		$this->_plugin = $plugin;
	    	}
	    }
  
	}
