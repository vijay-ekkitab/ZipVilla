<?php

/*****************************************
 *       The Attributes Collection 
 *****************************************/

include_once("ZipVilla/TypeConstants.php");
include_once("Mongo/ModelBase.php");

class Application_Model_Attributes extends Mongo_ModelBase {

    public static $_collectionName = ATTRIBUTES; 
    public static $_collection = null; 

}
?>

