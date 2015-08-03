<?php
/*
  $Id: all_customers.php, v1.0 March 21, 2005 18:45:00
  adapted by Robert Goth June 24, 2005

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com
  Copyright (c) 2002 - 2004 osCommerce

  written by Jared Call at client' suggestion
  some code nicked and modified from /catalog/admin/customers.php
  Released under the GNU General Public License
*/

  require('includes/application_top.php');
  
  // Sort Function 
  function tep_sort_order ($orderby, $sorted, $title, $order_criteria){
  if (!isset($orderby) or ($orderby == $order_criteria and $sorted == "ASC"))  $to_sort = "DESC"; else $to_sort = "ASC"; 
 $link = '<a href="' . tep_href_link(FILENAME_ALL_CUSTOMERS, 'orderby=' . $order_criteria .'&sorted='. $to_sort) . '" class="headerLink">' . $title . '</a>';
  return $link;
  }
  
  // Produce CSV string for output
function mirror_out ($field, $btw = false) {
	global $csv_accum;
	echo $field;
	$field = strip_tags($field);
	$field = ereg_replace (",","",$field);
	if ($btw) {
		$field = preg_replace('/([a-zA-z]+)([0-9. ]+)/i', '$2', $field);
	}
	if ($csv_accum=='') {
		$csv_accum=$field; 
	} else {
		if (strrpos($csv_accum,chr(10)) == (strlen($csv_accum)-1)) {
			$csv_accum .= $field;
		} else {
			$csv_accum .= ";" . $field; 
		}
	};
	return;
};

  
  //
// entry for bouncing csv string back as file
if (isset($_POST['csv'])) {
	if ($HTTP_POST_VARS['saveas']) {  // rebound posted csv as save file
		$savename= $HTTP_POST_VARS['saveas'] . ".csv";
	}
	else $savename='unknown.csv';

	$csv_string = '';

	if ($HTTP_POST_VARS['csv']) 
		//$csv_string=$HTTP_POST_VARS['csv'];
 /*
 		header("Content-type: application/vnd.ms-excel");
		header("Content-disposition: attachment; filename=$EXPORT_TIME.txt");
		header("Pragma: no-cache");
		header("Expires: 0");
		echo $filestring;
		die();
*/ 	
	if (strlen($HTTP_POST_VARS['csv'])>0){
		
		if (isset($_POST['CSV_save'])) {
			$filename = DIR_FS_DOCUMENT_ROOT . '/temp/customers.csv';
			$f = fopen($filename, "w");
			fwrite($f, $HTTP_POST_VARS['csv']);
			fclose($f);
			//chmod($filename, 0777);
			echo "Customers file created: " . $filename;
		}
		
		if (isset($_POST['CSV_download'])) {
			header("Content-type: application/vnd.ms-excel");
			header("Content-disposition: attachment; filename=$savename");
			header("Pragma: no-cache");
			header("Expires: 0");
			echo $HTTP_POST_VARS['csv'];
			die();
		}
/*
	  header("Expires: Mon, 26 Nov 1962 00:00:00 GMT");
	  header("Last-Modified: " . gmdate('D,d M Y H:i:s') . ' GMT');
	  header("Cache-Control: no-cache, must-revalidate");
	  header("Pragma: no-cache");
	  header("Content-Type: Application/octet-stream");
	  header("Content-Disposition: attachment; filename=$savename");
	  echo $csv_string;
*/
	}
	else 
		echo "CSV string empty";

	//exit;
}

?>

<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
<script language="javascript" src="includes/general.js"></script>
</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF">
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->
<table border="0" width="100%" cellspacing="2" cellpadding="2">
  <tr>
    <td width="<?php echo BOX_WIDTH; ?>" valign="top"><table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="1" cellpadding="1" class="columnLeft">
<!-- left_navigation //-->
<?php require(DIR_WS_INCLUDES . 'column_left.php'); ?>
<!-- left_navigation_eof //-->
    </table></td>
<!-- body_text //-->
    <td width="100%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="0">

      <tr>
        <td colspan="2"><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>    
<?php 

