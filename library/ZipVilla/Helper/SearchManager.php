<?php
include_once("ListingsManager.php");
include_once("ZipVilla/Utils.php");

const SOLR_HOST_NAME = "hostname";
const SOLR_PORT = "port";

class SearchManager {

	public function __construct() {
		$this->init();
	}
	private static $options = null;
	
	private function init()
	{
		if (self::$options == null) {
			$config_file = APPLICATION_PATH . "/configs/application.ini";
    		$config = new Zend_Config_Ini($config_file, APPLICATION_ENV);
    	
			self::$options = array (
				SOLR_HOST_NAME => $config->solr->server,
				SOLR_PORT => $config->solr->port
			);
		}
	}
	
	private function buildQuery($q) {
		$qstr = "";
		if($q != null) {
			$i = 0;
			foreach ($q as $fd => $val) {
				if($i > 0) { $qstr = $qstr . "+OR+"; }
				$qstr = $qstr . $fd . ":" . $val;
				$i++;
			}
		}
		return $qstr;
	}
	/***********************************************************************************
 	 * perform solr search. if q = { a1 : v1 , a2 : v2} - it fires a search a1:v1 OR a2:v2 etc
	 * returns null if nothing is found - otherwise an array objects with key specified in $fds 
	 * if fds is null only a list of [ { id : ivVal }, ... ] are returned	 *
	 * @param q - query of form { a1 : v1 , a2 : v2 }
	 * @param $fds - list of fields to return
	 * @return - an array of SolrDocuments which can be accessed as simple PHP objects
	 ***********************************************************************************/
	public function search($q,$fds=null,$start=0,$count=50) {
		$client = new SolrClient(self::$options);
		$query = new SolrQuery();
		$qstr = $this->buildQuery($q);
		$query->setQuery($qstr);
		$query->setStart($start);
		$query->setRows($count);
		if($fds == null) {
			$fds = array("id");
		} else if(!array_key_exists("id",$fds)){
			$fds[] = "id";
		}
		foreach($fds as $fd) {
			$query->addField($fd);
		}
		$qr = $client->query($query);
		if(!$qr->success()) {
			//TODO; log error here
			return null;
		} else {
			$resp = $qr->getResponse();
			$docs = $resp['response']['docs'];
			return $docs; 
		}
	}
}
?>
