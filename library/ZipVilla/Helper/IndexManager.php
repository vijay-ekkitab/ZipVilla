<?php
include_once("ZipVilla/TypeConstants.php");
include_once("ListingsManager.php");
include_once("ZipVilla/Utils.php");


class ZipVilla_Helper_IndexManager extends Zend_Controller_Action_Helper_Abstract {

	public function __construct() {
		$this->init();
	}
	private static $options = null;
	
	public function init()
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
	    if ((isset($obj['address__state'])) && (isset($obj['address__city']))) {
	        $obj['citystate'] = $obj['address__city'] . ', '.$obj['address__state']; 
	    }
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
			//print_r($res);
			return $this->_indexDocument($res);
		} else {
			return null;
		}
	}
	public function indexAll() {
	    $lm = new ZipVilla_Helper_ListingsManager();
        $cursor = $lm->getCursor();
        $count = 0;
        foreach($cursor as $mongodoc) {
            $doc = new Application_Model_Listings($mongodoc);
            if ($doc != null) {
                $fdoc = $lm->flatten($doc,true);
                $result = $this->_indexDocument($fdoc);
                if ($result->success()) {
                    $doc->indexed = TRUE;
                    $doc->save();
                    $count++;
                }
            }
        }
        return $count;
	}
	
	public function updateIndex() {
	    $lm = new ZipVilla_Helper_ListingsManager();
	    $q = array(INDEXED => FALSE);
        $cursor = $lm->getCursor($q);
        $count = 0;
        foreach($cursor as $mongodoc) {
            $doc = new Application_Model_Listings($mongodoc);
            if ($doc != null) {
                $fdoc = $lm->flatten($doc,true);
                $result = $this->_indexDocument($fdoc);
                if ($result->success()) {
                    $doc->indexed = TRUE;
                    $doc->save();
                    $count++;
                }
            }
        }
        return $count;
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
