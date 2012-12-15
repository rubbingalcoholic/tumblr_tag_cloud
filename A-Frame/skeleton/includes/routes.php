<?
	/**
	 * Configures the system routes. Basically takes an apparent URL and maps it to a transparent controller/action.
	 * 
	 * Format:
	 * $routes['/URL/user/sees'] = array (
	 * 		'controller'	=>	'real_controller',
	 * 		'action'		=>	'real_controller_action'
	 * );
	 * 
	 * Example:
	 * $routes['/about'] = array(
	 * 		'controller'	=>	'pages',
	 * 		'action'		=>	'about'
	 * );
	 * 
	 * or
	 * 
	 * $routes['/login'] = array(
	 * 		'controller'	=>	'users',
	 * 		'action'		=>	'login'
	 * );
	 * 
	 * Routes cannot contain yet make use of extra parameters (/controller/action/extra/parameters), so program accordingly. They are generally
	 * good for landing pages and pretty urls for long ugly controller/action names.
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
	
	// a standard default route that maps '/' to the home page action in the pages controller.
	$routes['/']		=	array(
		'controller'	=>	'pages',
		'action'		=>	'home'
	);
?>