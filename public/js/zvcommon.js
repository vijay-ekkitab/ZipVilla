$(document).ready(function() 
{	
	FB.init({ apiKey: '234678403269599' });
    FB.getLoginStatus(fbIsLoggedIn);
    
	//select all the a tag with name equal to modal
	$('a[name=lightbox_zv]').click(function(e) 
	{
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
		$(window).scrollTop($("#lb_search_zv").offset().top);
		
		$('#tabs1').tabs();
		
	});
	
	//if close button is clicked
	$('.lb_window_zv .close').click(function (e) 
	{
		//Cancel the link behavior
		e.preventDefault();
		
		$('#mask_zv').hide();
		$('.lb_window_zv').hide();
	});		
	
	//if mask_zv is clicked
	$('#mask_zv').click(function () 
	{
		$(this).hide();
		$('.lb_window_zv').hide();
	});
	
	$("#logout").click(function(e) {
		FB.getLoginStatus(fbLogout);
	});
	
	$( ".checkin" ).datepicker({dateFormat:dateformat, minDate:0});
	$( ".checkout" ).datepicker({dateFormat:dateformat, minDate:0,
                                 beforeShowDay: enableCheckOutFor});
	
	$('.checkin').change(function() {
		var val = $(this).val();
		$('.checkin').val(val);
        var checkIn = getCheckInDate(this);
        if (checkIn != null) {
            setCheckOutDateAuto(checkIn);
        }
        calculate_rate();
     });
	
	$('.checkout').change(function() {
		calculate_rate();
	});
	
	$(".defaultText").focus(function(srcc) {
        if ($(this).val() == $(this)[0].title) {
            $(this).removeClass("defaultTextActive");
            $(this).val("");
        }
     });
        
    $(".defaultText").blur(function() {
        if ($(this).val() == "") {
            $(this).addClass("defaultTextActive");
            $(this).val($(this)[0].title);
        }
        else {
        	$(this).removeClass("defaultTextActive");
        }
     });

    $(".defaultText").change(function() {
        if ($(this).val() != $(this)[0].title) {
        	$(this).removeClass("defaultTextActive");
        }
    });
        
    $(".defaultText").blur(); 
});


function showlightbox(id, mask, msg) 
{
	//Get the screen height and width
	var maskHeight = $(document).height();
	var maskWidth = $(window).width();
	
	msg = msg || '';

	//Set heigth and width to mask_zv to fill up the whole screen
	$(mask).css({'width':maskWidth,'height':maskHeight});
	
	//transition effect		
	$(mask).fadeIn(1000);	
	$(mask).fadeTo("slow",0.8);	

	var winH = $(document).height();
	var winW = $(document).width();

	//Set the popup window to center
	$(id).css('top',  winH/2-$(id).height()/2);
	$(id).css('left', winW/2-$(id).width()/2);
	
	$('#lb_show_msg').children('p').remove();
	if (msg != '') {
		$('#lb_show_msg').prepend('<p><strong>'+msg+'</strong></p>');
	}

	//transition effect
	$(id).fadeIn(2000); 
	$(window).scrollTop($(id).offset().top);
}

function closelightbox(id, mask)
{
	$(mask).hide();
	$(id).hide();
};

var lastdecline = null;
var userloggedin = false;
var fb_uid = null;
var fb_accessToken = null;
var fb_login_in_progress = false;
var dateformat = 'dd-M-yy';
var minPrice = 100;
var maxPrice = 20000;


function calculate_rate(_id) {
	if (typeof _id == 'undefined') {
		if (typeof property_id == 'undefined')
			return;
		_id = property_id;
	}
	var _checkin=$(".checkin").val();
	if (_checkin == $(".checkin").attr('title')) {
		_checkin = '';
	}
	var _checkout=$(".checkout").val();
	if (_checkout == $(".checkout").attr('title')) {
		_checkout = '';
	}
	var _guests=$("#guests").val();
	$("#calc_rate").text("Calculating...");
    $.ajax({ 
        type: "POST",
        dataType: "json",
        url:"/default/list/calculate/format/json",
        data:{id:_id,checkin:_checkin,checkout:_checkout,guests:_guests},
        complete:function(json) { $("#calc_rate").text("Rs. "+jQuery.parseJSON(json.responseText.substring(2)).total);}});
}

