 function mapMarker( pMapInfo, pZoom, pCenterLat, pCenterLng, pMapCanvas)
 {
//  alert("pMapinfo: "  + pMapInfo );
  //for( arrKey in pMapInfo )
  //{
  //  alert("info : " + pMapInfo[arrKey]);
  //}
  
 //     for( i = 0; i < pMapInfo.length; ++i) {
 //	document.writeln(">>>> " + pMapInfo[i][0] + "<BR>");
 //	document.writeln(">>>> " + pMapInfo[i][1] + "<BR>");
 //	document.writeln(">>>> " + pMapInfo[i][2] + "<BR>");
 //	document.writeln(">>>> " + pMapInfo[i][3] + "<BR>");
 //	document.writeln(">>>> " + document.getElementById(pMapCanvas) + "<BR>");
  //   if( pMapInfo[i][1] > 0 ) {
  //       marker = new google.maps.Marker({
  //          position: new google.maps.LatLng(pMapInfo[i][1], pMapInfo[i][2]),
  //          map: map,
  //          title : pMapInfo[i][0],
  //         html: pMapInfo[i][3] 
  //        });
  //   }
     }

  var map;
  var infowindow;
  var marker=0;
  var i;
  
  var myOptions = {
    zoom: pZoom,
    mapTypeId: google.maps.MapTypeId.ROADMAP
  };

  map = new google.maps.Map(document.getElementById(pMapCanvas), myOptions);
  infowindow = new google.maps.InfoWindow();

  var row; 
  var col;
  for( row = 0; row < pMapInfo.length; ++row)
  {
    for( col = 0; col < pMapInfo[row].length; ++col)
    {
	document.writeln(">>>> " + pMapInfo[row][col] + "<BR>");
    }
  }

  for( i = 0; i < pMapInfo.length; ++i) {
	document.writeln(">>>> " + pMapInfo[i][0] + "<BR>");
     if( pMapInfo[i][1] > 0 ) {
         marker = new google.maps.Marker({
            position: new google.maps.LatLng(pMapInfo[i][1], pMapInfo[i][2]),
            map: map,
            title : pMapInfo[i][0],
            html: pMapInfo[i][3] 
          });
     }
     google.maps.event.addListener(marker, 'click', (function(marker, i) {
           return function() {
                  infowindow.setContent(this.html);
                  infowindow.open(map, this);
           };
     })(marker, i));
  }
}

// End of Script - Copyright (c) ZipVilla, 2011 - 2011.
// Author SYLVESTER THOMAS
//
