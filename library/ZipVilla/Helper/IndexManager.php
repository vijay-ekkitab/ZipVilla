<?php
include_once("ZipVilla/TypeConstants.php");
include_once("ListingsManager.php");
include_once("ZipVilla/Utils.php");
include_once("ZipVilla/Logger.php");


class ZipVilla_Helper_IndexManager extends Zend_Controller_Action_Helper_Abstract {

	public function __construct() {
		$this->init();
	}
	private static $options = null;
	private $logger = null;
	
	public function init()
	{
		if (self::$options == null) {
			$config = Zend_Registry::get('config'); 
    	
			self::$options = array (
				"hostname" => $config->solr->server,
				"port" => $config->solr->port
			);
		}
		if ($this->logger == null) {
			$this->logger=Zend_Registry::get('zvlogger');
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
	private function _indexDocument($client, $obj) {
	    if ((isset($obj['address__state'])) && (isset($obj['address__city']))) {
	        $obj['citystate'] = $obj['address__city'] . ', '.$obj['address__state']; 
	    }
		$doc = $this->buildSolrInputDocument($obj);
		try {
			$updateResponse = $client->addDocument($doc);
			$client->commit();
			return $updateResponse;
		} catch (Exception $e) {
			$this->logger->log("Error Indexing the Listing with Id : ".$obj['id'],1);
			$this->logger->log($e->getMessage(),1);
			return null;
		}		
	}
	public function indexById($mid) {
		$lm = new ZipVilla_Helper_ListingsManager();
		$res = $lm->queryById($mid,true,true);
		$client = new SolrClient(self::$options);
		if($res != null) {
			//print_r($res);
			return $this->_indexDocument($client, $res);
		} else {
			return null;
		}
	}
	public function indexAll() {
	    $lm = new ZipVilla_Helper_ListingsManager();
        $cursor = $lm->getCursor();
        $count = 0;
        $client = new SolrClient(self::$options);
        foreach($cursor as $mongodoc) {
            $doc = new Application_Model_Listings($mongodoc);
            if ($doc != null) {
                $fdoc = $lm->flatten($doc,true);
                $result = $this->_indexDocument($client, $fdoc);
                if ($result !=null && $result->success()) {
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
        $client = new SolrClient(self::$options);
        foreach($cursor as $mongodoc) {
            $doc = new Application_Model_Listings($mongodoc);
            if ($doc != null) {
                $fdoc = $lm->flatten($doc,true);
                $result = $this->_indexDocument($client, $fdoc);
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
		$client = new SolrClient(self::$options);
		if($flatObj != null) {
			return $this->_indexDocument($client, $flatObj);
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
    public function deleteById($id) {
        $client = new SolrClient(self::$options);
        $updateResponse = $client->deleteById($id); /* remove the document */
        $client->commit();
    }
}
