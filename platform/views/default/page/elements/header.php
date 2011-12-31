<?php
	/**
	 * @file
	 * Default view header block.
	 * 
	 * @package core
	 * @license The MIT License (see LICENCE.txt), other licenses available.
	 * @author Marcus Povey <marcus@marcus-povey.co.uk>
	 * @copyright Marcus Povey 2009-2012
	 * @link http://platform.barcamptransparency.org/
	 */

	global $version, $release, $codename, $CONFIG;

	// Set some headers (for APIs and other systemic processes)
	header("X-BCT-Version: $release; $version");
?>
<head>
	<meta charset="utf-8" />
	<meta name="bctengine" content="<?php echo "$release $codename ($version)"; ?>" />
	<title><?php
		if (!empty($vars['title'])) { 
			echo $vars['title'] . ' | ';
		}
		echo $CONFIG->name;
	?></title>
	<?php echo view('metatags', $vars); ?>
	<?php echo view('favicon', $vars); ?>
</head>