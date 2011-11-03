<?php
include_once("ZipVilla/Helper/ListingsManager.php");
include_once("ZipVilla/Helper/IndexManager.php");
include_once("ZipVilla/Helper/SearchManager.php");
include_once("ZipVilla/Utils.php");

class SearchManagerTest extends PHPUnit_Framework_TestCase
{

	public function setUp()
	{
		$this->bootstrap = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini');
		parent::setUp();
		$seed_script = APPLICATION_PATH . "/../tests/library/ZipVilla/test_schema.js";
		shell_exec("/usr/bin/mongo " . $seed_script);
		//clear all the indexed document from solr
		$im = new ZipVilla_Helper_IndexManager();
		$res = $im->delete();
	}

	function _insertListing1() {
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
		$vals['amenities'] = array('wifi'=> '','sauna'=> '');
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
	function _insertListing2() {
		$lm = new ZipVilla_Helper_ListingsManager();
		$res = $lm->query();
		$tname = "resort";
		$vals = array();
		$vals['activities'] = array('bonfire', 'swimming');
		$vals['specalities'] = array('goan food');
		$vals['userId'] = "owner_5668";
		$vals['streetNumber'] = "456-D";
		$vals['street'] = "Calungute Road";
		$vals['line1'] = "North Gao";
		$vals['line2'] = "Beach resort";
		$vals['city'] = "Calungute";
		$vals['state'] = "Goa";
		$vals['country'] = "India";
		$vals['pincode'] = "5676719";
		$vals['neighbourhood'] = "near church";
		$vals['lat'] = 45.6;
		$vals['long'] = 75.8;
		$vals['amenities'] = array('health club'=> '','sauna' => '');
		$vals['title'] = "The Beach Home"; 
		$description = "right by the beach";
		$vals['description'] = $description;
		$vals['images'] = array('content/one11.jpg','content/two11.jpg');
		$vals['thumbnail'] = 'content/thumb11.jpg';
		$vals['url'] = 'http://mycompany1.com';
		$vals['rate'] = 8600;
		$vals['rateType'] = "suite offer";
		$vals['maxGuests'] = 6;

		$lm = new ZipVilla_Helper_ListingsManager();
		$res = $lm->insert($tname,$vals);

		$this->assertEquals("resort", $res->type, "Wrong 'Type' for inserted object."); 
		$this->assertEquals("Goa", $res->address['state'], "Wrong 'State' for inserted object."); 
		$this->assertEquals($description, $res->description, "Wrong 'Description' for inserted object.");
		$this->assertTrue($res->id != null);
		return $res->id;
		
	}
	public function _createData() {
		$im = new ZipVilla_Helper_IndexManager();
		$id = $this->_insertListing1();
		$res = $im->indexById($id);
		$this->assertTrue($res->success());
		$id = $this->_insertListing2();
		$res = $im->indexById($id);
		$this->assertTrue($res->success());			
	}
	public function testSearchFacets() {
		$this->_createData();
		$sm = new ZipVilla_Helper_SearchManager();
		$q = array("address__state"=>'Goa');
		$fds = array('address__city','id','title');
		$ffds = array('address__city','amenities');
		$results = $sm->search($q,$fds,$ffds);
		$this->assertTrue($results != null);
		$docs = $results['docs'];
		//print_r($docs);
		$this->assertTrue(count($docs) == 2);
		$d = $docs[0];
		$t = $d['title'];
		$this->assertEquals(1,count($t),"Wrong number of results");
		$facets = $results['facets'];
		//print_r($facets);
		$af = $facets['amenities'];
		$sc = $af['Sauna'];
		$this->assertEquals(2,$sc,"Wrong sauna count. expected 2.");
		$hc = $af['Health Club'];
		$this->assertEquals(1,$hc,"Wrong health club. expected 1.");
	}
}		
?>
