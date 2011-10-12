<?php

/****************************************************************************
 *
 *                             Helper for Types
 *
 ****************************************************************************/

include_once("ZipVilla/TypeConstants.php");
include_once("ZipVilla/Utils.php");
include_once("ZipVilla/Exception.php");

class TypeManager {

	private static $attrs       = array();
	private static $types       = array();
	private static $typeNames   = array();
    private static $initialized = FALSE;

	private static function init() {
        if (static::$initialized) {
            return;
        }
		$attribsArray = Application_Model_Attributes::find();	
		foreach ($attribsArray as $at) {
            $z = $at->getDoc();
			static::$attrs[$z[NAME]] = $z;
		}
		$typesArray = Application_Model_Types::find();	
		foreach($typesArray as $t) { 
            $z = $t->getDoc();
            array_push(static::$typeNames,$z[NAME]);
        }
        static::$initialized = TRUE;

	}

	private function _attrDefinition($name) {

		$def = null;
		$rtype = array_search($name,static::$typeNames);
		if($rtype != FALSE) {
			$def = array(
				NAME        => $name,
				IS_TYPE     => TRUE
			);		
		} else {
			$def = array(
				NAME        => $name,
				IS_TYPE     => FALSE,
				DATA_TYPE   => "string",
				VALUE_TYPE  => "simple",
				KEYWORD	    => "true",
				FACET	    => "true"
			);
			$en = array_key_exists($name,static::$attrs) ? static::$attrs[$name] : null;
			if($en != null) {
				if(array_key_exists(DATA_TYPE,$en))   $def[DATA_TYPE]  = $en[DATA_TYPE];
				if(array_key_exists(VALUE_TYPE,$en))  $def[VALUE_TYPE] = $en[VALUE_TYPE];
				if(array_key_exists(KEYWORD,$en))     $def[KEYWORD]    = $en[KEYWORD];
				if(array_key_exists(FACET,$en))       $def[FACET]      = $en[FACET];
			}
		}
		return $def;
	}

    public function getTypeNames() {
        static::init();
        return static::$typeNames;
    }

	public function getType($typeName) {

        static::init();

		$temp = array_key_exists($typeName,static::$types) ? static::$types[$typeName] : null;

		if($temp != null) {
			return $temp;
		}

		$p = null;
            
		$tpa = Application_Model_Types::findOne(array(NAME => $typeName));	

		if($tpa == null) {
			return null; //no such type definition
		}	

        $tp = $tpa->getDoc();
        
		$pname = array_key_exists(PARENT,$tp) ? $tp[PARENT] : null;
		if( $pname != null) {
			$p = $this->getType($pname);	
		}

		$alist = $tp[ATTRIBUTES];
		if($alist != null) {
			$props = array();
			foreach ($alist as $aname) {
				$prop = null;
				$def = $this->_attrDefinition($aname);
				$prop = new Attribute($def);
				$props[$aname] = $prop;
			}
		}
		$type = new Type($tp,$props,$p);
		static::$types[$typeName] = $type;
		return $type;
	}	
}

class Attribute {

	private  $map;

	public function __construct($amap) {
		$this->map = $amap;
	}

	public function getName() {
		return $this->map[NAME];
	}

	public function isType() {
		return $this->map[IS_TYPE] == TRUE;
	}

	public function isIndexable() {
		return ($this->map[FACET] != 'false') || ($this->map[KEYWORD] != 'false');
	}

	public function isMultiValued() {
		return array_key_exists(VALUE_TYPE,$this->map) && ($this->map[VALUE_TYPE] == 'multi-valued');
	}

	public function isNumeric() {
		return array_key_exists(DATA_TYPE,$this->map) && ($this->map[DATA_TYPE] == 'numeric');
	}

	public function isBoolean() {
		return array_key_exists(DATA_TYPE,$this->map) && ($this->map[DATA_TYPE] == 'boolean');
	}

	public function convertValue($val) {
		if($this->isNumeric()) {
			return floatval($val);
		} else if($this->isBoolean()) {
			return (bool)$val;
		} else {
			return $val;
		}
	}

