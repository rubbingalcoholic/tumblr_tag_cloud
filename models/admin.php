<?
	/**
	 * Admin Model
	 * 
	 * This is the admin model. All of the workflow logic, API and database code related
	 * to admin functionality is set up here.
	 *
	 * Copyright (c) 2012 Rubbing Alcoholic
	 * 
	 * Licensed under The MIT License. 
	 * Redistributions of files must retain the above copyright notice.
	 * 
	 * @copyright	Copyright (c) 2012 Rubbing Alcoholic
	 * @license		http://www.opensource.org/licenses/mit-license.php
	 * @package		tumblr_tag_cloud
	 */
	class admin_model extends app_model
	{
		/**
		 * Any database error code
		 * @var int
		 */
		public $db_error_code = null;
		
		/**
		 * Any database error message, useful for helping debug connection errors
		 * @var string
		 */
		public $db_error_msg = null;

		/**
		 *	This function tries to connect to the database based on the settings defined in includes/local.php
		 *
		 *	@return boolean		true if database is configured properly, false otherwise
		 */
		public function test_database_connection()
		{
			error_reporting(0);
			$is_connected_properly = $this->db->connect(true);
			error_reporting(ERROR_LEVEL);

			if (!$is_connected_properly)
			{
				$this->db_error_code	= $this->db->error_code;
				$this->db_error_msg		= $this->db->error_msg;
			}
			return $is_connected_properly;
		}	

		/**
		 *	This function checks to see that all of the database tables are properly in place
		 *
		 *	RA NOTE ~ These checks are dependent on MySQL 5.0+. We could support other DBs by improving this
		 *
		 *	@return boolean		Whether the fucking tables exist
		 */
		public function test_database_tables_exist()
		{
			return (
				$this->exists_database_table('blog')
				&&
				$this->exists_database_table('blog_setting')
				&&
				$this->exists_database_table('blog_cache')
			);
		}

		/**
		 *	Checks existence of the blog table
		 *
		 *	@param string $table_name		The name of the table to look for
		 *	@return boolean
		 */
		private function exists_database_table($table_name)
		{
			$qry = "SELECT	COUNT(*) as table_exists
					FROM	information_schema.tables 
					WHERE	table_schema = ?
					AND 	table_name = ?";
			$result = $this->db->row($qry, array($this->config['db']['dsn']['database'], $this->tbl($table_name)));

			if (!empty($result) && $result['table_exists'] == 1)
				return true;

			return false;
		}

		/**
		 *	Creates the stupid database tables that I hate
		 *
		 *	RA NOTE ~ This is dependent on MySQL 5.0+ and MySQLi. We could support more DB's by not being retarded about this.
		 */
		public function create_database_tables()
		{
			$sql = file_get_contents(BASE . '/db.sql');

			$sql = str_replace('CREATE TABLE IF NOT EXISTS `', 'CREATE TABLE IF NOT EXISTS `' . $this->config['db']['prefix'], $sql);

			$this->db->connect();

			$queries = mysqli_multi_query($this->db->dbc, $sql);
			if($queries)
			{
				// We have to loop over each result from the multi-query and pretend to care about it
				// Otherwise the database driver will shit a brick if we try to do any further querying
				while(mysqli_next_result($this->db->dbc))
				{
					if($result = mysqli_store_result($this->db->dbc))
					{
						while($row = mysqli_fetch_row($result))
						{
							// do what you want, you little weasel. i don't care. i DON'T care.
						}
					}
				}
			}

			return true;
		}

		/**
		 *	Checks whether the provided admin username is valid, and if so, sets the session to logged in
		 *	Right now it just checks against our local.php defines because this doesn't need to be fancy
		 *
		 *	@param string $username		The username
		 *	@param string $password		The password
		 */
		function do_login($username, $password)
		{
			if ($username == ADMIN_USERNAME && $password == ADMIN_PASSWORD)
			{
				$_SESSION['logged_in'] = true;
				return true;
			}
			return false;
		}

		/**
		 *	Checks whether the provided admin username is valid, and if so, sets the session to logged in
		 *	Right now it just checks against our local.php defines because this doesn't need to be fancy
		 */
		function do_logout()
		{
			if (isset($_SESSION['logged_in']))
				unset($_SESSION['logged_in']);

			return true;
		}
	}
?>