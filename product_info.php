<?php

require_once('includes/application_top.php');

$product_check_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.products_status = '1' and p.products_id = '" . (int)$_GET['products_id'] . "' and pd.products_id = p.products_id and pd.language_id = '" . (int)$languages_id . "'");

$product_check = tep_db_fetch_array($product_check_query);

require(DIR_WS_INCLUDES . 'header.php');

require(DIR_WS_INCLUDES . 'column_left.php');

?>

<table border="0" width="100%" cellspacing="0" cellpadding="0">

        <tr>

                <td>

                        <?php if (IS_PRINT_FRIENDLY=='1') { ?>

                        <span id="print_date"><?php echo Translate('Afgedrukt op').' '.date('d/m/Y'); ?></span>

                        <?php } ?>

                        <div class="product-info">

                        <?php

                                /*no product found*/

                                if ($product_check['total'] < 1) {

                                ?>

                    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="border: 1px solid #979797; padding:10px; border-radius: 3px 3px 3px 3px;">

                        <tr>

                            <td>

                                <?php echo Translate('Geen product gevonden'); ?>

                            </td>

                        </tr>

                        <tr>

                            <td>&nbsp;



                            </td>

                        </tr>

                        <tr>

                            <td>

                                <a href="<?php echo tep_href_link(FILENAME_DEFAULT); ?>" class="button-a"><?php echo Translate('Ga verder'); ?></a>

                            </td>

                        </tr>

                    </table>

                                <?php

                                } else {

                                        /*product query*/

                                        $product_info_query = tep_db_query("select p.products_id, pd.products_name, pd.products_description, p.products_model, p.products_quantity, p.products_image, pd.products_url, p.products_price, p.products_tax_class_id, p.products_date_added, p.products_date_available, p.manufacturers_id, pd.products_technical, p.products_image_1, p.products_image_2, p.products_image_3, p.products_image_4, p.products_opt1, p.products_opt2, p.products_opt3, p.products_opt4, p.products_opt5 from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.products_status = '1' and p.products_id = '" . (int)$_GET['products_id'] . "' and pd.products_id = p.products_id and pd.language_id = '" . (int)$languages_id . "'");

                                        $product_info = tep_db_fetch_array($product_info_query);

                                        /*Cross selling*/

                                        if (CROSS_SELLING == 'true') {

                                                if (!is_object($cross_products)) {

                                                        $cross_products = new cross($product_info['products_model'], $current_category);

                                                }

                                        }

                                        /*EOF Cross selling*/

                                        if (USE_PRICES_TO_QTY == 'false') {

                                                /*Cross selling*/

                                                if (CROSS_SELLING == 'true' && CROSS_SELLING_AFTER_ADD_TO_CART == 'true' && (count($cross_products->products) > 0)) {

                                                        echo tep_draw_form('cart_quantity', tep_href_link('accessoires.php', 'product_id='.$_GET['products_id']));

                                                } else {

                                                        echo tep_draw_form('cart_quantity', tep_href_link(FILENAME_PRODUCT_INFO, tep_get_all_get_params(array('action', 'block')) . 'action=add_product'));

                                                }

                                                /*EOF Cross selling*/

                                        } else {

                                                echo tep_draw_form('cart_quantity', tep_href_link(FILENAME_PRODUCT_INFO, tep_get_all_get_params(array('action', 'block')) . 'action=update_product'));

                                        }

                                        /*language fallback*/

                                        if (LANGUAGE_FALLBACK=='true') {

                                                $language_fallback_query = tep_db_query("select products_name, products_description, products_technical from " . TABLE_PRODUCTS_DESCRIPTION . " where products_id = '" . (int)$_GET['products_id'] . "' and language_id = '1'");

                                                $language_fallback = tep_db_fetch_array($language_fallback_query);

                                                if ($product_info['products_name']=='') {

                                                        $product_info['products_name'] = $language_fallback['products_name'];

                                                }

                                                if ($product_info['products_description']=='') {

                                                        $product_info['products_description'] = $language_fallback['products_description'];

                                                }

                                                if ($product_info['products_technical']=='') {

                                                        $product_info['products_technical'] = $language_fallback['products_technical'];

                                                }

                                        }

                                        /*language fallback*/

                                        $manufacturer_query = tep_db_query("select manufacturers_name from " . TABLE_MANUFACTURERS . " where manufacturers_id = '" . $product_info['manufacturers_id'] . "'");

                                        $manufacturer = tep_db_fetch_array($manufacturer_query);

                                        tep_db_query("update " . TABLE_PRODUCTS_DESCRIPTION . " set products_viewed = products_viewed+1 where products_id = '" . (int)$_GET['products_id'] . "' and language_id = '" . (int)$languages_id . "'");

                                        $categories_query = tep_db_query("select categories_id from " . TABLE_PRODUCTS_TO_CATEGORIES . " where products_id = '".(int)$_GET['products_id']."'");

                                        $categories = tep_db_fetch_array($categories_query);

                                        $current_category = $categories['categories_id'];

                                        //DISCOUNT

                                        if (USE_PRICES_TO_QTY == 'false' && PRICE_BOOK == 'true') { //added here so this can be used add the whole page

                                                $discount_price = tep_get_discountprice($product_info['products_price'], $customer_id, $customer_group, $product_info['products_id'], $cPath, $product_info['manufacturers_id']);

                                                if (PRICE_BOOK_STAR_PRODUCT_INFO == 'true') {

                                                        if (($discount_price['lowest']['discount'] > 0 || strstr($discount_price['lowest']['discount'], '%')) && (tep_get_products_special_price($product_info['products_id']) > $discount_price['lowest']['price'] || !tep_get_products_special_price($product_info['products_id']))) {

                                                                $discount = $discount_price['lowest']['discount'];

                                                        } else {

                                                                $discount = false;

                                                        }

                                                } else {

                                                        $discount = false;

                                                }

                                        }

                                        //END DISCOUNT

                                        /*breadcrumbs*/

                                        if (IS_PRINT_FRIENDLY!='1') {

                                        ?>

                                                <div class="breadCrumbHolder module">

                                                        <div id="breadCrumbs" class="breadCrumb module">$breadcrumbs$</div>

                                                </div>

                                                <div class="chevronOverlay main"></div>

                                        <?php

                                        }

                                        /*print button or print date*/

                                        if (IS_PRINT_FRIENDLY=='1') {

                                        ?>

                                                <div class="product-printfriendly-details">

                                                        <a href="javascript:window.print();" class="productFile print" id="print_link"><?php echo Translate('Afdrukken'); ?></a>

                                                </div>

                                        <?php

                                        }

                                        /*compare window*/

                                        if (PRODUCT_COMPARE=='true') { ?>

                                        <div id="compare_window">

                                        </div>

                                        <?php

                                        }

                                        /*image gallery*/

                                        if (PRODUCT_INFO_GALLERY!='false') {

                                        ?>

                                        <div class="product-gallery">

                                                <?php

                                                echo tep_get_product_stars($product_info['products_id'], 'detail', $discount);

                                                if (!(file_exists(DIR_WS_IMAGES.$product_info['products_image']))) {

                                                        $product_info['products_image'] = 'foto/no_image.jpg';

                                                }

                                                $images = Array();

                                                for ($i = 0; $i < 5; $i++) {

                                                        $postfix = ($i > 0) ? '_'.$i : '';

                                                        if ($product_info['products_image'.$postfix])$images[] = $product_info['products_image'.$postfix];

                                                }

                                                if (sizeof($images) > 0) {

                                                        if (IS_PRINT_FRIENDLY=='1') {

                                                                echo tep_image(DIR_WS_IMAGES.$product_info['products_image'], MEDIUM_IMAGE_WIDTH, MEDIUM_IMAGE_HEIGHT);

                                                        } else {

                                                                ?>

                                                                <div id="image">

                                                                        <a href="<?php echo DIR_WS_IMAGES.$product_info['products_image'];?>" class="medium_image">

                                                                                <?php echo tep_image(DIR_WS_IMAGES.$product_info['products_image'], '', MEDIUM_IMAGE_WIDTH, MEDIUM_IMAGE_HEIGHT);?>

                                                                        </a>

                                                                </div>

                                                                <div id="thumbnails">

                                                                        <?php

                                                                        if ($product_info['products_image_1']!='') {

                                                                                for ($i = 0; $i < sizeof($images); $i++) {

                                                                                        echo '<a href="'.DIR_WS_IMAGES.$images[$i].'" class="thumb-'.$i.'" rel="product_images">'.tep_image(DIR_WS_IMAGES.$images[$i], '', MEDIUM_THUMBNAIL_WIDTH, MEDIUM_THUMBNAIL_HEIGHT, 'class="thumbnail"').'</a>';

                                                                                }

                                                                        }

                                                                        ?>

                                                                </div>

                                                                <script type="text/javascript">

                                                                var info_images = new Array(<?php echo sizeof($images); ?>);

                                                                var popup_images = new Array(<?php echo sizeof($images); ?>);

                                                                var pos = 0;

                                                                <?php

                                                                for ($i = 0; $i < sizeof($images); $i++)

                                                                {

                                                                        list($w, $h, $src) = tep_image(DIR_WS_IMAGES.$images[$i], '', MEDIUM_IMAGE_WIDTH, MEDIUM_IMAGE_HEIGHT, true, 'border=0');

                                                                        $src2 = DIR_WS_IMAGES.$images[$i];

                                                                        echo "info_images[$i] = new Image();\n"."info_images[$i].src='".$src."'; info_images[$i].width = ".$w."; info_images[$i].height = ".$h.";\n"."info_images[$i].src2='".$src2."';\n";

                                                                }

                                                                ?>

                                                                $(document).ready(function(){

                                                                        if ($("#thumbnails").find('a').length > 0) {

                                                                                $("a[rel='product_images']").colorbox({rel:'product_images'});

                                                                                //$("#image a").append(info_images[0]).attr('href', info_images[0].src2);

                                                                                $("#thumbnails img.thumbnail").mouseover(function(){

                                                                                        pos = $("#thumbnails img.thumbnail").index(this);

                                                                                        $("#image a").html(info_images[pos]).attr('href', info_images[pos].src2);

                                                                                });

                                                                                $('.medium_image').click(function() {

                                                                                        var current = $(this).attr('href');

                                                                                                $('#thumbnails').find('a').each(function() {

                                                                                                        if ($(this).attr('href') == current) {

                                                                                                                $(this).colorbox({rel: 'product_images', open: true});

                                                                                                        }

                                                                                                });

                                                                                        return false;

                                                                                });

                                                                        } else {

                                                                                //$("#image a").append(info_images[0]).attr('href', info_images[0].src2);

                                                                                $('a.medium_image').colorbox();

                                                                        }

                                                                });

                                                                </script>

                                                        <?php

                                                        }

                                                }

                                                ?>

                                        </div>

                                        <?php

                                        }

                                        ?>

                                        <div class="product-name">

                                                <h2><?php echo $product_info['products_name'];?></h2>

                                        </div>

                                        <?php

                                        /*quantity*/

                                        if (PRODUCT_INFO_QUANTITY!='false') {

                                        ?>

                                        <div class="product-quantity">

                                                <?php

                                                echo Translate('Actuele status').': ';

                                                if ($product_info['products_quantity'] < 1) {

                                                        ?>

                                                        <span class="unavailable"><?php echo Translate('tijdelijk uitverkocht'); ?></span>

                                                        <?php

                                                } else {

                                                        ?>

                                                        <span class="available"><?php echo Translate('op voorraad'); ?></span>

                                                        <?php

                                                }

                                                ?>

                                        </div>

                                        <?php

                                        }

                                        if (CanShop() == 'true') {

                                                ?>

                                                <div class="product-canshop">

                                                        <?php

                                                        /*add to cart & hidden product id*/

                                                        if (IS_PRINT_FRIENDLY!='1') {

                                                                /*add to cart*/

                                                               //- original-  if (USE_PRICES_TO_QTY == 'false') {
/* added - && (STOCK_ALLOW_CHECKOUT == 'true') || ($product_info['products_quantity'] >0)){  -  for not allowing checkout if product quantity is < 0 . and STOCK_ALLOW_CHECKOUT = false */

                                                                if((USE_PRICES_TO_QTY == 'false') && (STOCK_ALLOW_CHECKOUT == 'true') || ($product_info['products_quantity'] >0)){

                                                                ?>

                                                                <div class="product-add-to-cart">

                                                                        <input type="submit" class="button-b" value="<?php echo Translate('Voeg toe aan winkelwagen');?>" />

                                                                </div>

                                                                <?php

                                                                }

                                                        }

                                                        /*price*/

                                                        ?>

                                                        <div class="product-price">

                                                                <?php

                                                                if (USE_PRICES_TO_QTY == 'false') {

                                                                        //DISCOUNT

                                                                        if (($discount_price['lowest']['discount'] > 0) || ($discount_price['lowest']['price'] > 0) && PRICE_BOOK == 'true') {

                                                                                if ($new_price = tep_get_products_special_price($product_info['products_id'])) {

                                                                                        if ($new_price < $discount_price['lowest']['price']) {

                                                                                                $products_price = '<span class="oldprice">';

                                                                                                $products_price .= $currencies->display_price($product_info['products_price'], tep_get_tax_rate($product_info['products_tax_class_id']));

                                                                                                $products_price .= '</span>&nbsp;';

                                                                                                $products_price .= '<span class="specialprice">';

                                                                                                $products_price .= $currencies->display_price($new_price, tep_get_tax_rate($product_info['products_tax_class_id']));

                                                                                                $products_price .= '</span>';

                                                                                        } else {

                                                                                                $products_price = '<span class="oldprice">';

                                                                                                $products_price .= $currencies->display_price($product_info['products_price'], tep_get_tax_rate($product_info['products_tax_class_id']));

                                                                                                $products_price .= '</span>&nbsp;';

                                                                                                $products_price .= '<span class="specialprice">';

                                                                                                $products_price .= $currencies->display_price($discount_price['lowest']['price'], tep_get_tax_rate($product_info['products_tax_class_id']));

                                                                                                $products_price .= '</span>';

                                                                                        }

                                                                                } else {

                                                                                        if ($product_info['products_price']!=$discount_price['lowest']['price']) {

                                                                                                $products_price = '<span class="oldprice">';

                                                                                                $products_price .= $currencies->display_price($product_info['products_price'], tep_get_tax_rate($product_info['products_tax_class_id']));

                                                                                                $products_price .= '</span>&nbsp;';

                                                                                                $products_price .= '<span class="specialprice">';

                                                                                                $products_price .= $currencies->display_price($discount_price['lowest']['price'], tep_get_tax_rate($product_info['products_tax_class_id']));

                                                                                                $products_price .= '</span>';

                                                                                        } else {

                                                                                                $products_price .= '<span class="yourprice">';

                                                                                                $products_price .= $currencies->display_price($product_info['products_price'], tep_get_tax_rate($product_info['products_tax_class_id']));

                                                                                                $products_price .= '</span>';

                                                                                        }

                                                                                }

                                                                                if (PRICE_BOOK_OTHER_PRICES_PRODUCT_INFO == 'true') {

                                                                                        $other_prices = '<div class="normalprice">';

                                                                                        foreach ($discount_price['others'] as $prices) {

                                                                                                $other_prices .= Translate('Vanaf').' '.$prices['min_amount'].' '.Translate('producten').': '.$currencies->display_price($prices['price'], tep_get_tax_rate($product_info['products_tax_class_id'])).' ('.$prices['discount'].' '.Translate('korting').')<br />';

                                                                                        }

                                                                                        $other_prices .= '</div>';

                                                                                }

                                                                        } else {

                                                                                if ($new_price = tep_get_products_special_price($product_info['products_id'])) {

                                                                                        $products_price = '<span class="oldprice">' . $currencies->display_price($product_info['products_price'], tep_get_tax_rate($product_info['products_tax_class_id'])) . '</span> <span class="specialprice">' . $currencies->display_price($new_price, tep_get_tax_rate($product_info['products_tax_class_id'])) . '</span>';

                                                                                } else {

                                                                                        $products_price = '<span class="yourprice">' . $currencies->display_price($product_info['products_price'], tep_get_tax_rate($product_info['products_tax_class_id'])).'</span>';

                                                                                }

                                                                        }

                                                                        echo $products_price;

                                                                        echo $other_prices;

                                                                        //END DISCOUNT

                                                                }

                                                                /*attributes*/
                                                                echo get_product_attributes($product_info['products_id']);

                                                        ?>

                                                        </div>

                                                </div>

                                                <?php

                                        }

                                        echo tep_draw_hidden_field('products_id', $product_info['products_id']);

                                        ?>

                                        <div class="product-fields">

                                        <?php

                                        /*merk*/

                                        if (PRODUCT_INFO_MANUFACTURER!='false') {

                                                if ($manufacturer['manufacturers_name']!='') {

                                                        ?>

                                                        <div class="product-manufacturer">

                                                                <strong><?php echo Translate('Fabrikant'); ?>:</strong> <a href="<?php echo tep_href_link(FILENAME_DEFAULT, 'manufacturers_id='.$product_info['manufacturers_id']); ?>"><?php echo $manufacturer['manufacturers_name']; ?></a>

                                                        </div>

                                                        <?php

                                                }

                                        }

                                        /*model*/

                                        if (PRODUCT_INFO_MODEL!='false') {

                                                if ($product_info['products_model']!='') {

                                                        ?>

                                                        <div class="product-model">

                                                                <strong><?php echo Translate('Product nummer'); ?>:</strong> <?php echo $product_info['products_model']; ?>

                                                        </div>

                                                        <?php

                                                }

                                        }

                                        ?>

                                        </div>

                                        <?php

                                        if (IS_PRINT_FRIENDLY!='1') {

                                                /*pdf datasheet*/

                                                if (PRODUCTSHEET_ACTIVE=='true') {

                                                        ?>

                                                        <div class="product-datasheet-link">

                                                                <a href="<?php echo tep_href_link(FILENAME_PRODUCT_DATASHEET, 'products_id=' . $product_info['products_id']); ?>" target="_blank" class="productFile pdf"><?php echo Translate('Productfiche'); ?></a>

                                                        </div>

                                                        <?php

                                                }

                                                /*print friendly*/

                                                if (PRINTFRIENDLY_ACTIVE=='true') {

                                                ?>

                                                        <div class="product-print-link">

                                                        <a href="<?php echo tep_href_link(FILENAME_PRODUCT_PRINT, 'products_id=' . $product_info['products_id']); ?>" onClick="window.open('<?php echo tep_href_link(FILENAME_PRODUCT_PRINT, 'products_id='.$product_info['products_id'], 'NONSSL'); ?>', '<?php echo $product_info['products_name'].' '.Translate('afdrukken.');?>','scrollbars=auto,resizable=yes,status=yes,width=600,height=600'); return false" target="_blank" class="productFile print"><?php echo Translate('Printvriendelijke versie'); ?></a>

                                                        </div>

                                                <?php

                                                }

                                        }

                                        /*Cross selling*/

                                        if (IS_PRINT_FRIENDLY!='1') {

                                                if (CROSS_SELLING == 'true') {

                                                        if (CROSS_SELLING_PAGE == 'true' && (count($cross_products->products) > 0)) {

                                                        ?>

                                                        <div class="product-cross-accessoires">

                                                                <a href="<?php echo tep_href_link('accessoires.php', 'product_id='.$_GET['products_id']); ?>"><?php echo Translate('Accessoires'); ?></a>

                                                        </div>

                                                        <?php

                                                        }

                                                        include(DIR_WS_MODULES.'cross_selling.php');

                                                }

                                        }

                                        /*EOF Cross selling*/


                                        /* Newly added to display social media icons - 26-03-2013 */
                                        if(HEADER_TAGS_DISPLAY_SOCIAL_BOOKMARKS!='false') {
                                            echo '<div class="product-description">';
                                            include(DIR_WS_BOXES.'social_media.php');
                                            echo '</div>';
                                        }



                                        /*description*/
                                        if (PRODUCT_INFO_DESCRIPTION!='false') {
                                        ?>
                                        <div class="product-description">

                                                <?php echo $product_info['products_description']; ?>

                                        </div>

                                        <?php

                                        }

                                        ?>

                                        <div class="clear"></div>

                                        <?php

                                        /*product files*/

                                        if (IS_PRINT_FRIENDLY!='1') {

                                                if (PRODUCT_INFO_FILES!='false') {

                                                ?>

                                                <div class="product-files">

                                                        <?php

                                                        $files = Array();

                                                        $files_query = tep_db_query('SELECT type, link, name FROM products_files WHERE products_model = "'.$product_info['products_model'].'" AND language_id = "'.(int)$languages_id.'"');

                                                        while ($pFiles = tep_db_fetch_array($files_query)) {

                                                                $type = end(explode('.', $pFiles['link']));

                                                                $files[] = array('name' =>$pFiles['name'], 'location' => $pFiles['link'], 'btype' => $type, 'type' => $pFiles['type']);

                                                        }

                                                        $javascript = '';

                                                        $i = 0;

                                                        foreach ($files as $file) {

                                                                if ($file['btype'] == 'pdf') {

                                                                        $javascript .= '$("#'.$file['btype'].'_'.$i.'").colorbox({width: "90%", height: "90%", iframe:true});'."\n";

                                                                        echo '<a href="'.tep_href_link($file['location']).'" id="'.$file['btype'].'_'.$i.'" class="productFile" title="'.$file['type'].' - '.$file['name'].'">'.tep_image(DIR_WS_IMAGES.'type/'.$file['btype'].'.gif', $file['type'].' - '.$file['name'], '75', '75').'<br />'.$file['name'].'</a>'."\n";

                                                                } else {

                                                                        echo '<a href="'.$file['location'].'" id="'.$file['btype'].'_'.$i.'" class="productFile download" title="'.$file['type'].' - '.$file['name'].'">'.tep_image(DIR_WS_IMAGES.'type/'.$file['btype'].'.gif', $file['type'].' - '.$file['name'], '75', '75').'<br />'.$file['name'].'</a>'."\n";

                                                                }

                                                                $i++;

                                                        }

                                                        ?>


                                                </div>

                                                <?php

                                                }

                                        }

                                        /*price table*/

                                        if (USE_PRICES_TO_QTY == 'true' && CanShop() == 'true') {

                                                if (SOAP_STATUS == 'true' && tep_session_is_registered('customer_id')) {

                                                ?>

                                                        <div class="box pricetable">

                                                        <?php echo showPriceTable($product_info['products_id'], $customer_id, '', 1);?>

                                                        </div>

                                                <?php

                                                } else {

                                                        $products_sizes_query = tep_db_query("SELECT * FROM ".TABLE_PRODUCTS_PLANT." WHERE products_model = '".tep_db_input($product_info['products_model'])."' ORDER BY plant_sort ASC");

                                                        $qty_array = getPricesToQty($products_size['plant_price']);

                                                        $products_titles_query = tep_db_query("SELECT * FROM ".TABLE_PRODUCTS_PLANT." WHERE products_model = '".tep_db_input($product_info['products_model'])."' ORDER BY plant_price ASC");

                                                        $products_titles = tep_db_fetch_array($products_titles_query);

                                                        $titles_array = getPricesToQty($products_titles['plant_price']);

                                                        ?>

                                                        <div class="box">

                                                        <table border="0" cellpadding="0" cellspacing="0" class="plantsizes-table" width="100%">

                                                                <tr class="heading">

                                                                        <td class="first">

                                                                                <?php echo Translate('Aantal').tep_draw_hidden_field('products_name_'.$product_info['products_id'], addcslashes(html_entity_decode($product_info['products_name'], ENT_QUOTES), "'")).tep_draw_hidden_field('min_qty_'.$product_info['products_id'], $product_info['products_opt4']); ?>

                                                                        </td>

                                                                        <td>

                                                                                <?php echo Translate('Maat omschrijving'); ?>

                                                                        </td>

                                                                        <td>

                                                                                <?php echo Translate('Maat'); ?>

                                                                        </td>

                                                                        <td>

                                                                                <?php echo Translate('Plant omschrijving'); ?>

                                                                        </td>

                                                                        <?php

                                                                        for ($i = 0; $i < sizeof($titles_array); $i++) {

                                                                        ?>

                                                                        <td>

                                                                                <?php

                                                                                if ($i==0) {

                                                                                        echo Translate('Prijs/stuk');

                                                                                } else {

                                                                                        if ($titles_array[$i][0]=='1') {

                                                                                        echo sprintf(Translate('Vanaf %s stuk'), $titles_array[$i][0]);

                                                                                        } else {

                                                                                        echo sprintf(Translate('Vanaf %s stuks'), $titles_array[$i][0]);

                                                                                        }

                                                                                }

                                                                                ?>

                                                                   </td>

                                                                        <?php

                                                                        }

                                                                        ?>

                                                                </tr>

                                                                <?php

                                                                $count = 0;

                                                                while ($products_size = tep_db_fetch_array($products_sizes_query))

                                                                {

                                                                        $count++;

                                                                        if ($count > 0)

                                                                        $qty_array = getPricesToQty($products_size['plant_price']);

                                                                        ?>

                                                                        <tr class="data">

                                                                                <td class="first">

                                                                                        <?php echo tep_draw_hidden_field('products_id[]', $products_size['products_plant_id']).tep_draw_input_field('cart_quantity[]', '', 'size="3" class="'.$product_info['products_id'].'"'); ?>

                                                                                </td>

                                                                                <td>

                                                                                        <?php echo $products_size['plant_mc']; ?>

                                                                                </td>

                                                                                <td>

                                                                                        <?php echo $products_size['plant_maat']; ?>

                                                                                </td>

                                                                                <td>

                                                                                        <?php echo $products_size['plant_description']; ?>

                                                                                </td>

                                                                                <?php

                                                                                foreach ($qty_array AS $price) {

                                                                                ?>

                                                                                <td>

                                                                                        <?php echo $currencies->format($price[1]); ?>

                                                                                </td>

                                                                                <?php

                                                                                }

                                                                                ?>

                                                                        </tr>

                                                                        <?php



                                                                }

                                                                ?>

                                                                <tr class="data">

                                                                        <td colspan="5">

                                                                        <input type="submit" class="button-a" value="<?php echo Translate('Voeg toe aan winkelwagen');?>" />

                                                                        </td>

                                                                </tr>

                                                        </table>

                                                        </div>

                                                <?php

                                                }

                                        }

                                        /*specs*/

                                        if (PRODUCT_INFO_SPECIFICATIONS!='false') {

                                        ?>

                                                <div class="product-specs">

                                                <?php

                                                echo StructuredSpecificationsTable($product_info['products_model']);

                                                ?>

                                                </div>

                                        <?php

                                        }

                                        /*compare*/

                                        if ((PRODUCT_COMPARE=='true')) {

                                                ?>

                                                <script type="text/javascript">

                                                /* <![CDATA[ */

                                                jQuery(document).ready(function(){

                                                        var compare_category = <?php echo $current_category; ?>;

                                                        jQuery(".compare_add").click(function(){

                                                                if (jQuery.readCookie("compare_"+compare_category)) {

                                                                        var cookie = jQuery.readCookie("compare_"+compare_category);

                                                                } else {

                                                                        var cookie = '';

                                                                }

                                                                var productId = 'compare_<?php echo $product_info['products_id']; ?>';

                                                                var productName = '<?php echo $product_info['products_name']; ?>';

                                                                jQuery('#compare_list .compare_empty').remove();

                                                                if(jQuery(this).text()=='<?php echo Translate('Vergelijken'); ?>') {

                                                                        if (jQuery('#compare_list').children().length>=<?php echo PRODUCT_COMPARE_MAX; ?>){

                                                                                alert('<?php echo sprintf(Translate('U kan maximum %s producten vergelijken.'), PRODUCT_COMPARE_MAX) ?>');

                                                                        } else {

                                                                                jQuery('#compare_list').append('<li class="' + productId  + '">' + productName  + '<a href="#" id="compare_delete_'+productId.replace('compare_', '')+'" class="compare_delete"><?php echo Translate('Verwijderen'); ?></a></li>');

                                                                                jQuery.setCookie("compare_"+compare_category, cookie+'_'+productId.replace('compare_', ''), {

                                                                                        duration: <?php echo PRODUCT_COMPARE_COOKIE_DURATION; ?>,

                                                                                        path: '<?php echo HTTP_COOKIE_PATH; ?>'

                                                                                });

                                                                                jQuery(this).text('<?php echo Translate('Niet meer vergelijken'); ?>');

                                                                        }

                                                                } else {

                                                                        jQuery('#compare_list .' + productId).remove();

                                                                        jQuery.setCookie("compare_"+compare_category, cookie.replace('_'+productId.replace('compare_', ''), ''), {

                                                                                duration: <?php echo PRODUCT_COMPARE_COOKIE_DURATION; ?>,

                                                                                path: '<?php echo HTTP_COOKIE_PATH; ?>'

                                                                        });

                                                                        jQuery(this).text('<?php echo Translate('Vergelijken'); ?>');

                                                                }

                                                                if (jQuery('#compare_list').children().length==0){

                                                                        jQuery('#compare_list').append('<li class="compare_empty"><?php echo Translate('Er zijn nog geen producten geselecteerd.'); ?></li>');

                                                                }

                                                                if (jQuery('#compare_list').children().length>1){

                                                                        jQuery('.box.compare .compare_button').show('slow');

                                                                } else {

                                                                        jQuery('.box.compare .compare_button').hide();

                                                                }

                                                                return false;

                                                        });

                                                });

                                                /* ]]> */

                                                </script>

                                                <?php

                                        }



                                        /*technical*/

                                        if (PRODUCT_INFO_TECHNICAL!='false') {

                                        ?>

                                        <div class="product-technical">

                                                <?php

                                                if (strstr($product_info['products_technical'], '</k>')) {

                                                        $technical = random_technical_description ($product_info['products_technical'], $limit = 0, /*$showtitle*/true, /*$showtags*/true, /*$showimage*/true);

                                                } else {

                                                        $technical = str_replace("t_yes", '<img src="images/technical/yes.gif" />', stripslashes($product_info['products_technical']));

                                                        $technical = str_replace("t_no", '<img src="images/technical/no.gif" />', $technical);

                                                }

                                                echo $technical;

                                                ?>

                                        </div>

                                        <?php

                                        }

                                }

                                ?>

                        </form>

                        </div>

                        <?php

                        include(DIR_WS_BOXES.'reviews.php');

                        ?>

                </td>

        </tr>

</table>

<?php

require(DIR_WS_INCLUDES . 'column_right.php');

require(DIR_WS_INCLUDES . 'footer.php');

require(DIR_WS_INCLUDES . 'application_bottom.php');

?>
