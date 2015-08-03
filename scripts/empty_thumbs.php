<?

	//include('dbconfig.php');

	//include('library.php');



   // connect2DB(); // as per values specified in config file

/**
* Empty folder (work recursuvely)
*
* @autor Hatem <http://hatem.phpmagazine.net>
* @param string        $folder        Folder name (without trailing slash)
* @param boolean    $debug        print debug message
* @return void
*/
function empty_folder($folder, $debug = false){
   
    if ($debug) {
        echo "Cleaning folder $folder ... <br>";
    }
   
    $d = dir($folder);
   
    while (false !== ($entry = $d->read())) {
   
        $isdir = is_dir($folder."/".$entry);
       
        if (!$isdir and $entry!="." and $entry!=".." and $entry!=".htaccess") {
       
            unlink($folder."/".$entry);
           
        } elseif ($isdir  and $entry!="." and $entry!=".." and $entry!=".htaccess") {
       
            empty_folder($folder."/".$entry,$debug);
           
            rmdir($folder."/".$entry);
           
        }
    }
    $d->close();
}
empty_folder("../images/foto/thumbs",true); 
?>