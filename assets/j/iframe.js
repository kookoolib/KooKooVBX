$(document).ready(function() {
	$('iframe').load(function(e) {
		var href = $(this).contents().attr('URL');
		var title = $(this).contents().attr('title');
	
		// Replace the current state's URL
		if(history && history.replaceState) {
			history.replaceState({}, title, href);
		} else {
			document.location.hash = href.replace(OpenVBX.home, '#');
		}
	});
});