<?php
include_once("ZipVilla/Availability.php");

class AvailabilityTest extends PHPUnit_Framework_TestCase
{
	private $conn = null;
	private $db   = null;
	private $collection = null;
	
	public function setUp()
	{
		$this->bootstrap = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini');
		parent::setUp();
		//$seed_script = APPLICATION_PATH . "/../tests/library/ZipVilla/price_test_schema.js";
		//shell_exec("/usr/bin/mongo " . $seed_script);
	}
	
	public function testAvailability() 
	{
		$from = new MongoDate(strtotime('2011-12-4'));
		$to = new MongoDate(strtotime('2011-12-5'));
		$period1 = array('period' => array('from' => $from, 'to' => $to));
		
		$from = new MongoDate(strtotime('2011-12-10'));
		$to = new MongoDate(strtotime('2011-12-11'));
		$period2 = array('period' => array('from' => $from, 'to' => $to));
		
		$booked = array ($period1, $period2);
		
		$start = new MongoDate(strtotime('2011-12-1'));
		$end   = new MongoDate(strtotime('2011-12-31'));
		
		$availability = new Availability();
		$available = $availability->is_available($booked, $start, $end);
		$this->assertFalse($available, "Not available period returned as available." );
		
		$booked_periods = $availability->get_booked_dates($booked, $start, 31);
		$this->assertEquals(2, count($booked_periods), "Calculation of booked periods returned wrong result.");
		
		$this->assertFalse($available, "Not available period returned as available." );
		
		$end   = new MongoDate(strtotime('2011-12-3'));
		$available = $availability->is_available($booked, $start, $end);
		$this->assertTrue($available, "Available period returned as NOT available." );
		
	}
}
