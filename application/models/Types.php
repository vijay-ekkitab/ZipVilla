<?php

/*****************************************
 *       The Types Collection 
 *****************************************/

include_once("ZipVilla/TypeConstants.php");
include_once("Mongo/ModelBase.php");

class Application_Model_Types extends Mongo_ModelBase {

    public static $_collectionName = TYPES; 
    public static $_collection = null; 

}
?>

