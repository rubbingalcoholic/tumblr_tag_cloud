<?
	/**
	 * This file holds the base db, which is an interface for abstract communication with
	 * different kinds of databases. This particular file communicates with SQL servers.
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
	 * Basic communication layer to an SQL server. Mimics a lot of the CakePHP base model DB layer. 
	 * 
	 * @package		aframe
	 * @subpackage	aframe.core
	 * @author		Andrew Lyon
	 */
	class base_db extends base
	{
		/**
		 * What table we're operating on
		 * @var string
		 */
		var $table	=	'';

		/**
		 * our database object
		 * @var object
		 */
		var $db;
		
		/**
		 * The notation to define a field in the DB usually ` or '
		 * @var string
		 */
		var $field_note;
		
		/**
		 * The function run to get the current datetime on our DB
		 * @var string
		 */
		var $now_func;
		
		/**
		 * Suppress automatic create_date / mod_date population on insert / update
		 * @var bool
		 */
		var $suppress_auto_timestamp	=	false;

		var $created_field	=	'create_date';
		var $updated_field	=	'mod_date';
		
		/**
		 * The name of the current tables primary key field. Defaults to 'id'. Used by base_model::save()
		 * @var string
		 */
		var $id_key	=	'id';
		

		/**
		 * Init function, calls base::_init() and loads the DB class.
		 * 
		 * @param object &$event	Event object
		 */
		function _init(&$event)
		{
			parent::_init($event);
			
			$this->db	=	&$event->object('db_sql');
			if($this->db->mode == AFRAME_DB_MODE_MYSQL)
			{
				$this->field_note	=	"`";
				$this->now_func		=	"NOW()";
				$this->last_id		=	"last_insert_id()";
			}
			else if($this->db->mode == AFRAME_DB_MODE_MYSQLI)
			{
				$this->field_note	=	"`";
				$this->now_func		=	"NOW()";
				$this->last_id		=	"last_insert_id()";
			}
			else if($this->db->mode == AFRAME_DB_MODE_MSSQL)
			{
				$this->field_note	=	"";
				$this->now_func		=	"GetDate()";
				$this->last_id		=	"@@IDENTITY";
			}
		}
		
		/**
		 * Doesn't do much right now...takes a table name and applies the DB prefix to it.
		 * 
		 * @param string $tbl_name	'Key' of table to return.
		 * @return string			Table name
		 */
		function tbl($tbl_name)
		{
			$prefix	=	isset($this->config['db']['prefix']) ? $this->config['db']['prefix'] : '';
			$name	=	$prefix . $tbl_name;
			return $name;
		}

		/**
		 * Super smart function. Give it your data, it updates/inserts it and gives you an id.
		 * If $data[base_model::$id_key] exists, it does an update on id = !, otherwise does an insert. Both
		 * operate on $this->table, much like CakePHP's base model.
		 * 
		 * @param array $data				data to insert / update
		 * @param array $functions			(optional) array containing SQL functions to be applied to data param
		 * @param array $duplicate_check	(optional) array of fields to use for duplicate checking
		 * @param bool	$runUpdate			(optional) if duplicate is found, update it with $data?
		 * @return integer					id of updated / inserted item
		 */
		function save($data, $functions = array(), $duplicate_check = array(), $update = false, $dup_false = false)
		{
			if(!isset($data[$this->id_key]) && !empty($duplicate_check))
			{
				// If the data was a duplicate, return the id to the original
				if($id = $this->duplicate_check($data, $duplicate_check))
				{
					if(!$update && !$dup_false)
					{
						return $id;
					}
					elseif($dup_false)
					{
						return false;
					}
					else
					{
						$data[$this->id_key]	=	$id;
					}
				}
			}
			
			// check whether we should insert or update
			if(isset($data[$this->id_key]))
			{
				// we have an 'id' in the data, assume update
				$id			=	$data[$this->id_key];
				unset($data[$this->id_key]);
				$this->run_update($id, $data, $functions);
				return $id;
			}
			else
			{
				// no 'id', probably an insert
				$id	=	$this->run_insert($data, $functions);
				return $id;		
			}
		}
		
		/**
		 * Takes an array of data and uses Key => Value pairing to build an INSERT query. 
		 * 
		 * Takes a condition (usually something like "id = 5"). Also has a functions array
		 * for running functions on certain fields...uses Field => Function pairing. So if 
		 * you have a field 'pass' and you need to run the PASSWORD() function on it, pass 
		 * array('pass' => 'PASSWORD') into the functions array, which will yield a
		 * pass = PASSWORD('value') in the query.
		 * 
		 * @param array $data		data to update DB with
		 * @param string $condition	condition upon which to update
		 * @param array $functions	(optional) functions to be applied to $data
		 * @param integer $limit	(optional) number of records to update (defaults to all)
		 * @access					private
		 */
		function run_update($id, $data, $functions = array())
		{
			$fields	=	"";
			$params	=	array();
			foreach($data as $field => $value)
			{
				$field	=	str_replace('`', '', $field);
				$fields	.=	$this->field_note . $field . $this->field_note . '=';
				
				if(isset($functions[$field]))
				{
					$fields	.=	$functions[$field] . "(";
				}
				
				if(is_numeric($value) && $value{0} != '0' && $value{0} != '+')
				{
					$fields		.=	"!";
					$params[]	=	$value;
				}
				else
				{
					$fields		.=	"?";
					$params[]	=	$value;
				}
				
				if(isset($functions[$field]))
				{
					$fields	.=	")";
				}
				$fields	.=	",";
			}
			
			if($this->suppress_auto_timestamp)
			{
				$fields	=	substr($fields, 0, -1) ." ";
			}
			else
			{
				$fields	.=	$this->field_note . $this->updated_field. $this->field_note ." = ". $this->now_func ." ";
			}

			$qry	=	"
				UPDATE
					". $this->table ."
				SET
					". $fields ."
				WHERE
					". $this->id_key ." = !
				LIMIT 1
			";
			
			$params[]	=	$id;
			$this->db->execute($qry, $params);
		}
		
		/**
		 * Inserts an array of data into $this->table.
		 * 
		 * Uses Key => Value pairing from data array to generate (FIELDS) VALUES () lists. 
		 * 
		 * @param array $data		array of data to insert into database
		 * @param array $functions	(optional) array of functions to apply to $data
		 * @return integer			id of inserted data
		 * @access					private
		 */
		function run_insert($data, $functions = array())
		{
			$params	=	array();
			$fields	=	"";
			$values	=	"";
			foreach($data as $field => $value)
			{
				// build field list
				$fields	.=	$this->field_note . str_replace('`', '', $field) . $this->field_note . ",";
				
				// build value list
				if(isset($functions[$field]))
				{
					$values	.=	$functions[$field] . "(";
				}
				if(is_numeric($value) && $value{0} != '0' && $value{0} != '+')
				{
					$values		.=	"!";
					$params[]	=	$value;
				}
				else
				{
					$values		.=	"?";
					$params[]	=	$value;
				}
				if(isset($functions[$field]))
				{
					$values	.=	")";
				}
				$values	.=	",";
			}
			
			// check our auto timestamp updating
			if($this->suppress_auto_timestamp)
			{
				$fields	=	substr($fields, 0, -1);
				$values	=	substr($values, 0, -1);
			}
			else
			{
				$fields	.=	$this->field_note .$this->created_field. $this->field_note;
				$values	.=	$this->now_func;
			}
			
			// build final query
			$qry	=	"
				INSERT INTO
					". $this->table ."
					(". $fields .")
				VALUES
					(". $values .")
			";
			
			if($this->db->mode == AFRAME_DB_MODE_MYSQLI)
			{
				// we're using MySQLi, which means multi-queries =). let's make this a bit more efficient.
				$this->db->add_query($qry, $params);
				
				$last_id_qry	=	$this->last_id(false);
				$this->db->add_query($last_id_qry);
				
				$results	=	$this->db->run_batch();
				$id			=	(int)$results[0][0]['id'];
			}
			else
			{
				// no MySQLi...just run two queries =(
				$this->db->execute($qry, $params);
				
				// get the inserted object's id
				$id	=	$this->last_id();
			}
			return $id;
		}
		
		/**
		 * Wrapper around base_model::_delete() that deletes an item with id = $id from $this->table.
		 * 
		 * @param integer $id		id of item to delete
		 * @param integer $limit 	(optional) Amount of items to delete (defaults to all)
		 */
		function delete($id, $limit = '')
		{
			if(!is_numeric($id))
			{
				$id	=	"'". $this->db->escape($id) ."'";
			}
			$this->_delete("id = ". $id, $limit);
		}
		
		/**
		 * Get the last insert ID from table in base_model::$table
		 * 
		 * @param bool $run_query		If false, will just return the query to run without running it (will also
		 * 								not select master)
		 * @return mixed				The ID of the last object inserted into base_model::$table; or query to run
		 * 								to get the last ID if $run_query == false
		 */
		function last_id($run_query = true)
		{
			// make SURE we get the ID from the right server
			if($run_query)
			{
				$this->db->use_master();
			}
			
			$qry	=	"
				SELECT
					". $this->last_id ." AS id
				FROM ". $this->table ."
			";
			
			if($this->db->mode == AFRAME_DB_MODE_MYSQLI || $this->db->mode == AFRAME_DB_MODE_MYSQL)
			{
				// add a limit to the query
				$qry	.=	"LIMIT 1";
			}
			
			if($run_query)
			{
				$res	=	(int)$this->db->one($qry);
			}
			else
			{
				$res	=	$qry;
			}
			
			return $res;
		}
		
		/**
		 * Runs a delete on $this->table using given condition
		 * 
		 * @param string $condition	contains the condition upon which to delete
		 * @param integer $limit	(optional) number of total items to delete 
		 * @access					private
		 */
		function _delete($condition, $limit = '')
		{
			$qry	=	"DELETE FROM ". $this->table ." WHERE ". $condition ." " . $limit;
			$this->db->query($qry);
		}
		
		/**
		 * Check an item for duplicates before inserting/updating
		 * 
		 * @param array $item		The data we are checking duplicates on
		 * @param array $fields		Fields (array keys) within $item which we will base the check off of. All fields
		 * 							in $fields must match exactly for a duplicate to be flagged.
		 * @return integer			The idea of the duplicated data in the DB (if found), otherwise false (no match)
		 */
		function duplicate_check($item, $fields)
		{
			$where	=	"1 = 1 ";
			$params	=	array();
			
			for($i = 0, $n = count($fields); $i < $n; $i++)
			{
				if(is_numeric($item[$fields[$i]]))
				{
					$where		.=	"AND ". $fields[$i] ." = ! ";
				}
				else
				{
					$where		.=	"AND ". $fields[$i] ." = ? ";
				}
				$params[]	=	$item[$fields[$i]];
			}
			
			$qry	=	"
				SELECT
					". $this->id_key ."
				FROM
					". $this->table ."
				WHERE
					". $where ."
				LIMIT 1
			";
			$check	=	$this->db->one($qry, $params);
			
			if($check > 0)
			{
				return $check;
			}
			return false;
		}
	}
?>
