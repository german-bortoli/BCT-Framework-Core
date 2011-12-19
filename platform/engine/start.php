<?php
	/**
	 * @file
	 * Main library entrypoint file.
	 *
	 * This file boots the library, includes all required files and triggers the starting events.
	 * 
	 * You need to include this file in all pages which use this framework.
	 * 
	 * @package core
	 * @license The MIT License (see LICENCE.txt), other licenses available.
	 * @author Marcus Povey <marcus@marcus-povey.co.uk>
	 * @copyright Marcus Povey 2009-2011
	 * @link http://platform.barcamptransparency.org/
	 */
		
	/**
	 * \mainpage BCT Platform
	 * Welcome to the Barcamp Transparency Platform.
	 * 
	 * This is a flexible and powerful PHP web and web services framework initially developed by
	 * <a href="http://www.marcus-povey.co.uk">Marcus Povey</a> for the <a href="http://www.barcamptransparency.org">Barcamp Transparency project</a>,
	 * but is now used for much much more.
	 * 
	 * \section Installation
	 *
	 * \subsection prerequisites Before you begin
	 * Before you begin, you should make sure the following pre-requisites are met:
	 *	- Apache (2 is recommended)
	 *	- PHP 5.2 or greater as a module (or CLI if you intend to use the library
	 *	    for scripting only.
	 *	- modrewrite module for apache
	 *
	 * This should be sufficient to get the core platform files to boot without error, however many plugins
	 * will require extra functionality. Here are some common ones:
	 *	- PHP5-mysql (infact you will almost certainly need this)
	 *	- PHP5-GD (for anything that does image manipulation, for example the profile plugin)
	 * \endsubsection
	 *
	 * \subsection files Installing files
	 * Installation should be fairly straightforward for anyone familiar to installing stuff on
	 * webservers:
	 *
	 *	- Install the core files in a suitable directory on your web server
	 *	- Rename htaccess_dist to .htaccess and modify it accordingly. If you are installing on docroot, this can
	 *	    probably be left unmodified. Sub directories will require the rewrite_base directive to be set.
	 *	- Rename settings.example.php to settings.php and configure accordingly.
	 *	- If you plan on using the database (most likely), install the appropriate schema from engine/schema/. Set the
	 *	    prefix as appropriate in settings.php
	 * \endsubsection
	 *
	 * \subsection plugins Installing plugins
	 * By default plugins are installed to /plugins in their own subdirectory, for example the "foo" plugin would be installed
	 * to /plugins/foo.
	 *
	 * Once you've installed the files for your new plugin you should enable it in settings.php.
	 *
	 * @note A common gotcha when developing / installing plugins is forgetting to clear the caches and so failing
	 *	to find new views. A simple way of avoiding this is to set $CONFIG->disable_cache = true; on your development
	 *	machines.
	 * \endsubsection
	 *
	 * \subsection configuration Configuration
	 *  As mentioned above, configuration is performed in /engine/settings.php, plugin configuration options are typically listed
	 *  in their documentation.
	 * \subsubsection perdomain Per-domain configuration
	 * A powerful feature of the BCT framework is the ability to set per domain configurations. Per domain configs load allow you to
	 * automatically load a different configuration file based on the incoming domain requested.
	 *
	 * Of all the possibilities this gives you not least is the ability to run multiple different sites off a single
	 * installation of the code, making management considerably easier.
	 *
	 * To take advantage of this:
	 *	- Enable $CONFIG->per_domain_config = true; in settings.php
	 *	- Create a config file for your domain called: settings.FQDN.php, for example settings.example.com.php
	 * \endsubsubsection
	 *
	 * \endsubsection
	 *
	 * \endsection
	 */

	// Library files to include in order
 	require_once(dirname(__FILE__) . "/version.php");
	require_once(dirname(__FILE__) . "/settings.php");

	$include = array();
		
	// See if we need to include a per-domain configuration
	if ((isset($CONFIG->per_domain_config)) && ($CONFIG->per_domain_config))
	{
		$settings_file = "/settings.{$_SERVER['SERVER_NAME']}.php";
		if (file_exists(dirname(__FILE__) . $settings_file))
			$include[] = $settings_file;		
	}
	
	// Include the remainder of the libraries.
	$include = array_merge($include, array (
		"/lib/exceptions.php",
		"/lib/events.php",
		"/lib/hooks.php",
		"/lib/main.php",
		"/lib/language.php",
		"/lib/session.php",
		"/lib/cache.php",
		"/lib/filestore.php",
		"/lib/messaging.php",
		"/lib/messages.php",
		"/lib/input.php",
		"/lib/output.php",
		"/lib/database.php",
		"/lib/data.php",
		"/lib/pages.php",
		"/lib/actions.php",
		"/lib/factories.php",
		"/lib/user.php",
		"/lib/plugins.php",
		"/lib/urls.php",
		"/lib/misc.php",
	));
	
	foreach ($include as $file)
	{
		if (!include_once(dirname(__FILE__) . $file))  
			die("Could not load library file: $file");
	}
	
	// Boot core system (paths etc)
	trigger_event('system', 'boot');

	// Load plugins
	plugins_load(
		$CONFIG->enabled_plugins,
		$CONFIG->plugins
	);
	
	// Finished event system
	trigger_event('system', 'init');
