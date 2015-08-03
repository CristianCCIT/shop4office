<?php

// Current File Version
$curver = '1.0 - MS2';

/*
  $Id: ABO_download.php,v 1.0 2004/08/01 chris@aboservice.be Exp $
*/

//
//*******************************
//*******************************
// C O N F I G U R A T I O N
// V A R I A B L E S
//*******************************
//*******************************

// **** Temp directory ****
// if you changed your directory structure from stock and do not have /catalog/temp/, then you'll need to change this accordingly.
//
$tempdir = "temp/";
$tempdir2 = "temp/";

//**** File Splitting Configuration ****
// we attempt to set the timeout limit longer for this script to avoid having to split the files
// NOTE:  If your server is running in safe mode, this setting cannot override the timeout set in php.ini
// uncomment this if you are not on a safe mode server and you are getting timeouts
// set_time_limit(330);

// if you are splitting files, this will set the maximum number of records to put in each file.
// if you set your php.ini to a long time, you can make this number bigger
global $maxrecs;
$maxrecs = 300; // default, seems to work for most people.  Reduce if you hit timeouts
//$maxrecs = 4; // for testing

/*
// **** Quote -> Escape character conversion ****
// If you have extensive html in your descriptions and it's getting mangled on upload, turn this off
// set to 1 = replace quotes with escape characters
// set to 0 = no quote replacement
global $replace_quotes;
$replace_quotes = true;
*/
// **** Field Separator ****
// change this if you can't use the default of tabs
// Tab is the default, comma and semicolon are commonly supported by various progs
// Remember, if your descriptions contain this character, you will confuse EP!
global $separator;
$separator = "\t"; // tab is default
//$separator = ","; // comma
//$separator = ";"; // semi-colon
//$separator = "~"; // tilde
//$separator = "-"; // dash
//$separator = "*"; // splat

require('includes/application_top.php');
require('includes/database_tables.php');

$avail_order_status = array('1');
if (
MODULE_PAYMENT_ICEPAY_CC_MASTERCARD_STATUS == 'True'
|| MODULE_PAYMENT_ICEPAY_CC_AMEX_STATUS == 'True'
|| MODULE_PAYMENT_ICEPAY_WIRE_STATUS == 'True'
|| MODULE_PAYMENT_ICEPAY_SMS_STATUS == 'True'
|| MODULE_PAYMENT_ICEPAY_PBAR_STATUS == 'True'
|| MODULE_PAYMENT_ICEPAY_PAYSAFECARD_STATUS == 'True'
|| MODULE_PAYMENT_ICEPAY_PAYPAL_STATUS == 'True'
|| MODULE_PAYMENT_ICEPAY_IDEAL_STATUS == 'True'
|| MODULE_PAYMENT_ICEPAY_GIROPAY_STATUS == 'True'
|| MODULE_PAYMENT_ICEPAY_DDEBIT_STATUS == 'True'
|| MODULE_PAYMENT_ICEPAY_EBANKING_STATUS == 'True'
|| MODULE_PAYMENT_ICEPAY_BANCASH_STATUS == 'True'
|| MODULE_PAYMENT_ICEPAY_STATUS == 'True'
) {
	$avail_order_status[] = MODULE_PAYMENT_ICEPAY_SUCCESS_ORDER_STATUS_ID;
}
$avail_order_status = '"'.implode('", "', $avail_order_status).'"';

global $filelayout, $filelayout_count, $filelayout_sql, $langcode, $fileheaders, $avail_order_status;

$orders_status = $HTTP_GET_VARS['orders_status'];


//elari check default language_id from configuration table DEFAULT_LANGUAGE
$epdlanguage_query = tep_db_query("select languages_id, name from " . TABLE_LANGUAGES . " where code = '" . DEFAULT_LANGUAGE . "'");
if (tep_db_num_rows($epdlanguage_query)) {
	$epdlanguage = tep_db_fetch_array($epdlanguage_query);
	$epdlanguage_id   = $epdlanguage['languages_id'];
	$epdlanguage_name = $epdlanguage['name'];
} else {
	Echo 'Strange but there is no default language to work... That may not happen, just in case... ';
}

$langcode = ep_get_languages();

if ( $dltype != '' ){
	// if dltype is set, then create the filelayout.  Otherwise it gets read from the uploaded file

	ep_create_filelayout($dltype, $orders_status); // get the right filelayout for this download
}

//*******************************
//*******************************
// E N D
// INITIALIZATION
//*******************************
//*******************************


