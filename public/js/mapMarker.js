function mapMarker(pMapCanvas, pStartIndex)
 {
	var map;
	var infowindow;
	var marker=0;
	var i;
	var startIndex = pStartIndex;
	
	if ((typeof zv_map_center_latitude === 'undefined') || 
	    (typeof zv_map_center_longitude === 'undefined')) {
		return;
	}
	var latlng = new google.maps.LatLng(zv_map_center_latitude, zv_map_center_longitude);
	var zv_options = {		
					 	zoom: 10,
					 	center: latlng,
					 	scale: 2,
					 	mapTypeId: google.maps.MapTypeId.ROADMAP
					 };

	map 		= new google.maps.Map(document.getElementById(pMapCanvas), zv_options);
	infowindow 	= new google.maps.InfoWindow();
	
	if ((typeof zv_villa_locations === 'undefined') ||
	    (! zv_villa_locations instanceof Array)) {
		return;
	}

	for( i = 0; i<zv_villa_locations.length; i++, startIndex++) {
//		$(document.getElementById(pMapCanvas)).append( 
//				'<div class="villas">'+'<h4>'+zv_villa_locations[i][0]+'</h4>'+'</div>');

		if( zv_villa_locations[i][1] > 0 ) {   

			
			if (typeof pStartIndex === 'undefined')
				var markerImage = 'images/map_markers/red1_99/blank.png';
			else
				var markerImage = 'images/map_markers/red1_99/marker' + startIndex + '.png';
			
			var image = new google.maps.MarkerImage(markerImage, new google.maps.Size(20, 34), new google.maps.Point(0, 0), new google.maps.Point(10, 34));

			
					marker = new google.maps.Marker({
								position: new google.maps.LatLng(zv_villa_locations[i][1], 
																 zv_villa_locations[i][2]),
								map: map,
								icon: image,
								title : zv_villa_locations[i][0],
								html: zv_villa_locations[i][3] 
							 });

					google.maps.event.addListener(marker, 
												  'click', 
												  function() {
														infowindow.setContent(this.html);
														infowindow.open(map, this);
													});
		}
	}
	

}

// End of Script - Copyright (c) ZipVilla, 2011 - 2011.
// Author SYLVESTER THOMAS
//

