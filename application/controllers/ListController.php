<?php
include_once("ZipVilla/TypeConstants.php");
include_once("ZipVilla/Helper/ListingsManager.php");
include_once(APPLICATION_PATH."/controllers/SearchController.php");

class ListController extends Zend_Controller_Action
{

    const USER_RATING  = 'user_rating';
    const PROPERTY_ID  = 'id';
    const MESSAGE      = 'message';
    const TITLE        = 'title';
    const CONTENT      = 'content';
    const RATING       = 'rating';
    const COMMENTS     = 'comments';
    const USERNAME     = 'username';
    const USER_EMAIL   = 'email';
    const SUBJECT      = 'subject';
    
    //protected $requireslogin = array('rate', 'submitreview');
    
    protected function read_value($values, $index, $default=null)
    {
        return isset($values[$index]) ? $values[$index] : $default;
    }

    public function init()
    {
        /* Initialize action controller here */
        $ajaxContext = $this->_helper->getHelper('AjaxContext');
        $ajaxContext->addActionContext('calculate', 'json')
                    ->addActionContext('getreviews', 'json')
                    ->addActionContext('checklogin', 'json')
                    ->addActionContext('getcalendar', 'html')
                    ->addActionContext('form', 'json')
                    ->initContext();
    }

    public function preDispatch()
    {
        /*if (!Zend_Auth::getInstance()->hasIdentity()) {
            if (in_array($this->getRequest()->getActionName(), $this->requireslogin)) {
                // Save the requested Uri
                $this->_helper->lastDecline->saveRequestUri();
                // Only logged in users have access to the access controlled pages;
                // Direct all other users to the Login Page.
                $this->_helper->redirector('index', 'login');
            }
        }*/
    }
    
    protected function readSession() {
        $session = new Zend_Session_Namespace(SESSION_NAME);
        $session_values = isset($session->values) ? $session->values : array();
        $values = array();
        $values[SearchController::CHECKIN]    = $this->read_value($session_values, SearchController::CHECKIN);
        $values[SearchController::CHECKOUT]   = $this->read_value($session_values, SearchController::CHECKOUT);
        $values[SearchController::GUESTS]     = $this->read_value($session_values, SearchController::GUESTS, 1);
        $values[ListController::USER_RATING]  = $this->read_value($session_values, ListController::USER_RATING, 0);
        $values[ListController::PROPERTY_ID]  = $this->read_value($session_values, ListController::PROPERTY_ID, 0);
        return $values;
    }
    
    protected function saveSession($values) {
        $session = new Zend_Session_Namespace(SESSION_NAME);
        $session_values = isset($session->values) ? $session->values : array();
        foreach($values as $key => $value) {
            $session_values[$key] = $value;
        }
        $session->values = $session_values;
    }

    public function indexAction()
    {
        $logger = Zend_Registry::get('zvlogger');

        $id = $this->_getParam(ListController::PROPERTY_ID, null);
        $showtab = $this->_getParam(SearchController::SHOWTAB, 0);

        if ($id != null) {
            $this->view->property = $this->_helper->listingsManager->queryById($id);
            $session_values = $this->readSession();
            $this->view->{SearchController::CHECKIN}  = $session_values[SearchController::CHECKIN];
            $this->view->{SearchController::CHECKOUT} = $session_values[SearchController::CHECKOUT];
            $this->view->{SearchController::GUESTS}   = $session_values[SearchController::GUESTS];
            if ($session_values[ListController::PROPERTY_ID] == $this->view->property->id) {
                $this->view->{ListController::USER_RATING} = $session_values[ListController::USER_RATING];
            }
            else {
                $this->saveSession(array(ListController::PROPERTY_ID => $this->view->property->id,
                                  ListController::USER_RATING => 0));
            }
            $this->saveSession(array(SearchController::SHOWTAB => $showtab));
        }
        else {
            $this->_helper->redirector('index', 'index');
        }
    }

