<?php
include_once("ListingsManager.php");
include_once("ZipVilla/Utils.php");

class IndexManager {

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
	private function buildSolrInputDocument($map) {
		$doc = new SolrInputDocument();
		foreach ( $map as $n => $v) {
			if(!is_array($v)) {
				$doc->addField($n,(string)$v);
			} else {
				foreach($v as $aval) {
					$doc->addField($n,(string)$aval);
				}
			}
		}
		//print_r($doc->toArray());
		return $doc; 
	}
	private function _indexDocument($obj) {
		$doc = $this->buildSolrInputDocument($obj);
		$client = new SolrClient(self::$options);
		$updateResponse = $client->addDocument($doc);
		//TODO: log error if not successful
		//it is always a auto commit
		$client->commit();
		return $updateResponse;		
	}
	public function indexById($mid) {
		$lm = new ZipVilla_Helper_ListingsManager();
		$res = $lm->queryById($mid,true,true);
		if($res != null) {
			return $this->_indexDocument($res);
		} else {
			return null;
		}
	}
	public function index($mobj) {
		$lm = new ZipVilla_Helper_ListingsManager();
		$flatObj = $lm->flatten($mobj,true);
		if($flatObj != null) {
			return $this->_indexDocument($flatObj);
		} else {
			return null;
		}		
	}
	public function delete($q="*:*") {
		$client = new SolrClient(self::$options);
		$updateResponse = $client->deleteByQuery($q); /* remove all documents */
		//print_r($updateResponse->getResponse());
		$client->commit();
	}
}
