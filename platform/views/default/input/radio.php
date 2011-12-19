<?php
	/**
	 * Radio button input field.
	 *
	 * @package core
	 * @license The MIT License (see LICENCE.txt), other licenses available.
	 * @author Marcus Povey <marcus@marcus-povey.co.uk>
	 * @copyright Marcus Povey 2009-2011
	 * @link http://platform.barcamptransparency.org/
	 */

	if (!$vars['class']) $vars['class'] = "input-radio";
	$vars['type'] = 'radio';

	echo view('input/input', $vars);
