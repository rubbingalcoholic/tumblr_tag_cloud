<?
	require_once LIBRARY . '/curllib.php';
	
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
	class blogs_model extends app_model
	{
		// Used to print out JavaScript comments about our tag cloud generation
		private $debug_mode = true;

		private $default_settings = array(
			'max_tag_size_percent'	=> array('numeric', 300),
			'number_of_results'		=> array('numeric', 1000),
			'expire_timeout'		=> array('numeric', 10),
			'apply_css_styles'		=> array('select', 'default', 'default|none'),
			'text_scaling_math'		=> array('select', 'logarithmic', 'logarithmic|linear')
		);

		/**
		 *	Checks if any blog exists, optionally checks for a specific blog
		 *
		 *	@param string $domain		default '', optional domain for the blog to check
		 *	@param int $except_id		default 0, optional excludes the given id from duplicate check
		 *	@return boolean
		 */
		public function check_if_blog_exists($domain = '', $except_id = 0)
		{
			if ($domain != '')
			{
				$qry = "SELECT 1 as blog_exists, id FROM " . $this->tbl('blog') . " WHERE domain = ?";
				$result = $this->db->row($qry, array($domain));
			}
			else
			{
				$qry = "SELECT COUNT(*) as blog_exists FROM	" . $this->tbl('blog');
				$result = $this->db->row($qry, array());
			}

			// Logic, bitch.
			if (
				!empty($result)
				&&
				$result['blog_exists'] > 0
				&&
				(
					$except_id == 0
					||
					(
						isset($result['id'])
						&&
						$result['id'] != $except_id
					)
				)
			)
			{
				return true;
			}

			return false;
		}

		/**
		 *	Gets a blog from the db by ID
		 *
		 *	@param int $blog_id		The blog ID to lookup
		 *	@return array
		 */
		public function get_blog_by_id($blog_id)
		{
			$qry = "SELECT id, domain, create_date, mod_date FROM " . $this->tbl('blog') . " WHERE id = !";
			return $this->db->row($qry, array($blog_id));
		}

		/**
		 *	Gets a blog from the db by domain
		 *
		 *	@param string $domain	The blog domain to lookup
		 *	@return array
		 */
		public function get_blog_by_domain($domain)
		{
			$qry = "SELECT id, domain, create_date, mod_date FROM " . $this->tbl('blog') . " WHERE domain = ?";
			return $this->db->row($qry, array($domain));
		}

		/**
		 *	Gets all blogs in the db
		 */
		public function get_blogs()
		{
			$qry = "SELECT id, domain, create_date, mod_date FROM " . $this->tbl('blog');
			return $this->db->all($qry, array());
		}

		/**
		 *	Saves a blog (either existing or new, after validation
		 *
		 *	@param int $id			default 0, only > 0 if saving an existing blog
		 *	@param string $domain	the domain
		 *	@param array $settings	optional array of settings to save
		 *	@return mixed			false on fail, integer id on success
		 */
		public function save_blog($id = 0, $domain, $settings = array())
		{
			// Validate that a domain string was actually provided
			if (empty($domain))
			{
				$this->msg->add('Please enter a domain.');
				return false;
			}

			// Now sanitize the domain string
			$domain = str_replace("http://", "", $domain);
			$has_slash = strpos($domain, "/");
			$domain = $has_slash ? substr($domain, 0, $has_slash) : $domain;
			
			// Now validate that our sanitized domain doesn't already exist
			if ($this->check_if_blog_exists($domain, $id))
			{
				$this->msg->add('The domain you entered is already registered in the database.');
				return false;
			}

			// Now validate that we can actually query and appropriate JSON feed from this blog
			$results = $this->query_blog($domain, 1);

			if (empty($results))
			{
				$this->msg->add('Couldn\'t get any results from that domain. Are you sure it\'s actually a blog?');
				return false;	
			}

			// We passed validation!!!!!!!!!!!!!!!!!
			$data = array('domain' => $domain);

			if ($id)
				$data['id'] = $id;

			// Set up a transaction so we don't fuck this up
			$this->db->transaction();

			// Save our data in the database using A-Frame's amazing DB mapping
			// Almost as convenient as using MongoDB but without the headache of a database engine that optimizes for benchmarks instead of stability
			// Oh wait they stopped doing that... now it's pretty fucking cool. Except for a one-off app that I want to make easy for n00bs to install
			// it doesn't really make sense (what shared hosting environment supports Mongo??). Also, the initial benefit in a schema-less design
			// ends up being more of a headache in the longrun when you effectively end up managing your schema in your code. There's something to be
			// said for having database code that shits a brick if you try to put something stupid into it, versus having it let you do whatever you
			// want and then realizing what a horrible mistake you've made later when you've corrupted the shit out of your data
			$this->table = $this->tbl('blog');
			$this->save($data);

			// If this is a new record, we need to lookup its ID for the next step
			if (!$id)
			{
				$qry = "SELECT @@identity AS last_id";
				$result = $this->db->row($qry, array());

				if (!$result)
				{
					// Something really just fucked up. Rollback our transaction.
					$this->db->rollback();

					$this->msg->add('Could not find the ID for the blog we just inserted. That\'s pretty weird and must be a database-level issue.');
					return false;
				}

				$data['id'] = $result['last_id'];
			}
			else
			{
				// Clear out any cached data for this blog
				$this->delete_all_cached_blog_data($id);
			}

			// If there are settings, save those as well
			if (!empty($settings))
			{
				if (!$this->save_blog_settings($data['id'], $settings))
				{
					$this->db->rollback();
					return false;
				}
			}
			else
			{
				// Check if ANY settings are defined for this blog. If not, we'll define some based on our defaults
				$blog_settings = $this->get_blog_settings($data['id']);

				if (empty($blog_settings))
				{
					$this->insert_default_settings($data['id']);
				}
			}

			// Commit the transaction. LOLOLOL.
			$this->db->commit();

			$this->msg->add('Successfully saved blog <em>' . $domain . '</em>!', MSG_SUCCESS);

			return $data['id'];
		}

		/**
		 *	Deletes a blog from the database and clears any cached shit associated with it
		 *
		 *	@param int $id		The blog ID to delete
		 *	@return boolean		Whether or not the deed was done
		 */
		public function delete_blog($id)
		{
			$blog = $this->get_blog_by_id($id);

			if (empty($blog))
			{
				$this->msg->add('Could not find the specified blog for deletion. LOL');
				return false;
			}

			$this->clear_blog_settings($id);
			$this->delete_all_cached_blog_data($id);

			$qry = "DELETE FROM " . $this->tbl('blog') . " WHERE id = !";
			$this->db->execute($qry, array($id));

			$this->msg->add('Successfully deleted <em>' . $blog['domain'] . '!</em>');

			return true;
		}

		/**
		 *	Queries a Tumblr blog via HTTP cURL for hopefully a JSON response
		 *
		 *	@param string $domain		The domain to call lookup (no http:// prefixed)
		 *	@param int $num_results		default 50, The desired number of results
		 *	@param int $start			default 0, The post offset to start with
		 *	@return array 				The results
		 */
		public function query_blog($domain, $num_results = 50, $start = 0)
		{
			$data = curllib::get('http://' . $domain . '/api/read/json', array('callback' => '', 'filter' => 'text', 'num' => $num_results, 'start' => $start));

			if (!$data)
				return array();

			// Oh good, there was data but unfortunately Tumblr's idea of JSON is not JSON. We have to chop it up a bit.
			// By default they prepend 'var tumblr_api_read = ' and append ';' to their data
			$data = substr($data, 22, (strlen($data) - 24));
		
			// Now we can convert to JSON!!
			$data = json_decode($data, true);

			if (!$data)
				return array();

			return $data; 
		}

		/**
		 *	Gets all settings associated with a particular blog in the database
		 *	If a setting is not saved for one of our defaults, we'll return the default
		 *
		 *	@param int $blog_id		The ID of the blog we're getting settings for
		 *	@return array 			An array containing the settings
		 */
		public function get_blog_settings($blog_id)
		{
			$qry = "SELECT	name, value
					FROM	" . $this->tbl('blog_setting') . "
					WHERE	blog_id = !";
			$results = $this->db->all($qry, array($blog_id));

			$settings = array();

			for ($i=0, $c=count($results); $i<$c; $i++)
			{
				$settings[$results[$i]['name']] = $results[$i]['value'];
			}

			// Set defaults, if needed
			foreach ($this->default_settings as $name => $value)
			{
				if (!isset($settings[$name]))
				{
					$settings[$name] = $value[1];
				}
			}

			return $settings;
		}

		/**
		 *	Clears out any settings for the specified blog
		 *
		 *	@param int $blog_id		The blog ID to clear settings for
		 */
		private function clear_blog_settings($blog_id)
		{
			$qry = "DELETE FROM " . $this->tbl('blog_setting') . " WHERE blog_id = !";
			$this->db->execute($qry, array($blog_id));
		}

		/**
		 *	Inserts our default settings for the specified blog
		 *
		 *	@param int $blog_id		The blog ID for which we insert settings
		 */
		private function insert_default_settings($blog_id)
		{
			// First clear out any settings! How sad would it be to have duplicates?
			$this->clear_blog_settings($blog_id);

			$this->table = $this->tbl('blog_setting');

			foreach ($this->default_settings as $name => $value)
			{
				$setting = array(
					'blog_id'	=> $blog_id,
					'name'		=> $name,
					'value'		=> $value[1]
				);
				$this->save($setting);
			}
		}

		/**
		 *	Saves settings for the given blog
		 *
		 *	@param int $blog_id			Blog ID
		 *	@param array $settings		Array of settings to save
		 */
		private function save_blog_settings($blog_id, $settings)
		{
			// Validate settings. This is a bit janky, but fuck you for noticing
			foreach ($settings as $name => $value)
			{
				if (!isset($this->default_settings[$name]))
				{
					$this->msg->add('No allowed setting for <em>' . $name . '</em>. Are you trying to be clever?');
					return false;
				}

				switch($this->default_settings[$name][0])
				{
					case 'numeric':
						if (!is_numeric($value))
						{
							$this->msg->add('The setting for <em>' . $name . '</em> must be a number!');
							return false;
						}
						break;
				}
			}

			// Clear any existing settings for the blog
			$this->clear_blog_settings($blog_id);

			// More A-Frame DB abstraction magic
			$this->table = $this->tbl('blog_setting');

			// Now loop again and save each setting. We could move the clear_blog_settings step to the top and save within
			// the first loop, but if we ever made a call to this function outside the safety of our save_blog function,
			// it would be possible to clear the settings and save some before failing validation and returning on others
			// NOT THAT ANYONE CARES
			foreach ($settings as $name => $value)
			{
				$setting = array(
					'blog_id'	=> $blog_id,
					'name' 		=> $name,
					'value' 	=> $value
				);
				$this->save($setting);
			}
			return true;
		}

		/**
		 *	The main workload function for the whole app. Returns HTML code for a tag cloud.
		 *	First it checks the (optional) cache and then the DB, and if no current hit is made, it calls out to Tumblr
		 *	and renders, caches and returns the cloud based on the blog's settings.
		 *
		 *	@param string $domain		The domain
		 *	@return string				HTML tag cloud
		 */
		public function get_tag_cloud($domain)
		{
			$blog = $this->get_blog_by_domain($domain);

			// If the domain was bogus, we're done.
			if (empty($blog))
			{
				return '[FAIL!!!1!! The specified blog is not defined. L0zER!]';
			}

			// RA HACK ~ Increase PHP's execution time limit so the request doesn't fail for bigger workloads
			set_time_limit(600);

			// First check the cache for the html output
			$ns 			= $this->get_blog_cache_ns($blog['id']);
			$cache_key		= 'blogs_model:'.$ns.':get_tag_cloud';
			$html_output	= $this->cache->get($cache_key);

			if ($html_output)
			{
				$this->debug_js_write("CACHE HIT!");
				return $html_output;
			}

			// Next check our DB cache
			$html_output	= $this->get_db_cache_data($blog['id'], 'tag_cloud');

			if ($html_output)
			{
				$this->debug_js_write("DB CACHE HIT!");
				return $html_output;
			}


			// Get the settings for this blog
			$settings = $this->get_blog_settings($blog['id']);
			
			// Initialize some variables
			$posts_queried	= 0;
			$max_hit_count 	= 1;		// we'll update this with our maximum hit count
			$min_hit_count 	= 1;		// we're just going to assume this is always 1.
			$html_output	= '';
			$posts			= array();
			$tags 			= array();

			// Query the actual Tumblr blog. We may have to call multiple pages to get as many results as we'd like
			$tags = array();

			// Get each page of results from the Tumblr API until there are no more results
			// or until we've hit our maximum desired lookup count
			do
			{
				$results = $this->query_blog($domain, 50, $posts_queried);

				if (empty($results) || empty($results['posts']))
					break;

				$posts_queried += count($results['posts']);

				$posts = array_merge($posts, $results['posts']);
			}
			while ($posts_queried < $settings['number_of_results']);

			// Loop over each of the Tumblr posts to assemble an array of tags and their counts
			for ($i=0, $c=count($posts); $i<$c; $i++)
			{
				if (!isset($posts[$i]['tags']))
				{
					continue;
				}

				for ($j=0,$d=count($posts[$i]['tags']); $j<$d; $j++)
				{
					if (!isset($tags[$posts[$i]['tags'][$j]]))
					{
						$tags[$posts[$i]['tags'][$j]] = 1;
					}
					else
					{
						$tags[$posts[$i]['tags'][$j]]++;

						if ($tags[$posts[$i]['tags'][$j]] > $max_hit_count)
						{
							$max_hit_count = $tags[$posts[$i]['tags'][$j]];
						}
					}
				}
			}

			// If we've chosen to include CSS output, do it
			if ($settings['apply_css_styles'] == 'default')
			{
				$html_output .= '<style type="text/css">#tumblr_tag_cloud .tag_list{list-style:none;margin-left:0;padding-left:0;} #tumblr_tag_cloud .tag_list li{display:inline;}</style>';
			}
			$html_output .= '<ul class="tag_list">';

			// Loop over our tags array and assemble HTML output
			foreach ($tags as $key => &$val)
			{
				$val = array(
					'hits'	=> $val
				);
				// If all of the tags have only 1 count, we avoid doing the fancy scaling equations (for fear of division by zero)
				if ($max_hit_count == $min_hit_count)
				{
					$val['scale'] = 100;
				}
				elseif ($settings['text_scaling_math'] == 'logarithmic')
				{
					// math. bitch.
					$val['scale'] = 100 + (( (log($val['hits'])) / (log($max_hit_count) - log($min_hit_count)) ) * ($settings['max_tag_size_percent'] - 100) );
				}
				elseif ($settings['text_scaling_math'] == 'linear')
				{
					$val['scale'] = 100 + (($settings['max_tag_size_percent'] - 100) / ($max_hit_count - $min_hit_count) * ($val['hits'] - 1));
				}
				// For some reason tags show up sometimes that are actually URLs, filter them
				if (strpos($key, "http://") === false)
				{
					$html_output .= "\n" . '<li style="font-size: ' . $val['scale'] .'%"><a href="http://' . $domain . '/tagged/' . $key .'" title="' . $val['hits'] . ' Posts">' . $key . '</a> </li>';
				}
			}

			// Close out the HTML output
			$html_output .= "\n</ul>";

			// Filter some special characters to make the string JavaScript-friendly
			$html_output = str_replace("'", "\'", str_replace("\n", "\\\n", $html_output));

			// RA NOTE ~ This might help you debug the app.
			/*
			echo "count(posts): " . count($posts) . "\n";
			echo "max_hit_count: " . $max_hit_count . "\n";
			echo "html_output: " . $html_output . "\n";
			echo "tags: ";
			print_r($tags);
			die();
			*/

			// Store it in the cache!
			$this->cache->set($cache_key, $html_output, ($settings['expire_timeout'] * 60));
			$this->set_db_cache_data($blog['id'], 'tag_cloud', $html_output);

			return $html_output;
		}

		/**
		 *	Looks for a (non-expired) cached value in the database
		 *
		 *	@param int $blog_id		The blog ID to lookup
		 *	@param string $type		The cache keyname type to lookup
		 *	@return string
		 */
		function get_db_cache_data($blog_id, $type)
		{
			$settings = $this->get_blog_settings($blog_id);

			$qry = "SELECT	data
					FROM 	" . $this->tbl('blog_cache') . "
					WHERE	blog_id = !
					AND 	type = ?
					AND		timestamp > !";
			$result = $this->db->row($qry, array($blog_id, $type, (time() - ($settings['expire_timeout'] * 60))));

			if (!empty($result))
				return $result['data'];

			return null;
		}

		/**
		 *	Writes cached data for a blog after first removing any existing data of its type already associated with the blog
		 *
		 *	@param int $blog_id		The blog ID
		 *	@param string $type		The cache type keyname
		 *	@param string $data		The cache data to write
		 *	@return boolean
		 */
		function set_db_cache_data($blog_id, $type, $data)
		{
			// First delete any existing data for this type keyname
			$this->delete_db_cache_data($blog_id, $type);

			$this->table = $this->tbl('blog_cache');

			$data = array(
				'blog_id'	=> $blog_id,
				'type'		=> $type,
				'data'		=> $data,
				'timestamp'	=> time()
			);
			$this->save($data);

			return true;
		}

		/**
		 *	Deletes cached data for a blog, optionally only for a specific type keyname
		 *
		 *	@param int $blog_id		The blog ID
		 *	@param string $type		The cache type keyname
		 *	@return boolean
		 */
		function delete_db_cache_data($blog_id, $type = '')
		{
			$qry 	= "DELETE FROM " . $this->tbl('blog_cache') . " WHERE blog_id = ! ";
			$params	= array($blog_id);

			if ($type)
			{
				$qry 		.= 	"AND type = ?";
				$params[] 	=	$type;
			}
			$this->db->execute($qry, $params);

			return true;
		}

		/**
		 *	Removes all cached data for a blog (from Memcache/APC and the database)
		 *
		 *	@param int $blog_id		The blog ID to clear cache for
		 */
		function delete_all_cached_blog_data($blog_id)
		{
			$this->delete_blog_cache_ns($blog_id);
			$this->delete_db_cache_data($blog_id);
		}

		/**
		 * Gets the cache namespace for the blog
		 *
		 * @param int $blog_id		The blog id
		 * @return string
		 */
		function get_blog_cache_ns($blog_id)
		{
			return $this->cache->ns('blog_id:'.$blog_id);
		}
		
		/**
		 * Deletes the cache namespace for the blog
		 *
		 * @param int $blog_id		The blog id
		 * @return bool
		 */
		function delete_blog_cache_ns($blog_id)
		{
			return $this->cache->del_ns('blog_id:'.$blog_id);
		}

		/**
		 *	Writes debug output in the form of JavaScript comments, if debug_mode is turned on.
		 *	Yes, I know it breaks the MVC paradigm to have the model output stuff before the view
		 *	that's why this is for DEBUGGING PURPOSES. asshole.
		 */
		function debug_js_write($string)
		{
			if ($this->debug_mode)
				echo "// " . $string . "\n";
		}
		 
	}
?>
