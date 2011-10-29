<?php
include("ZipVilla/Logger.php");

Zend_Controller_Action_HelperBroker::addPrefix('ZipVilla_Helper');
$zvlogger = new ZipVilla_Logger();
Zend_Registry::set('zvlogger', $zvlogger);

$config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV);
Zend_Registry::set('config', $config);

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{

}

