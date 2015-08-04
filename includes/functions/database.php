<?php



/**

 * @param string $server

 * @param string $username

 * @param string $password

 * @param string $database

 * @param string $link

 * @return resource

 */

function tep_db_connect($server = DB_SERVER, $username = DB_SERVER_USERNAME, $password = DB_SERVER_PASSWORD, $database = DB_DATABASE, $link = 'db_link') {

    global $$link;



    if (USE_PCONNECT == 'true') {

        $$link = mysql_pconnect($server, $username, $password) or abo_db_error(DB_CONNECT_FAIL, mysql_error());

    } else {

        $$link = mysql_connect($server, $username, $password) or abo_db_error(DB_CONNECT_FAIL, mysql_error());

    }



    if ($$link) mysql_select_db($database) or abo_db_error(DB_SELECT_FAIL, mysql_error());



    mysql_set_charset('utf8',$$link);



   return $$link;

}



/**

 * @param string $link

 * @return bool

 */

function tep_db_close($link = 'db_link') {

    global $$link;



    return mysql_close($$link);

}



/**

 * @param $query

 * @param $errno

 * @param $error

 * @deprecated use abo_db_error() instead.

 */

function tep_db_error($query, $errno, $error) {



    echo "Deprecated function. Use abo_db_error instead";



    abo_db_error(DB_BASIC_FAIL, $error, $query, $errno);

    //die('<font color="#000000"><b>' . $errno . ' - ' . $error . '<br><br>' . $query . '<br><br><small><font color="#ff0000">[TEP STOP]</font></small><br><br></b></font>');

}



/**

 * @param $type

 * @param string $error

 * @param string $query

 * @param string $errno

 */

function abo_db_error($type, $error ='' ,$query = '', $errno = '') {



    $temp = debug_backtrace();



    $output = "<div style='background: #7a7a7a; width: 100%; height: 100%; min-width: 800px; min-height: 600px; position:fixed; top:0; left:0;;'>";

    $output .= "<div style='margin: 10px auto; width: 900px; background: #FFFFFF; padding: 10px;'>";

    $output .= "<h1>Something went wrong!</h1>";



    switch ($type) {



        case DB_CONNECT_FAIL:

            $output .= "<p style='color: #000000;'><span style='color: #FF0000'>[DATABASE]&nbsp;</span>I'm sorry, I was unable to connect to the database. Please check your configuration files.</p>";

            $output .= "<h3>Official MySQL Error</h3>";

            $output .= "<p style='color: #000000;'>[".$errno."]&nbsp;".$error."</p>";

            break;



        case DB_QUERY_FAIL:

            $output .= "<p style='color: #000000;'><span style='color: #FF0000'>[DATABASE]&nbsp;</span>I'm sorry. That query failed. </p>";

            $output .= "<p style='color: #000000;'>". $query ."</p>";

            $output .= "<h2>Official MySQL Error</h2>";

            $output .= "<p style='color: #000000;'>[".$errno."]&nbsp;".$error."</p>";

            break;



    }



    $output .= "<h3>Debug Info</h3>";

    $output .= "<table width='100%'>";

    $output .= "<tr><td style='width: 100px;'>FUNCTION:</td><td>" . $temp[1]['function'] . "</td></tr>";

    $output .= "<tr><td>CALLED IN:</td><td>" . $temp[1]['file'] . "</td></tr>";

    $output .= "<tr><td>LINE:</td><td>".$temp[1]['line']."</td></tr>";

    $output .= "</table>";



    $output .= "</div></div>";



    echo $output;

    die();

}





/**

 * @param $query

 * @param string $link

 * @return resource

 */

function tep_db_query($query, $link = 'db_link') {

    global $$link;



    if (defined('STORE_DB_TRANSACTIONS') && (STORE_DB_TRANSACTIONS == 'true')) {

        if (STORE_PAGE_PARSE_TIME_PATH=='relative') {

            $log_file = DIR_FS_CATALOG.STORE_PAGE_PARSE_TIME_LOG;

        } else {

            $log_file = STORE_PAGE_PARSE_TIME_LOG;

        }

        error_log('QUERY ' . $query . "\n", 3, $log_file);

    }



    $result = mysql_query($query, $$link) or abo_db_error(DB_QUERY_FAIL, mysql_error($$link), $query, mysql_errno($$link));



    if (defined('STORE_DB_TRANSACTIONS') && (STORE_DB_TRANSACTIONS == 'true')) {

        $result_error = mysql_error();

        if (STORE_PAGE_PARSE_TIME_PATH=='relative') {

            $log_file = DIR_FS_CATALOG.STORE_PAGE_PARSE_TIME_LOG;

        } else {

            $log_file = STORE_PAGE_PARSE_TIME_LOG;

        }

        error_log('RESULT ' . $result . ' ' . $result_error . "\n", 3, $log_file);

    }



    return $result;

}



/**

 * @param $table

 * @param $data

 * @param string $action

 * @param string $parameters

 * @param string $link

 * @return resource

 */

