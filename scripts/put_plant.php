<?
include ('dbconfig.php');
include ('library.php');
connect2DB(); // as per values specified in config file
if (isset($_GET['filename']))
{
	$filename = "../temp/".$_GET['filename'];
	//echo $filename;
	if (is_file($filename))
	{
		$fcontents = file($filename);
		$prev_model = false;
		for ($i = 1; $i < sizeof($fcontents); $i++)
		{
			$line = trim($fcontents[$i]);
			if ($line == "") continue;
			$arr   = explode(";", $line);
			#if your data is comma separated
			# instead of tab separated,
			# change the '\t' above to ','
			// Check if the record exists

			if ($prev_model != $arr[0])
			{
				$prev_model = $arr[0];
				$query = "DELETE FROM products_plant WHERE products_model = '".$arr[0]."'";
				if (deleteFrom($query))
					if (mysql_affected_rows() > 0)
						echo "Sizes deleted for plant  = ".$arr[0].'<br />';
			}

			$query = "SELECT * FROM ".products_plant." WHERE products_model='".$arr[0]."' AND plant_maat='".$arr[3]."'";
			if (recordExists($query))
			{
				if ($arr[7] == 'DELETE') //Status
				{
					$query = "DELETE FROM products_plant WHERE products_model='".$arr[0]."' AND plant_maat = '".$arr[3]."'";
					if (deleteFrom($query)) echo "Record Delete for the plant = ".$arr[0]." AND plant_mmat = ".$arr[3]."<br />";
				}
				else
				{
					// Update the record
					$query = "UPDATE products_plant SET plant_sort='".$arr[1]."',plant_mc='".$arr[2]."',plant_description='".$arr[4]."',plant_price='".$arr[5]."',plant_vrij='".$arr[6]
						."' WHERE products_model='".$arr[0]."' AND plant_maat='".$arr[3]."'";
					if (updateRecord($query)) { echo "Record Updated for the plant ='".$arr[0]."' AND plant_maat=".$arr[3]."<br>"; }
				}
			}
			elseif ($arr[7] != 'DELETE')
			{
				$arr = array_slice($arr, 0, 7); //First 7 values;
				// Add new record
				$sql = "insert into products_plant (products_model, plant_sort, plant_mc, plant_maat, plant_description, plant_price, plant_vrij) values ('".implode("','", $arr)."')";
				mysql_query ($sql);
				//		echo $sql ."<br>\n";
				if (mysql_error()) { echo mysql_error()."<br>\n"; }
				else { echo "Record Added for the planten maten='".$arr[0]."' AND plant_maat=".$arr[3]."<br>"; }
			}
		}
	}
	else { die ("The filename you have specified does not exist"); }
}
else { die ('Please specify a valid filename to load data from'); }
?>