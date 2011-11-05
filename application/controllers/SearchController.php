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
                                 'address__street_name',
                                 'address__city', 
                                 'address__state',
                                 'address__coordinates__latitude',
                                 'address__coordinates__longitude', 
                                 'bedrooms',
                                 'guests',
                                 'amenities');
            $facet_fields= array('amenities',
                                 'suitability',
                                 'onsite_services');
            $sm = $this->_helper->searchManager;
            $sm->setSelectFields($select_fields);
            $sm->setFacetFields($facet_fields);
            $q = array('address__city' => $place);
            $search_results = $sm->search($q);
            if ($search_results) {
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