function tep_db_perform($table, $data, $action = 'insert', $parameters = '', $link = 'db_link') {

    reset($data);

    if ($action == 'insert') {

        $query = 'insert into ' . $table . ' (';

        while (list($columns, ) = each($data)) {

            $query .= '`'.$columns . '`, ';

        }

        $query = substr($query, 0, -2) . ') values (';

        reset($data);

        while (list(, $value) = each($data)) {

            switch ((string)$value) {

                case 'now()':

                    $query .= 'now(), ';

                    break;

                case 'null':

                    $query .= 'null, ';

                    break;

                default:

                    $query .= '\'' . tep_db_input($value) . '\', ';

                    break;

            }

        }

        $query = substr($query, 0, -2) . ')';

    } elseif ($action == 'update') {

        $query = 'update ' . $table . ' set ';

        while (list($columns, $value) = each($data)) {

            switch ((string)$value) {

                case 'now()':

                    $query .= $columns . ' = now(), ';

                    break;

                case 'null':

                    $query .= $columns .= ' = null, ';

                    break;

                default:

                    $query .= $columns . ' = \'' . tep_db_input($value) . '\', ';

                    break;

            }

        }

        $query = substr($query, 0, -2) . ' where ' . $parameters;

    }



    return tep_db_query($query, $link);

}



/**

 * @param $db_query

 * @return array

 */

function tep_db_fetch_array($db_query) {

    return mysql_fetch_array($db_query, MYSQL_ASSOC);

}



/**

 * @param $db_query

 * @return int

 */

function tep_db_num_rows($db_query) {

    return mysql_num_rows($db_query);

}



/**

 * @param $db_query

 * @param $row_number

 * @return bool

 */

function tep_db_data_seek($db_query, $row_number) {

    return mysql_data_seek($db_query, $row_number);

}



/**

 * @param string $link

 * @return int

 */

function tep_db_insert_id($link = 'db_link') {

    global $$link;



    return mysql_insert_id($$link);

}



/**

 * @param $db_query

 * @return bool

 */

function tep_db_free_result($db_query) {

    return mysql_free_result($db_query);

}



/**

 * @param $db_query

 * @return an|object

 */

function tep_db_fetch_fields($db_query) {

    return mysql_fetch_field($db_query);

}



/**

 * @param $string

 * @return string

 */

function tep_db_output($string) {

    return htmlspecialchars($string);

}



/**

 * @param $string

 * @param string $link

 * @return string

 */

function tep_db_input($string, $link = 'db_link') {

    global $$link;



    if (function_exists('mysql_real_escape_string')) {

        return mysql_real_escape_string($string, $$link);

    } elseif (function_exists('mysql_escape_string')) {

        return mysql_escape_string($string);

    }



    return addslashes($string);

}



/**

 * @param $string

 * @return array|string

 */

function tep_db_prepare_input($string) {

    if (is_string($string)) {

        return trim(tep_sanitize_string(stripslashes($string)));

    } elseif (is_array($string)) {

        reset($string);

        while (list($key, $value) = each($string)) {

            $string[$key] = tep_db_prepare_input($value);

        }

        return $string;

    } else {

        return $string;

    }

}

//phplist functions

/**

 * @param string $server

 * @param string $username

 * @param string $password

 * @param string $database

 * @param string $link

 * @return resource

 */

function tep_db_list_connect($server = PHPLIST_DB_SERVER, $username = PHPLIST_DB_USER, $password = PHPLIST_DB_PASSWORD, $database = PHPLIST_LIST_DB, $link = 'db_list_link') {

    global $$link_list;

    if (USE_PCONNECT == 'true') {

        $$link_list = mysql_pconnect($server, $username, $password) or die('den pconnect ga nie');

    } else {

        $$link_list = mysql_connect($server, $username, $password) or die('den connect ga nie');

    }

    if ($$link_list) mysql_select_db($database) or die('den select database ga nie');

    return $$link_list;

}



/**

 * @param string $link

 * @return bool

 */

function tep_db_list_close($link = 'db_list_link') {

    global $$link_list;

    return mysql_close($$link_list);

}



/**

 * @param $query

 * @param string $link

 * @return resource

 */

function tep_db_list_query($query, $link = 'db_list_link') {

    global $$link_list;

    if (defined('STORE_DB_TRANSACTIONS') && (STORE_DB_TRANSACTIONS == 'true')) {

        if (STORE_PAGE_PARSE_TIME_PATH=='relative') {

            $log_file = DIR_FS_CATALOG.STORE_PAGE_PARSE_TIME_LOG;

        } else {

            $log_file = STORE_PAGE_PARSE_TIME_LOG;

        }



        error_log('QUERY ' . $query . "\n", 3, $log_file);

    }
//    xD3bug($query,1,'127.0.0.1');
    $result = mysql_query($query, $$link_list) or tep_db_error($query, mysql_errno(), mysql_error());

    if (defined('STORE_DB_TRANSACTIONS') && (STORE_DB_TRANSACTIONS == 'true')) {

        $result_error = mysql_error();

        if (STORE_PAGE_PARSE_TIME_PATH=='relative') {

            $log_file = DIR_FS_CATALOG.STORE_PAGE_PARSE_TIME_LOG;

        } else {

            $log_file = STORE_PAGE_PARSE_TIME_LOG;

        }

        error_log('RESULT ' . $result . ' ' . $result_error . "\n", 3, $log_file);

    }

    return $result;

}

?>