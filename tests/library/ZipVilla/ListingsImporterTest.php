<?php
include_once("ZipVilla/ListingsImporter.php");
include_once("ZipVilla/Helper/ListingsManager.php");

class ListingsImporterTest extends PHPUnit_Framework_TestCase
{

	public function setUp()
	{
		$this->bootstrap = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini');
		parent::setUp();
		$seed_script = APPLICATION_PATH . "/../tests/library/ZipVilla/test_schema.js";
		shell_exec("/usr/bin/mongo " . $seed_script);
	}
	public function testCSVImport() {
		$lfile = APPLICATION_PATH . "/../tests/library/ZipVilla/listings.csv";
		$imp = new ListingsImporter();
		$res = $imp->importFile($lfile);
		$titles = $this->getTitles();
		$lm = new ZipVilla_Helper_ListingsManager();
		foreach ($titles as $title) {
			$q = array('title'=>$title);
			$res = $lm->query($q,true);
			$this->assertNotNull($res,"Query did not return anything");
			$obj = $res[0];
			$t = $obj['title'];
			$this->assertEquals($t,$title, "expected : ".$title." Got : ".$t." import went wrong");
		}		 		
	}
	private function getTitles() {
		$lfile = APPLICATION_PATH . "/../tests/library/ZipVilla/listings.csv";
		$handle = fopen($lfile, "r"); 
		$buffer = null;
		$header = null;
		if(($buffer = fgetcsv($handle)) !== false) {
			$header = $buffer;
		}
		$titles = array();
		while(($buffer = fgetcsv($handle)) !== false) {
			$titles[] = $buffer[1]; 
		}
		//print_r($titles);
		return $titles;
	}
}
?>
