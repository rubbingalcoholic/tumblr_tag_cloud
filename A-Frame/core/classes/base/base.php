<?
	/**
	 * This file holds the base class, which is extended by everything, except maybe libraries (but 
	 * they can extend it too!)
	 * 
	 * 
	 * Copyright (c) 2009, Lyon Bros Enterprises, LLC. (http://www.lyonbros.com)
	 * 
	 * Licensed under The MIT License. 
	 * Redistributions of files must retain the above copyright notice.
	 * 
	 * @copyright	Copyright (c) 2009, Lyon Bros Enterprises, LLC. (http://www.lyonbros.com)
	 * @package		aframe
	 * @subpackage	aframe.core
	 * @license		http://www.opensource.org/licenses/mit-license.php
	 */
	
	/**
	 * Welcome to the base class, extended by all other framework objects ...probably.
	 * 
	 * Does some basic initialization and object loading. Holds objects for later use.
	 * 
	 * @package		aframe
	 * @subpackage	aframe.core
	 * @author		Andrew Lyon
	 */
	class base
	{
		/**
		 * Holds the event object
		 * @var object
		 */
		var $event;
		
		/**
		 * Holds the caching object
		 * @var object
		 */
		var $cache;
		
		/**
		 * Holds the app config array
		 * @var array
		 */
		var $config;
		
		/**
		 * Constructor, runs _init()
		 * 
		 * @param object &$event	Event object
		 */
		function base(&$event)
		{
			$this->_init($event);
		}
		
		/**
		 * Initializes all objects using event object.
		 * 
		 * @param object &$event	Event object
		 */
		function _init(&$event)
		{
			if($event instanceof event)
			{
				// set our event object
				$this->event		=	&$event;
				
				// cache is the only object needed by all other objects, mostly.  
				$this->cache		=	&$event->return_object('cache');
				
				// not MVC compliant -- make config available to all
				// TODO: move this to base_controller once all external code is updated (aka never...whoops)
				$this->config		=	&$event->get_ref('config');
			}
		}
		
		/**
		 * Get the IP address for the remote client. Tries to be smart about forwarded IP 
		 * addresses from a load (X-Forwarded-For).
		 * 
		 * TODO: move this to base_controller. if a model needs an IP, it should be passed in as an arg...
		 * 
		 * @param bool $retarded		if set to true, will only get REMOTE_IP (not check 
		 * 								X-Forwarded-For). default == false (smart mode)
		 * @return string				IP addres of remote client
		 */
		function get_remote_ip($retarded = false)
		{
			if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
			{
				return $_SERVER['HTTP_X_FORWARDED_FOR'];
			}
			
			return $_SERVER['REMOTE_ADDR'];
		}
		
		/**
		 * Returns the Message object...mainly used in base
		 */
		function &_get_msg()
		{
			return $_SESSION['msg_object'];
		}
		
		/**
		 * Our empty init function -- override me!
		 */
		function init()
		{
		}
	}
?>