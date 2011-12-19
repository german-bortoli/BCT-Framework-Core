<?php
	/**
	 * @file
	 * Factories.
	 * 
	 * Factories provide a hook which allow plugins to override the creation of key objects within the 
	 * system. For example, a factory for "cache" could be overridden by a plugin to provide a memcache 
	 * object if the memcache plugin is installed.
	 * 
	 * @package core
	 * @license The MIT License (see LICENCE.txt), other licenses available.
	 * @author Marcus Povey <marcus@marcus-povey.co.uk>
	 * @copyright Marcus Povey 2009-2011
	 * @link http://platform.barcamptransparency.org/
	 */

	/**
	 * Register a factory with a handler.
	 *
	 * @param string $factory What you're manufacturing.
	 * @param function $handler The handling function (same prototype as a hook), but returning the appropriate object.
	 * @param int $priority Optional priority.
	 * @see register_hook();
	 */
	function register_factory($factory, $handler, $priority = 500)
	{
		if (is_callable($handler))
			return register_hook('factory', $factory, $handler);
		
		return false;
	}
	
	/**
	 * Create an object with a given factory.
	 *
	 * @param string $factory What you're manufacturing.
	 * @param array $parameters Parameters to pass.
	 * @return Object|false
	 */
	function factory($factory, array $parameters = null) 
	{
		return trigger_hook('factory', $factory, $parameters, false); 
	}
