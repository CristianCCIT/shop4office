<?php

require('includes/application_top.php');

  function getSuggestions($keyword)
  {
  global $languages_id;
	$output_array = array();
    // escape the keyword string      
    $patterns = array('/\s+/', '/"+/', '/%+/');
    $replace = array('');
    $keyword = preg_replace($patterns, $replace, $keyword);
    $keyword = tep_db_input($keyword);
    // build the SQL query that gets the matching functions from the database
    if($keyword != '') {
      $query = 'SELECT pd.products_name, pd.products_description ' .
               'FROM products_description pd, products p ' .
               'WHERE (pd.products_name LIKE "%' . strtolower($keyword) . '%" OR pd.products_description LIKE "%' . strtolower($keyword) . '%") and p.products_id = pd.products_id and p.products_status = "1" and pd.language_id = "'.$languages_id.'"';
		$page_query = 'SELECT it.infopages_title, it.infopages_description, it.infopages_preview, i.infopages_id FROM infopages i, infopages_text it WHERE i.infopages_id = it.infopages_id AND (LOWER(it.infopages_title) LIKE "%'.strtolower($keyword).'%" OR LOWER(it.infopages_description) LIKE "%'.strtolower($keyword).'%" OR LOWER(it.infopages_preview) LIKE "%'.strtolower($keyword).'%")';
	} else {
      $query = 'SELECT pd.products_name ' .
               'FROM products_description pd, products p ' .
               'WHERE pd.products_name !="" and p.products_id = pd.products_id and p.products_status = "1" and pd.language_id = "'.$languages_id.'"';
		$page_query = 'SELECT infopages_title FROM infopages_text WHERE infopages_id < 0';
	}
    // execute the SQL query
    $result = tep_db_query($query);
    // build the XML response
    $output = '<?xml version="1.0" encoding="'.CHARSET.'" standalone="yes"'.'?'.'>';
    $output .= '<response>';    
    // if we have results, loop through them and add them to the output
    if(tep_db_num_rows($result)>0) {
      while ($row = tep_db_fetch_array($result)) {
	  	if ($keyword != '') {
			$pattern = "/[A-Za-z0-9._-]*".$keyword."[A-Za-z0-9._-]*/i";
			preg_match_all($pattern, $row['products_description'], $matches);
			foreach($matches as $key=>$value) {
				foreach($value as $keys=>$values) {
					$output_array[] = $values;
				}
			}
			preg_match_all($pattern, $row['products_name'], $matchesn);
			foreach($matchesn as $key=>$value) {
				foreach($value as $keys=>$values) {
					$output_array[] = $values;
				}
			}
		} else {
			$output_array[] = $row['products_name'];
		}
	  }
	}
	$page_result = tep_db_query($page_query);
	if(tep_db_num_rows($page_result)>0) {
      while ($page_row = tep_db_fetch_array($page_result)) {
	  	if ($keyword != '') {
			$pattern = "/[A-Za-z0-9]*".$keyword."[A-Za-z0-9]*/i";
			preg_match_all($pattern, $page_row['infopages_title'], $page_matchesT);
			foreach($page_matchesT as $key=>$value) {
				foreach($value as $keys=>$values) {
					$output_array[] = $values;
				}
			}
			preg_match_all($pattern, $page_row['infopages_description'], $page_matchesd);
			foreach($page_matchesd as $key=>$value) {
				foreach($value as $keys=>$values) {
					$output_array[] = $values;
				}
			}
			preg_match_all($pattern, $page_row['infopages_preview'], $page_matchesp);
			foreach($page_matchesp as $key=>$value) {
				foreach($value as $keys=>$values) {
					$output_array[] = $values;
				}
			}
		} else {
			$output_array[] = $page_row['infopages_title'];
		}
	  }
	}
	$output_array = array_unique($output_array);
	foreach ($output_array as $key=>$value) {
		$output .= '<name>' . $value . '</name>';
	}
    // close the result stream 
    // add the final closing tag
    $output .= '</response>';   
    // return the results
    return $output;  
  }

// retrieve the keyword passed as parameter
$keyword = $_GET['keyword'];
// clear the output 
if(ob_get_length()) ob_clean();
// headers are sent to prevent browsers from caching
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT' ); 
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . 'GMT'); 
header('Cache-Control: no-cache, must-revalidate'); 
header('Pragma: no-cache');
header('Content-Type: text/xml');
// send the results to the client
echo getSuggestions($keyword);
?>
