<?php
	/**
	 * @file
	 * URL handling functions.
	 *
	 * Some useful functions for handling URLS.
	 * 
	 * @package core
	 * @license The MIT License (see LICENCE.txt), other licenses available.
	 * @author Marcus Povey <marcus@marcus-povey.co.uk>
	 * @copyright Marcus Povey 2009-2012
	 * @link http://platform.barcamptransparency.org/
	 */

	/**
	 * Parse a piece of text and activate any URLs found.
	 *
	 * @param string $text Text body.
	 * @return text
	 */
	function parse_urls($text)
	{
		return preg_replace_callback('/(?<!=["\'])((ht|f)tps?:\/\/[^\s\r\n\t<>"\'\!\(\)]+)/i', 
       	create_function(
            '$matches',
            '
            	$url = $matches[1];
            	$urltext = str_replace("/", "/<wbr />", $url);
            	return "<a href=\"$url\" class=\"link\">$urltext</a>";
            '
        ), $text);
	}
	
	/**
	 * Parse a text string into a suitable string for inclusion on a URL string.
	 * 
	 * This function provides a URL friendly version of a title string for inclusion on 
	 * the URL of a page, helping with SEO.
	 * 
	 * To extend/replace filtering attach a hook to "filter", "friendlyurltitle" 
	 *
	 * @param string $title_string The title string
	 * @return string The filtered string, or if no filter provided then the unmodified string.
	 */
	function friendly_url_title($title_string)
	{
		return trigger_hook('filter', 'friendlyurltitle', null, $title_string);
	}