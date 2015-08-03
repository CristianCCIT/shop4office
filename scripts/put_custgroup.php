<?php
/*
  Script created by ABO Service
  Exporteren klantengroepen vanuit ABO naar osCommerce
 
*/

  require('includes/application_top.php');

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

if ($data[0][0] === 'groups_id')
{
  $gid_col = 0;
  $gname_col = 1;
}
else
{
  $gname_col = 0;
  $gid_col = 1;
}

for ($t = 1; $t < $i; $t++)
{  
	$gid = $data[$t][$gid_col];
	$gname = $data[$t][$gname_col];
	verwerk($gid, $gname);
}

function verwerk($id, $naam)
{
  if ($id != 0)
  {
  	// checken of klantengroep bestaat (op id))
	$id_query = tep_db_query("select groups_id from " . TABLE_GROUPS . " where groups_id = '" . $id . "'");
	$row = mysql_fetch_array($id_query);
	if ($row != '')		// id bestaat
	{
		update($id, $naam);
	}
	else	// fout id
	{
	  echo "Fout! Id " . $id . " bestaat niet!";
	}
  }
  else	// id = 0 dus nieuwe klantengroep aanmaken
  {
	maak($naam);
  }	
}

function update($id, $naam)
{
	tep_db_query("update " . TABLE_GROUPS . " set groups_name = '" . $naam . "' where groups_id = " . $id);
}

function maak($naam)
{
	tep_db_query("insert into " . TABLE_GROUPS . " (groups_name) values ('" . $naam . "')");
}

require(DIR_WS_INCLUDES . 'application_bottom.php');

?>
