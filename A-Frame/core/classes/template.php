<?
	/**
	 * Holds the template object, which sort of mirrors smarty, but without the horrible
	 * scripting language within a scripting language.
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
	 * Thuper duper template object.
	 * 
	 * Object that very closely mimics the smarty templating engine, except for one difference...
	 * it doesnt suck. It's the same concept without the totally shitty scripting language - 
	 * just pure PHP.
	 * 
	 * @package		aframe
	 * @subpackage	aframe.core
	 * @author		Andrew Lyon
	 */
	class template extends base
	{
		/**
		 * Where the wild templatez are
		 * @var string
		 */
		var $template_dir;
		
		/**
		 * The layout were going to display
		 * @var string
		 */
		var $layout;
		
		/**
		 * Where the wild layoutz are
		 * @var string
		 */
		var $layout_dir;
		
		/**
		 * This variable holds all the data we are passing to the view
		 * @var array
		 */
		var $data;
		
		/**
		 * Constructor. Inits with the event object and sets some other defaults.
		 * 
		 * @param object &$event		Event object
		 * @param string $template_dir	(optional) Base dir to load templates from
		 * @param string $layout_dir	(optional) Base layout directory
		 */
		function template(&$event, $template_dir = '', $layout_dir = '')
		{
			$this->_init($event);
			$this->template_dir	=	empty($template_dir) ? APPPATH . 'views' : $template_dir;
			$this->layout_dir	=	empty($layout_dir) ? APPPATH . 'views/layouts' : $layout_dir;
			$this->data			=	array();
			$this->set_layout('default');
		}
		
		/**
		 * Push a variable into the view we'll be displaying. If I do
		 * template->assign('shit', 'chunky w/ corn') in my controller, upon loading my view,
		 * if I echo $shit, I'll get 'chunky w/ corn'...amazing hehe. 
		 * 
		 * @param string $key		variable that of which into the template we are asigning
		 * @param mixed $value		value to set into template
		 */
		function assign($key, $value)
		{
			$this->data[$key]	=	$value;
		}
		
		/**
		 * Push a variable into the view we'll be displaying BY REFERENCE. Same as
		 * template::assign(), but uses reference.
		 * 
		 * @param string $key		variable that of which into the template we are asigning
		 * @param mixed &$value		DEEEERRRR WUTS UH VALU!?!?!
		 * @see						template::assign()
		 */
		function assign_by_ref($key, &$value)
		{
			$this->data[$key]	=	&$value;
		}
		
		/**
		 * Another Smarty mimic function. As the name suggests, it 'gets template vars'
		 * 
		 * @param string $key		(optional) key to load value from
		 * @return mixed			Value stored at $key in template vars. Returns reference.
		 */
		function &get_template_vars($key = null)
		{
			if(empty($key))
			{
				return $this->data;
			}
			elseif(isset($this->data[$key]))
			{
				return $this->data[$key];
			}
			else
			{
				$tmp	=	NULL;
				return $tmp;
			}
		}
		
		/**
		 * Assign all variables in template::$data to the local scope, render the given view, and
		 * return the content.
		 * 
		 * @param string $template		location:name of template
		 * @param bool $throw			throw (up) error if template not found (default = true) BLEGGGH
		 * @return string				content of rendered template
		 */
		function fetch($template, $throw = true)
		{
			if(!empty($template))
			{
				// remove obnoxious warnings and notices in templates, and store previous level in var (like smarty)
				$error_level	=	ini_set("error_reporting", E_ALL & (~E_NOTICE & ~E_WARNING));
				
				// check what kind of template we're loading
				if(preg_match('/^file:/', $template))
				{
					// we're loading an absolute path
					$file	=	preg_replace('/^file:/', '', $template);
				}
				else
				{
					// not an absolute path, give it one of $template_dir...DERR
					$file	=	$this->template_dir . DIRECTORY_SEPARATOR . $template;
				}

				$old_file	=	$file;		// Just in case there's a view error, we want to keep an accurate filename
				
				// standardize the filename...we only need one .php
				$file		=	preg_replace('/\.php$/', '', $file);
				$file		.=	'.php';
				
				if(!file_exists($file))
				{
					// sorry, template doesn't exist...what now?
					if($throw)
					{
						// throw an error tantrum. load our error object and err it
						$error	=	&$this->event->object('error');
						$error->err(
							'loading_view',
							array(
								'controller'	=>	$this->controller,
								'action'		=>	$this->action,
								'template'		=>	$template,
								'file'			=>	$file
							)
						);
					}
					else
					{
						// ...entering stealth mode...
						return false;
					}
				}
				
				ob_start();		// ob_FART();!!1 LOL!
				
				// great, file exists, include it.
				$this->do_include_template($file);
				
				// grab our output (thanks, ob!)
				$output	=	ob_get_contents();
				ob_end_clean();

				// resume our paranoid error reporting
				ini_set("error_reporting", $error_level);

				return $output;
			}
		}

		/**
		 * The purpose of this tiny function is to allow a view to "return" or break out of
		 * the template. Although a bit of an edge case, it can many times save wrapping an 
		 * entire view in one big if {} statement.  
		 * 
		 * @param string $file	Filename to include
		 */
		private function do_include_template($file)
		{
			// now we push all our data into the view which we are about to include
			extract($this->data, EXTR_OVERWRITE | EXTR_REFS);

			include $file;
		}
		
		/**
		 * Get the content for a layout and display it
		 * 
		 * This function returns the rendered content for a template from TemplateObject::fetch().
		 * 
		 * @param string $resource_name		path to template
		 */
		function show_layout($file = '')
		{
			$file		=	'file:' . $this->layout_dir . DIRECTORY_SEPARATOR . $this->get_layout() .'.php';
			$content	=	$this->fetch($file);
			return $content;
		}
		
		/**
		 * Set the current layout
		 * 
		 * @param string $layout	Layout to set
		 */
		public function set_layout($layout)
		{
			$this->layout	=	$layout;
		}

		/**
		 * Get the current layout
		 * 
		 * @return string	Current layout
		 */
		public function get_layout()
		{
			return $this->layout;
		}
	}
?>
