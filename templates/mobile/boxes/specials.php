<?php
$lang = getSiteLanguage();
$currencies = new currencies();

$query = "select p.products_id, pd.products_name, p.products_price, p.products_tax_class_id, p.products_image, s.specials_new_products_price, p.manufacturers_id from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_SPECIALS . " s where p.products_status = '1' and p.products_id = s.products_id and pd.products_id = s.products_id and pd.language_id = '".$lang['id']."' and s.status = '1' order by s.specials_date_added desc limit " . MAX_RANDOM_SELECT_SPECIALS;
if ($random_product = tep_random_select($query)) {
	if (USE_PRICES_TO_QTY == 'false' && PRICE_BOOK == 'true') { //added here so this can be used add the whole page
		$discount_price = tep_get_discountprice($random_product['products_price'], $customer_id, $customer_group, $random_product['products_id'], $cPath, $random_product['manufacturers_id']);
	}
	$oldPrice = 0;
	$newPrice = 0;

	if ($discount_price['lowest']['discount'] > 0 && PRICE_BOOK == 'true') {
		if ($new_price = tep_get_products_special_price($random_product['products_id'])) {
			if ($new_price < $discount_price['lowest']['price']) {
				$oldPrice = $currencies->display_price($random_product['products_price'], tep_get_tax_rate($random_product['products_tax_class_id']));
				$newPrice = $currencies->display_price($new_price, tep_get_tax_rate($random_product['products_tax_class_id']));
			} else {
				$oldPrice = $currencies->display_price($random_product['products_price'], tep_get_tax_rate($random_product['products_tax_class_id']));
				$newPrice = $currencies->display_price($discount_price['lowest']['price'], tep_get_tax_rate($random_product['products_tax_class_id']));
			}
		} else {
			$oldPrice = $currencies->display_price($random_product['products_price'], tep_get_tax_rate($random_product['products_tax_class_id']));
			$newPrice = $currencies->display_price($discount_price['lowest']['price'], tep_get_tax_rate($random_product['products_tax_class_id']));
		}
	} else {
		if ($new_price = tep_get_products_special_price($random_product['products_id'])) {
			$oldPrice = $currencies->display_price($random_product['products_price'], tep_get_tax_rate($random_product['products_tax_class_id']));
			$newPrice = $currencies->display_price($new_price, tep_get_tax_rate($product_info['products_tax_class_id']));
		} else {
			$newPrice = $currencies->display_price($random_product['products_price'], tep_get_tax_rate($random_product['products_tax_class_id']));
		}
	}
?>
	<!-- Special promo product -->
	<div class="col-xs-12">
		<div class="panel panel-info">
			<div class="panel-heading"><?php echo '<a href="'.tep_href_link(FILENAME_SPECIALS).'">'.Translate('Speciale aanbiedingen').'</a>'; ?></div>
			<div class="panel-body">
				<div class="row">
					<div class="col-xs-6 col-sm-12 text-center">
						<a href="<?php echo tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $random_product["products_id"]) ?>">
							<img src="<?php echo DIR_WS_IMAGES . $random_product['products_image']?>" class="img-responsive img-thumbnail" alt="<?php echo $random_product['products_name']?>">
						</a>
					</div>
					<div class="col-xs-6 col-sm-12 text-center">
						<a href="<?php echo tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $random_product['products_id']); ?>">
							<h4><?php echo $random_product['products_name'] ?></h4>
						</a>
						<div class="row">
							<div class="col-xs-6 text-right">
								<del><?php echo $oldPrice; ?></del>
							</div>
							<div class="col-xs-6 text-left">
								<strong class="text-danger"><?php echo $newPrice; ?></strong>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div><?php } ?>