			$(function(){

				// Accordion
				$("#accordion").accordion({ header: "h4" });
	
				// Tabs
				$('#tabs').tabs();
	

				// Dialog			
				$('#dialog').dialog({
					autoOpen: false,
					width: 600,
					buttons: {
						"Ok": function() { 
							$(this).dialog("close"); 
						}, 
						"Cancel": function() { 
							$(this).dialog("close"); 
						} 
					}
				});
				
				// Dialog Link
				$('#dialog_link').click(function(){
					$('#dialog').dialog('open');
					return false;
				});

				// Datepicker
				$('#datepicker').datepicker({
					inline: true
				});
				
				// Slider
				$('#price_slider').slider({
					range: true,
					min: 100,
					max:20000,
					values: [500, 10000],
					step:100,
					slide: function( event, ui ) {
						$( "#price_range" ).val( "Rs." + ui.values[ 0 ] + " - Rs." + ui.values[ 1 ] );
					}
				});
				$( "#price_range" ).val( "Rs." + $( "#price_slider" ).slider( "values", 0 ) +
						" - Rs." + $( "#price_slider" ).slider( "values", 1 ) );
				
				// Progressbar
				$("#progressbar").progressbar({
					value: 20 
				});
				
				//hover states on the static widgets
				$('#dialog_link, ul#icons li').hover(
					function() { $(this).addClass('ui-state-hover'); }, 
					function() { $(this).removeClass('ui-state-hover'); }
				);
				
			});
