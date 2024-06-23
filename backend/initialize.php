<?php
define('__ROOT__', dirname(dirname(__FILE__)));
if (!defined('BASE_URL'))
    define('BASE_URL', 'http://localhost/WE4B/backend/classes/');
if (!defined('BASE_APP'))
    define('BASE_APP', str_replace('\\', '/', __DIR__) . '/');
if (!defined('DB_SERVER'))
    define('DB_SERVER', "localhost");
if (!defined('DB_USERNAME'))
    define('DB_USERNAME', "root");
if (!defined('DB_PASSWORD'))
    define('DB_PASSWORD', "");
if (!defined('DB_NAME'))
    define('DB_NAME', "netatlas");
?>