<?php
/**
 * Created by PhpStorm.
 * User: Cristian
 * Date: 8/4/2015
 * Time: 5:14 PM
 */
$lang = getSiteLanguage();
$currencies = new currencies();

$query = "select p.products_id, p.products_image, p.products_price, s.specials_new_products_price, p.products_tax_class_id, p.products_quantity, pd.products_name from " . TABLE_PRODUCTS . " p left join ".TABLE_PRODUCTS_DESCRIPTION." pd on p.products_id = pd.products_id left join " . TABLE_SPECIALS . " s on p.products_id = s.products_id where p.products_opt5 = 'A' and pd.language_id = '" . $lang['id'] . "' order by p.products_date_added desc";
$data = queryToArray($query);
$columns = 12 / count($data);
?>
<div class="col-xs-12">
	<div class="panel panel-info">
		<div class="panel-heading"><?php echo Translate('Nieuw in ons Assortiment'); ?></div>
		<div class="panel-body">
			<?php for($i=0; $i<count($data); $i++){ ?>
				<div class="col-xs-<?php echo $columns ?> text-center">
					<a href="<?php echo tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $data[$i]["products_id"]) ?>">
						<img src="<?php echo DIR_WS_IMAGES . $data[$i]['products_image']?>" class="img-responsive img-thumbnail" alt="<?php echo $data[$i]['products_name']?>">
					</a>
					<a href="<?php echo tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $data[$i]['products_id']); ?>">
						<h4><?php echo $data[$i]['products_name'] ?></h4>
					</a>
					<span class="text-center">
						<?php echo $currencies->display_price($data[$i]['products_price'], tep_get_tax_rate($data[$i]['products_tax_class_id'])); ?>
					</span>
				</div>
			<?php } ?>
		</div>
	</div>
</div>