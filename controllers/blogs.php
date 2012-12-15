<?
	/**
	 * Blogs Controller
	 * 
	 * This is the blogs controller. It defines functionality specific to blogs.
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
	class blogs extends app_controller
	{
		/**
		 *	Displays a list of all the blogs a user can edit
		 */
		function manage()
		{
			// Pop an HTTP authorization window if the user hasn't already done it
			$this->require_http_authorization();
			
			$blogs = $this->model->get_blogs();

			$this->set('blogs', $blogs);

			$this->set('page_title', 'Manage Your Blogs');

			$this->render('blogs/manage');
		}

		/**
		 *	Creates or edits the record for a tumblr blog
		 *
		 *	@param int $id		Optional ID to edit, if not passed we create a new blog
		 */
		function edit($id=0)
		{
			// Pop an HTTP authorization window if the user hasn't already done it
			$this->require_http_authorization();

			$posted		= $this->event->get('posted');
			$domain		= $this->event->get('domain');
			$settings	= $this->event->get('settings', array());

			if ($posted == 'yes')
			{
				$saved_id = $this->model->save_blog($id, $domain, $settings);
				if ($saved_id)
				{
					$this->redirect('/blogs/edit/' . $saved_id);
				}
			}
			elseif ($id > 0)
			{
				$blog = $this->model->get_blog_by_id($id);

				if (!empty($blog))
				{
					$domain 	= $blog['domain'];
					$settings	= $this->model->get_blog_settings($id);
				}
				else
				{
					$this->msg->add('Could not find the specified blog.');
					$this->redirect('/');
				}
			}

			$this->set('id', $id);
			$this->set('domain', $domain);
			$this->set('settings', $settings);

			$this->set('page_title', $id ? 'Edit Blog Settings' : 'Add a Blog');

			$this->render('blogs/edit');
		}

		/**
		 *	Deletes a blog
		 *
		 *	@param int $id		The blog ID to delete
		 */
		function delete($id)
		{
			// Pop an HTTP authorization window if the user hasn't already done it
			$this->require_http_authorization();

			$this->model->delete_blog($id);
			$this->redirect('/');
		}

		/**
		 *	Renders the cached tag cloud javascript include code
		 *	(this is what actually gets embedded on the tumblr site)
		 *
		 *	@param $string $domain		The domain to get the tags for
		 */
		function js($domain)
		{
			// Set our header type to javascript. This is good karma for browser compatibility.
			header("Content-type: text/javascript");

			$html_output = $this->model->get_tag_cloud($domain);

			$this->set('html_output', $html_output);

			$this->layout('js');
			$this->render('blogs/js');
		}
	}
?>