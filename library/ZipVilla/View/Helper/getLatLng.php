<?php 
$address = "1134,+100,+Feet,+Road,+12th,+main,+HAL,+2nd+stage,+Indiranagar,+Bangalore,+560038,+Karnataka,+India";
$myURL = "http://maps.google.com/maps/api/geocode/json?address=".$address."&sensor=false";
$jsonLL=file_get_contents($myURL);
$zvLatLng= json_decode($jsonLL);
$zvLat = $zvLatLng->results[0]->geometry->location->lat;
$zvLng = $zvLatLng->results[0]->geometry->location->lng;
?>