<?php
	/**
	 * @file
	 * Plugin library.
	 * 
	 * This file contains functions for handling plugins and generally extending
	 * the framework.
	 *
	 * In the BCT framework, 90% of whatever you use will be provided by a plugin of some sort.
	 *
	 * In order to activate a plugin you need to configure the array $CONFIG->enabled_plugins in settings.php,
	 * this array also goes some way to controlling the boot order by providing the inclusion order of
	 * the plugin's start.php (certain aspects of the boot order can also be specified by specifying a
	 * priority to the boot event, but this method is not generally advised).
	 * 
	 * @package core
	 * @license The MIT License (see LICENCE.txt), other licenses available.
	 * @author Marcus Povey <marcus@marcus-povey.co.uk>
	 * @copyright Marcus Povey 2009-2011
	 * @link http://platform.barcamptransparency.org/
	 */

	/**
	 * Load all plugins in a given path.
	 *
	 * @param array $enabled_plugins An array of enabled plugins in the $path.
	 * @param string $path The path including a trailing slash.
	 */
	function plugins_load($enabled_plugins , $path)
	{
		global $CONFIG;
		
		if (!empty($path)) {

			// Attempt retrieval of cache
			$viewpath_cache = factory('cache:viewpaths');
			$viewpath_cached_view_paths = false;
			if ($viewpath_cache) {
				$viewpath_cached_view_paths = $viewpath_cache->load('viewpaths');
				$CONFIG->views = unserialize($viewpath_cached_view_paths);
			}
			
			// Load plugins
		
			if (($enabled_plugins) && (is_array($enabled_plugins)))
			{
				foreach($enabled_plugins as $mod) 
				{
					if (file_exists($path . $mod)) {
						if (!include($path . $mod . "/start.php"))
							throw new PluginException(sprintf(_echo('plugins:exception:misconfigured')), $mod); 
							
						// If we haven't already loaded cached views, construct them now
						if (!$viewpath_cached_view_paths) {
							if (is_dir($path . $mod . "/views")) 
							{
								if ($handle = opendir($CONFIG->plugins . $mod . "/views"))
								{
									while ($viewtype = readdir($handle)) 
									{
										if (!in_array($viewtype, array('.','..','.svn','CVS')))
										{
											if  (is_dir($path . $mod . "/views/" . $viewtype)) 
												load_output_views(
													'',
													$path . $mod . '/views/' . $viewtype, 
													$path . $mod . '/views/', 
													$viewtype
												);
										}
									}
								}
							}
						}
							
						// Load languages
						if (is_dir($CONFIG->plugins . $mod . "/languages")) 
							load_languages($CONFIG->plugins . $mod . "/languages/");
					}		
				}
			}
			
			// Cache results
			if ((!$viewpath_cached_view_paths) && ($viewpath_cache)) 
				$viewpath_cache->save('viewpaths', serialize($CONFIG->views));
		
			
			return true;
		}
		
		return false;
	}
	
	/**
	 * Test to see if a plugin of a given name is enabled or not.
	 *
	 * @param string $plugin The plugin name
	 * @return bool
	 */
	function is_plugin_enabled($plugin)
	{
		global $CONFIG;
		
		return in_array($plugin, $CONFIG->enabled_plugins);
	}
	
	/**
	 * Safely call a plugin function.
	 * 
	 * This function will call a function that exists in a plugin safely, if the plugin is installed 
	 * and enabled then the function will be called with the provided parameters, otherwise it will throw 
	 * a FunctionNotFoundException.
	 *
	 * @param unknown_type $function Function name
	 * @param ... Function parameters.
	 * @throws FunctionNotFoundException If a function is not callable, catch this to avoid critical errors.
	 */
	function call_plugin_function($function)
	{
		if (is_callable($function))
		{
			$argstring='';
			
			$numargs = func_num_args(); 
			
			if ($numargs > 1) {
				$arg_list = func_get_args();

				for ($x=1; $x<$numargs; $x++) {
					$argstring .= '$arg_list['.$x.']';
					if ($x != $numargs-1) $argstring .= ',';
					
				}
			} 
			
			return eval("return $function($argstring);");
		}
		else
			throw new FunctionNotFoundException(sprintf(_echo('plugins:exception:functionnotcallable'), $function)); 
		
		return false;
	}
	
	/**
	 * Safely instanciate a class provided by a plugin.
	 * 
	 * This function will call a constructor on a class. If the class exists a new instance is returned, 
	 * otherwise a trapable ClassNotFoundException is thrown.
	 * 
	 * This lets your plugins safely try and use classes provided by third party plugins and fail 
	 * gracefully if they are not present.
	 * 
	 * @throws ClassNotFoundException
	 */
	function create_plugin_class()
	{
		$argstring='';
			
		$numargs = func_num_args(); 
		$classname = func_get_arg(0); 
		
		if ($numargs > 1) { 
			$arg_list = func_get_args();

			for ($x=1; $x<$numargs; $x++) {
				$argstring .= '$arg_list['.$x.']';
				if ($x != $numargs-1) $argstring .= ',';
				
			}
		}
		
		if (class_exists($classname))
			return eval ("return new $classname($argstring);");
		else
			throw new ClassNotFoundException(sprintf(_echo('plugins:exception:classnotfound'), $classname)); 
			
		return false;
	}
	
	/**
	 * Enforce a plugin dependency. 
	 * 
	 * Use this in your init function to enforce plugin dependency.
	 *
	 * @param string $plugin_name Plugin name
	 */
	function plugin_depends($plugin_name)
	{
		if (!is_plugin_enabled($plugin_name))
			throw new PluginException(sprintf(_echo('plugins:exception:missingplugin'), $plugin_name)); 
			
		return true;
	}
	
	/**
	 * Require a specific version of the core.
	 * 
	 * Use this in your init function to enforce a plugin core dependency.
	 *
	 * @param int $core_version Machine version.
	 */
	function plugin_depends_core($core_version)
	{
		global $version;
		$core_version = (int) $core_version;
		
		if ($version < $core_version)
			throw new PluginException(sprintf(_echo('plugins:exception:platformdependency'), $core_version, $version)); 
			
		return true;
	}

