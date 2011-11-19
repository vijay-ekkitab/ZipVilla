<?php

class ZipVilla_ValidateRegisteredUser extends Zend_Validate_Abstract
{
    const EMAIL_EXISTS='emailExists';
    
    protected $_messageTemplates = array(
        self::EMAIL_EXISTS=>'Registered user with email address "%value%" exists.'
    );
    
    public function __construct()
    {
    }
    
    public function isValid($value, $context=null)
    {
        $this->_setValue($value);
        $q = array('emailid' => $value);
        $user = Application_Model_Users::findOne($q);
        if ($user == null) {
            return true;
        }
        else {
            $this->_error(self::EMAIL_EXISTS);
        } 
        return false;
    }
}

?>