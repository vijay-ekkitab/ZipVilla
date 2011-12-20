
function mapMarker(pMapCanvas, pStartIndex, pZoom, pMapIndex)
{
	var myMap = new Array(6);
	var myInfowindow = new Array(6);
	var marker=0;
	var i;
	var startIndex = pStartIndex;
	var myMapIndex = 0;

	if ((typeof zv_map_center_latitude === 'undefined') || 
			(typeof zv_map_center_longitude === 'undefined') ||
			(typeof zv_villa_locations === 'undefined') ||
		  (! zv_villa_locations instanceof Array)
		  ) {
				$(document.getElementById(pMapCanvas)).append('<h4>Information unavailable to render Map</h4>');
			return;
	}

	if (typeof pMapIndex === 'undefined') {
			myMapIndex = 0;
	} else {
		myMapIndex = pMapIndex;
		if( myMapIndex>5 ){
			myMapIndex=0;
		}
	}
	
	if (typeof pZoom === 'undefined'){
		pZoom = 10;
	}


	var latlng = new google.maps.LatLng(zv_map_center_latitude, zv_map_center_longitude);

	var zv_options = {
					 	zoom: pZoom,
					 	center: latlng,
					 	//scale: 2,
					 	mapTypeId: google.maps.MapTypeId.ROADMAP
					 };

	myMap[myMapIndex] 		= new google.maps.Map(document.getElementById(pMapCanvas), zv_options);
	myInfowindow[myMapIndex] 	= new google.maps.InfoWindow();
	

	for( i = 0; i < zv_villa_locations.length; i++, startIndex++) {
		$(document.getElementById(pMapCanvas)).append( 
				'<div class="villas">'+'<h4>{'+zv_villa_locations[i][0]
				+'}-{'+zv_villa_locations[i][1]+'}-{'+zv_villa_locations[i][2]+'}</h4></div>');
	
		if( zv_villa_locations[i][1] > 0 ) {   
//alert("inside  map render");

//			if (typeof pStartIndex === 'undefined')
//				var markerImage = '/images/map_markers/red1_99/blank.png';
//			else
//				var markerImage = '/images/map_markers/red1_99/marker' + startIndex + '.png';
			
			var markerImage = '/images/map_markers/red1_99/marker' + startIndex + '.png';
			
			var image = new google.maps.MarkerImage(markerImage, new google.maps.Size(20, 34), new google.maps.Point(0, 0), new google.maps.Point(10, 34));
			
					marker = new google.maps.Marker({
								position: new google.maps.LatLng(zv_villa_locations[i][1], 
																 zv_villa_locations[i][2]),
								map: myMap[myMapIndex],
								icon: image,
								title : zv_villa_locations[i][0],
								html: zv_villa_locations[i][3] 
							 });

					google.maps.event.addListener(marker, 
												  'click', 
												  function() {
														myInfowindow[myMapIndex].setContent(this.html);
														myInfowindow[myMapIndex].open(myMap[myMapIndex], this);
														myInfowindow[myMapIndex].setMaxWidth(100);
													});
					google.maps.event.trigger(myMap[myMapIndex], 'resize');
		}
	}
	google.maps.event.trigger(myMap[myMapIndex], 'resize');
	myMap[myMapIndex].setZoom( myMap[myMapIndex].getZoom() );

}


// End of Script - Copyright (c) ZipVilla, 2011 - 2011.
// Author SYLVESTER THOMAS
//

