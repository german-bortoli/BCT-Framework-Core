<?php
	/**
	 * @file
	 * Functions handling input.
	 * 
	 * @package core
	 * @license The MIT License (see LICENCE.txt), other licenses available.
	 * @author Marcus Povey <marcus@marcus-povey.co.uk>
	 * @copyright Marcus Povey 2009-2011
	 * @link http://platform.barcamptransparency.org/
	 */

	/**
	 * Retrieve input.
	 *
	 * @param string $variable The variable to retrieve.
	 * @param mixed $default Optional default value.
	 * @param bool $filter_results Filter the results?
	 * @return mixed
	 */
	function input_get(
		$variable, 
		$default = null, 
		$filter_results = true
	)
	{
		global $CONFIG;
		
		if (isset($CONFIG->_INPUT[$variable])) 
		{
			$var = $CONFIG->_INPUT[$variable];
			
			if ($filter_result)
				$var = trigger_hook('input', 'filter', NULL, $var);
				
			return $var;
		}
		
		if (isset($_REQUEST[$variable])) 
		{
			
			if (is_array($_REQUEST[$variable])) 
				$var = $_REQUEST[$variable];
			else 
				$var = trim($_REQUEST[$variable]);
			
			if ($filter_results)
				$var = trigger_hook('input', 'filter', NULL, $var);
			
			return $var;
		}

		return $default;
	}
	
	/**
	 * Set an input value
	 *
	 * @param string $variable The name of the variable
	 * @param mixed $value its value
	 */
	function input_set($variable, $value) 
	{
		global $CONFIG;
		
		if (!isset($CONFIG->_INPUT))
			$CONFIG->_INPUT = array();
					
		if (is_array($value))
		{
			foreach ($value as $key => $val)
				$value[$key] = trim($val);
			
			$CONFIG->_INPUT[trim($variable)] = $value;
		}
		else
			$CONFIG->_INPUT[trim($variable)] = trim($value);	
	}
	
	/**
	 * Get the contents of an uploaded file.
	 *
	 * @param file $filename The variable containing the file data
	 * @param bool $filter_results Do we want to filter the results?
	 * @return file|false
	 */
	function input_get_file($filename, $filter_results = true) 
	{
		if (isset($_FILES[$filename]) && $_FILES[$filename]['error'] == 0) 
		{
			if ($filter_results)
				return trigger_hook(
					'input', 'filter:file', 
					$_FILES[$filename], 
					file_get_contents($_FILES[$filename]['tmp_name'])
				);
			else
				return file_get_contents($_FILES[$filename]['tmp_name']);
		}
		
		return false;
	}
	
	/**
	 * Get raw POST request data.
	 *
	 * @param bool $filter_results Pass POST data through a filter?
	 * @return string|false
	 */
	function input_get_post($filter_results = true) 
	{
		global $GLOBALS;
			
		$post = '';
		
		if (isset($GLOBALS['HTTP_RAW_POST_DATA']))
			$post = $GLOBALS['HTTP_RAW_POST_DATA'];
	
		// If always_populate_raw_post_data is switched off, attempt another method.
		if (!$post) 
			$post = file_get_contents('php://input');
		
		// If we have some results then return them
		if ($post)
		{
			if ($filter_results)
				return trigger_hook('input', 'filter:post', null, $post);
			else
				return $post;
		}
			
		return false;
	}

	/**
	 * Get an image from an uploaded file variable.
	 *
	 * Retrieves an image from an uploaded file, optionally resizing it.
	 *
	 * @param string $filename The variable containing the file data
	 * @param int $maxwidth Optional maximum width
	 * @param int $maxheight Optional maximum height
	 * @param bool $square Should the image be made square?
	 * @param int $x1 Optional coordinate to use part of image instead.
	 * @param int $y1 Optional coordinate to use part of image instead.
	 * @param int $x2 Optional coordinate to use part of image instead.
	 * @param int $y2 Optional coordinate to use part of image instead.
	 */
	function input_get_image($filename, $maxwidth = 0, $maxheight = 0, $square = false, $x1 = 0, $y1 = 0, $x2 = 0, $y2 = 0)
	{
	    $file_data = input_get_file($filename, false);
	    $file_details = $_FILES[$filename];

	    if ((!$file_data) || (!$file_details)) return false;

	    if ($imgsizearray = getimagesize($file_details['tmp_name']))
	    {
		$width = $imgsizearray[0];
		$height = $imgsizearray[1];
		$newwidth = $width;
		$newheight = $height;
		if (!$maxwidth) $maxwidth = $width;
		if (!$maxheight) $maxheight = $height;

		if ($square)
		{
		    if ($width < $height)
			$height = $width;
		    else
			$width = $height;

		    $newwidth = $width;
		    $newheight = $height;
		}

		if ($width > $maxwidth) {
			$newheight = floor($height * ($maxwidth / $width));
			$newwidth = $maxwidth;
		}

		if ($newheight > $maxheight) {
			$newwidth = floor($newwidth * ($maxheight / $newheight));
			$newheight = $maxheight;
		}

		$accepted_formats = array(
		    'image/jpeg' => 'jpeg',
		    'image/png' => 'png',
		    'image/gif' => 'gif'
		);

		if (array_key_exists($imgsizearray['mime'], $accepted_formats))
		{
		    $function = "imagecreatefrom" . $accepted_formats[$imgsizearray['mime']];

		    $newimage = imagecreatetruecolor($newwidth, $newheight);

		    if (is_callable($function) && $oldimage = $function($file_details['tmp_name']))
		    {
			if ($square)
			{
			    if ($x1 == 0 && $y1 == 0 && $x2 == 0 && $y2 ==0) {
				$widthoffset = floor(($imgsizearray[0] - $width) / 2);
				$heightoffset = floor(($imgsizearray[1] - $height) / 2);
			    } else {
				$widthoffset = $x1;
				$heightoffset = $y1;
				$width = ($x2 - $x1);
				$height = $width;
			    }
			} else {
			    if ($x1 == 0 && $y1 == 0 && $x2 == 0 && $y2 ==0) {
				$widthoffset = 0;
				$heightoffset = 0;
			    } else {
				$widthoffset = $x1;
				$heightoffset = $y1;
				$width = ($x2 - $x1);
				$height = ($y2 - $y1);
			    }
			}

			if ($square) {
			    $newheight = $maxheight;
			    $newwidth = $maxwidth;
			}

			imagecopyresampled($newimage, $oldimage, 0,0,$widthoffset,$heightoffset,$newwidth,$newheight,$width,$height);

			// Add filters to image?
			$newimage = trigger_hook(
				'input', 'filter:image',
				array(
				    'image' => $newimage,
				    'image_file_details' => $file_details
				),
				$newimage
			);

			ob_start();
			imagepng($newimage);
			return ob_get_clean();
		    }
		}
	    }

	    return false;

	}