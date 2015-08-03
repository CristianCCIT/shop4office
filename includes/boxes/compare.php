<?php
if (PRODUCT_COMPARE=='true') {
    $current_category = '';
    if ($_GET['cPath']) {
        $current_category = end(explode('_', $_GET['cPath']));
    } elseif ( (strstr($_SERVER['PHP_SELF'], FILENAME_PRODUCT_INFO)) ) {
        $categories_query = tep_db_query("select categories_id from " . TABLE_PRODUCTS_TO_CATEGORIES . " where products_id = '".(int)$_GET['products_id']."'");
        $categories = tep_db_fetch_array($categories_query);
        $current_category = $categories['categories_id'];
    }
    ?>
    <?php if (($current_category) && (tep_has_category_subcategories($current_category)==0)) { ?>
    <div class="box compare">
        <div class="box_title">
             <?php echo Translate('Producten vergelijken'); ?>
        </div>
        <div class="box_content">
            <ul id="compare_list">
                <?php 
                $compare_count = 0;
                $compare_category = $current_category;
                if ($_COOKIE['compare_'.$compare_category]) {
                    $cookie_val = $_COOKIE['compare_'.$compare_category];
                    foreach (explode('_', $cookie_val ) as $products_id) {
                        if ($products_id!='') {
                            $compare_count++;
                            $product_info_query = tep_db_query("select pd.products_name from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.products_status = '1' and p.products_id = '" . (int)$products_id . "' and pd.products_id = p.products_id and pd.language_id = '" . (int)$languages_id . "'");
                            $product_info = tep_db_fetch_array($product_info_query);
                            ?>
                            <li class="compare_<?php echo $products_id; ?>"><?php echo $product_info['products_name']; ?><a href="#" id="compare_delete_<?php echo $products_id; ?>" class="compare_delete"><?php echo Translate('Verwijderen'); ?></a></li>
                            <?php
                        }
                    }
                }
                if ($compare_count == 0) {
                ?>
                    <li class="compare_empty"><?php echo Translate('Er zijn nog geen producten geselecteerd.'); ?></li>
                <?php
                }
                ?>
            </ul>
            <script type="text/javascript">
            /* <![CDATA[ */
            jQuery(document).ready(function(){
                if (jQuery('#compare_list').children().length>1) {
                    jQuery('.box.compare .compare_button').show('slow');
                }
                var compare_category = <?php echo $current_category; ?>;
                jQuery('.compare_delete').live('click', function() {
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
                    if (jQuery('#compare_list').children().length>=2) {
                        jQuery('.box.compare .compare_button').show('slow');
                    } else {
                        jQuery('.box.compare .compare_button').hide('slow');
                    }
                    return false;
                });
            });
            /* ]]> */
            </script>
            <?php
            $category_info_query = tep_db_query("select categories_name from " . TABLE_CATEGORIES_DESCRIPTION . " where categories_id = '" . (int)$compare_category . "' and language_id = '" . (int)$languages_id . "'");
            $category_info = tep_db_fetch_array($category_info_query);
            ?>
            <div class="compare_button">
            <a href="<?php echo tep_href_link(FILENAME_COMPARE_PRINT, 'cc=' . $compare_category); ?>" <?php if (PRODUCT_COMPARE_VIEW=='popup') { ?>onClick="window.open('<?php echo tep_href_link(FILENAME_COMPARE_PRINT, 'cc='.$compare_category, 'NONSSL'); ?>', '<?php echo $category_info['categories_name'].' '.Translate('vergelijken');?>','scrollbars=yes,resizable=yes,status=yes,width=800,height=600'); return false"<?php } ?> target="_blank" class="button-c" id="compare_button"><?php echo Translate('Vergelijken'); ?></a>
            </div>
            <?php if (PRODUCT_COMPARE_VIEW=='content') { ?>
                <script type="text/javascript">
                /* <![CDATA[ */
                jQuery(document).ready(function(){
                    jQuery("#compare_button").click(function(){
                        jQuery.ajax({
                            url: '<?php echo tep_href_link(FILENAME_AJAX_SEARCH, 'mode=compare&cc='.$compare_category); ?>',
                            success: function(data){
                                jQuery("#compare_window").html(data);
                            }
                        });									 
                        jQuery("#compare_window").show('slow');
                        return false;
                    });
                });
                /* ]]> */
                </script>
            <?php } elseif (PRODUCT_COMPARE_VIEW=='lightbox') { ?>
                <script type="text/javascript">
                /* <![CDATA[ */
                jQuery(document).ready(function(){
                    jQuery("#compare_button").colorbox({href:"<?php echo tep_href_link(FILENAME_AJAX_SEARCH, 'mode=compare&cc='.$compare_category); ?>",width:"850px",height:"85%"});							 
                });
                /* ]]> */
                </script>
            <?php } ?>
        </div>
    </div>
    <?php
    }
}
?>