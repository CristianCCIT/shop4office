<?php
$folders_query = tep_db_query('SELECT i.image, it.infopages_title as title, it.infopages_banner as folder FROM infopages i, infopages_text it WHERE i.infopages_id = it.infopages_id AND i.type="folder" AND i.infopages_status = "1" ORDER BY i.sort_order asc LIMIT 1');

if (tep_db_num_rows($folders_query) > 0) {	
	$folders = tep_db_fetch_array($folders_query);
	$imagegallery = '<a href="#" title="'.$folders['title'].'" onClick="javascript:window.open(\''.tep_href_link('folders/'.$folders['folder']).'\',\'\',\'scrollbars=yes,menubar=no,height=600,width=800,resizable=yes,toolbar=no,location=no,status=no\')">'.tep_image('images/folder/'.$folders['image'], '', '100', '100').'</a><br /><a href="#" title="'.$folders['title'].'" onClick="javascript:window.open(\''.tep_href_link('folders/'.$folders['folder']).'\',\'\',\'scrollbars=yes,menubar=no,height=600,width=800,resizable=yes,toolbar=no,location=no,status=no\')">Bekijk hem hier</a>';
	
	$title = '<a href="#" title="'.$folders['title'].'" onClick="javascript:window.open(\''.tep_href_link('folders/'.$folders['folder']).'\',\'\',\'scrollbars=yes,menubar=no,height=600,width=800,resizable=yes,toolbar=no,location=no,status=no\')">Onze folder</a>';
	echo '<div class="box-title">'.$title.'</div>';
	echo '<div class="box-content">'.$imagegallery.'</div>';
}
?>