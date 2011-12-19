<?php
	/**
	 * Platform JSON item list view.
	 * 
	 * @package core
	 * @license The MIT License (see LICENCE.txt), other licenses available.
	 * @author Marcus Povey <marcus@marcus-povey.co.uk>
	 * @copyright Marcus Povey 2009-2011
	 * @link http://platform.barcamptransparency.org/
	 */

	$array = array();
	foreach ($vars['list'] as $item) 
		$array[] = "$item";
		
	echo "[".implode(',', $array)."]";
