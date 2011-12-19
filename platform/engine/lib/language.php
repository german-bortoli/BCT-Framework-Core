<?php
	/**
	 * @file
	 * Language library.
	 * 
	 * Handle strings, languages and translations.
	 * 
	 * @package core
	 * @license The MIT License (see LICENCE.txt), other licenses available.
	 * @author Marcus Povey <marcus@marcus-povey.co.uk>
	 * @copyright Marcus Povey 2009-2011
	 * @link http://platform.barcamptransparency.org/
	 * 
	 * @todo Caching
	 */

	/**
	 * Load languages from a path.
	 *
	 * @param string $path Path to load languages from.
	 * @param bool $load_all If true, all found language files are loaded, if false only 
	 * 				the default and the current language is loaded.
	 */
	function load_languages($path, $load_all = false)
	{
		global $CONFIG;
		
		if (!isset($CONFIG->_LANGUAGE_PATHS)) 
			$CONFIG->_LANGUAGE_PATHS = array();
			
		$CONFIG->_LANGUAGE_PATHS[$path] = true;
		
		$current_language = language_get_current();
		$default_language = language_get_default();
		
		if ((isset($CONFIG->debug)) && ($CONFIG->debug == true))
			log_echo("Languages loaded from : $path", 'DEBUG');
						
		if ($handle = opendir($path)) 
		{
			while ($language = readdir($handle)) 
			{
				if (
					((in_array($language, 
						array(
							"$default_language.php", // Default language
							"$current_language.php"  // Current language
						)
					))) ||
					(($load_all) && (strpos($language, '.php')!==false)) 
				)
					include_once($path . $language);			
			}
		}
	}

	/**
	 * Register a translation array
	 *
	 * @param array $array Language array
	 * @param string $lang Language code
	 * @return bool
	 */
	function register_language(array $array, $lang = 'en')
	{
		global $CONFIG;
	
		if ((!$array) || (count($array)==0))
			return false;
			
		if (!isset($CONFIG->_LANGUAGES))
			$CONFIG->_LANGUAGES = array();
		
		if (!isset($CONFIG->_LANGUAGES[$lang]))
			$CONFIG->_LANGUAGES[$lang] = $array;
		else
			$CONFIG->_LANGUAGES[$lang] = $array + $CONFIG->_LANGUAGES[$lang];
			
		return true;
	}

	/**
	 * Echo a translated string.
	 * 
	 * If a language code could not be found, the untranslated key is returned.
	 *
	 * @param string $key Language key
	 * @param string $lang Language code
	 * @return string
	 */
	function _echo($key, $lang = 'en')
	{
		global $CONFIG;

		if (isset($CONFIG->_LANGUAGES[$lang][$key]))
			return $CONFIG->_LANGUAGES[$lang][$key];

		log_echo("Missing translation: $key ($lang)");

		return $key;
	}
	
	/**
	 * Return whether a given key exists.
	 * @param $key Key
	 * @param $lang Language, default 'en'
	 * @return bool
	 */
	function language_key_exists($key, $lang = 'en')
	{
		global $CONFIG;

		return isset($CONFIG->_LANGUAGES[$lang][$key]);
	}
	
	/**
	 * Return the default language.
	 */
	function language_get_default()
	{
		$language = 'en';
		
		return trigger_hook('language', 'default', NULL, $language);
	}
	
	/**
	 * Get the current language;
	 *
	 * @return string Language code.
	 */
	function language_get_current()
	{
		global $CONFIG;
		
		if (isset($CONFIG->language))
			return $CONFIG->language;
			
		return 'en';
	}
	
	/**
	 * Set the current language.
	 *
	 * @param string $language Set current language.
	 */
	function language_set_current($language = 'en')
	{
		global $CONFIG;
		
		$CONFIG->language = $language;
	}
	
	/**
	 * Language system initialisation.
	 */
	function language_boot()
	{
		global $CONFIG;
		
		language_set_current(language_get_default());
		
		load_languages($CONFIG->docroot . 'languages/');
	}
	
	register_event('system', 'boot', 'language_boot', 3);
