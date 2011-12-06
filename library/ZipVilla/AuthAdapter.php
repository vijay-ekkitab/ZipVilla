<?php
include_once 'ZipVilla/TypeConstants.php';

class ZipVilla_AuthAdapter implements Zend_Auth_Adapter_Interface
{
    private $_username;
    private $_password;

    public function __construct($username, $password)
    {
        $this->_username = $username;
        $this->_password = $password;
    }
    
    public function authenticate()
    {
        $user = Application_Model_Users::findOne(array('emailid' => $this->_username));
        if ($user == null) {
            $result = new Zend_Auth_Result(
                Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND,
                $this->_username,
                array('User is not known to the system.'));
        }
        else {
            if ($user->isValidPsw($this->_password)) {
                $result =  new Zend_Auth_Result(
                    Zend_Auth_Result::SUCCESS,
                    $user->emailid.AUTH_FIELD_SEPARATOR.$user->firstname);
            }
            else {
                $result =  new Zend_Auth_Result(
                    Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID,
                    $this->_username,
                    array('User password is not correct.'));
            }
        }
        
        return $result;
    }
}
?>