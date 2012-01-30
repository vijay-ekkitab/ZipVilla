<?php
include_once("ZipVilla/TypeConstants.php");
//include_once("ZipVilla/Helper/ListingsManager.php");

class AccountController extends Zend_Controller_Action
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

    }

}