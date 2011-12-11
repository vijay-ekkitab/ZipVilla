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
        $sponsoredcollection = Application_Model_SponsoredListings::findAll();
        $sponsoredlistings = array();
        foreach($sponsoredcollection as $sponsored) {
            $listing = $sponsored->getListing();
            $reviews = Application_Model_Reviews::count(array('listing' => $listing->getRef()));
            $listing->reviews = $reviews;
            $sponsoredlistings[] = $listing;
        }
        $this->view->sponsoredlistings = $sponsoredlistings;
        $this->view->mostviewedlistings = $sponsoredlistings;
        $this->view->mostbookedlistings = $sponsoredlistings;
        $this->view->mostratedlistings = $sponsoredlistings;
        
    }


}







