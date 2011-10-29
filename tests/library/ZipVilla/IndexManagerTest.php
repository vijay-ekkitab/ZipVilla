<?php
include_once("ZipVilla/Helper/ListingsManager.php");
include_once("ZipVilla/Helper/IndexManager.php");
include_once("ZipVilla/Helper/SearchManager.php");
include_once("ZipVilla/Utils.php");

class IndexManagerTest extends PHPUnit_Framework_TestCase
{

	public function setUp()
	{
		$this->bootstrap = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini');
		parent::setUp();
		$seed_script = APPLICATION_PATH . "/../tests/library/ZipVilla/test_schema.js";
		shell_exec("/usr/bin/mongo " . $seed_script);
		//clear all the indexed document from solr
		$im = new IndexManager();
		$res = $im->delete();
	}

	function _insertListing() {
		$lm = new ZipVilla_Helper_ListingsManager();
		$res = $lm->query();
		$tname = "hotel";
		$vals = array();
		$vals['size'] = "15 acres"; 
		$vals['activities'] = array('bonfire', 'nature walk','trekking');
		$vals['specalities'] = array('house wines');
		$vals['userId'] = "owner_5678";
		$vals['streetNumber'] = "456-D";
		$vals['street'] = "Calungute Road";
		$vals['line1'] = "North Gao";
		$vals['line2'] = "Beach resort";
		$vals['city'] = "Mapusa";
		$vals['state'] = "Goa";
		$vals['country'] = "India";
		$vals['pincode'] = "5676799";
		$vals['neighbourhood'] = "near church";
		$vals['lat'] = 55.6;
		$vals['long'] = 65.8;
		$vals['amenities'] = array('health club','sauna');
		$vals['title'] = "The Beach Home"; 
		$description = "very nice  place near fort";
		$vals['description'] = $description;
		$vals['images'] = array('content/one1.jpg','content/two1.jpg');
		$vals['thumbnail'] = 'content/thumb1.jpg';
		$vals['url'] = 'http://mycompany.com';
		$vals['rate'] = 4600;
		$vals['rateType'] = "double occupancy";
		$vals['maxGuests'] = 6;

		$lm = new ZipVilla_Helper_ListingsManager();
		$res = $lm->insert($tname,$vals);

		$this->assertEquals("hotel", $res->type, "Wrong 'Type' for inserted object."); 
		$this->assertEquals("Goa", $res->address['state'], "Wrong 'State' for inserted object."); 
		$this->assertEquals($description, $res->description, "Wrong 'Description' for inserted object.");
		$this->assertTrue($res->id != null);
		return $res->id;
		
	}
	public function testIndexById() {
		$id = $this->_insertListing();
		$im = new IndexManager();
		$res = $im->indexById($id);
		$this->assertTrue($res->success());
		$sm = new SearchManager();
		$q = array("address__city"=>'Mapusa');
		$fds = array('address__city','id','title');
		$docs = $sm->search($q,$fds);
		$this->assertTrue($docs != null);
		//print_r($docs);
		$this->assertTrue(count($docs) == 1);
		$d = $docs[0];
		$t = $d['title'];
		$this->assertEquals("The Beach Home",$t[0],"Wrong search fired. fields not matching");
	}
	function _insertListing1() {
		$lm = new ZipVilla_Helper_ListingsManager();
		$res = $lm->query();
		$tname = "resort";
		$vals = array();
		$vals['size'] = "25 acres"; 
		$vals['activities'] = array('bonfire', 'nature walk','trekking');
		$vals['specalities'] = array('oil therapy');
		$vals['userId'] = "owner_5656";
		$vals['streetNumber'] = "700-D";
		$vals['street'] = "Waynad Road";
		$vals['line1'] = "Thomas Church";
		$vals['line2'] = "Ayurveda resort";
		$vals['city'] = "Thrissur";
		$vals['state'] = "Kerala";
		$vals['country'] = "India";
		$vals['pincode'] = "5656789";
		$vals['neighbourhood'] = "near lake";
		$vals['lat'] = 65.6;
		$vals['long'] = 85.8;
		$vals['amenities'] = array('massage','sauna','astrology');
		$vals['title'] = "Dhanvantari Mahal"; 
		$description = "very nice  place to relax and heal";
		$vals['description'] = $description;
		$vals['images'] = array('content/a1.jpg','content/a2.jpg');
		$vals['thumbnail'] = 'content/t1.jpg';
		$vals['url'] = 'http://ayush.com';
		$vals['rate'] = 8600;
		$vals['rateType'] = "suite";
		$vals['maxGuests'] = 6;

		$lm = new ZipVilla_Helper_ListingsManager();
		$res = $lm->insert($tname,$vals);

		$this->assertEquals("resort", $res->type, "Wrong 'Type' for inserted object."); 
		$this->assertEquals("Kerala", $res->address['state'], "Wrong 'State' for inserted object."); 
		$this->assertEquals($description, $res->description, "Wrong 'Description' for inserted object.");
		$this->assertTrue($res->id != null);
		return $res->id;
		
	}
	public function testIndexByObject() {
	 	$id = $this->_insertListing1();
		$lm = new ZipVilla_Helper_ListingsManager();
		$res = $lm->queryById($id);
		$im = new IndexManager();
		$res = $im->index($res);
		$this->assertTrue($res->success());
		$sm = new SearchManager();
		$q = array('address__state'=>'Kerala');
		$fds = array('address__state','address_city','id','title');
		$docs = $sm->search($q,$fds);
		$this->assertTrue($docs != null);
		$this->assertTrue(count($docs) == 1);
		//print_r($docs);
		$d = $docs[0];
		$t = $d['title'];
		$this->assertEquals("Dhanvantari Mahal",$t[0],"Wrong search fired. fields not matching");
	}
}		
?>
