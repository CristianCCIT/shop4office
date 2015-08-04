<?php
/**
 * Created by PhpStorm.
 * User: Cristian
 * Date: 5/7/2015
 * Time: 7:47 AM
 */

function xD3bug($data, $exit=false, $ip='82.208.181.52')
{
	if($_SERVER['REMOTE_ADDR'] == $ip){
		if(is_callable($data)){
			$data = $data;
		}

		if($data){
			echo '<pre style="padding: 10px; background-color: #FFF0A5; border: 2px solid #8E2800; color: #468966; border-radius: 5px">',
			'<h1 style="color: #B64926">xD3bug</h1>',
			print_r($data,1),
			'</pre>';
		}

		if($exit){
			exit(0);
		}
	}
}

function getSiteLanguage()
{
	if (!tep_session_is_registered('language') || isset($_GET['language'])) {
		if (!tep_session_is_registered('language')) {
			tep_session_register('language');
			tep_session_register('languages_id');
			tep_session_register('languages_code');
		}
		include(DIR_WS_CLASSES . 'language.php');
		$lng = new language();
		if (isset($_GET['language']) && tep_not_null($_GET['language'])) {
			$lng->set_language($_GET['language']);
		} else {
			$lng->get_browser_language();
			if (empty($lng)) {
				$lng->set_language(DEFAULT_LANGUAGE);
			}
		}

		return array(
			'dir' => $lng->language['directory'],
			'id' => $lng->language['id'],
			'code' => $lng->language['code']
		);
	}

	return array(
		'dir' => 'dutch',
		'id' => '1',
		'code' => 'nl'
	);
}

function queryToArray($query)
{
	$data = array();

	if($query){
		$res = tep_db_query($query);

		while($tmp = tep_db_fetch_array($res)){
			$data[] = $tmp;
		}
	}

	return $data;
}