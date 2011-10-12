<?php

/*****************************************
 *       The Listings Collection 
 *****************************************/

include_once("ZipVilla/TypeConstants.php");
include_once("Mongo/ModelBase.php");

class Application_Model_Listings extends Mongo_ModelBase {

    public static $_collectionName = LISTINGS; 
    public static $_collection = null; 

}
?>
