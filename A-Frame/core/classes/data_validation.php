<?
	/**
	 * Holds aframe's data validation class, mainly used by models for data validation.
	 * For the most part, replaces the input_validation class.
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
	 * Used by models for validating data structures recursively. Has the ability to remove
	 * unwanted data and also validate passed in data using a colleciton of simple types 
	 * (string, integer, array w/ sub-objects) OR specify callbacks for validation
	 * 
	 * Callbacks MUST return boolean true/false.
	 * 
	 * @package		aframe
	 * @subpackage	aframe.core
	 * @author		Andrew Lyon
	 */
	class data_validation extends base
	{
		public static $fake_types	=	array(
			'number',
			'date',
			'assoc'
		);

		public static $numeric_types	=	array(
			'int',
			'integer',
			'float',
			'double',
			'real'
		);

		public static function validate(&$data, $format, $options, $breadcrumbs = '')
		{
			if(!is_array($data))
			{
				// something is wrong here, fail
				return array(data_validation::error($breadcrumbs, 'bad_type', 'Data must be an associative array or object for validation to continue.'));
			}

			$remove_extra_data	=	isset($options['remove_extra_data']) ? $options['remove_extra_data'] : true;
			$cast_data			=	isset($options['cast_data']) ? $options['cast_data'] : true;
			$edit_mode			=	isset($options['edit_mode']) ? $options['edit_mode'] : false;
			$errors		=	array();
			$renames	=	array();

			if(!$edit_mode)
			{
				// check for values that are required but not present. HERP DERP
				foreach($format as $key => $validate)
				{
					$required	=	isset($validate['required']) ? $validate['required'] : false;
					$message	=	isset($validate['message']) ? $validate['message'] : '';

					// the breadcrumb keeps track of how deep the rabbit hole goes
					$breadcrumb	=	empty($breadcrumbs) ? $key : $breadcrumbs . ':' . $key;

					if($required && $key != '*' && ((is_object($data) && !isset($data->$key)) || (is_array($data) && !isset($data[$key]))))
					{
						$errors[]	=	data_validation::error($breadcrumb, 'missing', $message);
					}
				}
			}

			// loop over the data validation and apply the comparisons/transformations to our data
			foreach($data as $key => &$value)
			{
				if(isset($format[$key]))
				{
					$validate	=	$format[$key];
				}
				else if(isset($format['*']))
				{
					$validate	=	$format['*'];
				}
				else if($remove_extra_data && !in_array($key, $renames))
				{
					if(is_object($data))
					{
						unset($data->$key);
					}
					else
					{
						unset($data[$key]);
					}
					continue;
				}
				else
				{
					// not much to do.
					continue;
				}

				// pull out some default params
				$required			=	isset($validate['required']) ? $validate['required'] : false;
				$type				=	isset($validate['type']) ? $validate['type'] : 'string';
				$message			=	isset($validate['message']) ? $validate['message'] : '';
				$cast				=	isset($validate['cast']) ? $validate['cast'] : true;
				$rename				=	isset($validate['rename']) ? $validate['rename'] : false;

				// the breadcrumb keeps track of how deep the rabbit hole goes
				$breadcrumb	=	empty($breadcrumbs) ? $key : $breadcrumbs . ':' . $key;

				// see if we want to explicitely rename an item.
				if(!empty($rename))
				{
					$data[$rename]	=	$data[$key];
					unset($data[$key]);
					$key			=	$rename;
					$renames[]		=	$key;
				}

				if(is_object($data))
				{
					$value	=	&$data->$key;
				}
				else
				{
					$value	=	&$data[$key];
				}

				// cast our types
				if($cast && $cast_data && !in_array($type, data_validation::$fake_types))
				{
					settype($value, $type);
				}

				// process any transformations (typical things would be strtolower() or uhh hmm, strotoupper()
				if(isset($validate['transform']) && !empty($validate['transform']) && (!is_string($validate['transform']) || function_exists($validate['transform'])))
				{
					$transform	=	$validate['transform'];
					$value		=	call_user_func_array($transform, array($value));
				}

				// validate the type. especially useful if $cast_data is false
				if($type == 'number' || $type == 'assoc' || !in_array($type, data_validation::$fake_types))
				{
					$type_fn	=	'is_' . $type;
					if($type == 'number')
					{
						$type_fn	=	'is_numeric';
					}

					if($type == 'assoc')
					{
						$type_fn	=	'is_array';
					}

					// if they passed "array" as a type, it has to be an ordered collection. assoc arrays don't
					// work. pass type "object" instead
					$array_check	=	$type != 'array' || ($type == 'array' && (empty($value) || isset($value[0])));

					if(!$type_fn($value) || !$array_check)
					{
						$errors[]	=	data_validation::error($breadcrumb, 'not_' . $type, $message);
						continue;
					}
				}

				// save our errors. if type checking increases the number of errors (ie we had failures), then we
				// continue; the main loop directly after (effectively skipping callbacks). normally we could
				// "continue;" within the switch() statement, but it only works for within the switch. lame.
				$errcount	=	count($errors);

				// do advanced type checking beyond just the normal "is_[type]()" functions
				switch($type)
				{
					// purposely ignore bool
					case 'date':
					case 'string':
					case 'int':
					case 'float':
					case 'double':
					case 'real':
					case 'number':
						$fn	=	'validate_' . $type;

						// we really only need one number validation function, so if we get ANY numbers, pass them
						// along to it. this especially true since all of the type checking has already happened
						// above.
						if(in_array($type, data_validation::$numeric_types))
						{
							$fn	=	'validate_number';
						}

						if(($error = data_validation::$fn($value, $validate)) !== true)
						{
							$errstr		=	$type;
							if(is_string($error))
							{
								$errstr	.=	':'.$error;
							}
							$errors[]	=	data_validation::error($breadcrumb, $errstr, $message);
						}
						break;
					case 'object':
					case 'assoc':
					case 'array':
						// we're going to have to check recursively
						if(isset($validate['format']))
						{
							if($type == 'object' || $type == 'assoc')
							{
								// recurse one layer down, save errors into $err
								$err	=	data_validation::validate($value, $validate['format'], $options, $breadcrumb);
							}
							else
							{
								$err	=	array();
								for($i = 0, $n = count($value); $i < $n; $i++)
								{
									$breadcrumb_a	=	$breadcrumb . ':'.$i;
									$error_a		=	data_validation::validate($value[$i], $validate['format'], $options, $breadcrumb_a);
									if(!empty($error_a))
									{
										// only add it to our local errors of we got an error (not just an empty array)
										$err	=	array_merge($err, $error_a);
									}
								}
							}

							if(!empty($err))
							{
								// only merge in our errors if we got any =]
								$errors	=	array_merge($errors, $err);
							}
						}
						else if($type == 'array' && isset($validate['subtype']))
						{
							$subtype	=	$validate['subtype'];
							foreach($value as $sub)
							{
								// cast the members
								if($cast && $cast_data && !in_array($type, data_validation::$fake_types))
								{
									settype($value, $type);
								}

								// TODO: standardize type checking 
								// type check here
							}
						}
						break;
				}

				// do an error check to make sure we got no new errors when type checking. if we got new errors,
				// skip callback processing since it's generally expensive.
				if(count($errors) > $errcount)
				{
					continue;
				}

				// process our callback, if it exists. we do this last because generally a callback is a call
				// to a database or api somewhere. this makes it generally the most expensive, meaning we don't
				// want to take two whole seconds to process the callback just to find out that the string we're
				// using is invalid. better to validate all the easy shit first, THEN run the callback.
				if(isset($validate['callback']) && !empty($validate['callback']))
				{
					$check	=	call_user_func_array(
						$validate['callback'],
						array(
							$data[$key],
							$validate
						)
					);

					// if callback failed, format a string to send back detailing what happened
					if(!$check || is_string($check))
					{
						$callback	=	$validate['callback'];
						if(is_array($callback) && is_object($callback[0]))
						{
							if(is_object($callback[0]))
							{
								$callback[0]	=	get_class($callback[0]);
							}
						}

						// allow the callback to pass a custom message back to the app
						if(is_string($check))
						{
							$message	=	$check;
						}

						$errors[]	=	data_validation::error(
							$breadcrumb,
							'callback_failed:'. $key .'('. preg_replace('/[\r\n\s]+/', ' ', print_r($data[$key], true)) .')',
							$message
						);
					}
				}
			}

			return $errors;
		}

		public static function validate_string($value, $validate)
		{
			// process length meta language
			if(isset($validate['length']) && preg_match('/^([><]=?)?[0-9]+$/', $validate['length']))
			{
				$length		=	$validate['length'];
				$compare	=	$length[0];
				$len		=	strlen($value);
				if(is_numeric($compare))
				{
					$compare	=	(int)$length;
					if($len < $compare)
					{
						return 'length-short';
					}
					else if($len > $compare)
					{
						return 'length-long';
					}
				}
				else
				{
					$equal		=	$length[1];
					if($equal == '=')
					{
						$length		=	(int)substr($length, 2);

						// note the sign reversla in the following ifs...because we're testing for a NOT match
						if($compare == '<' && $len > $length)
						{
							return 'length-long';
						}
						else if($compare == '>' && $len < $length)
						{
							return 'length-short';
						}
					}
					else
					{
						$length		=	(int)substr($length, 1);

						// note the sign reversla in the following ifs...because we're testing for a NOT match
						if($compare == '<' && $len >= $length)
						{
							return 'length-long';
						}
						else if($compare == '>' && $len <= $length)
						{
							return 'length-short';
						}
					}
				}
			}

			// process regex patterns
			if(isset($validate['pattern']) && !empty($validate['pattern']))
			{
				if(!preg_match($validate['pattern'], $value))
				{
					return 'pattern';
				}
			}

			// simple is string empty? validation
			if(isset($validate['required']) && $validate['required'])
			{
				if(empty($value))
				{
					return 'empty';
				}
			}

			// YESSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSSsssSSsssSSSssSSs!!!
			return true;
		}

		public static function validate_number($value, $validate)
		{
			if(isset($validate['min']) && $value < $validate['min'])
			{
				return 'min';
			}

			if(isset($validate['max']) && $value > $validate['max'])
			{
				return 'max';
			}
			return true;
		}

		public static function validate_date($value, $validate)
		{
			if(strtotime($value) === false)
			{
				return false;
			}
			return true;
		}

		public static function error($key, $type, $message = '')
		{
			if(empty($message))
			{
				$message	=	$type;
			}
			return array('key' => $key, 'type' => $type, 'message' => $message);
		}
	}
?>