if ( $download == 'stream' or  $download == 'tempfile' ){
	//*******************************
	//*******************************
	// DOWNLOAD FILE
	//*******************************
	//*******************************
	$filestring = ""; // this holds the csv file we want to download


	$result = tep_db_query($filelayout_sql);
	$row =  tep_db_fetch_array($result);

	// Here we need to allow for the mapping of internal field names to external field names
	// default to all headers named like the internal ones
	// the field mapping array only needs to cover those fields that need to have their name changed
	if ( count($fileheaders) != 0 ){
		$filelayout_header = $fileheaders; // if they gave us fileheaders for the dl, then use them
	} else {
		$filelayout_header = $filelayout; // if no mapping was spec'd use the internal field names for header names
	}
	//We prepare the table heading with layout values
	foreach( $filelayout_header as $key => $value ){
		$filestring .= $key . $separator;
	}
	// now lop off the trailing tab
	$filestring = substr($filestring, 0, strlen($filestring)-1);

	// set the type

		$endofrow = $separator . 'EOREOR' . "\n";
	$filestring .= $endofrow;

	$num_of_langs = count($langcode);
	while ($row){

		// If you have other modules that need to be available, put them here

		// remove any bad things in the texts that could confuse EasyCustomer
		$therow = '';
		foreach( $filelayout as $key => $value ){
			//echo "The field was $key<br>";

			$thetext = $row[$key];
			// kill the carriage returns and tabs in the descriptions, they're killing me!
			$thetext = str_replace("\r",' ',$thetext);
			$thetext = str_replace("\n",' ',$thetext);
			$thetext = str_replace("\t",' ',$thetext);
			// and put the text into the output separated by tabs
			if ($key == 'billing_tva_intracom') {
				$thetext = preg_replace('/([a-zA-z]+)([0-9. ]+)/i', '$2', $thetext);
			}
			$therow .= $thetext . $separator;
		}

		// lop off the trailing tab, then append the end of row indicator
		$therow = substr($therow,0,strlen($therow)-1) . $endofrow;

		$filestring .= $therow;
		// grab the next row from the db
		$row =  tep_db_fetch_array($result);
	}

	$EXPORT_TIME = strftime('%Y%b%d-%H%I');
		$EXPORT_TIME = "abo_order";
	// now either stream it to them or put it in the temp directory
	if ($download == 'stream'){
		//*******************************
		// STREAM FILE
		//*******************************
		header("Content-type: application/vnd.ms-excel");
		header("Content-disposition: attachment; filename=$EXPORT_TIME.txt");
		header("Pragma: no-cache");
		header("Expires: 0");
		echo $filestring;
		die();
	} else {
		//*******************************
		// PUT FILE IN TEMP DIR
		//*******************************
		$tmpfname = DIR_FS_CATALOG . $tempdir . "$EXPORT_TIME.txt";
		//unlink($tmpfname);
		$fp = fopen( $tmpfname, "w+");
		fwrite($fp, $filestring);
		fclose($fp);
		$sql = 'UPDATE orders SET abo_status = 99 WHERE abo_status = 0 AND orders_status IN ('.$avail_order_status.')';
                // " . tep_db_input($orders_status) . ";' ;
		$result = tep_db_query($sql);
		die();
	}
}   // *** END *** download section
?>

<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF">
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>

<table border="0" width="100%" cellspacing="2" cellpadding="2">
<tr>
<td width="<?php echo BOX_WIDTH; ?>" valign="top" height="27">
<table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="1" cellpadding="1" class="columnLeft">
<?php require(DIR_WS_INCLUDES . 'column_left.php');?>
</table></td>
<td class="pageHeading" valign="top"><?php
echo "Orders Downloader $curver - Default Language : " . $epdlanguage_name . '(' . $epdlanguage_id .')' ;

?>

<p class="smallText">
<?php echo $orders_status; ?>

		<p><b>Download Orders File</b></p>

	      <!-- Download file links -  Add your custom fields here -->
	  <a href="abo_order.php?download=tempfile&dltype=full&orders_status=<?php echo $orders_status ?>">Download <b>Pending</b> orders</a><br>
	  </td>
	</tr>
	
      </table>
    </td>
 </tr>
</table>

<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>

<p>�</p>
<p>�</p><p><br>
</p></body>
</html>

<?php

function ep_get_languages() {
	$languages_query = tep_db_query("select languages_id, code from " . TABLE_LANGUAGES . " order by sort_order");
	// start array at one, the rest of the code expects it that way
	$ll =1;
	while ($ep_languages = tep_db_fetch_array($languages_query)) {
		//will be used to return language_id en language code to report in product_name_code instead of product_name_id
		$ep_languages_array[$ll++] = array(
					'id' => $ep_languages['languages_id'],
					'code' => $ep_languages['code']
					);
	}
	return $ep_languages_array;
};


function print_el( $item2 ) {
	echo " | " . substr(strip_tags($item2), 0, 10);
};

