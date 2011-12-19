<?php
	/**
	 * Output a url.
	 * 
	 * @package core
	 * @license The MIT License (see LICENCE.txt), other licenses available.
	 * @author Marcus Povey <marcus@marcus-povey.co.uk>
	 * @copyright Marcus Povey 2009-2011
	 * @link http://platform.barcamptransparency.org/
	 */

	$url = $vars['href'];
	if (!$url)
		$url = $vars['url'];
		
	$value = $vars['value'];
	if (!$value)
		$value = $url;
		
	if (!$url) $url = $value;
	
	$title = $vars['title'];
	$target = $vars['target'];
	
	if ($vars['is_action']) {
		$ts = time();
		$token = action_token_generate($ts);
	
		$sep = "?";
		if (strpos($url, '?')>0) $sep = "&";
		$url = "$url{$sep}__to=$token&__ts=$ts";
	}
	
?>
<a href="<?php echo $url; ?>" <?php if ($vars['onclick']) echo "onclick=\"{$vars['onclick']}\""; ?><?php if ($target) echo "target=\"$target\" "; ?> <?php if ($title) echo "title=\"$title\" "; ?>><?php echo $value; ?></a>