<?php
  $manufacturers_query = tep_db_query("select manufacturers_id, manufacturers_name from " . TABLE_MANUFACTURERS . " order by manufacturers_name");
  if ($number_of_rows = tep_db_num_rows($manufacturers_query)) {
    if ($number_of_rows <= MAX_DISPLAY_MANUFACTURERS_IN_A_LIST) {
	// Display a list
      $manufacturers_list = '';
      while ($manufacturers = tep_db_fetch_array($manufacturers_query)) {
        $manufacturers_name = ((strlen($manufacturers['manufacturers_name']) > MAX_DISPLAY_MANUFACTURER_NAME_LEN) ? substr($manufacturers['manufacturers_name'], 0, MAX_DISPLAY_MANUFACTURER_NAME_LEN) . '..' : $manufacturers['manufacturers_name']);
        if (isset($_GET['manufacturers_id']) && ($_GET['manufacturers_id'] == $manufacturers['manufacturers_id'])) $manufacturers_name = '<b>' . $manufacturers_name .'</b>';
        $manufacturers_list .= '<a href="' . tep_href_link(FILENAME_DEFAULT, 'manufacturers_id=' . $manufacturers['manufacturers_id']) . '">' . $manufacturers_name . '</a><br>';
      }
      $manufacturers_list = substr($manufacturers_list, 0, -4);
      $info_box_contents = array();
      $info_box_contents[] = array('text' => $manufacturers_list);
	  new infoBox($info_box_contents);
    } else {
	// Display a drop-down
      $manufacturers_array = array();
      if (MAX_MANUFACTURERS_LIST < 2) {
        $manufacturers_array[] = array('id' => '', 'text' => Translate('Selecteer een merk'));
      }
      while ($manufacturers = tep_db_fetch_array($manufacturers_query)) {
        $manufacturers_name = ((strlen($manufacturers['manufacturers_name']) > MAX_DISPLAY_MANUFACTURER_NAME_LEN) ? substr($manufacturers['manufacturers_name'], 0, MAX_DISPLAY_MANUFACTURER_NAME_LEN) . '..' : $manufacturers['manufacturers_name']);
        $manufacturers_array[] = array('id' => tep_href_link(FILENAME_DEFAULT, 'manufacturers_id='.$manufacturers['manufacturers_id']),
                                       'text' => $manufacturers_name);
      }
    echo tep_draw_pull_down_menu_seo('manufacturers_id', $manufacturers_array, (isset($_GET['manufacturers_id']) ? $_GET['manufacturers_id'] : ''), ' size="' . MAX_MANUFACTURERS_LIST . '" style="width: 150px"') . tep_hide_session_id()."<script>
function cakesz() {
var list=document.getElementById('cakesize');
var s=list.options[list.selectedIndex].value;
window.location = s;
}
</script>";
    }
  }
?>