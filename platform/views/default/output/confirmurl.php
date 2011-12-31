<?php
	/**
	 * Output a url, with a confirmation when clicked.
	 * 
	 * @package core
	 * @license The MIT License (see LICENCE.txt), other licenses available.
	 * @author Marcus Povey <marcus@marcus-povey.co.uk>
	 * @copyright Marcus Povey 2009-2012
	 * @link http://platform.barcamptransparency.org/
	 */

	$confirm = $vars['confirm'];
	if (!$confirm) 
		$confirm = _echo('question:areyousure');
	
	$vars['onclick'] .= "return confirm('".addslashes($confirm) ."');";
	
	echo view('output/url', $vars);