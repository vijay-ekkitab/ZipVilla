function mapMarker(pMapCanvas)
 {
	var map;
	var infowindow;
	var marker=0;
	var i;
	
	if ((typeof zv_map_center_latitude === 'undefined') || 
	    (typeof zv_map_center_longitude === 'undefined')) {
		return;
	}
	var latlng = new google.maps.LatLng(zv_map_center_latitude, zv_map_center_longitude);
	var zv_options = {		
					 	zoom: 12,
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

	for( i = 0; i<zv_villa_locations.length; i++) {
		$(document.getElementById(pMapCanvas)).append( 
				'<div class="villas">'+'<h3>'+zv_villa_locations[i][0]+'</h3>'+'</div><div class="clear"></div>');

		if( zv_villa_locations[i][1] > 0 ) {   
					marker = new google.maps.Marker({
								position: new google.maps.LatLng(zv_villa_locations[i][1], 
																 zv_villa_locations[i][2]),
								map: map,
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

