<?php
include_once("ZipVilla/Helper/OwnersManager.php");
include_once("ZipVilla/Helper/ListingsManager.php");

class OwnersManagerTest extends PHPUnit_Framework_TestCase
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
    
    public function testGetOwners()
    {
        echo "\n";
        $om = new ZipVilla_Helper_OwnersManager();
        $owners = $om->query(array());
        $this->assertNotNull($owners, "No Owners were found.");
        $this->assertEquals(2, count($owners), "Wrong number of owners returned."); 
    }
    
    public function testInsertAndUpdate()
    {
        echo "\n";
        $obj = array();
        $obj['name'] = 'Owners Name';
        $obj['address'] = 'Owners Address';
        $om = new ZipVilla_Helper_OwnersManager();
        $res = $om->insert($obj);
        $this->assertNotNull($res, "Insert of new owner failed.");
        $obj = array();
        $obj['user_id'] = 'Owners UserId';
        $obj = $om->update($res->id, $obj);
        $this->assertEquals('Owners Name', $obj->name, "Owners name of updated object is not correct.");
        $this->assertEquals('Owners Address', $obj->address, "Owners address of updated object is not correct.");
        $this->assertEquals('Owners UserId', $obj->user_id, "Owners user id of updated object is not correct.");
        $owners = $om->query(array());
        $this->assertEquals(3, count($owners), "Wrong number of owners returned.");
    }
    
    public function testAddListing()
    {
        echo "\n";
        $lm = new ZipVilla_Helper_ListingsManager();
        $tname = "hotel";
        $vals = array();
        $vals['description'] = "This is the home of owner 1.";
        $vals['city'] = 'Goa';
        $vals['rating'] = 10;
        $res = $lm->insert($tname,$vals);
        
        $om = new ZipVilla_Helper_OwnersManager();
        $owners = $om->query(array('user_id' => 'own1'));
        $this->assertNotNull($owners, "No Owners were found.");
        $this->assertEquals(1, count($owners), "Wrong number of owners returned.");

        $owner = $owners[0];
        $owner->addListing($res);
        $owner = $om->queryById($owner->id);
        $this->assertNotNull($owner, "Owner not found.");
        $listings = $owner->getListings();
        $this->assertEquals(1, count($listings), "Wrong number of listings returned for owner.");
        $listing = $listings[0];
        $this->assertEquals(10, $listing->rating, "Incorrect listing returned for owner.");
        
    }

	
	
}
?>
