<?php
function put_user_in_list($list_id, $type, $email_address, $name){	
	global $all_lists;
	//check for existing by email address
	tep_db_list_connect();
	$existing_email_query = tep_db_list_query("select id, email from " . PHPLIST_TABLE_PREFIX . "user_user where email = '" . $email_address . "'");
	
	$history_systeminfo_text = "\nHTTP_USER_AGENT = " . $_SERVER["HTTP_USER_AGENT"] ."\nREMOTE_ADDR = " . $_SERVER["REMOTE_ADDR"] . "";
	$history_detail_text = "";
	$getListName_query = tep_db_list_query('SELECT name FROM ' . PHPLIST_TABLE_PREFIX . 'list WHERE id = "'.$list_id.'"');
	$getListName = tep_db_fetch_array($getListName_query);
	$listName = $getListName['name'];
	

 if ($type == 'subscribe') { 		
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
			$history_detail_text .= "" . $attribute_name[name] . " = " . $name . "\n";
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
				$history_detail_text .= $all_lists;
				//subscribe the new user to the correct list
				tep_db_list_query("insert into " . PHPLIST_TABLE_PREFIX . "listuser (userid, listid, entered) values (" . $existing_email['id'] . ", " . $list_id . ", now())"); 
				
				$all_lists .= $listName.', ';
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
} else if ($type == 'unsubscribe') {	
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
function check_if_subscribed($email_address, $list_id) {
	tep_db_list_connect();
	$existing_email_query = tep_db_list_query("select id, email from " . PHPLIST_TABLE_PREFIX . "user_user where email = '" . $email_address . "'");
	$listname = getListName($list_id);
	tep_db_list_connect();
	if (tep_db_num_rows($existing_email_query) > 0 ) {
		$existing_email = tep_db_fetch_array($existing_email_query);
		$list = tep_db_list_query("SELECT userid, listid from " . PHPLIST_TABLE_PREFIX . "listuser where listid = '" . $list_id . "' and userid = '" . $existing_email['id'] . "'");
		if (tep_db_num_rows($list) > 0 ) {
			tep_db_list_close();
			tep_db_connect();
			return $listname.'|1';
		} else {
			tep_db_list_close();
			tep_db_connect();
			return $listname.'|0';
		}
	} else {
		tep_db_list_close();
		tep_db_connect();
		return $listname.'|0';
	}
	tep_db_list_close();
	tep_db_connect();
}
function getListName($list_id) {
	tep_db_list_connect();
	$listname_query = tep_db_list_query('SELECT name FROM '.PHPLIST_TABLE_PREFIX.'list WHERE id="'.$list_id.'"');
	$listname = tep_db_fetch_array($listname_query);
	tep_db_list_close();
	tep_db_connect();
	return $listname['name'];
}
?>