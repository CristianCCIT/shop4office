<?php
require('includes/configure.php');
//PDO Connection
try {
        $pdo = new PDO('mysql:host='.DB_SERVER.';dbname='.DB_DATABASE.';charset=utf8', DB_SERVER_USERNAME, DB_SERVER_PASSWORD);
        $pdo->exec("set names utf8");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); //only in development mode
} catch(PDOException $e) {
        echo 'ERROR: '.$e->getMessage();
}
//load files first so that the session vars can be reused.
require_once('includes/modules/checkout/classes/Analytics_module.php');
require_once('includes/modules/checkout/Modules.php');
require_once('includes/modules/checkout/Checkout.php');
//load all module files so that they can be used from $_SESSION
// @TODO delete this in cms 2.0 and find better way
// this has to be loaded before application_top because session vars needs the files for class definition
if (is_dir(DIR_FS_CATALOG.'includes/modules/checkout/modules/')) {
        $dirHandle = opendir(DIR_FS_CATALOG.'includes/modules/checkout/modules/');
        while(false !== ($module = readdir($dirHandle))) {
                if (is_dir(DIR_FS_CATALOG.'includes/modules/checkout/modules/'.$module) && $module != '.' && $module != '..')  {
                        $object = glob(DIR_FS_CATALOG.'includes/modules/checkout/modules/'.$module.'/*_module.php');
                        require_once($object[0]);
                }
        }
}
//Set country
if (isset($_POST['shipping_country']) && $_POST['shipping_country'] > 0) {
        $country = $_POST['shipping_country'];
        setcookie('shipping_country', $_POST['shipping_country']);
} else {
        if ($_COOKIE['shipping_country']) {
                $country = $_COOKIE['shipping_country'];
        } else {
                $country = '21';
                setcookie('shipping_country', '21');
        }
}
// @TODO End Of Delete
require_once("includes/application_top.php");
if (!tep_session_is_registered('customer_id') && canShop() != 'true') {
        $navigation->set_snapshot();
        tep_redirect(tep_href_link(FILENAME_LOGIN, '', 'SSL'));
}
//start logging class
$Analytics = new Analytics();
if (!is_object($Modules)) {
        //start modules class
        $Modules = new Modules($country);
}

