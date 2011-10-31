<?php

/*****************************************************************************
 *
 *                           Helper for Listings 
 *
 *****************************************************************************/

include_once("TypeManager.php");
include_once("ZipVilla/TypeConstants.php");
include_once("ZipVilla/Exception.php");
include_once("ZipVilla/PriceModel.php");
include_once("ZipVilla/Availability.php");

class ZipVilla_Helper_ListingsManager extends Zend_Controller_Action_Helper_Abstract {

    public function __construct()
    { 
	
    }
	
    /*********************************************************************
     * Public Functions
     *********************************************************************/

	/**
	 * Inserts the given record as flat map of key,value pairs
	 * into the Listings DB. 
     * @param string $typeName the type of the listing such as resort, hotel etc.
     * @param array $rec the array containing the data to be inserted.
     * @param boolean $flat whether data should be flattened before insert.
     & @return APPLICATION_MODEL_LISTINGS object
     */
	public function insert($typeName,$rec,$flat=true) {
		$tm = new TypeManager();
		$tp = $tm->getType($typeName);
		if($tp == null) { // Exception. Should not be so.
            throwZVException('Type not registered with TypeManager');
		} else {
			$obj = $flat ? $tp->makeObject($rec) : $rec;
			$obj[INDEXED] = false;
            $apObj = new Application_Model_Listings($obj);
            $apObj->save();
            return $apObj;
		}
	}

	/**
	 * Deletes the given record from the Listings DB. 
     * @param integer $id the id of the object to be deleted.
     & @return boolean success or failure. 
     */
	public function delete($id) {
        $apObj = Application_Model_Listings::load($id);
        if ($apObj) {
            $apObj->delete();
        }
	}

	/**
	 * Updates the given record in the Listings DB. 
     */
	public function update($id, $vals) {
		
		$apObj = $this->queryById($id);
		if ($apObj == null) {
			throwZVException('Cannot update object that does not exist.');
		}
		$tm = new TypeManager();
		$typeName = $apObj->type;
		if ($typeName == null) {
			throwZVException('Internal Error. Type for existing object could not be determined.');
		}
		$tp = $tm->getType($typeName);
		if($tp == null) { // Exception. Should not be so.
            throwZVException('Internal Error. Type for existing object not registered with TypeManager');
		} else {
			$doc = $tp->updateObject($apObj->getDoc(), $vals);
			$apObj->setDoc($doc); 
            $apObj->save();
		}
        return $apObj;
	}

	/**
	 * Queries the Listings DB for objects that match.  
     * Flattens the returned results if required.
     * @param array $q the query map.
     * @param boolean $flat whether to flatten or not.
     * @param boolean $onlyIndexableProp whether to select only indexable properties.
     & @return array of objects that matched the query. 
     */
	public function query($q=null,$flat=false,$onlyIndexableProp=false) {
        $apObjArray = Application_Model_Listings::find($q);
		$res = array();
		foreach ($apObjArray as $obj) {
			array_push($res,($flat ? $this->flatten($obj,$onlyIndexableProp) : $obj));
		}	
		return (count($res) == 0) ? null : $res;
	}

	/**
	 * Queries the Listings DB for a single object by Id.  
     * Flattens the returned object if required.
     * @param integer $id the object id.
     * @param boolean $flat whether to flatten or not.
     * @param boolean $onlyIndexableProp whether to select only indexable properties.
     & @return object with the queried id. 
     */
	public function queryById($id,$flat=false,$onlyIndexableProp=false) {
		$idq = array('_id'=>new MongoId($id));
		$res = $this->query($idq,$flat,$onlyIndexableProp);
		if($res != null) {
			return $res[0];
		} else {
			return null;
		}
	}

	/**
     * Flattens an object.
     * @param integer $obj the object.
     * @param boolean $onlyIndexableProp whether to select only indexable properties.
     & @return object flattened object. 
     */
	public function flatten($obj,$onlyIndexableProp=false) {
		$tm = new TypeManager();
        if ($obj instanceof Application_Model_Listings) {
            $obj = $obj->getDoc();
        }
		$type = $obj[TYPE];
		$tp = $tm->getType($type);
		$fres = $tp->flatten($obj,$onlyIndexableProp);
		$fres[TYPE] = $type;
		return $fres;
	}
	
	public function getEnumOptions($name) {
		$enums = Application_Model_Enumerations::findAll();
		foreach ($enums as $enum) {
			$doc = $enum->getDoc();
			if (array_key_exists($name, $doc)) {
				return $doc[$name];
			}
		}		
		return null;
	}
	
	public function getAverageRate($id, $from, $to, $quiet=TRUE) {
		if ($id != null) {
			$listing = $this->queryById($id);
			if ($listing != null) {
				$std_rate = $listing->rate;
				$special_rates = $listing->special_rate;
				$pmodel = new PriceModel($special_rates, $std_rate);
				return $pmodel->get_average_rate($from, $to, $quiet);
			}
		}
		return -1;
	}
	
	public function isAvailable($id, $from, $to) {
		if ($id != null) {
			$listing = $this->queryById($id);
			if ($listing != null) {
				$bookings = $listing->booked;
				$av = new Availability();
				return $av->is_available($bookings, $from, $to);
			}
		}
		return FALSE;
	}

	public function getBookingCalendar($id, $from, $days, $quiet=TRUE) {
		if (($id != null) && ($from != null) && ($days > 0)){
			$listing = $this->queryById($id);
			if ($listing != null) {
				$bookings = $listing->booked;
				$av = new Availability();
				return $av->get_booked_dates($bookings, $from, $days, $quiet);
			}
		}
		return array();
	}
}
?>
