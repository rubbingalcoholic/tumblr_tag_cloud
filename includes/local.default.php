<?
	/**
	 *   TTTTTTTTTT  UU     UU  MM          MM  BBBBBB    LL       RRRRRR
	 *       TT      UU     UU  MMMM      MMMM  BB    BB  LL       RR    RR
	 *       TT      UU     UU  MM  MM  MM  MM  BBBBBB    LL       RRRRRR
	 *       TT      UU     UU  MM    MM    MM  BB    BB  LL       RR    RR
	 *       TT        UUUUU    MM          MM  BBBBBB    LLLLLLL  RR    RR
	 *
	 *
	 *   TTTTTTTTTT    AAAA      GGGGGG
	 *       TT      AA    AA  GG    
	 *       TT      AAAAAAAA  GG    GGGG
	 *       TT      AA    AA  GG      GG
	 *       TT      AA    AA    GGGGGG
	 *
	 *
	 *      CCCCCCC  LL         OOOOO    UU     UU  DDDDDD
	 *    CC         LL       OO     OO  UU     UU  DD    DD
	 *    CC         LL       OO     OO  UU     UU  DD     DD
	 *    CC         LL       OO     OO  UU     UU  DD    DD
	 *      CCCCCCC  LLLLLLL    OOOOO      UUUUU    DDDDDD
	 *
	 *
	 *  --------------------- MASTER CONFIG FILE LOLOLOL ---------------------
	 */
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
	 * Based on A-Frame PHP Framework
	 * Copyright (c) 2009, Lyon Bros Enterprises, LLC. (http://www.lyonbros.com)
	 * 
	 * Licensed under The MIT License. 
	 * Redistributions of files must retain the above copyright notice.
	 * 
	 * @copyright	Copyright (c) 2012 Rubbing Alcoholic
	 * @package		tumblr_tag_cloud
	 * @license		http://www.opensource.org/licenses/mit-license.php
	 */
	
	/**
	 *	Your base website address without http:// (eg. tumblr_tag_cloud.rubbingalcoholic.com)
	 */
	define('SITE', 'tumblr_tag_cloud.com');		// base website address (without http://)

	/**
	 *	If you install the app under a subdirectory of an existing domain, put it here...
	 *	...note that this is untested and currently YMMV status. Recommend giving the app its own subdomain
	 */
	define('WEBROOT', '');

	/**
	 *	This controls whether Memcache or APC caching will be used. The app will cache to your MySQL
	 *	-- THE APP WILL CACHE TO YOUR MYSQL DATABASE REGARDLESS. --
	 *	Set to true **only** if your hosting environment supports Memcache or APC. Most shared hosts don't.
	 *	If set to true, the cache configuration below must be properly specified
	 */
	define('CACHING', false);

	/**
	 *	Framework defines. Do not remove. You probably don't need to touch them either.
	 */
	define('DEBUG', false);						// debug mode...displays extended debug info for each request
	define('DATABASE', true);					// yes, you need a database for this app to work
	define('ERROR_LEVEL', E_ALL | E_NOTICE);	// our default app PHP error level
	define('PATH_DISPATCHER', true);			// true = /index.php/framework_request, false = /index.php?url=framework_request
	define('APP_ERROR_HANDLING', true);			// true = application handles its own errors in controllers/error_handler.php, false = PHP does error handling

	/**
	 *	Admin Username, used to password protect the app
	 */
	define('ADMIN_USERNAME', 'admin');

	/**
	 *	Admin Password
	 */
	define('ADMIN_PASSWORD', 'password');
	
	/**
	 *	Put your MySQL database info here.
	 */
	$config['db']['dsn']	=	array(
		'hostspec'	=>	'127.0.0.1',				// the hostname / socket we're connecting to. if a unix socket, be sure to use proper mysql_connect() notation for sockets
		'port'		=>	'3306',						// port the DB server lives on
		'username'	=>	'username',					// connect as username
		'password'	=>	'password',					// using password...
		'database'	=>	'db_name',					// database to connect to
		'persist'	=>	false,						// whether or not connections persist. 
		'mode'		=>	AFRAME_DB_MODE_MYSQLI,		// This app only supports MySQL 5.0+
		'free_res'	=>	true						// free results after use? (MUST be true if using AFRAME_DB_MODE_MYSQLI)
	);
	
	/**
	 *	Database table prefix. Leave blank if you want, I don't care.
	 */
	$config['db']['prefix']	=	'cloud_';		// table prefix of your app

	/**
	 *	Cache configuration for Memcache or APC.
	 *	If you set CACHING to true above, you need to fill this out
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
?>
