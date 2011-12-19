<?php
	/**
	 * @file
	 * Action handler.
	 * Handle system actions in a constructive way.
	 * 
	 * @package core
	 * @license The MIT License (see LICENCE.txt), other licenses available.
	 * @author Marcus Povey <marcus@marcus-povey.co.uk>
	 * @copyright Marcus Povey 2009-2011
	 * @link http://platform.barcamptransparency.org/
	 */

	require_once(dirname(dirname(__FILE__)) . "/engine/start.php");
	
	$action = input_get('action');
	$forward = $_SERVER['HTTP_REFERER'];

	$result = action($action);
	if ($result === false) 
	{
		// We have returned an explicit fail, report this.
		
		header("HTTP/1.1 404 Not Found");
		header("Status: 404");
		
		throw new ActionNotFoundException(sprintf(_echo('action:exception:notfound'), $action)); 
	}
	else if (($result) && ($result !== true)) // Result has returned a value, probably a forward. Forward to this location.
		$forward = $result;
	
	// Default forward to 	
	forward($forward);
?>