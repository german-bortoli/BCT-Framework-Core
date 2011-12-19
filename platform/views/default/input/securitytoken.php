<?php
	/**
	 * Generate security tokens.
	 * 
	 * @package core
	 * @license The MIT License (see LICENCE.txt), other licenses available.
	 * @author Marcus Povey <marcus@marcus-povey.co.uk>
	 * @copyright Marcus Povey 2009-2011
	 * @link http://platform.barcamptransparency.org/
	 */


	$ts = time();
	$token = action_token_generate($ts);
	
	echo view('input/hidden', array('name' => '__to', 'value' => $token));
	echo view('input/hidden', array('name' => '__ts', 'value' => $ts));
?>
<?php /*
<script type="text/javascript">
//<![CDATA[
	window.setInterval(
		function () {
			alert('<?php echo _echo('action:token:timeout'); ?>')
		}
	, 3600000);
//]]>
</script>
*/
?>