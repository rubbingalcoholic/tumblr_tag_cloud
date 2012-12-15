<?
	/**
	 * This file holds the base db, which is an interface for abstract communication with
	 * different kinds of databases. This particular file communicates with MongoDB.
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
	 * Basic communication layer to a MongoDB server.
	 * 
	 * @package		aframe
	 * @subpackage	aframe.core
	 * @author		Andrew Lyon
	 */
	class base_db extends base
	{
		/**
		 * What collection we're operating on
		 * @var string
		 */
		var $table	=	'';

		/**
		 * our mongo library object
		 * @var object
		 */
		var $mongo;

		/**
		 * our database object
		 * @var object
		 */
		var $db;
		

		/**
		 * Init function, calls base::_init() and loads the DB class.
		 * 
		 * @param object &$event	Event object
		 */
		function _init(&$event)
		{
			parent::_init($event);
			
			$this->mongo	=	&$event->object('db_mongo');
			$this->db		=	$this->mongo->db;
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

		function save(&$data)
		{
			return $this->db->{$this->table}->save($data);
		}
	}
?>
