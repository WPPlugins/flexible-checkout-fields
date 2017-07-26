<?php
	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 
	
	if (!class_exists('inspire_pluginPostType3'))
	{
		/**
		 * 
		 * 
		 *
		 */
	    abstract class inspire_pluginPostType3
	    {  
	    	protected $_post;
	    	protected $_meta;
	    	
	    	public function __construct($post)
	    	{
	    		$this->_post = get_post($post);
	    		$this->_meta = get_post_meta($this->_post->ID);
	    	}
	    	
	    	public function getId()
	    	{
	    		return $this->getPost()->ID;
	    	}
	    	
	    	public function get($name)
	    	{
	    		if (isset($this->_post->$name))
	    		{
	    			return $this->_post->$name;
	    		} else {
	    			
	    			return $this->_meta[$name];
	    		}
	    	}
	    	
	    	public function setMeta($name, $value)
	    	{
	    		$this->_meta[$name] = $value;
	    		return update_post_meta($this->_post->ID, $name, $value);
	    	}
	    	
	    	/**
	    	 * 
	    	 * @return WP_Post
	    	 */
	    	public function getPost()
	    	{
	    		return $this->_post;
	    	}
	    	
	    	public function refresh()
	    	{
	    		$this->_post = get_post($this->_post->ID);
	    		$this->_meta = get_post_meta($this->_post->ID);
	    	}
	    	
	    }
	}
