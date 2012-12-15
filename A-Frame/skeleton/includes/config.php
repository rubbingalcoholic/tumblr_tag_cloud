<?
	/**
	 * This is the a-frame configuration file. It defines app-specific directories. 
	 * 
	 * A trillion and one applications could exist without ever touching this information, but some people want 
	 * the extra setup control!
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
	define('BASE', $app_base);						// the root directory where the App lies
	define('CONTROLLERS', BASE . '/controllers');	// the root controllers folder
	define('MODELS', BASE . '/models');				// the root models folder
	define('VIEWS', BASE . '/views');				// the root view folder
	define('LIBRARY', BASE . '/library');			// where all the external libraries are kept
	define('DATA', BASE . '/data');					// where file-based data is stored (file cache, session data, etc)
	define('TMP', DATA . '/tmp');					// where application temporary files are stored
	define('LOGS', DATA . '/logs');
	
	define('CRON_CONTROLLER', 'cron');				// default name for the built-in cron-job controller
	
	define('ROUTE_LIBRARY', false);					// set to a controller in the controllers/ directory to use custom routing
?>