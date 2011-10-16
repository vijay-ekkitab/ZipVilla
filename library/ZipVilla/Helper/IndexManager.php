<?php
include_once("ListingsManager.php");
include_once("../Utils.php");

const SOLR_HOST_NAME = "hostname";
const SOLR_PORT = "port";

class IndexManager  extends Zend_Controller_Action_Helper_Abstract {

	public function __construct() {
		$this->init();
	}
	private $options = null;
	private function init() 
	{
		$this->options = array (
			SOLR_HOST => 'localhost',
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
		$res = $lm->queryById($id,true,true);
		if($res != null) {
			return $this->_indexDocument($obj);
		} else {
			return null;
		}
	}
	public function index($mobj) {
		$lm = ListingsManager::instance();
		$flatObj = $lm->flatten($obj,true);
		if($flatObj != null) {
			return $this->_indexDocument($obj);
		} else {
			return null;
		}		
	}
}
