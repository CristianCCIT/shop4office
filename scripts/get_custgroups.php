<?php
/* 
  Script created by ABO Service 
  
  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2002 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');

function Table_Exists($table_name)
{
         $Table = mysql_query("show tables like '" . 
                  $table_name . "'");
         
         if(mysql_fetch_row($Table) === false)
            return(false);
         
         return(true);
}

$filename = DIR_FS_CATALOG . '/temp/custgroups.csv';

if(Table_Exists("groups"))  {
	$group_query_raw = "SELECT groups_id, groups_name FROM " . TABLE_GROUPS;
    $group_query = tep_db_query($group_query_raw);
//  $num_rows = tep_db_num_rows($product_query);
  while ($groups = tep_db_fetch_array($group_query)) {
//	  $rows++;
//	    if (strlen($rows) < 2) {
//      $rows = '0' . $rows;
//    }		
	$csv_accum .= $groups['groups_id'] . ";" . $groups['groups_name'] . "\n"; 
  } 
  $melding = "Bestand klantgroepen werd aangemaakt: " . $filename;
}
else
{
  $csv_accum = '';
  $melding = "Tabel groups bestaat niet!";
}

  $f = fopen($filename, "w");
  fwrite($f, $csv_accum);
  fclose($f);
			
  echo $melding;

  require(DIR_WS_INCLUDES . 'application_bottom.php');
  
  

?>