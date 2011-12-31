<?php
	/**
	 * @file
	 * Debug details view
	 * 
	 * @package core
	 * @license The MIT License (see LICENCE.txt), other licenses available.
	 * @author Marcus Povey <marcus@marcus-povey.co.uk>
	 * @copyright Marcus Povey 2009-2012
	 * @link http://platform.barcamptransparency.org/
	 */
?>
<div class="debug">
    <?php
    global $CONFIG;

    if ($dbengine = $CONFIG->_DATABASEENGINE) {

	$statistics = $dbengine->getStatistics();
	?>
    <div class="database">
	<b>Database</b>

	<ul>
		<?php
		foreach ($statistics as $key => $value)
		{
		?>
	    <li class="<?php echo $key; ?>"><span class="key"><?php echo $key; ?></span>: <?php
		    if (is_array($value)) {
			?>
		<ol>
		    <?php foreach ($value as $line => $entry) { ?>
		    <li class="line"><?php echo $line; ?>: <?php echo $entry; ?></li>
		    <?php } ?>
		</ol>
			<?php
		    }
		    else
			echo $value;
	    ?></li>
		<?php
		}
		?>
	</ul>
    </div>
	    <?php
	}
    ?>

    <div class="section log">
	<b>Log</b>
	<div class="log">
	    <ol>
	<?php
	    global $__DEBUG_LOG_HISTORY;

	    foreach ($__DEBUG_LOG_HISTORY as $entry)
	    {
	?>
	    <li class="<?php echo strtolower($entry['level']); ?>"><span class="level"><?php echo $entry['level']; ?></span>: <span class="message"><?php echo $entry['message']; ?></span></li>
	<?php
	    }
	?>
	    </ol>
	</div>
	
    </div>

    <div class="section config">
	<b>$CONFIG</b>
	<pre><?php print_r($CONFIG); ?></pre>
    </div>

    <div class="section server">
	<b>$_SERVER</b>
	<pre><?php print_r($_SERVER); ?></pre>
    </div>

    <div class="section session">
	<b>$_SESSION</b>
	<pre><?php print_r($_SESSION); ?></pre>
    </div>
</div>	