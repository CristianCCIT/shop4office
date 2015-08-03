<?php
/*
  $Id: products_new.php,v 1.27 2003/06/09 22:35:33 hpdl Exp $
  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com
  Copyright (c) 2003 osCommerce
  Released under the GNU General Public License
*/
	//require('includes/application_top.php');
	//require(DIR_WS_LANGUAGES . $language . '/' . FILENAME_NEWS);
	// Begin PHPlist Newsletter add-on
	include('includes/application_top.php'); //get the phplist specifics

  global $all_lists;
  $all_lists = '';

	function send_confirmation($name, $email_address, $listName){
		$enquiry = 'Welkom, u bent ingeschreven als abonnee op onze nieuwsbrief.<br />'."\n".'Uw emailadres werd toegevoegd als abonnee op volgende nieuwsbrief: '.$listName;
		$cmessage = $enquiry . "\n";
		tep_mail(STORE_NAME, $email_address, STORE_NAME, $cmessage, $name, STORE_OWNER_EMAIL_ADDRESS);
	}

	//get user specifics for PHPlist	
	$list_ids = $_GET['list'];
	foreach ($list_ids as $key=>$value) {
		$subscription = put_user_in_db_list($value);
	}
	tep_db_list_close();
	tep_db_connect();
	$subscription_text = Translate('Dit email adres is niet ingeschreven op onze nieuwsbrief.');
	if ($subscription == '1') {
		$subscription_text = Translate('U bent nu ingeschreven op onze nieuwsbrief.');
	} else if ($subscription == '2') {
		$subscription_text = Translate('U bent reeds ingeschreven voor de nieuwsbrief.');
	} else if ($subscription == '3') {
		$subscription_text = Translate('U bent nu uitgeschreven van onze nieuwsbrief.');
	}
	$all_lists = substr($all_lists, 0, -2);
	echo sprintf($subscription_text, $all_lists);
	
function put_user_in_db_list($list_id){	
	global $all_lists;
	$email_address = $_GET['email'];
	//check for existing by email address
	tep_db_list_connect();
	$existing_email_query = tep_db_list_query("select id, email from " . PHPLIST_TABLE_PREFIX . "user_user where email = '" . $email_address . "'");
	
	$history_systeminfo_text = "\nHTTP_USER_AGENT = " . $_SERVER["HTTP_USER_AGENT"] ."\nREMOTE_ADDR = " . $_SERVER["REMOTE_ADDR"] . "";
	$history_detail_text = "";
	$getListName_query = tep_db_list_query('SELECT name FROM ' . PHPLIST_TABLE_PREFIX . 'list WHERE id = "'.$list_id.'"');
	$getListName = tep_db_fetch_array($getListName_query);
	$listName = $getListName['name'];
	$firstname = $_GET['name'];
	

 if (isset($_GET['subscribe'])) { 		
		//subscribe logic
		if (tep_db_num_rows($existing_email_query) < 1) { //no existing user by email address found (therefore a new user - no id or email found)
		
			//generate unique id and add new user to database
			$id = md5(uniqid(mt_rand(0,1000).$email_address)); 
			
			 //insert the new user into phplist
			tep_db_list_query("insert into " . PHPLIST_TABLE_PREFIX . "user_user (email, confirmed, subscribepage, entered, modified, disabled, uniqid, htmlemail) 
			values ('" . $email_address . "', 1, " . PHPLIST_SPAGE . ", now(), now(), 0, '" . $id . "', " . PHPLIST_HTMLEMAIL . ")");
			
			//get the new user's phplist id
			$user_query=tep_db_list_query("select id from " . PHPLIST_TABLE_PREFIX . "user_user where email = '" . $email_address . "'"); 
			$user = tep_db_fetch_array($user_query); 
			
			//subscribe the new user to the correct list
			tep_db_list_query("insert into " . PHPLIST_TABLE_PREFIX . "listuser (userid, listid, entered) values (" . $user['id'] . ", " . $list_id . ", now())"); 
			
			//generating history
			$history_detail_text .= "\nSubscribepage = " . PHPLIST_SPAGE . "\n";
			$history_detail_text .= "" . $attribute_name[name] . " = " . $firstname . "\n";
			tep_db_list_query("insert into " . PHPLIST_TABLE_PREFIX . "user_user_history (userid, ip, date, summary, detail, systeminfo) values (" . $user['id'] . ", '" . $_SERVER["REMOTE_ADDR"] . "', '" . date('Y-m-d H:i:s') . "', 'Update through osC', '" . $history_detail_text . "', '" . $history_systeminfo_text . "')"); //create history post
			$all_lists .= $listName.', ';
			$return = '1';
		} else { //subscribe the existing user if disabled
			$existing_email = tep_db_fetch_array($existing_email_query); //existing user by email found
			tep_db_list_query("update " . PHPLIST_TABLE_PREFIX . "user_user set disabled = 0, confirmed = 1 where id = " . $existing_email['id'] . "");
			
			//check to see if they already are subscribed to the correct list
			$list_query = tep_db_list_query("select * from " . PHPLIST_TABLE_PREFIX . "listuser where userid = " . $existing_email['id'] . " and listid = " . $list_id . "");
			if ($list=tep_db_num_rows($list_query) < 1) { //no existing subscription to the newsletter found
			
				//generating history, previous subscriptions
				$history_detail_text .= "\n\nList subscriptions:\n";
				
				//subscribe the new user to the correct list
				tep_db_list_query("insert into " . PHPLIST_TABLE_PREFIX . "listuser (userid, listid, entered) values (" . $existing_email['id'] . ", " . $list_id . ", now())"); 
				
				$all_lists .= $listName.', ';
				send_confirmation($HTTP_POST_VARS['name'], $_GET['email'], $listName);
				$return =  '1';
			} else {
				$all_lists .= $listName.', ';
				$return = '2';
			}
			
			tep_db_list_query("insert into " . PHPLIST_TABLE_PREFIX . "user_user_history (userid, ip, date, summary, detail, systeminfo) values (" . $existing_email['id'] . ", '" . $_SERVER["REMOTE_ADDR"] . "', '" . date('Y-m-d H:i:s') . "', 'Update through osC', '" . $history_detail_text . "', '" . $history_systeminfo_text . "')"); //create history post
		}
		tep_db_list_close();
		tep_db_connect();
		return $return;
} else if (isset($_GET['unsubscribe'])) {	
	//unsubscribe logic	
	if (tep_db_num_rows($existing_email_query) > 0 ) {
		$history_detail_text = "\n";
		$existing_email = tep_db_fetch_array($existing_email_query);
		$testid = $existing_email['id'];

		$delete_subscription_query = tep_db_list_query("delete from " . PHPLIST_TABLE_PREFIX . "listuser where listid = '" . $list_id . "' and userid = '" . $existing_email['id'] . "'");
		
		mysql_query("delete from " . PHPLIST_TABLE_PREFIX . "listuser where listid = '" . $list_id . "' and userid = '" . $existing_email['id'] . "'") or die(mysql_error());  

		tep_db_list_query("insert into " . PHPLIST_TABLE_PREFIX . "user_user_history (userid, ip, date, summary, detail, systeminfo) values (" . $existing_email['id'] . ", '" . $_SERVER["REMOTE_ADDR"] . "', '" . date('Y-m-d H:i:s') . "', 'Update through osC', '" . $history_detail_text . "', '" . $history_systeminfo_text . "')"); //create history post
		$all_lists .= $listName.', ';
		tep_db_list_close();
		tep_db_connect();
		return '3';
	}
}
tep_db_list_close();
}
?>