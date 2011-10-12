<?php
	ini_set('display_errors', 1);
	ini_set('log_errors', 1);
	ini_set('error_log', dirname(__FILE__) . '/error_log.txt');
	error_reporting(E_ALL);
	include("../db/types.php");	
	$type = array_key_exists('type',$_REQUEST) ? $_REQUEST['type'] : null;	
?>
<html>
	<head>
		<title>Insert listing </title>
	</head>
	<body>
		<div>
			<form method="post" action="attributes.php" name="typeForm">
				<label for="type"><b>Choose type of listing to enter</b></label>
				<select name="type"  onChange="document.typeForm.submit();">
					<option <?php if($type == null) {?> selected <?php } ?>>Select...</option>
					<option <?php if($type == 'resort') {?> selected <?php } ?> value="resort">Resort</option>
					<option <?php if($type == 'hotel') {?> selected <?php } ?> value="hotel">hotel</option>
					<option <?php if($type == 'farm_house') {?> selected <?php } ?> value="farm_house">Farm House</option>
					<option <?php if($type == 'apartment') {?> selected <?php } ?> value="apartment">Apartment</option> 
				</select>
			</form>
		</div>
		<?php 
			$type = array_key_exists('type',$_REQUEST) ? $_REQUEST['type'] : null;
			if($type != null) { 
				$tm = TypeManager::instance();
				$tp = $tm->getType($type);
		?>	
			<div style="float:left;clear:both">
			<?php if($tp == null) { ?>
				<h3><?php echo $type ." does not exist!" ?></h3>
			<?php } 
				else {
			?>
 				<table width="600px" border="0"><form method="post" action="insert.php?type=<?php echo $type;?>">
			<?php 
				$attrs = $tp->getAttributes();
				foreach($attrs as $name => $attr) { 
			?>
					<tr>
					    <td>
					    <label style="width:10%;" for="<?php echo $attr->getName(); ?>"><?php echo $attr->getName(); ?></label>
					    </td>
					    <td>
					    <input style="width:90%;" type="text" name="<?php echo $attr->getName(); ?>">
					    </td>	
					</tr>
				<?php } ?>
			<?php } ?>
				</table>
				<input type="submit" value="insert">
				</form>
			</div>
		<?php } ?>
	</body>
</html>

