<?php

include_once("ZipVilla/TypeConstants.php");

    /**
     * Gets the value of a key in a map in the context of embedded objects. 
     * @param object $obj the input object from which the value is to be retrieved.
     * @param string $key the key for which the value is required.
     & @return $string the value for the key. 
     */
    function getValue($obj, $key) {
	    if($key != null) {
            $names = explode(POINTS, $key);
		    $cur = $obj;
            for ($i = 0; $i<count($names); $i++) {
                $tok = $names[$i];
			    if(array_key_exists($tok,$cur)) {
				    $cur = $cur[$tok];
			    } else {
				    return null;	
			    }			
		    }
		    return $cur;
	    }
	    return null;
    }

    /**
     * Converts an input to a string whether it is an element or an array. 
     * @param object $vals 
     & @return $string printable. 
     */
    function toString($vals) {
	    if(!is_array($vals)) {
		    return print_r($vals,true);
	    } else {
		    $n = count($vals);
		    if( $n == 1) { return print_r($vals[0],true);}
		    $ret = print_r($vals[0],true);
		    for($i = 1 ; $i < $n ; $i++) {
			    $ret = $ret . "," . print_r($vals[$i],true);				
		    }
		    return $ret;
	    }
    }
?>
