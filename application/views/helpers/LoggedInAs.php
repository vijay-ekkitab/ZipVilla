<?php

class Zend_View_Helper_LoggedInAs extends Zend_View_Helper_Abstract 
{
    public function loggedInAs()
    {
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            $username = $auth->getIdentity();
            $logoutUrl = $this->view->url(array('controller'=>'login',
            									'action'=>'logout'), 
            							  null, true);
            return 'Welcome ' . $username .  '. <a href="'.$logoutUrl.'">Logout</a>';
        } 

        $loginUrl = $this->view->url(array('controller'=>'login', 'action'=>'index'));
        return '<a href="'.$loginUrl.'">Login</a>';
    }
}