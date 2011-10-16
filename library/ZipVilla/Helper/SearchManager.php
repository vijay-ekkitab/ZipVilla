<?php
include_once("ListingsManager.php");
include_once("ZipVilla/Utils.php");

const SOLR_HOST_NAME = "hostname";
const SOLR_PORT = "port";

class SearchManager {

	public function __construct() {
		$this->init();
	}
	private $options = null;
	private function init()
	{
		$this->options = array (
			SOLR_HOST_NAME => 'localhost',
			SOLR_PORT => 8983
		);
	}
}
?>
