/*
  JQuery code to add or remove option fields
*/


jQuery(function() { // when document has loaded

	var i = jQuery('input.wprss-input').size(); // check how many input exists on the document and add 1 for the add command to work
	i = (i / 2) + 1;

	jQuery('a#add').click(function() { // when you click the add link				
		jQuery( "<div class='wprss-input'><p><label class='textinput' for='feed_name_" + i + "'>Feed name " + i + "</label>" +
		        "<input id='feed_name_" + i + "' class='wprss-input' size='100' name='wprss_options[feed_name_" + i + "]' type='text' value='' /></p>" +
			"<p><label class='textinput' for='feed_url_" + i + "'>Feed URL " + i + "</label>" +
			"<input id='feed_url_" + i + "' class='wprss-input' size='100' name='wprss_options[feed_url_" + i + "]' type='text' value='' /></p></div>")
			.fadeIn('slow').insertBefore('div#buttons');
		// append (add) a new input to the document.
		// if you have the input inside a form, change body to form in the appendTo
		i++; //after the click i will be i = 3 if you click again i will be i = 4
	});

	jQuery('a#remove').click(function() { // similar to the previous, when you click remove link
	if(i > 1) { // if you have at least 1 input on the form
		jQuery('div.wprss-input:last').remove(); //remove the last input
		i--; //deduct 1 from i so if i = 3, after i--, i will be i = 2
	}
	});

	jQuery('a.reset').click(function() {
	while(i > 2) { // while you have more than 1 input on the page
		//jQuery('input.wprss-input:last').remove(); // remove inputs
		jQuery('div.wprss-input:last').remove(); // remove inputs
		i--;
	}
	});

});

