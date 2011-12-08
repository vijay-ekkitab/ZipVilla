<?php
include_once("ZipVilla/TypeConstants.php");
class ListController extends Zend_Controller_Action
{

    protected $requireslogin = array('rate', 'submitreview');

    public function init()
    {
        /* Initialize action controller here */
        $ajaxContext = $this->_helper->getHelper('AjaxContext');
        $ajaxContext->addActionContext('calculate', 'json')
                    ->addActionContext('getreviews', 'json')
                    ->addActionContext('checklogin', 'json')
                    ->initContext();
    }

    public function preDispatch()
    {
        if (!Zend_Auth::getInstance()->hasIdentity()) {
            if (in_array($this->getRequest()->getActionName(), $this->requireslogin)) {
                // Save the requested Uri
                $this->_helper->lastDecline->saveRequestUri();
                // Only logged in users have access to the Rating Page;
                // Direct all other users to the Login Page.
                $this->_helper->redirector('index', 'login');
            }
        }
    }

    public function indexAction()
    {
        $logger = Zend_Registry::get('zvlogger');

        $id = $this->_getParam('id', null);

        if ($id != null) {
            $this->view->property = $this->_helper->listingsManager->queryById($id);
            $session = new Zend_Session_Namespace('guest_data');
            $this->view->checkin  = $session->checkin;
            $this->view->checkout = $session->checkout;
            $this->view->guests   = $session->guests;
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
            $session = new Zend_Session_Namespace('guest_data');
            $this->view->checkin  = $session->checkin;
            $this->view->checkout = $session->checkout;
            $this->view->guests   = $session->guests;
            $this->view->user_rating = $score;
            $this->_helper->viewRenderer('index');
            $url = $this->view->getHelper('BaseUrl')->setBaseUrl('..');
        }
        else {
            $this->_helper->redirector('index', 'index');
        }
    }

    public function submitreviewAction()
    {
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            $username = $auth->getIdentity();
        }
        else {
            $this->_helper->redirector('index', 'index'); //error!
        }
        $pos = strpos($username,AUTH_FIELD_SEPARATOR);
        if ($pos)  {
            $username = substr($username,0,$pos);
        }
        $request = $this->getRequest();
        if ($request->isPost()) {
            $values = $request->getPost();
            $id = isset($values['id']) ? $values['id'] : null;
            $title = isset($values['title']) ? $values['title'] : '';
            $content = isset($values['content']) ? $values['content'] : '';
            $rating = isset($values['rating']) ? $values['rating'] : 0;
            $date = date('d-m-Y');
            $review = array('title' => $title,
                            'content' => $content,
                            'rating' => $rating,
                            'date' => $date);
            $dbr = new Application_Model_Reviews($review);
            $property = Application_Model_Listings::findOne(array("_id"=>new MongoId($id)));
            $user = Application_Model_Users::findOne(array("emailid"=>$username));
            if ($property && $user) {
                $dbr->save();
                $dbr->setListing($property);
                $dbr->setUser($user);
            }
            $this->view->property = $property;
            $session = new Zend_Session_Namespace('guest_data');
            $this->view->checkin  = $session->checkin;
            $this->view->checkout = $session->checkout;
            $this->view->guests   = $session->guests;
            $this->view->user_rating = $rating;
            $this->_helper->viewRenderer('index');
            $url = $this->view->getHelper('BaseUrl')->setBaseUrl('..');
        }
        else {
            $this->_helper->redirector('index', 'index'); //error!
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
                                           $num .
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
                                       $num .
                                       '); return false;">' .
                                       '<div class="btn_prev_page"><img src="' .
                                       $baseurl . '/images/btn_prev_page.jpg"'.
                                       'alt="Previous page"/></div></a>' ;
             }
             if ($nextpage) {
                 $num = ($thispage)*REVIEWS_PER_PAGE;
                 $user_reviews_pages .= '<a href="#" onclick="getReviews(' .
                                       $num .
                                       '); return false;">' .
                                       '<div class="btn_next_page"><img src="' .
                                       $baseurl . '/images/btn_next.jpg"' .
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
        if ($auth->hasIdentity())
            $response = "yes";
        else
            $response = "no";
            
        echo $response;
    }
    
}

