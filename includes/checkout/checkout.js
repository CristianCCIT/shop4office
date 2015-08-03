var submitter = null;
function submitFunction() {
	submitter = 1;
}

var errCSS = {
	'border-color': 'red',
	'border-style': 'solid'
};

function bindAutoFill($el){
	if ($el.attr('type') == 'select-one'){
		var method = 'change';
	}else{
		var method = 'blur';
	}
	
	$el.blur(unsetFocus).focus(setFocus);
	
	if (document.attachEvent){
		$el.get(0).attachEvent('onpropertychange', function (){
			if ($(event.srcElement).data('hasFocus') && $(event.srcElement).data('hasFocus') == 'true') return;
			/*if ($(event.srcElement).val() != '' && $(event.srcElement).hasClass('required')){
				$(event.srcElement).trigger(method);
			}*/
		});
	}else{
		$el.get(0).addEventListener('onattrmodified', function (e){
			if ($(e.currentTarget).data('hasFocus') && $(e.currentTarget).data('hasFocus') == 'true') return;
			if ($(e.currentTarget).val() != '' && $(e.currentTarget).hasClass('required')){
				$(e.currentTarget).trigger(method);
			}
		}, false);
	}
}

function setFocus(){
	$(this).data('hasFocus', 'true');
}

function unsetFocus(){
	$(this).data('hasFocus', 'false');
}

