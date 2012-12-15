<?
	/**
	 * This file holds an example controller class. It shows some extremely basic functionality of a controller.
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
	 * Example controller.
	 * 
	 * This is the 'pages' controller. Each page is loaded as a different function. From there, variables like 'site_title' (which can be used
	 * in the <title> tag of the website) can be set into the template.
	 * 
	 * Controllers handle data, call models, and load views. Make sure you program with this in mind!
	 * 
	 * @package		aframe
	 * @subpackage	aframe.skeleton
	 */
	class pages extends app_controller
	{
		/**
		 * This site's 'home page' function
		 */
		function home()
		{
			// Can set the site_title. Is an arbitrary variable that could be used in the site's <title> tag.
			$this->set('site_title', 'Welcome!');
			
			// render the view (APP_ROOT/views/pages/home.php)
			$this->render('pages/home');
		}
	}
?>