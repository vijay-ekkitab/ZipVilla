<?php

/*****************************************
 *       The Reviews Collection 
 *****************************************/

include_once("ZipVilla/TypeConstants.php");
include_once("Mongo/ModelBase.php");


/*
 * Fields:
 *        user  [dbRef to users collection]
 *        title
 *        content
 *        rating
 *        date
 *        listing [dbRef to listings collection]
 */

class Application_Model_Reviews extends Mongo_ModelBase {

    public static $_collectionName = REVIEWS; 
    public static $_collection = null; 

    /*
     * Generate a reference to this document 
     */
    public function getRef() {
        static::init();
        return static::$_collection->createDBRef(array('_id' => $this->id));
    }
    
    public function setListing($listing) {
        if ($listing != null) {
            $this->document['listing'] = $listing->getRef();
            static::update(array("_id"=>$this->id), $this->document);
        }
    }
    
    public function getListing() {
        if (isset($this->document['listing'])) {
            $obj = static::$_collection->getDBRef($this->document['listing']);
            if ($obj != null) {
                return new Application_Model_Listings($obj);
            }
        }
        return null;
    }
    
    public function setUser($user) {
        if ($user != null) {
            $this->document['user'] = $user->getRef();
            static::update(array("_id"=>$this->id), $this->document);
        }
    }
    
    public function getUser() {
        if (isset($this->document['user'])) {
            $obj = static::$_collection->getDBRef($this->document['user']);
            if ($obj != null) {
                return new Application_Model_Users($obj);
            }
        }
        return null;
    }
    
}
?>
