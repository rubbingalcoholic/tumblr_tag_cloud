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

			// Initialize the session. This will allow us to have a session, DUH
			if(!session_id())
			{
				session_start();
			}

			// Initialize our message object
			if(!isset($_SESSION['msg_object']) || !($_SESSION['msg_object'] instanceof msg))
			{
				$_SESSION['msg_object']	=	&$this->event->object('msg', array());
			}
			$this->msg	=	&$_SESSION['msg_object'];
			$this->set_ref('msg_object', $this->msg);
		}

		/**
		 *	Pops a janky HTTP login window
		 *	Can be called by controller functions we wish to password protect
		 */
		function require_http_authorization()
		{
			if (
				!isset($_SERVER['PHP_AUTH_USER'])
				||
				!isset($_SERVER['PHP_AUTH_PW'])
				||
				$_SERVER['PHP_AUTH_USER'] != ADMIN_USERNAME
				||
				$_SERVER['PHP_AUTH_PW'] != ADMIN_PASSWORD
			)
			{
				Header("WWW-Authenticate: Basic realm=\"Memcache Login\"");
				Header("HTTP/1.0 401 Unauthorized");

				echo <<<EOB
				<html><body>
				<h1>Access Denied!</h1>
				<big>YOU HAVE FAILED TO PROVIDE THE CORRECT LOGIN CREDENTIALS! LOL!!!1</big>
				</body></html>
EOB;
				exit;
			}
		}
	}
?>