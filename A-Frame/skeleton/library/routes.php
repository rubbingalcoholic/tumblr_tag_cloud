<?
	/**
	 * This file holds an example library for customized routing. Uses REST-style routes and
	 * can set media types and user access through routes as well. 
	 * 
	 * This is included as an example for customized routing, one of a-frame's features.
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
	 * REST routes class. Very robust class for using regexes and HTTP methods for routing.
	 * Parses doc comments of controllers to find routing array, and caches them (must have
	 * caching enabled in your app!). 
	 * 
	 * @author Andrew Lyon
	 * @package		aframe
	 * @subpackage	aframe.skeleton
	 */
	class routes extends base
	{
		/**
		 * Runs a URL against a list of regexes (route array keys) to find matching routes.
		 * 
		 * The routes are loaded by routes::load_routes(), which reads doc comments for route
		 * patterns and parses them into usable routes. For instance:
		 * 
		 * !route GET: /events/([0-9]+)
		 * 
		 * Will create a route such that when /events/6 is visited, the function directly below
		 * the doc comment will be called using '6' as the first argument for that function. This 
		 * is a robust and simple way to keep routing outside of a single file (gets messy). 
		 * 
		 * Also note the request method. You can specify whatever HTTP method you'd like in the
		 * route, and that function will only be called if that request method is used. In the
		 * example above, if I were to POST to /events/6, the route would NOT match because it is
		 * only accepting a GET. You could do multiple routes:
		 * 
		 * !route GET:	/events/([0-9]+)
		 * !route POST:	/events/([0-9]+)
		 * 
		 * This routing style is prefect for building REST web services as you can route depending
		 * on GET, POST, PUT, DELETE, OPTIONS, etc. 
		 */
		function route($url, $args, $routes, $request_method, $trigger_errors = true)
		{
			$return			=	false;
			$route_found	=	false;
			
			// forget the system routes, load our own from doc comments
			$routes			=	$this->load_routes();
			
			// advanced routing, looop through routes treating each key as a regex, until a match is found.
			// very flexible, very slow. may want to inbreastigate adding caching.
			foreach($routes as $pattern => $route)
			{
				// init empty matches array for regex to pull args out of
				$matches	=	array();
				$rurl		=	'/' . urldecode($url);
				$rurl		=	preg_replace('/\/*$/', '', $rurl);
				$pattern	=	str_replace('/', '\/', $pattern);
				$regex		=	'/^'. $pattern .'$/';
				
				if(preg_match($regex, $rurl, $matches))
				{
					if(!isset($route[$request_method]))
					{
						if($trigger_errors)
						{
							// the route matches, but the request method doesn't. someone is trying
							// to POST to something that only takes GET or something...send them packing
							// (fudge packing)
							trigger_error('that method is not allowed on this resource', E_USER_ERROR);
						}
						else
						{
							continue;
						}
					}
					
					$route	=	$route[$request_method];
					
					// pop off first item in matches (the full pattern match)
					array_shift($matches);
					
					// set to true so we don't bother using default route
					$route_found	=	true;
					
					// route found, no more need to loop
					break;
				}
			}
			
			if($route_found)
			{
				$return	=	array(
					'route'		=>	$route,
					'params'	=>	$matches
				);
			}
			else if($trigger_errors)
			{
				
				trigger_error('the requested resource doesn\'t exist', E_USER_ERROR);
			}
			
			return $return;
		}
		
		/**
		 * Load routes from doc comments in controllers. Routes file is huge and messy. This will solve it
		 * elegantly:
		 * 
		 * !route (GET|POST|PUT|DELETE|...): /path/regex/([0-9]+)/etc
		 * !media ANY_DEFINE_CAN_GO_HERE
		 * !access * -guest
		 * 
		 * Very nice. !media and !access are optional parameters, and must be handled by the application (they
		 * are ignored while routing, but saved for later use). !media is used to define which media type is
		 * associated with a method, and !access is used to define which users can access the method. They are
		 * beyond the scope of this class, and as mentioned, must be handled by the app (usually in 
		 * app_controller).
		 * 
		 * @return array		associative routing information loaded from doc comments
		 */
		function load_routes()
		{
			$ckey	=	'routes:controller:routes';
			
			// init empty routes array
			$routes	=	array();
			
			if(($routes = $this->cache->get($ckey)) === false)
			{
				// OOOOP!! no cached item, have to fetch routes from doc comments
				
				// get a list of files in our CONTROLLERS directory
				$d		=	dir(CONTROLLERS);
				$files	=	array();
				while(($file = $d->read()) !== false)
				{
					// make sure we only include non-system controllers (for much better security)
					if($file{0} != '.' && $file != 'app_controller.php' && $file != CRON_CONTROLLER.'.php' && $file != 'error_handler.php')
					{
						// we have a controller! add it
						$files[]	=	$file;
					}
				}
				$d->close();
				
				// loop over the controller we got and find any !route references in the comments
				foreach($files as $file)
				{
					// get the file source
					$src		=	file_get_contents(CONTROLLERS . '/' . $file);
					
					// get the class name (used for the 'controller' key later)
					$matches	=	array();
					preg_match('/(^|[\n\r])\s*class\s+(.*?)\s+/i', $src, $matches);
					$class		=	trim($matches[2]);
					
					// find ALL instances of !route in the file and save them (also save the "function ..."
					// entry immediately following
					$matches	=	array();
					preg_match_all('/\/\*\*.*?((!route.*?[\r\n])+.*?function .*?)\(/is', $src, $matches);
					$matches	=	$matches[1];
					
					// loop over our !routes we found and parse them
					foreach($matches as $route)
					{
						// get the function name from the route
						$function	=	trim(preg_replace('/.*function[\s\r\n]+([\w]+).*/is', '$1', $route));
						$security	=	null;
						if(strpos($route, '!access'))
						{
							$security	=	trim(preg_replace('/.*!access\s+([^\r\n]+).*/is', '$1', $route));
						}
						
						// get the media type. if we actually have one, eval() it (i know, i know) and get
						// it's actual value. this allows us to use DEFINES for !media instead of hard-coding
						// a string.
						$media		=	array();
						preg_match('/!media[\s]+([\w]+)/is', $route, $media);
						$media		=	isset($media[1]) ? eval('return defined("' . $media[1].'") ? '. $media[1] .' : null;') : null;
						
						// split the remaining !route entries for this function
						preg_match_all('/!route[\s]+([a-z]+):[\s]+(.*?)[\r\n]/is', $route, $matches);
						
						// loop over the individual route entries and build our actual route
						for($i = 0, $n = count($matches[1]); $i < $n; $i++)
						{
							// pull the route regex and the method (GET|POST|DELETE|etc)
							$method	=	strtolower($matches[1][$i]);
							$regex	=	$matches[2][$i];
							
							// build the route
							$routes[$regex][$method]	=	array(
								'controller'	=>	$class,
								'action'		=>	$function,
								'media'			=>	$media,
								'security'		=>	$security
							);
						}
					}
				}
				
				// cache for a mothafuckin week
				$this->cache->set($ckey, $routes, 604800);
			}
			
			return $routes;
		}
	}
?>
