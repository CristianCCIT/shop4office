<?php
function CheckInfomationPages($searchTerm, $pagesList, $languages_id)
{
     $pages_query = tep_db_query("select i.infopages_id, it.infopages_title from infopages i left join infopages_text it on i.infopages_id = it.infopages_id where ( it.infopages_title like '%" . $searchTerm . "%' or it.infopages_description like '%" . $searchTerm . "%' or it.infopages_preview like '%" . $searchTerm . "%' ) AND i.infopages_status = 1 and it.language_id = " . (int)$languages_id. " ORDER BY i.type asc, i.sort_order asc"); 
     while ($pages = tep_db_fetch_array($pages_query))
     {
       $pagesList[] = array('file' => FILENAME_INFOPAGE, 'id' => 'page='.$pages['infopages_id'], 'text' => $pages['infopages_title']); 
     }
   return $pagesList;
}
  
function SortFileLists($a, $b) {
 return strnatcasecmp($a["id"], $b["id"]);
}

$pagesList = CheckInfomationPages($searchTerm, $pagesList, $languages_id);

if (count($pagesList) > 0) {
  usort($pagesList, "SortFileLists"); 
?>
<table border="0" width="100%" cellspacing="0" cellpadding="2">
  <tr>
   <td height="20"></td>
  </tr>
  <tr>
    <td class="pageHeading"><?php echo Translate('Zoeken'); ?></td>
  </tr>
<?php   
	for($i=0; $i < count($pagesList); ++$i) {
?>
   <tr>
	  <td class="main"><?php echo '<a href="' . tep_href_link($pagesList[$i]['file'], $pagesList[$i]['id']) . '">' . ucwords($pagesList[$i]['text']) . '</a>'; ?></td>
   </tr>
<?php } ?>
</table> 
<?php
  }
?>