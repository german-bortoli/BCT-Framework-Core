<?php 
	$url = $vars['item']->getUrl();
	$created_ts = $vars['item']->created_ts;
	
	// Try and work out a title and description
	$title = $vars['item']->title;
	$desc = $vars['item']->description;

	if ($vars['item']->canView()) {
?>
<item>
	<guid isPermaLink='true'><?php echo htmlspecialchars($url); ?></guid>
	<pubDate><?php echo date("r", $created_ts); ?></pubDate>
	<link><?php echo htmlspecialchars($url); ?></link>
	<title><![CDATA[<?php echo $title; ?>]]></title>
	<description><![CDATA[<?php  
		if ($desc)
			echo $desc;
		else
		{
			foreach ($vars['item'] as $key => $val)
			{
				 echo "$key: ". var_export($val) . "\n";
			}
		}
	?>]]></description>
	<?php echo view('data/extensions/item', $vars); ?>
</item>
<?php } ?>