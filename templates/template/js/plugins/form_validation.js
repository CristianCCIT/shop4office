function validate(element, showError) {
	var condition = element.attr('condition'),
		title = element.attr('title'),
		error = false;
	if (/^\d*$/.test(condition)) { 																				//check length
		if (element.hasClass('required')) {
			if (element.val().length < condition) {
				error = true;
			}
		} else {
			if (element.val().length > 0) {
				if (element.val().length < condition) {
					error = true;
				}
			}
		}
	} else if (condition !== undefined) {
		if ((condition == 'email' && element.val() != '') || (condition == 'email_required')) { 				//check email
			error = validateEmail(element.val());
		} else if (condition.indexOf('select') == 0) {															//check select/dropdown
			condition = condition.replace('select_', '');
			if (condition.indexOf('i') == 0) {
				var index = condition.substr(1),
					elementName = element.attr('name'),
					currentIndex = $('select[name='+elementName+'] option:selected').index();
				if (currentIndex == index) {
					error = true;
				}
			}
		} else if (condition.indexOf('confirmation_') == 0) {													//check confirmation
			var confirmation_input = condition.replace('confirmation_', '');
			if (confirmation_input.indexOf('_required') > 0) {
				confirmation_input = confirmation_input.replace('_required', '');
				if ((element.val() != $('input[name='+confirmation_input+']').val()) || (element.val() == '')) {
					error = true;
				}
			} else {
				if (element.val() != $('input[name='+confirmation_input+']').val()) {
					error = true;
				}
			}
		} else if (condition.indexOf('reg') == 0) { 															//check regular expression
			if ((condition.indexOf('_required') >= 0) || (element.val() != '')) {
				if (condition.indexOf('_required') >= 0) {
					condition = condition.replace('_required', '');
				}
				condition = new RegExp(condition.substr(3));
				if (condition.test(element.val())) {
					error = false;
				} else {
					error = true;
				}
			}
		} else if ((condition == 'btw' && element.val() != '') || (condition == 'btw_required')) { 				//check btw/vat
			var european_union_countries = ['AT', 'BE', 'BG', 'CY', 'CZ', 'DE', 'DK', 'EE', 'ES', 'FI', 'FR', 'GB', 'GR', 'HU', 'IE', 'IT', 'LT', 'LU', 'LV', 'MT', 'NL', 'PL', 'PT', 'RO', 'SE', 'SI', 'SK'],
				country = element.val().substr(0, 2),
				number = element.val().substr(2).replace(/ /gi, '');
			if (/^\d*$/.test(country)) { //check if numeric
				error = true;
			} else if ((/^\d*$/.test(number)) && (european_union_countries.indexOf(country) >= 0)) {
				if (element.hasClass('error')) {
					error = true;
				}
				$.getJSON('http://isvat.appspot.com/'+country+'/'+number+'/?callback=?', function(data){
					if (data) {
						element.removeClass('error');
						element.removeClass('hiddenError');
						updateSubmitButton(false, 'false');
					} else {
						element.addClass('error');
					}
				});
			} else {
				error = true;
			}
		} else if (condition.indexOf('number') >= 0) { 															//check if number + length from-to
			if (condition.indexOf('_required') >= 0 || element.val() != '') {
				if (condition.indexOf('_required') >= 0) {
					condition = condition.replace('_required', '');
				}
				var length = condition.replace('number', '').split('-');
				if (/^\d*$/.test(element.val())) { //check if numeric
					if (element.val().length < length[0] || element.val().length > length[1]) {
						error = true;
					}
				} else {
					error = true;
				}
			}
		} else if (condition.indexOf('range') >= 0) { 															//check if range from-to (numeric)
			if (condition.indexOf('_required') >= 0 || element.val() != '') {
				if (condition.indexOf('_required') >= 0) {
					condition = condition.replace('_required', '');
				}
				var range = condition.replace('range', '').split('-');
				if (/^\d+[.]*\d*$/.test(element.val())) { //check if numeric
					if ((element.val() - 0) < range[0] || (element.val() - 0) > range[1]) {
						error = true;
					}
				} else {
					error = true;
				}
			}
		}
	}
	if (showError) {
		if (error) {element.addClass('error');} else {element.removeClass('hiddenError');element.removeClass('error');}
	} else {
		if (error) {element.addClass('hiddenError');} else {element.removeClass('hiddenError');element.removeClass('error');}
	}
}
function validateEmail(value){
	var filter = /\b[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}\b/;
	if (value.match(filter)){return false;}else{return true;}
}
function updateSubmitButton(showError, validateIt) {
	if (validateIt != 'false') {
		$('input:not(:hidden), select:not(:hidden), textarea:not(:hidden)').each(function() {
			if ($(this).attr('condition')) {
				validate($(this), showError);
			}
		});
	}
	if ((($('input.error:not(:hidden)').length > 0) && ($('select.error:not(:hidden)').length > 0) && ($('textarea.error:not(:hidden)').length > 0)) || ($('.hiddenError:not(:hidden)').length > 0)) {
		$('input:submit').css('opacity', '0.5');
	} else {
		$('input:submit').animate({'opacity': '1'}, 300);
	}
}
function showMask(element) {
	var mask = element.attr('mask'), top = element.height(), width = element.width(), height = element.height(), div = $('<div class="mask" style="width:'+width+'px;height:'+height+'px;line-height:'+height+'px;">'+mask+'</div>');
	if (element.parent('div').children('.mask').length < 1) {
		element.after(div);
		div.animate({'margin-top': top}, 300);
	} else {
		element.parent('div').children('.mask').show().animate({'margin-top': top}, 300);
	}
	
}
function hideMask(element) {
	var marginTop = element.css('margin-top').replace('px', '');
	if (marginTop > 0) {
		element.parent('div').children('.mask').animate({'margin-top': marginTop}, 300);
	} else {
		element.parent('div').children('.mask').animate({'margin-top': '0'}, 300);
	}
}
$(document).ready(function(){
	$('#telephone').live('focus', function() {
		$land = $('#country').val();
		if ($land == '21') {
			//$('#telephone').attr('condition', 'reg\\d{3}\\/\\d{2}\\.\\d{2}\\.\\d{2}_required');
			//$('#telephone').attr('condition', 'reg[a-zA-Z0-9]_required');
			$('#telephone').attr('condition', 'reg[a-zA-Z0-9]{5}_required');
			//$('#telephone').next('div').html('bv. 000/00.00.00');
		} else if ($land == '150') {
			//$('#telephone').attr('condition', 'reg\\d{3}\\-\\d{7}_required');
			$('#telephone').attr('condition', 'reg[a-zA-Z0-9]\s\d_required');
			$('#telephone').next('div').html('bv. 000-0000000');
		} else {
			$('#telephone').attr('condition', '5');
			//$('#telephone').next('div').html('bv. 000/00.00.00');
		}
	});
	$('#fax').live('focus', function() {
		$land = $('#country').val();
		if ($land == '21') {
			$('#fax').attr('condition', 'reg\\d{3}\\/\\d{2}\\.\\d{2}\\.\\d{2}');
			$('#fax').next('div').html('bv. 000/00.00.00');
		} else if ($land == '150') {
			$('#fax').attr('condition', 'reg\\d{3}\\-\\d{7}');
			$('#fax').next('div').html('bv. 000-0000000');
		} else {
			$('#fax').removeAttr('condition');
			$('#fax').next('div').html('bv. 000/00.00.00');
		}
	});
	if ($.browser.msie && $.browser.version == '6.0') {}else{
		$('.masked').remove();
		$('input').each(function() {
			if($(this).attr('mask')) {
				$(this).wrap('<div style="position:relative;" />');
				$(this).live('focus', function() {
					showMask($(this));
					$(this).live('blur', function() {
						hideMask($(this));
					});
				});
			}
		});
	}
	updateSubmitButton();
	$('input:not(:hidden), select, textarea').live('change', function() {validate($(this), true);updateSubmitButton();});
	$('input:not(:hidden), select, textarea').live('blur', function() {validate($(this), true);updateSubmitButton();});
	$('input:submit').hover(function() {
		updateSubmitButton();
	});
	$('input:submit').click(function() {
		updateSubmitButton(true);
		if ((($('input.error').length > 0) && ($('select.error').length > 0) && ($('textarea.error').length > 0)) || ($('.hiddenError').length > 0)) {
			return false;
		}
	});
});