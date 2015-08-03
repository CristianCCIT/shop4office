<?php
class GoogleSitemap{
	var $filename;
	var $savepath;
	var $base_url;
	var $debug;
	var $excludeList;

	function GoogleSitemap(){
		$this->filename = "sitemap";
		$this->savepath = /*'/'.*/DIR_FS_CATALOG;
		$this->base_url = HTTP_SERVER . DIR_WS_CATALOG;
		$this->debug = array();
    
		$this->excludeList = array();
		$exclude_sql = "SELECT `name` FROM `seo` WHERE language_id = '1' AND type='pages' AND value='1'";
		$exclude_query = tep_db_query($exclude_sql);
		while ($exclude =tep_db_fetch_array($exclude_query)) {
			$this->excludeList[] = $exclude['name'];
		} 
		$this->excludeList[] = 'getroot.php';
		$this->excludeList[] = 'advanced_search_result.php';
		$this->excludeList[] = 'popup_coupon_help.php';
		$this->excludeList[] = '404.php';
	}

	function SaveFile($data, $type){
		$filename = $this->savepath . $this->filename . $type;		
    if (strpos($filename, 'googlesitemap') !== FALSE)
    {
      $file_check = file_exists($filename) ? 'true' : 'false';
			$this->debug['SAVE_FILE_XML'][] = array('file' => $filename, 'status' => 'failure due to incorrect file name', 'file_exists' => $file_check);
			return false;
    }    	
		$compress = defined('GOOGLE_SITEMAP_COMPRESS') ? GOOGLE_SITEMAP_COMPRESS : 'false';
		if ($type == 'index') $compress = 'false';
		switch($compress){
			case 'true':
				$filename .= '.xml.gz';
				if ($gz = gzopen($filename,'wb9')){
					gzwrite($gz, $data);
					gzclose($gz);
					$this->debug['SAVE_FILE_COMPRESS'][] = array('file' => $filename, 'status' => 'success', 'file_exists' => 'true');
					return true;
				} else {
					$file_check = file_exists($filename) ? 'true' : 'false';
					$this->debug['SAVE_FILE_COMPRESS'][] = array('file' => $filename, 'status' => 'failure', 'file_exists' => $file_check);
					return false;
				}
				break;
			default:
				$filename .= '.xml';
         if (GOOGLE_XML_SITEMAP_SHOW_DIAGNOSTIC == 'true')
           echo 'Opening   '.$filename. '<br>FS_CAT    '.DIR_FS_CATALOG. '<br>Server    ' . HTTP_SERVER . '<br>Save Path '. $this->savepath . '<br>WS_CAT    '. DIR_WS_HTTP_CATALOG.' <br>';
				if ($fp = fopen($filename, 'w+')){
				     //echo 'Write '.$filename.'<br>';
					fwrite($fp, $data);
					fclose($fp);
					$this->debug['SAVE_FILE_XML'][] = array('file' => $filename, 'status' => 'success', 'file_exists' => 'true');
					return true;
				} else {
					$file_check = file_exists($filename) ? 'true' : 'false';
					$this->debug['SAVE_FILE_XML'][] = array('file' => $filename, 'status' => 'failure', 'file_exists' => $file_check);
					return false;
				}
				break;
		}
	}

	function CompressFile($file){
		$source = $this->savepath . $file . '.xml';
		$filename = $this->savepath . $file . '.xml.gz';
		$error_encountered = false;
		if( $gz_out = gzopen($filename, 'wb9') ){
			if($fp_in = fopen($source,'rb')){
				while(!feof($fp_in)) gzwrite($gz_out, fread($fp_in, 1024*512));
					fclose($fp_in);
				 
			} else {
				$error_encountered = true;
			}
			gzclose($gz_out);
		} else {
			$error_encountered = true;
		}
		if($error_encountered){
			return false;
		} else {
			return true;    
		}
	}