function checkLogin() {
	if (userloggedin) 
		return;
	$.ajax({ 
        type: "POST",
        dataType: "json",
        async: false,
        url:"/default/list/checklogin/format/json",
        data:{},
        complete:function(json) {
                var loggedin = json.responseText.substring(2);
                if (loggedin != '') {
                    userloggedin = loggedin;
                }
            }
        }
    );
}

function setRate(score, max_score, baseurl) {
	checkLogin();
	if (!userloggedin) {
		lastdecline = 'setRate('+score+','+max_score+',"'+baseurl+'")';
		requestLogin();
		return;
	}
	var image;
	for(var i=1; i<=max_score;i++) {
		if (i<=score) {
			image=baseurl+"/images/icon_ratings_gold.jpg";
		}
		else {
			image=baseurl+"/images/icon_ratings_silver.jpg";
		}
		$("#rate"+i).attr("src", image);
	}
	$('input[name="rating"]').each(function() {
		$(this).val(score);
	});
} 

function fbIsLoggedIn(response) {
	if (response.status === 'connected') {
		fb_uid = response.authResponse.userID;
	    fb_accessToken = response.authResponse.accessToken;
		FB.api('/me', fbCompleteLogin);
    }
}

function fbLogin(response) {
	if (response.authResponse) {
		FB.api('/me', fbCompleteLogin);
    }
	else if (fb_login_in_progress) {
		$(fb_login_in_progress).children('p').remove();
        $(fb_login_in_progress).append('<p>Facebook login failed. User cancelled login or did not fully authorize.</p>');
	}
}
function fbLogout(response) {
	if (response.status === 'connected') {
		FB.logout(function(response) {});
	}
}
function fbCompleteLogin(response) {
    if (!userloggedin) {
    	$.ajax({ 
            type: "POST",
            dataType: "json",
            async: false,
            url:"/default/login/fblogin/format/json",
            data:{firstname:response.first_name,
                  lastname:response.last_name,
                  email:response.email},
            complete:function(json){}
        });
    }
    userloggedin = response.email+','+response.first_name+','+response.last_name;
    updateIdentityOnPage();
    if (fb_login_in_progress) {
    	if (typeof is_login_page === 'undefined') {
    		closelightbox('#lb_login', '#mask_zv');
    		if (lastdecline != null) {
    			eval(lastdecline);
    			lastdecline = null;
    		}
    	}
    	else {
    		$("#register").submit();
    	}
        fb_login_in_progress = false;
    }
};

function updateIdentityOnPage()
{
	var userparams = userloggedin.split(',');
	var message = 'Welcome '+userparams[1]+' '+userparams[2]+'. <a id="logout" href="/login/logout">Logout</a>';
	$('#logged_in_as').empty();
	$('#logged_in_as').html(message);
	$('#logout').click(function(e) {
		FB.getLoginStatus(fbLogout);
	});
}

function login(iduser,idpsw, idmsg) {
	if ((iduser == '') && (idpsw == '')) { //FB Login
		fb_login_in_progress = idmsg;
		FB.login(fbLogin, {scope:'email'});
		$(idmsg).children('p').remove();
        $(idmsg).append('<p>Facebook login in progress...</p>');
		return;
	}
	var user = $(iduser).val();
	var psw  = $(idpsw).val();
	$.ajax({ 
        type: "POST",
        dataType: "json",
        async: false,
        url:"/default/login/ajaxlogin/format/json",
        data:{username:user, password:psw},
        complete:function(json) {
                var loggedin = json.responseText.substring(2);
                if (loggedin != '') {
                    userloggedin = loggedin;
                    updateIdentityOnPage();
                    closelightbox('#lb_login', '#mask_zv');
                    if (lastdecline != null) {
                        eval(lastdecline);
                        lastdecline = null;
                    }
                }
                else {
                    $(idmsg).empty().append('<p>username or password is incorrect.</p>');
                }
            }
        }
    );
}

