<?
	/**
	 * This file holds the error handling class.
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
	 * Error class used for loading correct templates and layout when an error is triggered.
	 * 
	 * Sets template variables and loads error templates, depending on the type of error. It also
	 * loads an optional class in the apps CONTROLLERS folder which can be used to do any app-specific
	 * error loading/reporting or variable setting.
	 *
	 * @package		aframe
	 * @subpackage	aframe.core
	 * @author		Andrew Lyon
	 */
	class error extends base_controller
	{
		/**
		 * Reports an error, loads corresponding template and layout.
		 * 
		 * @param string $type	Type of error to trigger, also used to load template
		 * @param mixed $data	Any data that may need to be passed into error template...could be a
		 * 						controller name, file name, etc. It's up to the template to use it.
		 */
		function err($type, $data = array())
		{
			// if our error handler exists in the app, load it and use it to pre-process error
			if(file_exists(CONTROLLERS . '/error_handler.php'))
			{
				include_once CONTROLLERS . '/error_handler.php';
				$erh	=	&$this->event->object('error_handler', array(&$this->event));
				
				// get the type back from the app error handler. this will be used to load our error template
				$type	=	$erh->trigger($type, $data);
			}
			
			// build our template string
			$tpl	=	'errors/'.$type.'.php';
			
			// shove the data we passed in into the template
			$this->template->assign('data', $data);
			
			// get our content from the template
			$content	=	$this->template->fetch($tpl, false);
			
			// content load failed, panic, die
			if($content === false)
			{
				die('Error: Couldn\'t load template: '. $tpl);
			}
			
			if($this->event->get('show_layout', true))
			{
				// load our main layout to display error in
				$this->template->assign('main_content', $content);
				$layout	=	$this->template->fetch('layouts/'. $this->template->layout, false);
				
				// if that doesnt work, just echo our content
				if($layout === false)
				{
					die($content);
				}
				
				// show all of our content (error w/ default layout)
				die($layout);
			}
			
			die($content);
		}
	}
?>
