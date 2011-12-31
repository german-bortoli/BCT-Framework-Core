<?php
	/**
	 * Submit button.
	 * 
	 * @package core
	 * @license The MIT License (see LICENCE.txt), other licenses available.
	 * @author Marcus Povey <marcus@marcus-povey.co.uk>
	 * @copyright Marcus Povey 2009-2012
	 * @link http://platform.barcamptransparency.org/
	 */


	$vars['type'] = 'submit';
	if (!$vars['class']) $vars['class'] = "input-submit";
		
	echo view('input/button', $vars);
