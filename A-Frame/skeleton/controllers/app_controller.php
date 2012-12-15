<?
	/**
	 * This file houses the app_controller class, which draws its power from base_controller. All controllers
	 * should extend app_controller.
	 * 
	 * 
	 * Copyright (c) 2009, Lyon Bros Enterprises, LLC. (http://www.lyonbros.com)
	 * 
	 * Licensed under The MIT License. 
	 * Redistributions of files must retain the above copyright notice.
	 * 
	 * @copyright	Copyright (c) 2009, Lyon Bros Enterprises, LLC. (http://www.lyonbros.com)
	 * @package		aframe
	 * @subpackage	aframe.skeleton
	 * @license		http://www.opensource.org/licenses/mit-license.php
	 */
	
	/**
	 * App controller class.
	 * 
	 * Abstracts out application-specific functionality from the base controller.
	 * 
	 * @package		aframe
	 * @subpackage	aframe.skeleton
	 */
	class app_controller extends base_controller
	{
		/**
		 * Init function.
		 * 
		 * This function is called on every controller start, and can be used for any app-specific
		 * object initialization needed.
		 */
		function init()
		{
			// Should always call parent's init
			parent::init();
		}
	}
?>