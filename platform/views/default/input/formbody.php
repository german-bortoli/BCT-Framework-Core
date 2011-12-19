<?php
	/**
	 * This input value provides a simple way of constructing forms
	 * simply without the need for a lot of typing.
	 * 
	 * It uses an associative array of 'field' => 'input_view' called $vars['fields']
	 * 
	 * Default values are in an array called $vars['values'] which can be an associative array
	 * 'field' => 'value', or an object.
	 * 
	 * Labels are "formbody:{$vars['name']}:$field", placeholders are "formbody:$name:$field:placeholder" language strings.
	 */


	$form = $vars['fields'];
	$values = $vars['values'];
	$required = $vars['required_fields'];
	$defaults = $vars['defaults'];
	
	
	$name = $vars['name'];
	if (!$name) $name = $vars['id'];
	if (!$name) $name = 'label';
	
	if ($form)
	{
		foreach ($form as $field => $type)
		{
			$value = "";
			if (is_array($values))
				$value = $values[$field];
			else
				$value = $values->$field;

			if ((!$value) && (isset($defaults[$field])))
			    $value = $defaults[$field];
				
			$params = array('name' => $field, 'value' => $value);
			if (language_key_exists("formbody:$name:$field:placeholder")) $params['placeholder'] = _echo("formbody:$name:$field:placeholder");
			if (($vars['required_fields']) && (in_array($field, $vars['required_fields']))) $params['required'] = true;

			if ($type != 'hidden') {
			?>
			<p class="<?php echo (++$n % 2 == 1) ? 'odd' : 'even'; ?> <?php echo $field; ?>"><label class="<?php echo $field; ?>"><?php echo _echo("formbody:$name:$field");?> <?php echo view("input/$type", $params); ?></label></p>
			<?php
			} else
			    echo view("input/$type", $params);
		}
		
		// If this is a BCT object, then we can put in a helper value
		if (($values) && ($values instanceof BCTObject))
		{
		?>
			<input type="hidden" name="item_guid" value="<?php echo $values->guid; ?>" />
		<?php 
		}
	}