    public function rateAction()
    {
        $id = $this->_getParam('id', null);
        if ($id != null) {
            $score = $this->_getParam('score', 0);
            $this->view->property = $this->_helper->listingsManager->queryById($id);
            $session_values = $this->readSession();
            $this->view->{SearchController::CHECKIN}  = $session_values[SearchController::CHECKIN];
            $this->view->{SearchController::CHECKOUT} = $session_values[SearchController::CHECKOUT];
            $this->view->{SearchController::GUESTS}   = $session_values[SearchController::GUESTS];
            $this->saveSession(array(ListController::USER_RATING => $score));
            $this->view->{ListController::USER_RATING} = $score;
            $this->_helper->viewRenderer('index');
            $url = $this->view->getHelper('BaseUrl')->setBaseUrl('..');
        }
        else {
            $this->_helper->redirector('index', 'index');
        }
    }
    
    protected function getLoggedInUser()
    {
        $auth = Zend_Auth::getInstance();
        if (!$auth->hasIdentity()) {
            return null;
        }
        $username = $auth->getIdentity();
        $userParams = explode(AUTH_FIELD_SEPARATOR, $username);
        return $userParams;
    }

    protected function submitReview($values)
    {
        $userEmail = $this->read_value($values, ListController::USER_EMAIL);
        $date = date('d-m-Y');
        $id = $this->read_value($values, ListController::PROPERTY_ID, 0);
        $title = $this->read_value($values, ListController::TITLE);
        $content = $this->read_value($values, ListController::CONTENT);
        $rating = $this->read_value($values, ListController::RATING, 0);
        
        $review = array('title' => $title,
                        'content' => $content,
                        'rating' => $rating,
                        'date' => $date);
            
        $dbr = new Application_Model_Reviews($review);
        $property = Application_Model_Listings::findOne(array("_id"=>new MongoId($id)));
        $user = Application_Model_Users::findOne(array("emailid"=>$userEmail));
        if ($property && $user) {
            $dbr->save();
            $dbr->setListing($property);
            $dbr->setUser($user);
        }
    }

    public function calculateAction()
    {
        $this->_helper->viewRenderer->setNoRender();    
        $request = $this->getRequest();
        $rate = 0;
        $days = 1;
        if ($request->isPost()) {
            $values = $request->getPost();
            $id = isset($values['id']) ? $values['id'] : null;
            $checkin = isset($values['checkin']) ? $values['checkin'] : null;
            $checkout = isset($values['checkout']) ? $values['checkout'] : null;
            $guests = isset($values['guests']) ? $values['guests'] : 1;
            if (($id != null) && ($id != '') && ($checkin != '') && ($checkout != '')) {
                $lm = $this->_helper->listingsManager;
                $start = new MongoDate(strtotime($checkin));
                $end   = new MongoDate(strtotime($checkout));
                $days = ($end->sec - $start->sec) / 86400;
                if ($days > 0) {
                    $property = $lm->queryById($id);
                    if ($property) {
                        $pmodel = new PriceModel($property->special_rate, $property->rate);
                        $rate = $pmodel->get_average_rate($start, $end);
                    }
                }
                //save session data
                $this->saveSession(array(SearchController::CHECKIN   => $checkin,
                                  SearchController::CHECKOUT  => $checkout,
                                  SearchController::GUESTS    => $guests));
            }
            elseif (($id != null) && ($id != '')) {
                $property = $this->_helper->listingsManager->queryById($id);
                $rate = $property->rate['daily'];
            }
        }
        
        //$this->view->rate = round($rate * $days);
        echo Zend_Json::encode(array('total' => round($rate*$days)));
    }
    
