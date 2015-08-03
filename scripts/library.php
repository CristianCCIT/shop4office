<?php
function getSingleRecord($strSQL)
{ 
	if((@$bResult = mysql_query ($strSQL))==FALSE)
   	{}   
	else
 	{	
		if ($check=mysql_fetch_array($bResult))
   		{
      		return $check;
   		}
			return false;	
	}
}

function getMultipleRecords($strSQL)
{   
	if((@$bResult = mysql_query ($strSQL))==FALSE)
   	{}   
	else
 	{	
	    $count = 0;
		$data = array();
		while ( $row = mysql_fetch_array($bResult)) 
		{
			$data[$count] = $row;
			$count++;
		}
			return $data;
	}
}

function countRecords($Query)
{
   	if((@$bResult = mysql_query ($Query))==FALSE)
   	{}   
	else
 	{	
		if ($check=mysql_fetch_array($bResult))
   		{
      		return $check[0];
   		}
			return false;	
	}
}

function recordExists($strSQL)
{  
   if((@$bResult = mysql_query ($strSQL))==FALSE)
   	{}   
	else
 	{	
		if ($check=mysql_fetch_array($bResult))
   		{
      		return $check;
   		}
		return false;	
	}
}

function insertRecord($strSQL)
{	
   if((@$bResult = mysql_query ($strSQL))==FALSE)
   	{ return false;}   
	else
 	{	
		return true;	
	}
}

function updateRecord($strSQL)
{
	if((@$bResult = mysql_query ($strSQL))==FALSE)
   	{}   
	else
 	{	
		return true;
   	}
}
function deleteFrom($strSQL)
{
	if((@$bResult = mysql_query ($strSQL))==FALSE)
   	{}   
	else
 	{	
		return true;	
	}
}
function insertError($error) //insert error into session
{
	$_SESSION['error']  = $error;
}
function redirect($url)
{
	header("location: $url");
	exit();
}
function visitorMenu()
{
}
function userMenu()
{
}
?>