<?php
	/**
	 * Render a time.
	 * 
	 * @package core
	 * @license The MIT License (see LICENCE.txt), other licenses available.
	 * @author Marcus Povey <marcus@marcus-povey.co.uk>
	 * @copyright Marcus Povey 2009-2011
	 * @link http://platform.barcamptransparency.org/
	 */

	$text = $vars['text'];
	$value = $vars['value'];
	if ((int)$value == 0) 
		$value = strtotime($vars['value']);
	
	$date_format = $vars['format'];
	if (!$date_format) $date_format = 'H:i';
		
	if (!$text) $text = date($date_format, $value);
	
?><time datetime="<?php echo date('c', $vars['value']); ?>"><?php echo htmlentities($text, ENT_QUOTES, 'UTF-8'); ?></time>