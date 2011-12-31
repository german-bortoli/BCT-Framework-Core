<?php
	/**
	 * @file
	 * Events.
	 * 
	 * A library containing functions for triggering events. Events can be triggered by various 
	 * parts of the system and allow plugins to do something when they occur.
	 * 
	 * @package core
	 * @license The MIT License (see LICENCE.txt), other licenses available.
	 * @author Marcus Povey <marcus@marcus-povey.co.uk>
	 * @copyright Marcus Povey 2009-2012
	 * @link http://platform.barcamptransparency.org/
	 */


	/**
	 * Register an event listener with the system.
	 *
	 * An event listener can be set to listen for a specific event, or a collection of events using wildcards.
	 *
	 * Namespaces are used to define the namespace of the event, typically this is the object hierachy if dealing
	 * with a BCTObject.
	 *
	 * The function being registered must have the following prototype:
	 *
	 * \code
	 *	function foo($namespace, $event, $parameters)
	 *	{
	 *	    // Your code, return boolean false will stop any further events being processed
	 *	}
	 * \endcode
	 *
	 * @section Example
	 *
	 * This example registers a couple of hooks for handling a number of different events:
	 *
	 * \code
	 *	// Listen to the saved event of a blog post (but not any of its subclasses)
	 *	register_event('obj:blog', 'saved', 'object_save_event_handler');
	 *
	 *	// Listen to the update event of all objects and subclasses
	 *	register_event('obj:*', 'updated', 'object_update_event_handler');
	 * \endcode
	 *
	 * @param string $namespace The event class, you may specify wild cards.
	 * @param string $event The event, you may specify wild cards.
	 * @param string $handler Handling function
	 * @param int $priority Value determining the order of execution.
	 * @see register_hook()
	 */
	function register_event($namespace, $event, $handler, $priority = 500)
	{
		global $CONFIG;
		
		// Turn highchair/elgg style wildcards into correct ones.
		$namespace = str_replace('*','.*',$namespace);
		$namespace = str_replace('/','\/',$namespace);
		$namespace = '/^'.str_replace('all','.*',$namespace).'$/';
		
		$event = str_replace('*','.*',$event);
		$event = str_replace('/','\/',$event);
		$event = '/^'.str_replace('all','.*',$event).'$/';
		
		
		if (!isset($CONFIG->_EVENTS))
			$CONFIG->_EVENTS = array();
			
		if (!isset($CONFIG->_EVENTS[$namespace]))
			$CONFIG->_EVENTS[$namespace] = array();
			
		if (!isset($CONFIG->_EVENTS[$namespace][$event]))
			$CONFIG->_EVENTS[$namespace][$event] = array();
			
		while (isset($CONFIG->_EVENTS[$namespace][$event][$priority]))
			$priority ++;
			
		$CONFIG->_EVENTS[$namespace][$event][$priority] = $handler;
		
		ksort($CONFIG->_EVENTS[$namespace][$event]);
		
		return true;
	}
	
	/**
	 * Trigger an event.
	 * 
	 * If a triggered event returns false it will prevent subsequent events
	 * in the chain from executing.
	 *
	 * @param string $namespace The event namespace
	 * @param string $event The event
	 * @param array $parameters Associated array of parameters.
	 * @return bool
	 */
	function trigger_event($namespace, $event, array $parameters = NULL)
	{
		global $CONFIG;
		
		$merged = array();
		
		if (!$CONFIG->_EVENTS) return true;
		
		// Get events we're triggering
		foreach ($CONFIG->_EVENTS as $namespace_key => $event_list)
		{
			// Does the namespace being triggered match a registered namespace?
			if (preg_match($namespace_key, $namespace))
			{
				foreach ($CONFIG->_EVENTS[$namespace_key] as $event_key => $function_list)
				{
					// Does the event being triggered match a registered event
					if (preg_match($event_key, $event))
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
			if ($function($namespace, $event, $parameters) === false)
				return false;
		}
		
		return true;
	}

	/**
	 * Returns true if a specific event has at least one handler.
	 * @param string $namespace The event namespace
	 * @param string $event The event
	 * @return bool
	 */
	function is_event_handled($namespace, $event)
	{
	    global $CONFIG;

	    // Turn highchair/elgg style wildcards into correct ones.
	    $namespace = str_replace('*','.*',$namespace);
	    $namespace = str_replace('/','\/',$namespace);
	    $namespace = '/^'.str_replace('all','.*',$namespace).'$/';

	    $event = str_replace('*','.*',$event);
	    $event = str_replace('/','\/',$event);
	    $event = '/^'.str_replace('all','.*',$event).'$/';

	    return isset($CONFIG->_EVENTS[$namespace][$event]);
	}