    public function getreviewsAction() {
        $this->_helper->viewRenderer->setNoRender();
        $logger = Zend_Registry::get('zvlogger');
        $request = $this->getRequest();
        $reviews = null;
        $baseurl = '';
        $start = 0;
        if ($request->isPost()) {
            $values = $request->getPost();
            $id = isset($values['id']) ? $values['id'] : null;
            $start = isset($values['start']) ? $values['start'] : 0;
            $baseurl = isset($values['baseurl']) ? $values['baseurl'] : '';
            $property = Application_Model_Listings::findOne(array("_id"=>new MongoId($id)));
            if ($property) {
                $reviews = Application_Model_Reviews::find(array('listing' => $property->getRef()));
            }
        }
        $pages = array();
        if ($reviews == null)
            return;
        $numpages = ceil(count($reviews) / REVIEWS_PER_PAGE);
        $thispage = ceil(($start + 1) / REVIEWS_PER_PAGE);
        $firstpage = 1;
        $lastpage = $numpages;
            
        if ($lastpage > $firstpage) {
            $backpages = $thispage - $firstpage;
            $pagesahead = $lastpage - $thispage;
            $prevpage = FALSE;
            $nextpage = FALSE;
            if (($backpages > 0) && ($pagesahead > 0)){
                $prevpage = $thispage-1;
                $nextpage = $thispage+1;
                $pages[0] = $thispage -1;
                $pages[1] = $thispage;
                $pages[2] = $thispage +1;
            }
            elseif ($backpages > 0) {
                $prevpage = $thispage-1;
                if ($backpages >= 2) {
                    $pages[0] = $thispage -2;
                    $pages[1] = $thispage -1;
                    $pages[2] = $thispage;
                }
                else {
                    $pages[0] = $thispage -1;
                    $pages[1] = $thispage;
                }
            }
            elseif ($pagesahead > 0) {
                $nextpage = $thispage+1;
                if ($pagesahead >= 2) {
                    $pages[0] = $thispage;
                    $pages[1] = $thispage +1;
                    $pages[2] = $thispage +2;
                }
                else {
                    $pages[0] = $thispage;
                    $pages[1] = $thispage +1;
                }
            }
        }
        $user_reviews = '';
        for ($i=$start; ($i<count($reviews)) && ($i < ($start + REVIEWS_PER_PAGE)); $i++) {
            $review = $reviews[$i];
            $user = $review->getUser();
            $user_reviews = $user_reviews . '<div class="list_review">' .
                                            '<div class="img_profle">' .
                                            '<img src="'.$baseurl.'/images/user/'.$user->image.'"/>' .
                                            '<p>' . $user->firstname . '<br><span>' . $review->date. '</span></p>' .
                                            '</div>' .
                                            '<div class="review"> <h2>' . $review->title . '</h2>' .
                                            '<p>' . $review->content . '</p>' .
                                            '</div> </div>';
        }
        
        $user_reviews_notice = '';
        $num1 = ($thispage - 1)*REVIEWS_PER_PAGE + 1;
        $num2 = ($thispage - 1)*REVIEWS_PER_PAGE + REVIEWS_PER_PAGE;
        $num2 = $num2 > count($reviews) ? count($reviews) : $num2;
        if (count($reviews) > 0) {
            $user_reviews_notice = '<div class="display_no"><p>Displaying <span>' .
                                   $num1 .
                                   '</span> — <span>' .
                                   $num2 . 
                                   '</span> of <span>' .
                                   count($reviews) . 
                                   '</span> reviews</p></div>';
        }
        
        $user_reviews_pages = '';
        if (count($pages) > 0) {

             $user_reviews_pages = '<div class="pagination"> <ul>' ;
             foreach ($pages as $pg) {
                if ($pg == $thispage) {
                    $user_reviews_pages .= '<li class="current">' .
                                           $pg .
                                           '</li>';
                }
                else {
                    $num = ($pg-1)*REVIEWS_PER_PAGE;
                    $user_reviews_pages .= '<li><a href="#" onclick="getReviews(' .
                                           "'" . $property->id . "'," .
                                           $num . ',' .
                                            "'" . $baseurl . "'" .
                                           '); return false;">' .
                                           $pg .
                                           '</a></li>';
                }
             }
             $user_reviews_pages .= '</ul></div>';
             $user_reviews_pages .= '<div class="l_page_buttons">' ;
             if ($prevpage) {
                $num = ($thispage-2)*REVIEWS_PER_PAGE;
                $user_reviews_pages .= '<a href="#" onclick="getReviews(' .
                                       "'" . $property->id . "'," . 
                                       $num . ',' .
                                       "'" . $baseurl . "'" .
                                       '); return false;">' .
                                       '<div class="btn_prev_page"><img src="' .
                                       $baseurl . '/images/btn_prev_page.jpg" '.
                                       'alt="Previous page"/></div></a>' ;
             }
             if ($nextpage) {
                 $num = ($thispage)*REVIEWS_PER_PAGE;
                 $user_reviews_pages .= '<a href="#" onclick="getReviews(' .
                                        "'" . $property->id . "'," . 
                                       $num . ',' .
                                       "'" . $baseurl . "'" .
                                       '); return false;">' .
                                       '<div class="btn_next_page"><img src="' .
                                       $baseurl . '/images/btn_next.jpg" ' .
                                       'alt="Next page" /></div></a>' ;
             }
             $user_reviews_pages .= '</div>'; 
        }
        echo Zend_Json::encode(array('user_reviews' => $user_reviews,
                                     'user_reviews_notice' => $user_reviews_notice,
                                     'user_reviews_pages'=> $user_reviews_pages));
        
        /*$this->view->reviews = $reviews;
        $this->view->start = $start;
        $this->view->baseurl = $baseurl;*/
    }
    
