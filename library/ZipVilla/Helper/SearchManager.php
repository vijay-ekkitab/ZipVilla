<?php
include_once("ListingsManager.php");
include_once("ZipVilla/Utils.php");

class ZipVilla_Helper_SearchManager extends Zend_Controller_Action_Helper_Abstract {

    private $facet_fields;
    private $std_fields;
    
    public function __construct($facet_fields, $std_fields) {
        $this->init();
        $this->facet_fields = ($facet_fields == null) ? array() : $facet_fields;
        $this->std_fields = ($std_fields == null) ? array() : $std_fields;
        if (!in_array("id", $this->std_fields))
            $this->std_fields[] = "id";
    }
    private static $options = null;
	
    public function init()
    {
        if (self::$options == null) {
            $config = Zend_Registry::get('config'); 

            self::$options = array ("hostname" => $config->solr->server,
                                    "port" => $config->solr->port);
        }
    }
	
    private function buildQuery($q) {
        $qstr = "";
        if($q != null) {
            $i = 0;
            foreach ($q as $fd => $val) {
                if($i > 0) { $qstr = $qstr . " AND "; }
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
    public function search($q, $include_facets=TRUE, $start=0, $count=50) {
        $client = new SolrClient(self::$options);
        $query = new SolrQuery();
        
        if ($q == null) {
            return array ('docs' => array(), 'facets' => array());
        }
        
        $qstr = $this->buildQuery($q);
        
        $query->setQuery($qstr);
        $query->setStart($start);
        $query->setRows($count);
        
        foreach($this->std_fields as $field) {
            $query->addField($field);
        }
        
        if ($include_facets) {
            $facets = $this->facet_fields;
            $query_fields = array_keys($q);
            foreach ($facets as $i => $facet) {
                if (in_array($facet, $query_fields)) {
                    unset($facets[$i]);
                }
            }
            if (count($facets) > 0) { 
                $query->setFacet(true);
                foreach($facets as $facet) {
                    $query->addFacetField($facet);    
                }
            }
        }
        
        $qr = $client->query($query);
        
        if(!$qr->success())
            return array ('docs' => array(), 'facets' => array());
        
        $resp = $qr->getResponse();
        $docs = $resp->response->docs;
        $facets = isset($resp->facet_counts->facet_fields) ? $resp->facet_counts->facet_fields : array();
        return array('docs'=> $docs , 'facets' => $facets); 
    }
	
}
?>
