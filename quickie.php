<?php
require('includes/application_top.php');
$navigation->set_snapshot();
$error_str = '';
if ($_GET['action'] == 'add_many_quickie_add_cart') {
	for ($i=1;$i<(count($_POST['quickie_model']) + 1);$i++) {
		if (tep_not_null($_POST['quickie_model'][$i])){
			$quickie_query = tep_db_query("select p.products_id, p.products_model, pd.products_name from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where products_model = '" . $_POST['quickie_model'][$i] . "' and pd.products_id = p.products_id and pd.language_id = '" . (int)$languages_id . "'");
			if (tep_db_num_rows($quickie_query) != 1 || !tep_db_num_rows($quickie_query)) {
				$error_str .= Translate('Product').' '.$i.' '.Translate('niet gevonden').'<br>';
			}
			$quickie = tep_db_fetch_array($quickie_query);
			if (tep_has_product_attributes($quickie['products_id'])) {
				if (isset($_POST['quickie_attr'][$i])) {
					$cart->add_cart($quickie['products_id'], $cart->get_quantity(tep_get_uprid($quickie['products_id'], $_POST['quickie_attr'][$i]))+$_POST['quickie_qty'][$i], $_POST['quickie_attr'][$i]);
				} else {
					$error_str .= Translate('Product').' '.$i.' '.Translate('heeft extra opties en is daardoor niet toegevoegd aan het winkelwagentje. Kies hier de gewenste opties').': <a style="text-decoration=underline" href="' . tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $quickie['products_id']) . '" target=_new>' . $quickie['products_name'] . '</a><br>';
				}
			} else {
				$cart->add_cart($quickie['products_id'], $cart->get_quantity($quickie['products_id']) + $_POST['quickie_qty'][$i], false);
			}
		}
	}
	tep_redirect(tep_href_link(FILENAME_SHOPPING_CART));
}
$breadcrumb->add('Snel-bestellen', tep_href_link(FILENAME_QUICKIE));
require(DIR_WS_INCLUDES . 'header.php');
require(DIR_WS_INCLUDES . 'column_left.php');
?>
<table  align="center" border="0" width="97%" cellspacing="0" cellpadding="0" bgcolor="#FFFFFF">
<?php
if ($error_str !='') {
?>
	<tr>
		<td class="errorBox"><?php echo $error_str; ?></td>
	</tr>
<?php 
}
?>	  
	<tr>
		<td><h1>Snel-bestellen</h1></td>
	</tr>
	<tr>
		<td class="main">Met snelbestellen kan u op een eenvoudige manier uw bestelling ingeven aan de hand van (gekende) productnummers.</td>
	</tr>
	<tr>
		<td height="10"></td>
	</tr>
	<tr>
		<td>
			U kan hier het aantal lijnen dat getoond wordt veranderen
			<select id="qtyLines">
				<option value="5">5</option>
				<option selected="selected" value="10">10</option>
				<option value="20">20</option>
				<option value="50">50</option>
				<option value="100">100</option>
			</select>
		</td>
	</tr>	  
	<tr>
		<td>
			<form name="quickie_direkt" method="POST" action="<?php  echo tep_href_link(FILENAME_QUICKIE,'action=add_many_quickie_add_cart');?>">
				<table cellspacing="2" cellpadding="0" border="0" width="100%" class="editTable">
					<tr class="quickie_header">
						<td class="quickie_header" style="width: 70px;">
							<?php echo Translate('Referentie n&deg;');?>
						</td>
						<td class="quickie_header" style="width: 50px;">
							<?php echo Translate('Aantal');?>
						</td>
						<td class="quickie_header">
							<?php echo Translate('Productnaam');?>
						</td>
						<td class="quickie_header">
							<?php echo Translate('Prijs per stuk');?>
						</td>
						<td class="quickie_header">
							<?php echo Translate('Totale prijs');?>
						</td>
						<td class="quickie_header">
							<?php echo Translate('Extra');?>
						</td>
					</tr>
					<?php
					for ($i=1; $i<11; $i++) {
						if ($i%2 == 1) {
							$class= 'quickie_odd';
						} else {
							$class= 'quickie_even';
						}
					?>
					<tr>
						<td class="<?php echo $class;?>">
							<input type="text" class="autocomplete" name="quickie_model[<?php echo $i;?>]" />
						</td>
						<td class="<?php echo $class;?>">
							<input type="text" name="quickie_qty[<?php echo $i;?>]" style="width: 56px;" />
						</td>
						<td class="<?php echo $class;?>"></td>
						<td class="<?php echo $class;?>"></td>
						<td class="<?php echo $class;?>"></td>
						<td class="<?php echo $class;?>"></td>
					</tr>
					<?php
					}
					?>
				</table>
				<div style="clear:both;height:5px;"></div>
				<input type="submit" class="button-b" value="<?php echo Translate('In Winkelwagen');?>" />
			</form>
			<script type="text/javascript">
				jQuery.fn.focusNextInputField = function() {
					return this.each(function() {
						var fields = jQuery(this).parents('form:eq(0),body').find('button,input,textarea,select');
						var index = fields.index( this );
						if ( index > -1 && ( index + 1 ) < fields.length ) {
							fields.eq( index + 1 ).select();
						}
						return false;
					});
				};
				jQuery(function () {
					/*change lines*/				
					function checkZebra () {
						jQuery('.editTable').find('tr:not(.quickie_header)').each(function(index) {
							if (index%2) {
								jQuery(this).children('td').each(function() {
									jQuery(this).removeClass('quickie_even');
									jQuery(this).addClass('quickie_odd');
									jQuery(this).children('input[name^=quickie_model]').attr('name', 'quickie_model['+index+']');
									jQuery(this).children('input[name^=quickie_qty]').attr('name', 'quickie_qty['+index+']');
								});
							} else {
								jQuery(this).children('td').each(function() {
									jQuery(this).removeClass('quickie_odd');
									jQuery(this).addClass('quickie_even');
									jQuery(this).children('input[name^=quickie_model]').attr('name', 'quickie_model['+index+']');
									jQuery(this).children('input[name^=quickie_qty]').attr('name', 'quickie_qty['+index+']');
								});
							}
						});
						
						jQuery(document).find('input[name^=quickie_]').change(function() {
							getProduct(jQuery(this));
						});
					}
					function openDialog (element, selected, currentLines) {
						var Referentie = element.find('input[name^=quickie_model]').val();
						var qty = element.children('td').eq(1).children('input').val();
						var name = element.children('td').eq(2).children('a').text();
						jQuery('#dialog-message').find('.dialogRef').remove();
						jQuery('#dialog-message').find('.dialogText').after('<p class="dialogRef"><br />'+qty+'x '+Referentie+' ('+name+')</p>');
						jQuery("#dialog-message").dialog({
							height: 180,
							modal: true,
							buttons: {
								'Nee': function() {
									jQuery(this).dialog('close');
									jQuery(this).dialog("destroy");
									currentLines--;
									if (currentLines == 0) {
										currentLines = jQuery('.editTable').find('tr:not(.quickie_header)').length;
										var lines = 100;
										if (currentLines > 5) {
											if (currentLines > 10) {
												if (currentLines > 20) {
													if (currentLines < 50) {
														lines = 50;
													}
												} else {
													lines = 20;
												}
											} else {
												lines = 10;
											}
										} else {
											lines = 5;
										}
										jQuery("#qtyLines option").attr('selected', false); 
										jQuery('#qtyLines option[value='+lines+']').attr('selected', 'selected');
										linesChanged ()
									} else {
										deleteRow(jQuery('.editTable tr').eq(currentLines), selected, currentLines);
									}
								},
								'Ja': function() {
									jQuery(this).dialog('close');
									jQuery(this).dialog("destroy");
									element.remove();
									currentLines--;
									deleteRow(jQuery('.editTable tr').eq(currentLines), selected, currentLines);
								}
							}
						});
					}
					function checkEmptyRows() {
						var count = 0
						jQuery('.editTable').find('tr:not(.quickie_header)').each(function() {
							jQuery(this).find('input[name^=quickie_model]').each(function() {
								if (jQuery(this).val() == '') {
									count++;
								}
							});
						});
						return count;
					}
					function deleteEmptyRows(element, selected, currentLines) {
						if (jQuery('.editTable').find('tr:not(.quickie_header)').length > selected) {
							if (checkEmptyRows() > 0) {
								element.find('input[name^=quickie_model]').each(function() {
									if (jQuery(this).val() == '') {
										element.remove();
									}
								});
								currentLines--;
								deleteEmptyRows(jQuery('.editTable tr').eq(currentLines), selected, currentLines);
							} else {
								currentLines = jQuery('.editTable').find('tr:not(.quickie_header)').length;
								deleteRow(jQuery('.editTable tr').eq(currentLines), selected, currentLines);
							}
						}
					}
					function deleteRow(element, selected, currentLines) {
						if (jQuery('.editTable').find('tr:not(.quickie_header)').length > selected) {
							element.find('input[name^=quickie_model]').each(function() {
								if (jQuery(this).val() == '') {
									element.remove();
									currentLines--;
									deleteRow(jQuery('.editTable tr').eq(currentLines), selected, currentLines);
								} else {
									openDialog(element, selected, currentLines);
								}
							});
						}
						checkZebra();
					}
					function linesChanged () {
						var selected = jQuery('#qtyLines').val();
						var currentLines = jQuery('.editTable').find('tr:not(.quickie_header)').length;
						if (selected > currentLines) {
							var difference = selected - currentLines;
							var newRows = jQuery('.editTable tr:last').html();
							for (var i = 0;i<difference;i++) {
								if (newRows.indexOf('quickie_even') >= 0) {
									newRows = newRows.replace('quickie_even', 'quickie_odd', "gi");
								} else {
									newRows = newRows.replace('quickie_odd', 'quickie_even', "gi");
								}
								jQuery('.editTable tr:last').after('<tr>'+newRows+'</tr>');
								jQuery('.editTable tr:last').children('td').eq(2).html('');
								jQuery('.editTable tr:last').children('td').eq(3).html('');
								jQuery('.editTable tr:last').children('td').eq(4).html('');
							}
						} else {
							deleteEmptyRows(jQuery('.editTable tr').eq(currentLines), selected, currentLines);
						}
						checkZebra();
					}
					jQuery('#qtyLines').change(function() {
						linesChanged();
					});
					/*End change linse*/
					/*Get product and qty*/
					function getProduct(element) {
						var parentTr = element.parent('td').parent('tr'),
							model = parentTr.children('td').eq(0).children('input').val(),
							count = parentTr.children('td').eq(0).children('input').attr('name'),
							currentQty = parentTr.children('td').eq(1).children('input').val();
						count = count.split('[');
						count = count[1].substr(0, count[1].length - 1);
						if (currentQty == '') {
							parentTr.children('td').eq(1).children('input').val(1);
							currentQty = 1;
						} else if (currentQty > <?php echo NUM_PROD_MAXORD;?>) {
							currentQty = <?php echo NUM_PROD_MAXORD;?>;
							parentTr.children('td').eq(1).children('input').val(currentQty);
						}
						jQuery.ajax({
							url: "get_products.php",
							data: "model="+model+"&qty="+currentQty,
							success: function(data){
								data = data.substr(1, data.length);
								data = data.substr(0, (data.length - 2));
								var allData = data.split('";"');
								parentTr.children('td').eq(2).html(allData[0]);
								parentTr.children('td').eq(3).html(allData[1]);
								parentTr.children('td').eq(4).html(allData[2]);
								if(allData[5] && allData[6]) {
									parentTr.children('td').eq(5).html('<input type="hidden" name="quickie_attr['+count+']['+allData[5]+']" value="'+allData[6]+'" />'+allData[7]);
								}
							}
						})
						return false;
					}
					jQuery(document).find('input[name^=quickie_]').change(function() {
						getProduct(jQuery(this));
					});
					
					jQuery('input').live('keydown', function(event) {
						if (event.keyCode == '13' || event.keyCode == '9') {
							event.preventDefault();
							getProduct(jQuery(this));
							jQuery(this).focusNextInputField();
						}
					});
					$('.autocomplete').each(function() {
						$(this).autocomplete({
							delay: 100,
							minLenght: 2,
							source: function(request, response) {
								$.ajax({
									url: 'ajax_search.php',
									dataType: "json",
									data: {
										mode: 'get_products_model',
										term: request.term
									},
									success: function(content) {
										response( $.map( content.list, function( item ) {
											return {
												label: item.text,
												value: item.text
											}
										}));
									}
								});
							},
							position: {  collision: "flip" },
							focus: function( event, ui ) {
								$(this).val( ui.item.label );
								return false;
							},
							select: function( event, ui ) {
								$(this).val( ui.item.label );
								$(this).trigger('change');
								return false;
							}
						});
					});
				});
			</script>
			<div id="dialog-message" title="Lijn verwijderen" style="display:none;">
				<p></p>
				<p style="padding-top:15px;">
					<span class="ui-icon ui-icon-circle-check" style="float:left; margin:4px 7px 0px 0;"></span>
					<p class="dialogText">Wilt u dit product uit de lijst verwijderen?</p>
				</p>
			</div>
		</td>
	</tr>	  
</table>
<?php
require(DIR_WS_INCLUDES . 'column_right.php');
require(DIR_WS_INCLUDES . 'footer.php');
require(DIR_WS_INCLUDES . 'application_bottom.php');
?>