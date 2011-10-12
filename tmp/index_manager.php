<?php
include_once("listings_manager.php");

class IndexManager {
	static $_inst = null;
	public static function instance() {
		if(IndexManager::$_inst == null) {
			IndexManager::$_inst = new IndexManager();
		}
		return IndexManager::$_inst;
	}	
	private $solrUrl = "http://localhost:8983/solr/";
	
	private function getStatusCode($response) {
		$xml = new SimpleXMLElement($response);
		$status = $xml->xpath("/response/lst/int[@name='status']");
		if($status == null) {
			return "-1";
		}
		$x = print_r((string)($status[0]),true);
		return $x;
 	}
	private function sendIndexRequest($req) {
		$url = $this->solrUrl . "update/json?commit=true";
		#echo "url is : " . $url;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-type:application/json")); 
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_POST,true);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($ch,CURLOPT_POSTFIELDS,$req);
		$res = curl_exec($ch);
		$code = "-2";
		if($res != false) {
			//check the error code has come - where is the response string
			$code = $this->getStatusCode($res);
		} 
		curl_close($ch);
		return $code;
	}
	//index matching entries which are not indexed
	public function indexById($id) {
		$lm = ListingsManager::instance();
		$res = $lm->queryById($id,true,true);
		if($res != null) {
			$cmd = array("add" => array("doc" => $res));
			$strCmd = json_encode($cmd);
			#echo "=====>" . "\n" . $strCmd ."\n";
			#echo "=====>" . "Going to index now";
			return $this->sendIndexRequest($strCmd);
		}
	}
	//index matching entries which are not indexed
	public function index($obj) {
		$lm = ListingsManager::instance();
		$flatObj = $lm->flatten($obj,true);
		if($flatObj != null) {
			$cmd = array("add" => array("doc" => $flatObj));
			$strCmd = json_encode($cmd);
			#echo "=====>" . "\n" . $strCmd ."\n";
			#echo "=====>" . "Going to index now";
			return $this->sendIndexRequest($strCmd);
		}
	}
	private function sendSearchRequest($req) {
		$url = $this->solrUrl . "select?" . $req;
		#echo "url is : " . $url;
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
		$res = curl_exec($ch);
		#echo "Raw response : " . print_r($res,true) . "\n";
		curl_close($ch);
		if($res != false) {
			##TODO: last error handling, HTTP error handling are all needed
			$solrRes = json_decode($res,true);
			$status = getValue($solrRes,"responseHeader_status");
			if($status != 0) {
				return null;
			} else {
				#echo "status Ok - getting docs : " . "\n";
				$solrDocs = getValue($solrRes,"response_docs");
				#echo "results are : ";
				#print_r($solrDocs);
				return $solrDocs;
			}
		} 
	}
	private function buildSearchRequest($q,$fds) {
		$qstr = "";
		if($q != null) {
			$i = 0;
			foreach ($q as $fd => $val) {
				if($i > 0) { $qstr = $qstr . "+OR+"; }
				$qstr = $qstr . $fd . ":" . $val;
				$i++;
			}
		}
		$qstr = "q=". $qstr . "&wt=json&";
		if($fds != null) {
			$fdstr = "fl=";
			foreach($fds as $fd) {
				$fdstr = $fdstr . $fd . ",";
			}
			$qstr = $qstr . $fdstr; 
		}
		#echo "===> request going to be : " . $qstr;
		return $qstr;
	}

	//perform solr search. if q = { a1 : v1 , a2 : v2} - it fires a search a1:v1 OR a2:v2 etc
	//returns null if nothing is found - otherwise an array objects with key specified in $fds 
	//if fds is null only a list of [ { id : ivVal }, ... ] are returned
	public function search($query , $fds=null) {
		if($fds == null) {
			$fds = array("id");
		}
		$req = $this->buildSearchRequest($query,$fds);
		return $this->sendSearchRequest($req);
	}
}
?>