    public function addownerAction()
    {
        $form = new Application_Form_AddOwner();
        $form->submit->setLabel('Save');
        $logger = Zend_Registry::get('zvlogger');
        if ($this->getRequest()->isPost()) {
            $formData = $this->getRequest()->getPost();
            if ($form->isValid($formData)) {
                $userid = $form->getValue('dropdown');
                $id = $form->getValue('id');
                if ($userid != null) {
                    $user = Application_Model_Users::load($userid);
                    $property = $this->_helper->listingsManager->queryById($id);
                    $property->setOwner($user);
                    $this->_helper->redirector('index', 'list', 'default', array('id' => $id));
                }
            }
            else {
                $form->populate($formData);
            }
        } else {
            $id = $this->_getParam('id', 0);
            $form->getElement('id')->setValue($id);
        }
        $this->view->getHelper('BaseUrl')->setBaseUrl('..');
        $this->view->form = $form;
        $this->view->headline = 'Please select Owner.';
    }
    
    public function checkloginAction()
    {
        $this->_helper->viewRenderer->setNoRender();
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
           $username = $auth->getIdentity();
           $username = str_replace(AUTH_FIELD_SEPARATOR, ',', $username);
        }
        else
            $username = "";
            
        echo $username;
    }
    
    public function getcalendarAction()
    {
        $logger = Zend_Registry::get('zvlogger');
        $request = $this->getRequest();
        $rate = 0;
        $days = 1;
        $datestr = '';
        if ($request->isPost()) {
            $datestr = $request->getPost('date', date('M  Y'));
            $id = $request->getPost('id', null);
        }
        if ($datestr == '') {
            $this->_helper->viewRenderer->setNoRender();
            return;
        }
        $startday = date('N',strtotime('01 '.$datestr));
        $startday = $startday%7;
        $tmp1 = explode(',', date('m,Y', strtotime($datestr)));
        $numdays = cal_days_in_month(CAL_GREGORIAN, $tmp1[0], $tmp1[1]);
        
        $booked = array();
        $specials = array();
        $special_dates = array();
        
        if ($id) {
            $lm = new ZipVilla_Helper_ListingsManager();
            $from = new MongoDate(strtotime($datestr));
            $booked_dates = $lm->getBookingCalendar($id, $from, $numdays);
            foreach($booked_dates as $b) {
                $f = intval(date('j', $b['from']->sec));
                $t = intval(date('j', $b['to']->sec));
                for ($i=$f;$i<=$t; $i++)
                    $booked[] = $i;
            }
            $to = new MongoDate($from->sec  + (86400 * ($numdays-1)));
            //$logger->debug('>>> From: '.date('d-m-Y', $from->sec).'  To: '.date('d-m-Y', $to->sec));
            $special_rates = $lm->getRates($id, $from, $to);
            foreach($special_rates as $s) {
                $f = intval(date('j', $s['from']->sec));
                $t = intval(date('j', $s['to']->sec));
                for ($i=$f;$i<=$t; $i++) {
                    $specials[$i] = $s['rate'];
                }
            }
            $special_dates = array_keys($specials);
        }
        
        $tmp2 = explode(',', date('m,j'));
        $today = 0;
        if ($tmp2[0] == $tmp1[0]) { //same month
            $today = $tmp2[1];
        }
        $html = '<table><tbody><tr><th>SUN</th><th>MON</th><th>TUE</th><th>WEN</th><th>THU</th><th>FRI</th><th>SAT</th></tr>';
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
        $this->view->html = $html;
    }
    
    protected function prepareAndSendMsg($data)
    {
        $response = 'OK';
        $body = '';
        
        $subject = $data[ListController::SUBJECT];
        unset($data[ListController::SUBJECT]);
        
        foreach($data as $k => $v) {
            $topic = strtoupper($k);
            $body .= "$topic: " . $data[$k] ."\n";
        }
        
        $zvconfig = Zend_Registry::get('config');
        $logger = Zend_Registry::get('zvlogger');
        
        if (isset($zvconfig->zipvilla->support->email)) {
            $config = array('auth' => 'login', 
                            'port' => 25, 
                            'username' => $zvconfig->zipvilla->support->user, 
                            'password' => $zvconfig->zipvilla->support->password);
        
            $transport = new Zend_Mail_Transport_Smtp($zvconfig->zipvilla->support->mailserver, $config);
        
            $mail = new Zend_Mail();
            $mail->setBodyText($body);
        
            $mail->setFrom($zvconfig->zipvilla->support->email, "Zipvilla")
                 ->addTo($zvconfig->zipvilla->support->email, "Zipvilla Support")
                 ->setSubject($subject);
                 
            try {
                 $mail->send($transport);
            }
            catch(Exception $e) {
                $response = $e->getMessage();
            }
            return $response;
        }
    }
    
    public function formAction()
    {
        $logger = Zend_Registry::get('zvlogger');
        $this->_helper->viewRenderer->setNoRender();
        $request = $this->getRequest();
        $response = '';
        
        if ($request->isPost()) {
            $values = $request->getPost();
            $userParams = $this->getLoggedInUser();
            if ($userParams == null) {
                $response = 'You must be logged in to do this operation.';
                echo $response;
                return;
            }
            
            $values[ListController::USER_EMAIL] = $userParams[0];
            
            if (isset($values[ListController::MESSAGE])) {//send message
                $values[ListController::SUBJECT] = "Inquiry";
                $values[ListController::USERNAME] = $userParams[1].' '.$userParams[2];
                $response = 'Thank you. Your message has been forwarded to the owner.';
                $status = $this->prepareAndSendMsg($values);
                if ($status != 'OK') {
                   $response = $status;
                }
               
            }
            elseif (isset($values[ListController::TITLE])) {//submit review
                $this->submitReview($values);
                $response = 'Thank you for your feedback. We appreciate your inputs.';
            }
            
            elseif (isset($values[ListController::COMMENTS])) {
                $values[ListController::SUBJECT] = "Reservation Request";
                $response = 'Thank you. Your request has been forwarded to the owner.';
                $status = $this->prepareAndSendMsg($values);
                if ($status != 'OK') {
                   $response = $status;
                }
            }
            $this->saveSession(array(ListController::USER_RATING => $this->read_value($values, ListController::RATING, 0))) ;
        }
        echo $response;
    }
    
        
                    
                    
    
}