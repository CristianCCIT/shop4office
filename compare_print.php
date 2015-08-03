<?php
require_once('includes/application_top.php');
$product_check_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.products_status = '1' and p.products_id = '" . (int)$_GET['products_id'] . "' and pd.products_id = p.products_id and pd.language_id = '" . (int)$languages_id . "'");
$product_check = tep_db_fetch_array($product_check_query);
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
        <td width="<?php echo BOX_WIDTH; ?>" valign="top">
			<table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="0" cellpadding="2">
				<!-- left_navigation //-->
				<?php require(DIR_WS_INCLUDES . 'column_left.php'); ?>
				<!-- left_navigation_eof //-->
			</table>
		</td>
        <!-- body_text //-->
		<td width="100%" valign="top">
			<table border="0" width="100%" cellspacing="0" cellpadding="0">
            	<tr>
                	<td>
                    <div id="compare_wrap">
					<script type="text/javascript">
                    /* <![CDATA[ */
                    jQuery(document).ready(function(){
                        jQuery.ajax({
                            url: '<?php echo tep_href_link(FILENAME_AJAX_SEARCH, 'mode=compare&cc='.$_GET['cc'].'&print_friendly=1'); ?>',
                            beforeSend: waitingCompareLoad,
                            success: function(data){
                                jQuery("#compare_wrap").html(data);
                            }
                        });
                    });
					function waitingCompareLoad() {
						var $pr = jQuery("#compare_wrap");
						if (!$pr.children().is('div.overflow')) {
							var width = $pr.width();
							var height = $pr.height();
							//var $div = $pr.prepend("<div class='overflow'><span><?php echo Translate('Even geduld, Uw selectie wordt geladen.');?></span></div>").find('div.overflow');
							$div.width(width);
							$div.height(height);
						}
					}
                    /* ]]> */
                    </script>
                    </div>
                    </td>
                </tr>
            </table>
		</td>
        <!-- body_text_eof //-->
		<td width="<?php echo BOX_WIDTH; ?>" valign="top">
			<table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="0" cellpadding="2">
				<!-- right_navigation //-->
				<?php require(DIR_WS_INCLUDES . 'column_right.php'); ?>
				<!-- right_navigation_eof //-->
    		</table>
		</td>
	</tr>
</table>
<!-- body_eof //-->
<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>