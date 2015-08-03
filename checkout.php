<?php
require('includes/configure.php');
//PDO Connection
try {
        $pdo = new PDO('mysql:host='.DB_SERVER.';dbname='.DB_DATABASE.';charset=utf8', DB_SERVER_USERNAME, DB_SERVER_PASSWORD);
        $pdo->exec("set names utf8");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); //only in development mode
} catch(PDOException $e) {
        echo 'ERROR: '.$e->getMessage();
}
//temporary orders_id, for temp_orders table
//saved in cookie AND session to be sure we stay working in the right (temp)order
if (!isset($_COOKIE['temp_orders_id'])) {
        $temp_orders_id = 0;
        setcookie('temp_orders_id', $temp_orders_id, time()+60*60*24*1, '/'); //available for 1 day
} else {
        $temp_orders_id = $_COOKIE['temp_orders_id'];
}
//load files first so that the session vars can be reused.
require_once('includes/modules/checkout/classes/Analytics_module.php');
require_once('includes/modules/checkout/Modules.php');
require_once('includes/modules/checkout/Checkout.php');
//load all module files so that they can be used from $_SESSION
// @TODO delete this in cms 2.0 and find better way
// this has to be loaded before application_top because session vars needs the files for class definition
if (is_dir(DIR_FS_CATALOG.'includes/modules/checkout/modules/')) {
        $dirHandle = opendir(DIR_FS_CATALOG.'includes/modules/checkout/modules/');
        while(false !== ($module = readdir($dirHandle))) {
                if (is_dir(DIR_FS_CATALOG.'includes/modules/checkout/modules/'.$module) && $module != '.' && $module != '..')  {
                        $object = glob(DIR_FS_CATALOG.'includes/modules/checkout/modules/'.$module.'/*_module.php');
                        require_once($object[0]);
                }
        }
}
// @TODO End Of Delete
require_once('includes/application_top.php');
if ($cart->count_contents() < 1) {
        tep_redirect(tep_href_link('shopping_cart.php'));
}
if (!tep_session_is_registered('temp_orders_id')) {
        tep_session_register($temp_orders_id);
}
//start logging class
$Analytics = new Analytics();
if (!is_object($Modules)) {
        //start modules class
        $Modules = new Modules();
}
$last_modified_time = filemtime(__FILE__);
header("Last-Modified: ".date("D, d M Y H:i:s", $last_modified_time)." GMT");
header("Content-Language: ".$languages_code);
header("content-type: text/html; charset: utf-8");
?>
<!DOCTYPE html>
<html lang="<?php echo $languages_code;?>">
<head>
        <meta charset="utf-8">
        <title><?php echo STORE_NAME;?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <!-- Le styles -->
        <link rel="stylesheet" type="text/css" media="screen" href="<?php echo $Modules->generateCSSFile();?>" />
        <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
        <!--[if lt IE 9]>
        <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
        <![endif]-->
        <script src="http://code.jquery.com/jquery-latest.min.js" type="text/javascript"></script>
        
