function mapMarker( pMapInfo, pZoom, pCenterLat, pCenterLng, pMapCanvas)
 {

 //     for( i = 0; i < pMapInfo.length; ++i) {
 // 	  document.writeln(">>>> " + pMapInfo[i][0] + "<BR>");
 // 	  document.writeln(">>>> " + pMapInfo[i][1] + "<BR>");
 // 	  document.writeln(">>>> " + pMapInfo[i][2] + "<BR>");
 // 	  document.writeln(">>>> " + pMapInfo[i][3] + "<BR>");
 // 	  document.writeln(">>>> div " + pMapCanvas + "<BR>");
 //  	  document.writeln(">>>> iddiv " + document.getElementById(pMapCanvas) + "<BR>");
 //      }

  var myMap;
  var infowindow;
  var marker=0;
  var i;

  var latlng = new google.maps.LatLng(12.932239227407264, 77.63092875480652);
	  
  $(document).ready(function(){
    var myOptions = {
      zoom: 12,
      center: latlng,
      scale: 2,
      //size:200x200,
      mapTypeId: google.maps.MapTypeId.ROADMAP
    }

     myMap = new google.maps.Map(document.getElementById(pMapCanvas), myOptions);
     infowindow = new google.maps.InfoWindow();
 
    $(document).ready(function(){
// 	alert("myMap: "+myMap);
// 	alert("InfoW: "+infowindow);

      for( i = 0; i < pMapInfo.length; i++) {
        $(document.getElementById(pMapCanvas)).append( '<div class="villas">'+'<h3>'+pMapInfo[i][1]+'</h3>'+
                        '</div><div class="clear"></div>'  );

//        alert("myMapInfo: " + pMapInfo[i][0]);
		// process only properties with lat/lng

        if( pMapInfo[i][1] > 0 ) {   
//          alert("LatLng YES!"+pMapInfo[i][1]);
          marker = new google.maps.Marker({
            position: new google.maps.LatLng(pMapInfo[i][1], pMapInfo[i][2]),
            map: myMap,
            title : pMapInfo[i][0],
            html: pMapInfo[i][3] 
          });

          google.maps.event.addListener(marker, 'click', (function(marker, i) {
              return function() {
                  infowindow.setContent(this.html);
                  infowindow.open(myMap, this);
              }
          })(marker, i));
        }
      }
    });
  });
}

// End of Script - Copyright (c) ZipVilla, 2011 - 2011.
// Author SYLVESTER THOMAS
//

