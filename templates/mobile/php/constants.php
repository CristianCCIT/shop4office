<?php
/**
 * Created by PhpStorm.
 * User: Cristian
 * Date: 5/7/2015
 * Time: 7:46 AM
 */

define('root', implode(DIRECTORY_SEPARATOR,array(dirname(__FILE__),'..','')));
define('inclPath', root.'..'.DIRECTORY_SEPARATOR);
define('devIP', '127.0.0.1');
define('www', implode('', array((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http'),  '://', $_SERVER['SERVER_NAME'], '/')));