	function GenerateSitemap($data, $file){
		$content = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
		$content .= '<?xml-stylesheet type="text/xsl" href="gss.xsl"?>' . "\n";
		$content .= '<urlset xmlns="http://www.google.com/schemas/sitemap/0.84">' . "\n";
		foreach ($data as $url){
			$content .= "\t" . '<url>' . "\n";
			$content .= "\t\t" . '<loc>'.$url['loc'].'</loc>' . "\n";
			$content .= "\t\t" . '<lastmod>'.$url['lastmod'].'</lastmod>' . "\n";
			$content .= "\t\t" . '<changefreq>'.$url['changefreq'].'</changefreq>' . "\n";
			$content .= "\t\t" . '<priority>'.$url['priority'].'</priority>' . "\n";
			$content .= "\t" . '</url>' . "\n";
		}
		$content .= '</urlset>';
		return $this->SaveFile($content, $file);
	}

	function GenerateSitemapIndex($showInfopages, $showManufacturers, $showSpecials, $category_sitemap, $product_sitemap){
		$content = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
		$content = '<?xml-stylesheet type="text/xsl" href="gss.xsl"?>' . "\n"; //human readable
		$content .= '<sitemapindex xmlns="http://www.google.com/schemas/sitemap/0.84">' . "\n";		
		$pattern = defined('GOOGLE_SITEMAP_COMPRESS')
				     ?	GOOGLE_SITEMAP_COMPRESS == 'true'
					 		?	"{sitemap*.xml.gz}"
							: 	"{sitemap*.xml}"
					 :	"{sitemap*.xml}";
		foreach ( glob($this->savepath . $pattern, GLOB_BRACE) as $filename ) {
		   if ( preg_match('/index/i', $filename) ) continue;
		   if ( preg_match('/manufacturers/i', $filename) && !$showManufacturers ) continue;
		   if ( preg_match('/pages/i', $filename) && !$showInfopages) continue;
		   if ( preg_match('/specials/i', $filename) && !$showSpecials ) continue;
		   if ( preg_match('/categories/i', $filename) && !$category_sitemap ) continue;
		   if ( preg_match('/products/i', $filename) && !$product_sitemap ) continue;
		   $content .= "\t" . '<sitemap>' . "\n";
		   $content .= "\t\t" . '<loc>'.$this->base_url . basename($filename).'</loc>' . "\n";
		   $content .= "\t\t" . '<lastmod>'.date ("Y-m-d", filemtime($filename)).'</lastmod>' . "\n";
		   $content .= "\t" . '</sitemap>' . "\n";		   		
		}
		$content .= '</sitemapindex>';
		return $this->SaveFile($content, 'index');
	}

	function GenerateProductSitemap(){
        $quotes = (defined('QUOTES_CATEGORY_NAME') ? " and customers_email_address = '' and quotes_email_address = ''" : '');
		$sql = "SELECT products_id as pID, products_date_added as date_added, products_last_modified as last_mod, products_ordered 
			    FROM " . TABLE_PRODUCTS . " 
				WHERE products_status='1'" . $quotes . "
				ORDER BY products_ordered DESC";
		if ( $products_query = tep_db_query($sql) ){
			$this->debug['QUERY']['PRODUCTS']['STATUS'] = 'success';
			$this->debug['QUERY']['PRODUCTS']['NUM_ROWS'] = tep_db_num_rows($products_query);
			$container = array();
			$number = 0;
			$top = 0;
			$test = 0;
			while( $result = tep_db_fetch_array($products_query) ){
				$top = max($top, $result['products_ordered']);
				$location = $this->hrefLink(FILENAME_PRODUCT_INFO, 'products_id=' . $result['pID'], 'NONSSL');
				$lastmod = $this->NotNull($result['last_mod']) ? $result['last_mod'] : $result['date_added'];
				$changefreq = GOOGLE_SITEMAP_PROD_CHANGE_FREQ;
				$ratio = $top > 0 ? $result['products_ordered']/$top : 0;
				$priority = $ratio < .1 ? .1 : number_format($ratio, 1, '.', ''); 
				
				$container[] = array('loc' => htmlspecialchars(utf8_encode($location)),
				                     'lastmod' => date ("Y-m-d", strtotime($lastmod)),
									 'changefreq' => $changefreq,
									 'priority' => $priority
				                     );
				if ( sizeof($container) >= 50000 ){
					$type = $number == 0 ? 'products' : 'products' . $number;
					$this->GenerateSitemap($container, $type);
					$container = array();
					$number++;
				}
				$test++;
			}
			tep_db_free_result($products_query);	
			if ( sizeof($container) > 0 ) {
				$type = $number == 0 ? 'products' : 'products' . $number;
				return $this->GenerateSitemap($container, $type);
			}			
		} else {
			$this->debug['QUERY']['PRODUCTS']['STATUS'] = 'false';
			$this->debug['QUERY']['PRODUCTS']['NUM_ROWS'] = '0';
		}
	}

