<?php

define('APPLICATION_PATH', dirname(__FILE__));
define('APPLICATION_VIEWS', APPLICATION_PATH . '/application/views');

$application = new Yaf_Application( APPLICATION_PATH . "/conf/application.ini");

$application->bootstrap()->run();