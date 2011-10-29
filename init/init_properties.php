<?php
define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));
defined('APPLICATION_ENV') || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'testing'));

set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    realpath(APPLICATION_PATH . '/../application/models'),
    get_include_path(),
)));

include_once("Zend/Controller/Action/Helper/Abstract.php");
include_once("ZipVilla/Helper/ListingsManager.php");
include_once("ZipVilla/Utils.php");
include_once("Attributes.php");
include_once("Types.php");
include_once("Listings.php");
include_once("Enumerations.php");
include_once("Zend/Config/Ini.php");

$prop1 = array();
$prop1['owner_id'] = 100;
$prop1['street_number'] = 100;
$prop1['street_name'] = "St. Marks Road";
$prop1['location'] = "St. Marks Road";
$prop1['full_address'] = "100, St. Marks Road, Bangalore - 560001";
$prop1['city'] = "Bangalore";
$prop1['state'] = "Karnataka";
$prop1['zipcode'] = "560001";
$prop1['coordinates'] = array();
$prop1['bedrooms'] = 2;
$prop1['baths'] = 2;
$prop1['guests'] = 4;
$prop1['entertainment_options'] = array("Television" => "32 inch LCD screen.", 
										"Radio" => "Satellite Radio Receiver for 24 hour music");
$prop1['kitchen_amenities'] = array("Dinnerware");
$prop1['bedroom_amenities'] = array("Queen Bed");
$prop1['outdoor_activities'] = array("swimming");
$prop1['location_and_view'] = array();
$prop1['suitability'] = array("No Pets");
$prop1['nearby'] = array("2 km. from M.G. Road");
$prop1['daily_rate'] = 5000.00;
$prop1['title'] = "Grand View of Cantonment";
$prop1['title'] = "This is a clean and beautiful old villa from the British days.";
$prop1['images'] = array("picture1.jpg", "picture2.jpg"); 
$prop1['video'] = array(""); 


function insertHomes($prop) {
        $lm = new ZipVilla_Helper_ListingsManager();
        $tname = "home";
        $res = $lm->insert($tname,$prop);
}
insertHomes($prop1);
?>
