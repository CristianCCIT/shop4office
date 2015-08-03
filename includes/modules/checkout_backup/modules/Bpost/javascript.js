
$(document).ready(function() {
	if (!$('input#shipping_Bpost').is(':checked')) {
		$('#Bpost_points_list').hide();
	}
	$('input[name=shipping]').change(function() {
		if ($('input#shipping_Bpost').is(':checked')) {
			$('#Bpost_points_list').show();
		} else {
			$('#Bpost_points_list').hide();
		}
	});
});
