<?php

/**
 * Created by PhpStorm.
 * User: lfliangli
 * Date: 2016/10/20 0020
 * Time: 10:11
 */
abstract class AbstractBaseController extends Yaf_Controller_Abstract {
    /**
     * init默认执行
     */
    public function init() {
        defined('CURMODULE') || define('CURMODULE', $this->getRequest()->action);
        $this->setViewpath(APPLICATION_PATH.'/application/views/default');

        global $_G;
        
        $this->_init();
    }

    public function _init(){}
}