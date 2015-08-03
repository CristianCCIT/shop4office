<?
include('includes/application_top.php');
if (isset($_GET['filename'])) {
	$filename = "../temp/" . $_GET['filename'];
	if (is_file($filename)) {
		$fcontents = file ($filename); 
		for($i=1; $i<sizeof($fcontents); $i++) { 
			$last_products_model = $products_model;
			$line = trim($fcontents[$i]); 
			if ( $line == "" )
				continue;
			$arr = explode("\t", $line); 
			$products_model = $arr[0];
			$track_id = $arr[1];
			$select_query = tep_db_query("SELECT * FROM products_track WHERE products_model='".$products_model."'");
			if (tep_db_num_rows($select_query)>0) {
				if ($products_model!=$last_products_model) {
					tep_db_query('DELETE FROM products_track WHERE products_model="' . $products_model . '"');
					echo "Records Deleted for products_model='" . $products_model . "'<br />";
				}
			}
			tep_db_query('INSERT INTO products_track (products_model, track_id, track_artist, track_title, track_composer, track_lenght, track_sample) VALUES ("'.$arr[0].'","'.$arr[1].'","'.$arr[3].'","'.$arr[2].'","'.$arr[4].'","'.$arr[5].'","'.$arr[6].'")');
			echo "Record Added for the Track products_model='" . $products_model . "' AND track_id=".$track_id."<br />";
		}
	} else {
		die("The filename you have specified does not exist");
	}
} else {
	die('Please specify a valid filename to load data from');
}
?>