<script type="text/javascript">
function openexpwindow(curl){
window.open(curl,"Conditions","toolbar=no,location=no,directories=no,status=yes,scrollbars=yes,resizable=yes,copyhistory=no,width=500,height=300");
}
</script>
</head>
<body>
        <div class="container">
                <div class="row">
                        <div class="span6">
                                <div class="logo"></div>
                        </div>
                        <div class="span6">
                                <?php
                                tep_get_module('checkout_top');
    // nikhil
    if($_POST['TermsAgree'] && $_POST['Customers_info_password'] != '' && $_POST['Customers_info_customers_email_address'] != '' && $_POST['Customers_info_billing_lastname'] != '' && $_POST['Customers_info_billing_street_address'] != '' && $_POST['Customers_info_billing_postcode'] != ''
     && $_POST['Customers_info_billing_city'] != '' && $_POST['Customers_info_billing_country'] != '' && $_POST['Customers_info_customers_telephone'] != '' && strlen($_POST['Customers_info_billing_lastname']) > '2'){
        $firstname          = tep_db_prepare_input($_POST['Customers_info_billing_firstname']);
        $lastname           = tep_db_prepare_input($_POST['Customers_info_billing_lastname']);
        $street_address     = tep_db_prepare_input($_POST['Customers_info_billing_street_address']);
        $postcode           = tep_db_prepare_input($_POST['Customers_info_billing_postcode']);
        $city               = tep_db_prepare_input($_POST['Customers_info_billing_city']);
        $country            = tep_db_prepare_input($_POST['Customers_info_billing_country']);
        $telephone          = tep_db_prepare_input($_POST['Customers_info_customers_telephone']);
        $email_address      = tep_db_prepare_input($_POST['Customers_info_customers_email_address']);
        $password           = tep_db_prepare_input($_POST['Customers_info_password']);

          $check_email_query = tep_db_query("select count(*) as total from " . TABLE_CUSTOMERS . " where customers_email_address = '" . tep_db_input($email_address) . "'");
          $check_email = tep_db_fetch_array($check_email_query);
          if ($check_email['total'] > 0) {?>

            <script type="text/javascript">
            $(document).ready(function() {
            $('#emailCheck').css({ 'display': 'block'});
            $("#TermsAgree").removeAttr("checked");
            $('#Customers_info_input_password').val('');
            $('#Customers_info_input_password').attr("placeholder", "Wachtwoord");
            $('#Customers_info_input_password2').val('');
            $('#Customers_info_input_password2').attr("placeholder", "Wachtwoord bevestigen");
            });
            </script>

       <?php    } else  {

             $sql_data_array = array('customers_firstname' => $lastname.' '.$firstname,
                                      'customers_lastname' => '',
                                      'customers_email_address' => $email_address,
                                      'customers_telephone' => $telephone,
                                      'customers_password' => tep_encrypt_password($password),
                                      'status' => '1');

              tep_db_perform(TABLE_CUSTOMERS, $sql_data_array);
              $customer_id = tep_db_insert_id();

             $sql_data_array1 = array('customers_id' => $customer_id,
                                      'entry_firstname' => $lastname.' '.$firstname,
                                      'entry_lastname' => '',
                                      'entry_street_address' => $street_address,
                                      'entry_postcode' => $postcode,
                                      'entry_city' => $city,
                                      'entry_country_id' => $country);

              tep_db_perform(TABLE_ADDRESS_BOOK, $sql_data_array1);
              $address_id = tep_db_insert_id();

              tep_db_query("update " . TABLE_CUSTOMERS . " set customers_default_address_id = '" . (int)$address_id . "' where customers_id = '" . (int)$customer_id . "'");
              tep_db_query("insert into " . TABLE_CUSTOMERS_INFO . " (customers_info_id, customers_info_number_of_logons, customers_info_date_account_created) values ('" . (int)$customer_id . "', '0', now())");

        } ?>
 <?php   }
    // nikhil
 ?>
                        </div>
                </div>
                <div class="row">
                        <div class="span8">
                                <?php
                                $Checkout = Checkout::instance();
                                ?>
                        </div>
                        <div class="span4 active summary">
                                <div class="step_title"><?php echo Translate('Overzicht');?></div>
                                <?php
                                foreach(Checkout::$checkout_steps['summary']['modules'] as $type) {
                                        foreach($Modules->modules[$type] as $module) {
                                                echo $$module->output();
                                        }
                                }
                                ?>
                                <a href="<?php echo tep_href_link(FILENAME_DEFAULT);?>" title="<?php echo Translate('Keer terug naar de shop en vervolledig uw bestelling.');?>"><?php echo Translate('Ik ben iets vergeten!');?></a>
                                <?php tep_get_module('checkout_right');?>
                        </div>
                </div>
                <div class="row">
                        <div class="span12">
                                <?php tep_get_module('checkout_bottom');?>
                        </div>
                </div>
        </div>
        <script type="text/javascript" src="<?php echo $Modules->generateJSFile();?>"></script>
</body>
</html>
<?php
//Close PDO connection
$pdo = null;
if(extension_loaded('apc') && ini_get('apc.enabled')) {
        apc_clear_cache();
}
require_once('includes/application_bottom.php');
?>
