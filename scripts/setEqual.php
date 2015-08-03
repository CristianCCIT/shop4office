<?php
/**
 * Written by Boris Wintein
 * For aboservice
 * Time: 10:13
 */
include('includes/application_top.php');

$languages = array();

$query = "SELECT * FROM modules WHERE modules_status = 1 AND type = 'modules'";
$resource = tep_db_query($query);

$languageQuery = "SELECT * FROM languages";
$languageResource = tep_db_query($languageQuery);

while ($langinfo = tep_db_fetch_array($languageResource)) {
    $languages[$langinfo['name']] = $langinfo['languages_id'];
}

while (($module = tep_db_fetch_array($resource))) {

    $module_id = $module['modules_id'];

    $moduleQuery = "SELECT * FROM modules_text WHERE modules_id = " . $module_id . " AND language_id = 1";
    $moduleResource = tep_db_query($moduleQuery);
    $moduleTemp = tep_db_fetch_array($moduleResource);
    $module_description = $moduleTemp['modules_description'];
    $module_title = $moduleTemp['modules_title'];

    foreach ($languages as $key => $language_id) {

        $checkQuery = "SELECT * FROM modules_text WHERE modules_id = " . $module_id . " AND language_id = " . $language_id;
        $checkResource = tep_db_query($checkQuery);

        if (tep_db_num_rows($checkResource) > 0) {
            $moduleUpdate = "UPDATE modules_text SET modules_description = '" . $module_description . "' WHERE modules_id = " . $module_id;
            tep_db_query($moduleUpdate);
            echo "SUCCESFULLY UPDATED " . $module_title . ' (Language = '.$key.')<br />';
        } else {
            $moduleInsert = "INSERT INTO modules_text (modules_id, modules_title, modules_description, language_id) VALUES ('".$module_id."', '".$module_title."', '".$module_description."', ".$language_id.")";
            tep_db_query($moduleInsert);
            echo "SUCCESFULLY INSERTED " . $module_title . ' (Language = '.$key.')<br />';
        }
    }
}

?>
