<?php
include_once('includes/application_top.php');
$data = "";
$countNo = 0;
$countYes = 0;
$products_query = tep_db_query('SELECT * FROM products');
while ($product = tep_db_fetch_array($products_query)) {
	$file = trim($product['products_image']);
	$status = trim($product['products_status']);
	$imgPath = "../images/".$product['products_image'];
	if ( ($file == "")|| (!file_exists($imgPath)) ) {
		$countNo++;
		if ($status == "1") {
			$data .= $product['products_model'] . "\r\n";
		}
	} else {
		$countYes++;
	}
}
//echo 'no: '.$countNo.'<br />yes: '.$countYes.'<br />';
//echo "Result: " . $data;
if ( $data != "" ) {
	$filename = "../temp/noimageproducts.csv";
	$handle = fopen($filename,"w+");
	fwrite($handle,$data);
	fclose($handle);
	echo "A CSV File has been generated with the name " . $filename;
} else {
	$filename = "../temp/noimageproducts.csv";
	$handle = fopen($filename,"w+");
	fwrite($handle,$data);
	fclose($handle);
	echo "There is no product with empty or invalid image field";
}
?>