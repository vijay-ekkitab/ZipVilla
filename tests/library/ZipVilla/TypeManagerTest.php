<?php
include_once("ZipVilla/Helper/TypeManager.php");

class TypeManagerTest extends PHPUnit_Framework_TestCase
{
	static $setup_completed = FALSE;

    public function setUp()
    {
    	if (!self::$setup_completed) {
        	$this->bootstrap = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini');
        	parent::setUp();
        	$seed_script = APPLICATION_PATH . "/../tests/library/ZipVilla/test_schema.js";
        	shell_exec("/usr/bin/mongo " . $seed_script);
        	self::$setup_completed = TRUE;
    	}
    }
    
    public function testGetTypeNames()
    {
        echo "\n";
        $tm = new TypeManager();
        $tnames = $tm->getTypeNames();
        $this->assertNotNull($tnames, "No Types are found.");
        $this->assertEquals(11, count($tnames), "Wrong number of types returned."); 
        echo "Types found:\n";
        foreach ($tnames as $name) {
            echo $name ."\n";
        }
    }

    public function testGetType() {
        echo "\n";
        $types = array("hotel", "resort", "address");
        foreach($types as $tname) {
	        $tm = new TypeManager();
	        $tp = $tm->getType($tname);
            $this->assertNotNull($tp, "GetType for $tname failed.");
            $n = $tp->getName();
            $this->assertEquals($tname, $n, "GetType for $tname returned wrong name - $n");
		    $attrs = $tp->getAttributes();
            $this->assertNotNull($attrs, "GetType for $tname failed.");
		    foreach($attrs as $an => $aval) {
			    echo $an . "--->" . $aval->str() . "\n";
		    }
	    }
    }

    public function testIndexable() {
        echo "\n";
        $tname = 'address';
        $attrName = 'lat';
 	    $tm = new TypeManager();
	    $tp = $tm->getType($tname);
        $this->assertNotNull($tp, "GetType for $tname failed.");
		$a = $tp->getAttribute($attrName);
        $this->assertEquals(FALSE, $a->isIndexable(), "Address.lat is indexable when it should not be.");
	}

    public function testAttributeIndexable() {
        echo "\n";
        $test_array = array("resort", "address__lat", FALSE,
                            "resort", "address__city", TRUE,
                            "resort", "title", TRUE,
                            "resort", "rate__daily", TRUE);
	    $tm = new TypeManager();
        for ($i =0; $i < count($test_array); $i+=3) {
            $tname = $test_array[$i];
            $attrName = $test_array[$i+1];
	        $tp = $tm->getType($tname);
            $this->assertNotNull($tp, "GetType for $tname failed.");
		    $a = $tp->attributeIndexable($attrName);
            $this->assertEquals($test_array[$i+2], $a, "Type: $tname Attribute: $attrName should be indexable: [" . $test_array[$i+2] ."] but returned [$a]");
        }
	}
    
    public function testMakeObject() {
        echo "\n";
        $tname = "resort";
        
	    $vals = array();
	    $vals['size'] = "10 acres"; 
	    $vals['activities'] = array('kayaking','bonfire', 'nature walk','trekking');
	    $vals['specalities'] = array('organic food','house wines');
	    $vals['userId'] = "owner_123";
	    $vals['streetNumber'] = "456-D";
	    $vals['street'] = "Vagator Beach Road";
	    $vals['line1'] = "Close to Petrol Bunk";
	    $vals['line2'] = "Beach home";
	    $vals['city'] = "Zuin";
	    $vals['state'] = "Goa";
	    $vals['country'] = "India";
	    $vals['pincode'] = "5678999";
	    $vals['neighbourhood'] = "Anjuna";
	    $vals['lat'] = 45.6;
	    $vals['long'] = 55.8;
	    $vals['amenities'] = array('laundry','health club','sauna'); 
	    $vals['description'] = "The village square is a great place";
	    $vals['images'] = array('content/one.jpg','content/two.jpg');
	    $vals['thumbnail'] = 'content/thumb.jpg';
	    $vals['url'] = 'http://mycompany.com';
	    $vals['rate'] = 4500;
	    $vals['rateType'] = "double occupancy";
	    $vals['maxGuests'] = 4;

	    $tm = new TypeManager();
	    $tp = $tm->getType($tname);
        $this->assertNotNull($tp, "GetType for $tname failed.");
		$obj = $tp->makeObject($vals);
        //echo json_encode($obj) . "\n";
        $this->assertEquals("Anjuna", $obj['address']['neighbourhood'], "Neighbourhood is changed in created object.");
        $this->assertEquals("Goa", $obj['address']['state'], "State is changed in created object.");
        $this->assertEquals("10 acres", $obj['size'], "Size is changed in created object.");
	}
	
	public function testRates() {
        echo "\n";
        
        $tm = new TypeManager();
        
	    $accom = array();
	    $accom['userId'] = "owner_123";
	    $accom['streetNumber'] = "456-D";
	    $accom['city'] = "Zuin";
	    $accom['state'] = "Goa";
	    $accom['country'] = "India";
	  
        $rate = array();
        $rate['daily'] = 100;
        $rate['weekly'] = 600;
        $rate['monthly'] = 2000;
        
        $accom['rate'] = $rate;
        
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
        
        $accom['special_rate'] = $sp;
        
	    $tp = $tm->getType("accommodation");
        $this->assertNotNull($tp, "GetType for 'accommodation' failed.");
	    $obj = $tp->makeObject($accom);
	    $this->assertEquals(100, $obj['rate']['daily'], "Standard daily rate is different.");
        $this->assertEquals(150, $obj['special_rate'][0]['rate']['daily'], "Special daily rate is different.");
	    $this->assertEquals(200, $obj['special_rate'][1]['rate']['daily'], "Special daily rate is different.");
	    $d = new MongoDate(strtotime('2011-12-1'));
	    $this->assertEquals($d->sec, $obj['special_rate'][0]['period']['from']->sec, "Special daily rate is different.");
	    
        $fobj = $tp->flatten($obj, TRUE);
        $this->assertEquals(6, count($fobj), "Flattened object has different number of elements.");
        
	} 
	
	public function testEnumerations() {
		echo "\n";
		$enums = Attribute::getAllEnumerations();
		$this->assertEquals(1, count($enums), "More than one enumeration was found.");
		$this->assertTrue(array_key_exists("amenities", $enums), "Amenities does not exist in Attribute enumerations.");
		$tname = "accommodation";
		$accom = array();
	    $accom['city'] = "Bangalore";
	    $accom['amenities'] = array('health club' => 'This is a fantastic club',
                                   'sauna' => 'hot water');
	    $tm = new TypeManager();
	    $tp = $tm->getType($tname);
	    $this->assertNotNull($tp, "GetType for $tname failed.");
	    $obj = $tp->makeObject($accom);
	    $fobj = $tp->flatten($obj, TRUE);
	    $this->assertEquals(2, count($fobj), "Flattened object contains different number of map elements.");
	    $this->assertTrue(array_key_exists('address__city', $fobj), "Flattened object does not contain expected key.");
	    $this->assertTrue(array_key_exists('amenities', $fobj), "Flattened object does not contain expected key.");
	    $this->assertEquals(2, count($fobj['amenities']), "Flattened object contains different number of enumerations.");
	    //echo json_encode($fobj) . "\n";
		
	}
	
	
}
?>
