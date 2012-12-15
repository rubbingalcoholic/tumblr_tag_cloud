<?
	/**
	 * A-Frame has built-in support for application cron jobs that can be run through the command
	 * line. Any file which has define('CRON_JOB', true) will activate the cron support (NOT
	 * advisable to have any such file in the webroot).
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
	 * Cron class.
	 * 
	 * Provides an interface for running cron jobs through the system.
	 * 
	 * @package		aframe
	 * @subpackage	aframe.skeleton
	 */
	class cron extends app_controller
	{
		function init()
		{
			// should always be called in init
			parent::init();
			
			// let's a-frame know we don't want to show a layout for any cron output
			$this->layout(null);
		}
	}
?>