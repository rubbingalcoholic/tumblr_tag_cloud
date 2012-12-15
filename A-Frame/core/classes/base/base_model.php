<?
	/**
	 * This file holds the base model, which is extended by all models to give them SUPER abilities
	 * such as automatic insert/updates, error checking, etc.
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
	 * Include the input validation class (deprecated, but included for backwards compatibility)
	 */
	include_once CLASSES . '/input_validation.php';
	
	/**
	 * Include the data validation class
	 */
	include_once CLASSES . '/data_validation.php';
	
	/**
	 * Basic communication layer to database. Mimics a lot of the CakePHP base model DB layer. ALSO
	 * does basic error checking with the aframe.includes.classes.input_validation class. Basically,
	 * provides an abstraction and simplification to model building, DB communication, and error
	 * checking.
	 * 
	 * @package		aframe
	 * @subpackage	aframe.core
	 * @author		Andrew Lyon
	 */
	class base_model extends base_db
	{
		/**
		 * our input validator
		 * @var object
		 */
		var $inp;
		
		/**
		 * The error var, the error var; it tells us quite a lot. It tells when there is
		 * an error, and it tells us when there's not. (form validation error)
		 * @var bool
		 */
		var $error	=	false;
		

		/**
		 * Init function, calls Base::_init() and loads the DB class.
		 * 
		 * @param object &$event	Event object
		 */
		function _init(&$event)
		{
			parent::_init($event);
			
			$this->inp				=	&$event->object('input_validation', array(&$event));
			$this->data_validation	=	&$event->object('data_validation', array(&$event));
		}
		
		/**
		 * DEPRECATED! Use base_model::validate() instead!!
		 * Add a form value into the validation system for checking. Allows developer
		 * to check all values at once instead of going through each one by one.
		 * 
		 * @param string $key		Array key of item. 'username' would pull up $data['username']
		 * @param string $type		One of the predefined types. See input_validation::check()
		 * @param bool $required	Is the form field required
		 * @param string $msg		Error message to spit out upon input error
		 * @param int $length		For strings...maximum length
		 * @return					Absolutely nothing
		 * @see						base_model::validate()
		 */
		function add_val($key, $type, $required, $msg, $length = -1)
		{
			$this->inp->add($key, $type, $required, $msg, $length); 
		}
		
		/**
		 * Wrapper around the data validation class. Does it's best to detect obsolete
		 * behavior and run the old validator, but otherwise runs the new and improved
		 * data validation class.
		 *
		 * Keep in mind this function has the side effect of modifying $data during
		 * validation...so keep a valid copy handy if you need the original.
		 *
		 * @param mixed $data				data to validate
		 * @param array $format				how to format data. if blank, defaults to old validation
		 * @param bool $remove_extra_data	whether or not to get rid of data not present in $format
		 * @return mixed					successful. 
		 */
		function validate(&$data, $format = null, $options = array())
		{
			if(empty($format))
			{
				return $this->inp->check($data);
			}
			else
			{
				return data_validation::validate($data, $format, $options);
			}
		}
		
		/**
		 * Add an error to the form system to let the user know of an input validation
		 * error.
		 */
		function err($msg, $field)
		{
			$this->msg->add($msg);
			$form_errors	=	$this->event->get('form_errors', array());
			$form_errors[]	=	$field;
			$this->event->set('form_errors', $form_errors);
			$this->error	=	true;
		}
		
		/**
		 * Called after the entire app is done processing. Can be used to close any
		 * 3rd party DB connections and such. Useful for general model cleanup.
		 */
		function _post()
		{
		}
	}
?>
