<?php
include_once("ZipVilla/TypeConstants.php");
class ListController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
        
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
}