function requestLogin() {
	showlightbox('#lb_login', '#mask_zv');
}

function writeReview(id) {
	checkLogin();
    if (!userloggedin) {
        lastdecline = 'writeReview(\''+id+'\')';
        requestLogin();
        return;
    }
    
    $(id).show();
    $(window).scrollTop($(id).offset().top);
}

function submitForm(id, names, lbdiv) {
    checkLogin();
    if (!userloggedin) {
        var str='[';
        for (i in names) {
            if (i>0) str=str+","; 
            str = str+"'"+names[i]+"'";
        }
        str=str+']';
        lastdecline = "submitForm('"+id+"',"+str+")";
        requestLogin();
        return false;
    }
    if (validate(id, names)) {
    	var inputs = $(id+' :input');
        var values = {};
        inputs.each(function() {
            values[this.name] = $(this).val();
        });
        clearForm(id, names);
        if (lbdiv != null) {
            closelightbox(lbdiv, '#mask_zv');
        }
        $.ajax({ 
            type: "POST",
            dataType: "json",
            async: false,
            url:"/default/list/form/format/json",
            data:values,
            complete:function(json) {
                    var response = json.responseText.substring(2);
                    showlightbox('#lb_thankyou', '#mask_zv', response);
                }
            }
        );
        return true;
    }
    else {
    	$(".defaultText").blur();
        return false;
    }
}

function clearForm(id, names) {
	$(names).each(function(i) {
        var input = $(id+" :input[name=\""+names[i]+"\"]");
        if (input != null) {
			input.val('');
		}
    });
	$(".defaultText").blur();
}

function showSendForm(fromid, toid) {
	checkLogin();
    if (!userloggedin) {
        var str='[';
        lastdecline = "showSendForm('"+fromid+"','"+toid+"')";
        requestLogin();
        return false;
    }
	var inputs = $(fromid+' :input');
    var values = {};
    inputs.each(function() {
        values[this.name] = $(this).val();
    });
    inputs = $(toid+' :input');
    inputs.each(function() {
        if (values.hasOwnProperty($(this).attr('name'))) {
            $(this).val(values[$(this).attr('name')]);
        }
    });
    userparams = userloggedin.split(',');
    $(toid+" :input[name=\"email\"]").val(userparams[0]);
    $(toid+" :input[name=\"firstname\"]").val(userparams[1]);
    $(toid+" :input[name=\"lastname\"]").val(userparams[2]);
    showlightbox('#lb_send_details', '#mask_zv');
}

function validate(id, names) 
{
    var isvalid = true;
    $(names).each(function(i) {
        var input = $(id+" :input[name=\""+names[i]+"\"]");
        if (input != null) {
            var classname = input.attr('class');
            if (classname.match(/defaultText/)) {
                if (input.val() == input[0].title) {  
                    input.val('');
                }
            }
            if (input.val() == '') { 
               isvalid=false;
               
            }
        }
        else {
            isvalid= false;
        }
    });
    return isvalid;
}

function getReviews(_id, _start, _baseurl) {
    $.ajax({ 
        type: "POST",
        dataType: "json",
        url:"/default/list/getreviews/format/json",
        data:{id:_id, start:_start, baseurl:_baseurl},
        complete:function(json) { 
        	var resp = jQuery.parseJSON(json.responseText.substring(2)); 
        	$("#user_reviews").html(resp.user_reviews);
        	$("#user_reviews_notice").html(resp.user_reviews_notice);
        	$("#user_reviews_pages").html(resp.user_reviews_pages);
        }
    });
}

