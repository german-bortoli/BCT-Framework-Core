<?php
	/**
	 * @file
	 * Page handler.
	 * A page handler allows plugins to define arbitrary virtual file systems.
	 * 
	 * @package core
	 * @license The MIT License (see LICENCE.txt), other licenses available.
	 * @author Marcus Povey <marcus@marcus-povey.co.uk>
	 * @copyright Marcus Povey 2009-2011
	 * @link http://platform.barcamptransparency.org/
	 */

	require_once(dirname(dirname(__FILE__)) . "/engine/start.php");
	
	$page = input_get('page');

	if (!page_handler($page))
	{
		
		header("HTTP/1.1 404 Not Found");
		header("Status: 404");
		
		throw new PageNotFoundException(sprintf(_echo('page:exception:notfound'), $page));
	}
?>