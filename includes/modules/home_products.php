<?php
$display_class = HOME_PRODUCTS_MODULE_VIEW;
$listing_type = 'home_products';
$listing_sql = "select p.products_id, p.products_image, p.products_price, s.specials_new_products_price, p.products_tax_class_id, p.products_quantity, pd.products_name from " . TABLE_PRODUCTS . " p left join ".TABLE_PRODUCTS_DESCRIPTION." pd on p.products_id = pd.products_id left join " . TABLE_SPECIALS . " s on p.products_id = s.products_id where p.products_opt5 = 'A' and pd.language_id = '" . (int)$languages_id . "' order by p.products_date_added desc";
$results_limit = MAX_DISPLAY_HOME_PRODUCTS_MODULE;
include(DIR_WS_MODULES . FILENAME_PRODUCT_LISTING);
?>