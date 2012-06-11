<?php
include_once('ZipVilla/TypeConstants.php');

class Zend_View_Helper_DisplayIndexLink extends Zend_View_Helper_Abstract 
{
    public function DisplayIndexLink()
    {
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            $username = $auth->getIdentity();
            $pos = strpos($username,AUTH_FIELD_SEPARATOR);
            $userId = '';
            if ($pos)  {
                $userId= substr($username, 0, $pos);
                if ($userId=="admin@zipvilla.com") {
		            $indexUrl = $this->view->url(array('controller'=>'search','action'=>'reindex'), null, true);            							  
	            	return '<li><a id="index" href="'.$indexUrl.'">Index</a></li>';
                }
            }
                   
        } 
    }
}