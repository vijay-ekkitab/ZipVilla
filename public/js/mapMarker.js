
function mapMarker(pMapCanvas, pStartIndex, pZoom, pMapIndex)
{
  var aSize = 6;
	var myMap = new Array(aSize);
	var myInfowindow = new Array(aSize);
  var myLatCenter = new Array(aSize);
  var myLngCenter = new Array(aSize);
	var startIndex = pStartIndex;
	var myMapIndex = 0;
	var marker=0;
	var i = 0;
	var myUrl = "";
	
	//alert("in mm mC: "+pMapCanvas+" sI: "+pStartIndex+" pZ: "+pZoom+" mI: "+pMapIndex );
	/*
	if ((typeof zv_map_center_latitude === 'undefined') || 
			(typeof zv_map_center_longitude === 'undefined') ||
			(typeof zv_villa_locations === 'undefined') ||
		  (! zv_villa_locations instanceof Array)
		  ) {
			//$(document.getElementById(pMapCanvas)).append('<h4>Information unavailable to render Map</h4>');
	}
  */
	if (typeof pMapIndex === 'undefined') {
		myMapIndex = 0;
	} else {
		myMapIndex = pMapIndex;
		if( myMapIndex>5 ){
			myMapIndex=0;
		}
	}
	
	if ((typeof zv_map_center_latitude === 'undefined') || 
			(typeof zv_map_center_longitude === 'undefined')
		  ) {
			// $(document.getElementById(pMapCanvas)).append('<h4>Lat/Lng information unavailable</h4>');

		myLatCenter[myMapIndex] = 22.0000;
		myLngCenter[myMapIndex] = 77.0000;

		if( typeof pZoom === 'undefined' ) {
		  pZoom = 3;
		} else {
		  pZoom = 5;
		}
	}	else if ((zv_map_center_latitude > 0 ) && (zv_map_center_longitude > 0)) {
		myLatCenter[myMapIndex] = zv_map_center_latitude;
		myLngCenter[myMapIndex] = zv_map_center_longitude;		
	}
	
	if (typeof pZoom === 'undefined'){
		pZoom = 10;
	}
	
	var latlng = new google.maps.LatLng(myLatCenter[myMapIndex], myLngCenter[myMapIndex]);

	var zv_options = {
		zoom: pZoom,
		center: latlng,
		//scale: 2,
		mapTypeId: google.maps.MapTypeId.ROADMAP
	};

	myMap[myMapIndex] = new google.maps.Map(document.getElementById(pMapCanvas), zv_options);
	myInfowindow[myMapIndex] 	= new google.maps.InfoWindow({maxWidth:50});
	
	if (typeof zv_villa_locations !== 'undefined') {
		for( var i = 0; i < zv_villa_locations.length; i++, startIndex++) {
			/*
				$(document.getElementById(pMapCanvas)).append( 
					'<div class="villas">'+'<h4>{'+zv_villa_locations[i][0]
					+'}-{'+zv_villa_locations[i][1]+'}-{'+zv_villa_locations[i][2]+'}</h4></div>');
			 */
			if( zv_villa_locations[i][1] > 0 ) {   
//			if (typeof pStartIndex === 'undefined')
//				var markerImage = '/images/map_markers/red1_99/blank.png';
//			else
//				var markerImage = '/images/map_markers/red1_99/marker' + startIndex + '.png';
			
				var markerImage = '/images/map_markers/zv_1_99/marker' + startIndex + '.png';
			
				var image = new google.maps.MarkerImage(markerImage, new google.maps.Size(20, 34), new google.maps.Point(0, 0), new google.maps.Point(10, 34));
			
				marker = new google.maps.Marker({
								 position: new google.maps.LatLng(zv_villa_locations[i][1], zv_villa_locations[i][2]),
								 title   : zv_villa_locations[i][0],
								 html    : zv_villa_locations[i][3], 
								 map     : myMap[myMapIndex],
								 icon    : image
							 });

				google.maps.event.addListener(marker, 'click', function() {
					location.href = this.html;
					/*
					google.maps.event.addListener(marker, 'click', function() {
							myInfowindow[myMapIndex].close(myMap[myMapIndex]);
							myInfowindow[myMapIndex].setOptions({maxWidth:50});
							myInfowindow[myMapIndex].setContent(this.html);
							myInfowindow[myMapIndex].open(myMap[myMapIndex], this);
					 */
					});

					// google.maps.event.trigger(myMap[myMapIndex], 'resize');
			}
		}
	}
	google.maps.event.trigger(myMap[myMapIndex], 'resize');
	myMap[myMapIndex].setZoom( myMap[myMapIndex].getZoom() );
}


// End of Script - Copyright (c) ZipVilla, 2011 - 2011.
// Author SYLVESTER THOMAS
//

