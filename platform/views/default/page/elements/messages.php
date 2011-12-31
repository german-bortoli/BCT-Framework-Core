<?php
	/**
	 * @file
	 * Messages view.
	 * 
	 * @package core
	 * @license The MIT License (see LICENCE.txt), other licenses available.
	 * @author Marcus Povey <marcus@marcus-povey.co.uk>
	 * @copyright Marcus Povey 2009-2012
	 * @link http://platform.barcamptransparency.org/
	 */

	global $CONFIG;

	$errors = messages_get('error');
	$system = messages_get('system');
	$debug = messages_get('debug');

	if (($errors) || ($system) || ($debug)) {
?>
<div class="system_messages">
<?php
	    if ($errors)
		    foreach ($errors as $error)
			    echo view('messages/error', array('message' => $error));


	    if ($system)
		    foreach ($system as $message)
			    echo view('messages/system', array('message' => $message));

	    if ($CONFIG->debug)
	    {

		    if ($debug)
			    foreach ($debug as $message)
				    echo view('messages/debug', array('message' => $message));
	    }
	    ?>
</div>
    <?php

	}
?>