<?php
	/**
	 * Platform default item list view.
	 * 
	 * @package core
	 * @license The MIT License (see LICENCE.txt), other licenses available.
	 * @author Marcus Povey <marcus@marcus-povey.co.uk>
	 * @copyright Marcus Povey 2009-2012
	 * @link http://platform.barcamptransparency.org/
	 */

?>
<div class="datalist __default">
<?php
	if (($vars['list']) && ($vars['list']->getTotalPages()>1)) // Show pagination, but only if necessary
		echo view('data/lists/elements/pagination', $vars);

	foreach ($vars['list'] as $item)
		echo $item;
?>
</div>