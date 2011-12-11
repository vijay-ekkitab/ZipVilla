<?php

/*****************************************
 *       The Users Collection 
 *****************************************/

include_once("ZipVilla/TypeConstants.php");
include_once("Mongo/ModelBase.php");

/*
 * Fields:
 *        emailid
 *        firstname
 *        lastname
 *        password
 *        image
 */

class Application_Model_Users extends Mongo_ModelBase {

    public static $_collectionName = USERS; 
    public static $_collection = null; 
    
    protected $_salt = '12ab34cd56ef78gh90ij';
    
    public function __construct($document = null) {
        if($document != null && (!isset($document['_id'])) && (isset($document['password']))) {
           $document['password'] = sha1($document['password'] . $this->_salt);
        }
        parent::__construct($document);
    }
    /*
     * Generate a reference to this document 
     */
    public function getRef() {
        static::init();
        return static::$_collection->createDBRef(array('_id' => $this->id));
    }
    
    public function isValidPsw($psw) {
        if ($this->document['password'] == sha1($psw . $this->_salt))
            return TRUE;
        return FALSE;
    }

}
?>