	public function convertValues($valStr) {
		if($valStr == null) {
			return null;
		}
		if($this->isMultiValued()) {
            if (is_array($valStr)) {
                $vals_array = $valStr;
            }
            elseif (is_string($valStr)) {
                $vals_array = explode(',', $valStr);
            }
            else {
                throwZVException("Invalid type of argument supplied to convertValues"); 
            }
            for ($i=0; $i<count($vals_array); $i++) {
				$vals[] = $this->convertValue($vals_array[$i]);
			}
			return $vals;
		} else {
			$convVal = $this->convertValue($valStr); 
			return $convVal;
		}
	}

	public function str() {
		$s = "";
		foreach ($this->map as $n => $v) {
			$s = $s . " , " .$n."=".$v;
		}
		return $s;
	}

}

class Type {

	private $map;
	private $attrs;
	private $parent;

	public function __construct($amap,$props,$apar) {

		$this->map = $amap;
		$this->attrs = $props;
		$this->parent = $apar;
	}

	public function getName() {
		return $this->map[NAME];
	}

	/**********************************************************************
     * IMPORTANT
	 * It is assumed that the attribute name is unique in type hierarchy
	 * - significant, but not a bad assumption for our case
	 **********************************************************************/

	public function getAttributes() {
		$props = array();
		$tm = new TypeManager();
		foreach ($this->attrs as $n => $val) {
			$rtype = $tm->getType($n);
			if($rtype != null) {
				$relAttrs = $rtype->getAttributes();
				foreach ($relAttrs as $rn => $val) {
					$props[$n . POINTS . $rn] = $val;
				} 
			} else {
				$props[$n] = $val;
			}
		}
		if($this->parent != null) {
			$pprop = $this->parent->getAttributes();
			$pname = $this->parent->getName();
			foreach ($pprop as $n => $val) {
				$props[$n] = $val;
			}
		}
		return $props;
	} 	

	public function getAttribute($attrName) {
		$attrs = $this->getAttributes();
		return $attrs[$attrName];
	}

	public function getParent() {
		return $parent;
	}

	function setParent($apar) {
		$this->parent = $apar;	
	}

	public function getHierachicalAttributes() {
		$l = array();
		if($this->parent != null) {
			$pattrs = $this->parent->getHierachicalAttributes();
			foreach ($pattrs as $pa) {
				array_push($l,$pa);
			}			
		} 
		foreach($this->attrs as $n => $att) {
			array_push($l,$att);
		}
		return $l;

	}

    /**
     * create an object - copying the values from the map provided
     */
	function makeObject($values) {
		$tm = new TypeManager();
		$obj = array();
		$obj[TYPE] = $this->getName();
		$hattrs = $this->getHierachicalAttributes();
		foreach($hattrs as  $attr) {
			$aname = $attr->getName();
			$tval = null;
			if($attr->isType()) {
				$rtype = $tm->getType($aname);
				$tval = $rtype->makeObject($values);				
                if ($tval != null) {
				    $obj[$aname] = $tval;
                }
			} else {
				$tval = array_key_exists($aname,$values) ? $values[$aname] : null;
			    if($tval != null) {
				    $obj[$aname] = $attr->convertValues($tval);
			    }
			}
		}
		if(count($obj) == 1) {
			return null;
		} else {
			return $obj;
		}
	}

	public function attributeIndexable($attr) {
    
		$tm = new TypeManager();
		$tp = $this;

        $ar = explode(POINTS, $attr);

		$an = $ar[count($ar)-1];
		$tn = count($ar) > 1 ? $ar[count($ar)-2] : null;
		if($tn != null) {
			$tp = $tm->getType($tn);
		}
		$l = $tp->getHierachicalAttributes();
		$at = null;
		foreach ($l as $a) {
			if($a->getName() == $an) {
			  $at = $a;
			  break;
			} 
		}
		return ($at != null) ? $at->isIndexable() : false;		
	}

	/**
	 * Returns a flattened version of the object as a map
	 * If index-only attributes are sought, only attributes 
	 * where KEYWORD or FACET are set are included
     */
	function flatten($obj,$indexableOnly=false) {

        if ($obj instanceof Application_Model_Listings) {
            $obj = $obj->getDoc();
        }
		$attrs = $this->getAttributes();
		$map = array();
		foreach ($attrs as $name => $attr) {
			$val = getValue($obj,$name);
			if($val != null) {
				$bool = $this->attributeIndexable($name);
				if($bool ||!$indexableOnly) {
					$map[$name] = $val;
				}
			}
		}
        // Add the db id
        if (isset($obj['_id'])) {
            $map["id"] = $obj['_id'];
        }
		return $map;
	}

} 
?>
