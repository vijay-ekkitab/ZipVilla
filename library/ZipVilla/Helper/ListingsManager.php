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

    private $std_fields;
    private $modelclass;
    
    public function __construct($modelclass = 'Application_Model_Listings', $std_fields = null)
    { 
        $this->modelclass = $modelclass;
        if ($std_fields == null) {
            $this->std_fields = array('title', 'address__city', 'address__state', 'address__street_name','address__location',
                                'address__country', 'average_rate', 'address__coordinates__latitude',
                                'guests', 'address__coordinates__longitude', 'images', 'rating', 'reviews');
        }
        else { 
            $this->std_fields = $std_fields;
        }
        if (!in_array("id", $this->std_fields))
            $this->std_fields[] = "id";
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
     & @return $modelclass object
     */
	public function insert($typeName,$rec,$flat=true) {
		$tm = new TypeManager();
		$tp = $tm->getType($typeName);
		if($tp == null) { // Exception. Should not be so.
            throwZVException('Type not registered with TypeManager');
		} else {
			$obj = $flat ? $tp->makeObject($rec) : $rec;
			$obj[INDEXED] = false;
            $apObj = new $this->modelclass($obj);
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
	    $classname = $this->modelclass;
        $apObj = $classname::load($id);
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
	    $classname = $this->modelclass;
        $apObjArray = $classname::find($q);
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
     * Returns a database cursor for the query.  
     * @param integer $q the query map.
     & @return cursor for the query. 
     */
    public function getCursor($q=null) {
        $classname = $this->modelclass;
        return $classname::getCursor($q);
    }
    
	/**
     * Flattens an object.
     * @param integer $obj the object.
     * @param boolean $onlyIndexableProp whether to select only indexable properties.
     & @return object flattened object. 
     */
	public function flatten($obj,$onlyIndexableProp=false) {
		$tm = new TypeManager();
        if ($obj instanceof $this->modelclass) {
            $obj = $obj->getDoc();
        }
		$type = isset($obj[TYPE]) ? $obj[TYPE] : 'home';
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
			return $this->_getAverageRate($listing->getDoc(), $from, $to, $quiet);
		}
		return -1;
	}
	
	public function isAvailable($id, $from, $to) {
		if ($id != null) {
			$listing = $this->queryById($id);
			return ($this->_isAvailable($listing->getDoc(), $from, $to));
		}
		return FALSE;
	}
	
	public function getRates($id, $from, $to) {
	    $rates = array();
	    if ($id != null) {
	        $listing = $this->queryById($id);
	        if ($listing != null) {
	            $std_rate = $listing->rate;
	            $special_rates = $listing->special_rate;
	            if ($special_rates != null) {
	                $fromsec = $from->sec;
	                $tosec = $to->sec;
	                foreach($special_rates as $special) {
	                    $spfromsec = $special['period']['from']->sec;
	                    $sptosec   = $special['period']['to']->sec;
	                    if (($sptosec < $fromsec) || ($spfromsec > $tosec)) 
	                       continue;
	                    $start = $fromsec > $spfromsec ? $fromsec : $spfromsec;
	                    $end =  $tosec < $sptosec ? $tosec : $sptosec;
	                    $rates[] = array('type' => 'special',
	                                     'from' => new MongoDate($start),
	                                     'to'   => new MongoDate($end),
                                         'rate' => $special['rate']['daily']);
	                }
	            }
	            /*$rates[] = array('type' => 'standard',
	                             'from' => null, 
	                             'to'   => null,
 	                             'rate' => $std_rate['daily']);*/
	        }
	    }
	    return $rates;
	}
	
    protected function _getAverageRate($listing, $from, $to, $quiet=TRUE) {
        if ($listing != null) {
            $std_rate = $listing['rate'];
            $special_rates = isset($listing['special_rate']) ? $listing['special_rate'] : null;
            $pmodel = new PriceModel($special_rates, $std_rate);
            return $pmodel->get_average_rate($from, $to, $quiet);
        }
        return -1;
    }
    
    protected function _isAvailable($listing, $from, $to) {
        if ($listing != null) {
            $bookings = isset($listing['booked']) ? $listing['booked'] : null;
            if ($bookings == null)
                return TRUE;
            $av = new Availability();
            return $av->is_available($bookings, $from, $to);
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
	
    private static function sort_by_rate_hl($a, $b) {
        if ($a['average_rate'] < $b['average_rate']) {
            return 1;
        }
        elseif ($a['average_rate'] > $b['average_rate']) {
            return -1;
        }
        return 0;
    }
    
    private static function sort_by_rate_lh($a, $b) {
        if ($a['average_rate'] < $b['average_rate']) {
            return -1;
        }
        elseif ($a['average_rate'] > $b['average_rate']) {
            return 1;
        }
        return 0;
    }
    
    private static function sort_by_rating($a, $b) {
        if ($a['rating'] < $b['rating']) {
            return 1;
        }
        elseif ($a['rating'] > $b['rating']) {
            return -1;
        }
        return 0;
    }
	
    public function getListings($ids, $from=null, $to=null, $start=0, $pagesize=20, 
                                $sortOrder=SORT_ORDER_RATING, $price_range=null) {
        $results = array();
	    
        if (($ids == null) || (!is_array($ids))) {
            return array('docs' => $results, 'count' => 0);
        }
	    
        $m_ids = array();
        foreach($ids as $id) {
            $m_ids[] = new MongoId($id);
        }
	    
        $q = array();
        $q['_id'] = array('$in' => $m_ids);
	    
        if (($from == null) || ($to == null)) {
            $cursor = $this->getCursor($q);
            $count = 0;
            foreach($cursor as $listing) {
                $results[] = $listing;
                $count++;           
            }
            $results = array_slice($results, $start, $pagesize);
            return array('docs' => $results, 'count' => $count);
        }
        
        //if (($sortParams == null) || (!is_array($sortParams))) {
        //    $sortParams = array();
        //}
        
        //$sortParams['field'] = isset($sortParams['field']) ? $sortParams['field'] : 'average_rate';
        
        $cursor = $this->getCursor($q);
        $count = 0;
        if ($price_range == null) {
            $price_range = array(0, 100000);
        }
        foreach($cursor as $listing) {
            if ($this->_isAvailable($listing, $from, $to)) {
                $listing['average_rate'] = $this->_getAverageRate($listing, $from, $to);
                if (($listing['average_rate'] >= $price_range[0]) &&
                    ($listing['average_rate'] <= $price_range[1])) {
                    $listing = $this->select_and_flatten($listing, $this->std_fields);
                    $results[] = $listing;
                    $count++;
                }
            }
        }
	    
        if ($sortOrder == SORT_ORDER_RATING) {
            usort($results, 'static::sort_by_rating');
        }
        elseif ($sortOrder == SORT_ORDER_RATE_HL) {
            usort($results, 'static::sort_by_rate_hl');
        }
        elseif ($sortOrder == SORT_ORDER_RATE_LH) {
            usort($results, 'static::sort_by_rate_lh');
        }
        
        //if ($sortParams['field'] == 'average_rate')
        //    usort($results, 'static::sort_by_rate');
        //elseif ($sortParams['field'] == 'rating')
        //    usort($results, 'static::sort_by_rating');
	    
        $results = array_slice($results, $start, $pagesize);
        return array('docs' => $results, 'count' => $count);
    }
    
    private function select_and_flatten($obj, $include, $prefix='') {
        $map = array();
        if (($obj == null)  || (isset($obj[0])))
            return $map;
        foreach ($obj as $k => $v) {
            
            $check = $prefix.$k;
            if ($check == '_id') {
                $check = 'id';
            }
            if (in_array($check, $include)) {
                $map[$check] = $check == 'id' ? $v->__toString() : $v;
            }
            elseif (is_array($v)) {
                $map = array_merge($map, $this->select_and_flatten($v, $include, $prefix.$k.POINTS));
            }
        }
        return $map;
    }
    
}
?>
