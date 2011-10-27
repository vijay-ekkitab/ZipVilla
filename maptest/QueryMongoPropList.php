<?php
  try {
    $conn = new Mongo('localhost');
    $db = $conn->ZipVilla;
    $collection = $db->properties;

    $cityToSearch = $_GET['city'];

    $criteria = array(
      'city_name' => $cityToSearch
    );

    $cursor = $collection->find( $criteria );
    // $cursor = $collection->find();


    $iCtr = 0;
    foreach ($cursor as $obj) {
      $rows[] = array(
        "name" => $obj['property_name'],
        "type" => $obj['property_type'],
        "lat"  => $obj['lat'],
        "lng"  => $obj['lng'],
        "city" => $obj['city_name']);
    }

    $json= json_encode($rows);
	 
    $callback = $_GET['callback'];
    echo $callback.'('. $json. ')';   

    if( $iCtr == 0 )
    {
      // echo $cursor->count() . ' document(s) found. <br/>' ;
    }
 
    $conn->close();
  } catch (MongoConnectionException $e) {
    die('Error connecting to MongoDB server');
  } catch (MongoException $e) {
    die('Error: ' . $e->getMessage());
}


// $fn="sylvester";
// $ln="thomas";

//    "name":"' . $obj['property_name'] . '",
//    "type":"' . $obj['property_type']. '",
//    "lat":"' . $obj['lat'] . '",
//    "lng":"' . $obj['lng']. '",
//    "city":"' . $obj['city_name'] . '"

//    echo $_POST[$obj['property_name']];
//    echo $_POST[$obj['property_type']];
//    echo $_POST[$obj['lat']];
//    echo $_POST[$obj['lng']];

//    echo 'name:' . $obj['property_name'];
//    echo ' type:' . $obj['property_type'];
//    echo ' lat:' . $obj['lat'];
//    echo ' lng:' . $obj['lng'];
//    echo ' city:' . $obj['city_name'];


	  // "fname":"' . $fn .'",
          // "lname":$ln
	  // "name":$obj['property_name'],
	  // "type":$obj['property_type'],
	  // "lat":$obj['lat'],
	  // "lng":$obj['lng'],
	  // "city":$obj['city_name']
	//    echo 'City  : ' . $obj['city_name'] ;
	//    echo '  Property: ' . $obj['property_name'];
	//    echo '  Type    : ' . $obj['property_type'];
	//    echo '  Lat     : ' . $obj['lat'];
	//    echo '  Lng    D: ' . $obj['lng'] . '<br/>';


?>
