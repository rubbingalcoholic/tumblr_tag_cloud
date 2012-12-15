<?
	/**
	 * This file holds the event class, responsible for object loading and inter-object and framework-
	 * app communication.
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
	 * The event object holds data. That is its primary function.
	 * 
	 * Event not only holds data, but it loads application and framework classes. It stores them 
	 * in the $GLOBALS variable. This re-use of objects saves on instantiation times and makes
	 * programming easier in general.
	 * 
	 * The reason $GLOBALS is used is because every app class (and some aframe classes) hold the event 
	 * object as a member. If all the objects exist in event::$data, and event lives in an object, debugging
	 * a problem with that object can be a daunting task because a print_r will show under that object ALL
	 * other objects and each of those objects will have all other objects within them. It is generally a 
	 * good practice to stay away from global variables, but the way the event object (and the rest of 
	 * aframe) is set up is such that there is a single write point (event) and single read point (event), 
	 * and nothing else even KNOWS about $GLOBALS storage. It is an insanely stupid idea to access this
	 * global variable from anywhere other than event, whether in app OR framework...unless in extreme
	 * cases of debugging. Also, in the case an idiot developer would set $event into session, there isn't
	 * 3MB of object data in there.
	 * 
	 * Long story short, using $GLOBALS cuts back on a lot of recursion and annoyance.
	 *  
	 * The main purpose of event is to hold all useful information, app-specific or otherwise, and provides
	 * a means to transport all this information from object to object easily and cleanly. Think of it
	 * as a global variable register.
	 * 
	 * One object to rule them all.
	 * 
	 * @package		aframe
	 * @subpackage	aframe.core
	 * @author Andrew Lyon
	 */
	class event
	{
		/**
		 * Stores what the default return value for get() or get_ref() is
		 */
		public $get_default	=	'';
		
		/**
		 * Constructor.
		 * 
		 * Initializes some data vars. not much here.
		 * 
		 * @param mixed $data	(optional) Pre-defined data to load into event object upon init
		 */
		public function event($data = null)
		{
			$GLOBALS['_event']		=	array();
			$GLOBALS['_obj']		=	array();
		}
		
		/**
		 * Get value $key out of $GLOBALS['_event']. If $GLOBALS['_event'][$key] does not exist, $default is returned.
		 * 
		 * @param string $key		Key to load data from in $GLOBALS['_event']
		 * @param mixed $default	(optional) If our $key doesn't exist, return $default instead
		 * @return mixed			Data held in $GLOBALS['_event'], or $default
		 */
		public function get($key, $default = '')
		{
			$default	=	$default === '' ? $this->get_default : $default;
			
			if(isset($GLOBALS['_event'][$key]))
			{ 
				return $GLOBALS['_event'][$key]; 
			}
			return $default;
		}
		
		/**
		 * Much like event::get(), but returns a reference to data stored in $GLOBALS['_event'][$key] instead of
		 * actual data.
		 * 
		 * @param string $key		Key to load data from in $GLOBALS['_event']
		 * @param mixed $default	(optional) If our $key doesn't exist, return $default instead
		 * @return mixed			Data held in $GLOBALS['_event'], or $default, returned by reference
		 * @see event::get()
		 */
		public function &get_ref($key, $default = '')
		{
			$default	=	$default === '' ? $this->get_default : $default;

			if(!isset($GLOBALS['_event'][$key]))
			{
				$GLOBALS['_event'][$key]	=	$default;
			}
			return $GLOBALS['_event'][$key];
		}
		
		/**
		 * Set a value in $GLOBALS['_event'] using $key. Sister function to event::get(). get() gets data out,
		 * set() puts data in...simple, right?
		 * 
		 * @param string $key	Key under which to file our $value
		 * @param mixed $value	The data we are saving
		 */
		public function set($key, $value)
		{
			$GLOBALS['_event'][$key]	=	$value;
		}
		
		/**
		 * Sets a value into our data array by reference instead of copying.
		 * 
		 * @param string $key	Key under which to file our $value
		 * @param mixed &$value	The data we are saving (by reference)
		 * @see					event::set()
		 */
		public function set_ref($key, &$value)
		{
			$GLOBALS['_event'][$key]	=	&$value;
		}
		
		/**
		 * Load an aframe object. Can be model, controller, library, or any old object laying around. This
		 * is the most basic form of object loading in event...the full path is used and nothing is assumed.
		 * Calls event::load_object() to do the loading and storing of object
		 * 
		 * @param string $class		Name/path of class to load
		 * @param array $params		The values we pass into $class's CTOR
		 * @param bool $overwrite	If the object already exists in $GLOBALS['_OBJ'], do we overwrite original 
		 * 							and re-instantiate?
		 * @param bool $run_init	Run the init() function of the object after instantiating? And yes, we  
		 * 							DO check if $class->init() exists...
		 * @return &object			The object we instantiated
		 * @see						event::load_object()
		 */
		public function &object($class, $params = array(), $overwrite = false, $run_init = true)
		{
			if(isset($GLOBALS['_obj'][$class]) && is_object($GLOBALS['_obj'][$class]) && !$overwrite)
			{
				return $GLOBALS['_obj'][$class];
			}
			
			return $this->load_object($class, $params, $run_init);
		}
		
		/**
		 * Load an aframe controller. Assumes the path is the CONTROLLERS constant, and looks for files
		 * under that directory. Sets the appropriate class attributes and calls event::load_object()
		 * 
		 * @param string $class		Name of controller to load
		 * @param array $params		The values we pass into $class's CTOR
		 * @param bool $overwrite	If the object already exists in $GLOBALS['_obj'], do we overwrite original 
		 * 							and re-instantiate?
		 * @param bool $run_init	Run the init() function of the object after instantiating? And yes, we  
		 * 							DO check if $class->init() exists...
		 * @return object			The reference to the object we instantiated
		 * @see						event::load_object()
		 */
		public function &controller($class, $params = array(), $overwrite = false, $run_init = true)
		{
			if(isset($GLOBALS['_obj'][$class]) && is_object($GLOBALS['_obj'][$class]) && !$overwrite)
			{
				return $GLOBALS['_obj'][$class];
			}
			
			if(!class_exists($class))
			{
				if(file_exists(CONTROLLERS . '/'. $class .'.php'))
				{
					include_once CONTROLLERS . '/'. $class .'.php';
				}
			}
			
			return $this->load_object($class, $params, $run_init, 'controller');
		}
		
		/**
		 * Load an aframe model. Assumes the path is the MODELS constant, and looks for files
		 * under that directory. Sets the appropriate class attributes and calls event::load_object()
		 * 
		 * @param string $class		Name of model to load
		 * @param array $params		The values we pass into $class's CTOR
		 * @param bool $overwrite	If the object already exists in $GLOBALS['_obj'], do we overwrite original 
		 * 							and re-instantiate?
		 * @param bool $run_init	Run the init() function of the object after instantiating? And yes, we  
		 * 							DO check if $class->init() exists...
		 * @return object			The reference to the object we instantiated
		 * @see						event::load_object()
		 */
		public function &model($class, $params = array(), $overwrite = false, $run_init = true)
		{
			// models are a bit tricky because we load them by name eg. 'chairs', and the filename is called
			// chairs.php, but the actual class name is 'chairs_model'. We ahve to keep this in mind when
			// including/loading
			$filename	=	$class;
			$class		.=	'_model';
			if(isset($GLOBALS['_obj'][$class]) && is_object($GLOBALS['_obj'][$class]) && !$overwrite)
			{
				return $GLOBALS['_obj'][$class];
			}
			
			if(!class_exists($class))
			{
				if(file_exists(MODELS . '/'. $filename .'.php'))
				{
					include_once MODELS . '/'. $filename .'.php';
				}
			}
			
			return $this->load_object($class, $params, $run_init, 'model');
		}
		
		/**
		 * Load an aframe library. Assumes the path is the LIBRARY constant, and looks for files
		 * under that directory. Sets the appropriate class attributes and calls event::load_object().
		 * Keep in mind, subdirectories are not supported, library file must be a direct child of the
		 * LIBRARY folder.
		 * 
		 * @param string $class		Name of library to load
		 * @param array $params		The values we pass into $class's CTOR
		 * @param bool $overwrite	If the object already exists in $GLOBALS['_obj'], do we overwrite original 
		 * 							and re-instantiate?
		 * @param bool $run_init	Run the init() function of the object after instantiating? And yes, we  
		 * 							DO check if $class->init() exists...
		 * @return object			The reference to the object we instantiated
		 * @see						event::load_object()
		 */
		public function &library($class, $params = array(), $overwrite = false, $run_init = true)
		{
			if(isset($GLOBALS['_obj'][$class]) && is_object($GLOBALS['_obj'][$class]) && !$overwrite)
			{
				return $GLOBALS['_obj'][$class];
			}
			
			if(!class_exists($class))
			{
				if(file_exists(LIBRARY . '/'. $class .'.php'))
				{
					include_once LIBRARY . '/'. $class .'.php';
					$controller	=	$class;
				}
			}
			
			return $this->load_object($class, $params, $run_init, 'library');
		}
		
		/**
		 * The brains behind event's object loading. Used by the other class loading functions to do the heavy
		 * lifting of the class loading.
		 * 
		 * Looks near and far for an object until it finds it. Looks in aframe/core/classes, 
		 * app/controllers, app/models, app/library, and any other places classes might be hiding. Loads the 
		 * class, calls init() (if specified), and sets the object into $GLOBALS['_obj']. If it can't
		 * find a class, it returns false.
		 * 
		 * If not specifying the class type event::controller(), event::library, etc, be sure to set a prefix:
		 * 'controllers/pages' instead of 'pages'. It will still find the pages controller, but it will take
		 * more guess work and nobody likes that.
		 * 
		 * @param string $class		Name/path of class to load
		 * @param array $params		The values we pass into $class's CTOR
		 * @param bool $run_init	Run the init() function of the object after instantiating? And yes, we  
		 * 							DO check if $class->init() exists...
		 * @return object			The reference to the object we instantiated
		 */
		private function &load_object($class, $params, $run_init, $type = '')
		{
			// check if our class already exists (cross your fingers). hopefully the helper functions did their 
			// job. $class INCLUDES the prefix, and intuitively it shouldn't, but the prefix needs to be there for
			// finding NEW classes that haven't been loaded yet.
			if(!class_exists($class))
			{
				// class doesn't exist, do all of our guessing.
				
				// if we have a class name prefix, load it.
				$prefix	=	preg_replace('/(^.*?)\/.*$/', '$1', $class);
			
				// get rid of the prefix.
				$class	=	preg_replace('/^.*\//', '', $class);
				
				// do all our prefix checking
				if($prefix == 'classes')
				{
					// we have an aframe class
					if(file_exists(CLASSES . '/'. $class .'.php'))
					{
						include_once CLASSES . '/'. $class .'.php';
					}
				}
				else if($prefix == 'controllers')
				{
					// we're loading a controller
					if(file_exists(CONTROLLERS . '/'. $class .'.php'))
					{
						include_once CONTROLLERS . '/'. $class .'.php';
						$type	=	'controller';
					}
				}
				else if($prefix == 'models')
				{
					// we're loading a model
					if(file_exists(MODELS . '/'. str_replace('_model', '', $class) .'.php'))
					{
						include_once MODELS . '/'. str_replace('_model', '', $class) .'.php';
					}
				}
				else if($prefix == 'library')
				{
					// we're loading a library
					if(file_exists(LIBRARY . '/'. $class .'.php'))
					{
						include_once LIBRARY . '/'. $class .'.php';
						$type	=	'library';
					}
				}
				else
				{
					// if all else fails, guess wildly - which is why we use the helper functions or prefixes ;)
					if(file_exists(CLASSES . '/'. $class .'.php'))
					{
						include_once CLASSES . '/'. $class .'.php';
					}
					else if(file_exists(CLASSES . '/db/'. $class .'.php'))
					{
						include_once CLASSES . '/db/'. $class .'.php';
					}
					else if(file_exists(CONTROLLERS . '/'. $class .'.php'))
					{
						include_once CONTROLLERS . '/'. $class .'.php';
					}
					else if(file_exists(MODELS . '/'. str_replace('_model', '', $class) .'.php'))
					{
						include_once MODELS . '/'. str_replace('_model', '', $class) .'.php';
					}
					else if(file_exists(LIBRARY . '/' . $class .'.php'))
					{
						include_once LIBRARY . '/' . $class .'.php';
					}
				}
			}
			
			// check to make sure our class actually exists
			if(!class_exists($class))
			{
				// needed because we ALWAYS return a reference :)
				$there_is_no_class = false;
				return $there_is_no_class;
			}
			
			// build our object argument list from the array passed in
			if(is_array($params)) 
			{
				// we have an array of params...start a somewhat non-graceful but EXTREMELY performance-enhancing set of ifs
				// that (hopefully) avoid almost ALL contact with eval()
				$pcount	=	count($params);
				if($pcount == 0)
				{
					// no params, just load the class
					$this->set_object($class, new $class());
				}
				else if($pcount == 1)
				{
					// as is very often the case, we may be only passing one argument (usually the event object)
					$this->set_object($class, new $class($params[0]));
				}
				else if($pcount == 2)
				{
					// sometimes we have two arguments
					$this->set_object($class, new $class($params[0], $params[1]));
				}
				else if($pcount == 3)
				{
					// just in case
					$this->set_object($class, new $class($params[0], $params[1], $params[2]));
				}
				else if($pcount == 4)
				{
					// we REALLY want to avoid eval. accounting for this many params should very well make sure eval is never called
					$this->set_object($class, new $class($params[0], $params[1], $params[2], $params[3]));
				}
				else
				{
					// cut my life into pieces, this is my last resort. SUFFOCATIO...
					$args	=	$this->build_args($params);
					$eval	=	'$this->set_object($class, new '. $class .'('. $args .'));';
					eval($eval);
				}
			}
			else if(empty($params))
			{
				// params is not an array(odd) but it's empty anyway
				$this->set_object($class, new $class());
			}
			else
			{
				// we don't have an array, just one param...pass it in!
				$this->set_object($class, new $class($params));
			}
			
			// check if all is well
			//if(is_object($GLOBALS['_obj'][$class]))
			if(is_object($this->return_object($class)))
			{
				// If we are loading a controller, be sure to set $controller to the object name  
				if($type == 'controller' || $type == 'library')
				{
					$GLOBALS['_obj'][$class]->controller	=	$class;
				}
				
				// Init after we set the controller var in case it needs it for initialization
				if($run_init && method_exists($GLOBALS['_obj'][$class], 'init'))
				{
					$this->return_object($class)->init();
				}
				
				return $this->return_object($class);
			}
			
			// uh oh... once again, need a variable because we always return a reference
			$there_definitely_is_no_class = false;
			return $there_definitely_is_no_class;
		}

		/**
		 * Single location for storing of created objects.
		 * 
		 * @param string $name		key to store object under
		 * @param object $object	object to store
		 * @return object			$object
		 */
		public function set_object($name, $object)
		{
			$GLOBALS['_obj'][$name]	=	&$object;
			return $object;
		}
		
		/**
		 * Pull an object directly from $GLOBALS['_obj']...in other words, if it doesn't exist, don't create!!
		 * 
		 * This is used a few times in the base/base_contoller classes for pulling objects that aren't quite
		 * ready to be instantiated.
		 * 
		 * @param string $name		Name of object to return
		 * @return object			Return the object, if it exists
		 */
		public function &return_object($name)
		{
			return $GLOBALS['_obj'][$name];
		}
		
		/**
		 * Used mainly by event::load_object() to build eval strings for class loading.
		 * 
		 * @param array $params		Params to build string with. Realistically, could just use a param
		 * 							count...but oh well.
		 * @return string			Argument string, ready to be run by eval! (sorta)
		 */
		private function build_args($params)
		{
			$args	=	'';
			for($i = 0, $n = count($params); $i < $n; $i++)
			{
				$args	.=	',$params['. $i .']';
			}
			$args	=	substr($args, 1);
			return $args;
		}

		/**
		 * There's a lot of data floating around - $_GET, $_POST, $_COOKIE - grab it all in the order
		 * CGP, later ones overriding previous, and shove it into $GLOBALS['_event']. Once this is done, the
		 * data can be accessed by event::get() or modified with event::set()! All pretty straightforward.
		 */
		public function populate()
		{
			$gpc	=	array_merge($_COOKIE, $_GET, $_POST);
			$t		=	array_keys($gpc);

			for($i = 0, $n = count($t); $i < $n; $i++)
			{
				$key	=	$t[$i];
				$val	=	$gpc[$key];
				
				$this->set($key, $val);
			}
		}
	}
?>
