<?php
	/**
	 * @file
	 * Platform standard page shell.
	 * 
	 * @package core
	 * @license The MIT License (see LICENCE.txt), other licenses available.
	 * @author Marcus Povey <marcus@marcus-povey.co.uk>
	 * @copyright Marcus Povey 2009-2011
	 * @link http://platform.barcamptransparency.org/
	 */

	global $CONFIG;

	header("Content-type: text/html; charset=UTF-8");
	
?><!DOCTYPE html>
<html <?php echo view('page/extensions/html'); ?>>
	<?php echo view('page/elements/header', $vars); ?>	
	<?php echo view('page/elements/messages', $vars); ?>
	<?php echo view('page/elements/body', $vars); ?>
	<?php echo view('page/elements/footer', $vars); ?>
	<?php if ($CONFIG->debug) echo view('page/elements/debug', $vars); ?>
</html>