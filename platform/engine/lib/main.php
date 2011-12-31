<?php
	/**
	 * @file
	 * Main library file.
	 * 
	 * This file contains a number of utility and initialisation functions.
	 * 
	 * @package core
	 * @license The MIT License (see LICENCE.txt), other licenses available.
	 * @author Marcus Povey <marcus@marcus-povey.co.uk>
	 * @copyright Marcus Povey 2009-2012
	 * @link http://platform.barcamptransparency.org/
	 */

	/**
	 * Write a message in the log.
	 *
	 * @param string $message The message.
	 * @param string $level Error level.
	 * @return bool
	 */
	function log_echo($message, $level='NOTICE') 
	{
		global $CONFIG;
		global $__DEBUG_LOG_HISTORY;
		
		$level = strtoupper($level);
	
		if (isset($CONFIG->debug)) {
	
			switch ($level) {
				case 'DEBUG': 
				case 'ERROR':
				case 'EXCEPTION':
				case 'WARNING':
				case 'NOTICE':
				default:
				    if (!$__DEBUG_LOG_HISTORY) $__DEBUG_LOG_HISTORY = array();

				    $__DEBUG_LOG_HISTORY[] = array('level' => $level, 'message' => $message);
				    error_log("$level: $message");
			}
	
			return true;
		}

		return false;
	}
	
	/**
	 * Forward the browser to a specific location.
	 * 
	 * Forwards the browser session using a forward header. Note that successful execution of this
	 * function will stop execution on the page.
	 *
	 * @param url $location The location to forward to, if this isn't the full url then it is assumed
	 * 						relative to $CONFIG->wwwroot. A blank location will forward to $CONFIG->wwwroot.
	 * @param int $code Optional HTTP code to use defining the forward, defaults to 302
	 * @return false if headers have already been sent.
	 */
	function forward($location = "", $code = 302) 
	{
		global $CONFIG;

		if (!headers_sent()) 
		{
			if ((substr_count($location, 'http://') == 0) && (substr_count($location, 'https://') == 0)) 
				$location = $CONFIG->wwwroot . $location;
				 
			header("Location: {$location}", true, $code);	
			exit;	
		}
			
		return false;
	}
	
	/**
	 * Return the full URL of the current page.
	 *
	 * @return url
	 */
	function current_page_url()
	{
		$https = $_SERVER["HTTPS"] ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : "";
		
		$protocol = substr(strtolower($_SERVER["SERVER_PROTOCOL"]), 0, strpos(strtolower($_SERVER["SERVER_PROTOCOL"]), "/")) . $https;
		
		$port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]);
		
		return $protocol . "://" . $_SERVER['SERVER_NAME'] . $port . $_SERVER['REQUEST_URI'];
	}
	
	/**
	 * Trap PHP error message.
	 * 
	 * @see http://www.php.net/set-error-handler
	 * @param int $errno The level of the error raised
	 * @param string $errmsg The error message
	 * @param string $filename The filename the error was raised in
	 * @param int $linenum The line number the error was raised at
	 * @param array $vars An array that points to the active symbol table at the point that the error occurred
	 */
	function __error_handler($errno, $errmsg, $filename, $linenum, $vars)
	{			
		$error = date("Y-m-d H:i:s (T)") . ": \"" . $errmsg . "\" in file " . $filename . " (line " . $linenum . ")";
		
		switch ($errno) {
			case E_USER_ERROR:
					log_echo($error, 'ERROR');
				break;

			case E_WARNING :
			case E_USER_WARNING : 
					log_echo($error, 'WARNING');
				break;

			default:
				log_echo($error, 'DEBUG'); 
				
		}
		
		return true;
	}
		
	/**
	 * Custom exception handler.
	 * This function catches any thrown exceptions and handles them appropriately.
	 *
	 * @see http://www.php.net/set-exception-handler
	 * @param Exception $exception The exception being handled
	 */
	function __exception_handler($exception) {

		ob_end_clean(); // Clear existing / half empty buffer
		
		// Log exception
		log_echo($exception->getMessage(), 'EXCEPTION');
		
		// If this is a platform exception then render it creatively, otherwise enforce the default
		if ($exception instanceof BCTPlatformException)
			$body = "$exception";
		else
			$body = view('exceptions/__default', array('exception' => $exception));
			
		output_page(_echo('exception:title'), $body);
		
	}

	/**
	 * Shutdown hook handler.
	 */
	function __shutdown_handler()
	{
	    ob_end_clean();
	    ob_start();

	    trigger_event('system', 'shutdown');

	    ob_end_clean();
	}
	
	/**
	 * Attempt to retrieve the system temporary directory using a variety of methods.
	 * 
	 * Use this function rather than sys_get_temp_dir() as this is only available in 
	 * the latest versions of php (>= 5.2.3).
	 *
	 */
	function get_temp_dir()
	{
	    if (function_exists('sys_get_temp_dir'))
		    return realpath(sys_get_temp_dir()) . '/';

	    if ($temp=getenv('TMP'))
		    return $temp . '/';

	    if ($temp=getenv('TEMP'))
		    return $temp . '/';

	    if ($temp=getenv('TMPDIR'))
		return $temp . '/';

	    // Last ditch
	    $temp=tempnam(__FILE__,'');

	    if (file_exists($temp))
	    {
		    unlink($temp);
		    return dirname($temp) . '/';
	    }
		
	    return false;
	}

	/**
	 * Main libary initialisation.
	 * 
	 * Initialise various core parts of the library.
	 */
	function main_boot()
	{
		global $CONFIG;
		
		// Docroot and URL, where the website docs are stored.
		if (!isset($CONFIG->wwwroot))
			$CONFIG->wwwroot = 'http://'.$_SERVER['SERVER_NAME'] . '/';
		if (!isset($CONFIG->url))
			$CONFIG->url = $CONFIG->wwwroot;
			
		// Docroot of static files (images, static css etc), usually the same as wwwroot, but can be split to another server / CDN
		if (!isset($CONFIG->staticroot))
			$CONFIG->staticroot = $CONFIG->wwwroot;
		
		// Temporary directory	
		if (!isset($CONFIG->temp))
			$CONFIG->temp = get_temp_dir() . md5($CONFIG->url) . '/';
		
		// Where the data directory is, note this should be outside of the wwwroot.
		if (!isset($CONFIG->dataroot))
			$CONFIG->dataroot = $CONFIG->temp;
			
			// Now ensure dataroot is created, so that caching works off the bat TODO: Move this to a runonce/install procedure
			@mkdir($CONFIG->dataroot, 0777, true);
			
		// Where on the file system are the website files stored (this is usally safe to leave autodetected)
		if (!isset($CONFIG->docroot))
			$CONFIG->docroot = dirname(dirname(dirname(__FILE__))) . '/';
			
		// Where plugins are.
		if (!isset($CONFIG->plugins))
			$CONFIG->plugins = $CONFIG->docroot . 'plugins/';

		// Work out a site secret
		if (!isset($CONFIG->site_secret))
			$CONFIG->site_secret = md5($CONFIG->docroot.$CONFIG->url);
		
		// Now set php error handlers
		if ($CONFIG->debug)
			set_error_handler('__error_handler', E_ALL & E_STRICT);
		else
			set_error_handler('__error_handler', E_ALL & ~E_NOTICE); // Hide notice level errors when not in debug
			
		set_exception_handler('__exception_handler');

		// Now register shutdown hook
		register_shutdown_function('__shutdown_handler');
			
	}
	
	register_event('system', 'boot', 'main_boot', 1);
