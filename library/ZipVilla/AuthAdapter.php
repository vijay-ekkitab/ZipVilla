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
    
    private function addFBUserIfNotInUsers($fbdata)
    {
        $user = Application_Model_Users::findOne(array('emailid' => $fbdata['email']));
        if ($user == null) {
            $fbdata['emailid'] = $fbdata['email'];
            unset($fbdata['email']);
            $user = new Application_Model_Users($fbdata);
            $user->save();
        }
    }
    
    public function authenticate()
    {
        if ($this->_fbdata != null) { //User has logged in via FB
            if ((isset($this->_fbdata['firstname'])) &&
                (isset($this->_fbdata['lastname'])) &&
                (isset($this->_fbdata['email']))) {
                 $this->addFBUserIfNotInUsers($this->_fbdata);
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
    
    public static function getUserLogin($identity)
    {
        $pos = strpos($identity,AUTH_FIELD_SEPARATOR);
        if ($pos > 0) {
            return substr($identity, 0, $pos);
        }
        else {
            return '';
        }
    }
    
    public static function getUserFirstName($identity)
    {
        $pos1 = strpos($identity, AUTH_FIELD_SEPARATOR);
        $pos2 = strrpos($identity, AUTH_FIELD_SEPARATOR);
        if (($pos1 > 0) && ($pos2 > 0)) {
            return substr($identity, $pos1+strlen(AUTH_FIELD_SEPARATOR), $pos2-$pos1-strlen(AUTH_FIELD_SEPARATOR));
        }
        else {
            return '';
        }
    }
    
    public static function getUserLastName($identity)
    {
        $pos = strrpos($identity, AUTH_FIELD_SEPARATOR);
        if ($pos > 0) {
            return substr($identity, $pos+strlen(AUTH_FIELD_SEPARATOR));
        }
        else {
            return '';
        }
    }
}
?>