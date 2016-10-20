<?php

/**
 * Created by PhpStorm.
 * User: lfliangli
 * Date: 2016/10/20 0020
 * Time: 10:11
 */
class ForumController extends SuperBaseController {
    public function _init() {
        defined('APPTYPEID') || define('APPTYPEID', 2);
    }

    public function indexAction() {
        var_dump($_GET);
    }
}