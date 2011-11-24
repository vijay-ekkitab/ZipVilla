<?php
include_once("ZipVilla/TypeConstants.php");
class SearchController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
        $ajaxContext = $this->_helper->getHelper('AjaxContext');
        $ajaxContext->addActionContext('lookahead', 'html')
                    ->initContext();
    }

    public function reindexAction() 
    {
        $im = $this->_helper->indexManager;
        $im->delete();
        $im->indexAll();
        $this->_helper->redirector('index', 'index');
    }
    
    public function indexAction()
    {
        $logger = Zend_Registry::get('zvlogger');
        
        $checkin = $this->_getParam('check_in', null);
        $checkout = $this->_getParam('check_out', null);
        $guests = $this->_getParam('guests', 1);
        $page = $this->_getParam('page', 1);
        $sortorder = $this->_getParam('sort', SORT_ORDER_RATING);
        $place = $this->_getParam('query', 0);
        $price_range = null;
        //$logger->debug("<Search Controller> cin=$checkin,cout=$checkout,g=$guests,p=$page,s=$sortorder,q=$place.");
        $sm = $this->_helper->searchManager;
            
        if (!$place) {
            $place = '*';
        }
        if ($place == '*') {
            $q = array('city_state' => $place);
        }
        else {
            $q = array('city_state' => '"'.$place.'"');
        }
            
        $facetstr = $this->_getParam('facet', 0);
        $facets = array();
        if ($facetstr) {
            $parts = explode(',', $facetstr);
            foreach ($parts as $part) {
                $tmp = explode('=', $part);
                if (count($tmp) == 2) {
                    $tmp[0] = trim($tmp[0]);
                    $tmp[1] = trim($tmp[1]);
                    if (!in_array($tmp[0].$tmp[1],$facets))
                        $facets[] = $tmp[0].$tmp[1];
                    if (!isset($q[$tmp[0]])) {
                        $q[$tmp[0]] = array();
                    }
                    $q[$tmp[0]][] = '"'.$tmp[1].'"';
                }
            }
        }
            
        $search_results = $sm->search($q, $checkin, $checkout, $guests, $sortorder, $price_range, $page, PAGE_SZ);
            
        if ($search_results) {
            $this->view->search_query = $place;
            $this->view->facet_query = isset($facetstr) ? $facetstr : null;
            $this->view->checkin = $checkin;
            $this->view->checkout = $checkout;
            $this->view->guests = $guests;
            $this->view->facets_used = $facets;
            $this->view->results = $search_results['docs'];
            $this->view->total_hits = $search_results['count'];
            $this->view->page = $page;
            $this->view->pagesz = PAGE_SZ;
            $this->view->sortorder = $sortorder;
            if (isset($search_results['facets'])) {
                $this->view->facets = $search_results['facets'];
            }
        }
        else {
            $this->view->error = 'encountered error in search.';
        }
    }

    public function refinedAction()
    {
        $logger = Zend_Registry::get('zvlogger');
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $this->_helper->redirector('index', 'index');
        }
        
        $values = $request->getPost();
        
        $checkin = isset($values['check_in']) ? $values['check_in'] : null;
        $checkout = isset($values['check_out']) ? $values['check_out'] : null;
        $guests = isset($values['guests']) ? $values['guests'] : 1;
        $page = isset($values['page']) ? $values['page'] : 1;
        $sortorder = isset($values['sort']) ? $values['sort'] : SORT_ORDER_RATING;
        $place = isset($values['query']) ? $values['query'] : 0;
        $keywords = isset($values['keywords']) ? $values['keywords'] : 0;
        $price_range = isset($values['price_range']) ? $values['price_range'] : "";
        if (preg_match_all('/([0-9]+)/', $price_range, $matches)) {
            $price_range = $matches[0];
        }
        else {
            $price_range = array(MIN_RATE, MAX_RATE);
        }
        
        $sm = $this->_helper->searchManager;
            
        if (!$place) {
            $place = '*';
        }
        if ($place == '*') {
            $q = array('city_state' => $place);
        }
        else {
            $q = array('city_state' => '"'.strtolower($place).'"');
        }
        
        if ($request->isPost()) {
            foreach ($values as $key => $value) {
                if (is_array($value)) {
                    if (!isset($q[$key])) {
                        $q[$key] = array();
                    }
                    foreach($value as $v) {
                        $v = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $v);
                        $q[$key][] = '"' . $v . '"';
                    }
                }
            }
        }
        
        if ($keywords) {
            $keyword_array = explode(' ', $keywords);
            for($i=0; $i<count($keyword_array); $i++) {
                $keyword_array[$i] = '"'.$keyword_array[$i].'"';
            }
            $q['description'] = $keyword_array;
        }
        
        $search_results = $sm->search($q, $checkin, $checkout, $guests, $sortorder, $price_range, $page, PAGE_SZ);
            
        if ($search_results) {
            $this->view->search_query = $place;
            //$this->view->facet_query = isset($facetstr) ? $facetstr : null;
            $this->view->checkin = $checkin;
            $this->view->checkout = $checkout;
            $this->view->guests = $guests;
            //$this->view->facets_used = $facets;
            $this->view->results = $search_results['docs'];
            $this->view->total_hits = $search_results['count'];
            $this->view->page = $page;
            $this->view->pagesz = PAGE_SZ;
            $this->view->sortorder = $sortorder;
            $this->view->price_range = $price_range;
            $this->view->keywords = $keywords;
            if (isset($search_results['facets'])) {
                $this->view->facets = $search_results['facets'];
            }
            unset($q['city_state']);
            $this->view->facets_selected = $q;
        }
        else {
            $this->view->error = 'encountered error in search.';
        }
        
        $this->_helper->viewRenderer('index');
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
    

}

