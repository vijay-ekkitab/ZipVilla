<?php
include_once("ZipVilla/Helper/ListingsManager.php");
include_once("ZipVilla/Helper/IndexManager.php");
include_once("ZipVilla/Helper/SearchManager.php");
include_once("ZipVilla/Utils.php");


class ListingsImporter {
	public function __construct() {
	}
	public function importFile($csvFile,$seperator=',',$txtDelimiter='"') {
		if (file_exists($csvFile)) {
            		$handle = fopen($csvFile, "r"); 
			$buffer = null;
			$lm = new ZipVilla_Helper_ListingsManager();
			$header = null;
			if(($buffer = fgetcsv($handle,0,$seperator,$txtDelimiter)) !== false) {
				$header = $buffer;
			}
			$fcount = count($header);
			while(($buffer = fgetcsv($handle,0,$seperator,$txtDelimiter)) !== false) {
				$obj = array();
				for ($i=0; $i < $fcount; $i++) {
            				$obj[$header[$i]] = $buffer[$i];
        			}
				$res = $lm->insert($obj['type'],$obj,true);
				//TODO check on res that it is really inserted
				//print_r($res);
			}
			fclose($handle);
			return true;
		} else {
			//file not found write a error log and return false
			return false;
		}
	}
}
?>
