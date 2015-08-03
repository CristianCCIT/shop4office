<?php
Header('Content-type: text/html; charset=UTF-8');
ob_start();
require('includes/application_top.php');
if ($_GET['mode'] == 'get_products_model') {
	$selected_pms = array();
	$pm_query = tep_db_query('SELECT products_model FROM products WHERE products_model LIKE "%'.$_GET['term'].'%" LIMIT 20');
	while ($pm = tep_db_fetch_array($pm_query)) {
		$selected_pms[] = array('text' => $pm['products_model']);
	}	
	$pms['list'] = $selected_pms;
	echo json_encode($pms);
} else if ($_GET['mode'] == 'productWithAttrInCart') {
	$attributes = array();
	$attr_query = tep_db_query('SELECT pa.options_id, pa.options_values_id, pa.options_values_price, pa.price_prefix, po.products_options_name, pov.products_options_values_name FROM products_attributes pa, products_options po, products_options_values pov WHERE pa.products_id = "'.$_GET['products_id'].'" AND pa.options_id = po.products_options_id AND po.language_id = "'.(int)$languages_id.'" AND pa.options_values_id = pov.products_options_values_id AND pov.language_id = "'.(int)$languages_id.'"');
	if (tep_db_num_rows($attr_query) > 0) {
		while ($attr = tep_db_fetch_array($attr_query)) {
			if ($cart->in_cart($_GET['products_id'].'{'.$attr['options_id'].'}'.$attr['options_values_id'])) {
				$attributes['success'] = true;
				$attributes['attr'][] = $_GET['products_id'].'{'.$attr['options_id'].'}'.$attr['options_values_id'];
				$attributes['products'][$_GET['products_id'].'{'.$attr['options_id'].'}'.$attr['options_values_id']] = $attr;
			}
		}
		$attributes['count'] = count($attributes['attr']);
	} else {
		$attributes['success'] = false;
	}
	echo json_encode($attributes);
} else if ($_GET['mode'] == 'removeFromCart') {
	$cart->remove($_GET['product']);
} else if ($_GET['mode'] == 'addToCart') {
	$cart->add_cart($_GET['products_id'], $_GET['quantity'], $_GET['attributes']);
} else if ($_GET['mode'] == 'shoppingCart') {
	include(DIR_WS_BOXES.'shopping_cart.php');
} else if ($_GET['mode']=='compare') {
	?>
    <div id="compare_table_products">
    <?php
	$cookie_val = $_COOKIE['compare_'.$_GET['cc']];
	$compare_count=0;
	$total_products = (sizeof(explode('_', $cookie_val )))-1;
	$product_width = 100/($total_products+1);
	$splitted = explode('_', $cookie_val );
	$first_product = $splitted[1];
	$print_friendly = $_GET['print_friendly'];
	if ($print_friendly=='1') { ?>
		<div class="product-printfriendly-details">
			<a href="javascript:window.print();" class="productFile print" id="print_link"><?php echo Translate('Afdrukken'); ?></a>
		</div>
	<?php
	}
	?>
	<h1><?php echo Translate('Producten vergelijken'); ?></h1>
	<?php
	if (($print_friendly!='1') && (PRODUCT_COMPARE_VIEW=='content')) { ?>
        <a href="#" id="compare_close" class="close_window"><?php echo Translate('Sluiten'); ?></a>
	<?php
	}
	$category_info_query = tep_db_query("select categories_name from " . TABLE_CATEGORIES_DESCRIPTION . " where categories_id = '" . (int)$_GET['cc'] . "' and language_id = '" . (int)$languages_id . "'");
	$category_info = tep_db_fetch_array($category_info_query);
	if ($total_products<1) {
		?>
        <p><?php echo Translate('U hebt geen producten geselecteerd voor vergelijking.'); ?></p>
		<?php
	} else {
		?>
        <p><?php echo sprintf(Translate('Hieronder kan u de geselecteerde %s vergelijken.'), $category_info['categories_name']); ?></p>
		<?php
	} 
	?>
	<div class="product-listing compare">
		<?php
		if ($first_product!='') {
			$product_info_query = tep_db_query("select p.products_id, p.products_model, p.products_image, pd.products_name from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.products_status = '1' and p.products_id = '" . (int)$first_product . "' and pd.products_id = p.products_id and pd.language_id = '" . (int)$languages_id . "'");
			$product_info = tep_db_fetch_array($product_info_query);
			?>
			<table width="100%" border="0" cellspacing="1" cellpadding="0" class="compare_table_products">
				<tr class="compare_table_products">
					<td style="width:<?php echo $product_width; ?>%;">
						<div class="compare_legende">
							<ul>
								<?php if ($print_friendly!='1') { ?>
								<li>
                                    <a href="<?php echo tep_href_link(FILENAME_COMPARE_PRINT, 'cc=' . $_GET['cc']); ?>" onClick="window.open('<?php echo tep_href_link(FILENAME_COMPARE_PRINT, 'cc='.$_GET['cc'], 'NONSSL'); ?>', '<?php echo $category_info['categories_name'].' '.Translate('vergelijken');?>','scrollbars=yes,resizable=yes,status=yes,width=800,height=600'); return false" target="_blank" class="productFile print"><?php echo Translate('Printvriendelijke versie'); ?></a>
                                </li>
								<?php } ?>
								<?php if (PRODUCT_COMPARE_DIFFERENCES=='true') { ?>
								<li><a href="#" id="compare_show_differ"><?php echo Translate('Toon verschillen'); ?></a></li>
								<?php } ?>
							</ul>
							<?php if (PRODUCT_COMPARE_DIFFERENCES=='true') { ?>
							<script type="text/javascript">
							/* <![CDATA[ */
							jQuery(document).ready(function(){
								jQuery("#compare_show_differ").click(function(){
									jQuery("tr.differ").toggleClass('highlight');
									jQuery(this).text(jQuery(this).text() == '<?php echo Translate("Toon verschillen"); ?>' ? '<?php echo Translate("Verberg verschillen"); ?>' : '<?php echo Translate("Toon verschillen"); ?>');
									return false;
								});
							});
							/* ]]> */
							</script>
							<?php } ?>
						</div>
					</td>
					<?php
					$compare_count=0;
					foreach (explode('_', $cookie_val ) as $products_id) {
						if ($products_id!='') {
							$compare_count++;
							$product_info_query = tep_db_query("select p.products_id, pd.products_name, pd.products_description, p.products_model, p.products_quantity, p.products_image, pd.products_url, p.products_price, p.products_tax_class_id, p.products_date_added, p.products_date_available, p.manufacturers_id, pd.products_technical, p.products_image_1, p.products_image_2, p.products_image_3, p.products_image_4, p.products_opt1, p.products_opt2, p.products_opt3, p.products_opt4, p.products_opt5 from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.products_status = '1' and p.products_id = '" . (int)$products_id . "' and pd.products_id = p.products_id and pd.language_id = '" . (int)$languages_id . "'");
							$product_info = tep_db_fetch_array($product_info_query);
							?>
							<td style="width:<?php echo $product_width; ?>%;" class="product-<?php echo $compare_count; ?>">
								<div class="product product-<?php echo $compare_count; ?>">
									<a href="#" id="compare_delete_<?php echo $product_info['products_id']; ?>" class="compare_delete_frame"><?php echo Translate('Verwijderen'); ?></a>
									<script type="text/javascript">
									/* <![CDATA[ */
									jQuery(document).ready(function(){
										var compare_category = <?php echo $_GET['cc']; ?>;
										jQuery('.compare_delete_frame').live('click', function() {
											if (jQuery.readCookie("compare_"+compare_category)) {
												var cookie = jQuery.readCookie("compare_"+compare_category);
											} else {
												var cookie = '';
											}
											var productId = jQuery(this).attr('id').replace('compare_delete', '');
											jQuery('#compare_list .compare' + productId).remove();
											jQuery('#compare'+productId).attr('checked', false);
											jQuery.setCookie("compare_"+compare_category, cookie.replace(productId, ''), { 
												duration: <?php echo PRODUCT_COMPARE_COOKIE_DURATION; ?>,
												path: '<?php echo HTTP_COOKIE_PATH; ?>'
											});
											if (jQuery('#compare_list').children().length==0){
												jQuery('#compare_list').append('<li class="compare_empty"><?php echo Translate('Er zijn nog geen producten geselecteerd.'); ?></li>').show('slow');
											}
											if (jQuery('#compare_list').children().length>1) {
												jQuery('.box.compare .compare_button').show('slow');
											} else {
												jQuery('.box.compare .compare_button').hide('slow');
											}
											var removalClass = jQuery(this).parent().attr('class').replace('product ', '');
											jQuery('.'+removalClass).remove();
											jQuery.ajax({
												url: '<?php echo tep_href_link(FILENAME_AJAX_SEARCH, 'mode=compare&cc='.$_GET['cc'].'&print_friendly='.$print_friendly); ?>',
												beforeSend: waitingCompareLoad,
												success: function(data){
													jQuery("#compare_table_products").html(data);
												}
											});
											return false;
										});
									});
									function waitingCompareLoad() {
										var $pr = jQuery("#compare_table_products");
										if (!$pr.children().is('div.overflow')) {
											var width = $pr.width();
											var height = $pr.height();
											var $div = $pr.prepend('<div class="overflow"><span><?php echo Translate('Even geduld, Uw selectie wordt geladen.');?></span></div>').find('div.overflow');
											$div.width(width);
											$div.height(height);
										}
									}
									/* ]]> */
									</script>
									<div class="product-image"><a href="<?php echo tep_href_link(FILENAME_PRODUCT_INFO, 'products_id='.$product_info['products_id']); ?>"><?php echo tep_image(DIR_WS_IMAGES.$product_info['products_image'], $product_info['products_name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT); ?></a></div>
									<div class="product-name"><?php echo $product_info['products_name']; ?></div>
									<?php if (CanShop() == 'true') { ?>
									<div class="product-buy-now">
                                    <?php if (PRODUCT_COMPARE_VIEW=='popup') { ?>
                                        <a href="javascript:window.opener.location.href='<?php echo tep_href_link(FILENAME_DEFAULT, tep_get_all_get_params(array('action')).'action=buy_now&products_id='.$product_info['products_id']); ?>';window.close();" class="button-b"><?php echo Translate('Voeg toe aan winkelwagen'); ?></a>
									<?php } else { ?>
                                        <a href="<?php echo tep_href_link(FILENAME_DEFAULT, tep_get_all_get_params(array('action')).'action=buy_now&products_id='.$product_info['products_id']); ?>" class="button-b"><?php echo Translate('Voeg toe aan winkelwagen'); ?></a>
									<?php } ?>
									</div>
									<?php } ?>
								</div>
							</td>
						<?php
						}
					}
					$compare_count=0;
					?>
				</tr>
				<?php
				/*model*/
				if (PRODUCT_COMPARE_MODEL=='true') {
					?>
					<tr class="compare_table_specifications" id="sk_model">
						<td class="legende" style="width:<?php echo $product_width; ?>%;"><?php echo Translate('Product nummer'); ?></td>
						<?php
						$differ='';
						$kenmerk='';
						foreach (explode('_', $cookie_val ) as $products_id) {
							if ($products_id!='') {
								$compare_count++;
								$products_model_query = tep_db_query("select products_model from " . TABLE_PRODUCTS . " where products_id = '" . (int)$products_id . "'");
								$products_model = tep_db_fetch_array($products_model_query);

								?>
								<td class="product-<?php echo $compare_count; ?>" style="width:<?php echo $product_width; ?>%;">
									<?php
									if (($differ=='') && ($kenmerk!='')) {
										if (('<span class="kenmerk-waarde">'.$products_model['products_model'].'</span>')!=$kenmerk) {
											$differ = true;
										}
									}
									$kenmerk = '<span class="kenmerk-waarde">'.$products_model['products_model'].'</span>';
									echo $kenmerk;
									?>
								</td>
							<?php
							}
						}
						$compare_count=0;
						?>
					</tr>
					<?php
					if ($differ=='true') {
						if (PRODUCT_COMPARE_DIFFERENCES=='true') {
						?>
						<script type="text/javascript">
						/* <![CDATA[ */
						jQuery(document).ready(function(){
							jQuery("#sk_model").addClass('differ');
						});
						/* ]]> */
						</script>
						<?php
						}
					}
				}
				/*model*/
				
				/*price*/
				if (PRODUCT_COMPARE_PRICE=='true') {
					if (CanShop() == 'true') { 
						?>
						<tr class="compare_table_specifications" id="sk_price">
							<td class="legende" style="width:<?php echo $product_width; ?>%;"><?php echo Translate('Prijs'); ?></td>
							<?php
							$differ='';
							$kenmerk='';
							foreach (explode('_', $cookie_val ) as $products_id) {
								if ($products_id!='') {
									$compare_count++;
									$price_query = tep_db_query("select products_price, products_tax_class_id from " . TABLE_PRODUCTS . " where products_id = '" . (int)$products_id . "'");
									$price = tep_db_fetch_array($price_query);
									//DISCOUNT
									if (USE_PRICES_TO_QTY == 'false' && PRICE_BOOK == 'true') { //added here so this can be used add the whole page
										$discount_price = tep_get_discountprice($price['products_price'], $customer_id, $customer_group, $products_id, $cPath, $price['manufacturers_id']);
										if (($discount_price['lowest']['discount'] > 0 || strstr($discount_price['lowest']['discount'], '%')) && (tep_get_products_special_price($products_id) > $discount_price['lowest']['price'] || !tep_get_products_special_price($products_id))) {
											$discount = $discount_price['lowest']['discount'];
										} else {
											$discount = false;
										}
									}
									if (USE_PRICES_TO_QTY == 'false') {
										if ($discount_price['lowest']['discount'] > 0 && PRICE_BOOK == 'true') {
											if ($new_price = tep_get_products_special_price($products_id)) {
												if ($new_price < $discount_price['lowest']['price']) {
													$products_price = '<span class="oldprice">';
													$products_price .= $currencies->display_price($price['products_price'], tep_get_tax_rate($price['products_tax_class_id']));
													$products_price .= '</span>&nbsp;';
													$products_price .= '<span class="specialprice">';
													$products_price .= $currencies->display_price($new_price, tep_get_tax_rate($price['products_tax_class_id']));
													$products_price .= '</span>';
												} else {
													$products_price = '<span class="oldprice">';
													$products_price .= $currencies->display_price($price['products_price'], tep_get_tax_rate($price['products_tax_class_id']));
													$products_price .= '</span>&nbsp;';
													$products_price .= '<span class="specialprice">';
													$products_price .= $currencies->display_price($discount_price['lowest']['price'], tep_get_tax_rate($price['products_tax_class_id']));
													$products_price .= '</span>';
												}
											} else {
												$products_price = '<span class="oldprice">';
												$products_price .= $currencies->display_price($price['products_price'], tep_get_tax_rate($price['products_tax_class_id']));
												$products_price .= '</span>&nbsp;';
												$products_price .= '<span class="specialprice">';
												$products_price .= $currencies->display_price($discount_price['lowest']['price'], tep_get_tax_rate($price['products_tax_class_id']));
												$products_price .= '</span>';
											}
										} else {
											if ($new_price = tep_get_products_special_price($product_info['products_id'])) {
												$products_price = '<span class="oldprice">' . $currencies->display_price($product_info['products_price'], tep_get_tax_rate($product_info['products_tax_class_id'])) . '</span> <span class="specialprice">' . $currencies->display_price($new_price, tep_get_tax_rate($product_info['products_tax_class_id'])) . '</span>';
											} else {
												$products_price = '<span class="yourprice">' . $currencies->display_price($product_info['products_price'], tep_get_tax_rate($product_info['products_tax_class_id'])).'</span>';
											}
										}
									}
									//END DISCOUNT
									?>
									<td class="product-<?php echo $compare_count; ?>" style="width:<?php echo $product_width; ?>%;">
										<?php
										if (($differ=='') && ($kenmerk!='')) {
											if (('<span class="kenmerk-waarde"><strong>'.$products_price.'</strong></span>')!=$kenmerk) {
												$differ = true;
											}
										}
										$kenmerk = '<span class="kenmerk-waarde"><strong>'.$products_price.'</strong></span>';
										echo $kenmerk;
										?>
									</td>
								<?php
								}
							}
							$compare_count=0;
							?>
						</tr>
						<?php
						if ($differ=='true') {
							if (PRODUCT_COMPARE_DIFFERENCES=='true') {
							?>
							<script type="text/javascript">
							/* <![CDATA[ */
							jQuery(document).ready(function(){
								jQuery("#sk_price").addClass('differ');
							});
							/* ]]> */
							</script>
							<?php
							}
						}
					}
				}
				/*price*/
				
				/*manufacturer*/
				if (PRODUCT_COMPARE_MANUFACTURER=='true') {
					?>
					<tr class="compare_table_specifications" id="sk_manufacturer">
						<td class="legende" style="width:<?php echo $product_width; ?>%;"><?php echo Translate('Merk'); ?></td>
						<?php
						$differ='';
						$kenmerk='';
						foreach (explode('_', $cookie_val ) as $products_id) {
							if ($products_id!='') {
								$compare_count++;
								$manufacturer_query = tep_db_query("select p.manufacturers_id, m.manufacturers_name from " . TABLE_PRODUCTS . " p, " . TABLE_MANUFACTURERS . " m where p.manufacturers_id = m.manufacturers_id and p.products_id = '" . (int)$products_id . "'");
								$manufacturer = tep_db_fetch_array($manufacturer_query);
								?>
								<td class="product-<?php echo $compare_count; ?>" style="width:<?php echo $product_width; ?>%;">
									<?php
									if (($differ=='') && ($kenmerk!='')) {
										if (('<span class="kenmerk-waarde">'.$manufacturer['manufacturers_name'].'</span>')!=$kenmerk) {
											$differ = true;
										}
									}
									$kenmerk = '<span class="kenmerk-waarde">'.$manufacturer['manufacturers_name'].'</span>';
									echo $kenmerk;
									?>
								</td>
							<?php
							}
						}
						$compare_count=0;
						?>
					</tr>
					<?php
					if ($differ=='true') {
						if (PRODUCT_COMPARE_DIFFERENCES=='true') {
						?>
						<script type="text/javascript">
						/* <![CDATA[ */
						jQuery(document).ready(function(){
							jQuery("#sk_manufacturer").addClass('differ');
						});
						/* ]]> */
						</script>
						<?php
						}
					}
				}
				/*manufacturer*/
				
				/*description*/
				if (PRODUCT_COMPARE_DESCRIPTION=='true') {
					?>
					<tr class="compare_table_specifications" id="sk_description">
						<td class="legende" style="width:<?php echo $product_width; ?>%;"><?php echo Translate('Omschrijving'); ?></td>
						<?php
						$differ='';
						$kenmerk='';
						foreach (explode('_', $cookie_val ) as $products_id) {
							if ($products_id!='') {
								$compare_count++;
								$description_query = tep_db_query("select products_description from " . TABLE_PRODUCTS_DESCRIPTION . " where products_id = '" . (int)$products_id . "' AND language_id = '".(int)$languages_id."'");
								$description = tep_db_fetch_array($description_query);
								?>
								<td class="product-<?php echo $compare_count; ?>" style="width:<?php echo $product_width; ?>%;">
									<?php
									if (($differ=='') && ($kenmerk!='')) {
										if (('<span class="kenmerk-waarde">'.$description['products_description'].'</span>')!=$kenmerk) {
											$differ = true;
										}
									}
									$kenmerk = '<span class="kenmerk-waarde">'.$description['products_description'].'</span>';
									echo $kenmerk;
									?>
								</td>
							<?php
							}
						}
						$compare_count=0;
						?>
					</tr>
					<?php
					if ($differ=='true') {
						if (PRODUCT_COMPARE_DIFFERENCES=='true') {
						?>
						<script type="text/javascript">
						/* <![CDATA[ */
						jQuery(document).ready(function(){
							jQuery("#sk_description").addClass('differ');
						});
						/* ]]> */
						</script>
						<?php
						}
					}
				}
				/*description*/
				
				/*technical*/
				if (PRODUCT_COMPARE_TECHNICAL=='true') {
					?>
					<tr class="compare_table_specifications" id="sk_technical">
						<td class="legende" style="width:<?php echo $product_width; ?>%;"><?php echo Translate('Technische info'); ?></td>
						<?php
						$differ='';
						$kenmerk='';
						foreach (explode('_', $cookie_val ) as $products_id) {
							if ($products_id!='') {
								$compare_count++;
								$technical_query = tep_db_query("select products_technical from " . TABLE_PRODUCTS_DESCRIPTION . " where products_id = '" . (int)$products_id . "' AND language_id = '".(int)$languages_id."'");
								$technical = tep_db_fetch_array($technical_query);
								?>
								<td class="product-<?php echo $compare_count; ?>" style="width:<?php echo $product_width; ?>%;">
									<?php
									if (($differ=='') && ($kenmerk!='')) {
										if (('<span class="kenmerk-waarde">'.$technical['products_technical'].'</span>')!=$kenmerk) {
											$differ = true;
										}
									}
									$kenmerk = '<span class="kenmerk-waarde">'.$technical['products_technical'].'</span>';
									echo $kenmerk;
									?>
								</td>
							<?php
							}
						}
						$compare_count=0;
						?>
					</tr>
					<?php
					if ($differ=='true') {
						if (PRODUCT_COMPARE_DIFFERENCES=='true') {
						?>
						<script type="text/javascript">
						/* <![CDATA[ */
						jQuery(document).ready(function(){
							jQuery("#sk_technical").addClass('differ');
						});
						/* ]]> */
						</script>
						<?php
						}
					}
				}
				/*technical*/
				
				/*products_opt1*/
				if (PRODUCT_COMPARE_OPT1=='true') {
					?>
					<tr class="compare_table_specifications" id="sk_products_opt1">
						<td class="legende" style="width:<?php echo $product_width; ?>%;"><?php echo Translate('Optieveld 1'); ?></td>
						<?php
						$differ='';
						$kenmerk='';
						foreach (explode('_', $cookie_val ) as $products_id) {
							if ($products_id!='') {
								$compare_count++;
								$products_opt_query = tep_db_query("select products_opt1 from " . TABLE_PRODUCTS . " where products_id = '" . (int)$products_id . "'");
								$products_opt = tep_db_fetch_array($products_opt_query);
								?>
								<td class="product-<?php echo $compare_count; ?>" style="width:<?php echo $product_width; ?>%;">
									<?php
									if (($differ=='') && ($kenmerk!='')) {
										if (('<span class="kenmerk-waarde">'.$products_opt['products_opt1'].'</span>')!=$kenmerk) {
											$differ = true;
										}
									}
									$kenmerk = '<span class="kenmerk-waarde">'.$products_opt['products_opt1'].'</span>';
									echo $kenmerk;
									?>
								</td>
							<?php
							}
						}
						$compare_count=0;
						?>
					</tr>
					<?php
					if ($differ=='true') {
						if (PRODUCT_COMPARE_DIFFERENCES=='true') {
						?>
						<script type="text/javascript">
						/* <![CDATA[ */
						jQuery(document).ready(function(){
							jQuery("#sk_products_opt1").addClass('differ');
						});
						/* ]]> */
						</script>
						<?php
						}
					}
				}
				/*products_opt1*/
				/*products_opt2*/
				if (PRODUCT_COMPARE_OPT2=='true') {
					?>
					<tr class="compare_table_specifications" id="sk_products_opt2">
						<td class="legende" style="width:<?php echo $product_width; ?>%;"><?php echo Translate('Optieveld 2'); ?></td>
						<?php
						$differ='';
						$kenmerk='';
						foreach (explode('_', $cookie_val ) as $products_id) {
							if ($products_id!='') {
								$compare_count++;
								$products_opt_query = tep_db_query("select products_opt2 from " . TABLE_PRODUCTS . " where products_id = '" . (int)$products_id . "'");
								$products_opt = tep_db_fetch_array($products_opt_query);
								?>
								<td class="product-<?php echo $compare_count; ?>" style="width:<?php echo $product_width; ?>%;">
									<?php
									if (($differ=='') && ($kenmerk!='')) {
										if (('<span class="kenmerk-waarde">'.$products_opt['products_opt2'].'</span>')!=$kenmerk) {
											$differ = true;
										}
									}
									$kenmerk = '<span class="kenmerk-waarde">'.$products_opt['products_opt2'].'</span>';
									echo $kenmerk;
									?>
								</td>
							<?php
							}
						}
						$compare_count=0;
						?>
					</tr>
					<?php
					if ($differ=='true') {
						if (PRODUCT_COMPARE_DIFFERENCES=='true') {
						?>
						<script type="text/javascript">
						/* <![CDATA[ */
						jQuery(document).ready(function(){
							jQuery("#sk_products_opt2").addClass('differ');
						});
						/* ]]> */
						</script>
						<?php
						}
					}
				}
				/*products_opt2*/
				/*products_opt3*/
				if (PRODUCT_COMPARE_OPT3=='true') {
					?>
					<tr class="compare_table_specifications" id="sk_products_opt3">
						<td class="legende" style="width:<?php echo $product_width; ?>%;"><?php echo Translate('Optieveld 3'); ?></td>
						<?php
						$differ='';
						$kenmerk='';
						foreach (explode('_', $cookie_val ) as $products_id) {
							if ($products_id!='') {
								$compare_count++;
								$products_opt_query = tep_db_query("select products_opt3 from " . TABLE_PRODUCTS . " where products_id = '" . (int)$products_id . "'");
								$products_opt = tep_db_fetch_array($products_opt_query);
								?>
								<td class="product-<?php echo $compare_count; ?>" style="width:<?php echo $product_width; ?>%;">
									<?php
									if (($differ=='') && ($kenmerk!='')) {
										if (('<span class="kenmerk-waarde">'.$products_opt['products_opt3'].'</span>')!=$kenmerk) {
											$differ = true;
										}
									}
									$kenmerk = '<span class="kenmerk-waarde">'.$products_opt['products_opt3'].'</span>';
									echo $kenmerk;
									?>
								</td>
							<?php
							}
						}
						$compare_count=0;
						?>
					</tr>
					<?php
					if ($differ=='true') {
						if (PRODUCT_COMPARE_DIFFERENCES=='true') {
						?>
						<script type="text/javascript">
						/* <![CDATA[ */
						jQuery(document).ready(function(){
							jQuery("#sk_products_opt3").addClass('differ');
						});
						/* ]]> */
						</script>
						<?php
						}
					}
				}
				/*products_opt3*/
				/*products_opt4*/
				if (PRODUCT_COMPARE_OPT4=='true') {
					?>
					<tr class="compare_table_specifications" id="sk_products_opt4">
						<td class="legende" style="width:<?php echo $product_width; ?>%;"><?php echo Translate('Optieveld 4'); ?></td>
						<?php
						$differ='';
						$kenmerk='';
						foreach (explode('_', $cookie_val ) as $products_id) {
							if ($products_id!='') {
								$compare_count++;
								$products_opt_query = tep_db_query("select products_opt4 from " . TABLE_PRODUCTS . " where products_id = '" . (int)$products_id . "'");
								$products_opt = tep_db_fetch_array($products_opt_query);
								?>
								<td class="product-<?php echo $compare_count; ?>" style="width:<?php echo $product_width; ?>%;">
									<?php
									if (($differ=='') && ($kenmerk!='')) {
										if (('<span class="kenmerk-waarde">'.$products_opt['products_opt4'].'</span>')!=$kenmerk) {
											$differ = true;
										}
									}
									$kenmerk = '<span class="kenmerk-waarde">'.$products_opt['products_opt4'].'</span>';
									echo $kenmerk;
									?>
								</td>
							<?php
							}
						}
						$compare_count=0;
						?>
					</tr>
					<?php
					if ($differ=='true') {
						if (PRODUCT_COMPARE_DIFFERENCES=='true') {
						?>
						<script type="text/javascript">
						/* <![CDATA[ */
						jQuery(document).ready(function(){
							jQuery("#sk_products_opt4").addClass('differ');
						});
						/* ]]> */
						</script>
						<?php
						}
					}
				}
				/*products_opt4*/
				/*products_opt5*/
				if (PRODUCT_COMPARE_OPT5=='true') {
					?>
					<tr class="compare_table_specifications" id="sk_products_opt5">
						<td class="legende" style="width:<?php echo $product_width; ?>%;"><?php echo Translate('Optieveld 5'); ?></td>
						<?php
						$differ='';
						$kenmerk='';
						foreach (explode('_', $cookie_val ) as $products_id) {
							if ($products_id!='') {
								$compare_count++;
								$products_opt_query = tep_db_query("select products_opt5 from " . TABLE_PRODUCTS . " where products_id = '" . (int)$products_id . "'");
								$products_opt = tep_db_fetch_array($products_opt_query);
								?>
								<td class="product-<?php echo $compare_count; ?>" style="width:<?php echo $product_width; ?>%;">
									<?php
									if (($differ=='') && ($kenmerk!='')) {
										if (('<span class="kenmerk-waarde">'.$products_opt['products_opt5'].'</span>')!=$kenmerk) {
											$differ = true;
										}
									}
									$kenmerk = '<span class="kenmerk-waarde">'.$products_opt['products_opt5'].'</span>';
									echo $kenmerk;
									?>
								</td>
							<?php
							}
						}
						$compare_count=0;
						?>
					</tr>
					<?php
					if ($differ=='true') {
						if (PRODUCT_COMPARE_DIFFERENCES=='true') {
						?>
						<script type="text/javascript">
						/* <![CDATA[ */
						jQuery(document).ready(function(){
							jQuery("#sk_products_opt5").addClass('differ');
						});
						/* ]]> */
						</script>
						<?php
						}
					}
				}
				/*products_opt5*/
				
					/*specifications*/
					$productspecs_query = tep_db_query("SELECT * FROM productspecs WHERE products_model = '".$product_info['products_model']."' order by subkenmerk");
					if (tep_db_num_rows($productspecs_query)>0) {
						while ($productspecs = tep_db_fetch_array($productspecs_query))
						{
							$specifications_query = tep_db_query("SELECT value, title, subkenmerk FROM specifications WHERE hoofdkenmerk = '".$productspecs['hoofdkenmerk']."' AND subkenmerk = '".$productspecs['subkenmerk']."' AND language_id = '".(int)$languages_id."' order by subkenmerk");
							while ($specifications = tep_db_fetch_array($specifications_query))
							{
								if ($count<1) {
									
									
								}
									
								if (SHOW_SPECS=='true') {
									if ($specifications['title'] != 1)
									{
										$count++;
										$differ='';
										$kenmerk='';
										?>
										<tr class="compare_table_specifications" id="sk_<?php echo $productspecs['subkenmerk']; ?>">
											<td class="legende" style="width:<?php echo $product_width; ?>%;"><?php echo $specifications['value']; ?></td>
											<?php
											foreach (explode('_', $cookie_val ) as $products_id) {
												if ($products_id!='') {
													$compare_count++;
													?>
													<td class="product-<?php echo $compare_count; ?>" style="width:<?php echo $product_width; ?>%;">
														<?php 
														$display_class='list';
														if (($differ=='') && ($kenmerk!='')) {
															if (ShowSpecificationIcons(show_product_spec($products_id, $productspecs['hoofdkenmerk'], $productspecs['subkenmerk'], $display_class))!=$kenmerk) {
																$differ = true;
															}
														}
														$kenmerk = ShowSpecificationIcons(show_product_spec($products_id, $productspecs['hoofdkenmerk'], $productspecs['subkenmerk'], $display_class));
														echo $kenmerk;
														?>
													</td>
												<?php
												}
											}
											if ($compare_count==$total_products) {
												?>
												</tr>
												<?php
												if ($differ=='true') {
													if (PRODUCT_COMPARE_DIFFERENCES=='true') {
													?>
													<script type="text/javascript">
													/* <![CDATA[ */
													jQuery(document).ready(function(){
														jQuery("#sk_<?php echo $productspecs['subkenmerk']; ?>").addClass('differ');
													});
													/* ]]> */
													</script>
													<?php
													}
												}
											}
									}
								}
								/*kenmerken*/
								$compare_count=0;
							}
						}
					}
					?>
				</tr>
			</table>
		<?php	
		}
		?>
		<div class="clear"></div>
	</div>
	<script type="text/javascript">
	/* <![CDATA[ */
	jQuery(document).ready(function(){
		jQuery("#compare_close").click(function(){
			jQuery("#compare_window").hide('slow');
			return false;
		});
	});
	/* ]]> */
	</script>
    </div>
<?php
} else {
	function get_products_by_search_values($array_final_products, $string_product_models, $string_search_val, $string_hk, $string_sk) {
		$get_search_product_models = array();
		$get_search_values_product_models_query = tep_db_query('SELECT products_model FROM productspecs WHERE hoofdkenmerk = "'.$string_hk.'" AND subkenmerk = "'.$string_sk.'" AND value IN ('.$string_search_val.') AND products_model IN ('.$string_product_models.')');
		while ($get_search_values_product_models = tep_db_fetch_array($get_search_values_product_models_query)) {
			$get_search_product_models[] = $get_search_values_product_models['products_model'];
		}
		return $get_search_product_models;
	}
	if (PRODUCT_LISTING_MODULE_VIEW == 'grid') {
		$listtype = 'GRID';
		$listtypenot = 'LIST';
	} else {
		$listtype = 'LIST';
		$listtypenot = 'GRID';
	}

	$search_subk = array(); //array for subkenmerken
	$search_prod = array();//array for products
	//get products for this categorie
	$specification_group = $_GET['hk'];
	for ($s=0 ; $s<PRODUCT_LIST_SPECS_COUNT; $s++) {
		if ($_GET['PRODUCT_LIST_SPECS_'.$s]!='') {
			$chosen_spec_column[$s] = $_GET['PRODUCT_LIST_SPECS_'.$s];
		}
	}
	$get_cpath = end(explode('_', $_GET['cPath']));
	$product_models = array();
	$get_product_models_query = tep_db_query('SELECT DISTINCT p.products_model FROM products p, products_to_categories ptc WHERE p.products_id = ptc.products_id AND ptc.categories_id = "'.$get_cpath.'"');
	while ($get_product_models = tep_db_fetch_array($get_product_models_query)) {
				$product_models[] = $get_product_models['products_model'];
	}
	$count_products_models = count($product_models);
	$product_models = '"'.implode('","', $product_models).'"';
	//end get products for this categorie
	
	//manufacturers
	//get all the ID's from the manufacturers
	$manufacturers_ids = array();
	foreach ($_GET as $var=>$value) {
		if ($var == 'manufacturers') {
			$manufacturers_ids[] .= $value;
		}
	}
	$strManufacturers="";
	$chkmanufacturers = $_GET['manufacturers'];
	$count=count($_GET['manufacturers']);
	for($i=0;$i<$count;$i++){
		if ($i == 0) {
			$strManufacturers="'$chkmanufacturers[$i]'";
		}
		$strManufacturers="$strManufacturers, '$chkmanufacturers[$i]'";
	}
	//end get all ID's from the manufacturers
	
	//get through all the GET parameters
	$i = 0;
	$first_time_options = 0;
	foreach ($_GET as $var=>$value) {
		$productspecs_prod_temp = array();
		if (($var == 'hk') || ($var == 'search') || ($var == 'x') || ($var == 'y') || ($var == 'Stock') || ($var == 'name') || ($var == 'price_from') || ($var == 'price_to') || ($var == 'sort') || ($var == 'ppp') || ($var == 'page') || (substr($var, 0, 7) == 'columns')) {
			// do nothing
		} else {
			if ($var == 'cPath') {
				if (strstr($_GET['cPath'], '_')) {
					$cpath = end(explode('_', $_GET['cPath']));
				} else {
					$cpath = $_GET['cPath'];
				}
				$products_from_categorie_query = tep_db_query('SELECT p.products_model FROM products p, products_to_categories p2c WHERE p.products_id = p2c.products_id AND p2c.categories_id = "'.$cpath.'"');
				while ($products_from_categorie = tep_db_fetch_array($products_from_categorie_query)) {
					$productspecs_prod_temp[] .= $products_from_categorie['products_model'];
				}
				$productspecs_prod = $productspecs_prod_temp;
				$m_models = count($productspecs_prod);
				$show_final_products = $productspecs_prod;
				$productspecs_prod = '"'.implode('","', $productspecs_prod).'"';
			} else if ($var == 'manufacturers') {
				$products_productspecs_query_select = sprintf('SELECT DISTINCT `products_model` FROM `products` WHERE manufacturers_id IN ('.$strManufacturers.') AND products_model IN ('.$productspecs_prod.')');
				$products_productspecs_query = tep_db_query($products_productspecs_query_select);
				while ($products_productspecs = tep_db_fetch_array($products_productspecs_query)) {
					$productspecs_prod_temp[] .= $products_productspecs['products_model'];
				}
				$productspecs_prod = $productspecs_prod_temp;
				$m_models = count($productspecs_prod);
				$show_final_products = $productspecs_prod;
				$productspecs_prod = '"'.implode('","', $productspecs_prod).'"';
			} else if (is_array($value)) {
				if ($first_time_options == 0) {
					$productspecs_prod_temp = array();
					$products_from_hoofdkenmerk_query = tep_db_query('SELECT DISTINCT products_model FROM productspecs WHERE hoofdkenmerk = "'.$_GET['hk'].'" AND products_model IN ('.$productspecs_prod.')');
					while ($products_from_hoofdkenmerk = tep_db_fetch_array($products_from_hoofdkenmerk_query)) {
						$productspecs_prod_temp[] .= $products_from_hoofdkenmerk['products_model'];
					}
					$productspecs_prod = $productspecs_prod_temp;
					$m_models = count($productspecs_prod);
					$show_final_products = $productspecs_prod;
					$productspecs_prod = '"'.implode('","', $productspecs_prod).'"';
					$first_time_options++;
				}
				
				$strValues = '';
				foreach ($value as $key=>$param){
					$strValues .= '*'.$param;
				}
				$products_productspecs_query_select = sprintf('SELECT DISTINCT `products_model` FROM `productspecs` WHERE subkenmerk = "'.$var.'" AND products_model IN ('.$productspecs_prod.')');
				$products_productspecs_query = tep_db_query($products_productspecs_query_select);
				while ($products_productspecs = tep_db_fetch_array($products_productspecs_query)) {
					$productspecs_prod_temp[] .= $products_productspecs['products_model'];
				}
				$productspecs_prod = $productspecs_prod_temp;
				$m_models = count($productspecs_prod);
				$show_final_products = $productspecs_prod;
				$productspecs_prod = '"'.implode('","', $productspecs_prod).'"';
				$values = explode('*', $strValues);
				$search_values = '';
				foreach ($values as $key=>$value) {
					if (empty($value)){	
					} else {
						$split_value = explode('*', $value);
						foreach ($split_value as $key2=>$value2) {
							$search_values .= '"'.$value2.'",';
						}
					}
				}
				$search_values = substr($search_values, 0, -1);
				$get_new_product_models = get_products_by_search_values($show_final_products, $productspecs_prod, $search_values, $_GET['hk'], $var);
				$show_final_products = $get_new_product_models;
				$productspecs_prod = '"'.implode('","', $get_new_product_models).'"';			
			} else if (strstr($var, '_from') || strstr($var, '_to')) {
				if (strstr($var, '_from')) {
					$to_values = explode('*', $_GET[str_replace('_from', '_to', $var)]);
					$to_value = $to_values[1];
					$from_values = explode($to_value, $value);
					$from_value = $from_values[0];
					$all_values = explode('*', $from_value.$to_value);
					unset($all_values[0]);
					$all_values = '"'.implode('", "', $all_values).'"';
					$get_new_product_models = get_products_by_search_values('', $productspecs_prod, $all_values, $_GET['hk'], str_replace('_from', '', $var));
					$show_final_products = $get_new_product_models;
					$productspecs_prod = '"'.implode('","', $get_new_product_models).'"';
				}
			}
			unset($_GET[$var]);
		}
	}
	unset($_GET['hk']);
	
	//end get through all GET parameters
	if ((isset($_GET['price_from'])) && (isset($_GET['price_to']))) {
		$from_price = $_GET['price_from'];
		$from_price = substr($from_price, 0, -1);
		$to_price = $_GET['price_to'];	
		$to_price = substr($to_price, 0, -1);	
		//CHECK FOR DISCOUNT PRICES
		$productspecs_prod_temp = array();
		$productspecs_prod_discount_temp = array();
		if (PRICE_BOOK == 'true') {
			$check_discount_query = tep_db_query('SELECT DISTINCT products_id, products_price, manufacturers_id, products_model FROM products WHERE products_model IN ('.$productspecs_prod.')');
			while ($check_discount = tep_db_fetch_array($check_discount_query)) {
				$discount_price = tep_get_discountprice($check_discount['products_price'], $customer_id, $customer_group, $check_discount['products_id'], $cPath, $check_discount['manufacturers_id']);
				if ($discount_price['lowest']['price'] > $from_price && $discount_price['lowest']['price'] < $to_price) {
					$productspecs_prod_temp[] .= $check_discount['products_model'];
				} else {
					$productspecs_prod_discount_temp[] .= $check_discount['products_model'];
				}
			}
			$productspecs_prod = '"'.implode('","', $productspecs_prod_discount_temp).'"';
		}
		//END CHECK FOR DISCOUNT PRICES				
		$productspecs_values_query = tep_db_query('SELECT DISTINCT p.products_model FROM products p LEFT JOIN specials s ON p.products_id = s.products_id WHERE (IF(s.status, s.specials_new_products_price, p.products_price) BETWEEN "'.$from_price.'" AND "'.$to_price.'") AND p.products_model IN ('.$productspecs_prod.')');
		while ($productspecs_values = tep_db_fetch_array($productspecs_values_query)) {
			$productspecs_prod_temp[] .= $productspecs_values['products_model'];
		}
		$productspecs_prod = $productspecs_prod_temp;
		$show_final_products = $productspecs_prod;
		$productspecs_prod = '"'.implode('","', $productspecs_prod).'"';
		unset($_GET['price_from']);
		unset($_GET['price_to']);
	}
		$list_config_query = tep_db_query("SELECT configuration_key, configuration_value FROM configuration WHERE configuration_group_id = '19' AND configuration_value <> 0 AND configuration_key LIKE '%".$listtype."%' ORDER BY configuration_value");
		if (PRODUCT_LIST_SPECS_COUNT>0) {
			$total_columns = tep_db_num_rows($list_config_query)+(PRODUCT_LIST_SPECS_COUNT-1);
		} else {
			$total_columns = tep_db_num_rows($list_config_query);
		}
		if ($total_columns > 0) {
			$define_list = array();
			$select_column_list = '';
			$listing_sql_suff = '';
			$column_list=0;
			$sort_order = substr($_GET['sort'], 1);
			$sort_col = substr($_GET['sort'], 0 , 1);
			$config_array = array();
			while ($list_config = tep_db_fetch_array($list_config_query)) {
				$column_list++;
				if ($list_config['configuration_key']=='PRODUCT_'.$listtype.'_SPECS') {
					for ($s=0 ; $s<PRODUCT_LIST_SPECS_COUNT; $s++) {
						$config_array[] = array($list_config['configuration_key'], $column_list);
						if ($s<(PRODUCT_LIST_SPECS_COUNT-1)) {
							$column_list++;
						}
					}
				} else {
					$config_array[] = array($list_config['configuration_key'], $column_list);
				}
			}
			$column_list=0;
			foreach ($config_array as $key => $value) {
				if (($value[0] == 'PRODUCT_'.$listtype.'_PRICE') || ($value[0] == 'PRODUCT_'.$listtype.'_BUY_NOW') || ($value[0] == 'PRODUCT_'.$listtype.'_QUANTITY')) {
					if (CanShop() == 'false') {
						$value[1]=0;
					} else if (USE_PRICES_TO_QTY == 'true') {
						$value[1]=0;
					}
				}
				if ($value[1]>0) {
					$column_list++;
					if ($value[0]=='PRODUCT_'.$listtype.'_MODEL') {
						$select_column_list .= 'p.products_model, ';
						$default_sort = 'p.products_model';
						if ($sort_col == $column_list) {
							$listing_sql_suff .= "p.products_model " . ($sort_order == 'd' ? 'desc' : '') . ", pd.products_name";
						}
					} else if ($value[0]=='PRODUCT_'.$listtype.'_NAME') {
						$select_column_list .= 'pd.products_name, ';
						$default_sort = 'pd.products_name';
						if ($sort_col == $column_list) {
							$listing_sql_suff .= "pd.products_name " . ($sort_order == 'd' ? 'desc' : '') . "";
						}
					} else if ($value[0]=='PRODUCT_'.$listtype.'_DESCRIPTION') {
						$default_sort = 'pd.products_description';
						if ($sort_col == $column_list) {
							$listing_sql_suff .= "pd.products_description " . ($sort_order == 'd' ? 'desc' : '') . "";
						}
					} else if ($value[0]=='PRODUCT_'.$listtype.'_TECHNICAL') {
						$select_column_list .= 'pd.products_technical, ';
						$default_sort = 'pd.products_technical';
						if ($sort_col == $column_list) {
							$listing_sql_suff .= "pd.products_technical " . ($sort_order == 'd' ? 'desc' : '') . "";
						}
					} else if ($value[0]=='PRODUCT_'.$listtype.'_MANUFACTURER') {
						$select_column_list .= 'm.manufacturers_name, ';
						$default_sort = 'm.manufacturers_name';
						if ($sort_col == $column_list) {
							$listing_sql_suff .= "m.manufacturers_name " . ($sort_order == 'd' ? 'desc' : '') . ", pd.products_name";
						}
					} else if ($value[0]=='PRODUCT_'.$listtype.'_QUANTITY') {
						$select_column_list .= 'p.products_quantity, ';
						$default_sort = 'p.products_quantity';
						if ($sort_col == $column_list) {
							$listing_sql_suff .= "p.products_quantity " . ($sort_order == 'd' ? 'desc' : '');
						}
					} else if ($value[0]=='PRODUCT_'.$listtype.'_IMAGE') {
						$select_column_list .= 'p.products_image, ';
						$default_sort = 'p.products_image';
						if ($sort_col == $column_list) {
							$listing_sql_suff .= "p.products_image desc";
						}
					} else if ($value[0]=='PRODUCT_'.$listtype.'_WEIGHT') {
						$select_column_list .= 'p.products_weight, ';
						$default_sort = 'p.products_weight';
						if ($sort_col == $column_list) {
							$listing_sql_suff .= "p.products_weight " . ($sort_order == 'd' ? 'desc' : '');
						}
					} else if ($value[0]=='PRODUCT_'.$listtype.'_PRICE') {
						$default_sort = 'p.products_price';
						if ($sort_col == $column_list) {
							$listing_sql_suff .= "p.products_price " . ($sort_order == 'd' ? 'desc' : '');
						}
					} else if ($value[0]=='PRODUCT_'.$listtype.'_OPT1') {
						$select_column_list .= 'p.products_opt1, ';
						$default_sort = 'p.products_opt1';
						if ($sort_col == $column_list) {
							$listing_sql_suff .= "p.products_opt1 " . ($sort_order == 'd' ? 'desc' : '');
						}
					} else if ($value[0]=='PRODUCT_'.$listtype.'_OPT2') {
						$select_column_list .= 'p.products_opt2, ';
						$default_sort = 'p.products_opt2';
						if ($sort_col == $column_list) {
							$listing_sql_suff .= "p.products_opt2 " . ($sort_order == 'd' ? 'desc' : '');
						}
					} else if ($value[0]=='PRODUCT_'.$listtype.'_OPT3') {
						$select_column_list .= 'p.products_opt3, ';
						$default_sort = 'p.products_opt3';
						if ($sort_col == $column_list) {
							$listing_sql_suff .= "p.products_opt3 " . ($sort_order == 'd' ? 'desc' : '');
						}
					} else if ($value[0]=='PRODUCT_'.$listtype.'_OPT4') {
						$select_column_list .= 'p.products_opt4, ';
						$default_sort = 'p.products_opt4';
						if ($sort_col == $column_list) {
							$listing_sql_suff .= "p.products_opt4 " . ($sort_order == 'd' ? 'desc' : '');
						}
					} else if ($value[0]=='PRODUCT_'.$listtype.'_OPT5') {
						$select_column_list .= 'p.products_opt5, ';
						$default_sort = 'p.products_opt5';
						if ($sort_col == $column_list) {
							$listing_sql_suff .= "p.products_opt5 " . ($sort_order == 'd' ? 'desc' : '');
						}
					} else if ($value[0]=='PRODUCT_'.$listtype.'_SPECS') {
						
						//mxl sd.value
						$default_sort = 'final_price';
						if ($sort_col == $column_list) {
							$listing_sql_suff .= "final_price " . ($sort_order == 'd' ? 'desc' : '');
						}
					} 
					$product_list_default_sort = str_replace($listtypenot, $listtype, PRODUCT_LIST_DEFAULT_SORT);
					if ($product_list_default_sort==$value[0]) {
						$default_sort_order = $default_sort;
					}
				}
			}
		}
		/*PRODUCT LIST SPECS*/
		if (PRODUCT_LIST_SPECS!=0) {
			$col_id = $sort_col-PRODUCT_LIST_SPECS;
			if (($sort_col!='') && ($chosen_spec_column[$col_id]!='')) {
				if ($col>(PRODUCT_LIST_SPECS-1)) {
					$col_id = 0;
				}
				$search_subkenmerk = $chosen_spec_column[$col_id];
			} else {
				$search_subkenmerk = $chosen_spec_column[0];
			}
			if ($search_subkenmerk!='') {
				$specs_tables = " left join productspecs ps on p.products_model = ps.products_model left join specsdescription sd on ps.value = sd.subkenmerk";
				$specs_vwd = " and ps.subkenmerk = '".$search_subkenmerk."' and ps.hoofdkenmerk = '".$specification_group."' and sd.language_id = '".(int)$languages_id."'";
			}
		} else {
			$specs_tables = "";
			$specs_vwd = "";
		}
		/*PRODUCT LIST SPECS*/
		$listing_sql = '';
		$listing_sql = "SELECT DISTINCT ".$select_column_list." p.products_price, p.products_id, s.specials_new_products_price  FROM ".TABLE_PRODUCTS." p  JOIN ".TABLE_PRODUCTS_DESCRIPTION." pd USING (products_id) LEFT JOIN " . TABLE_SPECIALS . " s ON (p.products_id = s.products_id) LEFT JOIN ".TABLE_MANUFACTURERS." m ON (p.manufacturers_id = m.manufacturers_id)".$specs_tables." JOIN ".TABLE_PRODUCTS_TO_CATEGORIES." p2c ON (p.products_id = p2c.products_id) WHERE p.products_status = '1' AND pd.language_id = '" . (int)$languages_id . "' and p2c.categories_id = '".(int)$current_category_id."'".$specs_vwd.$where_str.$order_str;
	
	$where_str = "";
	
	if (isset($show_final_products) && (sizeof($show_final_products) > 0)) {
	  $where_str .= " AND ";
	  for ($i=0, $n=sizeof($show_final_products); $i<$n; $i++ ) {
		switch ($show_final_products[$i]) {
		  case '(':
		  case ')':
		  case 'and':
		  case 'or':
			$where_str .= " " . $show_final_products[$i] . " ";
			break;
		  default:
			$keyword = tep_db_prepare_input($show_final_products[$i]);
			$where_str .= "(p.products_model = '" . tep_db_input($keyword) . "'";
			if ($i<( $n - 1)) {
				$where_str .= ') OR ';
			} else {
				$where_str .= ')';
			}
			break;
		}
	  }
	  $where_str .= "";
	}
	if (strlen($productspecs_prod)>1) {
	$where_str = ' AND p.products_model IN ('.$productspecs_prod.')';
	} else {
	$where_str = '';
	}
	$order_str = '';
	$listing_sql .= $where_str.$order_str;
	if ( (!isset($_GET['sort'])) || (!preg_match('/^[1-8][ad]$/', $_GET['sort'])) || (substr($_GET['sort'], 0, 1) > $total_columns) ) {
		$listing_sql .= " order by ".$default_sort_order;
	} else {
		$listing_sql .= " order by ".$listing_sql_suff;
	}
	$_GET['cPath'] = $get_cpath;
	$display_class = PRODUCT_LISTING_MODULE_VIEW;
	$listing_type = 'product_listing';
	include(DIR_WS_MODULES . FILENAME_PRODUCT_LISTING);
}
echo iconv('ISO-8859-1', 'UTF-8', ob_get_clean());
?>