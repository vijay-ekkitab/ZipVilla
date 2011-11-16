function mapMarker( pMapInfo, pMapOptions, pMapCanvas)
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
  var myInfoWindow;
  var myMarker=0;
  var i;

  var latlng = new google.maps.LatLng(12.932239227407264, 77.63092875480652);
	  
  $(document).ready(function(){
    var myOptions = {
      zoom: 12,
      center: latlng,
      scale: 2,
      mapTypeId: google.maps.MapTypeId.ROADMAP
    }
alert("pMapOptions:" + pMapOptions);
     myMap = new google.maps.Map(document.getElementById(pMapCanvas), pMapOptions);
     myInfoWindow = new google.maps.InfoWindow();
 
    $(document).ready(function(){
// 	alert("myMap: "+myMap);
// 	alert("InfoW: "+myInfoWindow);

      for( i = 0; i < pMapInfo.length; i++) {
        $(document.getElementById(pMapCanvas)).append( '<div class="villas">'+'<h3>'+pMapInfo[i][1]+'</h3>'+
                        '</div><div class="clear"></div>'  );

// process only properties with lat/lng
        if( pMapInfo[i][1] > 0 ) {   
          myMarker = new google.maps.Marker({
            position: new google.maps.LatLng(pMapInfo[i][1], pMapInfo[i][2]),
            map: myMap,
            title : pMapInfo[i][0],
            html: pMapInfo[i][3] 
          });

          google.maps.event.addListener(myMarker, 'click', (function(myMarker, i) {
              return function() {
                  myInfoWindow.setContent(this.html);
                  myInfoWindow.open(myMap, this);
              }
          })(myMarker, i));
        }
      }
    });
  });
}

// End of Script - Copyright (c) ZipVilla, 2011 - 2011.
// Author SYLVESTER THOMAS
//

