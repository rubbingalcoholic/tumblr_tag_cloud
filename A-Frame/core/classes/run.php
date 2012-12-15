<?
	/**
	 * Holds THE aframe class. Let me repeat. THE class. run runs the whole framework.
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
	 * Main framework class in charge of processing requests, loading controller classes,
	 * displaying layouts, and running cleanup. 
	 * 
	 * Really "ties the room together"
	 * 
	 * @package		aframe
	 * @subpackage	aframe.core
	 * @author		Andrew Lyon
	 */
	class run extends base
	{
		/**
		 * Our slappy constructor.
		 * 
		 * @param object &$event	Event object. Holds all other objects. One object to rule them all.
		 */
		public function run(&$event)
		{
			$this->_init($event);
		}
		
		/**
		 * Main framework function, does most of the loading and running.
		 * 
		 * Splits up the URL, checks if it has any routes. If so, loads the route and
		 * the assocciated controller/action, otherwise it loads the controller/action
		 * based on the URL params (/controller/action/args).
		 * 
		 * Also checks if the page is allowed to be in HTTPS. If the URL is https:// but
		 * it's not allowed, user will be forwarded to http://
		 * 
		 * Loads the layout (unless otherwise specified) and displays final contents.
		 * 
		 * This function could be split up into 300 different classes, but the beauty of
		 * it is its speed...so suck my balls, OOP!
		 */
		public function parse()
		{
			$event	=	&$this->event;
			$error	=	&$event->object('error');
			
			if(APP_ERROR_HANDLING)
			{
				// set our application's error handler. 
				$error_handler	=	&$this->event->controller('error_handler', array(&$this->event), false, false);
				set_error_handler(array($error_handler, 'app_error'), E_ALL);
			}
			
			// Load and proccess our URL
			if(PATH_DISPATCHER)
			{
				$url	=	isset($_SERVER['REQUEST_URI']) ? preg_replace('/(\?|\&).*/', '', $_SERVER['REQUEST_URI']) : '';
			}
			else
			{
				$url	=	isset($_GET['url']) ? $_GET['url'] : '';
			}
			
			// if we have a WEBROOT define, make sure it is NOT included when parsing the URL. this way if we're in a subdir
			// like /project, then setting WEBROOT to '/project' and going to site.com/project/users/view will load 
			// users/view as the action instead of project/users
			if(defined('WEBROOT'))
			{
				$webroot	=	WEBROOT;
				if(!empty($webroot))
				{
					// we have a webroot, replace it in the URL with a blank string
					$url	=	str_replace(WEBROOT, '', $url);
				}
			}
			
			// get our request method (GET/POST/PUT/DELETE) and save it
			$method	=	$event->get('_method', null);
			$method	=	empty($method) && isset($_SERVER['REQUEST_METHOD']) && !empty($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : $method;
			if(!empty($method))
			{
				$request_method	=	$method;
			}
			else
			{
				$request_method	=	'GET';
			}
			$event->set('_method', $request_method);
			
			// remove leading/trailing slash
			$url	=	preg_replace('/(^\/|\/$)/', '', $url);
			
			// explode URL into workable arguments
			$args	=	explode('/', $url);
			if(isset($args[0]) && $args[0] == '')
			{
				$args	=	array();
			}
			$arg_count	=	count($args);
			
			// Set some defaults we may/may not override
			$this->controller	=	'main';
			$this->action		=	'index';
			$this->params		=	array();
			
			if(!CRON_JOB)
			{
				$app_controller	=	new app_controller($event);
				if(method_exists($app_controller, 'pre_route'))
				{
					$app_controller->pre_route();
				}

				// run our routing. started getting pretty hairy and warranted its own method.
				$this->route($url, $args, $request_method, (defined('ROUTE_LIBRARY') ? ROUTE_LIBRARY : false));
			}
			
			// do our HTTPS checking
			if(!$this->ssl_check())
			{
				// User is in HTTPS on a no-ssl-allowed section of site...redirect to HTTP
				$redir	=	'http://'. SITE . $_SERVER['REQUEST_URI'];
				header('HTTP/1.1 301 Moved Permanently');
				header('Location: '. $redir);
				die();
			}
			
			if(CRON_JOB)
			{
				$argv	=	$GLOBALS['_argv'];		// pull from argv we stored during bootup (index.php)
				if(!isset($argv[1]))
				{
					print('cron action not specified');
					die(1);
				}
				
				$this->controller	=	CRON_CONTROLLER;
				$this->action		=	$argv[1];
				$this->params		=	array_slice($argv, 2);
				$url				=	'/'. CRON_CONTROLLER .'/'. $this->action;
			}
			
			// create a function-name safe variable for calling the action (actual action var will be left untouched)
			$action_name		=	preg_replace('/[^a-z0-9\_]/i', '_', $this->action);
			$controller_name	=	preg_replace('/[^a-z0-9\_]/i', '_', $this->controller);
			
			// Set some globals to help us out later on
			$event->set('_controller', $this->controller);
			$event->set('_action', $this->action);
			$event->set('_arguments', $this->params);
			$event->set('_url', $url);
			
			// open our templating object
			$template	=	&$event->object('template');
			
			// assign the view helper into the template for the views to use. does form building
			// and automated pagination 
			$template->assign('helper', $event->object('view_helper', array(&$event)));
			
			// start an output buffer to capture framework output for later manipulation 
			// such as HTML compression (if desired)
			ob_start();
			
			// Load and run our controller->action(args)
			if($controller = &$event->controller($controller_name, array(&$event)))
			{
				if(method_exists($controller, $this->action))
				{
					// our action exists in our controller - load it
					call_user_func_array(
						array(
							$controller,
							$action_name
						),
						$this->params
					);
				}
				elseif(isset($controller->catch_all) && method_exists($controller, $controller->catch_all))
				{
					// If variable $catch_all is found the controller, and the specified action doesn't exist,
					// we call this "catch-all" method and pass in all of the URL parameters after the controller name
					call_user_func_array(
						array(
							$controller,
							$controller->catch_all
						), 
						array_merge(
							array($this->action), 
							array($this->params)
						)
					);
				}
				else
				{
					// booo our action doesnt exist, and we dont have a catchAll...load the missing_action error.
					$error->err('missing_action', array($this->action));
				}
			}
			else
			{
				// The specified controller doesn't exist. Call the error object. Any pre-error app-specific code
				// can be run in the 'error_handler' class in the app's /controllers directory
				$error->err('missing_controller', array($this->controller));
			}

			// Call any finishing we need to do before rendering
			$controller->finish();
			
			// do we want to display the main layout?
			$show_layout	=	$event->get('show_layout', true);
			
			if($show_layout)
			{
				// Load our layout
				$content	=	$template->show_layout();
				echo $content;
			}
			else
			{
				// no layout, just echo the main contents of the rendered view
				$content	=	$template->get_template_vars('main_content');
				echo $content;
			}
			
			// get our completed output and... 
			$content	=	ob_get_contents();
			ob_end_clean();
			
			// ...REGURGITATE!
			echo $content;
			
			// any cleaning up we need to do
			$controller->post();
			
			if(isset($controller->model) && is_object($controller->model) && method_exists($controller->model, '_post'))
			{
				$controller->model->_post();
			}
		}
		
		/**
		 * Routing function. Takes a URL, arguments ('/' split URL), request method (GET/POST/PUT/DELETE)
		 * and returns a controller/action pair and corresponding arguments.
		 * 
		 * @param string $url				main url we are routing
		 * @param array $args				flat argument array (/events/view/4 => array('events', 'view', '4'))
		 * @param string $request_method	GET/POST/PUT/DELETE/WHATEVER
		 * @param string $route_library		set to an object name within the application's controller folder
		 * 									to run application custom routing. null == do traditional routing
		 * @return bool						true. sets needed vars into object scope, no need for return
		 */
		public function route($url, $args, $request_method, $route_library = null)
		{
			// default to false
			$route_found	=	false;
			$arg_count		=	count($args);
			$request_method	=	strtolower($request_method);
			
			// get our routes
			$routes			=	$this->event->get('routes', array());
			
			if($route_library)
			{
				// we have a custom routing controller...load it and process our routes.
				$routelib	=	&$this->event->library($route_library, array(&$this->event), true, false);
				$route		=	$routelib->route($url, $args, $routes, $request_method);
				
				if($route !== false)
				{
					// fo real, baby...we's got a route.
					$this->controller	=	$route['route']['controller'];
					$this->action		=	$route['route']['action'];
					$this->params		=	$route['params'];
					$route_found		=	true;
					
					// used later to set into the event scope
					$route				=	$route['route'];
				}
			}
			else
			{
				// basic routing, uses array hash lookups which is extremely fast and a good start for most 
				// applications
				
				// create a URL for checking our route against (not an exact match of the current url,
				// for ex if we go to /events/view/16, our route url will be /events/view. This gives
				// us a LOT more flexibility with our routes.
				$rurl				=	'/';
				$rurl_1up			=	'//';
				$route_arg_count	=	0;
				if($arg_count > 0)
				{
					$rurl				.=	$args[0];
					$route_arg_count	=	1;
					if($arg_count > 1)
					{
						$rurl				.=	'/' . $args[1];
						$rurl_1up			=	'/' . $args[0] . '/*';
						$route_arg_count	=	2;
					}
				}
				
				// catch a route if we have one... prefers exact matches first, but also accepts "one level up"
				// routes...for ex: if the url is /pages/view, it will check for /pages/view first. If it doesn't
				// find a route for /pages/view, it will look for one for /pages
				if(
					(isset($routes[$rurl]) && (isset($routes[$rurl]['controller']) || isset($routes[$rurl][$request_method])) ) || 
					(isset($routes[$rurl_1up]) && (isset($routes[$rurl_1up]['controller']) || isset($routes[$rurl_1up][$request_method])) )
				)
				{
					// We have a route! Load it, checking our request methods first (most specific -> least specific)
					if(isset($routes[$rurl]))
					{
						$route	=	isset($routes[$rurl]['controller']) ? $routes[$rurl] : $routes[$rurl][$request_method];
					}
					else
					{
						$route	=	isset($routes[$rurl_1up]['controller']) ? $routes[$rurl_1up] : $routes[$rurl_1up][$request_method];
						$route_arg_count	=	1;
					}
					
					if(!empty($route['controller']))
					{
						$this->controller	=	$route['controller'];
					}
					
					if(!empty($route['action']))
					{
						$this->action		=	$route['action'];
					}
					
					// saves ALL our items in the URL after the route match as params
					if($arg_count > $route_arg_count)
					{
						$this->params		=	array_slice($args, $route_arg_count);
					}
					
					$route_found	=	true;
				}
				else if(isset($routes['*']) && (isset($routes['*']['controller']) || isset($routes['*'][$request_method])))
				{
					// no specific routes matches, but we DO have a catch-all. load it
					$route	=	isset($routes['*']['controller']) ? $routes['*'] : $routes['*'][$request_method];
					
					if(!empty($route['controller']))
					{
						$this->controller	=	$route['controller'];
					}
					
					if(!empty($route['action']))
					{
						$this->action		=	$route['action'];
					}
					
					$route_found	=	true;
				}
			}
			
			if(!$route_found)
			{
				// No route specified, run as normal /controller/action/args
				if($arg_count > 0)
				{
					$this->controller	=	$args[0];
				}
				if($arg_count > 1)
				{
					$this->action		=	$args[1];
				}
				if($arg_count > 2)
				{
					$this->params		=	array_slice($args, 2);
				}
			}
			else
			{
				$this->event->set('_route', $route);
			}
			
			return true;
		}
		
		/**
		 * Close anything that needs closing. Disconnect anything that needs disconnecting. Our objects are tired after a long
		 * request's work and need some rest.
		 */
		public function cleanup()
		{
			$cache	=	&$this->event->object('cache');
			$cache->close();
			if(DATABASE)
			{
				$db	=	&$this->event->object('_db');
				$db->disconnect();
			}
		}
		
		/**
		 * If not using HTTPS, returns true. If using HTTPS and current controller/action is in allowed https list, returns true.
		 * Otherwise, returns false.
		 * 
		 * @return bool		Whether or not HTTPS is allowed for the current URL (only applicable if IN HTTPS ;))
		 */
		private function ssl_check()
		{
			$have_https	=	false;
			$keys		=	$this->event->get('https_keys', array('HTTPS'));
			
			// search $_SERVER for all of our HTTPS keys
			foreach($keys as $key)
			{
				if(isset($_SERVER[$key]) && $_SERVER[$key] == 'on')
				{
					// found one, and it's on!
					$have_https	=	true;
					break;
				}
			}
			
			if(!$have_https)
			{
				// no https found, just return
				return true;
			}
			
			$https	=	$this->event->get('https', array());
			
			$url	=	'/'.$this->controller;
			if($this->action != 'index')
			{
				$url	.=	'/'.$this->action;
			}
			
			if(isset($https[$url]) || isset($https['*']))
			{
				// let the app know (if it cares) that we are in https
				$this->event->set('_in_https', true);
				return true;
			}
			
			return false;
		}
	}
?>
