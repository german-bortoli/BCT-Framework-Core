<?php
	/**
	 * @file
	 * Messages library.
	 * Handle messages and pass them to the user (note it is the template's responsibility
	 * to echo these messages in a meaninful way).
	 * 
	 * @package core
	 * @license The MIT License (see LICENCE.txt), other licenses available.
	 * @author Marcus Povey <marcus@marcus-povey.co.uk>
	 * @copyright Marcus Povey 2009-2012
	 * @link http://platform.barcamptransparency.org/
	 */

	/**
	 * Retrieve a list of messages to be echoed.
	 *
	 * @param string $type The type of message to be retrieved.
	 * @return array|false
	 */
	function messages_get($type)
	{ 
		if ((isset($_SESSION['_MESSAGES'])) && (is_array($_SESSION['_MESSAGES']))) 
		{
			if ((isset($_SESSION['_MESSAGES'][$type])) && (is_array($_SESSION['_MESSAGES'][$type])))
			{
				$messages = $_SESSION['_MESSAGES'][$type];
				$_SESSION['_MESSAGES'][$type] = null;
				
				return $messages;
			}
		}
		
		return false;
	}
	
	/**
	 * Add a system message for display.
	 *
	 * @param string $message The message
	 * @param string $type The type of message.
	 */
	function messages_set($message, $type = 'system')
	{
		if (!is_array($_SESSION['_MESSAGES']))
			$_SESSION['_MESSAGES'] = array();
		if (!is_array($_SESSION['_MESSAGES'][$type]))
			$_SESSION['_MESSAGES'][$type] = array();
			
		$_SESSION['_MESSAGES'][$type][] = $message;
		
		return true;
	}
	
	/**
	 * Debug message.
	 * 
	 * Display debug messages which are echoed by the page shell. This is particularly handy for debugging 
	 * actions, or for returning messages which users can report in bug reports.
	 *
	 * @param string $message
	 * @return bool
	 */
	function debug_message($message) 
	{
		global $CONFIG;
		
		if ($CONFIG->debug)
			return messages_set($message, 'debug');
			
		return false;
	}
	
	/**
	 * Error messages.
	 *
	 * @param string $message
	 * @return bool
	 */
	function error_message($message) { return messages_set($message, 'error'); }
	
	/**
	 * Send a normal notification message.
	 *
	 * @param string $message
	 * @return bool
	 */
	function message($message) { return messages_set($message, 'system'); }
