<?php

	/**
	 * Button.
	 * 
	 * @package core
	 * @license The MIT License (see LICENCE.txt), other licenses available.
	 * @author Marcus Povey <marcus@marcus-povey.co.uk>
	 * @copyright Marcus Povey 2009-2012
	 * @link http://platform.barcamptransparency.org/
	 */


	if (!$vars['class']) $vars['class'] = "input-submit";
	if (!$vars['type']) $vars['type'] = 'submit';
	
	// Sanity check
	unset($vars['autofocus']);
	unset($vars['autocomplete']);
	
	echo view('input/input', $vars);
 