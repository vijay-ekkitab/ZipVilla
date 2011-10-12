<html>
<?php
	ini_set('display_errors', 1);
	ini_set('log_errors', 1);
	ini_set('error_log', dirname(__FILE__) . '/error_log.txt');
	error_reporting(E_ALL);
	include_once("../db/types.php");	
	include_once("../db/listings_manager.php");
	include_once("../db/index_manager.php");
	$type = array_key_exists('type',$_REQUEST) ? $_REQUEST['type'] : null;	
	$mid = null;	
	$error = null;
	if($type != null) {
		$tm = TypeManager::instance();
		$tp = $tm->getType($type);
		if($tp == null) {
			$error =  "<h3>Unknown type : " . $type ."</h3>";		
		} else {
			$obj = $tp->makeObject($_POST);
			//echo var_dump($obj);
			$lm = ListingsManager::instance();
			$res = $lm->insert($type,$obj,false);
			#echo "<b>Return value from insertion&nbsp;". var_dump($res)."</b><br>";
			$mid = $res['_id'];
			#echo "<b>returned obj id from insertion&nbsp;".$mid->__toString()."</b><br>";
			$im = IndexManager::instance();
			$c = $im->index($res);
			#echo "<b>returned status from indexing&nbsp;".$c."</b><br>";
			echo '<head><meta http-equiv="Refresh" content="0; url=' . 'details.php?b=true&id='.$mid .'"/></head>';
		}
	}
?>
<?php if ($mid == null) { ?>
	<body>
	<h3>Error type is not specified</h3>
	<input type="button value="Back" onclick="javascript:window.back();">
	</body>
<?php } ?>
</html>


