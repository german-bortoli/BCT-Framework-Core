<?php
	/**
	 * @file
	 * Database engine.
	 * 
	 * Database and datastore engine functions, defining a database interface
	 * and providing a default mysql engine. Other plugins may decide to implement
	 * postgres or nosql interfaces.
	 * 
	 * @package core
	 * @license The MIT License (see LICENCE.txt), other licenses available.
	 * @author Marcus Povey <marcus@marcus-povey.co.uk>
	 * @copyright Marcus Povey 2009-2011
	 * @link http://platform.barcamptransparency.org/
	 */

	/**
	 * Database engine.
	 * 
	 * This class defines a database engine, allowing plugins 
	 * to define their own database engines in an extendable way.
	 */
	abstract class DatabaseEngine
	{
		/**
		 * Links to databases.
		 */
		protected $link = array();
		
		/**
		 * Database query cache.
		 */
		private $query_cache;
		
		/**
		 * Split read/writes.
		 */
		private $splitreadwrites = false;
		
		/**
		 * Database connection parameters.
		 */
		protected $configuration = array();

		/**
		 * Total number of queries executed.
		 */
		protected $query_count = 0;

		/**
		 * Number of queries which were cached.
		 */
		protected $query_count_inc_cached = 0;

		/**
		 * Details of queries being executed.
		 */
		protected $query_details = array();

		/**
		 * A nice human readable transcript of notable events (cache resets etc).
		 */
		protected $query_human_transcript = array();
		
		/**
		 * Build a query.
		 * 
		 * This abstract class should be implemented by any database engine and should turn
		 * the parameter array into a valid SQL query.
		 *
		 * @param array $query_params Associated array of parameters:
		 * 			'type' => 'select'|'update'|'delete'|'insert'
		 * 			'distinct' => bool (select only)
		 * 			'select_func' => Function to apply to select_expr, valid commands are 
		 * 							 COUNT, AVG, MIN, MAX, SUM - returns result as select_func_result
		 * 			'select_expr' => selecting from what (select only)
		 * 			'table_col' => Table references
		 * 			'set_values' => Set field values in an update query
		 * 			'joins' => Joins
		 * 			'where => List of wheres, if this is an array the joining operator will be AND, if you want 
		 * 					  to use another operator please add it as an individual query.
		 * 			'groupby' => What field to having by
		 * 			'group' => Ascend/decend
		 * 			'having' => Having the following...
		 * 			'orderby' => What field to order by
		 * 			'order' => Ascend/decend
		 * 			'limit' => Limit
		 * 			'offset' => Offset
		 * @return query string|false
		 */
		abstract protected function buildQuery(array $query_params);

		/**
		 * Execute a raw query.
		 *
		 * @param string $query Query.
		 * @param string $linkname Link type, default 'readwrite'. 
		 */
		abstract protected function executeQuery($query, $linkname = 'readwrite');
		
		/**
		 * Create a database connection.
		 * 
		 * @param string $linkname Default "readwrite"; you can change this to set up additional global 
		 * 							 database links, eg "read" and "write".
		 */
		abstract public function establishLink($linkname = 'readwrite');
		
	
		
		/**
		 * Sanitise a string using the database key.
		 *
		 * @param string $string The string to sanitise.
		 */
		abstract public function sanitise($string);
		
		/**
		 * Return a query with multiple return results.
		 *
		 * @param array $query_params Array of query parameters.
		 * @param string $callback Optional callback function.
		 * @return array|false
		 */
		abstract public function select(array $query_params, $callback = "");
		
		/**
		 * Get a single row from database.
		 *
		 * @param array $query_params Array of query parameters.
		 * @param string $callback Optional callback function.
		 * @return stdClass|false
		 */
		abstract public function selectrow(array $query_params, $callback = "");
		
		/**
		 * Insert data into a database according to given parameters.
		 *
		 * @param array $query_params Array of query parameters.
		 * @return bool
		 */
		abstract public function insert(array $query_params);
		
		/**
		 * Update delete data.
		 *
		 * @param array $query_params Array of query parameters.
		 * @return bool
		 */
		abstract public function update(array $query_params);
		
		/**
		 * Delete data from the database, returning the number of affected rows.
		 *
		 * @param array $query_params Array of query parameters.
		 * @return int
		 */
		abstract public function delete(array $query_params);
		
		/**
		 * Retrieve a connection link.
		 *
		 * Gets a database link, or attempt to establish one if one isn't present. This
		 * lets the framework have lazy database connections - i.e only establishing
		 * a connection when one is actually necessary.
		 *
		 * @param string $linkname The link name
		 * @return resource|false 
		 */
		public function getLink($linkname = 'readwrite')
		{
			if (isset($this->link[$linkname])) 
				return $this->link[$linkname];
				
			if (isset($this->link['readwrite'])) 
				return $this->link['readwrite'];
				
			if ($this->setupConnections())
				return $this->getLink($linkname);
		
			return false;
		}
		
		/**
		 * Establish the database connections based.
		 */
		protected function setupConnections()
		{
			if ($this->splitreadwrites)
				return $this->establishLink('read') && $this->establishLink('write');
			else
				return $this->establishLink('readwrite');
			
			$this->query_cache = factory('cache:database'); // Create query cache using a factory
		}
		
		/**
		 * Configure the engine based on passed parameters.
		 *
		 * @param array $parameters The parameter array of stdClass objects in the format
		 * 		$parameters['linktype']->dbname etc
		 * 
		 */
		protected function configureEngine(array $parameters)
		{
			$this->configuration = $parameters;
			
			if (count($this->configuration)>1)
				$this->splitreadwrites = true;
				
			return true;
		}
		
		/**
		 * Retrieve a query from cache.
		 *
		 * @param string $query The query
		 */
		protected function cacheGetQuery($query)
		{
			if ($this->query_cache)
				return $this->query_cache[$query];
		}
	
		/**
		 * Cache a query.
		 *
		 * @param string $query The raw query.
		 * @param array $result The query result.
		 * @return bool
		 */
		protected function cache($query, $result)
		{
			if ($this->query_cache) 
				$this->query_cache[$query] = $result;
				
			return true;
		}
	
		/**
		 * Clear a query cache.
		 */
		protected function cacheClear()
		{
			if ($this->query_cache)
				$this->query_cache->clear();
		}

		/**
		 * Returns some statistics about the engine.
		 *
		 * Currently defined:
		 *	'total_queries' - Total queries actually getting executed (i.e. not cached)
		 *	'total_queries_inc_cached' - Total number of database calls, even those which are cached.
		 *	'query_details' - What queries were called, and how many times.
		 *	'transcript' - A transcript of the database
		 */
		public function getStatistics()
		{
		    return array(
			'total_queries' => $this->query_count,
			'total_queries_inc_cached' => $this->query_count_inc_cached,
			'query_details' => $this->query_details,
			'transcript' => $this->query_human_transcript
		    );
		}
	}

	/**
	 * MySQL database engine.
	 *
	 * This class provides a database engine using the mysql subsystem.
	 */
	class MysqlDatabaseEngine extends DatabaseEngine
	{
		/**
		 * Construct a mysql database engine.
		 *
		 * @param array $parameters
		 */
		function __construct(array $parameters)
		{
			$this->configureEngine($parameters);
		}
		
		/**
		 * Build a MYSQL query.
		 *
		 * @param array $query_params Query parameters as passed by database functions in data.php
		 */
		protected function buildQuery(array $query_params)
		{
			global $CONFIG;
		
			// Query type
			$type_matrix = array(
				'select' => 'SELECT',
				'update' => 'UPDATE',
				'delete' => 'DELETE',
				'insert' => 'INSERT',
			);
		
			$type = $query_params['type'];
			$query = "{$type_matrix[$type]} ";
		
			// What (select)
			if (($type == 'select') && ($query_params['select_expr']))
			{
				// Select function
				$select_func_matrix = array (
					'avg' => 'AVG',
					'count' => 'COUNT',
					'min' => 'MIN',
					'max' => 'MAX',
					'sum' => 'SUM'
				);
				
				$select_func_start = "";
				$select_func_end = "";
				if ($query_params['select_func'])
				{
					$select_func_start = $select_func_matrix[strtolower($query_params['select_func'])] . '(';
					$select_func_end = ') as select_func_result';
				}
				
				// Distinct?
				$query .= $select_func_start;
				if ($query_params['distinct'])
					$query .= "DISTINCT ";
				
				// Select expression
				$select_expr = $query_params['select_expr'];
				if (!is_array($select_expr))
					$select_expr = array($select_expr);
	
				$query .= implode(',', $select_expr) . "$select_func_end ";
			}
		
			// From/Into
			$table_matrix = array(
				'select' => 'FROM',
				'update' => '',
				'delete' => 'FROM',
				'insert' => 'INTO'
			);
		
			if ($query_params['table_col']) {
				$table_expr = $query_params['table_col'];
				if (!is_array($table_expr))
					$table_expr = array($table_expr);
				
				$query .= "{$table_matrix[$type]} ". implode(',', $table_expr) . " ";
			}
				
			// Set 
			if ((($type == 'update') || ($type == 'insert') ) && ($query_params['set_values']))
			{
				$set_expr = $query_params['set_values'];
				if (!is_array($set_expr))
					$set_expr = array($set_expr);
				
				$query .= "SET " . implode(',', $set_expr) . " ";
			}
				
			// Joins
			if (($type == 'select') && ($query_params['joins']))
			{
				$joins_expr = $query_params['joins'];
				if (!is_array($joins_expr))
					$joins_expr = array($joins_expr);
					
				$query .= "JOIN " . implode(' JOIN ', $joins_expr) . " ";
			}
				
			// Where
			if ($query_params['where']) 
			{
				$where_expr = $query_params['where'];
				if (!is_array($where_expr))
					$where_expr = array($where_expr);
					
				$query .= "WHERE " . implode(' AND ', $where_expr) . " ";
			}
				
			// Group
			if ($query_params['group'])
			{
				$group_matrix = array (
					'asc' => 'ASC',
					'desc' => 'DESC',
				);
				
				$group_tables = $query_params['groupby'];
				$group = strtolower($query_params['group']);
				if (!is_array($group_tables))
					$group_tables = array($group_tables);
				
				$query .= "GROUP BY " . implode(',', $group_tables) . " {$group_matrix[$group]} ";
			}
			
			// Having
			if ($query_params['having'])
			{
				$having_expr = $query_params['having'];
				if (!is_array($having_expr))
					$having_expr = array($having_expr);
					
				$query .= "HAVING " . implode(' AND ', $having_expr) . " ";
			}
					
			// Order by
			if ($query_params['orderby'])
			{
				$order_matrix = array (
					'asc' => 'ASC',
					'desc' => 'DESC',
				);
				
				$order_tables = $query_params['orderby'];
				$order = strtolower($query_params['order']);
				if (!is_array($order_tables))
					$order_tables = array($order_tables);
				
				$query .= "ORDER BY " . implode(',', $order_tables) . " {$order_matrix[$order]} ";
			}
				
			// Limit & Offset
			$offset = null;
			$limit = null;
			if (isset($query_params['limit']))
				$limit = (int)$query_params['limit'];
			if (isset($query_params['offset']))
				$offset = (int)$query_params['offset'];
		
			if (($limit) || ($offset))
			{
				$query .= "LIMIT ";
				if ($offset)
					$query .= "$offset, ";
				if ($limit)
					$query .= "$limit ";
			} 
				
			if ($CONFIG->debug)
				log_echo("Database query constructed as : $query", 'DEBUG'); 

			$this->query_count_inc_cached ++;

			return $query;
		}

		/**
		 * Execute a raw query.
		 *
		 * @param string $query Query cache.
		 * @param string $linkname Link type, default 'readwrite'. 
		 */
		protected function executeQuery($query, $linkname = 'readwrite')
		{
			global $CONFIG;

			$this->query_count++;
			if ($CONFIG->debug) $this->query_details[$query]++;

			$dblink = $this->getLink($linkname);
			if (!$dblink)
				return false;
			
			$result = mysql_query($query, $dblink);
			
			$this->cache($query, $result);
		 
			if (mysql_errno($dblink))
				throw new DatabaseException(mysql_error($dblink) . " QUERY: " . $query);
					
			return $result;
		}
		
		/**
		 * Create a database connection.
		 * 
		 * @param string $dblinkname Default "readwrite"; you can change this to set up additional global 
		 * 							 database links, eg "read" and "write".
		 */
		public function establishLink($linkname = 'readwrite')
		{
			global $CONFIG;
			
			if (isset($this->configuration[$linkname]))
			{
				if (is_array($this->configuration[$linkname]))
				{
					$index = rand(0,sizeof($CONFIG->db[$linkname]));
					$dbhost = $this->configuration[$linkname][$index]->dbhost;
					$dbuser = $this->configuration[$linkname][$index]->dbuser;
					$dbpass = $this->configuration[$linkname][$index]->dbpass;
					$dbname = $this->configuration[$linkname][$index]->dbname;
				}
				else
				{
					$dbhost = $this->configuration[$linkname]->dbhost;
					$dbuser = $this->configuration[$linkname]->dbuser;
					$dbpass = $this->configuration[$linkname]->dbpass;
					$dbname = $this->configuration[$linkname]->dbname;
				}
			}
			else
				return false;
			
			
			if (!$this->link[$linkname] = mysql_connect($dbhost, $dbuser, $dbpass, true))
				throw new DatabaseException(sprintf(_echo('database:exception:wrongcredentials'), $dbuser, $dbhost, $CONFIG->debug ? $dbpass : "****")); 
			if (!mysql_select_db($dbname, $this->link[$linkname]))
				throw new DatabaseException(sprintf(_echo('database:exception:connectionfail'), $dbname));  
		
			mysql_query("SET NAMES utf8", $this->link[$linkname]);
			
			return true;
		}
		
		/**
		 * Sanitise a string using the database key.
		 *
		 * @param string $string The string to sanitise.
		 */
		public function sanitise($string)
		{
			$dblink = $this->getLink('read');
			if (!$dblink)
				return false;
				
			return mysql_real_escape_string(trim($string), $dblink);
		}
		
		/**
		 * Return a query with multiple return results.
		 *
		 * @param array $query_params Array of query parameters.
		 * @param $callback Optional callback function.
		 * @return array|false
		 */
		public function select(array $query_params, $callback = "")
		{
			// Construct query
			$query_params['type'] = 'select';
			$query = $this->buildQuery($query_params);
		
			// See if it's cached
			$cached_query = $this->cacheGetQuery($query);
			if ($cached_query) {
				if ($cached_query === -1)
					return array();
				return $cached_query;
			}
		
			if ($query)
			{
				if ($result = $this->executeQuery("$query", 'read'))
				{
					$resultarray = array();
					
					while ($row = mysql_fetch_object($result)) 
					{
						if (!empty($callback) && is_callable($callback)) 
							$row = $callback($row);
							
						if ($row) $resultarray[] = $row;
					}
				}
				else
					$resultarray = -1;
			
				// Return result array
				if (empty($resultarray)) 
				{
					if ((isset($CONFIG->debug)) && ($CONFIG->debug==true))
						log_echo("WARNING: DB query \"$query\" returned no results.", 'WARNING');
					 
					return false;
				}
				else
					$this->cache($query, $resultarray);
					
				if (is_array($resultarray))
					return $resultarray;
			}
				
			return false;	
		}
		
		/**
		 * Get a single row from database.
		 *
		 * @param array $query_params Array of query parameters.
		 * @param string $callback Optional callback function.
		 * @return stdClass|false
		 */
		public function selectrow(array $query_params, $callback = "")
		{
			// Construct query
			$query_params['type'] = 'select';
			$query = $this->buildQuery($query_params);
			
			// See if it's cached
			$cached_query = $this->cacheGetQuery($query);
			if ($cached_query) {
				if ($cached_query === -1)
					return array();
				return $cached_query;
			}
		
			if ($query)
			{
				// Execute
				if ($result = $this->executeQuery("$query", 'read'))
				{	
					if ($row = mysql_fetch_object($result))
					{
						if (!empty($callback) && is_callable($callback)) 
							$row = $callback($row);
							
						if ((empty($row)) && (isset($CONFIG->debug)) && ($CONFIG->debug==true))
							log_echo("WARNING: DB query \"$query\" returned no results.", 'WARNING');
							
						// Cache result
						$this->cache($query, $row);
						
						return $row;	
					}
					
				}
					
				// If got here then no result is returned.
				$this->cache($query, -1);
			}
			
			return false;
		}
		
		/**
		 * Insert data into a database according to given parameters.
		 *
		 * @param array $query_params Array of query parameters.
		 * @return bool
		 */
		public function insert(array $query_params)
		{
			global $CONFIG;

			// Construct query
			$query_params['type'] = 'insert';
			$query = $this->buildQuery($query_params);
		
			if ($query)
			{
				// Invalidate query cache
				if (($CONFIG->debug) && ($cache_size = $this->query_cache->size()))
				    $this->query_human_transcript[] = "There were " . $size . " queries in the cache before it was reset by the query '$query'";

				$this->cacheClear();
		
				if ($this->executeQuery("$query", 'write'))
				{
					$insert_id = mysql_insert_id($this->getLink('write')); 
					if ($insert_id) 
						return $insert_id;
				}
			}
			
			return false;
		}
		
		/**
		 * Update delete data.
		 *
		 * @param array $query_params Array of query parameters.
		 * @return bool
		 */
		public function update(array $query_params)
		{
			// Construct query
			$query_params['type'] = 'update';
			$query = $this->buildQuery($query_params);
		
			if ($query)
			{
				// Invalidate query cache
				if (($CONFIG->debug) && ($cache_size = $this->query_cache->size()))
				    $this->query_human_transcript[] = "There were " . $size . " queries in the cache before it was reset by the query '$query'";

				$this->cacheClear();
		
				if ($this->executeQuery("$query", 'write'))
					return true;
			}
				
			return false;
		}
		
		/**
		 * Delete data from the database, returning the number of affected rows.
		 *
		 * @param array $query_params Array of query parameters.
		 * @return int
		 */
		public function delete(array $query_params)
		{
			$query_params['type'] = 'delete';
			$query = $this->buildQuery($query_params);
		
			if ($query)
			{
				// Invalidate query cache
				if (($CONFIG->debug) && ($cache_size = $this->query_cache->size()))
				    $this->query_human_transcript[] = "There were " . $size . " queries in the cache before it was reset by the query '$query'";

				$this->cacheClear();
		
				if ($this->executeQuery("$query", 'write'))
					return mysql_affected_rows($this->getLink('write'));
					
			}
				
			return false;
		}
	}
	
	/**
	 * Get a database engine.
	 * 
	 * This function will create a database engine if one hasn't been created by calling the appropriate
	 * factory.
	 */
	function db_get_engine() 
	{
		global $CONFIG;
		
		// Already created database engine?
		if (isset($CONFIG->_DATABASEENGINE))
			return $CONFIG->_DATABASEENGINE;

		// Parameters for factory
		$parameters = array();
		if (isset($CONFIG->db))
			$parameters = $CONFIG->db;
		else
		{
			$parameters['readwrite'] = new stdClass;
			$parameters['readwrite']->dbhost = $CONFIG->dbhost;
			$parameters['readwrite']->dbname = $CONFIG->dbname;
			$parameters['readwrite']->dbuser = $CONFIG->dbuser;
			$parameters['readwrite']->dbpass = $CONFIG->dbpass;
		}
			
		// Has the database engine been defined?	
		if ($engine = factory("database:engine:{$CONFIG->dbengine}", $parameters))
		{
			$CONFIG->_DATABASEENGINE = $engine;
			return $engine;
		}
		
		// Default engine?	
		if ($engine = factory("database:engine:default", $parameters))
		{
			$CONFIG->_DATABASEENGINE = $engine;
			return $engine;
		}
		
		return false;
	}

	/**
	 * Return a query with multiple return results.
	 *
	 * @param array $query_params Array of query parameters.
	 * @param $callback Optional callback function.
	 * @return array|false
	 */
	function db_getdata(array $query_params, $callback = "")
	{
		$engine = db_get_engine();

		if ($engine)
			return $engine->select($query_params, $callback);
			
		return false;
	}
	
	/**
	 * Get a single row from database.
	 *
	 * @param array $query_params Array of query parameters.
	 * @param string $callback Optional callback function.
	 * @return stdClass|false
	 */
	function db_getdata_row(array $query_params, $callback = "")
	{
		$engine = db_get_engine();

		if ($engine)
			return $engine->selectrow($query_params, $callback);
			
		return false;
	}
	
	/**
	 * Insert data into a database according to given parameters.
	 *
	 * @param array $query_params Array of query parameters.
	 * @return bool
	 */
	function db_insert_data(array $query_params)
	{
		$engine = db_get_engine();

		if ($engine)
			return $engine->insert($query_params);
			
		return false;
	}
	
	/**
	 * Update delete data.
	 *
	 * @param array $query_params Array of query parameters.
	 * @return bool
	 */
	function db_update_data(array $query_params)
	{
		$engine = db_get_engine();

		if ($engine)
			return $engine->update($query_params);
			
		return false;
	}
	
	/**
	 * Delete data from the database, returning the number of affected rows.
	 *
	 * @param array $query_params Array of query parameters.
	 * @return int|false
	 */
	function db_delete_data(array $query_params)
	{
		$engine = db_get_engine();

		if ($engine)
			return $engine->delete($query_params);
			
		return false;
	}
	
	/**
	 * Sanitise a string for database use
	 *
	 * @param string $string The string to sanitise
	 * @return string Sanitised string
	 */
	function sanitise_string($string) 
	{
		if (is_array($string))
			return sanitise_string_array($string);
			
		$engine = db_get_engine();
		
		if ($engine)
			return $engine->sanitise($string);
		
		return strtr($string, array(
		  "\x00" => '\x00',
		  "\n" => '\n', 
		  "\r" => '\r', 
		  '\\' => '\\\\',
		  "'" => "\'", 
		  '"' => '\"', 
		  "\x1a" => '\x1a'
		));
	}
	
	/**
	 * Sanitise an array of strings.
	 *
	 * @param array $strings String array
	 */
	function sanitise_string_array(array $strings)
	{
		foreach ($strings as $k => $v)
			$strings[$k] = sanitise_string($v);
		
		return $strings;
	}
	
	/**
	 * Default database factory.
	 * 
	 * Provides a factory for the default (mysql) database engine.
	 */
	function database_factory($class, $hook, $parameters, $return_value)
	{
		switch ($hook)
		{
			// Default database engine creation
			case 'database:engine' : 
			case 'database:engine:default' :
			case 'database:engine:mysql' :
			default :
				return new MysqlDatabaseEngine($parameters);
			break;
		}	
	}

	/**
	 * Initialise the database engine subsystem.
	 */
	function database_boot()
	{
		register_factory('database:engine', 'database_factory');
		register_factory('database:engine:default', 'database_factory');
		register_factory('database:engine:mysql', 'database_factory');
	}
	
	register_event('system', 'boot', 'database_boot');
