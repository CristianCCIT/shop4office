<?php
/*
  Script created by ABO Service
  Voorraad artikelen updaten (na alles op 0 gezet te hebben)
 
  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2002 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');

// overal voorraad op nul zetten in de shop
tep_db_query("update " . TABLE_PRODUCTS . " set products_quantity = 0");

$dir = '../temp/';
$newfile = $_REQUEST['filename'];

$separator = ';';

$filename = $dir . $newfile;

$data = array();

$fp = fopen ( $filename , "r" ); 
$i = 0;
while (( $data[] = fgetcsv ( $fp ,1000, $separator )) !== FALSE ) { 
$i++;
}
fclose ( $fp );

if ($data[0][0] === 'products_id')
{
  $id_col = 0;
  $quant_col = 1;
}
else
{
  $quant_col = 0;
  $id_col = 1;
}

for ($t = 1; $t < $i; $t++)
{  
	$id = $data[$t][$id_col];
	$quant = $data[$t][$quant_col];
	verwerk($id, $quant);
}

function verwerk($id, $quant)
{
	// checken of product-id bestaat
	$id_query = tep_db_query("select * from " . TABLE_PRODUCTS . " where products_id = " . $id);
	$row = mysql_fetch_array($id_query);
	if ($row != '')		// product-id bestaat
	{
		update($id, $quant);
	}
}

function update($id, $quant)
{
	tep_db_query("update " . TABLE_PRODUCTS . " set products_quantity = " . $quant . " where products_id = " . $id);
}

require(DIR_WS_INCLUDES . 'application_bottom.php');

?>