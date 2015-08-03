<?php
/*
  $Id: cache.php 1739 2007-12-20 00:52:16Z hpdl $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2006 osCommerce

  Released under the GNU General Public License
*/

////
//! Write out serialized data.
//  write_cache uses serialize() to store $var in $filename.
//  $var      -  The variable to be written out.
//  $filename -  The name of the file to write to.
  function write_cache(&$var, $filename) {
	  if (USE_CACHE=='true') {
		if (DIR_FS_CACHE_PATH=='relative') {
			$cache_path = DIR_FS_CATALOG.DIR_FS_CACHE;
		} else {
			$cache_path = DIR_FS_CACHE;
		}
		$filename = $cache_path . $filename;
		$success = false;
	
	// try to open the file
		if ($fp = @fopen($filename, 'w')) {
	// obtain a file lock to stop corruptions occuring
		  flock($fp, 2); // LOCK_EX
	// write serialized data
		  fputs($fp, serialize($var));
	// release the file lock
		  flock($fp, 3); // LOCK_UN
		  fclose($fp);
		  $success = true;
		}
	
		return $success;
	} else {
		return false;	
	}
  }

////
//! Read in seralized data.
//  read_cache reads the serialized data in $filename and
//  fills $var using unserialize().
//  $var      -  The variable to be filled.
//  $filename -  The name of the file to read.
  function read_cache(&$var, $filename, $auto_expire = false){
	if (USE_CACHE=='true') {
		if (DIR_FS_CACHE_PATH=='relative') {
			$cache_path = DIR_FS_CATALOG.DIR_FS_CACHE;
		} else {
			$cache_path = DIR_FS_CACHE;
		}
		$filename = $cache_path . $filename;
		$success = false;
		
		if (($auto_expire == true) && file_exists($filename)) {
		  $now = time();
		  $filetime = filemtime($filename);
		  $difference = $now - $filetime;
		
		  if ($difference >= $auto_expire) {
			return false;
		  }
		}
		
		// try to open file
		if ($fp = @fopen($filename, 'r')) {
		// read in serialized data
		  $szdata = fread($fp, filesize($filename));
		  fclose($fp);
		// unserialze the data
		  $var = unserialize($szdata);
		
		  $success = true;
		}
		return $success;
	} else {
		return false;	
	}
  }

////
//! Get data from the cache or the database.
//  get_db_cache checks the cache for cached SQL data in $filename
//  or retreives it from the database is the cache is not present.
//  $SQL      -  The SQL query to exectue if needed.
//  $filename -  The name of the cache file.
//  $var      -  The variable to be filled.
//  $refresh  -  Optional.  If true, do not read from the cache.
  function get_db_cache($sql, &$var, $filename, $refresh = false){
    $var = array();

// check for the refresh flag and try to the data
    if (($refresh == true)|| !read_cache($var, $filename)) {
// Didn' get cache so go to the database.
//      $conn = mysql_connect("localhost", "apachecon", "apachecon");
      $res = tep_db_query($sql);
//      if ($err = mysql_error()) trigger_error($err, E_USER_ERROR);
// loop through the results and add them to an array
      while ($rec = tep_db_fetch_array($res)) {
        $var[] = $rec;
      }
// write the data to the file
      write_cache($var, $filename);
    }
  }

////
//! Cache the categories box
// Cache the categories box
  function tep_cache_categories_box($auto_expire = false, $refresh = false) {
    global $cPath, $language, $languages_id, $tree, $cPath_array, $categories_string;

    $cache_output = '';

    if (($refresh == true) || !read_cache($cache_output, 'categories_box-' . $language . '.cache', $auto_expire)) {
      ob_start();
      include(DIR_WS_BOXES . 'categories.php');
      $cache_output = ob_get_contents();
      ob_end_clean();
      write_cache($cache_output, 'categories_box-' . $language . '.cache');
    }

    return $cache_output;
  }

////
//! Cache the manufacturers box
// Cache the manufacturers box
  function tep_cache_manufacturers_box($auto_expire = false, $refresh = false) {
    global $_GET, $language;

    $cache_output = '';

    $manufacturers_id = '';
    if (isset($HTTP_GET_VARS['manufacturers_id']) && is_numeric($_GET['manufacturers_id'])) {
      $manufacturers_id = $_GET['manufacturers_id'];
    }

    if (($refresh == true) || !read_cache($cache_output, 'manufacturers_box-' . $language . '.cache' . $manufacturers_id, $auto_expire)) {
      ob_start();
      include(DIR_WS_BOXES . 'manufacturers.php');
      $cache_output = ob_get_contents();
      ob_end_clean();
      write_cache($cache_output, 'manufacturers_box-' . $language . '.cache' . $manufacturers_id);
    }

    return $cache_output;
  }
  
function tep_cache_product_finder($auto_expire = false, $refresh = false) {
	global $cPath, $_GET, $language, $languages_id, $cPath_array, $specification_group;
	if (($refresh == true) || !read_cache($cache_output, 'product_finder-' . $language . '.cache'.$cPath, $auto_expire)) {
		ob_start();
		include(DIR_WS_MODULES . 'product_finder.php');
		$cache_output = ob_get_contents();
		ob_end_clean();
		write_cache($cache_output, 'product_finder-' . $language . '.cache'.$cPath);
	}
	return $cache_output;
}
?>
