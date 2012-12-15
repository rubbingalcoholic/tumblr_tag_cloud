<?
	/**
	 * System defines. To be copied to "local.php"
	 * 
	 * This is a local configuration (specific the app's dev environment) and should NOT be checked in to SCM.
	 * All modifications to this file should be made to the template (local.default.php) so other developers
	 * on the project can get those changes and make them to their local configuration.
	 * 
	 * These are app-specific settings but required by the framework itself and should not be removed or renamed.
	 * 
	 * This file also contains all database connection information.
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
	 * Framework defines. Do not remove!
	 */
	define('WEBROOT', '');						// if under a subdirectory...usually blank
	define('SITE', 'website.com');				// base website address (without http://)
	define('DEBUG', false);						// debug mode...displays extended debug info for each request
	define('CACHING', true);					// app-based caching. can be Memcached (default) or a-frame's file-based caching
	define('DATABASE', false);					// does the app use a database?
	define('ERROR_LEVEL', E_ALL | E_NOTICE);	// our default app PHP error level
	define('PATH_DISPATCHER', true);			// true = /index.php/framework_request, false = /index.php?url=framework_request
	define('APP_ERROR_HANDLING', true);			// true = application handles its own errors in controllers/error_handler.php, false = PHP does error handling
	
	/**
	 * App defines.
	 * 
	 * A spot for users to declare defines specific to the application
	 */
	define('USER_DEFINE', 12345);

	/**
	 * Cache configuration.
	 */
	$config['cache']['options']	=	array(
		'caching'		=> 	CACHING,			// Enable/disable caching?
		'type'			=>	'memcache',			// (memcache|apc|filecache)
		'key_prefix'	=>	'aframe:',
		'servers'		=>	array(				// servers, each is
			array('127.0.0.1')					// array([server],[port=11211],[persist=true],[weight=10])
		),
		'compression'	=>	false,				//MEMCACHE_COMPRESSED,
		'ttl'			=>	300					// default TTL (in seconds)
	);
	
	/**
	 * Standard one-server MySQL/MSSQL server config. No support for PgSQL, as of yet. See below for read/write
	 * split config setup.
	 * 
	 * Standard PEAR-like database configuration array, although the system has it's own DB abstraction layer 
	 * and does NOT use PEAR.
	 */
	$config['db']['dsn']	=	array(
		'hostspec'	=>	'127.0.0.1',				// the hostname / socket we're connecting to. if a unix socket, be sure to use proper mysql_connect() notation for sockets
		'port'		=>	'3306',						// port the DB server lives on
		'username'	=>	'username',					// connect as username
		'password'	=>	'password',					// using password...
		'database'	=>	'db_name',					// database to connect to
		'persist'	=>	false,						// whether or not connections persist. 
		'mode'		=>	AFRAME_DB_MODE_MYSQL,		// _MYSQL, _MYSQLI, _MSSQL
		'free_res'	=>	true						// free results after use? (MUST be true if using AFRAME_DB_MODE_MYSQLI)
	);
	
	/**
	 * Standard configuration for a mongo database
	 */
	/*
	$config['db']['dsn']	=	array(
		'hostspec'	=>	'mongodb://127.0.0.1:27017',	// this string can also include authentication
		'database'	=>	'yourdb',						// main database to connect to
		'replicate'	=>	false,							// set to true if using replica sets
		'connect'	=>	true,							// set to true to force connection when DB object is loaded
		'persist'	=>	false,							// generally a good idea to keep false
		'mode'		=>	AFRAME_DB_MODE_MONGODB,			// ALWAYS use this value if using MongoDB
	);
	*/

	/**
	 * Standard SQL read/write split config. Only supports one master/one slave, so 
	 * you'll have to do the pooling with a load balancer if you actually have more
	 * than two servers.
	 */
	/*
	$config['db']['dsn']	=	array(
		'master'		=>	array(
			'hostspec'  =>	'127.0.0.1',			// master hostname / ip (writes go here)
			'port'		=>	'3306',					// port the DB server lives on
		),
		'slave'			=>	array(
			'hostspec'  =>	'127.0.0.1',			// slave hostname / ip (reads go here)
			'port'		=>	'3306',					// port the DB server lives on
		),
		'username'		=>	'username',				// connect as username
		'password'		=>	'password',				// using password...
		'database'		=>	'db_name',				// database to connect to
		'persist'		=>	false,					// whether or not connections persist. 
		'mode'			=>	AFRAME_DB_MODE_MYSQL,	// _MYSQL, _MYSQLI, _MSSQL
		'free_res'		=>	true,					// free results after use? (MUST be true if using AFRAME_DB_MODE_MYSQLI)
		'replication'	=>	true					// replication is used, split our reads and writes
	);
	*/

	/**
	 * Database table prefix. Applicable to *SQL and MongoDB databases
	 */
	$config['db']['prefix']	=	'prfx_';		// table prefix of your app
?>
