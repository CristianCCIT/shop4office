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