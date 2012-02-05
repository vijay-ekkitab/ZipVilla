<?php
include_once("ZipVilla/TypeConstants.php");
include_once("ZipVilla/Helper/ListingsManager.php");
include_once("ZipVilla/PriceModel.php");
include_once("ZipVilla/Availability.php");

class AccountController extends Zend_Controller_Action
{

    const SHARED  = 'shared';
    const GUESTS  = 'guests';
    const BEDROOMS = 'bedrooms';
    const BATHS = 'baths';
    const TITLE = 'title';
    const DESCRIPTION = 'description';
    const DAILY_RATE = 'price_day';
    const WEEKLY_RATE = 'price_week';
    const MONTHLY_RATE = 'price_month';
    const LONGITUDE = 'longitude';
    const LATITUDE = 'latitude';
    const ADDRESS = 'address';
    const LOCATION = 'location';
    const CITY = 'city';
    const STATE = 'state';
    const ZIPCODE = 'zipcode';
    const AMENITIES = 'amenities';
    const SERVICES = 'onsite_services';
    const SUITABILITY = 'suitability';
    const HOUSERULES = 'house_rules';
    
    public function init()
    {
        $ajaxContext = $this->_helper->getHelper('AjaxContext');
        $ajaxContext->addActionContext('getaccountdata', 'html')
                    ->addActionContext('getcalendar', 'html')
                    ->addActionContext('updatecalendar', 'html')
                    ->addActionContext('deleteprelisting', 'html')
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
        $listings = array();
        $q = array('owner' => $user->getRef());
        $cursor1 = Application_Model_Listings::getCursor($q);
        $cursor2 = Application_Model_PreListings::getCursor($q);
        $count1 = $cursor1->count();
        $count2 = $cursor2->count();
        if ($start < $count1) {
            $matches = $cursor1->skip($start)->limit(ZV_AC_LISTINGS_PAGE_SZ);
            foreach($matches as $match) {
                $listings[] = new Application_Model_Listings($match);
            }
        }
        if (count($listings) < ZV_AC_LISTINGS_PAGE_SZ) {
            $limit = ZV_AC_LISTINGS_PAGE_SZ - count($listings);
            $start = $start > $count1 ? ($start - $count1) : 0; 
            $matches = $cursor2->skip($start)->limit($limit);
            foreach($matches as $match) {
                $listing = new Application_Model_Listings($match);
                $listing->prelisting = true;
                $listings[] = $listing;
            } 
        }
        return array('listings' => $listings, 'max' => $count1+$count2);
    }
    
