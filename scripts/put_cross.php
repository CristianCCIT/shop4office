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
            mysql_query("TRUNCATE TABLE `products_cross`");
			$fcontents = file ($filename); 
			//echo count($fcontents) . "<br>";
			for($i=0; $i<sizeof($fcontents); $i++) { 

				$line = trim($fcontents[$i]); 
				echo $line."$i<br>";
				if ( $line == "" )
					continue;
				$arr = explode(";", $line); 
				//echo "<br>".$arr[0]."$".$arr[1]."$".$arr[2]."$".$arr[4]."$<br>";
				#if your data is comma separated

				# instead of tab separated, 

				# change the '\t' above to ',' 

				{
					// Add new record
					$sql = "insert into products_cross values ('". 
	
					  implode("','", $arr) ."')"; 
	
					mysql_query($sql);
	
					if(mysql_error()) {
	
						echo mysql_error() ."test<br>\n";
	
					} 
					else
					{
						echo "Cross Record added<br>";
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