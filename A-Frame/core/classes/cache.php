<?
	/**
	 * This file contains the main cache-abstraction class. It's purpose is to provide a common interface for
	 * cache interaction without tying the developer down to a specific caching method. Mainly mimicks the
	 * behavior of the PHP memcache class.
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
	
	// Cache-specific defines. The file_cache class mirrors the PHP memcache's implementation closely enough 
	// the two objects are interchangable, and thus CACHE_FILECACHE is uneccesary.
	/**
	 * Memcached cache type (interchangable with filecache)
	 */
	define('CACHE_MEMCACHE', 1);
	
	/**
	 * APC cache type
	 */
	define('CACHE_APC', 2);
	
	/**
	 * Cache object and interface that wraps around a few different cache objects.
	 * 
	 * This cache class conveniently wraps around the following:
	 *  a. PHP's Memcache object
	 *  b. A proprietary file cache object that writes data to files
	 *  c. APC's caching functions
	 * 
	 * The class automatically detects which to use, or can be specified by user.
	 * 
	 * This class also contains provisions to deal with Memcache's namespacing issues, or at least
	 * make them a bit more managable.
	 * 
	 * @package		aframe
	 * @subpackage	aframe.core
	 * @author		Andrew Lyon
	 */
	class cache
	{
		/**
		 * Holds our caching object (memcache, file_cache, etc)
		 * @var object
		 */
		var $cache;
		
		/**
		 * Is cache enabled?
		 * @var boolean
		 */
		var $use_cache;
		
		/**
		 * What cache class we're using...default memcache
		 * @var define
		 */
		var $cache_type	=	CACHE_MEMCACHE;
		
		/**
		 * Is our Memcache object using compression?
		 * @var integer
		 */
		var $compression	=	0;
		
		/**
		 * The default "Time To Live" value
		 * @var integer
		 */
		var $ttl;
		
		/**
		 * The key prefix for our cache (in case multiple apps use the same memcache server)
		 */
		var $key_prefix;
		
		/**
		 * CTOR
		 *
		 * Reads the params and fills in the gaps based on them. 
		 * 
		 * @param array $options	contains the options to initialize caching with
		 */
		function cache($options)
		{
			$this->use_cache	=	$options['caching'];
			$this->key_prefix	=	isset($options['key_prefix']) ? $options['key_prefix'] : '';
			
			if($options['type'] == 'memcache' && class_exists('Memcache'))
			{
				$this->cache		=	new Memcache;
				if($this->use_cache)
				{
					for($i = 0, $n = count($options['servers']); $i < $n; $i++)
					{
						$host		=	$options['servers'][$i][0];
						$port		=	isset($options['servers'][$i][1]) ? $options['servers'][$i][1] : 11211;
						$persist	=	isset($options['servers'][$i][2]) ? $options['servers'][$i][2] : true;
						$weight		=	isset($options['servers'][$i][3]) ? $options['servers'][$i][3] : 10;
						
						$con	=	$this->addServer($host, $port, $persist, $weight);
					}
				}
				$this->cache_type	=	CACHE_MEMCACHE;
				$this->compression	=	$options['compression'];
			}
			else if($options['type'] == 'apc')
			{
				$this->cache_type	=	CACHE_APC;
			}
			else
			{
				// no available cache class = no cache :(
				$this->use_cache	=	false;
			}

			$this->ttl	=	$options['ttl'];
		}
		
		/**
		 * Used to get a value from the cache with key $key
		 * 
		 * @param string $key	key used to load cache value with
		 * @return mixed		value of the data stored at $key
		 */
		function get($key)
		{
			if(!$this->use_cache) return false;
			
			$key	=	$this->key_prefix . $key;
			if($this->cache_type == CACHE_MEMCACHE)
			{
				return $this->cache->get($key);
			}
			elseif($this->cache_type == CACHE_APC)
			{
				return apc_fetch($key);
			}
		}
		
		/**
		 * Store a value into cache
		 * 
		 * @param string $key	defines the key to store the data into
		 * @param mixed $value	the data to store
		 * @param integer $ttl	(optional) the amount of life this particular item has
		 * @return boolean		indication of the success (true) or failure (false) of the
		 * 						operation
		 */
		function set($key, $value, $ttl = '')
		{
			if(!$this->use_cache) return false;
			
			$key	=	$this->key_prefix . $key;
			if(empty($ttl))
			{
				$ttl	=	$this->ttl;
			}
			
			if($this->cache_type == CACHE_MEMCACHE)
			{
				return $this->cache->set($key, $value, $this->compression, $ttl);
			}
			elseif($this->cache_type == CACHE_APC)
			{
				return apc_add($key, $value, $ttl);
			}
		}
		
		/**
		 * Delete a value from cache
		 * 
		 * @param string $key	key of the item to delete
		 * @return boolean		indication of the success or failure of the operation (true / false)
		 */
		function delete($key)
		{
			if(!$this->use_cache) return false;
			
			$key	=	$this->key_prefix . $key;
			if($this->cache_type == CACHE_MEMCACHE)
			{
				return $this->cache->delete($key);
			}
			else if($this->cache_type == CACHE_APC)
			{
				return apc_delete($key);
			}
		}
		
		/**
		 * Create/pull a namespace value and return it
		 * 
		 * @param string $name		name of our namespace
		 * @return					value of namespace
		 */
		function ns($name)
		{
			if(!$this->use_cache) return false;
			if($this->cache_type != CACHE_MEMCACHE) return false;
			
			$key	=	'namespace:'. $name;
			$key	=	$this->key_prefix . $key;
			if(($val = $this->cache->get($key)) === false)
			{
				$val	=	rand(0, 1000000);
			}
			$this->cache->set($key, $val, $this->compression, 3600);
			return $val;
		}
		
		/**
		 * Delete (increment) namespace value
		 * 
		 * @param string $name		name of namespace to "remove"
		 */
		function del_ns($name)
		{
			if(!$this->use_cache) return false;
			if($this->cache_type != CACHE_MEMCACHE) return false;
			
			$key	=	'namespace:'. $name;
			$key	=	$this->key_prefix . $key;
			$this->cache->increment($key);
		}
		
		/**
		 * Increment a value help in cache
		 * 
		 * @param string $key	Key in cache to increment
		 */
		function increment($key)
		{
			if(!$this->use_cache) return false;
			if($this->cache_type != CACHE_MEMCACHE) return false;
			
			$key	=	$this->key_prefix . $key;
			$this->cache->increment($key);
		}
		
		/**
		 * Decrement a value help in cache
		 * 
		 * @param string $key	Key in cache to decrement
		 */
		function decrement($key)
		{
			if(!$this->use_cache) return false;
			if($this->cache_type != CACHE_MEMCACHE) return false;
			
			$key	=	$this->key_prefix . $key;
			$this->cache->decrement($key);
		}

		/**
		 * Memcached-specific method used to add servers to the server pool
		 * 
		 * @param string $host		host / ip of server to add
		 * @param integer $port		port to cennect to $host with
		 * @param integer $weight	weight (importance) of this particular connection
		 * @return boolean			indication of success of the operation
		 */
        function addServer($host, $port = 11211, $persist = true, $weight = 10)
        {
			if(!$this->use_cache) return false;
			if($this->cache_type != CACHE_MEMCACHE) return false;

			$this->cache->addServer($host, $port, $persist, $weight);
        	return true;
        }
        
        /**
         * Flush out the cache (delete all items)
         */
		function flush()
		{
			if(!$this->use_cache) return false;
			
			if($this->cache_type == CACHE_MEMCACHE)
			{
				$this->cache->flush();
			}
			else if($this->cache_type == CACHE_APC)
			{
				return apc_clear_cache('user');
			}
			
		}
		
		/**
		 * Close all connections / open files
		 * 
		 * @return boolean	value indicating success of the operation
		 */
		function close()
		{
			if(!$this->use_cache) return false;
			if($this->cache_type != CACHE_MEMCACHE) return false;
			
			return $this->cache->close();
		}
	}
?>