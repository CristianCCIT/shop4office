<?

define('DB_SERVER','localhost');

define('DB_NAME','a5353abo_basis');

define('DB_USERNAME','a5353abo_admin');

define('DB_PASSWORD','narf39');



// Table Names

define('TBL_PRODUCTS_TRACK','products_track');



function connect2DB()

{

	mysql_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD) or die("Could not connect to the database");

	mysql_select_db(DB_NAME) or die("Could not select the Database-> " . DB_NAME);

}

?>