// Used to determine sorting order by field 
  switch ($orderby) {

// toegevoegd TV
	case "id" :
	$db_orderby = "c.customers_id";
	break;
	
	case "abo" :
	$db_orderby = "c.abo_id";
	break;
// einde toevoeging TV

   case "email" :
   $db_orderby = "c.customers_email_address";
   break;
   
   	case "address":
	$db_orderby = "a.entry_street_address";
	break;
	
	case "city":
	$db_orderby = "a.entry_city";
	break;
	
	case "state":
	$db_orderby = "z.zone_code";
	break;
	
	case "country":
	$db_orderby = "co.countries_name";
	break;

	case "telephone":
	$db_orderby = "c.customers_telephone";
	break;
	
	case "pcode":
	$db_orderby = "a.entry_postcode";
	break;
	
   default :
   $db_orderby = "c.customers_id";;
   break;

   }



//  $customers_query_raw = "SELECT c.customers_id , c.customers_default_address_id, c.customers_email_address, c.customers_fax, c.customers_telephone, a.entry_company, a.address_book_id, a.customers_id, a.entry_firstname, a.entry_lastname, a.entry_street_address, a.entry_suburb, a.entry_city, a.entry_state, a.entry_postcode, a.entry_country_id, a.entry_zone_id, z.zone_code, co.countries_name FROM " . TABLE_CUSTOMERS . " c JOIN " . TABLE_ZONES . " z ON a.entry_zone_id = z.zone_id JOIN " . TABLE_COUNTRIES . " co ON a.entry_country_id = co.countries_id LEFT JOIN " . TABLE_ADDRESS_BOOK . " a ON c.customers_id = a.customers_id and c.customers_default_address_id = a.address_book_id ORDER BY $db_orderby $sorted";
  $customers_query_raw = "SELECT c.customers_id , c.abo_id, c.customers_lastname, c.customers_firstname, c.customers_default_address_id, c.customers_email_address, c.customers_fax, c.customers_telephone, c.customers_gsm, a.entry_company, a.address_book_id, a.entry_street_address, a.entry_suburb, a.entry_city, a.entry_state, a.entry_postcode, a.entry_country_id, a.entry_zone_id, a.billing_tva_intracom, z.zone_code, co.countries_name FROM " . TABLE_CUSTOMERS . " c 
						  LEFT JOIN " . TABLE_ADDRESS_BOOK . " a ON c.customers_id = a.customers_id and c.customers_default_address_id = a.address_book_id 
                          LEFT JOIN " . TABLE_ZONES . " z ON a.entry_zone_id = z.zone_id 
						  LEFT JOIN " . TABLE_COUNTRIES . " co ON a.entry_country_id = co.countries_id 
						  ORDER BY $db_orderby $sorted";

  $customers_query = tep_db_query($customers_query_raw); 
 
 
 //BOF HEADER  ?> 
<tr class="dataTableHeadingRow">
<? /*<td class="dataTableHeadingContent"><?php echo TABLE_HEADING_NUMBER; ?></td> */ ?>
<td class="dataTableHeadingContent"><?php echo tep_sort_order ($orderby, $sorted, 'Shop_ID', 'id');?></td>
<td class="dataTableHeadingContent"><?php echo tep_sort_order ($orderby, $sorted, 'ABO_ID', 'abo');?></td>
<td class="dataTableHeadingContent"><?php echo tep_sort_order ($orderby, $sorted, 'Last name', 'lastname');?></td>
<td class="dataTableHeadingContent"><?php echo tep_sort_order ($orderby, $sorted, 'First name', 'firstname');?></td>
<td class="dataTableHeadingContent"><?php echo tep_sort_order ($orderby, $sorted, 'Company', 'company');?></td>
<td class="dataTableHeadingContent"><?php echo tep_sort_order ($orderby, $sorted, EMAIL, 'email');?></td> 
<td class="dataTableHeadingContent"><?php echo tep_sort_order ($orderby, $sorted, ADDRESS, 'address');?></td> 
<td class="dataTableHeadingContent"><?php echo tep_sort_order ($orderby, $sorted, CITY_NAME, 'city');?></td>
<td class="dataTableHeadingContent"><?php echo tep_sort_order ($orderby, $sorted, STATE, 'state');?></td> 
<td class="dataTableHeadingContent"><?php echo tep_sort_order ($orderby, $sorted, POSTAL_CODE, 'pcode');?></td> 
<td class="dataTableHeadingContent"><?php echo tep_sort_order ($orderby, $sorted, CONTRY_NAME, 'country');?></td> 
<td class="dataTableHeadingContent"><?php echo tep_sort_order ($orderby, $sorted, TELEPHONE_NUMBER, 'telephone');?></td>
<td class="dataTableHeadingContent"><?php echo tep_sort_order ($orderby, $sorted, 'Gsm', 'gsm');?></td>
<td class="dataTableHeadingContent"><?php echo tep_sort_order ($orderby, $sorted, 'tva_intra', 'tva');

