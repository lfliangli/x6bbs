<?php

define('APPLICATION_PATH', dirname(__FILE__));

require_once './public/Common.php';
$application = new Yaf_Application( APPLICATION_PATH . "/conf/application.ini");

$application->bootstrap()->run();
