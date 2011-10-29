<?php
include_once("ListingsManager.php");
include_once("ZipVilla/Utils.php");

class SearchManager {

	public function __construct() {
		$this->init();
	}
	private static $options = null;
	
	private function init()
	{
		if (self::$options == null) {
			$config = Zend_Registry::get('config'); 
    	
			self::$options = array (
				"hostname" => $config->solr->server,
				"port" => $config->solr->port
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
	 * @param $ffds - list of fields to return
	 * @return - an array of SolrDocuments which can be accessed as simple PHP objects
	 ***********************************************************************************/
	public function search($q,$fds=null,$ffds=null,$start=0,$count=50) {
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
		if(($ffds != null)&&(count($ffds) > 0)) {
			$query->setFacet(true);
			foreach($ffds as $ffd) {
				$query->addFacetField($ffd);	
			}
		}
		$qr = $client->query($query);
		if(!$qr->success()) {
			//TODO; log error here
			return null;
		} else {
			$resp = $qr->getResponse();
			//print_r($resp);
			$docs = $resp['response']['docs'];
			$facets = null;
			if(($ffds != null)&&(count($ffds) > 0)) {
				//print_r($resp);
				$facets = $resp['facet_counts']['facet_fields'];
				//print_r($facets);
			}
			return array('docs'=> $docs , 'facets' => $facets); 
		}
	}
}
?>
