
$(document).ready(function() {
	if (!$('input#shipping_Kiala').is(':checked')) {
		$('#Kiala_points_list').hide();
	}
	$('input[name=shipping]').change(function() {
		if ($('input#shipping_Kiala').is(':checked')) {
			$('#Kiala_points_list').show();
		} else {
			$('#Kiala_points_list').hide();
		}
	});
});
