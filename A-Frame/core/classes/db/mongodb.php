<?
	/**
	 * This file holds the MongoDB database abstraction object. It's a simple layer over
	 * Mongo that aims to be very loosely compatible with the the db_sql library.
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
	 * MongoDB operation mode define
	 */
	if(!defined('AFRAME_DB_MODE_MONGODB'))
	{
		define('AFRAME_DB_MODE_MONGODB', 3);
	}

	/**
	 * Honestly, couldn't think of a better place for it. Here's a very simple wrapper around
	 * new MongoId($id)
	 */
	function ObjectId($id)
	{
		return new MongoId($id);
	}

	/**
	 * Very tiny layer over native Mongo object for PHP. Somewhat compatible with A-Frame's
	 * db_sql (at least for connect/disconnect/constructors).
	 *  
	 * @package		aframe
	 * @subpackage	aframe.core
	 * @author		Andrew Lyon
	 */
	class db_mongo
	{
		/**
		 * Current DB connection
		 * @var Mongo
		 */
		public $dbc	=	false;

		/**
		 * Current DB
		 * @var MongoDB
		 */
		public $db	=	false;

		/**
		 * Holds the last error message
		 */
		public $last_error	=	'';

		/**
		 * holds the default w value for safe_(update/insert)
		 */
		public $w_value	=	true;
		
		/**
		 * CTOR
		 * 
		 * @param array $params		DB params to use when connecting
		 * @return bool				true
		 */
		public function db_mongo($params)
		{
			$hostspec		=	isset($params['hostspec']) ? $params['hostspec'] : 'mongodb://127.0.0.1:27017';
			$database		=	isset($params['database']) ? $params['database'] : 'test';
			$replicate		=	isset($params['replicate']) ? $params['replicate'] : false;
			$connect		=	isset($params['connect']) ? $params['connect'] : true;
			$persist		=	isset($params['persist']) ? $params['persist'] : false;
			$this->params	=	$params;

			$this->connect($hostspec, $database, $replicate, $persist, $connect);
			return true;
		}
		
		/**
		 * Abraction of connection.
		 * 
		 * @param string $hostspec		connection string to use
		 * @param string $database		database name
		 * @param bool $replicate		whether or not we're using replica sets
		 * @param bool $persist			persist the connect? (bad idea)
		 * @param bool $connect			whether to connect right away
		 * @return object				MongoDB object pointing to $database
		 */
		public function connect($hostspec, $database, $replicate = false, $persist = false, $connect = true)
		{
			if($this->dbc)
			{
				return $this->db;
			}

			$params		=	array(
				'connect'		=>	$connect,
				'replicaSet'	=>	$replicate
			);
			if($persist)
			{
				$params['persist']	=	$persist;
			}

			$this->dbc	=	new Mongo(
				$hostspec,
				$params
			);
			$this->db	=	$this->dbc->$database;

			return $this->db;
		}

		/**
		 * Perform a "safe" insert. Tries to insert (using safe mode) a number of times
		 * before giving up.
		 */
		public function safe_insert($collection, $data, $num_tries = 3)
		{
			// loop at most $numtries times before giving up
			for($i = 0; ; $i++)
			{
				try
				{
					$this->db->$collection->insert($data, array('safe' => $this->w_value));
				}
				catch(MongoCursorException $e)
				{
					if($i < $num_tries)
					{
						// we're still under the count, just keep trying
						usleep(500000);
						continue;
					}

					// save the error message
					$this->last_error	=	$e->getMessage();

					return false;
				}
				break;
			}

			return $data;
		}

		public function safe_update($collection, $query, $data, $num_tries = 3)
		{
			// loop at most $numtries times before giving up
			for($i = 0; ; $i++)
			{
				try
				{
					$this->db->$collection->update($query, $data, array('safe' => $this->w_value));
				}
				catch(MongoCursorException $e)
				{
					if($i < $num_tries)
					{
						// we're still under the count, just keep trying
						usleep(500000);
						continue;
					}

					// save the error message
					$this->last_error	=	$e->getMessage();

					return false;
				}
				break;
			}

			return $data;
		}

		/**
		 * Convert a mongo iterator to array. Wrapper around the very basic PHP function.
		 * 
		 * @param object $iterator	iteratable object
		 * @param bool $use_keys	use the keys from the MongoCursor (usually _ids)
		 * @return array			data from iterator
		 */
		public function itoa($iterator, $use_keys = false)
		{
			return iterator_to_array($iterator, $use_keys);
		}

		/**
		 * Computer, disconnect database. "DISCONNECTING!!!!!" *BEEP* *BOOP* *BOOP*
		 */
		public function disconnect()
		{
			if(!$this->dbc)
			{
				return;
			}
			$this->dbc->close();
			$this->dbc	=	false;
			$this->db	=	false;
		}
	}
?>
