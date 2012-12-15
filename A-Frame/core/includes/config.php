<?
	/**
	 * includes/config.php
	 *
	 * This page does the hard-lifting of starting up a-frame. It includes and instantiates all our main classes, loads configuration data from
	 * the app includes, and defines a few constants.
	 * 
	 * The framework is set up so that hopefully you'll never have to touch this file (hint, hint).
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
	 * aframe base define (folder a-frame lives)
	 */
	define('CORE_BASE', $core_base);
	
	/**
	 * aframe includes define (directory config lives)
	 */
	define('CORE_INCLUDES', CORE_BASE .'/core/includes');
	
	/**
	 * aframe classes define (folder that holds all the classes)
	 */
	define('CLASSES', CORE_BASE . '/core/classes');

	/**
	 * application's includes directory (holds the application's configuration)
	 */
	define('INCLUDES', BASE . '/includes');
	
	// if CRON_JOB isn't defined, make sure it's false
	if(!defined('CRON_JOB')) define('CRON_JOB', false);
	
	// annoyances squelched
	define('DS', DIRECTORY_SEPARATOR);
	define('PS', PATH_SEPARATOR);

	// define our database modes
	define('AFRAME_DB_MODE_MYSQL', 0);
	define('AFRAME_DB_MODE_MSSQL', 1);
	define('AFRAME_DB_MODE_MYSQLI', 2);
	if(!defined('AFRAME_DB_MODE_MONGODB')) define('AFRAME_DB_MODE_MONGODB', 3);

	$routes	=	array();
	$https	=	array();
	
	include_once INCLUDES .'/local.php';
	include_once INCLUDES .'/routes.php';
	include_once INCLUDES .'/https.php';
	
	include_once CLASSES .'/base/base.php';

	// now we decide which base_db class to use. this has to be done BEFORE base_model
	// loads because it extends base_db =]
	$db_mode	=	isset($config['db']['dsn']['mode']) ? $config['db']['dsn']['mode'] : '';
	if(DATABASE)
	{
		switch($db_mode)
		{
			case AFRAME_DB_MODE_MONGODB:
				include_once CLASSES . '/base/db/mongodb.php';
				break;
			case AFRAME_DB_MODE_MYSQL:
			case AFRAME_DB_MODE_MSSQL:
			case AFRAME_DB_MODE_MYSQLI:
			default:
				include_once CLASSES . '/base/db/sql.php';
				break;
		}
	}
	else
	{
		// no database needed, use an empty database base class
		include_once CLASSES . '/base/db/empty.php';
	}

	include_once CLASSES .'/base/base_controller.php';
	include_once CLASSES .'/base/base_model.php';
	include_once CLASSES .'/event.php';
	include_once CLASSES .'/msg.php';
	include_once CONTROLLERS . '/app_controller.php';
	include_once MODELS . '/app_model.php';
	
	// another assumption squeltched
	if(!defined('APP_ERROR_HANDLING')) define('APP_ERROR_HANDLING', false);
	
	$event	=	new event();				// start up our event object...stores GET/POST info, and holds all other objects!
	$event->populate();						// fill event object with GET/POST/COOKIE data
	$event->set_ref('config', $config);		// put the config array into the event object so its accessible to all
	$event->set('routes', $routes);			// put the routes array into the event object so its accessible to all
	$event->set('https', $https);			// put the https array into the event object so its accessible to all
	$event->set('https_keys', isset($https_keys) ? $https_keys : array('HTTPS'));	// put our https keys " ... "

	// load some system objects
	$cache		=	&$event->object('classes/cache', array($config['cache']['options']));			// our application's cache...a must have!
	$run		=	&$event->object('classes/run', array(&$event));									// the brain and brawn of a-frame
	$template	=	&$event->object('classes/template', array(&$event, VIEWS, VIEWS . '/layouts'));	// needed for, you know, templating (loading and populating views)
	$error		=	&$event->object('classes/error', array(&$event));								// probably wont need, but accidents happen
	
	if(DATABASE)
	{
		// database.php is now optional, as it's recommended to put all configuration options in local.php
		if(file_exists(INCLUDES . '/database.php'))
		{
			include_once INCLUDES .'/database.php';
		}

		// load the correct database class for whatever DB we're using. does NOT allow multiple databases
		// to be used through A-Frame, although one could easily enough pull the code into a library and
		// do multi-datbases themselves.
		switch($db_mode)
		{
			case AFRAME_DB_MODE_MONGODB:
				// load the aframe DB class for MongoDB databases
				include_once CLASSES . '/db/mongodb.php';
				$db_class_name	=	'db_mongo';
				break;
			case AFRAME_DB_MODE_MYSQL:
			case AFRAME_DB_MODE_MSSQL:
			case AFRAME_DB_MODE_MYSQLI:
			default:
				// load the aframe DB class for *SQL databases
				include_once CLASSES . '/db/sql.php';
				$db_class_name	=	'db_sql';
				break;
		}
		$db	=	&$event->object($db_class_name, array($config['db']['dsn']));
		$event->set_object('_db', $db);
	}
?>