var checkout = {
	charset: 'UTF-8',
	pageLinks: {},
	fieldSuccessHTML: '<div style="margin-left:1px;margin-top:1px;float:left;" class="success_icon ui-icon-green ui-icon-circle-check"></div>',
	fieldErrorHTML: '<div style="margin-left:1px;margin-top:1px;float:left;" class="error_icon ui-icon-red ui-icon-circle-close"></div>',
	fieldRequiredHTML: '<div style="margin-left:1px;margin-top:1px;float:left;" class="required_icon ui-icon-red ui-icon-gear"></div>',
	
	waitingLoad: function(shippingMethods, paymentMethods) {
		if (shippingMethods) {
			var $sm = jQuery("#shippingMethods");
			if (!$sm.children().is('div.overflow')) {
				var width = $sm.width();
				var height = $sm.outerHeight()+20;
				var $div = $sm.prepend('<div class="overflow"><div id="overflow-container"><div id="overflow-spinner"></div></div></div>').find('div.overflow');
				$div.width(width);
				$div.height(height);
			}
		}
		if (paymentMethods) {
			var $pm = jQuery("#paymentMethods");
			if (!$pm.children().is('div.overflow')) {
				var width = $pm.width();
				var height = $pm.outerHeight();
				var $div = $pm.prepend('<div class="overflow"><div id="overflow-container"><div id="overflow-spinner"></div></div></div>').find('div.overflow');
				$div.width(width);
				$div.height(height);
			}
		}
	},
	fieldErrorCheck: function ($element, forceCheck, hideIcon){
		forceCheck = forceCheck || false;
		hideIcon = hideIcon || false;
		var errMsg = this.checkFieldForErrors($element, forceCheck);
		if (hideIcon == false){
			if (errMsg != false){
				this.addIcon($element, 'error', errMsg);
				return true;
			}else{
				this.addIcon($element, 'success', errMsg);
			}
		}else{
			if (errMsg != false){
				return true;
			}
		}
		return false;
	},
	checkFieldForErrors: function ($element, forceCheck){
		var hasError = false;
		if ($element.is(':visible') && ($element.hasClass('required') || forceCheck == true)){
			var errCheck = getFieldErrorCheck($element);
			if (!errCheck.errMsg){
				return false;
			}
			switch($element.attr('type')){
				case 'password':
				if ($element.attr('name') == 'password'){
					if ($element.val().length < errCheck.minLength){
						hasError = true;
					}
				}else{
					if ($element.val() != $(':password[name="password"]', $('#billingAddress')).val() || $element.val().length <= 0){
						hasError = true;
					}
				}
				break;
				case 'radio':
				if ($(':radio[name="' + $element.attr('name') + '"]:checked').size() <= 0){
					hasError = true;
				}
				break;
				case 'checkbox':
				if ($(':checkbox[name="' + $element.attr('name') + '"]:checked').size() <= 0){
					hasError = true;
				}
				break;
				case 'select-one':
				if ($element.val() == ''){
					hasError = true;
				}
				break;
				default:
				if ($element.val().length < errCheck.minLength){
					hasError = true;
				}
				break;
			}
			if (hasError == true){
				return errCheck.errMsg;
			}
		}
		return hasError;
	},
	addIcon: function ($curField, iconType, title){
		title = title || false;
		$('.success_icon, .error_icon, .required_icon', $curField.parent()).hide();
		switch(iconType){
			case 'error':
			if (this.initializing == true){
				this.addRequiredIcon($curField, 'Required');
			}else{
				this.addErrorIcon($curField, title);
			}
			break;
			case 'success':
			this.addSuccessIcon($curField, title);
			break;
			case 'required':
			this.addRequiredIcon($curField, 'Required');
			break;
		}
	},
	addSuccessIcon: function ($curField, title){
		if ($('.success_icon', $curField.parent()).size() <= 0){
			$curField.parent().append(this.fieldSuccessHTML);
		}
		$('.success_icon', $curField.parent()).attr('title', title).show();
	},
	addErrorIcon: function ($curField, title){
		if ($('.error_icon', $curField.parent()).size() <= 0){
			$curField.parent().append(this.fieldErrorHTML);
		}
		$('.error_icon', $curField.parent()).attr('title', title).show();
	},
	addRequiredIcon: function ($curField, title){
		if ($curField.hasClass('required')){
			if ($('.required_icon', $curField.parent()).size() <= 0){
				$curField.parent().append(this.fieldRequiredHTML);
			}
			$('.required_icon', $curField.parent()).attr('title', title).show();
		}
	},
	clickButton: function (elementName){
		if ($(':radio[name="' + elementName + '"]').size() <= 0){
			$('input[name="' + elementName + '"]').trigger('click', true);
		}else{
			$(':radio[name="' + elementName + '"]:checked').trigger('click', true);
		}
	},
	addRowMethods: function($row){
		$row.hover(function (){
			if (!$(this).hasClass('moduleRowSelected')){
				$(this).addClass('moduleRowOver');
			}
		}, function (){
			if (!$(this).hasClass('moduleRowSelected')){
				$(this).removeClass('moduleRowOver');
			}
		}).click(function (){
			if (!$(this).hasClass('moduleRowSelected')){
				var selector = ($(this).hasClass('shippingRow') ? '.shippingRow' : '.paymentRow') + '.moduleRowSelected';
				$(selector).removeClass('moduleRowSelected');
				$(this).removeClass('moduleRowOver').addClass('moduleRowSelected');
				if($(':radio', $(this)).is(':disabled')!==true)
				if (!$(':radio', $(this)).is(':checked')){
					$(':radio', $(this)).attr('checked', 'checked').click();
				}
			}
		});
	},
	queueAjaxRequest: function (options){
		var checkoutClass = this;
		if (options.waitingLoads) {
			var shippingMethodVal = options.waitingLoads['shippingMethods'];
			var paymentMethodVal = options.waitingLoads['paymentMethods'];
		} else {
			var shippingMethodVal = false;
			var paymentMethodVal = false;
		}
		var o = {
			url: options.url,
			cache: options.cache || false,
			dataType: options.dataType || 'html',
			type: options.type || 'GET',
			contentType: options.contentType || 'application/x-www-form-urlencoded; charset=' + this.ajaxCharset,
			data: options.data || false,
			beforeSend: checkout.waitingLoad(shippingMethodVal, paymentMethodVal),
			complete: function (){
				if (document.ajaxq.q['orderUpdate'].length <= 0){
					$('div.overflow').remove();
				}
			},
			success: options.success,
			error: function (XMLHttpRequest, textStatus, errorThrown){
				if (XMLHttpRequest.responseText == 'session_expired') document.location = this.pageLinks.shoppingCart;
				alert(options.errorMsg || 'There was an ajax error, please contact ' + checkoutClass.storeName + ' for support.');
			}
		};
		$.ajaxq('orderUpdate', o);
	},
	updateAddressHTML: function (type){
		var checkoutClass = this;
		this.queueAjaxRequest({
			url: this.pageLinks.checkout,
			data: 'action=' + (type == 'shipping' ? 'getShippingAddress' : 'getBillingAddress'),
			type: 'post',
			success: function (data){
				$('#' + type + 'Address').html(data);
				if(checkoutClass.showAddressInFields == true)
				{
				  checkoutClass.attachAddressFields();
			    }
			},
			errorMsg: 'There was an error loading your ' + type + ' address, please inform ' + checkoutClass.storeName + ' about this error.'
		});
	},
	attachAddressFields: function(){
		var checkoutClass = this;
		$('input', $('#billingAddress')).each(function (){
			if ($(this).attr('name') != undefined && $(this).attr('type') != 'checkbox' && $(this).attr('type') != 'radio'){
				$(this).blur(function (){
					if ($(this).hasClass('required')){
						checkoutClass.fieldErrorCheck($(this));
					}
				});
				bindAutoFill($(this));

				if ($(this).hasClass('required')){
					if (checkoutClass.fieldErrorCheck($(this), true, true) == false){
						checkoutClass.addIcon($(this), 'success');
					}else{
						checkoutClass.addIcon($(this), 'required');
					}
				}
			}
		});

		$('input,select[name="billing_country"], ', $('#billingAddress')).each(function (){
			var processFunction = function (){
				if ($(this).hasClass('required')){
					if (checkoutClass.fieldErrorCheck($(this)) == false){
						checkoutClass.processBillingAddress();
					}
				}else{
					checkoutClass.processBillingAddress();
				}
			};
			
			$(this).unbind('blur');
			if ($(this).attr('type') == 'select-one'){
				$(this).change(processFunction);
			}else{
				$(this).blur(processFunction);
			}
			bindAutoFill($(this));
		});
		$('input,select[name="shipping_country"]', $('#shippingAddress')).each(function (){
			if ($(this).attr('name') != undefined && $(this).attr('type') != 'checkbox'){
				var processAddressFunction = function (){
					if ($(this).hasClass('required')){
						if (checkoutClass.fieldErrorCheck($(this)) == false){
							checkoutClass.processShippingAddress();
						}else{
							$('#noShippingAddress').show();
							$('#shippingMethods').hide();
						}
					}else{
						checkoutClass.processShippingAddress(true);
					}
				};
				$(this).unbind('blur');
				if ($(this).attr('type') == 'select-one'){
					$(this).change(processAddressFunction);
				}else{
					$(this).blur(processAddressFunction);
				}
				bindAutoFill($(this));
			}
		});		
	},
	updateCartView: function (){
		var checkoutClass = this;
		this.queueAjaxRequest({
			url: this.pageLinks.checkout,
			data: 'action=updateCartView',
			type: 'post',
			success: function (data){
				if (data == 'none'){
					document.location = checkoutClass.pageLinks.shoppingCart;
				}else{
					$('#shoppingCart').html(data);

					$('.removeFromCart').each(function (){
						checkoutClass.addCartRemoveMethod($(this));
					});
				}
			},
			errorMsg: 'There was an error refreshing the shopping cart, please inform ' + checkoutClass.storeName + ' about this error.'
		});
	},
	updateFinalProductListing: function (){
		var checkoutClass = this;
		this.queueAjaxRequest({
			url: this.pageLinks.checkout,
			data: 'action=getProductsFinal',
			type: 'post',
			success: function (data){
				$('.finalProducts').html(data);
			},
			errorMsg: 'There was an error refreshing the final products listing, please inform ' + checkoutClass.storeName + ' about this error.'
		});
	},
	setGV: function (status){
		var checkoutClass = this;
		this.queueAjaxRequest({
			url: this.pageLinks.checkout,
			data: 'action=setGV&cot_gv=' + status,
			type: 'post',
			dataType: 'json',
			success: function (data){
				checkoutClass.updateOrderTotals();
			},
			errorMsg: 'There was an error ' + (status=='on'?'':'Un') + 'setting Gift Voucher method, please inform ' + checkoutClass.storeName + ' about this error.'
		});
	},
	updateOrderTotals: function (){
		var checkoutClass = this;
		this.queueAjaxRequest({
			url: this.pageLinks.checkout,
			cache: false,
			data: 'action=getOrderTotals',
			type: 'post',
			success: function (data){
				$('.orderTotals').html(data);
			},
			errorMsg: 'There was an error refreshing the shopping cart, please inform ' + checkoutClass.storeName + ' about this error.'
		});
	},
	updatePoints: function()
	{
		var checkoutClass = this;
		checkoutClass.queueAjaxRequest({
			url: this.pageLinks.checkout,
			data: 'action=updatePoints',
			type: 'post',
			success: function (data){
				$('#pointsSection').html(data);
					if($(':input[name="customer_points"]',$(this)))
					{
						$(':input[name="customer_points"]').keypress(function(event){
							if (event.keyCode == '13') {
								if($(':checkbox[name="use_shopping_points"]').is(':checked'))
								{
									$('input[name="customer_points"]').attr('disabled','true');
									checkoutClass.checkPoints();
									this.changed = true;
								}else
								{
									this.changed = false;
								}
								event.preventDefault();
								return false;
							}
							
						});
						$(':checkbox[name="use_shopping_points"]').click(function() {
							if($(':checkbox[name="use_shopping_points"]').is(':checked'))
							{
								$('input[name="customer_points"]').attr('disabled','true');
								checkoutClass.checkPoints();
							}else
							{
								checkoutClass.clearPoints();
							}
							return true;
						});
						
						$(':input[name="customer_points"]').blur(function() {
							if($(':checkbox[name="use_shopping_points"]').is(':checked'))
							{
								$('input[name="customer_points"]').attr('disabled','true');
								checkoutClass.checkPoints();
							}
						});
						
					}
			},
			errorMsg: 'There was an error updating points, please inform IT Web Experts about this error.'
		});
		return false;
	},
	checkPoints: function()
	{
		var checkoutClass = this;
		checkoutClass.queueAjaxRequest({
			url: checkoutClass.pageLinks.checkout,
			data: 'action=redeemPoints&points=' + $('input[name="customer_points"]').val(),
			type: 'post',beforeSend: checkout.waitingLoad(),
			dataType: 'json',
			success: function (data){
				if (data.success == false){
					alert('You do not have ' + $('input[name="customer_points"]').val() + ' points please enter a valid number of points');
				}
				$('input[name="customer_points"]').removeAttr('disabled');
				checkoutClass.updatePoints();
				checkoutClass.updateOrderTotals();
				$('div.overflow').remove();
			},
			errorMsg: 'There was an error redeeming points, please inform IT Web Experts about this error.'
		});
		return false;
	},
	clearPoints: function()
	{
		var checkoutClass = this;
		checkoutClass.queueAjaxRequest({
			url: checkoutClass.pageLinks.checkout,
			data: 'action=clearPoints',
			type: 'post',
			beforeSend: checkout.waitingLoad(),
			dataType: 'json',
			success: function (data){
				checkoutClass.updatePoints();
				checkoutClass.updateOrderTotals();
				$('div.overflow').remove();
			},
			errorMsg: 'There was an error redeeming points, please inform IT Web Experts about this error.'
		});
		return false;
	},
	updateModuleMethods: function (action, noOrdertotalUpdate){
		var checkoutClass = this;
		var descText = (action == 'shipping' ? 'Shipping' : 'Payment');
		this.queueAjaxRequest({
			url: this.pageLinks.checkout,
			data: 'action=update' + descText + 'Methods',
			type: 'post',
			waitingLoads: {'shippingMethods' : true, 'paymentMethods' : true},
			success: function (data){
				$('#no' + descText + 'Address').hide();
				$('#' + action + 'Methods').html(data).show();
				if(action == 'payment')
				{
					if($('input[name="cot_gv"]', $(this))) {
						$('input[name="cot_gv"]', $(this)).each(function (){
							$(this).change(function (e){
								checkoutClass.setGV(($(':checkbox[name="cot_gv"]').is(':checked'))?'':'on');
							});
						});
					}
					if($(':input[name="customer_points"]',$(this)))
					{
						$(':input[name="customer_points"]').keypress(function(event){
							if (event.keyCode == '13') {
								if($(':checkbox[name="use_shopping_points"]').is(':checked'))
								{
									$('input[name="customer_points"]').attr('disabled','true');
									checkoutClass.checkPoints();
									this.changed = true;
								}else
								{
									this.changed = false;
								}
								event.preventDefault();
								return false;
							}
							
						});
						$(':checkbox[name="use_shopping_points"]').click(function() {
							if($(':checkbox[name="use_shopping_points"]').is(':checked'))
							{
								$('input[name="customer_points"]').attr('disabled','true');
								checkoutClass.checkPoints();
							}else
							{
								checkoutClass.clearPoints();
							}
							return true;
						});
						
						$(':input[name="customer_points"]').blur(function() {
							if($(':checkbox[name="use_shopping_points"]').is(':checked'))
							{
								$('input[name="customer_points"]').attr('disabled','true');
								checkoutClass.checkPoints();
							}
						});
						
					}
				}
				$('.' + action + 'Row').each(function (){
					checkoutClass.addRowMethods($(this));

					$('input[name="' + action + '"]', $(this)).each(function (){
						var setMethod = checkoutClass.setPaymentMethod;
						if (action == 'shipping'){
							setMethod = checkoutClass.setShippingMethod;
						}
						$(this).click(function (e, noOrdertotalUpdate){
							setMethod.call(checkoutClass, $(this));
								checkoutClass.updateOrderTotals();
						});
					});
				});
				checkoutClass.clickButton(descText.toLowerCase());
			},
			errorMsg: 'There was an error updating ' + action + ' methods, please inform ' + checkoutClass.storeName + ' about this error.'
		});
	},
	updateShippingMethods: function (noOrdertotalUpdate){
		if (this.shippingEnabled == false){
			return false;
		}

		this.updateModuleMethods('shipping', noOrdertotalUpdate);
	},
	updatePaymentMethods: function (noOrdertotalUpdate){
		this.updateModuleMethods('payment', noOrdertotalUpdate);
	},
	setModuleMethod: function (type, method, successFunction){
		var checkoutClass = this;
		this.queueAjaxRequest({
			url: this.pageLinks.checkout,
			data: 'action=set' + (type == 'shipping' ? 'Shipping' : 'Payment') + 'Method&method=' + method,
			type: 'post',
			dataType: 'json',
			success: successFunction,
			errorMsg: 'There was an error setting ' + type + ' method, please inform ' + checkoutClass.storeName + ' about this error.'
		});
	},
	setShippingMethod: function ($button){
		if (this.shippingEnabled == false){
			return false;
		}

		var checkoutClass = this;
		this.setModuleMethod('shipping', $button.val(), function (data){
		});
		this.updatePaymentMethods(true);
	},
	setPaymentMethod: function ($button){
		var checkoutClass = this;
		this.setModuleMethod('payment', $button.val(), function (data){
			$('.paymentFields').remove();
			if (data.inputFields != ''){
				$(data.inputFields).insertAfter($button.parent().parent());
			}
		});
	},
	loadAddressBook: function ($dialog, type){
		var checkoutClass = this;
		this.queueAjaxRequest({
			url: this.pageLinks.checkout,
			data: 'action=getAddressBook&addressType=' + type,
			type: 'post',
			success: function (data){
				$dialog.html(data);
			},
			errorMsg: 'There was an error loading your address book, please inform ' + checkoutClass.storeName + ' about this error.'
		});
	},
	addCountryAjax: function ($input, fieldName, stateCol){
		var checkoutClass = this;
		$input.change(function (event, callBack){
			if ($(this).hasClass('required')){
				if ($(this).val() != '' && $(this).val() > 0){
					checkoutClass.addIcon($(this), 'success');
				}
			}
			var thisName = $(this).attr('name');
			var $origStateField = $('*[name="' + fieldName + '"]', $('#' + stateCol));
			checkoutClass.queueAjaxRequest({
				url: checkoutClass.pageLinks.checkout,
				data: 'action=countrySelect&fieldName=' + fieldName + '&cID=' + $(this).val() + '&curValue=' + $origStateField.val(),
				type: 'post',
				beforeSendMsg: 'Getting Country\'s Zones',
				success: function (data){
					$('#' + stateCol).html(data);
					var $curField = $('*[name="' + fieldName + '"]', $('#' + stateCol));

					if ($curField.hasClass('required')){
						if (checkoutClass.fieldErrorCheck($curField, true, true) == false){
							checkoutClass.addIcon($curField, 'success');
						}else{
							checkoutClass.addIcon($curField, 'required');
						}
					}

					var processAddressFunction = checkoutClass.processBillingAddress;
					if (thisName == 'shipping_country'){
						processAddressFunction = checkoutClass.processShippingAddress;
					}
					
					var processFunction = function (){
						if ($(this).hasClass('required')){
							if (checkoutClass.fieldErrorCheck($(this)) == false){
								processAddressFunction.call(checkoutClass);
							}
						}else{
							processAddressFunction.call(checkoutClass);
						}
					};
					
					bindAutoFill($curField);
					
					if ($curField.attr('type') == 'select-one'){
						$curField.change(processFunction);
					}else{
						$curField.blur(processFunction);
					}

					if (callBack){
						callBack.call(checkoutClass);
					}
				},
				errorMsg: 'There was an error getting states, please inform ' + checkoutClass.storeName + ' about this error.'
			});
		});
	},
	addCartRemoveMethod: function ($element){
		var checkoutClass = this;
		$element.click(function (){
			var $productRow = $(this).parent().parent();
			checkoutClass.queueAjaxRequest({
				url: checkoutClass.pageLinks.checkout,
				data: $(this).attr('linkData'),
				type: 'post',
				dataType: 'json',
				success: function (data){
					if (data.products == 0){
						document.location = checkoutClass.pageLinks.shoppingCart;
					}else{
						$productRow.remove();
						checkoutClass.updateFinalProductListing();
						checkoutClass.updateShippingMethods(true);
						checkoutClass.updateOrderTotals();
					}
				},
				errorMsg: 'There was an error updating shopping cart, please inform ' + checkoutClass.storeName + ' about this error.'
			});
			return false;
		});
	},
	processBillingAddress: function (skipUpdateTotals){
		var hasError = false;
		var checkoutClass = this;
		$('select[name="billing_country"], input[name="billing_street_address"], input[name="billing_zipcode"], input[name="billing_city"], *[name="billing_state"]', $('#billingAddress')).each(function (){
			if (checkoutClass.fieldErrorCheck($(this), false, true) == true){
				hasError = true;
			}
		});
		if (hasError == true){
			return;
		}

		this.setBillTo();
		if ($('#diffShipping').is(':checked')){
			this.setSendTo(true);
		}else{
			this.setSendTo(false);
		}
		if(skipUpdateTotals != true) {
			//this.updateCartView();
			this.updateFinalProductListing();
			if ($('#diffShipping').is(':checked')){
				if ($('select[name="shipping_country"] option:selected').index() != '0') {
					this.updatePaymentMethods(true);
					this.updateShippingMethods(true);
				}
			} else {
				this.updatePaymentMethods(true);
				this.updateShippingMethods(true);
			}
			this.updateOrderTotals();
		}
	},
	processShippingAddress: function (skipUpdateTotals){
		var hasError = false;
		var checkoutClass = this;
		$('select[name="shipping_country"], input[name="shipping_street_address"], input[name="shipping_zipcode"], input[name="shipping_city"]', $('#shippingAddress')).each(function (){
			if (checkoutClass.fieldErrorCheck($(this), false, true) == true){
				hasError = true;
			}
		});
		if (hasError == true){
			return;
		}

		this.setSendTo(true);
		if (this.shippingEnabled == true && skipUpdateTotals != true){
			this.updateShippingMethods(true);
		}
		if(skipUpdateTotals != true)
		{
			//this.updateCartView();
			this.updateFinalProductListing();
			this.updatePaymentMethods(true);
			this.updateShippingMethods(true);
			this.updateOrderTotals();
		}
	},
	setCheckoutAddress: function (type, useShipping){
		var checkoutClass = this;
		var selector = '#' + type + 'Address';
		var sendMsg = 'Setting ' + (type == 'shipping' ? 'Shipping' : 'Billing') + ' Address';
		var errMsg = type + ' address';
		if (type == 'shipping' && useShipping == false){
			selector = '#billingAddress';
			sendMsg = 'Setting Shipping Address';
			errMsg = 'billing address';
		}

		action = 'setBillTo';
		if (type == 'shipping'){
			action = 'setSendTo';
		}

		this.queueAjaxRequest({
			url: this.pageLinks.checkout,
			beforeSendMsg: sendMsg,
			dataType: 'json',
			data: 'action=' + action + '&' + $('*', $(selector)).serialize(),
			type: 'post',
			success: function (){
			},
			errorMsg: 'There was an error updating your ' + errMsg + ', please inform ' + checkoutClass.storeName + ' about this error.'
		});
	},
	SearchDealer: function (skipUpdateTotals){
		var hasError = false;
		var dealer = $('input[name="dealer_search"]').val();
		var checkoutClass = this;
		checkoutClass.queueAjaxRequest({
			url: checkoutClass.pageLinks.checkout,
			data: 'action=updateShippingMethods&dealer='+dealer,
			type: 'post',
			dataType: 'json',
			success: function (data){
				alert(data);
				checkoutClass.updateShippingMethods(true);
			}
		});
		if(skipUpdateTotals != true)
		{
			//this.updateCartView();
			this.updateFinalProductListing();
			this.updatePaymentMethods(true);
			this.updateShippingMethods(true);
			this.updateOrderTotals();
		}
	},
	setBillTo: function (){
		this.setCheckoutAddress('billing', false);
	},
	setSendTo: function (useShipping){
		this.setCheckoutAddress('shipping', useShipping);
	},
	initCheckout: function (){
		var checkoutClass = this;
		if(this.autoshow == true){
			$('#shippingAddress').hide();
        	//this.updateCartView();
			this.updateFinalProductListing();
            this.setBillTo();
            this.setSendTo(false);
			this.updatePaymentMethods(true);
			this.updateShippingMethods(true);
			this.updateOrderTotals();
			
		}else	if (this.loggedIn == false){
			$('#shippingAddress').hide();
			$('#shippingMethods').html('');
		}

		$('#checkoutNoScript').remove();
		$('#checkoutYesScript').show();

		$('.removeFromCart').each(function (){
			checkoutClass.addCartRemoveMethod($(this));
		});

		this.updateFinalProductListing();
		this.updateOrderTotals();

		$('#diffShipping').click(function (){
			if (this.checked){
				$('#shippingAddress').show();
				$('select[name="shipping_country"]').trigger('change');
			}else{
				$('#shippingAddress').hide();
				$('select[name="billing_country"]').trigger('change');
			}
		});
		$('#billing_show_pw').click(function (){
			if (this.checked){
				$('#PwFields').show();
			}else{
				$('#PwFields').hide();
			}
		});


		if (this.loggedIn == true){
			$('.shippingRow, .paymentRow').each(function (){
				checkoutClass.addRowMethods($(this));
			});

			$('input[name="payment"]').each(function (){
				$(this).click(function (){
					checkoutClass.setPaymentMethod($(this));
					checkoutClass.updateOrderTotals();
				});
			});

			if (this.shippingEnabled == 'true'){
				$('input[name="shipping"]').each(function (){
					$(this).click(function (){
						checkoutClass.setShippingMethod($(this));
						checkoutClass.updateOrderTotals();
					});
				});
			}
		}

		if ($('#paymentMethods').is(':visible')){
			this.clickButton('payment');
		}

		if (this.shippingEnabled == true){
			if ($('#shippingMethods').is(':visible')){
				this.clickButton('shipping');
			}
		}

		$('input, password', $('#billingAddress')).each(function (){
			if ($(this).attr('name') != undefined && $(this).attr('type') != 'checkbox' && $(this).attr('type') != 'radio'){
				if ($(this).attr('type') == 'password'){
					$(this).blur(function (){
						if ($(this).hasClass('required')){
							checkoutClass.fieldErrorCheck($(this));
						}
					});
					/* Used to combat firefox 3 and it's auto-populate junk */
					$(this).val('');

					if ($(this).attr('name') == 'password'){
						$(this).focus(function (){
							$(':password[name="confirmation"]').val('');
						});

						var rObj = getFieldErrorCheck($(this));
						$(this).pstrength({
							addTo: '#pstrength_password',
							minchar: rObj.minLength
						});
					}
				}else{
					$(this).blur(function (){
						if ($(this).hasClass('required')){
							checkoutClass.fieldErrorCheck($(this));
						}
					});
					bindAutoFill($(this));
				}

				if ($(this).hasClass('required')){
					if (checkoutClass.fieldErrorCheck($(this), true, true) == false){
						checkoutClass.addIcon($(this), 'success');
					}else{
						checkoutClass.addIcon($(this), 'required');
					}
				}
			}
		});
		$('input,select[name="billing_country"], ', $('#billingAddress')).each(function (){
			var processFunction = function (){
				if ($(this).hasClass('required')){
					if (checkoutClass.fieldErrorCheck($(this)) == false){
						checkoutClass.processBillingAddress();
					}
				}else{
					checkoutClass.processBillingAddress(true);
				}
			};
			
			$(this).unbind('blur');
			if ($(this).attr('type') == 'select-one'){
				$(this).change(processFunction);
			}else{
				$(this).blur(processFunction);
			}
			bindAutoFill($(this));
		});

		$('input[name="billing_email_address"]').each(function (){
			$(this).unbind('blur').blur(function (){
				var $thisField = $(this);
				checkoutClass.setBillTo();
				if (checkoutClass.initializing == true){
					checkoutClass.addIcon($thisField, 'required');
				}else{
					if (checkoutClass.fieldErrorCheck($thisField, true, true) == false){
						this.changed = false;
						if($thisField.val() == '')
						{
							checkoutClass.addIcon($thisField, 'error', data.errMsg.replace('/n', "\n"));
						}
						checkoutClass.queueAjaxRequest({
							url: checkoutClass.pageLinks.checkout,
							data: 'action=checkEmailAddress&emailAddress=' + $thisField.val(),
							type: 'post',
							beforeSendMsg: 'Checking Email Address',
							dataType: 'json',
							success: function (data){
								if (data.success == 'false'){
									$thisField.val('').addClass('error');
									alert(data.errMsg.replace('/n', "\n").replace('/n', "\n").replace('/n', "\n"));
								}
							},
							errorMsg: 'There was an error checking email address, please inform ' + checkoutClass.storeName + ' about this error.'
						});
					}
				}
			}).keyup(function (){
				this.changed = true;
			});
			bindAutoFill($(this));
		});
		$('input,select[name="shipping_country"]', $('#shippingAddress')).each(function (){
			if ($(this).attr('name') != undefined && $(this).attr('type') != 'checkbox'){
				var processAddressFunction = function (){
					if ($(this).hasClass('required')){
						if (checkoutClass.fieldErrorCheck($(this)) == false){
							checkoutClass.processShippingAddress();
						}else{
							$('#noShippingAddress').show();
							$('#shippingMethods').hide();
						}
					}else{
						checkoutClass.processShippingAddress(true);
					}
				};
				
				$(this).unbind('blur');
				if ($(this).attr('type') == 'select-one'){
					$(this).change(processAddressFunction);
				}else{
					$(this).blur(processAddressFunction);
				}
				bindAutoFill($(this));
			}
		});
		$('#updateCartButton').click(function (){
			checkoutClass.queueAjaxRequest({
				url: checkoutClass.pageLinks.checkout,
				data: 'action=updateQuantities&' + $('input', $('#shoppingCart')).serialize(),
				type: 'post',
				dataType: 'json',
				success: function (){
					//checkoutClass.updateCartView();
					checkoutClass.updateFinalProductListing();
					if ($('#noPaymentAddress:hidden').size() > 0){
						checkoutClass.updatePaymentMethods();
						checkoutClass.updateShippingMethods(true);
					}
					checkoutClass.updateOrderTotals();
				},
				errorMsg: 'There was an error updating shopping cart, please inform ' + checkoutClass.storeName + ' about this error.'
			});
			return false;
		});

		function checkAllErrors(){
			var errMsg = '';
			if ($('.error:visible, .hiddenError:visible', $('#billingAddress')).size() > 0 || $('.inputRequirement', $('#billingAddress')).size() > 0){
				errMsg += required_billing_address+"\n";
			}
			if ($('.required_icon:visible', $('#billingAddress')).size() > 0){
				errMsg += required_billing_address+"\n";
			}
			if($('#billingAddress :password[name="password"]').val() != '')
			{
				if($('#billingAddress :password[name="confirmation"]').val() == '' || $('#billingAddress :password[name="confirmation"]').val() != $('#billingAddress :password[name="password"]').val() )
				{
					errMsg += confirmation_paswoord_error+"\n";
				}
			}

			if ($('#diffShipping:checked').size() > 0){
				if ($('.error, .hiddenError', $('#shippingAddress')).size() > 0){
					errMsg += required_shipping_address+"\n";
				}
			}

			if (errMsg != ''){
				errMsg = addressErrors + errMsg+"\n\n";
			}

			if ($(':radio[name="payment"]:checked').size() <= 0){
				if ($('input[name="payment"]:hidden').size() <= 0){
					if ($('input[name="cot_gv"]:checked').size() <= 0 || ($(':radio[name="payment"]:disabled').size() != $(':radio[name="payment"]').size() && $('input[name="cot_gv"]:checked').size() > 0)) {
						errMsg += payment_selection_error+"\n\n";
					}
				}
			}
			
			if(this.pointsInstalled == true && $(':checkbox[name="use_shopping_points"]').is(':checked') && $('input[name="customer_points"]').val() >0)
			{
			}

			/*if (checkoutClass.shippingEnabled === true){*/
				if ($(':radio[name="shipping"]:checked').size() <= 0){
					if ($('input[name="shipping"]:hidden').size() <= 0){
						errMsg += shipping_selection_error+"\n\n";
					}
				}
			/*}*/
			if(this.ccgvInstalled == true) {
				if($('input[name="gv_redeem_code"]').val() == 'redeem code')
				{
					$('input[name="gv_redeem_code"]').val('');
				}
			}
			if(!$("#TermsAgree").attr("checked")){
				errMsg += terms_agree_error+"\n\n";
			}
			if (errMsg.length > 0){
				alert(errMsg);
				return false;
			}else{
				return true;
			}
		}
		if(this.pointsInstalled == true)
		{
			$(':input[name="customer_points"]').keypress(function(event){
				if (event.keyCode == '13') {
					if($(':checkbox[name="use_shopping_points"]').is(':checked'))
					{
						$('input[name="customer_points"]').attr('disabled','true');
						checkoutClass.checkPoints();
						this.changed = true;
					}else
					{
						this.changed = false;
					}
					event.preventDefault();
					return false;
				}
			});

			$(':checkbox[name="use_shopping_points"]').click(function() {
				if($(':checkbox[name="use_shopping_points"]').is(':checked'))
				{
					$('input[name="customer_points"]').attr('disabled','true');
					checkoutClass.checkPoints();
				}else
				{
					checkoutClass.clearPoints();
				}
				return true;
			});
			
			$(':input[name="customer_points"]').blur(function() {
				if($(':checkbox[name="use_shopping_points"]').is(':checked'))
				{
					$('input[name="customer_points"]').attr('disabled','true');
					checkoutClass.checkPoints();
				}
			});
			
		}

		$('#checkoutButton').click(function() {
			if (checkAllErrors()) {
				$(this).closest('form').submit();
				return true
			} else {
				return false
			}
		});

		if (this.ccgvInstalled == true){
			$('input[name="gv_redeem_code"]').focus(function (){
				if ($(this).val() == 'redeem code'){
					$(this).val('');
				}
			});

			$('#voucherRedeem').click(function (){
				checkoutClass.queueAjaxRequest({
					url: checkoutClass.pageLinks.checkout,
					data: 'action=redeemVoucher&code=' + $('input[name="gv_redeem_code"]').val(),
					type: 'post',
					beforeSendMsg: 'Validating Coupon',
					dataType: 'json',
					success: function (data){
						if (data.success == 'gift') {
							checkoutClass.updateModuleMethods('payment');
							checkoutClass.updateOrderTotals();
						} else {
							if (data.success == "false"){
								alert(voucher_redeem_error);
							}
							checkoutClass.updateOrderTotals();
						}
					},
					errorMsg: 'There was an error redeeming coupon, please inform ' + checkoutClass.storeName + ' about this error.'
				});
				return false;
			});
			if($('input[name="cot_gv"]'))
			{
				$('input[name="cot_gv"]').live('change', function(e) {
					checkoutClass.setGV(($(':checkbox[name="cot_gv"]').is(':checked'))?'on':'');
				});
				$('input[name="cot_gv"]').each(function (){
					$(this).change(function (e){
						checkoutClass.setGV(($(':checkbox[name="cot_gv"]').is(':checked'))?'on':'');
					});
				});
			}
		}
		if (this.loggedIn == true && this.showAddressInFields == true){
			$('*[name="billing_state"]').trigger('change');
			$('*[name="delivery_state"]').trigger('change');
		}

		this.initializing = false;
	}
}