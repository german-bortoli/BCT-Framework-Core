<?php
	/**
	 * Platform RSS page shell.
	 * 
	 * @package core
	 * @license The MIT License (see LICENCE.txt), other licenses available.
	 * @author Marcus Povey <marcus@marcus-povey.co.uk>
	 * @copyright Marcus Povey 2009-2012
	 * @link http://platform.barcamptransparency.org/
	 */

	header("Content-Type: text/xml");
	
	echo "<?xml version='1.0'?>\n";
			
	// Remove RSS from URL
	$url = str_replace('?view=rss','',current_page_url());
	$url = str_replace('&view=rss','',$url);
?>
<rss version="2.0" xmlns:dc="http://purl.org/dc/elements/1.1/" <?php echo view('page/extensions/xmlns'); ?> >
	<channel>
		<title><![CDATA[<?php echo $vars['title']; ?>]]></title>
		<link><?php echo htmlentities($url); ?></link>
		<?php

			echo $vars['body'];
		
		?>
	</channel>
</rss>