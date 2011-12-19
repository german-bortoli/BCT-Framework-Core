<?php

	/**
	 * @file
	 * Actions.
	 *
	 * Actions are endpoints for web forms and provide ways of modifying data
	 * rather than passing variables to pages in the traditional way.
	 *
	 * The advantages of this are numerous, but among them are:
	 *
	 * 1) You call functions not pages
	 * 2) The framework provides protection against a number of scripting attacks.
	 *  
	 * @package core
	 * @license The MIT License (see LICENCE.txt), other licenses available.
	 * @author Marcus Povey <marcus@marcus-povey.co.uk>
	 * @copyright Marcus Povey 2009-2011
	 * @link http://platform.barcamptransparency.org/
	 */


	/**
	 * Trigger an action.
	 * Trigger an action, called by the action handler.
	 * @param $action Action to trigger
	 * @return bool
	 * @throws ActionException
	 */
	function action($action)
	{
		global $CONFIG;
		
		if (isset($CONFIG->_ACTIONS[$action]))
		{
			$details = $CONFIG->_ACTIONS[$action];

			$handler = $details->handler;
			$require_token = $details->require_token;
			
			if (($require_token) && (!action_token_verify(input_get('__ts'), input_get('__to'))))
				throw new ActionException(_echo('action:exception:tokeninvalid')); 
				
			if (!is_callable($handler))
				throw new ActionException(sprintf(_echo('action:exception:handlernotcallable'), $handler)); 
				
			if (!trigger_event('action', $action))
				throw new ActionBlockedException(_echo('action:exception:actionblocked'));
				
			// Construct parameters and execute action handler
			$params = action_get_parameters($action);
			$serialised_parameters = array();

			if ($CONFIG->_ACTIONS[$action]->parameters) {
				foreach ($CONFIG->_ACTIONS[$action]->parameters as $k)
				{
					if (is_array($params[$k])) {
						$array = array();
						foreach ($params[$k] as $key => $element)
							$array[addslashes($key)] = $element;
						$serialised_parameters[] = $array;
					}
					else
						$serialised_parameters[] = $params[$k];	
				}
			}
				
			//$func_params = implode(",", $serialised_parameters);
			$result = call_user_func_array($handler, $serialised_parameters);
				
			return $result;
		}
		
		return false;
	}
	
	/**
	 * Register an action handler function. 
	 * 
	 * Reflection is used to detect the parameters required by the function.
	 *
	 * @param string $action The action
	 * @param string $handler The handler function (reflection is used to extract parameters)
	 * @param bool $require_token Is a security token required (true by default)
	 */
	function register_action($action, $handler, $require_token = true)
	{
		global $CONFIG;
		
		if (!isset($CONFIG->_ACTIONS))
			$CONFIG->_ACTIONS = array();
			
		$stdClass = new stdClass;
		$stdClass->handler = $handler;
		$stdClass->require_token = $require_token;
		
		// Use reflection to detect parameters
		if (strpos($handler, '::')!==false)
		    $reflection = new ReflectionMethod($handler);
		else
		    $reflection = new ReflectionFunction($handler);

		$parameters = $reflection->getParameters();
		$param_array = array();
		if ($parameters)
		{
			foreach ($parameters as $param)
				$param_array[] = $param->name;
			
			$stdClass->parameters = $param_array;
		}
		
		$CONFIG->_ACTIONS[$action] = $stdClass;
	}
	
	/**
	 * Return a list of parameters expected for a given action.
	 *
	 * @param string $action The handler
	 * @return array 
	 */
	function action_get_parameters($action)
	{
		global $CONFIG;
		
		$sanitised = array();
		
		if (isset($CONFIG->_ACTIONS[$action]->parameters)) {
			foreach ($CONFIG->_ACTIONS[$action]->parameters as $param)
				$sanitised[$param] = input_get($param, null);
		}
			
		return $sanitised;
	}
	
	/**
	 * Test whether a token is valid.
	 *
	 * @param string $__ts Token timestamp.
	 * @param string $__to Token to verify.
	 * @return bool
	 */
	function action_token_verify($__ts, $__to) 
	{
	    $__ts = trim($__ts);
	    $__to = trim($__to);

	    if ((!$__ts) || (!$__to)) return false;

	    if (strcmp($__to, action_token_generate($__ts))!==0)
		    return false;

	    $now = time();
	    $hour = 60*60;
	    if ( ($__ts < $now-$hour) || ($__ts > $now+$hour) )
		    return false;

	    return true;
	}
	
	/**
	 * Generate an action token with a given timestamp.
	 *
	 * @param int $timestamp
	 * @return token|false
	 */
	function action_token_generate($timestamp)
	{
	    $session_id = session_id();

	    $ua = $_SERVER['HTTP_USER_AGENT'];

	    if ($session_id)
		    return md5($timestamp.$session_id.$ua);

	    return false;
	}

