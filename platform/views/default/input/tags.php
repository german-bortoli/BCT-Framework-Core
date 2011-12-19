<?php
	/**
	 * Tags input view.
	 * 
	 * @package core
	 * @license The MIT License (see LICENCE.txt), other licenses available.
	 * @author Marcus Povey <marcus@marcus-povey.co.uk>
	 * @copyright Marcus Povey 2009-2011
	 * @link http://platform.barcamptransparency.org/
	 */


	if (!$vars['class']) $vars['class'] = "input-tags";
		
	if ($vars['value']) 
	{
		if (is_array($vars['value']))
			$vars['value'] = implode(', ', $vars['value']);
	}
	
	echo view('input/text', $vars);
?>