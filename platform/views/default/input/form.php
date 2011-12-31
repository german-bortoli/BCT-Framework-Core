<?php
	/**
	 * Input form
	 * 
	 * @package core
	 * @license The MIT License (see LICENCE.txt), other licenses available.
	 * @author Marcus Povey <marcus@marcus-povey.co.uk>
	 * @copyright Marcus Povey 2009-2012
	 * @link http://platform.barcamptransparency.org/
	 */
	
	// Define possible fields and their defaults, a boolean FALSE means don't show if not present
	$fields_and_defaults = array(
		'name' => false,
		'id'   => false,
		
		'method' => 'POST',
		'action' => false,
		'enctype' => false,
		'novalidate' => false,
	
		'onsubmit' => false
	);

	global $form_id;
	if (!$vars['id']) {
		$form_id ++;
		$vars['id'] = $vars['name'] . "$form_id";
	}
?>
<form <?php
		foreach ($fields_and_defaults as $field => $default)
		{
			if (isset($vars[$field]))
			{
				if ($vars[$field]===true)
					echo "$field ";
				else
					echo "$field=\"{$vars[$field]}\" ";
			}
			else
			{
				if ($default!==false)
				{
					if ($default===true)
						echo "$field ";
					else
						echo "$field=\"$default\" ";		
				}
			}
		}
	?>>
	<?php echo view('input/securitytoken'); ?>
	<?php echo $vars['body']; ?>
</form>