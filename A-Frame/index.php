<?
	/**
	 * This is the main controller file for aframe. It includes what's needed, and runs the
	 * framework's main functions.
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
	
	// get our start time and memory usage for our optional app debugging
	$start	=	microtime_float();
	$mem_s	=	memory_get_usage(true);
	
	/**
	 * Include our application's configuration
	 */
	include_once $app_base . '/includes/config.php';
	
	/**
	 * Load the aframe config. This will create global classes, but the only one we care about is run
	 */
	include_once $core_base . '/core/includes/config.php';
	
	// if this is a cron, shove the arguments into global scope for run class
	if(CRON_JOB)
	{
		$GLOBALS['_argv']	=	$argv;
	}
	
	/**
	 * Run the frameworks parse function. Reads the URL, loads and runs all needed objects, echos output from APP
	 * Basically, the function that runs the whole framework.
	 */
	$run->parse();
	
	/**
	 * Clean up! Disconnect DB, caches, and any other core-specific objects
	 */
	$run->cleanup();

	if(DEBUG)
	{
		// we're in core debug mode...spit out the execution time and total memory usage
		echo '
			<div style="background:#000">
				<span style="color:#fff">Time: '. (microtime_float() - $start) .'</span><br/>
				<span style="color:#fff">Memory from '. (int)($mem_s / 1024) .'k to '. (int)(memory_get_usage(true) / 1024) .'k</span>
			</div>
		';
	}

	/**
	 * Global function that returns the current time in microseconds
	 * 
	 * @return float	Current time in microseconds
	 */
	function microtime_float()
	{
	   list($usec, $sec) =	explode(" ", microtime());
	   return ((float)$usec + (float)$sec);
	}
?>