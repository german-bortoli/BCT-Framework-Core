<?php
	/**
	 * @file
	 * Functions handling output.
	 * 
	 * @package core
	 * @license The MIT License (see LICENCE.txt), other licenses available.
	 * @author Marcus Povey <marcus@marcus-povey.co.uk>
	 * @copyright Marcus Povey 2009-2011
	 * @link http://platform.barcamptransparency.org/
	 */

	
	/**
	 * Load a view hierachy from a given folder and any subfolders.
	 *
	 * @param string $base The base in the view system.
	 * @param string $folder Folder to load views from.
	 * @param string $base_location_path The physical base location.
	 * @param string $viewtype The viewtype to load these views into. Default is 'default'
	 */
	function load_output_views(
		$base = "",
		$folder, 
		$base_location_path = "",
		$viewtype = 'default'
	) 
	{	
		// Open directory
		if ($handle = opendir($folder)) 
		{
			// Read directory	
			while ($view = readdir($handle)) 
			{
				// Can we open this?
				if (!in_array($view, array('.','..','.svn','CVS'))) 
				{
					if (is_dir("$folder/$view"))
					{
						$newbase = "";
						if ($base)
							$newbase = "$base/";
						
						// This is a sub directory, load it
						load_output_views("{$newbase}$view", "$folder/$view", $base_location_path, $viewtype);
					}
					else
					{
						if (strpos($view, '.php')!==false)
						{
							$newbase = "";
							if ($base)
								$newbase = "$base/";
								
							// This is a view file, register it
							set_view_location($newbase.str_replace('.php', '', $view), $base_location_path, $viewtype);
						}
					}
				}
			}
		}
	}
	
	/**
	 * Wrapper around the output:filter hook.
	 * 
	 * Filter output from user supplied content, removing disallowed and dangerous tags etc.
	 *
	 * @param string $value Value being filtered
	 * @param array $params Optional parameters passed to the underlaying filter.
	 * @return Filtered results, if no fiter is provided by plugin then unmodified results are returned.
	 */
	function output_filter($value, array $params = null) { return trigger_hook('output', 'filter', $params, $value); }

	/**
	 * Get the current view type.
	 *
	 * @return string The view type, defaulting to 'default'
	 */
	function get_viewtype()
	{
		global $CURRENT_SYSTEM_VIEWTYPE, $CONFIG;
			
		$viewtype = NULL;
		
		if ($CURRENT_SYSTEM_VIEWTYPE != "")
			return $CURRENT_SYSTEM_VIEWTYPE;
			
		if (!$CONFIG->view) $CONFIG->view = "";
			
		if ((empty($_SESSION['view'])) || ( (trim($CONFIG->view)!="") && ($_SESSION['view']!=$CONFIG->view) )) {
	        $_SESSION['view'] = "default";
	        
	        // If we have a config default view for this site then use that instead of 'default'
	        if ((!empty($CONFIG->view)) && (trim($CONFIG->view)!=""))
	        	$_SESSION['view'] = $CONFIG->view;
	    }
			
	    if (empty($viewtype))
	        $viewtype = input_get('view');
	        
	    if (empty($viewtype)) 
	        $viewtype = $_SESSION['view'];
	    
	    return $viewtype;
	}
	
	/**
	 * Manually set the current view type.
	 *
	 * @param string $viewtype The viewtype
	 * @return bool
	 */
	function set_viewtype($viewtype = "default")
	{
		global $CURRENT_SYSTEM_VIEWTYPE;
		
		$CURRENT_SYSTEM_VIEWTYPE = $viewtype;
		
		return true;
	}

	/**
	 * Get the location of a given view.
	 *
	 * @param string $view The view
	 * @param string $viewtype View type
	 * @return string|false
	 */
	function get_view_location($view, $viewtype)
	{
		global $CONFIG;
		
		if (isset($CONFIG->views->locations[$viewtype][$view]))
			return $CONFIG->views->locations[$viewtype][$view];

    	if (!isset($CONFIG->viewpath)) 
			return dirname(dirname(dirname(__FILE__))) . "/views/";		    			
    	else 
    		return $CONFIG->viewpath;
    	    	
    	return false;
	}
	
	/**
	 * Set a location for a view.
	 *
	 * @param string $view The view.
	 * @param string $location The view's location
	 * @param string $viewtype Optional view type
	 */
	function set_view_location($view, $location, $viewtype = null) {
			
		global $CONFIG;
		
		if (!$viewtype)
			$viewtype = 'default';
		
		if (!isset($CONFIG->views)) 
			$CONFIG->views = new stdClass;

		if (!isset($CONFIG->views->locations)) $CONFIG->views->locations = array();
		if (!isset($CONFIG->views->locations[$viewtype])) $CONFIG->views->locations[$viewtype] = array();
		$CONFIG->views->locations[$viewtype][$view] = $location;

	}
	
	/**
	 * Render a view.
	 *
	 * @param string $view The view to render.
	 * @param array $vars Associated array containing variables to be passed to the view.
	 * @param string $viewtype Optional view type
	 * @return string The rendered view
	 */
	function view($view, array $vars = null, $viewtype = null)
	{
		global $CONFIG;
		
		// Get view type
		if (!$viewtype) $viewtype = get_viewtype(); 
		$viewtype = trigger_hook('view', 'default', NULL, $viewtype); // Call out to plugins to override (for example, detect mobile view)
		
		if (empty($vars)) $vars = array();
		
		// Bring in session
		if (isset($_SESSION))
		 	$vars['_SESSION'] = $_SESSION;
		
		// Bring in config 
		if (isset($CONFIG))
		 	$vars['CONFIG'] = $CONFIG;
		 	
		// URL
		$vars['url'] = $CONFIG->url;
		
		ob_start();
		
		$content = "";
		
		ob_start();
			trigger_hook('view', 'prepend', array('view' => $view, 'vars' => $vars), $content);
		$content .= ob_get_clean();
		    
    	$view_location = get_view_location($view, $viewtype);
    	
	    if (
	    	(file_exists($view_location . "{$viewtype}/{$view}.php")) && 
	    	(!include($view_location . "{$viewtype}/{$view}.php"))
	    ) {
        	if ($viewtype != "default") 
        	{
	            if (!include($view_location . "default/{$view}.php")) 
	            {
	                if ($CONFIG->debug)
	                	log_echo("View: $view does not exist", 'DEBUG');
	            }
	        }

	    } 
	    else if (!file_exists($view_location . "{$viewtype}/{$view}.php")) 
	    {
    		if ($CONFIG->debug) 
	    		log_echo("View: $view_location{$viewtype}/{$view}.php does not exist", 'DEBUG');
	    }

		// Save the output buffer into the $content variable
		$content .= ob_get_clean();
		
		ob_start();
			trigger_hook('view', 'extend', array('view' => $view, 'vars' => $vars), $content);
		$content .= ob_get_clean();
		
		return trigger_hook('view', 'display', array('view' => $view, 'vars' => $vars), $content);
	}
	
	/**
	 * Test to see if a view exists.
	 *
	 * @param string $view The view
	 * @param string $viewtype Optional viewtype
	 * @return bool
	 */
	function view_exists($view, $viewtype = 'default')
	{
		global $CONFIG;
		
		if (isset($CONFIG->views->locations[$viewtype][$view]))
			return true;
		
		$result = view($view, $viewtype);
		if ($result)
			return true;
		
		return false;
	}
	
	/**
	 * Register a view extension according to a given register.
	 * 
	 * This should not be called directly, use either extend_view() or prepend_view().
	 *
	 * @param string $register
	 * @param string $view
	 * @param string $view_extension
	 * @param int $priority
	 * @param mixed $viewtype
	 */
	function extend_prepend_view($register, $view, $view_extension, $priority = 500, $viewtype = false)
	{
		global $CONFIG;
		
		if (!$viewtype)
			$viewtype = get_viewtype();
		
		if (!isset($CONFIG->view_extensions))
			$CONFIG->view_extensions = array();

		if (!isset($CONFIG->view_extensions[$viewtype]))
			$CONFIG->view_extensions[$viewtype] = array();
			
		if (!isset($CONFIG->view_extensions[$viewtype][$view]))
			$CONFIG->view_extensions[$viewtype][$view] = array();
			
		if (!isset($CONFIG->view_extensions[$viewtype][$view][$register]))
			$CONFIG->view_extensions[$viewtype][$view][$register] = array();
		
		// Find the priority
		while (isset($CONFIG->view_extensions[$viewtype][$view][$register][$priority]))
			$priority++;
			
		$CONFIG->view_extensions[$viewtype][$view][$register][$priority] = "$view_extension";
		ksort($CONFIG->view_extensions[$viewtype][$view][$register]);
	}
	
	/**
	 * Extend a view with a given view extension.
	 *
	 * @param string $view The view
	 * @param string $view_extension The extension
	 * @param int $priority The priority that this view will be registered
	 * @param mixed $viewtype The view type type of the view, leave blank for the current view
	 */
	function extend_view($view, $view_extension, $priority = 500, $viewtype = false)
	{
		return extend_prepend_view('extend', $view, $view_extension, $priority, $viewtype);
	}
	
	/**
	 * Prepend a view with a given view with a given view extension.
	 *
	 * @param string $view The view
	 * @param string $view_extension The extension
	 * @param int $priority The priority that this view will be registered
	 * @param mixed $viewtype The view type type of the view, leave blank for the current view
	 */
	function prepend_view($view, $view_extension, $priority = 500, $viewtype = false)
	{
		return extend_prepend_view('prepend', $view, $view_extension, $priority, $viewtype);
	}
	
	/**
	 * Output a page using a page shell.
	 *
	 * @param string $title The title
	 * @param string $body Body
	 * @param bool $return_value If true, output is returned as a return value, otherwise it is echoed.
	 * @return null|string depending on the value of $return_value
	 */
	function output_page($title, $body, $return_value = false) {

		// Draw the page
		$output = view('page/shell', array(
											'title' => $title,
											'body' => $body
										  )
									);
									
		if (!$return_value) {		
			// Break long output to avoid a php performance bug							
			$split_output = str_split($output, 1024);
	
	    	foreach($split_output as $chunk)
	        	echo $chunk;
		}
		else
			return $output;
	}
	
	/**
	 * Hook handler for view extensions.
	 * This is called internally by the system.
	 *
	 * @param string $class
	 * @param stromg $hook
	 * @param array $parameters
	 * @param string $return_value
	 */
	function __output_view_extension_handler($class, $hook, $parameters, $return_value) 
	{
		global $CONFIG;
		
		if (
			($class == 'view') &&
			(($hook == 'prepend') || ($hook == 'extend'))
		)
		{
			$view = $parameters['view'];
			$vars = $parameters['vars'];
			$viewtype = get_viewtype(); 
			
			if (isset($CONFIG->view_extensions[$viewtype][$view][$hook]))
			{
				$tmp = null;
				
				foreach ($CONFIG->view_extensions[$viewtype][$view][$hook] as $extension)
					echo view($extension, $vars, $viewtype);
			
			}
		}
	}
	
	/**
	 * Initialise the output and views system
	 *
	 */
	function output_boot()
	{
		// Handle view extensions
		register_hook('view', 'prepend', '__output_view_extension_handler');
		register_hook('view', 'extend', '__output_view_extension_handler');
	}
	
	register_event('system', 'boot', 'output_boot', 4);
