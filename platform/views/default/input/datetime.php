<?php
	/**
	 * Date time picker
	 * 
	 * This uses HTML 5 date pickers.
	 * 
	 * @package core
	 * @license The MIT License (see LICENCE.txt), other licenses available.
	 * @author Marcus Povey <marcus@marcus-povey.co.uk>
	 * @copyright Marcus Povey 2009-2011
	 * @link http://platform.barcamptransparency.org/
	 */

	// If ts then convert to date format
	$value = $vars['value'];
	if ((int)$value > 0) {
		// This is a timestamp
		$value = date('Y-m-d H:i', $value);
	}

	
	$vars['value'] = $value;
	$vars['type'] = 'datetime';
	
	echo view('input/input', $vars);