<?php
include_once("ZipVilla/TypeConstants.php");
class SearchController extends Zend_Controller_Action
{
    const CHECKIN  = 'check_in';
    const CHECKOUT = 'check_out';
    const GUESTS   = 'guests';
    const QUERY    = 'query';
    const PAGE     = 'page';
    const SORT     = 'sort';
    const PRICE_RANGE = 'price_range';
    const KEYWORDS = 'keywords';
    const SHOWTAB  = 'showtab';
    
    protected $facets = array('amenities', 'onsite_services', 'suitability', 'address__location', 'shared');
    
    public function init()
    {
        /* Initialize action controller here */
        $ajaxContext = $this->_helper->getHelper('AjaxContext');
        $ajaxContext->addActionContext('lookahead', 'html')
                    ->addActionContext('refine', 'html')
                    ->addActionContext('autocomplete', 'json')
                    ->addActionContext('test', 'json')
                    ->initContext();
    }
    
    protected function save_session_data($values)
    {
        $session = new Zend_Session_Namespace('guest_data');
        $session->values = $values;
    }
    
    protected function get_session_data()
    {
        $session = new Zend_Session_Namespace('guest_data');
        return $session->values;
    }
    
    protected function read_value($values, $index, $default=null)
    {
        return isset($values[$index]) ? $values[$index] : $default;
    }
    
    protected function set_view_params($values, $results, $q) 
    {
        $this->view->search_query   = $this->read_value($values, SearchController::QUERY);
        $this->view->checkin        = $this->read_value($values, SearchController::CHECKIN);
        $this->view->checkout       = $this->read_value($values, SearchController::CHECKOUT);
        $this->view->guests         = $this->read_value($values, SearchController::GUESTS);
        $this->view->page           = $this->read_value($values, SearchController::PAGE);
        $this->view->pagesz         = PAGE_SZ;
        $this->view->sortorder      = $this->read_value($values, SearchController::SORT);
        $this->view->price_range    = $this->read_value($values, SearchController::PRICE_RANGE);
        $this->view->keywords       = $this->read_value($values, SearchController::KEYWORDS);
        $this->view->showtab        = $this->read_value($values, SearchController::SHOWTAB, 0);
        $this->view->results        = $results['docs'];
        $this->view->total_hits     = $results['count'];
        if (isset($results['facets'])) {
            $this->view->facets     = $results['facets'];
        }
        unset($q['city_state']);
        $this->view->facets_selected = $q;
    }
    
    protected function doSearch(&$values, &$q) {
        
        $sm = $this->_helper->searchManager;
            
        if ((!isset($values[SearchController::QUERY])) ||
            ($values[SearchController::QUERY] == '')) {
            $values[SearchController::QUERY] = '*';
        }

        if (!isset($values[SearchController::PRICE_RANGE]))
            $values[SearchController::PRICE_RANGE] = null;
        else {
            if(is_string($values[SearchController::PRICE_RANGE])) {
                if (preg_match_all('/([0-9]+)/', $values[SearchController::PRICE_RANGE], $matches)) {
                    $values[SearchController::PRICE_RANGE] = $matches[0];
                }
                else {
                    $values[SearchController::PRICE_RANGE] = array(MIN_RATE, MAX_RATE);
                }
            }
        }
        
        $q = array();
        
        if ($values[SearchController::QUERY] == '*') {
            $q = array('city_state' => $values[SearchController::QUERY]);
        }
        else {
            $tmp = preg_replace("/[^a-zA-Z0-9]/", " ", $values[SearchController::QUERY]);
            $tmp = preg_replace("/\s+/", " ", $tmp);
            $terms = explode(' ', $tmp);
            $qterms = array();
            foreach($terms as $term) {
                $qterms[] = '"' . $term . '"';
            }
            $q = array('city_state' => $qterms);
        }
        
        foreach ($this->facets as $facet) {
            if (isset($values[$facet])) {
                if (!isset($q[$facet])) {
                    $q[$facet] = array();
                }
                foreach($values[$facet] as $v) {
                    $v = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $v);
                    $q[$facet][] = '"' . $v . '"';
                }
            }
        }
        
        if (isset($values[SearchController::KEYWORDS])) {
            if ($values[SearchController::KEYWORDS] != '') {
                $keyword_array = explode(' ', $values[SearchController::KEYWORDS]);
                for($i=0; $i<count($keyword_array); $i++) {
                    $keyword_array[$i] = '"'.$keyword_array[$i].'"';
                }
                $q['search_keyword'] = $keyword_array;
            }
        }
        
        $search_results = $sm->search($q, 
                                      $this->read_value($values, SearchController::CHECKIN),
                                      $this->read_value($values, SearchController::CHECKOUT),
                                      $this->read_value($values, SearchController::GUESTS,1),
                                      $this->read_value($values, SearchController::SORT,SORT_ORDER_RATING),
                                      $this->read_value($values, SearchController::PRICE_RANGE),
                                      $this->read_value($values, SearchController::PAGE,1),
                                      PAGE_SZ);
        
        $this->handleCookies($q['city_state'][0]);
                                      