//EOF HEADER
?></td>
								  
		   	 </tr>
  
  <?PHP
  $num_rows = tep_db_num_rows($customers_query);
  while ($customers = tep_db_fetch_array($customers_query)) {
	  if ( tep_not_null($customers['customers_id']) ) {
	  $rows++;
	    if (strlen($rows) < 2) {
      $rows = '0' . $rows;
    }		
	$csv_accum .= "\n";  
		  
		  $email = '<a href="mailto:' . $customers['customers_email_address'] . '">' 
		            . $customers['customers_email_address'] . '</a>';
		  $full_name = '<a href="customers.php?cID=' . $customers['customers_id'] . '&action=edit"> ' . $customers['customers_lastname'] . '</a>';
?>  
		      <tr class="dataTableRow" onMouseOver="rowOverEffect(this)" onMouseOut="rowOutEffect(this)">
		      	 <? /*  <td align="left" class="dataTableContent"><?php echo $rows; ?>.</td> */ ?>
		      	  <td class="dataTableContent"><?php mirror_out($customers['customers_id']);?></td>
		      	  <td class="dataTableContent"><?php mirror_out($customers['abo_id']);?></td>
		      	  <td class="dataTableContent"><?php mirror_out($customers['customers_lastname']);?></td>
				  <td class="dataTableContent"><?php mirror_out($customers['customers_firstname']);?></td>
				  <td class="dataTableContent"><?php mirror_out($customers['entry_company']);?></td>
		      	  <td class="dataTableContent"><?php mirror_out($email); ?></td> 
		      	  <td class="dataTableContent"><?php mirror_out($customers['entry_street_address']); ?></td> 
		          <td class="dataTableContent"><?php mirror_out($customers['entry_city']); ?></td> 
		      	  <td class="dataTableContent"><?php mirror_out($customers['zone_code']); ?></td> 
		      	  <td class="dataTableContent"><?php mirror_out($customers['entry_postcode']); ?></td> 
		      	  <td class="dataTableContent"><?php mirror_out($customers['countries_name']); ?></td>
				  <td class="dataTableContent"><?php mirror_out($customers['customers_telephone']); ?></td>
				  <td class="dataTableContent"><?php mirror_out($customers['customers_gsm']); ?></td>
				  <td class="dataTableContent"><?php mirror_out($customers['billing_tva_intracom'], true); ?></td>
				  
		   	 </tr>

	      <?php 	  
      } else { }
  }          
?>

<!-- body_text_eof //-->
  </tr>
  <?PHP 
			 if ($num_rows>0 && !$print) {
				if (isset($_GET['save_csv']) && $_GET['save_csv']!='') {
					$filename = DIR_FS_DOCUMENT_ROOT . 'temp/' . $_GET['save_csv'];
					$f = fopen($filename, "w");
					fwrite($f, $csv_accum);
					fclose($f);
					
					echo "Customers file created: " . $filename;
				}
?>


				<td class="smallText" colspan="4"><form action="<?php echo $_SERVER['PHP_SELF']; ?>" method=post><input type='hidden' name='csv' value='<?php echo $csv_accum; ?>'><input type='hidden' name='saveas' value='Customer_list <?php
					//suggested file name for csv, include year and month 
				echo date("Y" . "-" . "m" . "-" . "d" . "-" . "Hi"); 
				?>'><input type="submit" name="CSV_download" value="<?php echo TEXT_BUTTON_REPORT_DOWNLOAD ;?>">
				<input type="submit" name="CSV_save" value="<?php echo TEXT_BUTTON_REPORT_SAVE ;?>"></form>
				</td>
</tr>
<?php }; // end button for Save CSV ?>
</table>



<!-- body_eof //-->

			
		   	


<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
</body>
</html>
