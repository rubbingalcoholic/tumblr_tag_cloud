<?
	/**
	 * This file holds the SQL database abstraction object. It closely models the functionality
	 * of PEAR::DB, but has taken on a life of its own and is NOT interchangeable with PEAR::DB.
	 * 
	 * This object has the capability to run both MySQL and MSSQL queries seamlessly.
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
	
	// Object-specific defines for current database operating mode. Supports MySQL (default) and MSSQL
	/**
	 * MySQL operation mode define
	 */
	if(!defined('AFRAME_DB_MODE_MYSQL'))
	{
		define('AFRAME_DB_MODE_MYSQL', 0);
	}
	
	/**
	 * MSSQL operation mode define
	 */
	if(!defined('AFRAME_DB_MODE_MSSQL'))
	{
		define('AFRAME_DB_MODE_MSSQL', 1);
	}
	
	/**
	 * MySQLi operation mode define
	 */
	if(!defined('AFRAME_DB_MODE_MYSQLI'))
	{
		define('AFRAME_DB_MODE_MYSQLI', 2);
	}

	/**
	 * Database object that removes some annoying busywork from database access. Aside from function
	 * notation, closely resembles functionality of PEAR::DB. 
	 * 
	 * It supports both MySQL (default) and MSSQL database handling modes.
	 * 
	 * Also supports database replication. It will read from a slave and write to a master. It does
	 * NOT support load balancing though, so an actual load balancer must be used to split up
	 * requests to different slaves/masters. 
	 *  
	 * @package		aframe
	 * @subpackage	aframe.core
	 * @author		Andrew Lyon
	 */
	class db_sql
	{
		/**
		 * Current DB connection
		 * @var res
		 */
		public $dbc;
		
		/**
		 * MySQL fetch mode
		 * @var int (constant)
		 */
		public $fetch_mode;
		
		/**
		 * Default character set we're operating in (default null)
		 */
		public $charset	=	null;
		
		/**
		 * An array containing all queries run by object in their final form
		 * @var array
		 */
		public $queries;
		
		/**
		 * Whether or not to free results directly after querying. saves memory, may slightly hit performance
		 * @var bool 
		 */
		public $free_res	=	false;
		
		/**
		 * Mode to run in (AFRAME_DB_MODE_MYSQL = mysql, AFRAME_DB_MODE_MYSQLI = mysqli, AFRAME_DB_MODE_MSSQL = mssql)
		 * @var int (constant)
		 */
		public $mode	=	AFRAME_DB_MODE_MYSQL;

		/**
		 * A connection error code (if any)
		 * @var int
		 */
		public $error_code = null;

		/**
		 * A connection error string (if any)
		 * @var string
		 */
		public $error_msg = null;		
		
		/**
		 * Holds our dbc connections (used for replication, mainly)
		 * @var array
		 */
		private $connections;
		
		/**
		 * Whether or not we want to support database replication
		 * @var bool
		 */
		private $replication		=	false;
		
		/**
		 * Whether or not a user manually set a connection lock (keeps db from auto-selecting master/slave depending
		 * on query type
		 * @var bool
		 */
		private $connection_lock	=	false;
		
		/**
		 * Whether or not we're in a transaction
		 * @var bool
		 */
		private $in_transaction		=	false;
		
		/**
		 * Whether or not we're connected
		 * @var bool
		 */
		private $connected			=	false;
		
		/**
		 * Holds queries we are going to be running in batch via multi-query
		 * @var array
		 */
		private $batch_queue		=	array();
		
		/**
		 * Textual indicator of which connection we're using
		 * @var string
		 */
		private $using;
		
		/**
		 * Holds our connection / operating parameters
		 * @var array
		 */
		private $params;
		
		/**
		 * CTOR
		 */
		function db_sql($params)
		{
			$this->queries		=	array();
			$this->connections	=	array();
			$this->connected	=	false;
			$this->params		=	$params;
			
			// initialize our parameters
			$this->mode			=	isset($params['mode']) ? $params['mode'] : AFRAME_DB_MODE_MYSQL;
			$this->free_res		=	isset($params['free_res']) ? $params['free_res'] : false;
			$this->replication	=	isset($params['replication']) ? $params['replication'] : false;
			$this->charset		=	isset($params['charset']) ? $params['charset'] : null;
		}
		
		/**
		 * Abraction of connections, devised mainly for ease of replication. 
		 * 
		 * @param bool $test_only		Whether to only test the connection settings. Prevents errors from being thrown.
		 *
		 * @return bool					true unless $test_only = true and the database failed to connect
		 */
		public function connect($test_only = false)
		{
			if($this->connected)
			{
				return true;
			}
			
			// set some defaults
			$port		=	isset($this->params['port']) ? $this->params['port'] : '';
			$persist	=	isset($this->params['persist']) ? $this->params['persist'] : false;
			
			if($this->replication)
			{
				$mport	=	isset($this->params['master']['port']) ? $this->params['master']['port'] : '';
				$sport	=	isset($this->params['slave']['port']) ? $this->params['slave']['port'] : '';
				$this->connections[0]	=	$this->do_connect($this->params['master']['hostspec'], $this->params['username'], $this->params['password'], $this->params['database'], $mport, $persist, $test_only);
				$this->connections[1]	=	$this->do_connect($this->params['slave']['hostspec'], $this->params['username'], $this->params['password'], $this->params['database'], $sport, $persist, $test_only);
			}
			else
			{
				// we aren't replicating, get our single connection and set it to connection slot 0
				$this->connections[0]	=	$this->do_connect($this->params['hostspec'], $this->params['username'], $this->params['password'], $this->params['database'], $port, $persist, $test_only);
				
				// immediately initialize our dbc
				$this->dbc				=	&$this->connections[0];
			}

			// JL NOTE ~ if we're testing only we don't want to actually do anything useful with our connection
			if ($test_only)
			{
				if (!$this->connections[0])
					return false;
				else
					return true;
			}
			
			$this->connected	=	true;
			
			// notice this is AFTER we set our DB to connected to avoid endless loop :)
			if(!empty($this->charset))
			{
				// if we're using a specific charset, set it
				$this->execute('SET NAMES ?', array($this->charset));
			}
			
			return true;
		}
		
		/**
		 * Generate and return a database connection based on the given database type.
		 * 
		 * @param string $host				hostname to conenct to
		 * @param string $username			username to connect with
		 * @param string $password			password to connect with
		 * @param string $database			database we're connecting to
		 * @param integer $port				(optional) port we're connecting on
		 * @param bool $persist				(optional) whether or not to persist this connection (default=false)
		 * @param bool $suppress_errors		whether to suppress errors. instead of throwing an error we'd return false.
		 * @return resource					database connection resource
		 */
		private function do_connect($host, $username, $password, $database, $port = '', $persist = false, $suppress_errors = false)
		{
			if($this->mode == AFRAME_DB_MODE_MYSQL)
			{
				// MySQL mode
				$host	=	!empty($port) && $port != 3306 ? $host .':'. $port : $host;
				if(isset($persist) && $persist)
				{
					$dbc	=	mysql_pconnect($host, $username, $password);
				}
				else
				{
					$dbc	=	mysql_connect($host, $username, $password);
				}
				
				if(!$dbc)
				{
					$this->error_code = mysql_error();
					$this->error_msg = mysql_errno();

					if (!$suppress_errors)
						trigger_error('Database connection failed: (' . $this->error_code . ': ' . $this->error_msg . ')', E_USER_ERROR);
					else
						return false;
				}
				
				mysql_select_db($database, $dbc);
				
				$this->fetch_mode	=	MYSQL_ASSOC;
			}
			else if($this->mode == AFRAME_DB_MODE_MYSQLI)
			{
				// MySQLi
				
				// check for socket
				if($host[0] == ':')
				{
					$socket	=	substr($host, 1);
					$host	=	'localhost';
				}
				else
				{
					$socket	=	null;
				}
				
				// no persistent connection allowed in MySQLi, just transparently connect the normal way
				if(!empty($port))
				{
					$dbc	=	mysqli_connect($host, $username, $password, $database, $port, $socket);
				}
				else
				{
					$dbc	=	mysqli_connect($host, $username, $password, $database, 3306, $socket);
				}
				
				if(mysqli_connect_error())
				{
					$this->error_code = mysqli_connect_errno();
					$this->error_msg = mysqli_connect_error();

					if (!$suppress_errors)
						trigger_error('Database connection failed: (' . $this->error_code . ': ' . $this->error_msg . ')', E_USER_ERROR);
					else
						return false;
				}
				
				$this->fetch_mode	=	MYSQLI_ASSOC;
			}
			else if($this->mode == AFRAME_DB_MODE_MSSQL)
			{
				// MSSQL mode, you know the rest.
				$host	=	isset($port) && $port != 1433 ? $host .':'. $port : $host;
				if(isset($persist) && $persist)
				{
					$dbc	=	mssql_pconnect($host, $username, $password);
				}
				else
				{
					$dbc	=	mssql_connect($host, $username, $password);
				}
				
				if(!$dbc)
				{
					$this->error_code = -1;
					$this->error_msg = mssql_get_last_message();

					if (!$suppress_errors)
						trigger_error('Database connection failed: (' . $this->error_msg . ')', E_USER_ERROR);
					else
						return false;
				}
				
				mssql_select_db($database, $dbc);

				$this->fetch_mode	=	MSSQL_ASSOC;
			}
			
			return $dbc;
		}
		
		/**
		 * Disconnect from our server(s)
		 */
		public function disconnect()
		{
			if(!$this->connected)
			{
				return true;
			}
			
			if($this->replication)
			{
				$this->do_disconnect($this->connections[0]);
				$this->do_disconnect($this->connections[1]);
			}
			else
			{
				$this->do_disconnect($this->dbc);
			}
			
			$this->connected	=	false;
			
			return true;
		}
		
		/**
		 * Run a disconnect with a given connection resource
		 */
		private function do_disconnect(&$dbc)
		{
			if($this->mode == AFRAME_DB_MODE_MYSQL)
			{
				mysql_close($dbc);
			}
			else if($this->mode == AFRAME_DB_MODE_MYSQLI)
			{
				mysqli_close($dbc);
			}
			else if($this->mode == AFRAME_DB_MODE_MSSQL)
			{
				mssql_close($dbc);
			}
			
			return true;
		}
		
		/**
		 * Make sure the next query is run on the master server.
		 */
		public function use_master()
		{
			// make sure we aren't stepping on any toes
			if($this->is_locked())
			{
				return true;
			}
			
			// return the main connection (non-replicated) and/or master connection (replicated)
			$this->dbc	=	&$this->connections[0];
			
			$this->using	=	'master';
			$this->set_lock();
		}
		
		/**
		 * Make sure the next query is run on the slave server. If replication is off, it selects
		 * the main connection at slot 0
		 */
		private function use_slave()
		{
			// make sure we aren't stepping on any toes
			if($this->is_locked())
			{
				return true;
			}
			
			if($this->replication)
			{
				$this->dbc	=	&$this->connections[1];
			}
			else
			{
				// we aren't replicating, transparently just select the main connection
				$this->dbc	=	&$this->connections[0];
			}
			
			$this->using	=	'slave';
			$this->set_lock();
		}
		
		/**
		 * Lock the current connection until AFTER the next query is run. Only good for one query!
		 */
		private function set_lock()
		{
			$this->connection_lock	=	true;
		}
		
		/**
		 * Clear the lock on the connection
		 */
		private function clear_lock()
		{
			$this->connection_lock	=	false;
		}
		
		/**
		 * Test whether or not the conneciton is locked
		 */
		private function is_locked()
		{
			return $this->connection_lock;
		}
		
		/**
		 * Takes a useless jumble of SQl, ?'s, and !'s and turns it all into a magnificent
		 * query that any database can run.
		 *
		 * This function takes ?'s, replaces it with the "'" escaped param, and puts quotes
		 * (') around it. It also takes ! and replaces it with the literal form of its 
		 * corresponding param. Although literal, it DOES escape with the regex [^a-z0-9=\-],
		 * ensuring that UNLESS SPECIFIED BY $rawlit, no harmful characters can enter the
		 * query, even through literals. bitchin. Even a moron developer couldn't fuck this up.
		 * 
		 * Examples:
		 * 
		 * $params = array(10)
		 * SELECT * FROM users WHERE id = !
		 * yields:
		 * SELECT * FROM users WHERE id = 10
		 * 
		 * $params = ('asdf', 13)
		 * SELECT * FROM idiots WHERE name <> ? AND age > !
		 * yields:
		 * SELECT * FROM idiots WHERE name <> 'asdf' AND age > 13
		 * 
		 * etc, etc, etc
		 * 
		 * @param string $query		string containing our SQL to transform
		 * @param array $params		collection of parameters to replace meta chars in SQL with 
		 * @param boolean $rawlit	(optional) use this to NOT filter character in the ! literal
		 * 							DO NOT set this to TRUE without a good understanding of SQL!!
		 * @return string			our damnin hellin query...ready to run
		 */
		public function prepare($query, $params, $rawlit = false)
		{
			// init our params
			if(!is_array($params))
			{
				$params	=	array($params);
			}
			
			// initialize blank query string
			$qry	=	'';
			
			// loop over every character in our query
			for($i = 0, $p = 0, $n = strlen($query); $i < $n; $i++)
			{
				// check for meta characters
				if($query[$i] == '!' || $query[$i] == '?')
				{
					// we have a meta character!! JOY! check whether its NULL (special case), a string, or a literal
					if($params[$p] === null)
					{
						// doesn't matter if we have a string or literal, we passed null (specifically) as the parameter.
						// this will be translated as NULL into the database. very nice!
						$qry	.=	"NULL";
					}
					else if($query[$i] == '!')
					{
						// we got a literal! if $rawlit is false (probably should be) escape the respective parameter
						if(!$rawlit && !is_numeric($params[$p]))
						{
							$params[$p]	=	preg_replace('/[^a-z0-9=\-]/i', '', $params[$p]);
						}
						
						// add our parameter to the query string
						$qry	.=	$params[$p];
					}
					elseif($query[$i] == '?')
					{
						// our parameter is a string. escape it no matter what, and.......
						$str	=	$this->escape($params[$p]);
						
						// ...add it to our query string, WITH quotes
						$qry	.=	"'" . $str . "'";
					}
					
					// since we added a parameter to the query string, increase the param number by one (so we don't keep re-using the same parameter)
					$p++;
				}
				else
				{
					// awww, no meta character, add it to our stupid query string
					$qry	.=	$query[$i];
				}
			}
			
			return $qry;
		}

		/**
		 * Run a query, grab the resource, and allow the developer to run mysql_* functions
		 * on it. 
		 * 
		 * @param string $query		un-prepared query to run
		 * @param array $params		SQL parameters
		 * @param boolean $rawlit	(optional) use this to NOT filter character in the ! literal
		 * @return resource			query resource
		 * @see						db:execute()
		 */
		public function query($query, $params = array(), $rawlit = false)
		{
			$query	=	$this->prepare($query, $params, $rawlit);
			$res	=	$this->_query($query);
			return $res;
		}
		
		/**
		 * Run a query, don't return resource.
		 * 
		 * This function wraps db::query(), except it doesn't return the query resource. 
		 * In other words, the two functions are interchangeable, unless you want to run mysql_*
		 * functions on the result...then use query().
		 * 
		 * @param string $query		un-prepared query to run
		 * @param array $params		SQL parameters
		 * @param boolean $rawlit	(optional) use this to NOT filter character in the ! literal
		 * @uses					db::query()
		 */
		public function execute($query, $params = array(), $rawlit = false)
		{
			$this->query($query, $params, $rawlit);
		}
		
		/**
		 * Run several queries at once. This function ONLY WORKS for AFRAME_DB_MODE_MYSQLI, using any
		 * other database mode with this function will break! We could program in support for splitting
		 * up the queries in the $query string into multiple queries, but parsing this within params
		 * would be difficult, time-wasting, and take a performance hit...don't use multi_query unless
		 * you are in AFRAME_DB_MODE_MYSQLI mode.
		 * 
		 * Also keep in mind, you MUST free the resulting resource if you are going to run any queries
		 * after using multi_query. When in doubt, use db::free($res) and make sure db::$free_res is
		 * set to true.
		 * 
		 * @param string $query		un-prepared query(s) to run
		 * @param array $params		SQL parameters
		 * @param boolean $rawlit	(optional) use this to NOT filter character in the ! literal
		 * @return resource			multi-query resource
		 */
		public function multi_query($query, $params = array(), $rawlit = false)
		{
			$query	=	$this->prepare($query, $params, $rawlit);
			$res	=	$this->_query($query, true);
			return $res;
		}
		
		/**
		 * This function allows adding queries to a batch to be executed all at once. Note that this
		 * only works with AFRAME_DB_MODE_MYSQLI selected! Preparing of the query is done in this
		 * method to make error checking easier.
		 * 
		 * Note that multi-queries can be run manually by just putting a bunch of queries in a string
		 * and executing (if using MySQLi), but this is a lot cleaner and allows better guessing of
		 * whether to use master or slave.
		 * 
		 * The batch is called by db::run_batch()
		 * 
		 * @param string $query		un-prepared query to run
		 * @param array $params		SQL parameters
		 * @param boolean $rawlit	(optional) use this to NOT filter characters in the ! literal
		 * @return bool				true
		 * @see						db::run_batch()
		 */
		public function add_query($query, $params = array(), $rawlit = false)
		{
			// prepare the query, trigger any necessary errors/warnings
			$prepared	=	$this->prepare($query, $params, $rawlit);
			
			// do we run this on master or slave?
			$use_server	=	$this->determine_query_server($prepared);
			
			// add the query and its parameters to our batch queue array
			$this->batch_queue[]	=	array(
				'query' 		=>	$prepared,
				'use_server'	=>	$use_server
			);
			
			// get the index (queue id) this query is in
			$idx	=	count($this->batch_queue) - 1;
			
			return $idx;
		}
		
		/**
		 * This takes all of the queries in the current batch queue, runs then all at once (in order of
		 * being added to the queue), clears the queue and returns the results.
		 * 
		 * It also decides whether or not this batch will be run on master or slave (if applicable).
		 * 
		 * @return array			results for each query, in order they were added to the queue
		 */
		public function run_batch()
		{
			if(empty($this->batch_queue))
			{
				return false;
			}
			
			// if we're in a transaction, always assume master
			$use_master	=	$this->in_transaction ? true : false;
			
			// loop over each query and add it to a flat array to be imploded later, checking which 
			// servers we're using along the way
			$queries	=	array();
			foreach($this->batch_queue as $query)
			{
				$queries[]	=	$query['query'];
				
				// check if we're using master. once set to true, cannot be unset =)
				if(!$use_master && $query['use_server'] == 'master')
				{
					$use_master	=	true;
				}
			}
			
			// reset our queue
			$this->batch_queue	=	array();
			
			// create a big multi query
			$multiquery	=	implode(";\n", $queries) . ";";
			
			// select the correct server
			if($use_master)
			{
				$this->use_master();
			}
			else
			{
				$this->use_slave();
			}
			
			// lock our server so _query() doesn't try to pick a server for us, then run the query and get our results
			$this->set_lock();
			$res	=	$this->_query($multiquery, true);
			$data	=	$this->gather_multi_results($res);
			
			return $data;
		}
		
		/**
		 * Run a query, shove all resulting rows into an array
		 * 
		 * @param string $query		un-prepared query to run
		 * @param array $params		SQL parameters
		 * @param boolean $rawlit	(optional) use this to NOT filter characters in the ! literal
		 * @param boolean $multi	(optional) whether or not to run a multi query and get all results
		 * @return array			result array
		 */
		public function all($query, $params = array(), $rawlit = false, $multi = false)
		{
			$query	=	$this->prepare($query, $params, $rawlit);
			$res	=	$this->_query($query, $multi);
			$data	=	array();
			
			if($this->mode == AFRAME_DB_MODE_MYSQLI && $multi)
			{
				// we have a multi query
				$data	=	$this->gather_multi_results($res);
			}
			else
			{
				// no multi-query, just process as normal...
				if($this->num_rows($res) > 0)
				{
					while($row = $this->fetch_row($res))
					{
						$data[]	=	$row;
					}
				}
				$this->free($res);
			}
			
			return $data;
		}

		/**
		 * Run a query, get first resultant row
		 * 
		 * @param string $query		un-prepared query to run
		 * @param array $params		SQL parameters
		 * @param boolean $rawlit	(optional) use this to NOT filter characters in the ! literal
		 * @return array			result array
		 */
		public function row($query, $params = array(), $rawlit = false)
		{
			$query	=	$this->prepare($query, $params, $rawlit);
			$res	=	$this->_query($query);
			$data	=	array();
			if($this->num_rows($res) > 0)
			{
				if($this->mode == AFRAME_DB_MODE_MYSQL)
				{
					$data	=	$this->fetch_row($res, MYSQL_ASSOC);
				}
				else if($this->mode == AFRAME_DB_MODE_MYSQLI)
				{
					$data	=	$this->fetch_row($res, MYSQLI_ASSOC);
				}
				else if($this->mode == AFRAME_DB_MODE_MSSQL)
				{
					$data	=	$this->fetch_row($res, MSSQL_ASSOC);
				}
			}
			$this->free($res);
			return $data;
		}
				
		/**
		 * Run a query, return value of first column of firsth row
		 * 
		 * @param string $query		un-prepared query to run
		 * @param array $params		SQL parameters
		 * @param boolean $rawlit	(optional) use this to NOT filter characters in the ! literal
		 * @return array			result array
		 */
		public function one($query, $params = array(), $rawlit = false)
		{
			$query	=	$this->prepare($query, $params, $rawlit);
			$res	=	$this->_query($query);
			$data	=	'';
			if($this->num_rows($res) > 0)
			{
				if($this->mode == AFRAME_DB_MODE_MYSQL)
				{
					$row	=	$this->fetch_row($res, MYSQL_NUM);
				}
				else if($this->mode == AFRAME_DB_MODE_MYSQLI)
				{
					$row	=	$this->fetch_row($res, MYSQLI_NUM);
				}
				else if($this->mode == AFRAME_DB_MODE_MSSQL)
				{
					$row	=	$this->fetch_row($res, MSSQL_NUM);
				}
				$data	=	$row[0];
			}
			$this->free($res);

			return $data;
		}

		/**
		 * Alert the stupid database that we are initiating a transaction
		 */
		public function transaction()
		{
			if($this->mode == AFRAME_DB_MODE_MYSQL)
			{
				$qry	=	"start transaction";
			}
			else if($this->mode == AFRAME_DB_MODE_MYSQLI)
			{
				$qry	=	"start transaction";
			}
			else if($this->mode == AFRAME_DB_MODE_MSSQL)
			{
				$qry	=	"begin transaction";
			}
			$this->query($qry);
			
			$this->in_transaction	=	true;
		}
		
		/**
		 * Commit all the changes to the db that happened since we opened our transaction
		 * 
		 * @see db::transaction()
		 */
		public function commit()
		{
			if($this->mode == AFRAME_DB_MODE_MYSQL)
			{
				$qry	=	"commit";
			}
			else if($this->mode == AFRAME_DB_MODE_MYSQLI)
			{
				$qry	=	"commit";
			}
			else if($this->mode == AFRAME_DB_MODE_MSSQL)
			{
				$qry	=	"commit transaction";
			}
			$this->query($qry);
			
			$this->in_transaction	=	false;
		}
		
		/**
		 * HOLY SHIT!!! OMFGOSH ABORT!!
		 * 
		 * @see db::transaction()
		 */
		public function revert()
		{
			if($this->mode == AFRAME_DB_MODE_MYSQL)
			{
				$qry	=	"rollback";
			}
			else if($this->mode == AFRAME_DB_MODE_MYSQLI)
			{
				$qry	=	"rollback";
			}
			else if($this->mode == AFRAME_DB_MODE_MSSQL)
			{
				$qry	=	"rollback transaction";
			}
			$this->query($qry);
			
			$this->in_transaction	=	false;
		}
		
		/**
		 * Wrapper around db::revert()
		 * 
		 * @see db::revert()
		 */
		public function rollback()
		{
			$this->revert();
		}

		/**
		 * Get the last query run off the stack and return it. Good for debugging a rotten query.
		 * 
		 * @return string	last query run off the stack
		 */
		public function last_query()
		{
			return $this->queries[count($this->queries) - 1];
		}
		
		/**
		 * Dump (return) all the queries run so far
		 * 
		 * @param string $sep	(optional) Separator betwee queries, defaults to '<br/>'
		 * @return string		Query dump
		 */
		public function dump_queries($sep = '<br/>')
		{
			$queries	=	implode($sep, $this->queries);
			return $queries;
		}
		
		/**
		 * Wrapper around mysql_real_escape_string in MySQL mode, replaces 's with '' in MSSQL mode.
		 * 
		 * @param string $str	string to be escaped
		 * @return string		escaped bulllllshit
		 */
		public function escape($str)
		{
			$this->connect();
			
			if($this->mode == AFRAME_DB_MODE_MYSQL)
			{
				return mysql_real_escape_string($str, $this->connections[0]);
			}
			else if($this->mode == AFRAME_DB_MODE_MYSQLI)
			{
				return mysqli_real_escape_string($this->connections[0], $str);
			}
			else if($this->mode == AFRAME_DB_MODE_MSSQL)
			{
				return str_replace("'", "''", $str);
			}
		}
		
		/**
		 * Given a prepared query, determine whether or not it should be run on the master or slave server.
		 * Doesn't select any connections or make any decisions, just returns it's verdict.
		 * 
		 * @param string $prepared_query	query that has been prepared for execution
		 * @return string					master|slave
		 */
		public function determine_query_server($prepared_query)
		{
			if(!$this->replication)
			{
				return 'slave';
			}
			
			$qry_test	=	substr(preg_replace('/[\r\n \t]+/is', ' ', $prepared_query), 0, 16);
			if(stripos($qry_test, 'SELECT') === false)
			{
				$server	=	'master';
			}
			else
			{
				$server	=	'slave';
			}
			
			return $server;
		}
		
		/**
		 * Send a prepared query to the database and record it for debugging purposes. dies on error.
		 * 
		 * Supports multi-queries, but ONLY when in AFRAME_DB_MODE_MYSQLI...dies otherwise.
		 * 
		 * Logs queries into db::$queries for later inspection (# queries run in request, cache testing, etc).
		 * 
		 * @param string $query		prepared query
		 * @param bool $multi		(optional) Whether or not we're running a multi-query
		 * @return resource			resource created by mysql_query(), on success
		 */
		public function _query($query, $multi = false)
		{
			if($this->mode != AFRAME_DB_MODE_MYSQLI && $multi)
			{
				trigger_error('You are trying to run a multi-query in a database extension that does not support this feature. Please modify your code to NOT use multi_query or use MySQLi mode.', E_USER_ERROR);
			}
			
			// make sure we're connected
			$this->connect();
			
			// TODO: fix this for multi queries. we're testing the first query for SELECT. we need to test ALL
			// queries for write commands (INSERT/REPLACE/DELETE/UPDATE/...) and select slave ONLY if we're 
			// running SELECTs and nothing else.
			
			// select the correct server
			if($this->in_transaction || $this->determine_query_server($query) == 'master')
			{
				// this is NOT a select OR we're in a transaction, use master
				$this->use_master();
			}
			else
			{
				// this is a select and we are not in a transaction, use slave
				$this->use_slave();
			}
			
			// add the query to our log
			$this->queries[]	=	($this->replication ? '('. $this->using .') ' : '') . $query;
			
			// reset connection lock
			$this->clear_lock();
			
			if($this->mode == AFRAME_DB_MODE_MYSQL)
			{
				if(!$res = mysql_query($query, $this->dbc))
				{
					trigger_error(mysql_error() . '<br/><br/>' . $query, E_USER_ERROR);
				}
			}
			else if($this->mode == AFRAME_DB_MODE_MSSQL)
			{
				if(!$res = mssql_query($query, $this->dbc))
				{
					trigger_error('Query failed: <br/><br/>' . $query, E_USER_ERROR);
				}
			}
			else if($this->mode == AFRAME_DB_MODE_MYSQLI)
			{
				if($multi)
				{
					$res	=	mysqli_multi_query($this->dbc, $query);
				}
				else
				{
					$res	=	mysqli_query($this->dbc, $query);
				}
				
				if(!$res)
				{
					trigger_error(mysqli_error($this->dbc) . '<br/><br/>' . $query, E_USER_ERROR);
				}
			}

			return $res;
		}
		
		/**
		 * Given a multi-query resource, gather all the results and return them
		 * 
		 * @param res $res		multi-query result (from db::_query())
		 * @return array		array of array of results
		 */
		public function gather_multi_results($res)
		{
			$data	=	array();
			
			do
			{
				if($res = mysqli_store_result($this->dbc))
				{
					$resdata	=	array();
					while($row = $this->fetch_row($res))
					{
						$resdata[]	=	$row;
					}
					$data[]	=	$resdata;
					$this->free($res);
				}
			} while(mysqli_more_results($this->dbc) && mysqli_next_result($this->dbc));
			
			return $data;
		}
		
		/**
		 * HMMMM I wonder
		 * 
		 * @param resource $res		mysql query resource
		 * @return int				number of fields in result
		 */
		public function num_fields(&$res)
		{
			if($this->mode == AFRAME_DB_MODE_MYSQL)
			{
				return mysql_num_fields($res);
			}
			else if($this->mode == AFRAME_DB_MODE_MYSQLI)
			{
				return mysqli_num_fields($res);
			}
			else if($this->mode == AFRAME_DB_MODE_MSSQL)
			{
				return mssql_num_fields($res);
			}
		}
		
		/**
		 * get a row from a db resource
		 * 
		 * @param resource $res			mysql query resource
		 * @param string $fetch_mode	(optional) The fetch mode we're using
		 * @return array				data array
		 */
		public function fetch_row(&$res, $fetch_mode = '')
		{
			$fetch_mode	=	empty($fetch_mode) ? $this->fetch_mode : $fetch_mode;
			if($this->mode == AFRAME_DB_MODE_MYSQL)
			{
				$data	=	mysql_fetch_array($res, $fetch_mode);
			}
			else if($this->mode == AFRAME_DB_MODE_MYSQLI)
			{
				$data	=	mysqli_fetch_array($res, $fetch_mode);
			}
			else if($this->mode == AFRAME_DB_MODE_MSSQL)
			{
				$data	=	mssql_fetch_array($res, $fetch_mode);
			}
			return $data;
		}
		
		/**
		 * Return the number of rows in a query resource
		 * 
		 * @param resource $res		the query resource to read from
		 * @return int				number of rows in resource
		 */
		public function num_rows(&$res)
		{
			if($this->mode == AFRAME_DB_MODE_MYSQL)
			{
				$data	=	mysql_num_rows($res);
			}
			else if($this->mode == AFRAME_DB_MODE_MYSQLI)
			{
				$data	=	mysqli_num_rows($res);
			}
			else if($this->mode == AFRAME_DB_MODE_MSSQL)
			{
				$data	=	mssql_num_rows($res);
			}
			return $data;
		}
		
		/**
		 * Frees database resource. Only frees the resource if it is specified in the object parameters.
		 * 
		 * @param resource $res		Resource to free (freeee mahi-mahi, freee mahi-mahi)
		 */
		public function free($res)
		{
			if(!$this->free_res)
			{
				return;
			}
			
			if($this->mode == AFRAME_DB_MODE_MYSQL)
			{
				mysql_free_result($res);
			}
			else if($this->mode == AFRAME_DB_MODE_MYSQLI)
			{
				mysqli_free_result($res);
			}
			else if($this->mode == AFRAME_DB_MODE_MSSQL)
			{
				mssql_free_result($res);
			}
		}
	}
?>
