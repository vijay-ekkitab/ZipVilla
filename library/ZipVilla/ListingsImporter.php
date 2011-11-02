<?php
include_once("ZipVilla/Helper/ListingsManager.php");
include_once("ZipVilla/Helper/IndexManager.php");
include_once("ZipVilla/Helper/SearchManager.php");
include_once("ZipVilla/Utils.php");


class ListingsImporter {

    public function importFile($csvFile,$separator=',',$txtDelimiter='"') {
        if (!file_exists($csvFile)) {
            echo "Could not read file '$csvFile'\n";
            return FALSE;
        }
        
        $handle = fopen($csvFile, "r");
        if ($handle == null) {
            echo "Could not read file '$csvFile'\n";
            return FALSE;
        }
        
        
        $lineno = 1;
        
        if(($buffer = fgetcsv($handle,0,$separator,$txtDelimiter)) !== false) {
            $tmp = $buffer;
        }
        else {
            echo "Could not read header from file '$csvFile'\n";
            return FALSE;
        }
        
        $header = array();
        foreach($tmp as $h) {
            if (preg_match('/(.*)\((.+)\)/', $h, $matches)) {
                $name = $matches[1];
                $sub_array = array();
                if (preg_match('/^@/', $name)) {
                    $name = preg_replace('/^@/', '', $name);
                    $sub_array[0] = TRUE; // is a list
                }
                else {
                    $sub_array[0] = FALSE; // is not a list
                }
                $subs = split(',',$matches[2]);
                for ($i=0; $i<count($subs);$i++) {
                    $sub_array[] = $subs[$i];
                }
                $header[$name] = $sub_array;
            }
            else {
                $header[$h] = '';
            }
        }
        
        $headernames = array_keys($header);
        $lm = new ZipVilla_Helper_ListingsManager();
        
        while(($buffer = fgetcsv($handle,0,$separator,$txtDelimiter)) !== FALSE) {
            $lineno++;
            $obj = array();
            $i = 0;
            if (count($buffer) != count($headernames)) {
                echo "Incorrect number of entries for listing in line number: $lineno. Ignoring listing.\n";
            }
            else 
            for ($i=0; $i<count($headernames); $i++) {
                $subattr = $header[$headernames[$i]];
                $content = $buffer[$i];
                $attrname = $headernames[$i];
                if (is_array($subattr)) {
                    $vals = split(',', $content);
                    $sub_array = array();
                    if (count($vals) == (count($subattr) -1)) {
                        for ($j=1; $j<count($subattr); $j++) { // note: ignore element 0 which specifies list or not.
                            $sub_array[$subattr[$j]] = $vals[$j-1];
                        }
                        if ($subattr[0] == TRUE) { // is a list;
                            $obj[$attrname] = array($sub_array);
                        }
                        else {
                            $obj[$attrname] = $sub_array;
                        }
                    }
                    else 
                        echo "Warning: Did not find input for attribute $attrname for listing in line $lineno.\n";
                }
                else {
                    $obj[$attrname] = $content;
                }
            }
            if ($i == count($headernames)) {
                try {
                    $res = $lm->insert($obj['type'],$obj);
                }
                catch(Exception $e) {
                    echo "Failed to insert listing in line number: $lineno\n";
                }
            }
        }
        fclose($handle);
        return TRUE;
    }
}
?>
