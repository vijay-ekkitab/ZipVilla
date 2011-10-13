<?php
//include_once("ZipVilla/Logger.php");

class LoggerTest extends PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        $this->bootstrap = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini');
        parent::setUp();
    }

    function testLogging() {
		$logger = new ZipVilla_Logger();
        $logger->debug("This is a debug message\n");
    }
        
}
?>
