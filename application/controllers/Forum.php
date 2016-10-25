<?php

/**
 * Created by PhpStorm.
 * User: lfliangli
 * Date: 2016/10/20 0020
 * Time: 10:11
 */
class ForumController extends AbstractBaseController {
    public function _init() {
        defined('CURSCRIPT') || define('CURSCRIPT', 'forum');
        defined('APPTYPEID') || define('APPTYPEID', 2);
    }

    public function indexAction() {
        $ret = ForumModel::getInstance()->test();

        $this->assign('ret', $ret);
        $this->display('test');
    }
}