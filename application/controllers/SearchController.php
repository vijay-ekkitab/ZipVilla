<?php

class SearchController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
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
        $guests = $this->_getParam('guests', 0);
        //$logger->debug("SearchController> checkin=[$checkin] checkout=[$checkout] guests=[$guests]");
        $place = $this->_getParam('query', 0);
        if ($place) {
            $sm = $this->_helper->searchManager;
            $q = array('city_state' => $place);
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
                        $q[$tmp[0]][] = $tmp[1];
                    }
                }
            }
            $search_results = $sm->search($q, $checkin, $checkout, $guests);
            
            if ($search_results) {
                $this->view->search_query = $place;
                $this->view->facet_query = isset($facetstr) ? $facetstr : null;
                $this->view->facets_used = $facets;
                $this->view->results = $search_results['docs'];
                if (isset($search_results['facets'])) {
                    $this->view->facets = $search_results['facets'];
                }
            }
            else {
                $this->view->error = 'encountered error in search.';
            }
        }
        else 
            $this->_helper->redirector('index', 'index');
    }


}

