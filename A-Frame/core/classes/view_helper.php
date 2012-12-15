<?
	/**
	 * This file holds the view helper, which has view-helping functions. Form field generation, 
	 * pagination functions, etc.
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
	 * Does a bunch of form and pagination busywork. Tries to take care of a lot of things in the
	 * view that are repetitious and annoying.
	 * 
	 * @package		aframe
	 * @subpackage	aframe.core
	 * @author		Andrew Lyon
	 */
	class view_helper
	{
		/**
		 * Whether or not to run htmlentities on form field value="" attributes when building forms
		 * @var bool
		 */
		public $escape_fields	=	true;
		
		/**
		 * Whether or not to enforce XHTML transitional item attributes (namely, converting brackets
		 * to _ (and stripping off any trailing underscores): 
		 * 
		 * A text box of name data[test] would normally have id="data[test]", but if this is set to
		 * true would be id="data_test" 
		 */
		public $strict_validation	=	false;
		
		/**
		 * Creates an <input type="text".../> field
		 * 
		 * @param string $name			name of field
		 * @param string $value			the field's value
		 * @param string $label			the label applied to the field
		 * @param bool $read_only		whether or not field is read only
		 * @param string $note			note attached to label
		 * @param integer $size			size of field
		 * @param integer $max_length	maximum field length
		 * @param array $params			extra parameters to attach to field
		 * @return string				string containing field (to be printed)
		 */
		function text($name, $value = '', $label = '', $params = array(), $note = '', $size = 0, $max_length = 0, $params_dep = array())
		{
			// AL - this is a bit of a hack, but I can no longer stand idly by while the atrocity
			// of having to type 600 arguments to get to $params continues. From this day forth,
			// if $read_only is an array, it will be used for $params. This is much more useful
			// because $params can replace any one of the attributes all these stupid arguments
			// create. 
			if(is_bool($params))
			{
				$read_only	=	$params;
				$params		=	$params_dep;
			}
			else
			{
				$read_only	=	false;

				// since passing in params for $read_only is the new standard, let the params 
				// override the options that come after.
				if(isset($params['size']))
				{
					$rows	=	$params['size'];
					unset($params['size']);
				}

				if(isset($params['maxlength']))
				{
					$max_length	=	$params['maxlength'];
					unset($params['maxlength']);
				}
			}

			$label		=	view_helper::label($label, $name, $note);
			$disabled	=	($read_only ? 'disabled="true"' : '');
			$id			=	$this->strict_validation ? preg_replace('/\_$/', '', preg_replace('/[\[\]]+/', '_', $name)) : $name;
			$input		=	'
				<input
					type="text"
					name="'. $name .'"
					id="'. $id .'"
					value="'.view_helper::_escape($value).'"
					size="'.($size > 0 ? $size : '').'"
					'. ($maxlength > 0 ? 'maxlength="'. $max_length .'"' : '') .'
					'. $disabled .'
			';
			for($i = 0, $k = array_keys($params), $n = count($params); $i < $n; $i++)
			{
				$input	.=	$k[$i].'="'.$params[$k[$i]].'" ';
			}
			$input	.=	'/>';
			$str	=	view_helper::_template($label, $input);
			return $str;
		}
		
		/**
		 * Creates an <textarea> field
		 * 
		 * @param string $name			name of field
		 * @param string $value			the field's value
		 * @param string $label			the label applied to the field
		 * @param bool $read_only		whether or not field is read only
		 * @param string $note			note attached to label
		 * @param integer $rows			how many rows
		 * @param integer $cols			how many cols
		 * @param array $params			extra parameters to attach to field
		 * @return string				string containing field (to be printed)
		 */
		function textarea($name, $value = '', $label = '', $params = array(), $note = '', $rows = 5, $cols = 30, $params_dep = array())
		{
			// AL - this is a bit of a hack, but I can no longer stand idly by while the atrocity
			// of having to type 600 arguments to get to $params continues. From this day forth,
			// if $read_only is an array, it will be used for $params. This is much more useful
			// because $params can replace any one of the attributes all these stupid arguments
			// create. 
			if(is_bool($params))
			{
				$read_only	=	$params;
				$params		=	$params_dep;
			}
			else
			{
				$read_only	=	false;

				// since passing in params for $read_only is the new standard, let the params 
				// override the options that come after.
				if(isset($params['rows']))
				{
					$rows	=	$params['rows'];
					unset($params['rows']);
				}

				if(isset($params['cols']))
				{
					$cols	=	$params['cols'];
					unset($params['cols']);
				}
			}

			$label		=	view_helper::label($label, $name, $note);
			$disabled	=	($read_only ? 'disabled="true"' : '');
			$id			=	$this->strict_validation ? preg_replace('/\_$/', '', preg_replace('/[\[\]]+/', '_', $name)) : $name;
			$input		=	'
				<textarea
					name="'. $name .'"
					id="'. $id .'"
					rows="'. $rows .'"
					cols="'. $cols .'"
					'. $disabled;
			for($i = 0, $k = array_keys($params), $n = count($params); $i < $n; $i++)
			{
				$input	.=	$k[$i].'="'.$params[$k[$i]].'" ';
			}
			
			$input .=	'>'.$value.'</textarea>';
			$str	=	view_helper::_template($label, $input);
			return $str;
		}
		
		/**
		 * Creates an <input type="file".../> field for file uploads
		 * 
		 * @param string $name			name of field
		 * @param string $value			the field's value
		 * @param string $label			the label applied to the field
		 * @param bool $read_only		whether or not field is read only
		 * @param string $note			note attached to label
		 * @param integer $size			size of field
		 * @param integer $max_length	maximum field length
		 * @param array $params			extra parameters to attach to field
		 * @return string				string containing field (to be printed)
		 */
		function file($name, $value, $label, $params = array(), $note = '', $size = 0, $max_length = 1000, $params_dep = array())
		{
			// AL - this is a bit of a hack, but I can no longer stand idly by while the atrocity
			// of having to type 600 arguments to get to $params continues. From this day forth,
			// if $read_only is an array, it will be used for $params. This is much more useful
			// because $params can replace any one of the attributes all these stupid arguments
			// create. 
			if(is_bool($params))
			{
				$read_only	=	$params;
				$params		=	$params_dep;
			}
			else
			{
				$read_only	=	false;

				// since passing in params for $read_only is the new standard, let the params 
				// override the options that come after.
				if(isset($params['size']))
				{
					$rows	=	$params['size'];
					unset($params['size']);
				}
			}

			$label	=	view_helper::label($label, $name, $note);
			$disabled	=	($read_only ? 'disabled="true"' : '');
			$id			=	$this->strict_validation ? preg_replace('/\_$/', '', preg_replace('/[\[\]]+/', '_', $name)) : $name;
			$input	=	'
				<input
					type="file"
					name="'. $name .'"
					id="'. $id .'"
					value="'.view_helper::_escape($value).'"
					size="'.($size > 0 ? $size : '').'"
					'. $disabled .'
			';

			for($i = 0, $k = array_keys($params), $n = count($params); $i < $n; $i++)
			{
				$input	.=	$k[$i].'="'.$params[$k[$i]].'" ';
			}
			$input	.=	'/>';
			$str	=	view_helper::_template($label, $input);
			return $str;
		}
		
		/**
		 * Creates an <select> field
		 * 
		 * @param string $name			name of field
		 * @param array $data			array of data to use as <options>
		 * @param string $value			the field's value
		 * @param string $label			the label applied to the field
		 * @param bool $read_only		whether or not field is read only
		 * @param string $onchange		onchange value for applying JS, should actually be in params
		 * @param string $note			note attached to label
		 * @return string				string containing field (to be printed)
		 */
		function select($name, $data, $value, $label, $params = array(), $onchange, $note = '')
		{
			if(is_bool($params))
			{
				$read_only	=	$params;
				$params		=	array();
			}
			else
			{
				$read_only	=	false;

				// since passing in params for $read_only is the new standard, let the params 
				// override the options that come after.
				if(isset($params['onchange']))
				{
					$onchange	=	$params['onchange'];
					unset($params['onchange']);
				}
			}

			$label	=	view_helper::label($label, $name, $note);
			$id		=	$this->strict_validation ? preg_replace('/\_$/', '', preg_replace('/[\[\]]+/', '_', $name)) : $name;
			if(!isset($data[0]['id']))
			{
				$tmp	=	array();
				$keys	=	array_keys($data);
				for($i = 0, $n = count($data); $i < $n; $i++)
				{
					$tmp[$i]['id']		=	$keys[$i];
					$tmp[$i]['display_name']	=	$data[$keys[$i]];
				}
				$data	=	$tmp;
			}
	
			
			$len	=	count($data);
			$kvalue	=	'id';
			$kdata	=	isset($data[0]['display_name']) ? 'display_name' : 'name';
			if(!$read_only)
			{
				$input	.=	'<select id="'.$id.'" name="'.$name.'"';
				if($onchange != '')
				{
					$input	.= ' onchange="'.$onchange.'" ';
				}
				for($i = 0, $k = array_keys($params), $n = count($params); $i < $n; $i++)
				{
					$input	.=	$k[$i].'="'.$params[$k[$i]].'" ';
				}
				$input	.= '>';
				$input	.= '<option value=""> -select- </option>';
				
				for($i = 0; $i < $len; $i++)
				{
					if($value == $data[$i][$kvalue])
					{
						$s = ' selected="selected" ';
					}
					else
					{
						$s = '';
					}
					
					$input	.= '<option value="'.$data[$i][$kvalue].'" '.$s.'>' . $data[$i][$kdata] . '</option>';
				}
				$input	.= '</select>';
			
			}
			else
			{
				for($i = 0; $i < $len; $i++)
				{
					if($value == $data[$i][$kvalue])
					{
						$input	.= $data[$i][$kdata];
						break;
					}
				}
				$input	.=	'<input type="hidden" name="'. $name .'" value="'.$data[$i][$kvalue].'" />';
			}
			
			$str	=	view_helper::_template($label, $input);
			return $str;
		}
		
		/**
		 * Creates an <input type="radio".../> field
		 * 
		 * @param string $name			name of field
		 * @param string $id			Specify an id="$id" for this field
		 * @param string $radio_value	the field's value
		 * @param string $item_value	the actual item value (for checking whether checked or not)
		 * @param string $label			the label applied to the field
		 * @param bool $read_only		whether or not field is read only
		 * @param string $note			note attached to label
		 * @return string				string containing field (to be printed)
		 */
		function radio($name, $id, $radio_value, $item_value, $label, $params = array(), $note = '')
		{
			if(is_bool($params))
			{
				$read_only	=	$params;
				$params		=	array();
			}
			else
			{
				$read_only	=	false;
			}

			$id			=	$this->strict_validation ? preg_replace('/\_$/', '', preg_replace('/[\[\]]+/', '_', $id)) : $id;
			$label		=	view_helper::label($label, $id, $note);
			$checked	=	'';
			
			if($item_value == $radio_value)
			{
				$checked	=	'checked="checked"';
			}
			$disabled	=	($read_only ? 'disabled="true"' : '');

			$input	=	'
				<input
					type="radio"
					name="'. $name .'"
					id="'. $id .'"
					value="'.view_helper::_escape($radio_value).'"
					'. $checked .'
					'. $disabled .'
			';
			for($i = 0, $k = array_keys($params), $n = count($params); $i < $n; $i++)
			{
				$input	.=	$k[$i].'="'.$params[$k[$i]].'" ';
			}
			$input	.= '/>';
			$str	=	view_helper::_template($label, $input);
			return $str;
		}
		
		/**
		 * Creates an <input type="checkbox".../> field
		 * 
		 * @param string $name			name of field
		 * @param string $check_value	the field's value
		 * @param string $item_value	the item's value, for checking whether checked or not
		 * @param string $label			the label applied to the field
		 * @param bool $read_only		whether or not field is read only
		 * @param string $note			note attached to label
		 * @return string				string containing field (to be printed)
		 */
		function checkbox($name, $check_value, $item_value, $label, $params = array(), $note = '')
		{
			if(is_bool($params))
			{
				$read_only	=	$params;
				$params		=	array();
			}
			else
			{
				$read_only	=	false;
			}

			$label		=	view_helper::label($label, $name, $note);
			$checked	=	'';
			$id			=	$this->strict_validation ? preg_replace('/\_$/', '', preg_replace('/[\[\]]+/', '_', $name)) : $name;
			
			if($item_value == $check_value)
			{
				$checked	=	'checked="checked"';
			}
			$disabled	=	($read_only ? 'disabled="true"' : '');
			
			$input	=	'
				<input
					type="checkbox"
					name="'. $name .'"
					id="'. $id .'"
					value="'.view_helper::_escape($check_value).'"
					'. $checked .'
					'. $disabled .'
			';
			for($i = 0, $k = array_keys($params), $n = count($params); $i < $n; $i++)
			{
				$input	.=	$k[$i].'="'.$params[$k[$i]].'" ';
			}
			$input	.= '/>';
			$str	=	view_helper::_template($label, $input);
			return $str;
		}
		
		/**
		 * Creates an <input type="password".../> field
		 * 
		 * @param string $name			name of field
		 * @param string $value			the field's value
		 * @param string $label			the label applied to the field
		 * @param bool $read_only		whether or not field is read only
		 * @param string $note			note attached to label
		 * @param integer $size			size of field
		 * @param integer $max_length	maximum field length
		 * @return string				string containing field (to be printed)
		 */
		function password($name, $value = '', $label = '', $params = array(), $note = '', $size = 0, $max_length = 1000)
		{
			// AL - this is a bit of a hack, but I can no longer stand idly by while the atrocity
			// of having to type 600 arguments to get to $params continues. From this day forth,
			// if $read_only is an array, it will be used for $params. This is much more useful
			// because $params can replace any one of the attributes all these stupid arguments
			// create. 
			if(is_bool($params))
			{
				$read_only	=	$params;
				$params		=	array();
			}
			else
			{
				$read_only	=	false;

				// since passing in params for $read_only is the new standard, let the params 
				// override the options that come after.
				if(isset($params['size']))
				{
					$rows	=	$params['size'];
					unset($params['size']);
				}

				if(isset($params['maxlength']))
				{
					$max_length	=	$params['maxlength'];
					unset($params['maxlength']);
				}
			}

			$label		=	view_helper::label($label, $name, $note);
			$disabled	=	($read_only ? 'disabled="true"' : '');
			$id			=	$this->strict_validation ? preg_replace('/\_$/', '', preg_replace('/[\[\]]+/', '_', $name)) : $name;
			$input		=	'
				<input
					type="password"
					name="'. $name .'"
					id="'. $id .'"
					value="'.view_helper::_escape($value).'"
					size="'.($size > 0 ? $size : '').'"
					maxlength="'.($max_length > 0 ? $max_length : '').'"
					'. $disabled .'
			';
			for($i = 0, $k = array_keys($params), $n = count($params); $i < $n; $i++)
			{
				$input	.=	$k[$i].'="'.$params[$k[$i]].'" ';
			}
			$input	.= '/>';
			$str	=	view_helper::_template($label, $input);
			return $str;
		}
		
		/**
		 * Returns three successive select boxes, with year, month, day
		 * 
		 * @param string $name			name of fields...will be name="$name[(y|m|d)]"
		 * @param string $value			the field's value...any value strtotime can handle
		 * @param string $label			the label applied to the field
		 * @param bool $read_only		whether or not field is read only
		 * @param string $note			note attached to label
		 * @return string				string containing field (to be printed)
		 */
		function dob($name, $value = '', $label = '', $read_only = false, $note = '')
		{
			// see if our date passed in through $value is even valid
			$date		=	strtotime(str_replace('-', '/', $value));
			if(!$date)
			{
				$value	=	'';
			}
			else
			{
				// it's valid, get our formatted date
				$value	=	date('m/d/Y', $date);
			}
			$label		=	view_helper::label($label, $name, $note);
			$disabled	=	($read_only ? 'disabled="true"' : '');
			
			// if we have a date, split its parts into an array
			$date	=	array();
			if(!empty($value))
			{
				preg_match_all('/([0-9]+)[\/]?/', $value, $date);
				$date	=	$date[1];
			}
			
			// build the months
			$id		=	$this->strict_validation ? preg_replace('/\_$/', '', preg_replace('/[\[\]]+/', '_', $name . '[m]')) : $name;
			$input	=	'<select name="'. $name. '[m]" id="'. $id .'">';
			$time	=	strtotime('2008/01/15');	// our start date
			for($i = 1; $i <= 12; $i++)
			{
				$input	.=	'<option value="'.$i.'"';
				$input	.=	(isset($date[0]) && $date[0] == $i) ? ' selected="selected"' : '';
				$input	.=	'>'.date('M', $time).'</option>';
				
				$time	+=	2592000;	// add a month on (30 days of seconds)
			}
			$input	.=	'</select>';
			
			// build the days
			$id		=	$this->strict_validation ? preg_replace('/\_$/', '', preg_replace('/[\[\]]+/', '_', $name . '[d]')) : $name;
			$input	.=	'<select name="'. $name .'[d]" id="'. $id .'">';
			for($i = 1; $i <= 31; $i++)
			{
				$input	.=	'<option value="'.$i.'"';
				$input	.=	(isset($date[1]) && $date[1] == $i) ? ' selected="selected"' : '';
				$input	.=	'>'.$i.'</option>';
			}
			$input	.=	'</select>';

			// build the years
			$id		=	$this->strict_validation ? preg_replace('/\_$/', '', preg_replace('/[\[\]]+/', '_', $name . '[y]')) : $name;
			$input	.=	'<select name="'. $name .'[y]" id="'. $id .'">';
			$y		=	(int)date('Y');
			for($i = $y; $i >= ($y - 120); $i--)
			{
				$input	.=	'<option value="'.$i.'"';
				$input	.=	(isset($date[2]) && $date[2] == $i) ? ' selected="selected"' : '';
				$input	.=	'>'.$i.'</option>';
			}
			$input	.=	'</select>';

			$str	=	view_helper::_template($label, $input, 'dob');
			return $str;
		}
		
		/**
		 * Creates an <input type="image".../> field
		 * 
		 * @param string $src			the image source
		 * @param string $value			the field's value
		 * @param string $label			the label applied to the field
		 * @param bool $read_only		whether or not field is read only
		 * @param string $note			note attached to label
		 * @param integer $size			size of field
		 * @param integer $max_length	maximum field length
		 * @param array $params			extra parameters to attach to field
		 * @return string				string containing field (to be printed)
		 */
		function image($src, $value = '', $params = '')
		{
			$input	=	'
				<input
					type="image"
					src="'. $src .'"
					value="'.view_helper::_escape($value).'"
					'. (empty($params) ? '' : $params) .'
				/>
			';
			return $input;
		}
		
		/**
		 * Does pagination. The stupid kind. We wanted to get rid of this, but plenty of our older 
		 * 
		 * @param string $url				The URL the pagination points to
		 * @param integer $page_number		What page we're currently on
		 * @param integer $items_per_page	How many items we're displaying per page
		 * @param integer $total_items		The total amount of items
		 * @param integer $max_pages		How many page numbers to display 1..10, 1..20, etc
		 * @param bool $prevnext			Show prev/next links
		 * @param bool $showjumps			Whether or not to show the < and > arrows (jump 10 back/forward pages)
		 * @return string					Contains our pagination
		 */
		function paginate($page_num, $url_string, $num_pages, $record_count, $items_pp, $show_prevnext = true, $page_var, $i_am_a_sex_machine = true)
		{
			$output = '';
			
			if ($page_num == 1 && $show_prevnext)
			{
				$output .= '&laquo;&nbsp;prev';
			}
			elseif ($page_num != 1 && $show_prevnext)
			{
				$output .= '&laquo;&nbsp;<a href="'.$url_string.$page_var.'='.($page_num-1).'">prev</a>';
			}
			for ($i=1; $i<=$num_pages; $i++)
			{
				if ($i == $page_num)
				{
					$output .= ' '.$i;
				}
				else
				{
					$output .= ' <a href="'.$url_string.$page_var.'='.$i.'">'.$i.'</a>';
				}
			}
			if ($page_num == $num_pages && $show_prevnext)
			{
				$output .= ' next&nbsp;&raquo;';
			}
			elseif ($page_num != $num_pages && $show_prevnext)
			{
				$output .= ' <a href="'.$url_string.$page_var.'='.($page_num+1).'">next</a>&nbsp;&raquo;';
			}
			return $output; 
		}
		
		/**
		 * Does pagination geared towards ajax. Returns a string containing the links, which can be CSS'ed. Only supports one 
		 * pagination format. This function is prefered over the above as it replaces [page] with the page number in the URL:
		 * 
		 * $url	=	/products/list/[page]
		 * /products/list/1 <- page 1
		 * /products/list/2 <- page 2
		 * etc...
		 * 
		 * Counterintuitively, does not support Javascript... developer must attach JS events to links, which is prefered over
		 * haveing 10 million onlick="" statements, generally. 
		 * 
		 * This function is clean, simple, and superior. In fact, view_helper::paginate() may be destroyed soon...
		 * 
		 * @param string $url				The URL the pagination points to
		 * @param integer $page_number		What page we're currently on
		 * @param integer $items_per_page	How many items we're displaying per page
		 * @param integer $total_items		The total amount of items
		 * @param integer $max_pages		How many page numbers to display 1..10, 1..20, etc
		 * @param bool $prevnext			Show prev/next links
		 * @param bool $showjumps			Whether or not to show the < and > arrows (jump 10 back/forward pages)
		 * @param bool $no_page_1			If true, pages will not include the page number if it is the first page (reduces
		 * 									duplicate content)
		 * @return string					Contains our pagination
		 */
		function paginate_ajax($url, $page_number, $items_per_page, $total_items, $max_pages, $prevnext = true, $showjumps = true, $no_page_1 = false)
		{
			if(empty($url))
			{
				return;
			}
			
			$pagestring	=	'';
			$separator	=	'';
			if( $page_number == '')
			{
				$page_number	=	1;
			}
			$pages	=	ceil($total_items / $items_per_page);
			
			if($total_items <= $items_per_page)
			{
				return;
			}
			if( $pages < 2 )
			{
				return;
			}
			
			if($prevnext)
			{
				if($page_number == 1)
				{
					$pagestring	.=	'<span class="sel">prev</span>' . $separator;
				}
				else
				{
					if(($page_number - 1) == 1)
					{
						if($no_page_1)
						{
							$pagestring	.=	'<span><a href="'. preg_replace('/(\/|\?|\&)?\[page\]/', '', $url) .'">prev</a></span>' . $separator;
						}
						else
						{
							$pagestring	.=	'<span><a href="'. str_replace('[page]', 1, $url) .'">prev</a></span>' . $separator;
						}
					}
					else
					{
						$pagestring	.=	'<span><a href="'. str_replace('[page]', ($page_number - 1), $url) .'">prev</a></span>' . $separator;
					}
				}
			}
	
			if( $pages <= $max_pages )
			{
				$i	=	1;
				$i_end	=	$i + $max_pages;
			}
			else
			{
				$i	=	$page_number - (int)($max_pages / 2);
				if($i < 1)
				{
					$diff	=	0 - $i;
					$i	=	1;
				}
				$i_end	=	$i + $max_pages - 1;
				if($i_end > $pages)
				{
					$i	=	$i - ($i_end - $pages);
					$i_end	=	$pages;
				}
				if($i > 1)
				{
					$n	=	$page_number - $max_pages;
					if( $n < 1 )
					{
						$n	=	1;
					}
					if($showjumps)
					{
						if($no_page_1)
						{
							$pagestring	.=	'<span><a href="'. preg_replace('/(\/|\?|\&)?\[page\]/', '', $url) .'">1</a></span>'.$separator;
						}
						else
						{
							$pagestring	.=	'<span><a href="'. str_replace('[page]', '1', $url) .'">1</a></span>'.$separator;
						}
						
						if($no_page_1 && $n == 1)
						{
							$pagestring	.=	'<span><a href="'. preg_replace('/(\/|\?|\&)?\[page\]/', '', $url) .'">&lt;&lt;</a></span>'. $separator;
						}
						else
						{
							$pagestring	.=	'<span><a href="'. str_replace('[page]', $n, $url) .'">&lt;&lt;</a></span>'. $separator;
						}
					}
				}
			}
			$first	=	true;	
			while( ($i <= $i_end) && ($i <= $pages) )
			{
				if($first)
				{
					$first		=	false;
				}
				else
				{
					$pagestring	.= $separator;
				}
				
				if($i != $page_number) 
				{
					if($i != 1)
					{
						$pagestring	.=	'<span><a href="'. str_replace('[page]', $i, $url) .'">'.$i.'</a></span>';
					}
					else
					{
						if($no_page_1)
						{
							$pagestring	.=	'<span><a href="'. preg_replace('/(\/|\?|\&)?\[page\]/', '', $url) .'">'.$i.'</a></span>';
						}
						else
						{
							$pagestring	.=	'<span><a href="'. str_replace('[page]', '1', $url) .'">'.$i.'</a></span>';
						}
					}
				}
				else
				{
					$pagestring	.=	'<span class="sel">'.$i.'</span>';
				}
				
				$i++;
			}
			if(($i - 1) < $pages)
			{
				$n	=	$page_number + $max_pages;
				if( $n > $pages )
				{
					$n	=	$pages;
				}

				if($showjumps)
				{
					$pagestring	.=	$separator . '<span><a href="'. str_replace('[page]', $n, $url). '">&gt;&gt;</a></span>';
					$pagestring	.=	$separator . '<span><a href="'. str_replace('[page]', $pages, $url). '">'.$pages.'</a></span>';
				}
			}
	
			if($prevnext)
			{
				if($page_number == $pages)
				{
					$pagestring	.=	$separator . '<span class="sel">next</span>';
				}
				else
				{
					$pagestring	.=	$separator . '<span><a href="'. str_replace('[page]', ($page_number + 1), $url) .'">next</a></span>';
				}
			}
	
			return $pagestring;
		}

		/**
		 * Gets the current URL. Used by view_helper::paginate(), mainly, but can also be used as a standalone function to get
		 * the URL, with get vars.
		 * 
		 * Has many helpful options.
		 * 
		 * @param array $exceptions		The GET vars we DO NOT want to be included in the URL. Useful for automated pagination
		 * 								so you don't get url?page=1&page=2 ...'page' would be an exclusion and can thus be added
		 * 								on by the pagination dynamically
		 * @param bool $return_filename	Whether or not to return the filename being called in the URL string
		 * @param bool $return_cap		Whether or not to return ? or & at the end of the URL string
		 * @param bool $framework_url	Whether or not to be smart about how we pull the filename (take URL rewriting into account)
		 * @return string				string containing URL
		 */
		function get_url($exceptions = array(), $return_filename = false, $return_cap = false, $framework_url = true)
		{
			$url	=	$_SERVER['QUERY_STRING'];
			if( is_array($exceptions) )
			{
				for($i = 0, $n = count($exceptions); $i < $n; $i++)
				{
					$regex	=	'/&?'.$exceptions[$i].'(=.*?(&|$))?/i';
					$rep	=	'$2';
					$url	=	preg_replace($regex, $rep, $url);
				}
			}
			
			$url	=	preg_replace('/&$/', '', preg_replace('/^&/', '', $url));
	
			if($return_filename)
			{
				if(!$framework_url)
				{
					$url	=	preg_replace('/.*\//i', '', $_SERVER['PHP_SELF']) . '?' . $url;
				}
				else
				{
					$url	=	view_helper::_get_self() . '?' . $url;
				}			
			}
		
			if($return_cap)
			{
				if(preg_match('/\?.+$/', $url))
				{
					$url	=	$url . '&';
				}
				elseif(preg_match('/\?/', $url))
				{
					$url	=	$url;
				}
				else
				{
					$url	=	$url . '?';
				}
			}
			else
			{
				$url	=	preg_replace('/(\?|&)$/', '', $url);
			}
		
			return $url;
		}
		
		/**
		 * Used mainly by pagination/get_url functions. Abstracts out getting 'self' in different server environments.
		 * 
		 * @return string	A string containing 'self'...a concept that can lead to realization of one's core being...	
		 * 					a realization that in the end, makes you come to terms with the fact that 'self' is an 
		 * 					illusion manifested by our ego in attempt to self-preserve. Either that, or the name of the
		 * 					current script's url...
		 */
		function _get_self()
		{	
			if((isset($_SERVER['PATH_INFO']) && !empty($_SERVER['PATH_INFO']))&& ereg($_SERVER['PATH_INFO'], $_SERVER['PHP_SELF']))
			{
				$self	=	$_SERVER['PATH_INFO'];
			}
			elseif(isset($_SERVER['PATH_INFO']) && !empty($_SERVER['PATH_INFO']))
			{
				$self	=	$_SERVER['PHP_SELF'] . $_SERVER['PATH_INFO'];
			}
			else
			{
				$self	=	$_SERVER['PHP_SELF'];
			}
			return $self;
		}

		/**
		 * Build a label string, mainly an internal templating function
		 * 
		 * @param string $label		name of label
		 * @param string $for		id of input field we're
		 * @param strin $note		note to attach to label 
		 * @return string			label string
		 */
		function label($label, $for, $note = '', $strict_validation = false)
		{
			$strict_validation	=	isset($this) ? $this->strict_validation : $strict_validation;
			$id		=	$strict_validation ? preg_replace('/\_$/', '', preg_replace('/[\[\]]+/', '_', $for)) : $for;
			$str	=	'<label for="'.$id.'">'. $label;
			if($note != '')
			{
				$str	.=	'<span class="note">'. $note .'</span>';
			}
			$str	.=	'</label>';
			return $str;
		}
		
		/**
		 * Function used to template other form functions. Makes it easy to switch around form layouts later on, very
		 * very quickly and only have to change one thing. Genious, really. Wow, so smart! OMGOSH...
		 * 
		 * @param string $label		Label string of form field
		 * @param string $input		Actual input string
		 * @param string $class		Extra classname to apply to form_row for styling
		 * @return string			Templated version of form fields
		 */
		function _template($label, $input, $class = '')
		{
			// insanely complicated template
			$str	=	'
				<div class="form_row'. (!empty($class) ? ' '. $class : '') .'">
					<span class="label">[[label]]</span>
					<span class="input">[[input]]</span>
				</div>
			';
			
			// insanely complicated search and replaces (dont even TRY to reverse engineer!!)
			$str	=	str_replace('[[label]]', $label, $str);
			$str	=	str_replace('[[input]]', $input, $str);
			return $str;
		}
		
		/**
		 * Escapes form values. Used for value="[val]", where you dont want "'s showing up unexpectedly and destroying
		 * good markup.
		 * 
		 * @param string $str	String to be escaped.
		 * @return string		Escaped $str...if escaping is on. Otherwise, just $str.
		 */
		function _escape($str)
		{
			if($this->escape_fields)
			{
				$str	=	htmlspecialchars($str);
			}
			return $str;
		}
	}
?>