	function GenerateCategorySitemap(){
        $quotes = (defined('QUOTES_CATEGORY_NAME') ? " where cd.categories_name NOT LIKE '" . QUOTES_CATEGORY_NAME . "' " : '');
		$sql = "SELECT DISTINCT c.categories_id as cID, c.date_added, c.last_modified as last_mod 
			    FROM " . TABLE_CATEGORIES . " c left join " . TABLE_CATEGORIES_DESCRIPTION . " cd on c.categories_id = cd.categories_id  
          " . $quotes . "
				ORDER BY c.parent_id ASC, c.sort_order ASC, c.categories_id ASC";
         
		if ( $categories_query = tep_db_query($sql) ){
			$this->debug['QUERY']['CATEGORY']['STATUS'] = 'success';
			$this->debug['QUERY']['CATEGORY']['NUM_ROWS'] = tep_db_num_rows($categories_query);
			$container = array();
			$number = 0;
			while( $result = tep_db_fetch_array($categories_query) ){
				$location = $this->hrefLink(FILENAME_DEFAULT, 'cPath=' . $this->GetFullcPath($result['cID']), 'NONSSL');
				$lastmod = $this->NotNull($result['last_mod']) ? $result['last_mod'] : $result['date_added'];
				$changefreq = GOOGLE_SITEMAP_CAT_CHANGE_FREQ;
				$priority = .5; 
				
				$container[] = array('loc' => htmlspecialchars(utf8_encode($location)),
				                     'lastmod' => date ("Y-m-d", strtotime($lastmod)),
									 'changefreq' => $changefreq,
									 'priority' => $priority
				                     );
				if ( sizeof($container) >= 50000 ){
					$type = $number == 0 ? 'categories' : 'categories' . $number;
					$this->GenerateSitemap($container, $type);
					$container = array();
					$number++;
				}
			}
			tep_db_free_result($categories_query);			
			if ( sizeof($container) > 0 ) {
				$type = $number == 0 ? 'categories' : 'categories' . $number;
				return $this->GenerateSitemap($container, $type);
			}
		} else {
			$this->debug['QUERY']['CATEGORY']['STATUS'] = 'false';
			$this->debug['QUERY']['CATEGORY']['NUM_ROWS'] = '0';
		}
	}

	function GenerateManufacturerSitemap(){
        $sql = "SELECT manufacturers_id as mID, date_added, last_modified as last_mod, manufacturers_name
                FROM " . TABLE_MANUFACTURERS . " order by manufacturers_name DESC";

		if ( $manufacturers_query = tep_db_query($sql) ){
			$this->debug['QUERY']['MANUFACTURERS']['STATUS'] = 'success';
			$this->debug['QUERY']['MANUFACTURERS']['NUM_ROWS'] = tep_db_num_rows($manufacturers_query);
			$container = array();
			$number = 0;
			while( $result = tep_db_fetch_array($manufacturers_query) ){
				$location = $this->hrefLink(FILENAME_DEFAULT, 'manufacturers_id=' . $result['mID'], 'NONSSL');
				$lastmod = $this->NotNull($result['last_mod']) ? $result['last_mod'] : $result['date_added'];
				$changefreq = GOOGLE_SITEMAP_MAN_CHANGE_FREQ;
				$priority = .5;

				$container[] = array('loc' => htmlspecialchars(utf8_encode($location)),
				                     'lastmod' => date ("Y-m-d", strtotime($lastmod)),
									 'changefreq' => $changefreq,
									 'priority' => $priority
				                     );
				if ( sizeof($container) >= 50000 ){
					$type = $number == 0 ? 'manufacturers' : 'manufacturers' . $number;
					$this->GenerateSitemap($container, $type);
					$container = array();
					$number++;
				}
			}
			tep_db_free_result($manufacturers_query);
			if ( sizeof($container) > 0 ) {
				$type = $number == 0 ? 'manufacturers' : 'manufacturers' . $number;
				return $this->GenerateSitemap($container, $type);
			}
		} else {
			$this->debug['QUERY']['MANUFACTURERS']['STATUS'] = 'false';
			$this->debug['QUERY']['MANUFACTURERS']['NUM_ROWS'] = '0';
		}
	}

