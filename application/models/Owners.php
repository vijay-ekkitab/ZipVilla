<?php

/*****************************************
 *       The Owners Collection 
 *****************************************/

include_once("ZipVilla/TypeConstants.php");
include_once("Mongo/ModelBase.php");

/*
 * Fields:
 *        emailid
 *        listings [array of dbRefs to listings collection] 
 */

class Application_Model_Owners extends Mongo_ModelBase {

    public static $_collectionName = OWNERS; 
    public static $_collection = null; 
    
    /*
     * Generate a reference to this document 
     */
    public function getRef() {
        return static::$_collection->createDBRef(array('_id' => $this->id));
    }
    
    public function addListing($listing) {
        if ($listing != null) {
            if (!isset($this->document['listings'])) { 
                $this->document['listings'] = array();
            }
            $this->document['listings'][] = $listing->getRef();
            static::update(array("_id"=>$this->id), $this->document);
        }
    }
    
    public function setListings($listings) {
        $refs = array();
        if (($listings != null) && (is_array($listings))) {
            foreach($listings as $listing) {
                $refs[] = $listing->getRef();
            }
            $this->document['listings'] = $refs;
            static::update(array("_id"=>$this->id), $this->document);
        }
    }
    
    public function getListings() {
        $listings = array();
        if (isset($this->document['listings'])) {
            foreach($this->document['listings'] as $ref) {
                $obj = static::$_collection->getDBRef($ref);
                if ($obj != null) {
                    $listings[] = new Application_Model_Listings($obj);
                }
            }
        }
        return($listings);
    }

}
?>
