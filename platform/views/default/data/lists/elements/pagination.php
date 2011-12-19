<?php
	/**
	 * Platform pagination view.
	 * 
	 * Paginates a list item, passed a BCTDataList as $vars['list'].
	 * 
	 * @package core
	 * @license The MIT License (see LICENCE.txt), other licenses available.
	 * @author Marcus Povey <marcus@marcus-povey.co.uk>
	 * @copyright Marcus Povey 2009-2011
	 * @link http://platform.barcamptransparency.org/
	 */

	$page = $vars['list']->getPage();
	$total_pages = $vars['list']->getTotalPages();
	$perpage = $vars['list']->getItemsPerPage();
	
	$baseurl = preg_replace('/[\&\?]offset\=[0-9]*/',"", $_SERVER['REQUEST_URI']);
?>
<div class="pagination">
	<ul>
	<?php 
		if ($page > 1)
		{
			// Display prev button
			$prevurl = $baseurl;
			if (substr_count($baseurl,'?')) 
				$prevurl .= "&offset=" . (($page-1) * $perpage - $perpage);
			else 
				$prevurl .= "?offset=" . (($page-1) * $perpage - $perpage);
				
			?>
				<li class="previous"><a href="<?php echo $prevurl; ?>">&laquo; <?php _echo('pagination:previous'); ?></a></li>
			<?php 
		}
	
		// Simple pagination for now
		for ($n=0; $n < $total_pages; $n++)
		{
			$pageurl = $baseurl;
			if (substr_count($baseurl,'?')) 
				$pageurl .= "&offset=" . ($n * $perpage);
			else 
				$pageurl .= "?offset=" . ($n * $perpage);
				
			?>
			<li<?php if ($n==$page-1) echo " class=\"current\""; ?>><a href="<?php echo $pageurl; ?>"><?php echo ($n+1); ?></a></li>
			<?php
		}
		
		if ($page < $total_pages)
		{
			// Display next button
			$nexturl = $baseurl;
			if (substr_count($baseurl,'?')) 
				$nexturl .= "&offset=" . (($page-1) * $perpage + $perpage);
			else 
				$nexturl .= "?offset=" . (($page-1) * $perpage + $perpage);
				
			?>
				<li class="next"><a href="<?php echo $nexturl; ?>"><?php _echo('pagination:next'); ?> &raquo;</a></li>
			<?php 
		}
	?>
	</ul>
</div>