jQuery(document).ready(function($) {
	$.each(accordion_shortcode, function(id, attr) {
		$("#" + id).accordion(attr);
	});
	if (location.hash) {
		$(location.hash).trigger('click');
	}
});