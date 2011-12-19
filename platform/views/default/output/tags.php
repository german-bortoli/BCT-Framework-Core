<?php

	/**
	 * Tags output view.
	 * 
	 * @package core
	 * @license The MIT License (see LICENCE.txt), other licenses available.
	 * @author Marcus Povey <marcus@marcus-povey.co.uk>
	 * @copyright Marcus Povey 2009-2011
	 * @link http://platform.barcamptransparency.org/
	 */


	$tags = $vars['value'];
	$searchbase = $vars['searchbase'];
	if (!$searchbase) $searchbase = $CONFIG->search_base; // Allow default search base to be set in config.
	
	
	if (!is_array($tags))
		$tags = explode(',', $tags);

	$taglist = "";
	if ($searchbase)
	{
		foreach ($tags as $tag)
		{
			$tag = trim($tag);
			
			if ($tag) {
				$taglist .= view('output/url', array(
					'href' => $searchbase . urlencode($tag),
					'value' => $tag
				)) . ', ';
			}
		}
	}
	else
		$taglist = implode(', ', $tags);
		
	$class = $vars['class'];
	if (!$class) $class='output-tags';
?>
<div class="<?php echo $class; ?>">
	<?php echo trim($taglist,' ,'); ?>
</div>