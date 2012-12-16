<?
	/**
	 * Admin Controller
	 * 
	 * This is the admin controller. Basically, all of the admin functionality is handled by this controller.
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
	class admin extends app_controller
	{
		/**
		 * The index function runs a bunch of checks to see if the app is installed and configured properly.
		 * If so, the user is redirected to the dashboard page (admin/dashboard).
		 * If not, the user is prompted to fix the problem.
		 */
		function index()
		{
			// Pop up a password prompt
			$this->require_http_authorization();

			// First check to see that the database is set up properly
			$db_configured_properly = $this->model->test_database_connection();

			// If the db is not set up properly, throw an error
			if ($db_configured_properly == false)
			{
				$this->set('db_error_code', $this->model->db_error_code);
				$this->set('db_error_msg', $this->model->db_error_msg);

				$this->layout('message');
				$this->render('errors/database_connect');
				return;
			}

			// Now check if the database is actually populated with tables
			$db_tables_exist = $this->model->test_database_tables_exist();

			// Do the tables exist?
			if ($db_tables_exist == false)
			{
				// OMG the fucking tables don't fucking exist. FUCK
				$this->set('message_type', 'success');
				$this->layout('message');
				$this->render('messages/needs_db_install');
				return;
			}

			// Check if the user has set up their first blog yet
			$blogs_model = $this->event->model('blogs', $this->event);
			$blog_exists = $blogs_model->check_if_blog_exists();

			// Oh, they haven't set one up. Take the user to the edit page to create one.
			if ($blog_exists == false)
			{
				$this->redirect('/blogs/edit');
			}

			// Otherwise, redirect to the blogs manager
			$this->redirect('/blogs/manage');			
		}

		/** 
		 *	This function is used to install the app's tables in the case that they don't properly exist
		 */
		function create_tables()
		{
			// Pop up a password prompt
			$this->require_http_authorization();

			$this->model->create_database_tables();

			$this->msg->add("Successfully created the database tables! Time to add a blog!", MSG_SUCCESS);

			$db_tables_exist = $this->model->test_database_tables_exist();

			$this->redirect('/admin/index');
		}

		/**
		 *	The login page
		 */
		function login()
		{
			$redirect_to	= htmlspecialchars($this->event->get('r', '/'));
			$username		= $this->event->get('username');
			$password		= $this->event->get('password');
			$posted			= $this->event->get('posted');

			$this->set('message_type', 'prompt');

			if ($posted == 'yes')
			{
				if ($this->model->do_login($username, $password))
				{
					$this->redirect($redirect_to);
				}
				else
				{
					$this->set('message_type', 'error');
					$this->set('access_denied', true);
				}
			}
			$this->set('redirect_to', $redirect_to);

			$this->layout('message');
			$this->render('admin/login');
		}

		/**
		 *	The log out page
		 */
		function logout()
		{
			$this->model->do_logout();

			$this->set('message_type', 'success');
			$this->layout('message');
			$this->render('admin/logout');
		}

	}
?>