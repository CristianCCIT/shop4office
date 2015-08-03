<?
	include('dbconfig.php');
	include('library.php');

    connect2DB(); // as per values specified in config file

	if ( isset($_GET['filename']) )

	{

		$filename = "../temp/" . $_GET['filename'];
		//echo $filename;
		if ( is_file($filename) )

		{

			$fcontents = file ($filename); 
			//echo count($fcontents) . "<br>";
			for($i=0; $i<sizeof($fcontents); $i++) { 

				$line = trim($fcontents[$i]); 
				//echo $line."$i<br>";
				if ( $line == "" )
					continue;
				$arr = explode(";", $line); 
				//echo "<br>".$arr[0]."$".$arr[1]."$".$arr[2]."$".$arr[4]."$<br>";
				#if your data is comma separated

				# instead of tab separated, 

				# change the '\t' above to ',' 

				
				// Check if the record exists
				
			    $query = "SELECT * FROM " . cartridges . " WHERE brand='" . $arr[0] . "' AND product_model='".$arr[3]."'";
//		echo $query."<br>";
				if ( recordExists($query) )
				{
					// Update the record
					$query = "UPDATE " . cartridges . " SET Printer_type='".$arr[1]."',Cartridge_type='".$arr[2]."' WHERE Brand='" . $arr[0] . "' AND product_model='".$arr[3]."'";
					if (updateRecord($query) )
					{
						echo "Record Updated for the Catridge Brand='" . $arr[0] . "' AND product_model='".$arr[3]."'<br>";
					}
				}
				else
				{
					// Add new record
					$sql = "insert into " . cartridges . " values ('". 
	
					  implode("','", $arr) ."')"; 
	
					mysql_query($sql);
	
			//		echo $sql ."<br>\n";
	
					if(mysql_error()) {
	
						echo mysql_error() ."<br>\n";
	
					} 
					else
					{
						echo "Record Added for the Catridge Brand='" . $arr[0] . "' AND product_model='".$arr[3]."'"."<br>";
					}
				}

			}

		}

		else

		{

			die("The filename you have specified does not exist");

		}

	}

	else

	{

		die('Please specify a valid filename to load data from');

	}

 

?>

