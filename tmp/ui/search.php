<?php
	ini_set('display_errors', 1);
	ini_set('log_errors', 1);
	ini_set('error_log', dirname(__FILE__) . '/error_log.txt');
	error_reporting(E_ALL);
	include_once("../db/index_manager.php");
	include_once("../db/utils.php");
	$query = null;	
	$emptyQuery = false;
	if(array_key_exists('q',$_REQUEST)) {
		$query = $_REQUEST['q'];
		if($query == null) { $emptyQuery = true;}
	} 
	$res = null;
	$fds = array('id','title','description','address_city','amenities');
	$displayFds = array('Title','Description','City','Amenities');	
	$fdsToShow = array('title','description','address_city','amenities');
	if($query != null) {
		$qr = array();
		foreach ($fds as $fd) {
			if($fd != 'id') {
				$qr[$fd] = $query;
			}
		}
		//do a query using index manager
		$im = IndexManager::instance();
		$res = $im->search($qr,$fds);
	}
?>
<html>
	<head>
		<title>Search Listings </title>
	</head>
	<body>
		<p><b> You can search for listings using keywords </b></p>
		<div style="clear:both;">
			<form method="GET" action="search.php" name="searchForm">
			<table width="600px" border="0">
			<tr>
				<td>
					<input style="width:90%;" type="text" name="q" value="<?php echo $query;?>">
				</td>
				<td>
					<input type="submit" value="Search">
				</td>
			<tr>
				<td colspan="2"><i>Search by name, city,description, amemities</i></td>
			</tr>
			</table>

		</div>
		<div style="clear:both;">
			<?php if($res != null) { ?>
				<!-- results -->
				<table border="1" cellspacing="0">
					<tr>
						<?php foreach ($displayFds as $fd) { ?>
							<th><i><?php echo $fd; ?></i><th>
						<?php } ?>
					</tr>
					<?php foreach ($res as $doc) { ?>
					<tr>
						<?php foreach ($fdsToShow as $fd) { ?>
							<td>
								<?php if($fd != 'title') { ?>
									<?php echo toString($doc[$fd]); ?>
								 <?php } else { ?>
									<a href="details.php?id=<?php echo $doc['id'];?>">
									<?php echo toString($doc[$fd]); ?>
									</a>
								 <?php } ?>
							<td>
						<?php } ?>
					</tr>					
					<?php } ?>				
				</table>
			<?php } ?>
			<?php if($emptyQuery) { ?>
				<p><b> There are no results matching your criteria</b></p>
			<?php } ?>
		</div>
		<div><input type="button" value="Insert New Listing..." onclick="window.location.href='attributes.php';">
