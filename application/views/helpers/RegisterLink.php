<?php

class Zend_View_Helper_RegisterLink extends Zend_View_Helper_Abstract 
{
    public function registerLink()
    {
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            return '';
        } 

        $request = Zend_Controller_Front::getInstance()->getRequest();
        $controller = $request->getControllerName();
        $action = $request->getActionName();
        if($controller == 'login' && $action == 'register') {
            return '';
        }
        
        $registerUrl = $this->view->url(array('controller'=>'login', 'action'=>'register'));
        return '<a href="'.$registerUrl.'">Register</a>';
    }
}