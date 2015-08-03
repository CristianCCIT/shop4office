<?php
/*
  Script created by ABO Service
  Vullen tabellen Kiala
 
  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2002 osCommerce

  Released under the GNU General Public License
*/

require('includes/application_top.php');

// functie om sleutels en waarden (2 arrays) samen te voegen in 1 array
if (!function_exists('array_combine')) {
   function array_combine($a, $b) {
	   $c = array();
	   if (is_array($a) && is_array($b))
		   while (list(, $va) = each($a))
			   if (list(, $vb) = each($b))
				   $c[$va] = $vb;
			   else
				   break 1;
	   return $c;
   }
} 

// **** VULLEN ZIPKPLIJST ****
// ophalen veldnamen tabel
$res2 = mysql_query("SHOW COLUMNS FROM " . TABLE_KIALA_BELUX_ZIPKPLIST);
while ($row2 = mysql_fetch_array($res2)) $col_names2[]=$row2[0];

$dir = '../temp/';
//$newfile = $_REQUEST['filename'];
$newfile2 = 'zipkplist0.csv';

$separator = ';';

$filename2 = $dir . $newfile2;

$data2 = array();

// vullen array met data csv-bestand
$fp2 = fopen ( $filename2 , "r" ); 
$j = 0;
while (( $data2[] = fgetcsv ( $fp2 ,1000, $separator )) !== FALSE ) { 
$j++;
}
fclose ( $fp2 );

$total_rows2 = count($data2) - 1;

// leegmaken tabel
//tep_db_query("TRUNCATE " . TABLE_KIALA_BELUX_ZIPKPLIST);

// veldnamen instellen als keys van array en vullen tabel
for($t=0; $t<$total_rows2; $t++)
{
  $kiala2 = array_combine($col_names2, $data2[$t]);
  tep_db_perform(TABLE_KIALA_BELUX_ZIPKPLIST, $kiala2);
}

require(DIR_WS_INCLUDES . 'application_bottom.php');
?>