	function GenerateSpecialsSitemap($languages_id){
		global $languages_id;
        $products_query = tep_db_query("SELECT p.products_id as pID, s.specials_date_added as date_added, s.specials_last_modified as last_mod, p.products_ordered
                FROM " . TABLE_PRODUCTS . " p left join " . TABLE_PRODUCTS_DESCRIPTION . " pd on p.products_id = pd.products_id left join " . TABLE_SPECIALS . " s on pd.products_id = s.products_id
            where p.products_status = '1' and s.status = '1' and pd.language_id = " . (int)$languages_id . " order by s.specials_date_added desc ");
		if ( tep_db_num_rows($products_query) > 0 ){
			$this->debug['QUERY']['SPECIALS']['STATUS'] = 'success';
			$this->debug['QUERY']['SPECIALS']['NUM_ROWS'] = tep_db_num_rows($products_query);
			$container = array();
			$number = 0;
			$top = 0;
			while( $result = tep_db_fetch_array($products_query) ){
				$top = max($top, $result['products_ordered']);
				$location = $this->hrefLink(FILENAME_PRODUCT_INFO, 'products_id=' . $result['pID'], 'NONSSL');
				$lastmod = $this->NotNull($result['last_mod']) ? $result['last_mod'] : $result['date_added'];
				$changefreq = GOOGLE_SITEMAP_SPECIALS_CHANGE_FREQ;
				$ratio = $top > 0 ? $result['products_ordered']/$top : 0;
				$priority = $ratio < .1 ? .1 : number_format($ratio, 1, '.', ''); 
				
				$container[] = array('loc' => htmlspecialchars(utf8_encode($location)),
				                     'lastmod' => date ("Y-m-d", strtotime($lastmod)),
									 'changefreq' => $changefreq,
									 'priority' => $priority
				                     );
				if ( sizeof($container) >= 50000 ){
					$type = $number == 0 ? 'specials' : 'specials' . $number;
					$this->GenerateSitemap($container, $type);
					$container = array();
					$number++;
				}
			}
			tep_db_free_result($products_query);			
			if ( sizeof($container) > 0 ) {
				$type = $number == 0 ? 'specials' : 'specials' . $number;
				return $this->GenerateSitemap($container, $type);
			}
		} else {
			$this->debug['QUERY']['SPECIALS']['STATUS'] = 'false';
			$this->debug['QUERY']['SPECIALS']['NUM_ROWS'] = '0';
			return $this->GenerateSitemap(array(), 'specials');
		}
	}

	function GeneratePagesSitemap(){
		global $languages_id;
		$container = array();
		$changefreq = GOOGLE_SITEMAP_PAGES_CHANGE_FREQ;
		$priority = '.1';
		$languages = tep_get_languages(true);
		reset($languages);
		$lang = 1;
		$slash = /*substr(DIR_FS_CATALOG, 0 -1) == '/' ? 1 : 0*/'';
		$path = (($pos = strpos(DIR_FS_CATALOG, "googlesitemap")) !== FALSE) ? substr(DIR_FS_CATALOG, 0, -strlen('googlesitemap') - $slash) : DIR_FS_CATALOG;
		$pages = $this->GetPagesArray($path, DIR_WS_LANGUAGES . $languages[$lang]['directory'], $languages[$lang]['id']);
		$this->debug['QUERY']['PAGES']['STATUS'] = 'success';
		$this->debug['QUERY']['PAGES']['NUM_ROWS'] = count($pages);
		for ($i = 0; $i < count($pages); ++$i) {
			$container[] = array('loc' => htmlspecialchars(utf8_encode($pages[$i]['filename'])),
								 'lastmod' => $pages[$i]['lastmod'],
								 'changefreq' => $changefreq,
								 'priority' => $priority);	             
		}
		if ($i > 0)
			return $this->GenerateSitemap($container, 'pages');
	}

	function GetFullcPath($cID){
		if ( preg_match('/_/', $cID) ){
			return $cID;
		} else {
			$c = array();
			$this->GetParentCategories($c, $cID);
			$c = array_reverse($c);
			$c[] = $cID;
			$cID = sizeof($c) > 1 ? implode('_', $c) : $cID;
			return $cID;
		}
	}

	function GetParentCategories(&$categories, $categories_id) {
		$sql = "SELECT parent_id 
		        FROM " . TABLE_CATEGORIES . " 
				WHERE categories_id='" . (int)$categories_id . "'";
		$parent_categories_query = tep_db_query($sql);
		while ($parent_categories = tep_db_fetch_array($parent_categories_query)) {
			if ($parent_categories['parent_id'] == 0) return true;
			$categories[sizeof($categories)] = $parent_categories['parent_id'];
			if ($parent_categories['parent_id'] != $categories_id) {
				$this->GetParentCategories($categories, $parent_categories['parent_id']);
			}
		}
	}

	function NotNull($value) {
		if (is_array($value)) {
			if (sizeof($value) > 0) {
				return true;
			} else {
				return false;
			}
		} else {
			if (($value != '') && (strtolower($value) != 'null') && (strlen(trim($value)) > 0)) {
				return true;
			} else {
				return false;
			}
		}
	}

	function hrefLink($page, $parameters, $connection) {
		if ( defined('SEO_URLS') && SEO_URLS == 'true' || defined('SEO_ENABLED') && SEO_ENABLED == 'true' ) {
			return tep_href_link($page, $parameters, $connection);
		} else {
			return tep_href_link($page, $parameters, $connection);
			//return $this->base_url . $page . '?' . $parameters;
		}
	}

	function ReadGZ( $file ){
		$file = $this->savepath . $file;
		$lines = gzfile($file);
		return implode('', $lines);
	}

	function GenerateSubmitURL(){
		$url = urlencode($this->base_url . 'sitemapindex.xml');
		return htmlspecialchars(utf8_encode('http://www.google.com/webmasters/sitemaps/ping?sitemap=' . $url));
	}
  
	function GetPagesArray($locn, $languagesDir, $languageID) {
		$cwd = getcwd(); 
		$pagesArray = array();
		
		$end =  (substr($locn, strlen($locn) - 1) !== '/') ? '/' : '';
		$root = /*'/'.*/$locn . $end;
		$path = $root . $languagesDir;
		$end =  (substr($path, strlen($path) - 1) !== '/') ? '/' : '';
		$path = /*'/'.*/$path . $end;
		/*
		fout op localhost door slash
		chdir ('/'.$locn);*/
		chdir ($locn);
		foreach (glob("*.php") as $filename) {
			if (! in_array($filename, $this->excludeList) && $this->IsViewable($root . $filename)) {
				$r = @stat($filename);
				$displayName = ucwords(str_replace("_", " ", substr($filename, 0, strpos($filename, "."))));

				if ($filename === 'infopage.php') {
					$sql = "SELECT i.infopages_id, DATE_FORMAT(i.date_added, '%Y-%m-%d') as date_added from infopages i, infopages_text it where i.infopages_id = it.infopages_id AND i.infopages_status = '1' and it.language_id = '" . (int)$languageID . "' AND (i.type='pages')";
					if ( $information_query = tep_db_query($sql) ) {
						while( $result = tep_db_fetch_array($information_query) ) {
							$page = 'infopage.php' . '?page=' . $result['infopages_id'];
							if (! in_array($page, $this->excludeList)) {
								$pagesArray[] = array('filename' => $this->hrefLink(FILENAME_INFOPAGE, 'page=' . $result['infopages_id'], 'NONSSL'),
													  'lastmod' => $result['date_added']);
							}
						}          
					} 
				} else {
					$pagesArray[] = array('filename' => $this->base_url . $filename,
									  	'lastmod' => gmstrftime ("%Y-%m-%d", $r[9]));
				}
			}    
		}
		chdir ($cwd);
		return $pagesArray;
	}  
  
  function IsViewable($file)
  {
    if (($fp = file($file)))
    {
      for ($idx = 0; $idx < count($fp); ++$idx)
      {
         if (strpos($fp[$idx], "<head>") !== FALSE)
           return true;
      }
    }  
    return false;
  }  
  
}
?>