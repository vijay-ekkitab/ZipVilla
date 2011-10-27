<?php
include_once("ZipVilla/PriceModel.php");

class PriceModelTest extends PHPUnit_Framework_TestCase
{
	private $conn = null;
	private $db   = null;
	private $collection = null;
	
	static $setup_completed = FALSE;
	
	public function __construct() {
		parent::__construct();
		$config_file = APPLICATION_PATH . "/configs/application.ini";
    	$config = new Zend_Config_Ini($config_file, APPLICATION_ENV);
    
        $mongoDns = sprintf('mongodb://%s:%s',
                $config->mongodb->server, $config->mongodb->port
            );
        $this->conn = new Mongo($mongoDns,array("persist" => "x"));
        $this->db   = $this->conn->selectDB($config->mongodb->dbname);
        $this->collection = $this->db->homes;
	}
	
	public function setUp()
	{
		if (!self::$setup_completed) {
			$this->bootstrap = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini');
			parent::setUp();
			$seed_script = APPLICATION_PATH . "/../tests/library/ZipVilla/price_test_schema.js";
			shell_exec("/usr/bin/mongo " . $seed_script);
			self::$setup_completed = TRUE;
		}
	}
	
	public function testPricesIndividualListing() 
	{
		
		//$homes = $this->collection->find(array('city'=> 'Goa'));
		$home = $this->collection->findOne(array('city' => 'Goa'));
		$start = new MongoDate(strtotime('2011-12-1'));
		$end   = new MongoDate(strtotime('2011-12-15'));
		
		$standard_rate = $home['standard_rate'];
		$special_rates = $home['special_rate'];
		$pmodel = new PriceModel($special_rates, $standard_rate);
		$rate_slabs = $pmodel->get_rate_structure($start, $end);
		$this->assertEquals(2, count($rate_slabs), "Number of rate slabs returned in not correct." );
		$rate = $pmodel->get_average_rate($start, $end, FALSE);
		$this->assertEquals(103, $rate, "Calculated rate is not correct.");
		echo "-------------------------------\n";
		
		$start = new MongoDate(strtotime('2011-11-1'));
		$end   = new MongoDate(strtotime('2011-11-4'));
		$rate_slabs = $pmodel->get_rate_structure($start, $end);
		$this->assertEquals(1, count($rate_slabs), "Number of rate slabs returned in not correct." );
		$rate = $pmodel->get_average_rate($start, $end, FALSE);
		$this->assertEquals(100, $rate, "Calculated rate is not correct.");
		echo "-------------------------------\n";
		
		$start = new MongoDate(strtotime('2011-11-1'));
		$end   = new MongoDate(strtotime('2012-2-10'));
		$rate_slabs = $pmodel->get_rate_structure($start, $end);
		$this->assertEquals(5, count($rate_slabs), "Number of rate slabs returned in not correct." );
		$rate = $pmodel->get_average_rate($start, $end, FALSE);
		$this->assertEquals(87, $rate, "Calculated rate is not correct.");
		echo "-------------------------------\n";
		
		
	}
}