if (isset($_GET['action']) && ($_GET['action']) == 'quick_order') {
        $modelno = $_POST['item'];
        $prodid_query = tep_db_query("select products_id, products_model from products where products_model = '" . $modelno . "'");
        $prodid_fetch = tep_db_fetch_array($prodid_query);
        if ($prodid_fetch['products_id'] == '') {
                $prodidString = tep_redirect(tep_href_link(FILENAME_SHOPPING_CART, 'avail=no'));
        } else {
                $prodidString = tep_redirect(tep_href_link(FILENAME_SHOPPING_CART, 'action=buy_now' . '&products_id=' . $prodid_fetch['products_id']));
        }
        echo $prodidString;
}
if (isset($_GET['avail']) && ($_GET['avail']) == 'no') {
        $availmsg = '<tr><td class="errorBox">' . Translate('Ongeldig item') . '</td></tr><tr><td height="10"></td></tr>';
}else {
        $availmsg = '';
}
//Catalog Quick Order by Rob Holzer
$breadcrumb->add(Translate('Winkelwagen'), tep_href_link(FILENAME_SHOPPING_CART));
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<base href="<?php echo (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG; ?>">
<link rel="stylesheet" type="text/css" href="stylesheet.css">
</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0">
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->
<!-- body //-->
<table border="0" width="100%" cellspacing="3" cellpadding="3">
<tr>
<td width="<?php echo BOX_WIDTH; ?>" valign="top"><table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="0" cellpadding="2">
<!-- left_navigation //-->
<?php require(DIR_WS_INCLUDES . 'column_left.php'); ?>
<!-- left_navigation_eof //-->
</table></td>
<!-- body_text //-->
<td width="100%" valign="top">
<?php echo tep_draw_form('cart_quantity', tep_href_link(FILENAME_SHOPPING_CART, 'action=update_product')); ?>
<div class="cart">
<table border="0" width="100%" cellspacing="0" cellpadding="0">
        <?php
        //Catalog Quick Order by Rob Holzer
        echo $availmsg;
        ?>
        <tr>
                <td class="padder">
                        <h1><?php echo Translate('Winkelwagen'); ?></h1>
                </td>
        </tr>
        <?php
        if ($cart->count_contents() > 0) {
        ?>
        <tr>
                <td valign="top">
                        <?php
                        $info_box_contents = array();
                        $info_box_contents[0][] = array('params' => 'class="productListing-heading"',
                                                                                        'text' => Translate('Producten'));
                        if (USE_PRICES_TO_QTY == 'true') {
                                $info_box_contents[0][] = array('params' => 'class="productListing-heading"',
                                                                                                'text' => Translate('Maat'));
                        }
                        $info_box_contents[0][] = array('align' => 'center',
                                                                                        'params' => 'class="productListing-heading"',
                                                                                        'text' => Translate('Hoeveelheid'));
                        if (CanShop() == 'true') {
                                $info_box_contents[0][] = array('align' => 'right',
                                                                                                'params' => 'class="productListing-heading"',
                                                                                                'text' => Translate('Totaal'));
                        }
                        $any_out_of_stock = 0;
                        $products = $cart->get_products();
                        for ($i=0, $n=sizeof($products); $i<$n; $i++) {
                                // Push all attributes information in an array
                                if (isset($products[$i]['attributes']) && is_array($products[$i]['attributes'])) {
                                        while (list($option, $value) = each($products[$i]['attributes'])) {
                                                echo tep_draw_hidden_field('id[' . $products[$i]['id'] . '][' . $option . ']', $value);
                                                $attributes = tep_db_query("select popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix
                                                                                                   from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval, " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                                                                                                   where pa.products_id = '" . $products[$i]['id'] . "'
                                                                                                   and pa.options_id = '" . $option . "'
                                                                                                   and pa.options_id = popt.products_options_id
                                                                                                   and pa.options_values_id = '" . $value . "'
                                                                                                   and pa.options_values_id = poval.products_options_values_id
                                                                                                   and popt.language_id = '" . $languages_id . "'
                                                                                                   and poval.language_id = '" . $languages_id . "'");
                                                $attributes_values = tep_db_fetch_array($attributes);
                                                $products[$i][$option]['products_options_name'] = $attributes_values['products_options_name'];
                                                $products[$i][$option]['options_values_id'] = $value;
                                                $products[$i][$option]['products_options_values_name'] = $attributes_values['products_options_values_name'];
                                                $products[$i][$option]['options_values_price'] = $attributes_values['options_values_price'];
                                                $products[$i][$option]['price_prefix'] = $attributes_values['price_prefix'];
                                        }
                                }
                        }
                        //change dropdown
                        for ($s=-1 ; $s<NUM_PROD_MAXORD; $s++) {
                                $z =  $s+1;
                                $options[] = array('id' => $z,
                                                                        'text' => $z);
                        }
                        //end change dropdown
                        for ($i=0, $n=sizeof($products); $i<$n; $i++) {
                                if (($i/2) == floor($i/2)) {
                                        $info_box_contents[] = array('params' => 'class="productListing-even"');
                                } else {
                                        $info_box_contents[] = array('params' => 'class="productListing-odd"');
                                }
                                $cur_row = sizeof($info_box_contents) - 1;
                                $product_opt = tep_db_query("select p.products_opt1 from " . TABLE_PRODUCTS . " p where p.products_id = '" . $products[$i]['id'] . "'");
                                $product_opt = tep_db_fetch_array($product_opt);
                                if ((isset($product_opt['products_opt1'])) && ($product_opt['products_opt1'] != '')) {
                                        $optie = '&nbsp;-&nbsp;'.$product_opt['products_opt1'];
                                } else {
                                        $optie = '';
                                }
                                if (isset($products[$i]['attributes']) && is_array($products[$i]['attributes'])) {
                                        reset($products[$i]['attributes']);
                                        while (list($option, $value) = each($products[$i]['attributes'])) {
                                          $products_optie = $products[$i][$option]['products_options_values_name'];
                                        }
                                }
                                $products_out_of_stock = '';

                                $products_name = '';
                                if (STOCK_CHECK == 'true') {
                                        if ((SOAP_STATUS=='true') && (SOAP_STOCK=='true')) {
                                                if (GetStockMaat($products[$i]['id'], $products_optie, SOAP_STOCK_TYPE) < $products[$i]['quantity']) {
                                                        $any_out_of_stock = 1;
                                                        $products_out_of_stock = '<span class="stockWarning">'.STOCK_MARK_PRODUCT_OUT_OF_STOCK.'</span> ';
                                                } else {
                                                        $products_out_of_stock = ' ';
                                                }
                                        } else {
                                                $stock_check = tep_check_stock($products[$i]['id'], $products[$i]['quantity']);
                                                if (tep_not_null($stock_check)) {
                                                        $any_out_of_stock = 1;
                                                        $products_name .= $stock_check;
                                                }
                                        }
                                }
                                 // $products_name .= $products_out_of_stock.'<a href="' . tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $products[$i]['id']) . '" class="prodlist_name"><b>' . $products[$i]['name'] . '</b></a>';
                                // nikhil starts

                                  $stock_check = tep_check_stock($products[$i]['id'], $products[$i]['quantity']);
                                  if (STOCK_ALLOW_CHECKOUT == 'false') {
                                  if (tep_not_null($stock_check)) {

                                                $products_name .= $products_out_of_stock.'<a href="' . tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $products[$i]['id']) . '" class="prodlist_name"><b>' . $products[$i]['name'] . '</b></a>*';
                                                }
                                                else
                                                {
                                                $products_name .= $products_out_of_stock.'<a href="' . tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $products[$i]['id']) . '" class="prodlist_name"><b>' . $products[$i]['name'] . '</b></a>';
                                                }
                                } else {
                                  $products_name .= $products_out_of_stock.'<a href="' . tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $products[$i]['id']) . '" class="prodlist_name"><b>' . $products[$i]['name'] . '</b></a>';
                                 }
                                // nikhil ends

                                if (isset($products[$i]['attributes']) && is_array($products[$i]['attributes'])) {
                                        reset($products[$i]['attributes']);
                                        while (list($option, $value) = each($products[$i]['attributes'])) {
                                                $products_name .= '<br><small><i> - ' . $products[$i][$option]['products_options_name'] . ' ' . $products[$i][$option]['products_options_values_name'] . '</i></small>';
                                        }
                                }
                                $info_box_contents[$cur_row][] = array('params' => 'class="productListing-data"',
                                                                                                                'text' => $products_name);




