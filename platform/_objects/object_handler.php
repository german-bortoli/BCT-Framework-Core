<?php
	/**
	 * @file
	 * Object handler.
	 * 
	 * Export / generic display interface for objects in the system, this provides a sophisticated
	 * yet generic interface for getting access to data in the system.
	 * 
	 * @package core
	 * @license The MIT License (see LICENCE.txt), other licenses available.
	 * @author Marcus Povey <marcus@marcus-povey.co.uk>
	 * @copyright Marcus Povey 2009-2012
	 * @link http://platform.barcamptransparency.org/
	 */

	require_once(dirname(dirname(__FILE__)) . "/engine/start.php");
	
	$mode = input_get('mode');
	
	switch ($mode)
	{
		case 'object' :
			$guid = input_get('guid');
			
			$entity = getObject($guid);
			
			if (!$entity) 
			{
				header("HTTP/1.1 404 Not Found");
				header("Status: 404");
				
				throw new ExportException(sprintf(_echo('export:exception:notfound'), $guid));
			}
			else
				output_page(sprintf(_echo('export:object'), $guid), $entity);
		
		break;
		
		case 'objects' :
			
			$query = input_get('query');
			$params = input_get('params', null);
			$limit = input_get('limit', 10);
			$offset = input_get('offset', 0);
			
			// Parse query
			$query = str_replace('/', ':', trim($query, '/')); // replace delimiters
			$query = str_replace('_', '%', $query); // replace wildcards 
			
			$datalist = getObjects($query, $params, array (
				'limit' => $limit,
				'offset' => $offset,
				'orderby' => 'o.guid'
			));
			
			if (!$datalist)
			{
				header("HTTP/1.1 404 Not Found");
				header("Status: 404");
				
				throw new ExportException(_echo('export:exception:entitiesnotfound'));
			}
			else
				output_page(_echo('export:objects'), $datalist);
			
			
		break;
		default: throw new ExportException(sprintf(_echo('export:exception:unknownmode'), $mode));
	}
	
?>