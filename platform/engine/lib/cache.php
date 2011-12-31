<?php
	/**
	 * @file
	 * Caching.
	 *
	 * This file contains the prototype and factory for the default caching
	 * technologies in the framework.
	 * 
	 * These caches are used by the framework, but can also be used by your plugins, to 
	 * do so it is recommended you ask for a cache (or a specific cache) by using a factory. 
	 * 
	 * This will allow your plugin to automatically take advantage of more advanced caches
	 * which may be provided by other plugins.
	 * 
	 * \code
	 *	// Ask for any old cache (defaults to a disk file cache)
	 *	$mycache = factory('cache');
	 * 
	 *	// Ask for a specific variable cache
	 *	$simplevariable = factory('cache:simplevariable');
	 * \endcode
	 *
	 * @package core
	 * @license The MIT License (see LICENCE.txt), other licenses available.
	 * @author Marcus Povey <marcus@marcus-povey.co.uk>
	 * @copyright Marcus Povey 2009-2012
	 * @link http://platform.barcamptransparency.org/
	 */


	/**
	 * Cache superclass.
	 * 
	 * This class defines the superclass for all cache objects. 
	 */
	abstract class Cache implements
		ArrayAccess // Override for array access
	{
		/**
		 * Variables used to configure cache object.
		 */
		private $variables;

		/**
		 * Configure cache.
		 *
		 * @param string $variable Variable name.
		 * @param string $value The value.
		 */
		public function setCacheVariable($variable, $value) 
		{
			if (!is_array($this->variables))
				$this->variables = array();
			
			$this->variables[$variable] = $value;	
		}
		
		/**
		 * Get variables for this cache.
		 *
		 * @param string $variable The variable to return.
		 * @return mixed|null The variable or null.
		 */
		public function getCacheVariable($variable) 
		{
			if (isset($this->variables[$variable]))
				return $this->variables[$variable];
				
			return null; 
		}
		
		/**
		 * Set a namespace for this cache.
		 * 
		 * A namespace is handy if multiple caches share the same storage medium (for example: shared memory caches
		 * such as memcache). It is up to the cache itself to interpret this.
		 *
		 * @param string $namespace
		 */
		public function setNamespace($namespace) { $this->setCacheVariable('namespace', $namespace); }
		
		/**
		 * Return the current namespace.
		 *
		 * @return bool
		 */
		public function getNamespace() { return $this->getCacheVariable('namespace'); }
		
		/**
		 * Load a stored value.
		 *
		 * @param string $key Key to load
		 * @return mixed
		 */
		function __get($key) { return $this->load($key); }
		
		/**
		 * Save a value against a key.
		 *
		 * @param string $key Key identifier.
		 * @param mixed $value The value.
		 * @return mixed
		 */
		function __set($key, $value) { return $this->save($key, $value); }
		
		/**
		 * Is a key set?
		 *
		 * @param string $key Load a key.
		 * @return bool
		 */
		function __isset($key) { return (bool)$this->load($key); }
		
		/**
		 * Unset a key.
		 *
		 * @param string $key Key to delete.
		 */
		function __unset($key) { return $this->delete($key); }

		/**
		 * Return the number of keys in the cache.
		 * @return int
		 */
		abstract public function size();

		/**
		 * Is the cache empty?
		 * @return bool
		 */
		public function isEmpty()
		{
		    if ($this->size > 0)
			return true;

		    return false;
		}
		
		/**
		 * Cache data.
		 *
		 * @param string $key Key to save data against.
		 * @param string $data Data to save.
		 * @return bool
		 */
		abstract public function save($key, $data);
		
		/**
		 * Cache data, but only if it doesn't already exist.
		 *  
		 * Implemented simply here, if you extend this class and your caching engine provides a 
		 * better way then override this accordingly.
		 *
		 * @param string $key Key.
		 * @param string $data The data.
		 * @return bool
		 */
		public function add($key, $data)
		{
			if (!isset($this[$key])) 
				return $this->save($key, $data);
				
			return false;
		}
		
		/**
		 * Load data from a cache.
		 *
		 * @param string $key Key
		 * @param int $offset Offset
		 * @param int $limit Limit
		 * @return mixed The stored data or false.
		 */
		abstract public function load($key, $offset = 0, $limit = null);
		
		/**
		 * Delete a cached item.
		 *
		 * @param string $key The key
		 * @return bool
		 */
		abstract public function delete($key);
		
		/**
		 * Purge a cache.
		 */
		abstract public function clear();

		
		// ARRAY ACCESS INTERFACE //////////////////////////////////////////////////////////
		function offsetSet($key, $value) { $this->save($key, $value); } 
 		
 		function offsetGet($key) { return $this->load($key); } 
 		
 		function offsetUnset($key) 
 		{
   			if ( isset($this->key) ) {
     			unset($this->key);
   			}
 		} 
 		
 		function offsetExists($offset) { return isset($this->$offset); } 
	}
	
	/**
	 * File cache.
	 *
	 * Cache data to the disk.
	 */
	class FileCache extends Cache
	{
		/**
		 * Configure the cache.
		 *
		 * @param string $cache_path The cache path, if none specified then the default temp directory is used.
		 * @param string $namespace Optional namespace.
		 */
		function __construct($cache_path = null, $namespace = null)
		{
			global $CONFIG;
			
			if (!$cache_path)
				$cache_path = $CONFIG->temp;
				 
			$this->setCacheVariable('cache_path', $cache_path);
			
			if ($namespace)
				$this->setCacheVariable('namespace', $namespace);
		}
		
		
		/**
		 * Create a file handle for a cache.
		 *
		 * @param string $filename The filename
		 * @param string $rw Write code.
		 */
		protected function createFile($filename, $rw = "rb")
		{
			// Create full path
			$namespace = $this->getCacheVariable('namespace');
			if ($namespace)
				$namespace = trim($namespace, '/') . '/';
			
			$path = $this->getCacheVariable('cache_path') . $namespace;
			
			// TODO: sanitise path
			
			if (!is_dir($path))
				mkdir($path, 0700, true);
			
			// Open the file
			if ((!file_exists($path . $filename)) && ($rw=="rb")) 
				return false;
			
			return fopen($path . $filename, $rw);
		}
		
		/**
		 * Save a key and data.
		 *
		 * @param string $key The key.
		 * @param string $data The data to save.
		 * @return boolean
		 */
		public function save($key, $data)
		{
			$f = $this->createFile($key, "wb");
			
			if ($f)
			{
				$result = fwrite($f, $data);
				fclose($f);
				
				return $result;
			}
			
			return false;
		}
		
		/**
		 * Load a key from a file.
		 *
		 * @param string $key The key.
		 * @param int $offset Offset
		 * @param int $limit Limit
		 * @return string
		 */
		public function load($key, $offset = 0, $limit = null)
		{
			$f = $this->createFile($key);
			if ($f) 
			{
				if (!$limit) 
					$limit = -1;
				
				$data = stream_get_contents($f, $limit, $offset);
				
				fclose($f);
				
				return $data;
			}
			
			return false;
		}
		
		/**
		 * Invalidate a given key.
		 *
		 * @param string $key
		 * @return bool
		 */
		public function delete($key)
		{
			$namespace = $this->getCacheVariable('namespace');
			if ($namespace)
				$namespace = trim($namespace, '/') . '/';
				
			$path = $this->getCacheVariable("cache_path");
			
			return unlink($path.$namespace.$key);
		}
		
		/**
		 * Clear all cache values in a given namespace.
		 */
		public function clear()
		{
			$namespace = $this->getCacheVariable('namespace');
			if (!$namespace) return false;
			
			$namespace = trim($namespace, '/') . '/';	
			$path = $this->getCacheVariable("cache_path");
			
			if ($handle = opendir($path.$namespace))
			{
				while ($file = readdir($handle)) 
				{
					if (!is_dir($path.$namespace.$file))
						unlink($path.$namespace.$file);
				}
			}
		}

		/**
		 * Count entries in cache.
		 * @todo: Make this more efficient!
		 */
		public function size()
		{
		    $size = 0;

		    $namespace = $this->getCacheVariable('namespace');
		    if (!$namespace) return false;

		    $namespace = trim($namespace, '/') . '/';
		    $path = $this->getCacheVariable("cache_path");

		    if ($handle = opendir($path.$namespace))
		    {
			while ($file = readdir($handle))
			{
				if (!is_dir($path.$namespace.$file))
				    $size++;
			}
		    }

		    return $size;
		}
		
	}
	
	/**
	 * Simple variable cache.
	 * 
	 * A cache which uses a simple variable per thread in order to temporarily store
	 * variables in memory for the duration of the script execution.
	 *
	 * This is handy for caching things like database queries etc.
	 */
	class SimpleVariableCache extends Cache
	{
		/**
		 * The cache.
		 */
		private $cache;
		
		/**
		 * Create the variable cache.
		 * 
		 * This function creates a variable cache in a static variable in memory, 
		 * optionally with a given namespace (to avoid overlap).
		 *
		 * @param string $namespace The namespace for this cache to write to.
		 */
		function __construct($namespace = 'default')
		{	
			$this->setCacheVariable('namespace', $namespace);
			$this->clear();
		}
		
		/**
		 * Save a key.
		 */
		public function save($key, $data) 
		{
			$namespace = $this->getCacheVariable('namespace');
			
			$this->cache[$namespace][$key] = $data;
			
			return true;
		}
		
		/**
		 * Load a key.
		 */
		public function load($key, $offset = 0, $limit = null)
		{
			$namespace = $this->getCacheVariable('namespace');
			
			if (isset($this->cache[$namespace][$key]))
				return $this->cache[$namespace][$key];
				
			return false;
		}
		
		/**
		 * Delete a key.
		 */
		public function delete($key) 
		{
			$namespace = $this->getCacheVariable('namespace');
			
			unset($this->cache[$namespace][$key]);
			
			return true;
		}
		
		/**
		 * Clear the cache.
		 */
		public function clear()
		{
			$namespace = $this->getCacheVariable('namespace');
			
			if (!isset($this->cache))
				$this->cache = array();
				
			$this->cache[$namespace] = array();
		}

		/**
		 * Return size of cache.
		 */
		public function size() { return count($this->cache); }
	
	}
	
	/**
	 * Cache factory.
	 * 
	 * This provides factories for various caching aspects of the system.
	 */
	function cache_factory($class, $hook, $parameters, $return_value)
	{
		global $CONFIG;
		
		// If caching is disabled, don't bother going any further
		if ((isset($CONFIG->disable_cache)) && ($CONFIG->disable_cache))
			return false;
			
		// If we already have a cache for this, don't create a new one.
		if ($return_value) 
			return $return_value;
		
		// Otherwise we see if we can create a cache
		switch ($hook)
		{
			case 'cache' : // If we ask for a 'cache' we just get this
			case 'cache:filecache' : // Ask for a file cache
			case 'cache:viewpaths' : // Default factory for the viewpaths cache
			case 'cache:languages' : // Default cache for languages and translations. 
				$cache = new FileCache();
				$cache->setNamespace($hook);
				
				return $cache;
			case 'cache:simplevariable' :
			case 'cache:database' : // Default for database storage
				return new SimpleVariableCache('database');
		}	
	}
	
	/**
	 * Initialise the cache subsystem, registering factories.
	 */
	function cache_boot()
	{
		register_factory('cache:languages', 'cache_factory');
		register_factory('cache:viewpaths', 'cache_factory');
		register_factory('cache:database', 'cache_factory');
		register_factory('cache', 'cache_factory');
		register_factory('cache:filecache', 'cache_factory');
		register_factory('cache:simplevariable', 'cache_factory');
	}
	
	register_event('system', 'boot', 'cache_boot');
