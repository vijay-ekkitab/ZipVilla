<?php
class IndexController extends Zend_Controller_Action
{
    public function init()
    {
        /* Initialize action controller here */
    }
    
	public function preDispatch()
    {
    }

    public function indexAction()
    {
        $logger = Zend_Registry::get('zvlogger');
        
        $sponsoredCollection = Application_Model_SponsoredListings::findAll();
        $sponsoredListings = array();
        foreach($sponsoredCollection as $item) {
            $listing = $item->getListing();
            $reviews = Application_Model_Reviews::count(array('listing' => $listing->getRef()));
            $listing->reviews = $reviews;
            $sponsoredListings[] = $listing;
        }
        $this->view->sponsoredListings = $sponsoredListings;
        
        $mostViewedCollection = Application_Model_MostViewedListings::findAll();
        $mostViewedListings = array();
        foreach($mostViewedCollection as $item) {
            $listing = $item->getListing();
            $reviews = Application_Model_Reviews::count(array('listing' => $listing->getRef()));
            $listing->reviews = $reviews;
            $mostViewedListings[] = $listing;
        }
        $this->view->mostViewedListings = $mostViewedListings;
        
        $bestRatedCollection = Application_Model_BestRatedListings::findAll();
        $bestRatedListings = array();
        foreach($bestRatedCollection as $item) {
            $listing = $item->getListing();
            $reviews = Application_Model_Reviews::count(array('listing' => $listing->getRef()));
            $listing->reviews = $reviews;
            $bestRatedListings[] = $listing;
        }
        $this->view->bestRatedListings = $bestRatedListings;
        
        $mostBookedCollection = Application_Model_MostBookedListings::findAll();
        $mostBookedListings = array();
        foreach($mostBookedCollection as $item) {
            $listing = $item->getListing();
            $reviews = Application_Model_Reviews::count(array('listing' => $listing->getRef()));
            $listing->reviews = $reviews;
            $mostBookedListings[] = $listing;
        }
        $this->view->mostBookedListings = $mostBookedListings;
        
        $this->view->mostViewedListings = $sponsoredListings;
        $this->view->bestRatedListings = $sponsoredListings;
        $this->view->mostBookedListings = $sponsoredListings;
        
    }


}







