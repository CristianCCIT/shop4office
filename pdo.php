<?php
 if (!defined('PDO::ATTR_DRIVER_NAME')) {
echo 'PDO unavailable';
}
elseif (defined('PDO::ATTR_DRIVER_NAME')) {
echo 'PDO available';
}
?>