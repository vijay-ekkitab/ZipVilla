<?php

class Zend_View_Helper_LoggedInAs extends Zend_View_Helper_Abstract 
{
    public function loggedInAs()
    {
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            $username = $auth->getIdentity();
            $pos = strpos($username,AUTH_FIELD_SEPARATOR);
            if ($pos)  {
                $username = substr($username,$pos+strlen(AUTH_FIELD_SEPARATOR));
            }
            $logoutUrl = $this->view->url(array('controller'=>'login',
            									'action'=>'logout'), 
            							  null, true);
            return 'Welcome ' . $username .  '. <a href="'.$logoutUrl.'">Logout</a>';
        } 

        $loginUrl = $this->view->url(array('controller'=>'login', 'action'=>'index'));
        return '<a href="'.$loginUrl.'">Login</a>';
    }
}