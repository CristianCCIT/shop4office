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

			for($i=1; $i<sizeof($fcontents); $i++) { 

				$line = trim($fcontents[$i]); 
				if ( $line == "" )
					continue;
				$arr = explode(";", $line); 

				#if your data is comma separated

				# instead of tab separated, 

				# change the '\t' above to ',' 

				
				// Check if the record exists
				
				// DELETE the record
				$query = "DELETE FROM products_kenmerken WHERE products_model='" . $arr[0] . "'" ;
        mysql_query($query) or die(mysql_error());

				// Add the record
				$sql = "insert into products_kenmerken values ('". 
	
					  implode("','", $arr) ."')"; 
	
					mysql_query($sql) or die(mysql_error());
	
			//		echo $sql ."<br>\n";
	
					if(mysql_error()) {
	
						echo mysql_error() ."<br>\n";
	
					} 
					else
					{
						echo "Record Added for  products_model='" . $arr[0] . "'<br>";
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

