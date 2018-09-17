jQuery(document ).ready(function() {
	jQuery('.variations .value').hide();
	jQuery("[for=asa]").next().find('.value').show();

	jQuery('.variations .value').each(function(i,el) {
		jQuery(el).click(function() {
			jQuery('.variations .value').hide('slow');
			jQuery(this).toggle('slow'); 
		});
	});
});