//////////////////////////////////////////////////////////////// 08-05-2013
                $products_weight = $products[$i]['weight'] * $products[$i]['quantity'];
//////////////////////////////////////////////////////////////// 08-05-2013




                                if (USE_PRICES_TO_QTY == 'true') {
                                        $info_box_contents[$cur_row][] = array('params' => 'class="productListing-data"',
                                                                                                                        'text' => $products[$i]['maat']);
                                }
                                if (USE_PRICES_TO_QTY == 'true' && $products[$i]['size_id'] != '') {
                                        $hidden_fields = tep_draw_hidden_field('min_quantity', $hoeveelheid['min_quant']).tep_draw_hidden_field('product_name', html_entity_decode($products[$i]['original_name'], ENT_QUOTES)).tep_draw_hidden_field('products_id[]', $products[$i]['size_id']);
                                        $delete_link = '<a href="' . tep_href_link(FILENAME_SHOPPING_CART, 'action=remove_product&products_id='.$products[$i]['size_id']) . '"><img src="images/icons/remove.gif" alt="'.Translate("Verwijderen").'"></a>';
                                } else {
                                        $hidden_fields = tep_draw_hidden_field('products_id[]', $products[$i]['id']) . tep_draw_hidden_field('min_quantity', 1);
                                        $delete_link = '<a href="' . tep_href_link(FILENAME_SHOPPING_CART, 'action=remove_product&products_id='.$products[$i]['id']) . '"><img src="images/icons/remove.gif" alt="'.Translate("Verwijderen").'"></a>';
                                }
                                $info_box_contents[$cur_row][] = array('align' => 'center',
                                                                                                                'params' => 'class="productListing-data" valign="top"',
                                                                                                                //change dropdown
                                                                                                                'text' => '<table><tr><td>'.$delete_link.'</td><td width="50">' . tep_draw_pull_down_menu('cart_quantity[]', $options, $products[$i]['quantity'], 'onchange="this.form.submit()" style="width: 100%"').$hidden_fields.'</td></tr></table>');
                                                                                                                //end change dropdown
                                if (CanShop() == 'true') {
                                        $info_box_contents[$cur_row][] = array('align' => 'right',
                                                                                                                        'params' => 'class="productListing-data"',
                                                                                                                        'text' => '<strong>' . $currencies->display_price($products[$i]['final_price'], tep_get_tax_rate($products[$i]['tax_class_id']), $products[$i]['quantity']) . '</strong>');
                                }

