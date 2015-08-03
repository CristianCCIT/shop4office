
$(document).ready(function() {
	if (!$('#showDelivery input').is(':checked')) {
		$('.hideDelivery').hide();
	}
	$('#showDelivery input').live('change', function() {
		$('.hideDelivery').toggle();
	});
	$('#CAparagraph a').click(function() {
		window.open($(this).attr('href'), $(this).html(), 'width=500,height=400,scrollbars=yes');
		return false;
	});
	
	$('.login_modal').hide();
	$('#login_modal_button').show();
	$('#login_modal_button').click(function() {
		$('.login_modal').addClass('modal fade');
		$('.login_modal').modal('show');
		return false;
	});
	
	if ($('.alert.alert-error', $('#create_account_block')).length < 1) {
		$('#create_account_block').hide();
		$('#show_create_account_button').css('display', 'block');
		$('#show_create_account_button').click(function() {
			$('#create_account_block').show();
			$(this).hide();
			return false;
		});
	}
});

