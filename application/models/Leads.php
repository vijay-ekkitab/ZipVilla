<?php

/*****************************************
 * The Leads (Booking Requests) Collection 
 *****************************************/

include_once("ZipVilla/TypeConstants.php");
include_once("Mongo/ModelBase.php");

/*
 * Fields:
 *        firstname
 *        lastname
 *        email
 *        phone
 *        check_in
 *        check_out
 *        guests
 *        message
 *        listing [DbRef to object in listing collection]
 *        booked
 *        owner
 */

class Application_Model_Leads extends Mongo_ModelBase {

    public static $_collectionName = LEADS; 
    public static $_collection = null; 
    
    /*
     * Generate a reference to this document 
     */
    public function getRef() {
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
    
    public function setOwner($owner) {
        if ($owner != null) {
            $this->document['owner'] = $owner->getRef();
            static::update(array("_id"=>$this->id), $this->document);
        }
    }
    
    public function getOwner() {
        if (isset($this->document['owner'])) {
            $obj = static::$_collection->getDBRef($this->document['owner']);
            if ($obj != null) {
                return new Application_Model_Users($obj);
            }
        }
        return null;
    }

}
?>
