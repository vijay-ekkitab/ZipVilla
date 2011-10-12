<?php
include_once("../index_manager.php");
error_reporting(E_ALL);
ini_set('display_errors', '1');

function testIndex($id) {
	$im = IndexManager::instance();
	$c = $im->indexById($id);
	echo $c . " = code\n";
}
function testXML() {
	$response = 	'<?xml version="1.0" encoding="UTF-8"?><response><lst name="responseHeader">' .
			'<int name="status">0</int>'.
                         '<int name="QTime">28</int></lst></response>';
	$xml = new SimpleXMLElement($response);
	$status = $xml->xpath("/response/lst/int[@name='status']");
	$st = $status[0];
	echo $st . " is the status\n";
}
function testSearch() {
	$im = IndexManager::instance();
	$q = array("title"=>"srinivasam");
	$fds = array("title","address_line1","description");
	$docs = $im->search($q,$fds);
	foreach($docs as $doc) {
		echo json_encode($doc) . "\n";
	}
}
function testSearch1() {
	$im = IndexManager::instance();
	$q = array("address_line1"=>"koramangala");
	$fds = array("id","title","address_line1","description");
	$docs = $im->search($q,$fds);
	foreach($docs as $doc) {
		echo json_encode($doc) . "\n";
	}
}
function testSearch3() {
	$fds = array('title','description','address_city','address_neighourhood','amenities');
	$qr = array();
	foreach ($fds as $fd) {
		$qr[$fd] = "srinivasam";
	}
	//do a query using index manager
	$im = IndexManager::instance();
	$docs = $im->search($qr,$fds);
	if($docs == null) {
		echo "nothing has worked\n";
	} else {
		foreach($docs as $doc) {
			echo json_encode($doc) . "\n";
		}
	}
}

#testIndex("4e7ca3df02908fac2f000000");
#testIndex("4e7cb68d02908fb933000000");
#testXML();
#testSearch();
#testSearch1();
testSearch3();

?>
