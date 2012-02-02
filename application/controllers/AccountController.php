<?php
include_once("ZipVilla/TypeConstants.php");
//include_once("ZipVilla/Helper/ListingsManager.php");

class AccountController extends Zend_Controller_Action
{

    public function init()
    {
        $ajaxContext = $this->_helper->getHelper('AjaxContext');
        $ajaxContext->addActionContext('getaccountdata', 'html')
                    ->initContext();
    }

    public function preDispatch()
    {
    }
    
    public function indexAction()
    {
        $user = null;
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            $username = $auth->getIdentity();
            $pos = strpos($username,AUTH_FIELD_SEPARATOR);
            $email = substr($username, 0, $pos);
            $q = array('emailid' => $email);
            $user = Application_Model_Users::findOne($q);
        }
        $this->view->user = $user;
    }
    
    protected function getLeads($user, $start=0, $filter='A')
    {
        $q = array('owner' => $user->getRef(),
                   'check_out' => array('$gte' => new MongoDate(strtotime(date('Y-m-d')))));
        switch($filter) {
            case 'B':$q['booked'] = 'yes';
                     break; 
            case 'U':$q['booked'] = 'no';
                     break;
            default: break;
        }
        
        $cursor = Application_Model_Leads::getCursor($q);
        $matches = $cursor->skip($start)->limit(ZV_AC_BOOKINGS_PAGE_SZ);
        $leads = array();
        foreach($matches as $match) {
            $leads[] = new Application_Model_Leads($match);
        }
        $count = $cursor->count(); 
        return array('leads' => $leads, 'max' => $count);
    }
    
    protected function getListings($user, $start=0)
    {
        $q = array('owner' => $user->getRef());
        $cursor = Application_Model_Listings::getCursor($q);
        $matches = $cursor->skip($start)->limit(ZV_AC_LISTINGS_PAGE_SZ);
        $listings = array();
        foreach($matches as $match) {
            $listings[] = new Application_Model_Listings($match);
        }
        $count = $cursor->count();
        return array('listings' => $listings, 'max' => $count);
    }
    
    public function getaccountdataAction()
    {
        $logger = Zend_Registry::get('zvlogger');
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            $username = $auth->getIdentity();
            $values = $this->getRequest()->getPost();
            $type = $values['type'];
            $start = $values['start'];
            $filter = $values['filter'];
            
            $this->view->leads = null;
            $this->view->listings = null;
            $this->view->start = $start;
            
            $pos = strpos($username,AUTH_FIELD_SEPARATOR);
            $email = substr($username, 0, $pos);
            $q = array('emailid' => $email);
            $user = Application_Model_Users::findOne($q);
            if ($user != null) {
                switch($type) {
                    case 'bookings': $this->view->results = $this->getLeads($user, $start, $filter);
                                     $this->view->filter = $filter;
                                     $this->_helper->viewRenderer('bookings');
                                     break;
                    case 'listings': $this->view->results = $this->getListings($user, $start);
                                     $this->_helper->viewRenderer('listings');
                                     break;
                    default:         break;
                }
            }
        }
        else {
            $this->_helper->redirector('index', 'index');
        }
    }
    
    public function newAction()
    {
        
    }
    
    public function basicinfoAction()
    {
    
    }
    
    public function calendarAction()
    {
        
    }

}