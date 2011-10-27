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
        $this->assertEquals(7, count($tnames), "Wrong number of types returned."); 
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
        $test_array = array("resort", "address_lat", FALSE,
                            "resort", "address_city", TRUE,
                            "resort", "title", TRUE,
                            "resort", "rate", FALSE);
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
	
	public function testEnumerations() {
		echo "\n";
		$enums = Attribute::getAllEnumerations();
		$this->assertEquals(1, count($enums), "More than one enumeration was found.");
		$this->assertTrue(array_key_exists("amenities", $enums), "Amenities does not exist in Attribute enumerations.");
	}
}
?>
