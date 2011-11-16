<?php

/*****************************************
 *       The Listings Collection 
 *****************************************/

include_once("ZipVilla/TypeConstants.php");
include_once("Mongo/ModelBase.php");

class Application_Model_Listings extends Mongo_ModelBase {

    public static $_collectionName = LISTINGS; 
    public static $_collection = null; 
    
    /*
     * Generate a reference to this document 
     */
    public function getRef() {
        return static::$_collection->createDBRef(array('_id' => $this->id));
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
                return new Application_Model_Owners($obj);
            }
        }
        return null;
    }

}
?>