function validate_default() 
{
  	$(".defaultText").each(function(i) {if ($(this).val() == $(this)[0].title) $(this).val('');});
}

function watchQueryBox() {
    if ($('#query').data('lastValue') != $('#query').val()) {
        $.ajax({ 
            type: "POST",
            url:"/default/search/lookahead/format/html",
            data:"query="+$('#query').val(),
            complete:function(data) { console.log(data.responseText);}});
        $('#query').data('lastValue', $('#query').val());
    }
}

function getCheckInDate(id) 
{
	var dstr = $(id).val();
	var date = null;
	try {
		date = $.datepicker.parseDate(dateformat, dstr);
	}
	catch(err) {
		date = null;
	}
	return date;
}
function enableCheckOutFor(date) {
    var checkIn = getCheckInDate('.checkin');
    var res = [];
    var select = false;
    if ((checkIn == null) || (date >= checkIn)) {
        select = true;
    }
    res[0] = select;
    res[1] = '';
    return res;
}

function setCheckOutDateAuto(date)
{ 
	var nextday = date.getDate()+1;
	date.setDate(nextday);
	var checkOut = getCheckInDate('.checkout');
	if ((checkOut == null) || (date > checkOut)) {
		$('.checkout').val($.datepicker.formatDate(dateformat, date));
		$('.checkout').blur();
	}
}

function refineSearch(facet, value, selected)
{
	$('#loadergif').empty().html('<img src="/css/loader.gif"/>');
	$.ajax({ 
        type: "POST",
        dataType: "html",
        url:"/default/search/refine/format/html",
        data:{facet:facet,value:value,selected:selected},
        complete:function(html){
        	$('#loadergif').empty();
            var response = html.responseText.split("<zipvilla>");
        	$('#tabs-1').children('div').remove();
        	$('#tabs-1').prepend(response[0]);
        	$('#tabs-2').children('div').remove();
            $('#tabs-2').prepend(response[1]);
        	$('#total_hits').text(totalhits+" Results");
        	$('#refine_search').empty();
        	$('#refine_search').prepend(response[2]);
          }
      });
}

function setPage(page)
{
    $("#page").val(page);
    setTab();
}
function setTab()
{
    var selected = $tabs.tabs('option', 'selected');
    $("#showtab").val(selected);
}

function resetCriteria()
{
	$('input[name="amenities[]"]').removeAttr('checked');
	$('input[name="shared[]"]').removeAttr('checked');
	$('input[name="onsite_services[]"]').removeAttr('checked');
	$('input[name="suitability[]"]').removeAttr('checked');
	$('input[name="address__location[]"]').removeAttr('checked');
	$('input[name="keywords"]').val(''); 
	$('input[name="price_range"]').val('Rs.' + minPrice + ' - Rs.' + maxPrice);
}

function mycarousel_initCallback(carousel) {
	/*
  jQuery('.jcarousel-control a').bind('click', function () {
  	alert("jC:C");
    carousel.scroll(jQuery.jcarousel.intval(jQuery(this).text()));
    return false;
  });

  //jQuery('#mycarousel-next').bind('click', function () {
  jQuery('#mostViewedCarousel-next').bind('click', function () {
  	alert("jC:N");
  	carousel.next();
    return false;
	});

  jQuery('#mostViewedCarousel-prev').bind('click', function () {
  	alert("jC:P");
  	carousel.prev();
    return false;
	});	
*/
	
  // Pause autoscrolling if the user moves with the cursor over the clip.
  carousel.clip.hover(function() {
    carousel.stopAuto();
  }, function() {
    carousel.startAuto();
  });
};

//Google Analytics code.

var _gaq = _gaq || [];
_gaq.push(['_setAccount', 'UA-33203611-1']);
_gaq.push(['_trackPageview']);

(function() {
  var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
  ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
  var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
})();

// End Of File