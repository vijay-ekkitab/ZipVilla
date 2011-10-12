<?php
	ini_set('display_errors', 1);
	ini_set('log_errors', 1);
	ini_set('error_log', dirname(__FILE__) . '/error_log.txt');
	error_reporting(E_ALL);
	include_once("../db/listings_manager.php");
	include_once("../db/utils.php");
	$id = null;	
	$b = false;
	if(array_key_exists('id',$_REQUEST)) {
		$id = $_REQUEST['id'];
	}
	if(array_key_exists('b',$_REQUEST)) {
		$b = true;
	}
	$obj = null;
	if($id != null) {
		$lm = ListingsManager::instance();
		$obj = $lm->queryById($id,true);
	}
?> 
<html>
	<head>
		<title>Listing details</title>
	</head>
	<body>
		<p><b> You can search for listings using keywords </b></p>
		<div style="clear:both;">
			<?php if($obj != null) { ?>
			<table>
				<?php foreach ($obj as $l => $v) { ?>
					<tr>
						<td><?php echo $l;?></td><td><?php echo $v; ?></td>
					</tr>
				<?php } ?>	
			</table>
			<?php } else { ?>
				<b><?php echo $id; ?> did not match any listing in the database </b>
			<?php } ?>
		</div>
		<div><input 
			type="button" 
			value="Insert Another Listing..." 
			onclick="<?php 
					if(!$b) { echo 'window.location.href=\'attributes.php?type='. $obj['type']. "'"; }
					else {
						echo 'window.back();';
					}
			?>"
		      >
	</body>
</html>
