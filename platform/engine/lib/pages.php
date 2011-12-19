<?php
	/**
	 * @file
	 * Page handling library.
	 *
	 * Contains functions for handling the framework's virtual pages.
	 * 
	 * @package core
	 * @license The MIT License (see LICENCE.txt), other licenses available.
	 * @author Marcus Povey <marcus@marcus-povey.co.uk>
	 * @copyright Marcus Povey 2009-2011
	 * @link http://platform.barcamptransparency.org/
	 */

	/**
	 * Handle an abstract page.
	 * This function returns a page, either a page from a handler or a physical page.
	 *
	 * @param string $page The page
	 * @return bool
	 */
	function page_handler($page)
	{
		global $CONFIG;
		
		// Attempt to set the appropriate mimetype for the page
		header('Content-Type: ' . page_get_mimetype($page));
		
		// Work out which page
		$pages = explode('/', $page);
		
		$key = ""; 
		foreach ($pages as $p)
		{
			$key .= $p;
			if ((isset($CONFIG->_PAGES[$key])) || (isset($CONFIG->_PAGES["$key/"]))) 
				break;
				
			$key .= "/";		
		}
		
		// Tokenise input variables
		$query = substr($_SERVER['REQUEST_URI'], strpos($_SERVER['REQUEST_URI'], '?')+1);
		if (isset($query)) {
			parse_str($query, $query_arr);
			if (is_array($query_arr)) {
				foreach($query_arr as $name => $val) {
					input_set($name, $val);
				}
			}
		} 
		
		// We have a page registered for this
		if ($key)
		{
			// Get the actual pages
			$pages = substr($page, strlen($key));
			$pages = trim($pages, "/?");
			$pages = explode('/', $pages);
			
			// Execute handler
			$handler = $CONFIG->_PAGES[$key];
			if (!$handler) $handler = $CONFIG->_PAGES["$key/"];
			
			if (is_callable($handler))
			{
				// Set the context of a page
				page_set_context($key);

				if ($handler($key, $pages)!==false)
					return true;
			}
		}
		
		// We don't have a page registered for this, attempt to pass through
		$newpage = $CONFIG->docroot.$page;
		
		if (is_dir($newpage))
		{
			$newpage = rtrim($newpage, "/");
		
			foreach (array('/index.php', 'index.html', '/index.htm') as $try)
			{
				if (file_exists($newpage . $try))
				{
					if (include_once($newpage . $try))
						return true;
				}
			}
			
		}
		else if (file_exists($newpage)) {
						
			// Include file
			if (include_once($newpage)) 
				return true;
				
		}
		
		return false;
	}
	
	/**
	 * Get mime type of page.
	 *
	 * @param string $page The url
	 * @return string
	 */
	function page_get_mimetype($page)
	{
		global $CONFIG;
		
		$mime = "";
		// TODO: Detect mime type in other ways 
		
		
		if ((!$mime) && (file_exists($page))) $mime = __mime_content_type($CONFIG->docroot.$page); // Attempt on physical files
		if (!$mime) $mime = __mime_content_type($page); // Attempt on virtual files
		 
		return $mime;
	}

	/**
	 * Wrapper around mime content type functions, offering substitute if the 
	 * standard php mime type detection files aren't present.
	 * 
	 * @param $file The file
	 * @return string
	 */
	function __mime_content_type($file)
	{
		if (!$file) return 'text/html'; // If file is blank, assume this is a dir, so return text/html
		
		if (function_exists('mime_content_type'))
			return @mime_content_type($file);
		else
		{
			$mime_types = array(
				'txt' => 'text/plain',
				'htm' => 'text/html',
				'html' => 'text/html',
				'php' => 'text/html',
				'css' => 'text/css',
				'js' => 'application/javascript',
				'json' => 'application/json',
				'xml' => 'application/xml',
				'swf' => 'application/x-shockwave-flash',
				'flv' => 'video/x-flv',
			
				// images
				'png' => 'image/png',
				'jpe' => 'image/jpeg',
				'jpeg' => 'image/jpeg',
				'jpg' => 'image/jpeg',
				'gif' => 'image/gif',
				'bmp' => 'image/bmp',
				'ico' => 'image/vnd.microsoft.icon',
				'tiff' => 'image/tiff',
				'tif' => 'image/tiff',
				'svg' => 'image/svg+xml',
				'svgz' => 'image/svg+xml',
				
				// archives
				'zip' => 'application/zip',
				'rar' => 'application/x-rar-compressed',
				'exe' => 'application/x-msdownload',
				'msi' => 'application/x-msdownload',
				'cab' => 'application/vnd.ms-cab-compressed',
				
				// audio/video
				'mp3' => 'audio/mpeg',
				'qt' => 'video/quicktime',
				'mov' => 'video/quicktime',
				
				// adobe
				'pdf' => 'application/pdf',
				'psd' => 'image/vnd.adobe.photoshop',
				'ai' => 'application/postscript',
				'eps' => 'application/postscript',
				'ps' => 'application/postscript',
				
				// ms office
				'doc' => 'application/msword',
				'rtf' => 'application/rtf',
				'xls' => 'application/vnd.ms-excel',
				'ppt' => 'application/vnd.ms-powerpoint',
				
				// open office
				'odt' => 'application/vnd.oasis.opendocument.text',
				'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
			);
			
			$ext = strtolower(array_pop(explode('.',$filename)));
			if (array_key_exists($ext, $mime_types))
				return $mime_types[$ext];
			else if (function_exists('finfo_open')) {
				$finfo = finfo_open(FILEINFO_MIME);
				$mimetype = finfo_file($finfo, $filename);
				finfo_close($finfo);
				return $mimetype;
	        }
		}
		
		return 'application/octet-stream';
	}
	
	
	/**
	 * Set the current context of the page.
	 * 
	 * This is useful to know where you are and what page you are on. Called automatically 
	 * by the page handling functions.
	 *
	 * @param string $context The context (same as a page key)
	 */
	function page_set_context($context)
	{
		global $CONFIG;
		
		$CONFIG->_PAGE_CURRENT_CONTEXT = $context;
	}
	
	/**
	 * Return the context of the current page.
	 *
	 * @return string Value (same as the page key of the current page)
	 */
	function page_get_context()
	{
		global $CONFIG;
		
		return $CONFIG->_PAGE_CURRENT_CONTEXT;
	}
	
	/**
	 * Register a page handler.
	 * 
	 * A page handler should be a function defined as :
	 * 
	 * 	handler($page, array $subpages);
	 *
	 * @param string $page Page to handle
	 * @param string $handler The handler.
	 * @return bool
	 */
	function register_page($page, $handler)
	{
		global $CONFIG;
		
		if (!isset($CONFIG->_PAGES))
			$CONFIG->_PAGES = array();
			
		$CONFIG->_PAGES[$page] = $handler;
		
		return true;
	}
