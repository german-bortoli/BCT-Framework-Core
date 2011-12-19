<?php
	/**
	 * Default exception view.
	 * 
	 * @package core
	 * @license The MIT License (see LICENCE.txt), other licenses available.
	 * @author Marcus Povey <marcus@marcus-povey.co.uk>
	 * @copyright Marcus Povey 2009-2011
	 * @link http://platform.barcamptransparency.org/
	 */

	$exception = $vars['exception'];
	$class = get_class($exception);
	$class_lower = strtolower($class);
	$message = $exception->getMessage();
?>
<div class="exception">
	<div class="<?php echo $class_lower; ?>">
		<p><?php echo $message; ?></p>
	</div>

<?php 
	if ($CONFIG->debug) {
?>
	<div class="debug">
<pre>
<?php print_r($exception); ?>
</pre>
	</div>
<?php		
	}
?>
</div>
