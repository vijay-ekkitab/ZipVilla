<?php
include_once("ZipVilla/TypeConstants.php");
class ListController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
        
    }

    public function indexAction()
    {
        $logger = Zend_Registry::get('zvlogger');
        
        $id = $this->_getParam('id', null);
        
        if ($id != null) {
            $this->view->property = $this->_helper->listingsManager->queryById($id);
        }
        else {
            $this->_helper->redirector('index');
        }
    }
}

