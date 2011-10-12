<?php
include_once("ZipVilla/Helper/ListingsManager.php");
include_once("ZipVilla/Utils.php");

class ListingsManagerTest extends PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        $this->bootstrap = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini');
        parent::setUp();
        $seed_script = APPLICATION_PATH . "/../tests/library/ZipVilla/test_schema.js";
        shell_exec("/usr/bin/mongo " . $seed_script);
    }

    function testInsert() {
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
        $doc = $res->getDoc();
        //echo json_encode($doc) . "\n";
    }

    function testQueryAndDelete() {
        $tname = "farm_house";
	    $vals = array();
	    $vals['size'] = "15 acres"; 
	    $vals['activities'] = array('bonfire', 'nature walk','trekking');
	    $vals['specalities'] = array('house wines');
	    $vals['userId'] = "owner_5678";
	    $vals['streetNumber'] = "456-D";
	    $vals['state'] = "Goa";
	    $vals['country'] = "India";
	    $vals['pincode'] = "5676799";
	    $vals['title'] = "The Beach Home"; 
	    $lm = new ZipVilla_Helper_ListingsManager();
	    $res = $lm->insert($tname,$vals);
	    $vals = array();
	    $vals['size'] = "20 acres"; 
	    $vals['activities'] = array('kayaking');
	    $vals['specalities'] = array('karaoke');
	    $vals['userId'] = "owner_1000";
	    $vals['streetNumber'] = "100";
	    $vals['state'] = "Trivandrum";
	    $vals['country'] = "India";
	    $vals['pincode'] = "601023";
	    $vals['title'] = "The Nest"; 
	    $res = $lm->insert($tname,$vals);
	    $res = $lm->query(array("type" => "farm_house"));
        $this->assertEquals(2, count($res), "Query did not retrieve all inserted objects."); 
        $objId = $res[0]->id;
        $lm->delete($objId);
	    $res = $lm->query(array("type" => "farm_house"));
        $this->assertEquals(1, count($res), "Query did not retrieve by 'type'"); 
        $this->assertNotEquals($objId, $res[0]->id, "Query did not retrieve the correct object. May be a delete failure."); 
        $objId = $res[0]->id;
	    $res = $lm->queryById($objId, TRUE);
        $this->assertNotNull($res, "Query by Id did not retrieve object."); 
        $this->assertEquals($objId, $res['id'], "Query did not retrieve the correct object."); 
    }

    function testGetValue() {
	    $val = array('a'=>array('b'=>10) , 'c' => "this is");
	    $x = getValue($val,'a_b');
        $this->assertEquals(10, $x, "GetValue did not return the right value."); 
	    $x = getValue($val,'c');
        $this->assertEquals("this is", $x, "GetValue did not return the right value."); 
    }

}
?>
