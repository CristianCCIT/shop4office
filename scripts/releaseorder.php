<?
include('dbconfig.php');
include('library.php');
connect2DB(); // as per values specified in config file

if ( isset($_GET['order']) )
{
	$query = "SELECT * FROM orders WHERE orders_id ='" . $_GET['order'] . "'";
	if ( recordExists($query) )
	{
		// Update the record
		$query = "UPDATE orders SET abo_status = '0', orders_status = '1' WHERE orders_id ='" . $_GET['order'] . "'";
		if (updateRecord($query) )
		{
			echo "Order " . $_GET['order'] . " geinitialiseerd.";
		}
		else
		{
			echo "Er deed zich een fout voor, gelieve opnieuw te proberen.";
		}
	}
	else
	{
		echo "Er is geen order gevonden met id " . $_GET['order'];
	}
}
else
{
	echo "Gelieve een order nummer in te geven.";
}
?>