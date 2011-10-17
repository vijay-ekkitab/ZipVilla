<?php
include_once("ListingsManager.php");
include_once("ZipVilla/Utils.php");

const SOLR_HOST_NAME = "hostname";
const SOLR_PORT = "port";

class IndexManager {

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
		$client = new SolrClient($this->options);
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
		$client = new SolrClient($this->options);
		$updateResponse = $client->deleteByQuery($q); /* remove all documents */
		//print_r($updateResponse->getResponse());
		$client->commit();
	}
}
