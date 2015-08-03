<?php
$display_class = NEW_PRODUCTS_MODULE_VIEW;
$listing_type = 'new_products';
$listing_sql = "select p.products_id, p.products_image, p.products_price, s.specials_new_products_price, p.products_tax_class_id, p.products_quantity, pd.products_name from " . TABLE_PRODUCTS . " p left join ".TABLE_PRODUCTS_DESCRIPTION." pd on p.products_id = pd.products_id left join " . TABLE_SPECIALS . " s on p.products_id = s.products_id, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c, " . TABLE_CATEGORIES . " c where p.products_id = p2c.products_id and p2c.categories_id = c.categories_id and c.parent_id = '" . $new_products_category_id . "' and pd.language_id = '" . (int)$languages_id . "' order by p.products_date_added desc ";
$results_limit = MAX_DISPLAY_NEW_PRODUCTS_MODULE;
include(DIR_WS_MODULES . FILENAME_PRODUCT_LISTING);
?>
