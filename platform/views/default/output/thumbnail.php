<?php
	/**
	 * Output a thumbnail.
	 * 
	 * @package core
	 * @license The MIT License (see LICENCE.txt), other licenses available.
	 * @author Marcus Povey <marcus@marcus-povey.co.uk>
	 * @copyright Marcus Povey 2009-2012
	 * @link http://platform.barcamptransparency.org/
	 */

	$url = $vars['href'];
	if (!$url)
		$url = $vars['url'];

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
<a href="<?php echo $url; ?>" <?php if ($target) echo "target=\"$target\" "; ?> <?php if ($title) echo "title=\"$title\" "; ?>><?php echo view('output/image', $vars); ?></a>