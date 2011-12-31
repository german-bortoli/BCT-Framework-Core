<?php
	/**
	 * Platform default item view.
	 * 
	 * @package core
	 * @license The MIT License (see LICENCE.txt), other licenses available.
	 * @author Marcus Povey <marcus@marcus-povey.co.uk>
	 * @copyright Marcus Povey 2009-2012
	 * @link http://platform.barcamptransparency.org/
	 */

	 if ($vars['item']->canView()) {
?>
<div class="item __default">
	<?php 
		$icon = $vars['item']->getIcon('large');
		if ($icon)
		{
	?>		
		<div class="icon">
			<?php echo view('output/image', array('src' => $icon)); ?>
		</div>
	<?php 		
		}
	?>
	<?php 
	foreach ($vars['item']->safeExport() as $key => $val)
	{
	?>
		<p>
			<span class="key"><?php echo $key; ?>:</span>
			<span class="value"><?php echo var_export($val); ?></span>
		</p>
	<?php 
	}
	
	?>
</div>
<?php	} ?>