//////////////////////////////////////////////////////////////// 08-05-2013
                    $total_weight += $products_weight;
//////////////////////////////////////////////////////////////// 08-05-2013


                        }
                        new contentBox($info_box_contents);


//////////////////////////////////////////////////////////////// 08-05-2013
         $total_weight = round($total_weight, 2);
        //$shipping_cost = 0;
//////////////////////////////////////////////////////////////// 08-05-2013


                        ?>
                </td>
        </tr>
        <tr>
                <td height="10"></td>
        </tr>
        <?php
        if (CanShop() == 'true') {
        ?>
        <tr>
                <td>
                        <?php
                        $active_modules = array();
                        if(sizeof($Modules->modules['shipping']) > 0){
                            ksort($Modules->modules['shipping']); //order by sort_order
                            foreach($Modules->modules['shipping'] as $module) {
                                if ($$module->is_active($country)) {
                                    $active_modules = array_merge($active_modules, $$module->output_array($country));
                                }
                            }
                        }
                        //go through active modules and select the one with the cheapest shipping quote
                        $active_module = 0;
                        foreach($active_modules as $id=>$data) {
                                if ($data['quote'] < $active_modules[$active_module]['quote']) {
                                        $active_module = $id;
                                }
                        }
                        ?>

<?php
///////////////////////////////////////////////////////////////////////////////////////////////08-05-2013
///Table rate shipping methods
    /*$queryShippingMethod = tep_db_query("SELECT quote FROM checkout_Table WHERE status ='true'");
    $arrShippingMethod = tep_db_fetch_array($queryShippingMethod);

    if(is_array($arrShippingMethod) && (sizeof($arrShippingMethod) > 0)){
        $tempArr = explode(';',$arrShippingMethod['quote']);
        foreach ($tempArr as $tempShippRate) {
            $arrShippRate = explode(':',$tempShippRate);
            if(intval($total_weight) <= intval($arrShippRate[0])){
                //$shipping_cost = $arrShippRate[1];
                //$active_modules[$active_module]['quote'] = $shipping_cost;

                $active_modules[$active_module]['quote'] = $arrShippRate[1];
                break;
            }//if ends
        }//for ends
    }*/

///////////////////////////08-05-2013
?>


                        <table cellspacing="0" cellpadding="0" border="0" width="100%">
                                <tr>
                                        <td style="text-align:right;"><strong><?php echo Translate('Subtotaal'); ?>: </strong></td>
                                        <td style="text-align:right;width: 60px;"><span class="totalprice"><?php echo $currencies->format($cart->show_total()); ?></span></td>
                                </tr>
                                <tr>
                                        <td style="text-align:right;">
                                                <?php
                                                echo Translate('Verzendingskosten naar').' '.$Modules->get_country_list('shipping_country', $country, 'onChange="this.form.submit()"').' '.Translate('via');
                                                ?>
                                                <strong>**<?php echo $active_modules[$active_module]['title']; ?>: </strong>
                                        </td>
                                        <td style="text-align:right;width: 60px;"><span class="totalprice"><?php echo $currencies->format($active_modules[$active_module]['quote']); ?></span></td>
                                </tr>
                                <tr>
                                        <td style="text-align:right;"><strong><?php echo Translate('Totaal'); ?>: </strong></td>
                                        <td style="text-align:right;width: 60px;"><span class="totalprice"><?php echo $currencies->format($cart->show_total()+$active_modules[$active_module]['quote']); ?></span></td>
                                </tr>
                        </table>
                </td>
        </tr>
    <?php
        }
        if ($any_out_of_stock == 1) {
                if (STOCK_ALLOW_CHECKOUT == 'true') {
        ?>
        <tr>
                <td height="10"></td>
        </tr>
        <tr>
                <td class="stockWarning" align="center">
                        <?php echo Translate('Producten gemarkeerd met * zijn niet voorradig.<br />U kunt het product toch kopen. Het zal verstuurd worden zodra het binnen is.'); ?>
                </td>
        </tr>
        <?php
                } else {
        ?>
        <tr>
                <td height="10"></td>
        </tr>
        <tr>
                <td class="stockWarning" align="center">
                        <?php echo Translate('Producten gemarkeerd met * zijn niet voorradig.<br />Verander het aantal van het product gemarkeerd met (*), Dank u'); ?>�
                </td>
        </tr>
        <?php
                }
        }
        ?>
        <tr>
                <td height="10"></td>
        </tr>
        <tr>
                <td>
                        <table cellpadding="5" cellspacing="5" width="100%">
                                <tr>
                                <?php /*<td><input type="submit" value="<?php echo Translate('Winkelwagen bijwerken'); ?>" class="formbutton button-a" /></td>*/ ?>
                                <td>
                                <?php
                                        $back = sizeof($navigation->path)-2;
                                                if (isset($navigation->path[$back])) {
                                                        echo '<a href="' . tep_href_link($navigation->path[$back]['page'], tep_array_to_string($navigation->path[$back]['get'], array('action')), $navigation->path[$back]['mode']) . '" class="button-a">';
                                                        echo '<span>' . Translate('Verder winkelen') . '</span>';
                                                        echo '</a>';
                                                }
                                        ?>
                                </td>
                                <?php if (CanShop() == 'true') { ?>
                    <?php
                                       /* - ORIGINAL 
                                        if (USE_CHECKOUT=='false') {
                                                $button_label = Translate('Bestelling bevestigen');
                                                $button_href = tep_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL');
                                        } else {
                                                $button_label = Translate('Afrekenen');
                                                $button_href = tep_href_link(FILENAME_CHECKOUT, '', 'SSL');
                                        } */
                                        
                                        /* 01-07-2013 - issue #1224 */
                                         if (USE_CHECKOUT=='false') {
                                                $button_label = Translate('Bestelling bevestigen');
                                                $button_href = tep_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL');
                                        } else if(STOCK_ALLOW_CHECKOUT == 'true'){
											    $button_label = Translate('Afrekenen');
                                                $button_href = tep_href_link(FILENAME_CHECKOUT, '', 'SSL');
										}else if((STOCK_ALLOW_CHECKOUT == 'false')&&($any_out_of_stock == 0)){ 
												$button_label = Translate('Afrekenen');
												$button_href = tep_href_link(FILENAME_CHECKOUT, '', 'SSL');
										}else{
											  $button_label = Translate('Afrekenen');
											  $button_href  = tep_href_link(FILENAME_SHOPPING_CART, '', 'SSL');
										}
                                        
                                        ?>
                                <td align="right"><?php echo '<a href="' . $button_href . '" class="button-b"><span>' . $button_label . '</span></a>'; ?></td>
                                <?php } ?>
                        </tr>
                        </table>
                </td>
        </tr>
        <tr>
                <td height="10"></td>
        </tr>
        <tr>
                <td style="text-align:right;font-size:10px;">
                        <?php echo '**'.Translate('Dit is de goedkoopste verzendmethode voor het gekozen land'); ?>
                </td>
        </tr>