        return $search_results;
    }

    public function reindexAction() 
    {
        $im = $this->_helper->indexManager;
        $im->delete();
        $im->indexAll();
        $this->_helper->redirector('index', 'index');
    }
    
    public function setzvidAction() 
    {
        $lm = $this->_helper->listingsManager;
        $cursor = $lm->getCursor();
        foreach($cursor as $mongodoc) {
            $doc = new Application_Model_Listings($mongodoc);
            if ($doc != null) {
                if (!isset($doc->{ZIPVILLA_ID})) {
                    $zvId = $doc->getNextSeq();
                    if ($zvId != null) {
                        $doc->{ZIPVILLA_ID} = $zvId;
                        $doc->save();
                    }
                }
            }
        }
        $this->_helper->redirector('index', 'index');
    }
    
    public function indexAction()
    {
        $logger = Zend_Registry::get('zvlogger');
        
        $values = $this->getRequest()->getParams();
        
        if (!isset($values[SearchController::QUERY])) {
            $values = $this->get_session_data();
            if ($values == null) {
                $this->_helper->redirector('index', 'index');
            }
        }
        
        $q = array();
        
        $search_results = $this->doSearch($values, $q);
        
        if ($search_results) {
            $this->set_view_params($values, $search_results, $q);
        }
        else {
            $this->view->error = 'encountered error in search.';
        }
        $this->save_session_data($values);
    }
    
    public function refineAction() {
        $logger = Zend_Registry::get('zvlogger');
        $values = $this->get_session_data();
        $request = $this->getRequest();
        $search_results = null;
        $q = array();
        if ($request->isPost()) {
            $post = $request->getPost();
            if (in_array($post['facet'], $this->facets)) {
                if (!isset($values[$post['facet']])) {
                    $values[$post['facet']] = array();
                }
                if ($post['selected'] == 'true') {
                    if (!in_array($post['value'], $values[$post['facet']])) {
                        $values[$post['facet']][] = $post['value'];
                    }
                }
                else {
                    foreach($values[$post['facet']] as $key => $val) {
                        if ($val == $post['value']) {
                            unset($values[$post['facet']][$key]);
                            break;
                        }
                    }
                }
            }
            else {
                $values[$post['facet']] = $post['value'];
            }
            $values[SearchController::PAGE] = 1;
            $search_results = $this->doSearch($values, $q);
        }
        if ($search_results) {
            $this->set_view_params($values, $search_results, $q);
            $this->save_session_data($values);
        }
        else {
            $this->view->error = 'encountered error in search.';
        }
    }

    
    public function lookaheadAction() {
        $logger = Zend_Registry::get('zvlogger');
        $results = array();
        $request = $this->getRequest();
        if ($request->isPost()) {
           $values = $request->getPost();
           $query = isset($values['query']) ? $values['query'] : '';
           if ($query != '') {
                $sm = $this->_helper->searchManager;
                $q = array('city_state' => strtolower($query)."*");
                $results = $sm->search_ajax($q);
                $this->lookahead = $results;
           }
        }
        $this->view->lookahead = $results;
    }
    
    public function autocompleteAction() {
        $logger = Zend_Registry::get('zvlogger');
        $json = '';
        $request = $this->getRequest();
        if ($request->isGet()) {
           $term = $request->getParam('term');
           if (($term != null) && ($term != '')) {
                $sm = $this->_helper->searchManager;
                $q = array('city_state' => strtolower($term)."*");
                $results = $sm->search_ajax($q);
           }
           $json = Zend_Json::encode(array_values($results));
        }
        $this->getResponse()->setHeader('Content-Type', 'text/json')
                            ->setBody($json)
                            ->sendResponse();
        exit;
    }
    
    public function testAction() {
        $logger = Zend_Registry::get('zvlogger');
        $json = '';
        $request = $this->getRequest();
        if ($request->isGet()) {
           $id = $request->getParam('id');
           if (($id != null) && ($id != '')) {
                $names = array();
                $user = Application_Model_Users::load($id);
                if ($user != null) {
                    $query = array('owner' => $user->getRef());
                    $listings = Application_Model_Listings::find($query);
                    if ($listings != null) {
                        foreach($listings as $listing) {
                            $names[] = $listing->title;
                        }
                    }
                    $json = Zend_Json::encode(array_values($names));
                }
           }
        }
        $this->getResponse()->setHeader('Content-Type', 'text/json')
                            ->setBody($json)
                            ->sendResponse();
        exit;
    }
    
    private function handleCookies($city) {
    	$city=substr($city,1,strlen($city)-2);
    	$serverHost=$_SERVER['HTTP_HOST'];
    	$clientIPAddress=$_SERVER['REMOTE_ADDR'];
    	$clientIPAddress=($clientIPAddress=='')?$_REQUEST['REMOTE_ADDR']:$clientIPAddress;
    	if ( array_key_exists("zipvilla_city", $_COOKIE) ) {
    		setcookie("zipvilla_city",$city,1,"/",$serverHost);//Remove the previous instance of the cookie
    	}
    	setcookie("zipvilla_city",$city,time()+(365*24*60*60),"/",$serverHost);//instill the new instance of the cookie
    }

}
