<?php
	/**
	 * @file
	 * Misc functions.
	 *
	 * Various utility functions which don't necessarily fit anywhere else.
	 * 
	 * @package core
	 * @license The MIT License (see LICENCE.txt), other licenses available.
	 * @author Marcus Povey <marcus@marcus-povey.co.uk>
	 * @copyright Marcus Povey 2009-2011
	 * @link http://platform.barcamptransparency.org/
	 */

	/**
	 * Produce a subset of an array, who's keys or values match wildcard values.
	 *
	 * @param array $haystack The array
	 * @param string $keymatch Key string, where * is a wildcard
	 * @param string $valuematch String matching value, where * is a wildcard.
	 * @return false|array of results
	 */
	function regexp_array_match(array $haystack, $keymatch = '*', $valuematch = '*')
	{
		$keymatch = str_replace('*','.*',$keymatch);
		$valuematch = str_replace('*','.*',$valuematch);
		$matches = array();

		foreach ($haystack as $key => $value)
		{
			// match or value match
			if (
				(preg_match("/^".$keymatch.'$/', $key)) &&
				(preg_match("/^".$valuematch.'$/', $value))
			)	
				$matches[$key] = $value;

		}
		
		if (count($matches))
			return $matches;

		return false;
	}
	
	/**
	 * Match a key against a wildcard query.
	 * @param array $haystack
	 * @param string $keymatch
	 * @return array|false
	 * @see regexp_array_match
	 */
	function regexp_array_key_match(array $haystack, $keymatch = '*')
	{
		$keymatch = str_replace('*','.*',$keymatch);
		$matches = array();

		foreach ($haystack as $key => $value)
		{
			// match or value match
			if (preg_match("/^".$keymatch.'$/', $key))	
				$matches[$key] = $value;

		}
		
		if (count($matches))
			return $matches;

		return false;
	}
	
	/**
	 * Match a key against a wildcard query.
	 * @param array $haystack
	 * @param string $keymatch
	 * @return array|false
	 * @see regexp_array_match
	 */
	function regexp_array_value_match(array $haystack, $valuematch = '*')
	{
		$valuematch = str_replace('*','.*',$valuematch);
		$matches = array();

		foreach ($haystack as $key => $value)
		{
			// match or value match
			if (preg_match("/^".$valuematch.'$/', $value))
				$matches[$key] = $value;

		}
		
		if (count($matches))
			return $matches;

		return false;
		
	}