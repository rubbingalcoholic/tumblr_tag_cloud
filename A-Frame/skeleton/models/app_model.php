<?
	/**
	 * This file holds the app model class, which draws power from base_model. Every model (or any object needing database
	 * access) should extend app_model.
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
	 * App model class.
	 * 
	 * Abstracts out application-specific functionality from the base model.
	 * 
	 * Can be used for application-wide model initialization. Can run queries, call models, etc. Also good for adding app-wide model
	 * functionality without modifying the base_model in the a-frame codebase.
	 * 
	 * @package		aframe
	 * @subpackage	aframe.skeleton
	 */
	class app_model extends base_model
	{
		function app_model(&$event)
		{
			// Should be called every time, otherwise event object will not be available in model
			$this->_init($event);
		}
	}
?>