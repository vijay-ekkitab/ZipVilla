<?php

/*****************************************************************************
 *
 *                           Helper for Owners 
 *
 *****************************************************************************/

include_once("ZipVilla/TypeConstants.php");
include_once("ZipVilla/Exception.php");

class ZipVilla_Helper_OwnersManager extends Zend_Controller_Action_Helper_Abstract {

    private $std_fields;
    
    public function __construct()
    { 
       
    }
	
    /*********************************************************************
     * Public Functions
     *********************************************************************/

	/**
	 * Inserts the given record as flat map of key,value pairs
	 * into the Owners DB. 
     * @param array $obj the array containing the data to be inserted.
     & @return APPLICATION_MODEL_OWNERS object
     */
	public function insert($obj) {
        $apObj = new Application_Model_Owners($obj);
        $apObj->save();
        return $apObj;
	}

	/**
	 * Deletes the given record from the Owners DB. 
     * @param integer $id the id of the object to be deleted.
     & @return void. 
     */
	public function delete($id) {
        $apObj = Application_Model_Owners::load($id);
        if ($apObj) {
            $apObj->delete();
        }
	}

	/**
	 * Updates the given record in the Owners DB. 
     */
	public function update($id, $vals) {
		$apObj = $this->queryById($id);
		if ($apObj == null) {
			throwZVException('Cannot update object that does not exist.');
		}
	    if ($vals == null) {
            return $apObj;
        }
		foreach ($vals as $k => $v) {
		    $apObj->{$k} = $v;
		}
        $apObj->save();
        return $apObj;
	}

	/**
	 * Queries the Owners DB for objects that match.  
     * @param array $q the query map.
     & @return array of objects that matched the query. 
     */
	public function query($q=null) {
        return Application_Model_Owners::find($q);
	}

	/**
	 * Queries the Owners DB for a single object by Id.  
     * @param integer $id the object id.
     & @return object with the queried id. 
     */
	public function queryById($id) {
		$idq = array('_id'=>new MongoId($id));
		$res = $this->query($idq);
		if($res != null) {
			return $res[0];
		} else {
			return null;
		}
	}

    /**
     * Returns a database cursor for the query.  
     * @param integer $q the query map.
     & @return cursor for the query. 
     */
    public function getCursor($q=null) {
        return Application_Model_Owners::getCursor($q);
    }
}
?>