    public function deleteprelistingAction()
    {
        $values = $this->getRequest()->getPost();
        $id = $values['id'];
        $start = $values['start'];
        $listing = Application_Model_PreListings::load($values['id']);
        if ($listing != null) {
            $listing->delete();
        }
        $this->view->start = $start;
        $user = $this->getUser();
        $this->view->results = $this->getListings('Application_Model_PreListings',$user, $start);
        $this->_helper->viewRenderer('prelistings');
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
                    /*case 'prelistings': $this->view->results = $this->getListings('Application_Model_PreListings',$user, $start);
                                        $this->_helper->viewRenderer('prelistings');
                                        break;*/
                    default:         break;
                }
            }
        }
        else {
            $this->_helper->redirector('index', 'index');
        }
    }
    protected function getUser()
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
        return $user;
    }
    protected function updateListing($values)
    {
        $listing = null;
        if (isset($values['id']) && ($values['id'] != '')) {
            $listing = Application_Model_PreListings::load($values['id']);
            if (!$listing) {
                $listing = null;
            }
        }
        if ($listing == null) {
            $listing = new Application_Model_PreListings();
            $user = $this->getUser();
            if ($user != null) {
                $listing->setOwner($user);
            }
        }
        if (isset($values[AccountController::SHARED]))
            $listing->shared        = $values[AccountController::SHARED];
        if (isset($values[AccountController::GUESTS]))
            $listing->guests        = $values[AccountController::GUESTS];
        if (isset($values[AccountController::BEDROOMS]))
            $listing->bedrooms      = $values[AccountController::BEDROOMS];
        if (isset($values[AccountController::BATHS]))
            $listing->baths         = $values[AccountController::BATHS];
        if (isset($values[AccountController::TITLE]))
            $listing->title         = $values[AccountController::TITLE];
        if (isset($values[AccountController::DESCRIPTION]))
            $listing->description   = $values[AccountController::DESCRIPTION];
        if (isset($values[AccountController::DAILY_RATE]))
            $listing->{'rate.daily'} = $values[AccountController::DAILY_RATE];
        if (isset($values[AccountController::WEEKLY_RATE]))
            $listing->{'rate.weekly'} = $values[AccountController::WEEKLY_RATE];
        if (isset($values[AccountController::MONTHLY_RATE]))
            $listing->{'rate.monthly'} = $values[AccountController::MONTHLY_RATE];
        if (isset($values[AccountController::LONGITUDE]))
            $listing->{'address.coordinates.longitude'} = $values[AccountController::LONGITUDE];
        if (isset($values[AccountController::LATITUDE]))
            $listing->{'address.coordinates.latitude'} = $values[AccountController::LATITUDE];
        if (isset($values[AccountController::ADDRESS]))
            $listing->{'address.street_name'} = $values[AccountController::ADDRESS];
        if (isset($values[AccountController::LOCATION]))
            $listing->{'address.location'} = $values[AccountController::LOCATION];
        if (isset($values[AccountController::CITY]))
            $listing->{'address.city'} = $values[AccountController::CITY];
        if (isset($values[AccountController::STATE])) {
            $listing->{'address.state'} = $values[AccountController::STATE];
            $listing->{'address.country'} = 'India';
        }
        if (isset($values[AccountController::ZIPCODE]))
            $listing->{'address.zipcode'} = $values[AccountController::ZIPCODE];
        if (isset($values[AccountController::AMENITIES]))
           $listing->{'amenities'} = $values[AccountController::AMENITIES];
        if (isset($values[AccountController::SERVICES]))
           $listing->{'onsite_services'} = $values[AccountController::SERVICES];
        if (isset($values[AccountController::SUITABILITY]))
           $listing->{'suitability'} = $values[AccountController::SUITABILITY];
        if (isset($values[AccountController::HOUSERULES]))
           $listing->{'house_rules'} = $values[AccountController::HOUSERULES];
        
        $listing->save();
        return $listing;
    }
    
    public function newAction()
    {
        $logger = Zend_Registry::get('zvlogger');
        $request = $this->getRequest();
        $values = $request->getParams();
        $nextpage = isset($values['nextpage']) ? $values['nextpage'] : 1;
        $listing = $this->updateListing($values);
        $this->view->listing = $listing;
        switch($nextpage) {
            case 1:  $this->_helper->viewRenderer('new1');
                     break;
            case 2:  $lm = new ZipVilla_Helper_ListingsManager();
                     $this->view->amenities = $lm->getEnumOptions('amenities');
                     $this->view->onsite_services = $lm->getEnumOptions('onsite_services');
                     $this->view->suitability = $lm->getEnumOptions('suitability');
                     $this->_helper->viewRenderer('new2');
                     break;
            case 3:  $this->_helper->viewRenderer('calendar');
                     break;
            case 4:  $this->_helper->redirector('index', 'account');
                     break; 
            default:  $this->_helper->viewRenderer('new1');
                      break;
        }
    }
    
    /*public function calendarAction()
    {
        $request = $this->getRequest();
        if ($request->isGet()) {
            $this->_helper->redirector('index', 'account');
        }
    }*/
    
    protected function getCalendarHtml($id, $datestr, $modelclass='Application_Model_PreListings')
    { 
        $html = '';
        if (($id == null) || ($id == '') || ($datestr == null) || ($datestr == '')) { 
            return $html;
        }
        $booked = array();
        $specials = array();
        $special_dates = array();
        
        $tmp1 = explode(',', date('m,Y', strtotime($datestr)));
        $numdays = cal_days_in_month(CAL_GREGORIAN, $tmp1[0], $tmp1[1]);
        $tmp2 = explode(',', date('m,j'));
        $today = 0;
        if ($tmp2[0] == $tmp1[0]) { //same month
            $today = $tmp2[1];
        }
        $startday = date('N',strtotime('01 '.$datestr));
        $startday = $startday%7;
        
        $lm = new ZipVilla_Helper_ListingsManager($modelclass);
        
        $from = new MongoDate(strtotime($datestr));
        $booked_dates = $lm->getBookingCalendar($id, $from, $numdays);
        foreach($booked_dates as $b) {
            $f = intval(date('j', $b['from']->sec));
            $t = intval(date('j', $b['to']->sec));
            $days = $b['days'];
            for ($i=$f;$i<=$f+$days; $i++)
                $booked[] = $i;
        }
        $to = new MongoDate($from->sec  + (86400 * ($numdays-1)));
        $special_rates = $lm->getRates($id, $from, $to);
        foreach($special_rates as $s) {
            $f = intval(date('j', $s['from']->sec));
            $t = intval(date('j', $s['to']->sec));
            for ($i=$f;$i<=$t; $i++) {
                $specials[$i] = $s['rate'];
            }
        }
        $special_dates = array_keys($specials);
        
       
        
        $html = '<table><tbody><tr><th>SUN</th><th>MON</th><th>TUE</th><th>WED</th><th>THU</th><th>FRI</th><th>SAT</th></tr>';
        $i=0;
        
        $html .= '<tr>';
        for($i=0; $i<$startday; $i++) {
            $html .= '<td><span></span></td>';
        }
        for ($start=1; $start<=$numdays; $start++,$i++) {
            if (($i>0) && ($i%7 == 0)) {
                $html .= '</tr><tr>';
            }
            $class = '';
            $special_price = '';
            if ($start >= $today) {
                if (in_array($start, $booked)) {
                    $class = 'unavail';
                }
                else {
                    $class = 'avail';
                }
                if (in_array($start, $special_dates)) {
                    $special_price = '</br><div class="today_price">Rs.'.$specials[$start].'</div>';
                }
            }
            $html .= '<td class="'.$class.'"><span>'.$start.'</span>'.$special_price.'</td>';
        }
        $html .= '</tr></tbody></table>';
        
        return $html;
        
    }
    
    public function getcalendarAction()
    {
        $request = $this->getRequest();
        $rate = 0;
        $days = 1;
        $datestr = '';
        if ($request->isPost()) {
            $datestr = $request->getPost('date', date('M  Y'));
            $id = $request->getPost('id', null);
        }
        if (($datestr == '') || ($id == '')) {
            $this->_helper->viewRenderer->setNoRender();
            return;
        }
        
        $html = $this->getCalendarHtml($id, $datestr);
        $this->view->html = $html;
    }
    
    protected function updateAvailability($id, $from, $to, $available, $price, $modelclass='Application_Model_PreListings')
    {
        $logger = Zend_Registry::get('zvlogger');
        $lm = new ZipVilla_Helper_ListingsManager($modelclass);
        $listing = $lm->queryById($id);
        if ($listing == null) {
            return;
        }
        $dailyrate = $listing->{'rate.daily'};
        if ($dailyrate == null) {
            return;
        }
        
        $addrate = ($price == $dailyrate) ? false : true;
        $specials = $listing->special_rate;
        if ($specials == null) {
            $specials = array();
        }
        $rate = array('daily' => $price);
        
        $specials = PriceModel::addSpecialRate($specials, 
                                               new MongoDate(strtotime($from)),
                                               new MongoDate(strtotime($to)),
                                               $rate, $addrate);
        $listing->special_rate = $specials;
        
        $addbooking = ($available == 'yes') ? false : true;
        $booked = $listing->booked;
        if ($booked == null) {
            $booked = array();
        }
        
        $booked = Availability::addBooking($booked, 
                                           new MongoDate(strtotime($from)),
                                           new MongoDate(strtotime($to)),
                                           $addbooking);
        
        $listing->booked = $booked;
        $listing->save();
    }
    
    function updatecalendarAction()
    {
        $logger = Zend_Registry::get('zvlogger');
        $request = $this->getRequest();
        $values = array();
        if ($request->isPost()) {
            $values = $request->getPost();
        }
        if (count($values) != 6) {
            $this->_helper->viewRenderer->setNoRender();
            return;
        }
        
        $id         = $values['id'];
        if ($id == '') {
            $this->_helper->viewRenderer->setNoRender();
            return;
        }
        $from       = $values['from'];
        $to         = $values['to'];
        $available  = $values['available'];
        $price      = $values['price'];
        $date       = $values['date'];
        
        $this->updateAvailability($id, $from, $to, $available, $price);
        
        $html = $this->getCalendarHtml($id, $date, 'Application_Model_PreListings');
        
        $this->view->html = $html;
        $this->_helper->viewRenderer('getcalendar');
    }

}

