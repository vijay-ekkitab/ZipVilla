<?php
include_once("ListingsManager.php");
include_once("ZipVilla/Utils.php");
include_once("ZipVilla/TypeConstants.php");

class ZipVilla_Helper_SearchManager extends Zend_Controller_Action_Helper_Abstract {
    
    private $facet_fields;
    private $std_fields;
    private $sort_field = null;
    private $sort_order = SolrQuery::ORDER_DESC;
    
    public function __construct($facet_fields = null, $std_fields = null) {
        $this->init();
        if ($facet_fields == null) {
            $this->facet_fields = array('amenities' => 'AND', 
                                        'onsite_services' => 'AND', 
                                        'suitability' => 'AND', 
                                        'shared' => 'OR', 
                                        'address__location' => 'OR');
        }
        else {
            $this->facet_fields = $facet_fields;
        }
            
        if ($std_fields == null) {
            $this->std_fields = array('title', 'address__street_name', 'address__city', 'address__state',
                                'guests', 'rating', 'reviews', 'address__country', 'average_rate', 'address__coordinates__latitude',
                                'address__coordinates__longitude', 'images');
        }
        else { 
            $this->std_fields = $std_fields;
        }
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
    
    public function setFacetFields($facets) {
        if ($facets != null) 
            $this->facet_fields = $facets;
    }
    
    public function getFacetFields() {
        return $this->facet_fields;
    }
    
    public function setSelectFields($fields) {
        if ($fields != null) {
            $this->std_fields = $fields;
            if (!in_array("id", $this->std_fields))
                $this->std_fields[] = "id";
        }
    }
    
    public function getSelectFields() {
        return $this->std_fields;
    }
    
    public function setSortField($field, $order = SolrQuery::ORDER_DESC) {
        $this->sort_field = $field;
        $this->sort_order = $order;
    }
    
    public function getSortField($field) {
        return $this->sort_field;
    }
	
    private function buildQuery($q, $guests, $price_range) {
        $qstr = "";
        if($q != null) {
            foreach ($q as $fd => $val) {
                if (is_array($val)) {
                    $op = 'AND';
                    if (isset($this->facet_fields[$fd])) {
                        $op = $this->facet_fields[$fd];
                    }
                    $substr = '';
                    foreach($val as $v) {
                        if ($substr == '') 
                            $substr .= $fd . ':' . $v;
                        else 
                            $substr .= ' '.$op.' '.$fd . ':' . $v ;
                    }
                    if ($substr != '')
                        $qstr .= ' AND ' .'( '.$substr.' )';
                }
                else {
                    $qstr .= ' AND ' . $fd . ':' . $val ;
                }
            }
        }
        if ($guests > 1) {
            $qstr = $qstr . " AND " . "guests:[" . $guests . " TO *]";
        }
        if ($price_range != null) {
            $qstr = $qstr . " AND " . "average_rate:[" . $price_range[0] . " TO " . $price_range[1] . "]";
        }
        return preg_replace('/^ AND /', '', $qstr);
    }
	
	
	
	/***********************************************************************************
 	 * Retrieve data from solr index or from mongo.
 	 * If dates are not provided, then search results from mongo are directly returned
 	 * to the application.
 	 * If dates are provided, first a solr search is performed. For each of the returned
 	 * results, the mongodb database is queried to compute actual rate information based
 	 * on whether the date range includes any special rates. Also, if a listing is
 	 * unavailable during this date range, it is dropped from the list.
 	 * The results are sorted as requested and returned.
 	 * 
	 * @param q - query of form { a1 : v1 , a2 : v2 } or { a1:[v1,v2...], ...}
	 * @param from - from date.
	 * @param to - to date.
	 * @param page - page number of results to be returned. Starts with 1.
	 * @param pagesize - page size.
	 * @return - an array of listings, each of which is a map that can be accessed as
	 *           simple PHP objects. 
	 ***********************************************************************************/
    public function search($q, $from=null, $to=null, $guests=1, $sortorder=SORT_ORDER_RATING, 
                           $price_range=null, $page=1, $pagesize=20) {
        $client = new SolrClient(self::$options);
        $query = new SolrQuery();
        $logger = Zend_Registry::get('zvlogger');
        
        if ($q == null) {
            return array ('docs' => array(), 'facets' => array(), 'count' => 0);
        }
        
        $solr_price_range = $price_range;
        if (($from != null) && ($to != null)) {
            $solr_price_range = null;
        }
        
        $qstr = $this->buildQuery($q, $guests, $solr_price_range);
        
        $logger->debug("Query>> $qstr");
        
        $start = ($page - 1)*$pagesize;
        //$end   = $start + $pagesize;
        
        $query->setQuery($qstr);
        $query->setStart($start);
        $query->setRows($pagesize);
        
        if (($from != null) && ($to != null)) { //With dates, retrieve data from Mongo not Solr.
            $query->addField('id');
        }
        else {
            foreach($this->std_fields as $field) {
                $query->addField($field);
            }
            $query->setStart($start);
            $query->setRows($pagesize);
            if ($sortorder == SORT_ORDER_RATING) {
                $query->addSortField('rating', SolrQuery::ORDER_DESC );   
            }
            elseif ($sortorder == SORT_ORDER_RATE_HL) {
                $query->addSortField('average_rate', SolrQuery::ORDER_DESC );
            }
            elseif ($sortorder == SORT_ORDER_RATE_LH) {
                $query->addSortField('average_rate', SolrQuery::ORDER_ASC );
            }
            $logger->debug("Requesting $pagesize rows starting at $start.");
        }
        
        $facets = array_keys($this->facet_fields);
            //$query_fields = array_keys($q);
            //foreach ($facets as $i => $facet) {
                //if (in_array($facet, $query_fields)) {
                //    unset($facets[$i]);
                //}
            //}
        if (count($facets) > 0) { 
            $query->setFacet(true);
            foreach($facets as $facet) {
                $query->addFacetField($facet);    
            }
        }
        
        /*if ($this->sort_field != null) {
            $query->addSortField($this->sort_field, $this->sort_order);
        }*/
        $qr = $client->query($query);
        
        if(!$qr->success())
            return array ('docs' => array(), 'facets' => array(), 'count' => 0);
        //$logger->debug($qr->getRawResponse());
        $resp = $qr->getResponse();
        $docs = $resp->response->docs;
        $doc_count = $resp->response->numFound;
        $logger->debug("Number of Hits>> $doc_count");
        if ($docs == null) 
            return array ('docs' => array(), 'facets' => array(), 'count' => 0);
            
        if (($from != null) && ($to != null)) {
            $ids = array();
            foreach ($docs as $doc) {
               //echo "SearchManager>> Id: ".$doc['id']."\n";
               $ids[] = $doc['id']; 
            }
            $lm = new ZipVilla_Helper_ListingsManager();
            $from = new MongoDate(strtotime($from));
            $to = new MongoDate(strtotime($to));
            /*$sortParams = array('field' => 'average_rate');
            if ($this->sort_field != null) 
                $sortParams = array('field' => $this->sort_field);*/
            $listings = $lm->getListings($ids, $from, $to, $start, $pagesize, $sortorder, $price_range);
            $docs = $listings['docs'];
            $doc_count = $listings['count'];
        }
        $facets = isset($resp->facet_counts->facet_fields) ? $resp->facet_counts->facet_fields : array();
        return array('docs'=> $docs , 'facets' => $facets, 'count' => $doc_count); 
    }
	
    public function search_ajax($q) {
        $client = new SolrClient(self::$options);
        $query = new SolrQuery();
        $logger = Zend_Registry::get('zvlogger');
        
        $results = array(); 
        
        if ($q == null) {
            return $results;
        }
        
        $qstr = $this->buildQuery($q, 0, null);
        
        $query->setQuery($qstr);
        
        $query->addField('address__city');
        $query->addField('address__state');
        
        $qr = $client->query($query);
        
        if(!$qr->success())
            return $results;
            
        $resp = $qr->getResponse();
        $docs = $resp->response->docs;
        
        if ($docs == null) 
            return $results;
       
        foreach($docs as $doc) {
            $city = isset($doc['address__city']) ? $doc['address__city'] : '';
            $state = isset($doc['address__state']) ? $doc['address__state'] : '';
            $citystate = $city.','.$state;
            if ($citystate != '')
                $results[] = $citystate;
        }
        $results = array_unique($results);
        return $results;
            
    }
    
    /*public function search_old($q, $include_facets=TRUE, $start=0, $count=50) {
        $client = new SolrClient(self::$options);
        $query = new SolrQuery();
        
        if ($q == null) {
            return array ('docs' => array(), 'facets' => array());
        }
        
        $qstr = $this->buildQuery($q);
        
        $logger = Zend_Registry::get('zvlogger');
        $logger->debug("Query>> $qstr");
        
        $query->setQuery($qstr);
        $query->setStart($start);
        $query->setRows($count);
        
        foreach($this->std_fields as $field) {
            $query->addField($field);
        }
        
        if ($include_facets) {
            $facets = $this->facet_fields;
            $query_fields = array_keys($q);
            //foreach ($facets as $i => $facet) {
                //if (in_array($facet, $query_fields)) {
                //    unset($facets[$i]);
                //}
            //}
            if (count($facets) > 0) { 
                $query->setFacet(true);
                foreach($facets as $facet) {
                    $query->addFacetField($facet);    
                }
            }
        }
        
        if ($this->sort_field != null) {
            $query->addSortField($this->sort_field, $this->sort_order);
        }
        $qr = $client->query($query);
        
        if(!$qr->success())
            return array ('docs' => array(), 'facets' => array());
        
        $resp = $qr->getResponse();
        $docs = $resp->response->docs;
        $facets = isset($resp->facet_counts->facet_fields) ? $resp->facet_counts->facet_fields : array();
        return array('docs'=> $docs , 'facets' => $facets); 
    }*/
    
}
?>
