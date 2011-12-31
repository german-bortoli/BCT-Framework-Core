<?php
	/**
	 * Platform JSON item view.
	 * 
	 * @package core
	 * @license The MIT License (see LICENCE.txt), other licenses available.
	 * @author Marcus Povey <marcus@marcus-povey.co.uk>
	 * @copyright Marcus Povey 2009-2012
	 * @link http://platform.barcamptransparency.org/
	 */

	$item = $vars['item'];
	$export = $item->safeExport();
		
	if ($vars['item']->canView())
	    echo json_encode($export);
?>