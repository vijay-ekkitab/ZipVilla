<?php
include_once("ZipVilla/TypeConstants.php");
class ListController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
        $ajaxContext = $this->_helper->getHelper('AjaxContext');
        $ajaxContext->addActionContext('calculate', 'html')
                    ->initContext();
        
    }
    
    public function preDispatch()
    {
        if (!Zend_Auth::getInstance()->hasIdentity()) {
            if ('rate' == $this->getRequest()->getActionName()) {
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
        $this->view->property = $this->_helper->listingsManager->queryById($id);
        $this->_helper->viewRenderer('index');
    }
    
    public function calculateAction() 
    {
        $logger = Zend_Registry::get('zvlogger');
        
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
        $this->view->rate = round($rate * $days);
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
        $this->view->form = $form;
    }
}

