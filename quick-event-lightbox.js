function lightbox(insertContent, ajaxContentUrl){

	// add lightbox/shadow <div/>'s if not previously added
	if(jQuery('#lightbox').size() == 0){
		var theLightbox = jQuery('<div id="lightbox"/>');
		var theShadow = jQuery('<div id="lightbox-shadow"/>');
		jQuery(theShadow).click(function(e){
			closeLightbox();
		});
		jQuery('body').append(theShadow);
		jQuery('body').append(theLightbox);
	}

	// remove any previously added content
	jQuery('#lightbox').empty();

	// insert HTML content
	if(insertContent != null){
		jQuery('#lightbox').append(insertContent);
	}

	// insert AJAX content
	if(ajaxContentUrl != null){
		// temporarily add a "Loading..." message in the lightbox
		jQuery('#lightbox').append('<p class="loading">Loading...</p>');

		// request AJAX content
		jQuery.ajax({
			type: 'GET',
			url: ajaxContentUrl,
			success:function(data){
				// remove "Loading..." message and append AJAX content
				jQuery('#lightbox').empty();
				jQuery('#lightbox').append(data);
			},
			error:function(){
				alert('AJAX Failure!');
			}
		});
	}

	// move the lightbox to the current window top + 100px
	jQuery('#lightbox').css('top', jQuery(window).scrollTop() + 100 + 'px');

	// display the lightbox
	jQuery('#lightbox').show();
	jQuery('#lightbox-shadow').show();

}

// close the lightbox
function closeLightbox(){

	// hide lightbox and shadow <div/>'s
	jQuery('#lightbox').hide();
	jQuery('#lightbox-shadow').hide();

	// remove contents of lightbox in case a video or other content is actively playing
	jQuery('#lightbox').empty();
}