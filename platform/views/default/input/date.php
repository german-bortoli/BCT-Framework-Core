<?php
	/**
	 * Date picker field.
	 * 
	 * This uses HTML 5 date pickers, (todo: fallback to picker if not supported).
	 * 
	 * @package core
	 * @license The MIT License (see LICENCE.txt), other licenses available.
	 * @author Marcus Povey <marcus@marcus-povey.co.uk>
	 * @copyright Marcus Povey 2009-2012
	 * @link http://platform.barcamptransparency.org/
	 */

	// If ts then convert to date format
	$value = $vars['value'];
	if ((int)$value > 0) {
		// This is a timestamp, then convert to date format (YYYY-MM-DD)
		$value = date('Y-m-d', $value);
	}

	$vars['value'] = $value;
	$vars['type'] = 'date';
	
	echo view('input/input', $vars);