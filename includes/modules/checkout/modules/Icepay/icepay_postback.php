<?php
chdir('../../../../../');
require('includes/configure.php');
//PDO Connection
try {
	$pdo = new PDO('mysql:host='.DB_SERVER.';dbname='.DB_DATABASE, DB_SERVER_USERNAME, DB_SERVER_PASSWORD);
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); //only in development mode
} catch(PDOException $e) {
	echo 'ERROR: '.$e->getMessage();
}
//load files first so that the session vars can be reused.
require_once('includes/modules/checkout/Analytics_module.php');
require_once('includes/modules/checkout/Modules.php');
require_once('includes/modules/checkout/Checkout.php');
//load all module files so that they can be used from $_SESSION
// @TODO delete this in cms 2.0 and find better way
// this has to be loaded before application_top because session vars needs the files for class definition
if (is_dir(DIR_FS_CATALOG.'includes/modules/checkout/modules/Icepay'))  {
	$object = glob(DIR_FS_CATALOG.'includes/modules/checkout/modules/Icepay/*_module.php');
	require_once($object[0]);
}
$temp_orders_id = $_POST['Reference'];
// @TODO End Of Delete
require_once('includes/application_top.php'); 
$Analytics = new Analytics();
if (!is_object($Modules)) {
	//start modules class
	$Modules = new Modules();
}
$Checkout = Checkout::instance(false);
$temp_data = $Checkout->get_all_data_from_temp_db($temp_orders_id);//get all orders data
$icepay = new Icepay_Postback();
$icepay->setMerchantID($Icepay->instances[$temp_data[$temp_orders_id]['orders']['payment_method']]['merchant_id'])
        ->setSecretCode($Icepay->instances[$temp_data[$temp_orders_id]['orders']['payment_method']]['secret_code'])
        ->enableLogging()
        ->logToFile(true, realpath("../logs"))
        ->logToScreen();
$data = '';
foreach($_POST as $key=>$value) {
	$data .= $key.': '."\n";
	$data .= $value."\n\n";
}
tep_db_query('INSERT INTO payment_log (type, data, date) VALUES ("Icepay", "'.$data.'", NOW())');
tep_db_query('DELETE FROM payment_log WHERE date < DATE_SUB(NOW(), INTERVAL 30 DAY)');
try {
    if($icepay->validate()){
		if ($temp_data[$temp_orders_id]['orders']['processed_order_id'] > 0) {
			//Update order status
			switch ($icepay->getStatus()){
				case Icepay_StatusCode::OPEN:
					//do nothing
					break;
				case Icepay_StatusCode::SUCCESS:
					tep_db_query('UPDATE orders SET orders_status = 2 WHERE orders_id = "'.$temp_data[$temp_orders_id]['orders']['processed_order_id'].'"');
					Checkout::send_order_error_mail(Translate('Icepay betaling is goedgekeurd voor weborder').': '.$temp_data[$temp_orders_id]['orders']['processed_order_id'], sprintf(Translate('De betaling voor weborder %s is goedgekeurd door Icepay.'), $temp_data[$temp_orders_id]['orders']['processed_order_id']));
					break;
				case Icepay_StatusCode::ERROR:
					//Redirect to cart
					tep_db_query('UPDATE orders SET orders_status = 53 WHERE orders_id = "'.$temp_data[$temp_orders_id]['orders']['processed_order_id'].'"');
					Checkout::send_order_error_mail(Translate('Ongeldige Icepay betaling voor weborder').': '.$temp_data[$temp_orders_id]['orders']['processed_order_id'], sprintf(Translate('De betaling voor weborder %s is ongeldig verklaard door Icepay.'), $temp_data[$temp_orders_id]['orders']['processed_order_id']));
					break;
				case Icepay_StatusCode::CHARGEBACK:
					//Redirect to cart
					tep_db_query('UPDATE orders SET orders_status = 51 WHERE orders_id = "'.$temp_data[$temp_orders_id]['orders']['processed_order_id'].'"');
					Checkout::send_order_error_mail(Translate('Terugboeking Icepay betaling gestart voor weborder').': '.$temp_data[$temp_orders_id]['orders']['processed_order_id'], sprintf(Translate('De terugboeking voor weborder %s is gestart.'), $temp_data[$temp_orders_id]['orders']['processed_order_id']));
					break;
				case Icepay_StatusCode::REFUND:
					//Redirect to cart
					tep_db_query('UPDATE orders SET orders_status = 52 WHERE orders_id = "'.$temp_data[$temp_orders_id]['orders']['processed_order_id'].'"');
					Checkout::send_order_error_mail(Translate('Icepay betaling is terugbetaald voor weborder').': '.$temp_data[$temp_orders_id]['orders']['processed_order_id'], sprintf(Translate('De betaling voor weborder %s is terugbetaald.'), $temp_data[$temp_orders_id]['orders']['processed_order_id']));
					break;
			}
		} else {
			//Order wasn't made yet...
		}
    } else {
		die ("Unable to validate postback data");
	}
} catch (Exception $e){
    echo($e->getMessage());
}
?>