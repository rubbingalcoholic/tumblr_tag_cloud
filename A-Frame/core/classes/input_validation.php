<?
	/**
	 * Holds aframe's input validation class, mainly used by models for data validation.
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
	 * Used by models for basic input validation and error checking. Some of the inputs can get a bit tricky
	 * and somewhat complicated, but nothing too bad.
	 * 
	 * @package		aframe
	 * @subpackage	aframe.core
	 * @author		Andrew Lyon
	 */
	class input_validation extends base
	{
		/**
		 * Holds the input data (name, type, error message, etc)
		 * @var array
		 */
		public $inputs	=	array();
		
		/**
		 * Add an error check to a form item.
		 * 
		 * Keep in mind this does NOT tie in with forms at all. It provides a way to error check values
		 * from WITHIN the models. The advanced checker is loaded when no other types fit, which can 
		 * check whether two values inthe passed in data are the same. For instance:
		 * 
		 * input_validation::add('password', 'string', true, 16);
		 * input_validation::add('confirm', '=[password]', true, 16);
		 * 
		 * That adds a value, 'password', which is to be checked as a string. The value of 'confirm' must be
		 * the EXACT same as 'password', or the validation will fail on 'confirm'
		 * 
		 * @param string $key		What key to store this input under (should match the array key of the data
		 * 							array passed in when doing the actual check
		 * @param string $type		The type to check for can be complex or simple: email, integer, string,
		 * 							float, bool, date
		 * @param bool $required	Whether or not the value being checked is required. handled differently
		 * 							for each check
		 * @param string $length	An int or string that determines the overall length. Follows the format:
		 * 							If simple int, strlen must be UNDER $length. If in format '50-', strlen 
		 * 							must be OVER 50 chars. If '-50', same as basic int...must be under 50
		 * 							chars. If in format '10-50', strlen must be above 10 chars and under 50.
		 */
		function add($key, $type, $required, $msg, $length)
		{
			$this->inputs[$key]	=	array(
				'type'		=>	$type,
				'required'	=>	($required === true || $required === 1) ? true : false,
				'msg'		=>	$msg,
				'length'	=>	$length
			);
		}
		
		/**
		 * Pass this function an array of data with keys corresponding to the keys set by
		 * input_validation::add() and it will systematically check the data array for errors. Automatically
		 * adds the message passed in to the $msg object.
		 * 
		 * @param array $data			The data we are going to validate, with keys corresponding to the ones added
		 * 								from input_validation::add()
		 * @param bool $clear_inputs	After running, set this to true to clear validation inputs
		 * @return bool					Whether or not the validation succeeded
		 */
		function check($data, $clear_inputs = true)
		{
			$msg	=	&$this->_get_msg();
			$error	=	true;
			foreach($this->inputs as $key => $cur)
			{
				$value	=	isset($data[$key]) ? $data[$key] : null;
				$result	=	false;
				switch($cur['type'])
				{
					case 'email'	:	$result	=	input_validation::check_email($value, $cur['required']); break;
					case 'integer'	:	$result	=	input_validation::check_int($value, $cur['required']); break;
					case 'string'	:	$result	=	input_validation::check_string($value, $cur['required'], $cur['length']); break;
					case 'float'	:	$result	=	input_validation::check_float($value, $cur['required']); break;
					case 'bool'		:	$result	=	input_validation::check_bool($value, $cur['required']); break;
					case 'date'		:	$result	=	input_validation::check_date($value, $cur['required'], $cur['length']); break;
					default			:	$result	=	input_validation::advanced($cur['type'], $data, $value, $cur['required']); break;
				}
				
				if(!$result)
				{
					$msg->add($cur['msg']);
					$error	=	false;
				}
			}
			
			if($clear_inputs)
			{
				$this->inputs	=	array();
			}
			
			return $error;
		}
		
		/**
		 * Checks two data values against eachother. For instance, it can check if a 'confirm' password is
		 * the same as the 'password'.
		 * 
		 * @param string $type		The variable we are comparing $value to: if $type = '=[password]', then $value
		 * 							is checked against $data['password']. If they are equal, then return true,
		 * 							else false
		 * @param array $data		We pass in the whole data array so we can dynamically check matches
		 * @param string $value		The value we are checking against
		 * @param bool $required	(optional) unused, here for forward compatibility
		 * @return bool				Whether or not $value and the $data item it was checked against are equal
		 */
		function advanced($type, $data, $value, $required = true)
		{
			if(preg_match('/^=\[(.*?)\]$/', $type, $matches))
			{
				if(isset($data[$matches[1]]) && $value == $data[$matches[1]])
				{
					return true;
				}
				return false;
			}
		}
		
		/**
		 * Use a regex to determine if $value is, in fact, an email address. 
		 * 
		 * @param string $value		Value to check against email regex
		 * @param bool $required	(optional) Whether or not this field is required. If false and $value = '' then
		 * 							return true
		 * @return bool				Whether or not email validates (or is blank and not required)
		 */
		function check_email($value, $required = false)
		{
			if(!$required && $value === '')
			{
				return true;
			}

			$valid	=	preg_match('/^(([a-z0-9!#$%&*+-=?^_`{|}~]
						[a-z0-9!#$%&*+-=?^_`{|}~.]*
						[a-z0-9!#$%&*+-=?^_`{|}~])
					 |[a-z0-9!#$%&*+-?^_`{|}~]|
					 ("[^"]+"))
					 [@]
					 ([-a-z0-9]+\.)+
					 ([a-z]{2,4}
						|com|net|edu|org
						|gov|mil|int|biz
						|pro|info|arpa|aero
						|coop|name|museum)$/ix', $value);
			return $valid;
		}
		
		/**
		 * Check $value to see if it's an integer.
		 * 
		 * @param string $value		Value to check
		 * @param bool $required	(optional) Whether or not value can be ommitted
		 * @return bool				If it's an int, true. If it's empty and not required, true.
		 */
		function check_int($value, $required = false)
		{
			if(!$required && ($value === '' || $value === null))
			{
				return true;
			}

			if(preg_match('/^[0-9]+$/', $value))
			{
				return true;
			}
			return false;
		}
		
		/**
		 * Check $value to see if it's a string. Since anything can be a string in PHP, we check the length 
		 * as well. Check out the long-winded description of the $length param for details.
		 * 
		 * @param string $value		Value to check
		 * @param bool $required	(optional) Whether or not this string is required
		 * @param string $length	(optional) An int or string that determines the overall length. Follows the 
		 * 							format: If simple int, strlen must be UNDER $length. If in format '50-', 
		 * 							strlen must be OVER 50 chars. If '-50', same as basic int...must be under 50
		 * 							chars. If in format '10-50', strlen must be above 10 chars and under 50.
		 */
		function check_string($value, $required = false, $length = -1)
		{
			$inrange	=	true;
			if(!is_numeric($length))
			{
				$length	=	explode('-', $length);
				if(!empty($length[0]) && strlen($value) < $length[0])
				{
					$inrange	=	false;
				}
				
				if(!empty($length[1]) && strlen($value) > $length[1])
				{
					$inrange	=	false;
				}
			}
			elseif($length > 0)
			{
				if(strlen($value) > $length)
				{
					$inrange	=	false;
				}
			}
			
			if($required)
			{
				if($value != '' && $inrange)
				{
					return true;
				}
			}
			else
			{
				if($value == '' || $inrange)
				{
					return true;
				}
			}
			
			return false;
		}
		
		/**
		 * Check $value to see if it's a float.
		 * 
		 * @param string $value		Value to check
		 * @param bool $required	(optional) Whether or not value can be ommitted
		 * @return bool				If it's a float, true. If it's empty and not required, true.
		 */
		function check_float($value, $required = false)
		{
			if(!$required && $value === '')
			{
				return true;
			}

			if(preg_match('/^([0-9]+)?(\.[0-9]+)?$/', $value))
			{
				return true;
			}
			return false;
		}
		
		/**
		 * Check $value to see if it's a boolean.
		 * 
		 * @param string $value		Value to check
		 * @param bool $required	(optional) Whether or not value can be ommitted
		 * @return bool				If it's a boolean, true. If it's empty and not required, true.
		 */
		function check_bool($value, $required = false)
		{
			if(!$required && $value === '')
			{
				return true;
			}

			if($value === true || $value === false || $value === 1 || $value === 0)
			{
				return true;
			}
			return false;
		}
		
		/**
		 * Check $value to see if it's a date, in format YYYY/MM/DD or MM/DD/YYYY. Also runs strtotime on
		 * the value and if it returns a value, then we KNOW it's a date.
		 * 
		 * @param string $value		Value to check
		 * @param bool $required	(optional) Whether or not value can be ommitted
		 * @return bool				If it's a date, true. If it's empty and not required, true.
		 */
		function check_date($value, $required = false, $length = '')
		{
			if(!$required && $value == '')
			{
				return true;
			}
			
			if(
				(preg_match('/[0-9]{2,4}(\/|\-)[0-9]{1,2}(\/|\-)[0-9]{1,2}/', $value) ||
				preg_match('/[0-9]{1,2}(\/|\-)[0-9]{1,2}(\/|\-)[0-9]{2,4}/', $value)) &&
				$t = strtotime($value)
			)
			{
				if($length == '+' && $t < strtotime(date('Y-m-d 0:0:0')))
				{
					return false;
				}
				return true;
			}
			return false;
		}
	}
?>