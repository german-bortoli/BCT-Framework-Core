<?php
	/**
	 * @file
	 * English language translations for engine commands.
	 * 
	 * @package core
	 * @license The MIT License (see LICENCE.txt), other licenses available.
	 * @author Marcus Povey <marcus@marcus-povey.co.uk>
	 * @copyright Marcus Povey 2009-2011
	 * @link http://platform.barcamptransparency.org/
	 */

	$english = array (
	
		// Actions
		'action:exception:tokeninvalid' => 'Action token is invalid.',
		'action:exception:handlernotcallable' => 'Action handler %s() is not callable',
	 	'action:exception:actionblocked' => 'Action blocked by an event handler.',
		'action:exception:notfound' => 'Sorry, action %s could not be found!',
		'action:token:timeout' => 'This form has expired, please refresh page and try again.',
	
		// Pages
		'page:exception:notfound' => 'Page %s not found.',
	
		// Data
		'class:exception:invalidrow' => 'Object factory called without a valid database row',
		'class:exception:invalidbctobject' => 'New object is not a valid BCTObject',
		'class:exception:missingtype' => 'A %s object has no type defined which means it can not be found in searches, your programmer should use the setType() method in the class constructor.',
		'class:error:classnotfound' => 'Class %s not found',
	
		// Database 
		'database:exception:wrongcredentials' => 'Wrong credentials %s@%s using pw %s',
		'database:exception:connectionfail' => 'Could not connect to database %s',
		'database:exception:unsupporteddbengine' => 'Unsupported database engine %s',
	
		// Plugins
		'plugins:exception:misconfigured' => 'Plugin %s is misconfigured, could not find start.php',
		'plugins:exception:functionnotcallable' => 'Function %s not called as it could not be found.',
		'plugins:exception:classnotfound' => 'Class %s could not be created as it could not be found.',
		'plugins:exception:missingplugin' => 'An installed plugin requires plugin %s to run, please install and activate it.',
		'plugins:exception:platformdependency' => 'An installed plugin requires at least version %s of the platform to run, you currently have %s installed.',
	
		// Export
		'export:exception:unknownmode' => 'Unknown mode "%s" passed to export handler',
		'export:exception:notfound' => 'Object %d could not be found',
		'export:exception:entitiesnotfound' => 'There were no objects found which matched your query.',
		'export:object' => 'Exporting object %s',
		'export:objects' => 'Exporting objects',

		// System
		'exception:title' => 'Fatal exception',
	
		// Relationships
		'relationship:create:missingguid' => 'Could create relationship as at least one of the objects you are linking to has not been saved',
		
		// Annotations
		'annotation:create:missingguid' => 'Could create annotation as the object you are annotating has no GUID',
	
		// Questions
		'question:areyousure' => 'Are you sure?',
	
		// Input
		'input:validation:nan' => 'Please enter value as a number.',
		'input:validation:toolow' => 'Enter a value above or equal to %d',
		'input:validation:toohigh' => 'Enter a value below or equal to %d',

		// User
		'user:datastore:nouser' => 'Unable to create a new user datastore as a user object was neither specified or found from the logged in session.'
	);
	
	register_language($english, 'en');
