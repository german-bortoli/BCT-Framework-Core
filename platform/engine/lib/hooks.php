<?php
	/**
	 * @file
	 * Hook functions.
	 * 
	 * Hooks work in a similar way to events, except they allow for an arbitrary return value.
	 * 
	 * @package core
	 * @license The MIT License (see LICENCE.txt), other licenses available.
	 * @author Marcus Povey <marcus@marcus-povey.co.uk>
	 * @copyright Marcus Povey 2009-2011
	 * @link http://platform.barcamptransparency.org/
	 */

	/**
	 * Register a event hook listener.
	 *
	 * A hook listener can be set to listen for a specific hook, or a collection of hooks using wildcards.
	 *
	 * Namespaces are used to define the namespace of the hook, typically this is the object hierachy if dealing
	 * with a BCTObject.
	 *
	 * The function being registered must have the following prototype:
	 *
	 * \code
	 *	function foo($namespace, $hook, $parameters, $return_value)
	 *	{
	 *	    // Handle your code, modify $return_value as necessary or return nothing if unmodified.
	 *	}
	 * \endcode
	 *
	 * @param string $namespace The hook class
	 * @param string $hook The hook
	 * @param string $handler Handler function
	 * @param int $priority Determine the sequence of hook event. 
	 * @return bool
	 * @see register_event()
	 */
	function register_hook($namespace, $hook, $handler, $priority = 500)
	{
		global $CONFIG;
		
		// Turn highchair/elgg style wildcards into correct ones.
		$namespace = str_replace('*','.*',$namespace);
		$namespace = str_replace('/','\/',$namespace);
		$namespace = '/^'.str_replace('all','.*',$namespace).'$/';
		
		$hook = str_replace('*','.*',$hook);
		$hook = str_replace('/','\/',$hook);
		$hook = '/^'.str_replace('all','.*',$hook).'$/';
		
		if (!isset($CONFIG->_HOOKS))
			$CONFIG->_HOOKS = array();
			
		if (!isset($CONFIG->_HOOKS[$namespace]))
			$CONFIG->_HOOKS[$namespace] = array();
			
		if (!isset($CONFIG->_HOOKS[$namespace][$hook]))
			$CONFIG->_HOOKS[$namespace][$hook] = array();
			
		while (isset($CONFIG->_HOOKS[$namespace][$hook][$priority]))
			$priority ++;
			
		$CONFIG->_HOOKS[$namespace][$hook][$priority] = $handler;
		
		ksort($CONFIG->_HOOKS[$namespace][$hook]);
		
		return true;
	}
	
	/**
	 * Trigger a hook.
	 *
	 * @param string $namespace The hook class.
	 * @param string $hook The hook.
	 * @param array $parameters Associated array of parameters.
	 * @param mixed $return_value The default return value.
	 * @return mixed
	 */
	function trigger_hook($namespace, $hook, array $parameters = NULL, $return_value = NULL)
	{
		global $CONFIG;
		
		$merged = array();
		
		// Get events we're triggering
		if ((!isset($CONFIG->_HOOKS)) || (!$CONFIG->_HOOKS)) return $return_value;
		
		foreach ($CONFIG->_HOOKS as $namespace_key => $hook_list)
		{
			// Does the namespace being triggered match a registered namespace?
			if (preg_match($namespace_key, $namespace))
			{
				foreach ($CONFIG->_HOOKS[$namespace_key] as $hook_key => $function_list)
				{
					// Does the hook being triggered match a registered event
					if (preg_match($hook_key, $hook))
					{
						// Now add and prioritise events
						foreach ($function_list as $priority => $function)
						{
							// Adjust priority to free slot
							while (isset($merged[$priority])) $priority++;
							$merged[$priority] = $function;	
						}
					}
				}
			}
		}
		
		// Now sort and execute 
		ksort($merged);
		foreach ($merged as $function)
		{
			$tmp = $function($namespace, $hook, $parameters, $return_value);

			if ($tmp!==NULL)
				$return_value = $tmp;
		}
		
		return $return_value;
	}
	
	/**
	 * Returns true if a specific hook has at least one handler.
	 * @param string $namespace The event namespace
	 * @param string $hook The event
	 * @return bool
	 */
	function is_hook_handled($namespace, $hook)
	{
	    global $CONFIG;

	    // Turn highchair/elgg style wildcards into correct ones.
	    $namespace = str_replace('*','.*',$namespace);
	    $namespace = str_replace('/','\/',$namespace);
	    $namespace = '/^'.str_replace('all','.*',$namespace).'$/';

	    $hook = str_replace('*','.*',$hook);
	    $hook = str_replace('/','\/',$hook);
	    $hook = '/^'.str_replace('all','.*',$hook).'$/';

	    return isset($CONFIG->_HOOKS[$namespace][$hook]);
	}