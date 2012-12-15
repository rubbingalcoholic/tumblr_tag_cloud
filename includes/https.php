<?
	/**
	 * Configures which pages have access to HTTPS (SSL) on site. If a page is accessed that isn't on this list,
	 * it gets 301 redirected to http://[url]
	 * 
	 * Note: this list is processed AFTER routes are. So add the /controller/action of the endpoint of the route,
	 * not the URL it displays as.
	 * 
	 * Format:
	 * $https['/controller/action'] = 1
	 * 
	 * Also, in the routes, if '/short' is mapped to '/controller/action', /short will be allowed to use HTTPS as well as /controller/action.
	 * 
	 * It's important to realize that just because HTTPS is allowed doesn't mean it's forced. Nobody will ever be automatically redirected
	 * to https:// by the framework (unless you implicitely do this in your app code).
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
	
	// checks all of the keys in this array in $_SERVER to see if one of them == 'on'. can be vary useful for 
	// checking custom HTTPS headers set by your load balancer
	$https_keys	=	array('HTTPS');
	
	$https['*']	=	1;		// allows HTTPS access on all pages
?>