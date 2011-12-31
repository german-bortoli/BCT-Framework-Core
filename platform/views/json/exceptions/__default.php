<?php
	/**
	 * Platform JSON exception view.
	 * 
	 * @package core
	 * @license The MIT License (see LICENCE.txt), other licenses available.
	 * @author Marcus Povey <marcus@marcus-povey.co.uk>
	 * @copyright Marcus Povey 2009-2012
	 * @link http://platform.barcamptransparency.org/
	 */

	$exception = $vars['exception'];
	$export = new stdClass;
	
	foreach ($exception as $k => $v)
		$export->$k = $v;
		
	echo json_encode($export);
?>