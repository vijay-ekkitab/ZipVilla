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
        $place = $this->_getParam('city', 0);
        if ($place) {
            $select_fields=array('type', 
                                 'rating',
                                 'address__street_name',
                                 'address__city', 
                                 'address__state',
                                 'address__coordinates__latitude',
                                 'address__coordinates__longitude', 
                                 'rate__daily',
                                 'bedrooms',
                                 'guests',
                                 'amenities');
            $facet_fields= array('amenities',
                                 'suitability',
                                 'onsite_services');
            $sm = $this->_helper->searchManager;
            $sm->setSelectFields($select_fields);
            $sm->setFacetFields($facet_fields);
            //$sm->setSortField('rating');
            $sm->setSortField('rate__daily', SolrQuery::ORDER_ASC);
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
                        $q[$tmp[0]] = $tmp[1];
                    }
                }
            }
            $search_results = $sm->search($q);
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

