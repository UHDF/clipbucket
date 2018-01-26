// CB Cinema Plugin JS
// Author: Fawaz, Adrien Ponchelet
// Date Created: 24th Feb, 2010
// Modified date: 15th Jan, 2018

// Function to insert the div
// @Div : Name of the Div used as an overlay
// @element : Name of the element to which Div will be appended
// @type : Type of Div ID or Class
function insert_overlay(Div,element,type) {
	$('<div ' + type+'="'+Div+'" />').appendTo(element);
}


// Function used to turn lights off and on
// @Div : Name of the Div used as an overlay
// @element : element is jQuery Object
// @type : Type of Div ID or Class
function cb_cinema(Div,element,type) {
	if(type == "id" || type == "ID") {
		var Divname = "#" + Div;
	}
	else {
		var Divname = "." + Div;
	}

	$(Divname).hide();
	
	$(window).bind("load",function() {
		$(Divname).css("height", $(document).height());

		var classList = document.querySelector("#lightButton").classList;

		$(element).click(function() {
			if (classList.contains('lightsoff')){
				classList.remove('lightsoff');
				classList.add('lightson');
				$(Divname).fadeIn('normal');
			}
			else{
				classList.add('lightsoff');
				classList.remove('lightson');
				$(Divname).fadeOut('normal');
			}
		});

		$(Divname).click(function() {
			$(Divname).fadeOut('normal');
			$("#lightButton").removeClass('lightson');
			$("#lightButton").addClass('lightsoff');
		});
	});
}
