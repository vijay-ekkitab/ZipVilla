<?php

/*****************************************
 *       The Enumerations Collection 
 *****************************************/

include_once("ZipVilla/TypeConstants.php");
include_once("Mongo/ModelBase.php");

class Application_Model_Enumerations extends Mongo_ModelBase {

    public static $_collectionName = ENUMERATIONS; 
    public static $_collection = null; 

}
?>