<?php
        } else {
?>
        <tr>
                <td align="center" class="infobox_text"><?php new contentBox(array(array('text' => Translate('Uw winkelwagen bevat geen items.')))); ?></td>
        </tr>
        <tr>
                <td height="10"></td>
        </tr>
        <tr>
                <td>
                        <table border="0" width="100%" cellspacing="1" cellpadding="2" class="infoBox">
                                <tr class="infoBoxContents">
                                        <td>
                                                <table border="0" width="100%" cellspacing="0" cellpadding="2">
                                                        <tr>
                                                                <td width="10" height="10"></td>
                                                                <td align="right" class="main"><?php echo '<a href="' . tep_href_link(FILENAME_DEFAULT) . '" class="button-a"><span>' . Translate('Ga verder') . '</span></a>'; ?></td>
                                                                <td width="10" height="10"></td>
                                                        </tr>
                                                </table>
                                        </td>
                                </tr>
                        </table>
                </td>
        </tr>
<?php
        }
?>
</table>
</div>
</form>
</td>
<!-- body_text_eof //-->
<td width="<?php echo BOX_WIDTH; ?>" valign="top"><table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="0" cellpadding="2">
<!-- right_navigation //-->
<?php require(DIR_WS_INCLUDES . 'column_right.php'); ?>
<!-- right_navigation_eof //-->
</table></td>
</tr>
</table>
<!-- body_eof //-->
<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
