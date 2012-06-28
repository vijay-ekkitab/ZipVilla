<?php
include_once('ZipVilla/TypeConstants.php');

class Zend_View_Helper_LoggedInAs extends Zend_View_Helper_Abstract 
{
    public function loggedInAs()
    {
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            $username = $auth->getIdentity();
            $pos = strpos($username,AUTH_FIELD_SEPARATOR);
            $userId = '';
            if ($pos)  {
                $userId= substr($username, 0,$pos);
                $username = substr($username,$pos+strlen(AUTH_FIELD_SEPARATOR));
                $username = str_replace(AUTH_FIELD_SEPARATOR, ' ', $username);
            }
            $logoutUrl = $this->view->url(array('controller'=>'login',
            									'action'=>'logout'), 
            							  null, true);
            							  
            $controller = Zend_Controller_Front::getInstance()->getRequest()->getControllerName();
            if ($controller == 'account') {
                $dashlink = '';
            } 
            else {
                if ($userId == ADMINISTRATOR) {
                    $dashurl  = $this->view->url(array('controller' => 'account',
                                                       'action' => 'review'), null, true);
                }
                else {
                    $dashurl  = $this->view->url(array('controller' => 'account',
                                                       'action' => 'index'), null, true);
                }
                $dashlink = '<li><a href="'.$dashurl.'">Dashboard</a></li>';
            }
            return '<li>Welcome ' . $username . '&nbsp;</li>' . 
                   '<li><a id="logout" href="'.$logoutUrl.'">Logout</a> &nbsp;&nbsp;' .
                   $dashlink . '</li>';
                   
        } 

        $loginUrl = $this->view->url(array('controller'=>'login', 'action'=>'index'));
        return '<a href="'.$loginUrl.'">Login</a>';
    }
}