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
                KEYWORD     => "true",
                FACET       => "false"
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
    private  static $enums = null;

    public function __construct($amap) {
        $this->map = $amap;
        $this->initEnumerations();
    }

    private function initEnumerations() {
        if (self::$enums == null) {
            $enumsArray = Application_Model_Enumerations::find();
            if (count($enumsArray) != 1) {
                throwZVException("Initialization of Attribute enumerations failed.");
            }
            self::$enums = $enumsArray[0];
        }
    }
    
    private function isValidEnum($value) {
        $name = $this->getName();
        $validvalues = self::$enums->$name;
        if ($validvalues == null) {
            return FALSE;
        }
        foreach($validvalues as $validvalue) {
            if (strcasecmp($value, $validvalue) == 0) {
                return $validvalue;
            }
        }
        return FALSE;
    }
    
    public static function getAllEnumerations() {
        if (self::$enums == null) {
            new Attribute(array()); //To initialize the enum array, in case it has not yet been.
        }
        $doc = self::$enums->getDoc();
        unset($doc['_id']);
        return $doc;
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
        return array_key_exists(VALUE_TYPE,$this->map) && ($this->map[VALUE_TYPE] == MULTI_VALUED);
    }
    
    public function isEnumerated() {
        return array_key_exists(VALUE_TYPE,$this->map) && ($this->map[VALUE_TYPE] == ENUMERATED);
    }

    public function isFloat() {
        return array_key_exists(DATA_TYPE,$this->map) && ($this->map[DATA_TYPE] == FLOAT);
    }
    
    public function isInteger() {
        return array_key_exists(DATA_TYPE,$this->map) && ($this->map[DATA_TYPE] == INTEGER);
    }

    public function isBoolean() {
        return array_key_exists(DATA_TYPE,$this->map) && ($this->map[DATA_TYPE] == BOOLEAN);
    }
    
    public function isDate() {
        return array_key_exists(DATA_TYPE,$this->map) && ($this->map[DATA_TYPE] == DATE);
    }
    
    public function getIndexInfo($val) {
        if ($this->isEnumerated() && is_array($val)) {
            return array_keys($val);
        }
        return $val;
    }

    public function convertValue($val) {
        if($this->isFloat()) {
            return floatval($val);
        }
        elseif($this->isInteger()) {
            return intval($val);
        }
        elseif($this->isBoolean()) {
            return (bool)$val;
        }
        elseif($this->isEnumerated()) {
            $result = array();
            foreach($val as $k => $v) {
                if ($k = $this->isValidEnum($k)) {
                    $result[$k] = $v;
                }
            }
            return $result;
        }
        elseif($this->isDate()) {
            if (is_string($val)) {
                return (new MongoDate(strtotime($val)));
            }
        }
        return $val;
    }

    public function convertValues($valStr) {
        if($valStr == null) {
            return null;
        }
        elseif($this->isMultiValued() || $this->isEnumerated()) {
            if (is_array($valStr)) {
                $vals_array = $valStr;
            }
            elseif (is_string($valStr)) {
                $vals_array = explode(',', $valStr);
                if ($this->isEnumerated()) {
                    $tmp_array = array();
                    foreach($vals_array as $val) {
                        $tmp = split(ENUM_KEY_SPLITTER, $val);
                        $tmp_array[$tmp[0]] = isset($tmp[1]) ? $tmp[1] : '';
                    }
                    $vals_array = $tmp_array;
                } 
            }
            else {
                throwZVException("Invalid type of argument supplied to convertValues"); 
            }
            $vals = $this->convertValue($vals_array);
            //$vals = array();
            //for ($i=0; $i<count($vals_array); $i++) {
            //foreach ($vals_array as $key => $value)
            //    $value = $this->convertValue($vals_array[$i]);
            //    if ($value != null) {
            //        $vals[] = $value;
            //    }
            //}
            return $vals;
        }
        else {
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
    
    public function isList() {
        if (isset($this->map[REPEATS])) {
            return ($this->map[REPEATS] == "true" ? TRUE : FALSE);
        }
        return false; 
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
    public function makeObject($values) {
        if (!is_array($values)) {
            return null;
        }
        $tm = new TypeManager();
        $obj = array();
        $obj[TYPE] = $this->getName();
        $hattrs = $this->getHierachicalAttributes();
        foreach($hattrs as  $attr) {
            $aname = $attr->getName();
            $tval = null;
            $tval = array_key_exists($aname,$values) ? $values[$aname] : null;
            if($tval != null) {
                if ($attr->isType()) {
                    $rtype = $tm->getType($aname);
                    if ($rtype->isList()) {
                        $subObj = array();
                        foreach($tval as $value) {
                            $z = $rtype->makeObject($value);
                            if ($z != null) {
                                $subObj[] = $z;
                            }
                        }
                        $obj[$aname] = $subObj;
                    }
                    else {
                        $z = $rtype->makeObject($tval);
                        if ($z != null) {
                            $obj[$aname] = $z;
                        }
                    }
                }
                else { 
                    $obj[$aname] = $attr->convertValues($tval);
                }
            }
            elseif($attr->isType()) {
                $rtype = $tm->getType($aname);
                if (!$rtype->isList()) {
                    $tval = $rtype->makeObject($values);                
                    if ($tval != null) {
                        $obj[$aname] = $tval;
                    }
                }
            }
        }
        if(count($obj) == 1) {
            return null;
        } else {
            return $obj;
        }
    }
    
    /**
     * update a listing object - with the values from the map provided
     */
    public function updateObject($obj, $map) {
        
        $tm = new TypeManager();
        $hattrs = $this->getHierachicalAttributes();
        foreach($hattrs as  $attr) {
            $aname = $attr->getName();
            $tval = array_key_exists($aname,$map) ? $map[$aname] : null;
            if($tval != null) {
                if ($attr->isType()) {
                    $rtype = $tm->getType($aname);
                    if ($rtype->isList()) {
                        $subObj = array();
                        foreach($tval as $value) {
                            $z = $rtype->makeObject($value);
                            if ($z != null) {
                                $subObj[] = $z;
                            }
                        }
                        $obj[$aname] = $subObj;
                    }
                    else {
                        $z = $rtype->updateObject($tval, $map);
                        if ($z != null) {
                            $obj[$aname] = $z;
                        }
                    }
                }
                else {          
                    $obj[$aname] = $attr->convertValues($tval);
                }
            }
            elseif($attr->isType()) {
                $rtype = $tm->getType($aname);
                if (!$rtype->isList()) {
                    $tval = isset($obj[$aname]) ? $obj[$aname] : array();
                    $tval = $rtype->updateObject($tval, $map);       
                    if ($tval != null) {
                        $obj[$aname] = $tval;
                    }
                }
            }
        }
        return $obj;
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
    public function flatten($obj,$indexableOnly=false) {

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
                    $map[$name] = $attr->getIndexInfo($val);
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