function print_el1( $item2 ) {
	echo sprintf("| %'.4s ", substr(strip_tags($item2), 0, 80));
};
function ep_create_filelayout($dltype, $orders_status){
#	echo $dltype;
#	echo $orders_status;
	
	global $filelayout, $filelayout_count, $filelayout_sql, $langcode, $fileheaders, $max_categories, $avail_order_status;
	// depending on the type of the download the user wanted, create a file layout for it.
	$fieldmap = array(); // default to no mapping to change internal field names to external.
		// The file layout is dynamically made depending on the number of languages
		$iii = 0;
		$filelayout = array(
			'customers_id'	=> $iii++,
			'orders_id'		=> $iii++,
			'date_purchased'	=> $iii++,
			'customers_name'	=> $iii++,
			'customers_company'	=> $iii++,                        
			'customers_telephone'	=> $iii++,
			'customers_gsm'	        => $iii++,
			'customers_email_address'=> $iii++,
			'billing_name'		=> $iii++,
			'billing_street_address'=> $iii++,
			'billing_city'		=> $iii++,
			'billing_postcode'	=> $iii++,
			'billing_state'		=> $iii++,
			'billing_country'	=> $iii++,
			'billing_tva_intracom'  => $iii++,
			'delivery_name'		=> $iii++,
			'delivery_street_address'=> $iii++,
			'delivery_city'		=> $iii++,
			'delivery_postcode'	=> $iii++,
			'delivery_state'	=> $iii++,
			'delivery_country'	=> $iii++,
			'items_ordered'		=> $iii++,
			'quantity_ordered'	=> $iii++,
			'price_ordered'		=> $iii++,
			'order_total'		=> $iii++,
			'payment_method'	=> $iii++,
			'cc_type'		=> $iii++,
			'cc_owner'		=> $iii++,
			'cc_number'		=> $iii++,
			'cc_expires'		=> $iii++,
			'comment'		=> $iii++
	);

if ($orders_status){

		$filelayout_sql = "SELECT
			o.customers_id as customers_id,
			o.orders_id as orders_id,
			o.date_purchased as date_purchased,
			o.customers_name as customers_name,
			o.customers_company as customers_company,
			o.customers_telephone as customers_telephone,
			o.customers_gsm as customers_gsm,
			o.customers_email_address as customers_email_address,
			o.billing_name as billing_name,
			o.billing_street_address as billing_street_address,
			o.billing_city as billing_city,
			o.billing_postcode as billing_postcode,
			o.billing_state as billing_state,
			o.billing_country as billing_country,
			o.billing_tva_intracom as billing_tva_intracom,
			o.delivery_name as delivery_name,
			o.delivery_street_address as delivery_street_address,
			o.delivery_city as delivery_city,
			o.delivery_postcode as delivery_postcode,
			o.delivery_state as delivery_state,
			o.delivery_country as delivery_country,
			op.products_model as items_ordered,
			op.products_quantity as quantity_ordered,
			op.products_price as price_ordered,
			ott.value as order_total,
                        o.payment_method as payment_method,
			o.cc_type as cc_type,
			o.cc_owner as cc_owner,
			o.cc_number as cc_number,
			o.cc_expires as cc_expires,
                        oh.comments as comment
			FROM ".TABLE_ORDERS." as o 
                        LEFT JOIN ".TABLE_ORDERS_PRODUCTS." as op
                            ON o.orders_id = op.orders_id
			LEFT JOIN ".TABLE_ORDERS_STATUS_HISTORY." as oh
                            ON o.orders_id = oh.orders_id AND oh.orders_status_id = 1
			LEFT JOIN ".TABLE_ORDERS_TOTAL." as ott
                            ON o.orders_id = ott.orders_id AND ott.class = 'ot_total'
			WHERE
			o.abo_status= 0 AND o.orders_status IN (".$avail_order_status.") ;";

}else{
		$filelayout_sql = "SELECT
			o.customers_id as customers_id,
			o.orders_id as orders_id,
			o.date_purchased as date_purchased,
			o.customers_name as customers_name,
			o.customers_company as customers_company,
			o.customers_telephone as customers_telephone,
			o.customers_gsm as customers_gsm,
			o.customers_email_address as customers_email_address,
			o.billing_name as billing_name,
			o.billing_street_address as billing_street_address,
			o.billing_city as billing_city,
			o.billing_postcode as billing_postcode,
			o.billing_state as billing_state,
			o.billing_country as billing_country,
			o.billing_tva_intracom as billing_tva_intracom,
			o.delivery_name as delivery_name,
			o.delivery_street_address as delivery_street_address,
			o.delivery_city as delivery_city,
			o.delivery_postcode as delivery_postcode,
			o.delivery_state as delivery_state,
			o.delivery_country as delivery_country,
			op.products_model as items_ordered,
			op.products_quantity as quantity_ordered,
			op.products_price as price_ordered,
			ott.value as order_total,
                        o.payment_method as payment_method,
			o.cc_type as cc_type,
			o.cc_owner as cc_owner,
			o.cc_number as cc_number,
			o.cc_expires as cc_expires,
                        oh.comments as comment
			FROM ".TABLE_ORDERS." as o 
                        LEFT JOIN ".TABLE_ORDERS_PRODUCTS." as op
                            ON o.orders_id = op.orders_id
			LEFT JOIN ".TABLE_ORDERS_STATUS_HISTORY." as oh
                            ON o.orders_id = oh.orders_id AND oh.orders_status_id = 1
			LEFT JOIN ".TABLE_ORDERS_TOTAL." as ott
                            ON o.orders_id = ott.orders_id AND ott.class = 'ot_total'
			WHERE
			o.abo_status= 0 AND o.orders_status IN (".$avail_order_status.") ;";
}
	$filelayout_count = count($filelayout);

}


require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>