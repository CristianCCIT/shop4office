<?php
require('includes/application_top.php');
$breadcrumb->add(NAVBAR_TITLE, tep_href_link(FILENAME_GET_ACCESSOIRES));
require(DIR_WS_INCLUDES . 'header.php');
require(DIR_WS_INCLUDES . 'column_left.php');
echo tep_draw_form('cart_quantity', tep_href_link(FILENAME_PRODUCT_INFO, tep_get_all_get_params(array('action', 'product_id')).'&products_id='.$_GET['product_id'].'&action=add_multiple_products'));
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td>
			<h1><?php echo Translate('Aanbevolen accessoires');?> :</h1>
		</td>
	</tr>
	<tr>
		<td>
			<?php
			$product_query = tep_db_query('SELECT p.products_id, p.products_model, p.products_image, p.products_price, p.manufacturers_id, pd.products_name, pd.products_description, m.manufacturers_name, p2c.categories_id FROM '.TABLE_PRODUCTS.' p, '.TABLE_PRODUCTS_DESCRIPTION.' pd, '.TABLE_MANUFACTURERS.' m, products_to_categories p2c WHERE p.products_id = pd.products_id AND p.products_id = p2c.products_id AND p.products_id = "'.$_GET['product_id'].'" AND pd.language_id = "'.(int)$languages_id.'" AND p.manufacturers_id = m.manufacturers_id');
			$product = tep_db_fetch_array($product_query);
			$product_name = explode('(', $product['products_name']);
			$product_name = $product_name[0];
			?>
			<table cellspacing="0" cellpadding="0" border="0" width="100%">
				<tr>
					<td align="center" width="120" valign="top">
						<?php echo tep_image(DIR_WS_IMAGES.$product['products_image'], $product['products_name'], 100, 100);?>
					</td>
					<td class="text" align="left" valign="top">
						<h2><?php echo $product['manufacturers_name'].' '.$product_name;?></h2>
						<?php echo get_product_attributes($product['products_id']);?>
					</td>
				</tr>
				<tr>
					<td colspan="2">
						<?php
						echo Translate("Vind de juiste accessoires voor uw product via onderstaande lijst. Indien je klaar bent, klik je op 'Voeg toe aan winkelwagen' om verder te gaan. ");
						echo tep_draw_hidden_field('products_id[]', '_'.$product['products_id']);
						foreach ($_POST as $key=>$value) {
							if ($key != 'products_id') {
								if (is_array($value)) {
									foreach($value as $k=>$v) {
										echo tep_draw_hidden_field($key.'['.$k.']', $v);
									}
								} else {
									echo tep_draw_hidden_field($key, $value);
								}
							}
						}
						?>
					</td>
				</tr>
				<tr>
					<td align="right" valign="bottom" colspan="2">
						<input type="submit" value="Voeg toe aan winkelwagen" class="button-b" />
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td height="10"></td>
	</tr>
	<tr>
		<td><hr class="hr" /></td>
	</tr>
	<tr>
		<td height="15"></td>
	</tr>
  	<tr>
  		<td>
			<?php
			$cross_products = new cross($product['products_model'], $product['categories_id']);
			if (CROSS_PRODUCTS_GROUPED == 'manufacturers') {
				$products_cross_list = $cross_products->manufacturers;
			} else {
				$products_cross_list = $cross_products->categories;
			}
			foreach($products_cross_list as $id=>$cross_info) {
			?>
				<div class="product-cross-group">
					<h4 class="product-cross-group-title">
						<?php echo $cross_info['name'];?>
					</h4>
					<div class="product-cross-group-products">
						<?php
						foreach($cross_info['products'] as $pid=>$cross_product_info) {
						?>
						<div class="product-cross-product">
							<table cellspacing="0" cellpadding="0" border="0" width="100%">
								<tr>
									<td width="90" valign="top">
										<div class="product-cross-image">
											<?php echo tep_image(DIR_WS_IMAGES.$cross_products->products[$cross_product_info]['products_image'], $cross_products->products[$cross_product_info]['products_name'], CROSS_PRODUCT_LIST_IMAGE_WIDTH, CROSS_PRODUCT_LIST_IMAGE_HEIGHT);?>
										</div>
									</td>
									<td valign="top">
										<div class="accessoires_box">
											<div class="accessoires_box_top">
												<div class="accessoires_checkbox">
													<?php
													$this_inCart = false;
													if (isset($cross_products->products[$cross_product_info]['attribute_ids']) && is_array($cross_products->products[$cross_product_info]['attribute_ids'])) {
														foreach($cross_products->products[$cross_product_info]['attribute_ids'] as $attr_id) {
															if ($cart->get_quantity($attr_id) > 0) {
																$this_inCart = true;
															}
														}
													} else {
														if ($cart->get_quantity($cross_product_info) > 0) {
															$this_inCart = true;
														}
													}
													if ($this_inCart) {
														echo Translate('Reeds besteld');
													} else {
														if (count($cross_products->products[$cross_product_info]['attribute_ids']) > 0) {
															if (count($cross_products->products[$cross_product_info]['attribute_ids']) == 1) {
																?>
																<input type="checkbox" value="<?php echo $cross_products->products[$cross_product_info]['attribute_ids'][0];?>" name="products_id[]" />
																<?php
															} else {
																
															}
														} else {
													?>
													<input type="checkbox" value="<?php echo $cross_product_info;?>" name="products_id[]" />
													<?php	
														}
													}
													?>
												</div>
												<div class="accessoires_price">
													<?php
													if (CanShop() == 'true') { 
														if ($cross_products->products[$cross_product_info]['specials_new_products_price'] > 0) {
															echo '<span class="specialprice">'.$currencies->format($cross_products->products[$cross_product_info]['specials_new_products_price']).'</span><br /><span class="oldprice">'.$currencies->format($cross_products->products[$cross_product_info]['products_price']).'</span>';
														} else {
															echo '<span class="yourprice">'.$currencies->format($cross_products->products[$cross_product_info]['products_price']).'</span>';
														}
													}
													?>
												</div>
												<a href="<?php echo tep_href_link(FILENAME_PRODUCT_INFO, 'products_id='.$cross_product_info);?>" title="<?php echo STORE_NAME.' - '.$cross_products->products[$cross_product_info]['products_name'];?>" class="accessoires_title">
													<?php echo $cross_products->products[$cross_product_info]['products_name'];?>
												</a>
												<br />
												<strong><?php echo Translate('Productcode');?>:</strong> <?php echo $cross_products->products[$cross_product_info]['products_model'];?>
											</div>
											<div class="accessoires_middle">
												<?php
												if (strlen($cross_products->products[$cross_product_info]['products_description']) > 50) {
													echo '<div class="short_description">';
													echo shorten_text($cross_products->products[$cross_product_info]['products_description'], 50, 1);
													echo '<div class="show_long_description">'.Translate('Toon meer').'</div>';
													echo '</div>';
													echo '<div class="long_description" style="display:none;">';
													echo $cross_products->products[$cross_product_info]['products_description'];
													echo '<div class="show_short_description">'.Translate('Verberg').'</div>';
													echo '</div>';
												} else {
													echo $cross_products->products[$cross_product_info]['products_description'];
												}
												?>
											</div>
										</div>
									</td>
								</tr>
							</table>
						</div>
						<?php
						}
						?>
					</div>
				</div>
				<?php
			}
			?>
			<script type="text/javascript">
				$('.show_long_description').live('click', function() {
					var parent = $(this).parent('div').parent('div');
					$('.short_description', parent).hide();
					$('.long_description', parent).show();
				});
				$('.show_short_description').live('click', function() {
					var parent = $(this).parent('div').parent('div');
					$('.long_description', parent).hide();
					$('.short_description', parent).show();
				});
			</script>
		</td>
	</tr>
</table>
</form>
<?php
require(DIR_WS_INCLUDES . 'column_right.php');
require(DIR_WS_INCLUDES . 'footer.php');
require(DIR_WS_INCLUDES . 'application_bottom.php');
?>