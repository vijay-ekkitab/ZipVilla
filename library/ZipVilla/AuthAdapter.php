<?php
include_once 'ZipVilla/TypeConstants.php';

class ZipVilla_AuthAdapter implements Zend_Auth_Adapter_Interface
{
    private $_username;
    private $_password;
    private $_fbdata;

    public function __construct($username, $password, $fbdata = null)
    {
        $this->_username = $username;
        $this->_password = $password;
        $this->_fbdata = $fbdata;
    }
    
    public function authenticate()
    {
        if ($this->_fbdata != null) { //User has logged in via FB
            if ((isset($this->_fbdata['firstname'])) &&
                (isset($this->_fbdata['lastname'])) &&
                (isset($this->_fbdata['email']))) {
                 $identity = $this->_fbdata['email'] . AUTH_FIELD_SEPARATOR . 
                             $this->_fbdata['firstname'] . AUTH_FIELD_SEPARATOR . 
                             $this->_fbdata['lastname'];
                 $result =  new Zend_Auth_Result(Zend_Auth_Result::SUCCESS, $identity);
            }
            else {
                $result = new Zend_Auth_Result(Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND,
                                               'facebook user',
                                               array('Facebook credentials not found.'));
            }
        }
        else {
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
                        $user->emailid.AUTH_FIELD_SEPARATOR.$user->firstname.AUTH_FIELD_SEPARATOR.$user->lastname);
                }
                else {
                    $result =  new Zend_Auth_Result(
                        Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID,
                        $this->_username,
                        array('User password is not correct.'));
                }
            }
        }
        return $result;
    }
}
?>