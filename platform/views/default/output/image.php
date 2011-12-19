<?php

	/**
	 * Image output field.
	 * 
	 * @package core
	 * @license The MIT License (see LICENCE.txt), other licenses available.
	 * @author Marcus Povey <marcus@marcus-povey.co.uk>
	 * @copyright Marcus Povey 2009-2011
	 * @link http://platform.barcamptransparency.org/
	 */


	$fields_and_defaults = array(
		'id'   => false,
		'class' => 'output-image',
		'width' => false,
		'height' => false,
		'align' => false,
		'src' => false,
		'border' => false,

		'onclick' => false,
		'onfocus' => false,
		'onblur' => false,
	);

	if (!$fields_and_defaults['src'])
		$fields_and_defaults['src'] = $vars['value'];	
?>
<img 
	<?php
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
		?>
 />