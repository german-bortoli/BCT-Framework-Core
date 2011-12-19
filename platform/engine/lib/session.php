<?php
	/**
	 * @file
	 * Session library.
	 * 
	 * Functions for initialising and maintaining a session.
	 * 
	 * @package core
	 * @license The MIT License (see LICENCE.txt), other licenses available.
	 * @author Marcus Povey <marcus@marcus-povey.co.uk>
	 * @copyright Marcus Povey 2009-2011
	 * @link http://platform.barcamptransparency.org/
	 */

	
	/**
	 * Initialise a session.
	 * 
	 * Note: Session must be started after plugins have been loaded as some plugins provide
	 * classes which must be declared first.
	 *
	 */
	function session_init()
	{
		session_start();
		
		// TODO: More session code
	}
	
	register_event('system', 'init', 'session_init', 1);
