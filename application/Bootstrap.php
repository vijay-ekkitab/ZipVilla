<?php
include("ZipVilla/Logger.php");

Zend_Controller_Action_HelperBroker::addPrefix('ZipVilla_Helper');
$zvlogger = new ZipVilla_Logger();
Zend_Registry::set('zvlogger', $zvlogger);

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{

}

