$(document).ready(function() {	

	//select all the a tag with name equal to modal
	$('a[name=lightbox_zv]').click(function(e) {
		//Cancel the link behavior
		e.preventDefault();
		
		//Get the A tag
		var id = $(this).attr('href');
	
		//Get the screen height and width
		var maskHeight = $(document).height();
		var maskWidth = $(window).width();
	
		//Set heigth and width to mask_zv to fill up the whole screen
		$('#mask_zv').css({'width':maskWidth,'height':maskHeight});
		
		//transition effect		
		$('#mask_zv').fadeIn(1000);	
		$('#mask_zv').fadeTo("slow",0.8);	
	
		//Get the window height and width
		//var winH = $(window).height();
		//var winW = $(window).width();
              
		var winH = $(document).height();
		var winW = $(document).width();


		//Set the popup window to center
		$(id).css('top',  winH/2-$(id).height()/2);
		$(id).css('left', winW/2-$(id).width()/2);
	
		//transition effect
		$(id).fadeIn(2000); 
	
	});
	
	//if close button is clicked
	$('.lb_window_zv .close').click(function (e) {
		//Cancel the link behavior
		e.preventDefault();
		
		$('#mask_zv').hide();
		$('.lb_window_zv').hide();
	});		
	
	//if mask_zv is clicked
	$('#mask_zv').click(function () {
		$(this).hide();
		$('.lb_window_zv').hide();
	});			
	
});