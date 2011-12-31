<?php
	/**
	 * Example settings.php.
	 * 
	 * This is an example file, rename it to settings.php
	 * 
	 * @package core
	 * @license The MIT License (see LICENCE.txt), other licenses available.
	 * @author Marcus Povey <marcus@marcus-povey.co.uk>
	 * @copyright Marcus Povey 2009-2012
	 * @link http://platform.barcamptransparency.org/
	 */

	/**
	 * Create the CONFIG object.
	 * You will want to do this at the top of settings file.
	 */
	global $CONFIG;
	if (!isset($CONFIG)) $CONFIG = new stdClass;

	/**
	 * Define which plugins will be enabled.
	 *  
	 * List the names of plugins you want to be active, in the order that you 
	 * want them to be triggered.
	 */
	$CONFIG->enabled_plugins = array (
		'foo',
		'bar'
	);
	
	/**
	 * Per-domain configuration.
	 * 
	 * Uncomment this line if you want to enable per-domain configuration files (settings.REFERRINGDOMAIN.php),
	 * this also lets you use a single BCT library codebase to host multiple sites.
	 * 
	 * Note, the per-domain config is called after the initial settings.php.
	 */
	// $CONFIG->per_domain_config = true;
	
	
	/*
	 * ...Other settings, eg database...
	 */