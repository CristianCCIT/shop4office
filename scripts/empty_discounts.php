<?php
  require('includes/application_top.php');

  tep_db_query("TRUNCATE TABLE `customers_discount`");
  echo "Kortingen werd leeggemaakt.";
			 
  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>