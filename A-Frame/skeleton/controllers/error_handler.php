<?
	/**
	 * This file houses the error handler class, which can control or manipulate framework or application errors when they come.
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
	 * This class is loaded and called whenever an application error occurs. It has the ability to change the error type (and thus the loaded
	 * template for any specific or all errors).
	 * 
	 * @package		aframe
	 * @subpackage	aframe.skeleton
	 */
	class error_handler extends app_controller
	{
		/**
		 * Called when an error is 'triggered'
		 * 
		 * @param string $type	The type of error. Can be altered and returned
		 * @param mixed $data	Any extra data passed into the trigger function
		 * @return string		The modified (or unmodified) error type to process.
		 */
		function trigger($type, $data)
		{
			if(CRON_JOB)
			{
				$type	=	'cron';
			}
			
			// can change error type here
			return $type;
		}
		
		/**
		 * Called when a PHP error occurs (or is triggered by failed some event such as a failed query). The default is to 
		 * just display the PHP error as it looks like when no error handling is enabled (for development). When LIVE is true,
		 * 
		 * When live, it is advisable to load some sort of "Oops an error occurred LOL" template as opposed to showing errors.
		 * 
		 * @param integer $errno	Error number
		 * @param string $errstr	Error string
		 * @param string $errfile	File error occured in
		 * @param integer $errline	Line in $errfile error occured on
		 */
		function app_error($errno, $errstr, $errfile, $errline)
		{
			// get our current error level
			$errlevel	=	ini_get('error_reporting');
			$errwarning	=	$errlevel & ~E_WARNING;
			$errnotice	=	$errlevel & ~E_NOTICE;
			$disp_errs	=	(bool)ini_get('display_errors');
			
			if($errno == E_USER_ERROR || $errno == E_ERROR)
			{
				if($disp_errs)
				{
					echo '<br/><b>Fatal error</b>: '. $errstr . ' in <b>'. $errfile .'</b> on line <b>'. $errline .'</b>';
				}
				die();
			}
			else if(($errno == E_USER_WARNING || $errno == E_WARNING) && $errlevel != $errwarning)
			{
				if($disp_errs)
				{
					echo '<br/><b>Warning</b>: '. $errstr . ' in <b>'. $errfile .'</b> on line <b>'. $errline .'</b>';
				}
			}
			else if(($errno == E_USER_NOTICE || $errno == E_NOTICE) && $errlevel != $errnotice)
			{
				if($disp_errs)
				{
					echo '<br/><b>Notice</b>: '. $errstr . ' in <b>'. $errfile .'</b> on line <b>'. $errline .'</b>';
				}
			}
		}
	}
?>