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
    
    function testInsertAndUpdate() {
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
        $vals['amenities'] = array('health club' => 'This is a fantastic club',
                                   'sauna' => 'hot water', 'internet' => '');
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
        $this->assertTrue(in_array("Health Club", array_keys($res->amenities)), "Item not found in amenities list in inserted object."); 
        $this->assertFalse(in_array("Television", array_keys($res->amenities)), "Wrong item found in amenities list in inserted object.");
        $this->assertFalse(in_array("internet", array_keys($res->amenities)), "Wrong item found in amenities list in inserted object.");
        $this->assertEquals("hot water", $res->amenities['Sauna'], "Amenities item has wrong description."); 
        
        $vals = array();
        $vals['state'] = "Kerala";

        $res = $lm->update($res->id, $vals);
        $this->assertNotNull($res, "Update returned null."); 
        $this->assertEquals("Kerala", $res->address['state'], "Update did not correctly update the object."); 

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
	    $x = getValue($val,'a__b');
        $this->assertEquals(10, $x, "GetValue did not return the right value."); 
	    $x = getValue($val,'c');
        $this->assertEquals("this is", $x, "GetValue did not return the right value."); 
    }

    function testEnums() {
		$lm = new ZipVilla_Helper_ListingsManager();
		$res = $lm->getEnumOptions("amenities");
        $this->assertNotNull($res, "getEnumOptions returned null for valid name."); 
        $this->assertEquals(7, count($res), "getEnumOptions returned wrong number of valid options."); 
		$res = $lm->getEnumOptions("doesnotexist");
        $this->assertNull($res, "getEnumOptions returned valid for for incorrect name."); 
    }
    
    function testPriceModel() {
		
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
	    $vals['amenities'] = array('health club' => 'This is a fantastic club',
                                   'sauna' => 'hot water', 'internet' => '');
	    $vals['title'] = "The Beach Home"; 
        $description = "very nice  place near fort";
	    $vals['description'] = $description;
	    $vals['images'] = array('content/one1.jpg','content/two1.jpg');
	    $vals['thumbnail'] = 'content/thumb1.jpg';
	    $vals['url'] = 'http://mycompany.com';
	    $vals['maxGuests'] = 6;
	    
        $rate = array();
        $rate['daily'] = 100;
        $rate['weekly'] = 600;
        $rate['monthly'] = 2000;
        
        $vals['rate'] = $rate;
        
        $sp = array();
	    
        $sp = array();
        $sp1 = array();
        $sp1['daily'] = 150;
        $sp1['weekly'] = 900;
        $sp1['monthly'] = 3000;
        $sp1['from'] = new MongoDate(strtotime('2011-12-1'));
        $sp1['to'] = new MongoDate(strtotime('2011-12-15'));
        $sp[] = $sp1;
        
        $sp1 = array();
        $sp1['daily'] = 200;
        $sp1['weekly'] = 1200;
        $sp1['monthly'] = 5000;
        $sp1['from'] = new MongoDate(strtotime('2011-12-25'));
        $sp1['to'] = new MongoDate(strtotime('2012-1-4'));
        $sp[] = $sp1;
        
        $vals['special_rate'] = $sp;
        
	    $lm = new ZipVilla_Helper_ListingsManager();
	    $res = $lm->insert($tname,$vals);
	    
	    $id = $objId = $res->id;
	    $from = new MongoDate(strtotime('2011-12-1'));
        $to = new MongoDate(strtotime('2011-12-31'));
        $rate = $lm->getAverageRate($id, $from, $to);
        
        $this->assertEquals(130, $rate, "Wrong calculated average rate."); 
        
        $vals = array();
        $rate = array();
        $rate['daily'] = 80;
        $rate['weekly'] = 480;
        $rate['monthly'] = 1600;
        $vals['rate'] = $rate;
        $sp = array();
        $sp1 = array();
        $sp1['daily'] = 200;
        $sp1['weekly'] = 1200;
        $sp1['monthly'] = 5000;
        $sp1['from'] = new MongoDate(strtotime('2011-12-20'));
        $sp1['to'] = new MongoDate(strtotime('2012-1-05'));
        $sp[] = $sp1;
        
        $sp1 = array();
        $sp1['daily'] = 300;
        $sp1['weekly'] = 1800;
        $sp1['monthly'] = 7500;
        $sp1['from'] = new MongoDate(strtotime('2011-11-01'));
        $sp1['to'] = new MongoDate(strtotime('2011-11-05'));
        $sp[] = $sp1;
        
        $vals['special_rate'] = $sp;
        $res = $lm->update($res->id, $vals);
        //echo json_encode($res->getDoc()) . "\n";
        $from = new MongoDate(strtotime('2011-10-31'));
        $to = new MongoDate(strtotime('2011-11-03'));
        $rate = $lm->getAverageRate($res->id, $from, $to);
        $this->assertEquals(227, $rate, "Wrong calculated average rate.");
    }
    
	function testAvailability() {
		
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
	    $vals['amenities'] = array('health club' => 'This is a fantastic club',
                                   'sauna' => 'hot water', 'internet' => '');
	    $vals['title'] = "The Beach Home"; 
        $description = "very nice  place near fort";
	    $vals['description'] = $description;
	    $vals['images'] = array('content/one1.jpg','content/two1.jpg');
	    $vals['thumbnail'] = 'content/thumb1.jpg';
	    $vals['url'] = 'http://mycompany.com';
	    $vals['maxGuests'] = 6;
	    
	    $bookings = array();
        $booking = array();
        $booking['from'] = new MongoDate(strtotime('2011-12-1'));
        $booking['to'] = new MongoDate(strtotime('2011-12-5'));
        $bookings[] = $booking;
        $booking = array();
        $booking['from'] = new MongoDate(strtotime('2011-12-10'));
        $booking['to'] = new MongoDate(strtotime('2011-12-15'));
        $bookings[] = $booking;
        
        $vals['booked'] = $bookings;
	    $lm = new ZipVilla_Helper_ListingsManager();
	    $res = $lm->insert($tname,$vals);
	    
	    $id = $objId = $res->id;
	    $from = new MongoDate(strtotime('2011-12-1'));
        $to = new MongoDate(strtotime('2011-12-10'));
        $available = $lm->isAvailable($id, $from, $to);
        
        $this->assertFalse($available, "Not available accommodation returned as available."); 
        
        $from = new MongoDate(strtotime('2011-12-6'));
        $to = new MongoDate(strtotime('2011-12-9'));
        $available = $lm->isAvailable($id, $from, $to);
        
        $this->assertTrue($available, "Available accommodation returned as unavailable.");
        
        $from = new MongoDate(strtotime('2011-12-1'));
        $calendar = $lm->getBookingCalendar($id, $from, 30, FALSE);
        
        $this->assertEquals(2, count($calendar), "Returned booking calendar is not correct.");
        
        $vals = array();
        $bookings = array();
        $booking = array();
        $booking['from'] = new MongoDate(strtotime('2011-12-6'));
        $booking['to'] = new MongoDate(strtotime('2011-12-8'));
        $bookings[] = $booking;
        $vals['booked'] = $bookings;
        
        $res = $lm->update($res->id, $vals);
        
        $from = new MongoDate(strtotime('2011-12-6'));
        $to = new MongoDate(strtotime('2011-12-9'));
        $available = $lm->isAvailable($id, $from, $to);
        
        $this->assertFalse($available, "Unavailable accommodation returned as available, after listing update.");
        
        $from = new MongoDate(strtotime('2011-12-1'));
        $calendar = $lm->getBookingCalendar($id, $from, 30, FALSE);
        
        $this->assertEquals(1, count($calendar), "Returned booking calendar is not correct, after listing update.");
        
    }
}
?>
